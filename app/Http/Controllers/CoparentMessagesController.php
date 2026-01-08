<?php

namespace App\Http\Controllers;

use App\Events\CoparentMessageSent;
use App\Models\Collaborator;
use App\Models\CoparentChild;
use App\Models\CoparentConversation;
use App\Models\CoparentMessage;
use App\Models\CoparentMessageAttachment;
use App\Models\CoparentMessageReaction;
use App\Models\CoparentMessageTemplate;
use App\Models\FamilyMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CoparentMessagesController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get own children with co-parenting enabled
        $ownChildrenIds = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->pluck('id')
            ->toArray();

        // Get collaborator IDs for this user (co-parent access from other tenants)
        $collaboratorIds = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->pluck('id')
            ->toArray();

        // Get family_member_ids the user has co-parent access to
        $sharedChildrenIds = CoparentChild::whereIn('collaborator_id', $collaboratorIds)
            ->pluck('family_member_id')
            ->toArray();

        // Merge all family_member IDs the user has access to
        $allFamilyMemberIds = array_unique(array_merge($ownChildrenIds, $sharedChildrenIds));

        // Get ALL CoparentChild IDs for these family members (so user sees all conversations about these children)
        $allCoparentChildIds = CoparentChild::whereIn('family_member_id', $allFamilyMemberIds)
            ->pluck('id')
            ->toArray();

        // Get conversations for these children
        $conversations = CoparentConversation::whereIn('child_id', $allCoparentChildIds)
            ->with(['child.familyMember', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderByDesc('last_message_at')
            ->get();

        // Calculate unread counts for each conversation
        foreach ($conversations as $conversation) {
            $conversation->unread_count = $conversation->unreadCountFor($user->id);
        }

        // Message categories for reference
        $categories = CoparentMessage::CATEGORIES;

        return view('pages.coparenting.messages.index', compact('conversations', 'categories'));
    }

    /**
     * Show the form for creating a new conversation.
     */
    public function create(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get own children with co-parenting enabled
        $ownChildrenIds = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->pluck('id')
            ->toArray();

        // Get CoparentChild records for own children
        $ownCoparentChildren = CoparentChild::whereIn('family_member_id', $ownChildrenIds)
            ->with('familyMember')
            ->get();

        // Get collaborator IDs for this user (co-parent access)
        $collaborators = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with('inviter')
            ->get()
            ->keyBy('id');

        // Get CoparentChild records for these collaborators
        $sharedCoparentChildren = CoparentChild::whereIn('collaborator_id', $collaborators->keys()->toArray())
            ->with('familyMember')
            ->get();

        // Add other_parent_name to shared children
        foreach ($sharedCoparentChildren as $coparentChild) {
            $collaborator = $collaborators->get($coparentChild->collaborator_id);
            $coparentChild->other_parent_name = $collaborator?->inviter?->name ?? 'Other Parent';
        }

        // Merge and dedupe by CoparentChild id
        $coparentChildren = $ownCoparentChildren->merge($sharedCoparentChildren)->unique('id');

        // Get templates
        $templates = CoparentMessageTemplate::where(function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id)
              ->orWhere('is_system', true);
        })->get()->groupBy('category');

        $categories = CoparentMessage::CATEGORIES;

        return view('pages.coparenting.messages.create', compact('coparentChildren', 'templates', 'categories'));
    }

    /**
     * Store a new conversation with the first message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:coparent_children,id',
            'subject' => 'nullable|string|max:255',
            'category' => 'required|string|in:General,Schedule,Medical,Expense,Emergency',
            'content' => 'required|string|max:5000',
        ]);

        $user = auth()->user();
        $child = CoparentChild::findOrFail($validated['child_id']);

        // Verify user has access to this child
        $hasAccess = $this->userHasAccessToChild($user, $child);
        abort_unless($hasAccess, 403, 'You do not have access to this child.');

        // Create conversation
        $conversation = CoparentConversation::create([
            'tenant_id' => $child->familyMember->tenant_id,
            'child_id' => $child->id,
            'subject' => $validated['subject'],
            'last_message_at' => now(),
        ]);

        // Create the first message
        $message = CoparentMessage::create([
            'tenant_id' => $child->familyMember->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'category' => $validated['category'],
            'content' => $validated['content'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Broadcast the message for real-time updates
        broadcast(new CoparentMessageSent($message))->toOthers();

        return redirect()->route('coparenting.messages.show', $conversation)
            ->with('success', 'Message sent successfully!');
    }

    /**
     * Display a conversation thread.
     */
    public function show(CoparentConversation $conversation): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Verify user is a participant
        abort_unless($conversation->isParticipant($user->id), 403);

        // Load messages with relationships
        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'edits', 'reads.user', 'reactions.user'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        foreach ($messages as $message) {
            if ($message->sender_id !== $user->id) {
                $message->markAsReadBy($user->id, request()->ip());
            }
        }

        // Get participants
        $participants = $conversation->getParticipants();

        // Get child info
        $child = $conversation->child;

        // Get templates for quick replies
        $templates = CoparentMessageTemplate::where(function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id)
              ->orWhere('is_system', true);
        })->get()->groupBy('category');

        $categories = CoparentMessage::CATEGORIES;

        return view('pages.coparenting.messages.show', compact(
            'conversation',
            'messages',
            'participants',
            'child',
            'templates',
            'categories'
        ));
    }

    /**
     * Store a new message in an existing conversation.
     */
    public function storeMessage(Request $request, CoparentConversation $conversation)
    {
        $user = auth()->user();

        // Verify user is a participant
        abort_unless($conversation->isParticipant($user->id), 403);

        $validated = $request->validate([
            'category' => 'required|string|in:General,Schedule,Medical,Expense,Emergency',
            'content' => 'required|string|max:5000',
        ]);

        // Create the message
        $message = CoparentMessage::create([
            'tenant_id' => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'category' => $validated['category'],
            'content' => $validated['content'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update conversation timestamp
        $conversation->touchLastMessage();

        // Broadcast the message for real-time updates
        broadcast(new CoparentMessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load(['sender', 'attachments']),
            ]);
        }

        return redirect()->route('coparenting.messages.show', $conversation)
            ->with('success', 'Message sent!');
    }

    /**
     * Show the edit form for a message.
     */
    public function edit(CoparentMessage $message): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Verify user can edit this message
        abort_unless($message->canBeEditedBy($user->id), 403, 'You cannot edit this message.');

        $categories = CoparentMessage::CATEGORIES;

        return view('pages.coparenting.messages.edit-message', compact('message', 'categories'));
    }

    /**
     * Update a message (with edit history).
     */
    public function update(Request $request, CoparentMessage $message)
    {
        $user = auth()->user();

        // Verify user can edit this message
        abort_unless($message->canBeEditedBy($user->id), 403, 'You cannot edit this message.');

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        // Update content (this method logs the edit automatically)
        $message->updateContent($validated['content'], $request->ip());

        return redirect()->route('coparenting.messages.show', $message->conversation_id)
            ->with('success', 'Message updated! Edit history has been recorded.');
    }

    /**
     * Show the edit history for a message.
     */
    public function showEditHistory(CoparentMessage $message): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Verify user is a participant in the conversation
        abort_unless($message->conversation->isParticipant($user->id), 403);

        $edits = $message->edits()->orderByDesc('created_at')->get();

        return view('pages.coparenting.messages.edit-history', compact('message', 'edits'));
    }

    /**
     * Upload an attachment to a message.
     */
    public function uploadAttachment(Request $request, CoparentConversation $conversation)
    {
        $user = auth()->user();

        // Verify user is a participant
        abort_unless($conversation->isParticipant($user->id), 403);

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt',
            'category' => 'required|string|in:General,Schedule,Medical,Expense,Emergency',
            'content' => 'nullable|string|max:5000',
        ]);

        $file = $request->file('file');

        // Validate mime type
        if (!CoparentMessageAttachment::isAllowedMimeType($file->getMimeType())) {
            return back()->withErrors(['file' => 'File type not allowed.']);
        }

        // Create a message for the attachment
        $message = CoparentMessage::create([
            'tenant_id' => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'category' => $validated['category'],
            'content' => $validated['content'] ?? 'Shared an attachment',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Store the file
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('coparent-messages/' . $conversation->id, $filename, 'public');

        // Create attachment record
        CoparentMessageAttachment::create([
            'tenant_id' => $conversation->tenant_id,
            'message_id' => $message->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        // Update conversation timestamp
        $conversation->touchLastMessage();

        // Broadcast the message for real-time updates
        broadcast(new CoparentMessageSent($message))->toOthers();

        return redirect()->route('coparenting.messages.show', $conversation)
            ->with('success', 'File shared successfully!');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(CoparentMessageAttachment $attachment)
    {
        $user = auth()->user();

        // Verify user is a participant in the conversation
        abort_unless($attachment->message->conversation->isParticipant($user->id), 403);

        return Storage::disk('public')->download($attachment->path, $attachment->original_filename);
    }

    /**
     * Export conversation to PDF.
     */
    public function exportPdf(CoparentConversation $conversation)
    {
        $user = auth()->user();

        // Verify user is a participant
        abort_unless($conversation->isParticipant($user->id), 403);

        // Load all messages with relationships
        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'edits', 'reads.user'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Get participants
        $participants = $conversation->getParticipants();

        // Get child info
        $child = $conversation->child;

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('pages.coparenting.messages.pdf.conversation', compact(
            'conversation',
            'messages',
            'participants',
            'child'
        ));

        $filename = 'conversation-' . $conversation->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get available message templates.
     */
    public function templates(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        $templates = CoparentMessageTemplate::where(function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id)
              ->orWhere('is_system', true);
        })->get()->groupBy('category');

        $categories = CoparentMessage::CATEGORIES;

        return view('pages.coparenting.messages.templates', compact('templates', 'categories'));
    }

    /**
     * Toggle a reaction on a message.
     */
    public function toggleReaction(Request $request, CoparentMessage $message)
    {
        $user = auth()->user();

        // Verify user is a participant in the conversation
        abort_unless($message->conversation->isParticipant($user->id), 403);

        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $existingReaction = CoparentMessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existingReaction) {
            // Remove the reaction
            $existingReaction->delete();
            $action = 'removed';
        } else {
            // Add the reaction
            CoparentMessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
            $action = 'added';
        }

        // Get all reactions for this message grouped by emoji
        $reactions = $message->reactions()
            ->selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->get()
            ->mapWithKeys(fn($r) => [$r->emoji => $r->count]);

        return response()->json([
            'success' => true,
            'action' => $action,
            'reactions' => $reactions,
        ]);
    }

    /**
     * Check if user has access to a coparent child.
     */
    private function userHasAccessToChild($user, CoparentChild $child): bool
    {
        // Check if user owns the tenant where the child belongs
        if ($child->familyMember->tenant_id === $user->tenant_id) {
            return true;
        }

        // Check if user is a co-parent with access to this child
        return Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->whereHas('coparentChildren', function ($q) use ($child) {
                $q->where('family_member_id', $child->family_member_id);
            })
            ->exists();
    }
}
