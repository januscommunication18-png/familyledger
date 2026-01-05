<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\JournalAttachment;
use App\Models\JournalEntry;
use App\Models\JournalTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    /**
     * Display journal entries.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get pinned entries (max 3)
        $pinnedEntries = JournalEntry::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->published()
            ->pinned()
            ->with(['tags', 'attachments', 'user'])
            ->limit(3)
            ->get();

        // Build query for other entries
        $query = JournalEntry::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->notPinned()
            ->with(['tags', 'attachments', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by mood
        if ($request->filled('mood')) {
            $query->withMood($request->mood);
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->inDateRange($request->from, $request->to);
        }

        $entries = $query->orderBy('entry_datetime', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get tags for filter
        $tags = JournalTag::where('tenant_id', $tenantId)
            ->popular(20)
            ->get();

        // Stats
        $stats = [
            'total' => JournalEntry::where('tenant_id', $tenantId)->where('user_id', $user->id)->published()->count(),
            'drafts' => JournalEntry::where('tenant_id', $tenantId)->where('user_id', $user->id)->draft()->count(),
            'this_month' => JournalEntry::where('tenant_id', $tenantId)->where('user_id', $user->id)
                ->published()
                ->whereMonth('entry_datetime', now()->month)
                ->whereYear('entry_datetime', now()->year)
                ->count(),
        ];

        return view('pages.journal.index', [
            'pinnedEntries' => $pinnedEntries,
            'entries' => $entries,
            'tags' => $tags,
            'types' => JournalEntry::TYPES,
            'moods' => JournalEntry::MOODS,
            'filters' => $request->only(['status', 'type', 'mood', 'tag', 'search', 'from', 'to']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new entry.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $tags = JournalTag::where('tenant_id', $user->tenant_id)
            ->popular(30)
            ->get();

        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name')
            ->get();

        return view('pages.journal.form', [
            'entry' => null,
            'tags' => $tags,
            'types' => JournalEntry::TYPES,
            'moods' => JournalEntry::MOODS,
            'visibility' => JournalEntry::VISIBILITY,
            'familyMembers' => $familyMembers,
            'defaultType' => $request->get('type', 'journal'),
        ]);
    }

    /**
     * Store a newly created entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string|max:50000',
            'entry_datetime' => 'nullable|date',
            'type' => 'required|string|in:' . implode(',', array_keys(JournalEntry::TYPES)),
            'mood' => 'nullable|string|in:' . implode(',', array_keys(JournalEntry::MOODS)),
            'status' => 'required|string|in:draft,published',
            'visibility' => 'required|string|in:' . implode(',', array_keys(JournalEntry::VISIBILITY)),
            'shared_with' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'new_tags' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:10240', // 10MB per photo
            'file' => 'nullable|file|max:25600', // 25MB
        ]);

        $user = Auth::user();

        $entry = JournalEntry::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'title' => $request->title,
            'body' => $request->body,
            'entry_datetime' => $request->entry_datetime ?? now(),
            'type' => $request->type,
            'mood' => $request->mood,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'shared_with_user_ids' => $request->visibility === 'specific' ? $request->shared_with : null,
        ]);

        // Handle tags
        $this->syncTags($entry, $request->tags ?? [], $request->new_tags, $user->tenant_id);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($entry, $request->file('photos'));
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $this->handleFileUpload($entry, $request->file('file'));
        }

        $message = $entry->is_draft ? 'Entry saved as draft.' : 'Entry published!';

        return redirect()->route('journal.show', $entry)
            ->with('success', $message);
    }

    /**
     * Display the specified entry.
     */
    public function show(JournalEntry $journalEntry)
    {
        $user = Auth::user();

        if (!$journalEntry->canBeViewedBy($user)) {
            abort(403);
        }

        $journalEntry->load(['tags', 'attachments', 'user']);

        // Get adjacent entries for navigation
        $previousEntry = JournalEntry::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('entry_datetime', '<', $journalEntry->entry_datetime)
            ->orderBy('entry_datetime', 'desc')
            ->first();

        $nextEntry = JournalEntry::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('entry_datetime', '>', $journalEntry->entry_datetime)
            ->orderBy('entry_datetime', 'asc')
            ->first();

        return view('pages.journal.show', [
            'entry' => $journalEntry,
            'previousEntry' => $previousEntry,
            'nextEntry' => $nextEntry,
        ]);
    }

    /**
     * Show the form for editing the specified entry.
     */
    public function edit(JournalEntry $journalEntry)
    {
        $user = Auth::user();

        if ($journalEntry->user_id !== $user->id) {
            abort(403);
        }

        $journalEntry->load(['tags', 'attachments']);

        $tags = JournalTag::where('tenant_id', $user->tenant_id)
            ->popular(30)
            ->get();

        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name')
            ->get();

        return view('pages.journal.form', [
            'entry' => $journalEntry,
            'tags' => $tags,
            'types' => JournalEntry::TYPES,
            'moods' => JournalEntry::MOODS,
            'visibility' => JournalEntry::VISIBILITY,
            'familyMembers' => $familyMembers,
            'defaultType' => $journalEntry->type,
        ]);
    }

    /**
     * Update the specified entry.
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        $user = Auth::user();

        if ($journalEntry->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string|max:50000',
            'entry_datetime' => 'nullable|date',
            'type' => 'required|string|in:' . implode(',', array_keys(JournalEntry::TYPES)),
            'mood' => 'nullable|string|in:' . implode(',', array_keys(JournalEntry::MOODS)),
            'status' => 'required|string|in:draft,published',
            'visibility' => 'required|string|in:' . implode(',', array_keys(JournalEntry::VISIBILITY)),
            'shared_with' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'new_tags' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:10240',
            'file' => 'nullable|file|max:25600',
            'remove_attachments' => 'nullable|array',
        ]);

        $journalEntry->update([
            'title' => $request->title,
            'body' => $request->body,
            'entry_datetime' => $request->entry_datetime ?? $journalEntry->entry_datetime,
            'type' => $request->type,
            'mood' => $request->mood,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'shared_with_user_ids' => $request->visibility === 'specific' ? $request->shared_with : null,
        ]);

        // Handle tags
        $this->syncTags($journalEntry, $request->tags ?? [], $request->new_tags, $user->tenant_id);

        // Remove attachments if requested
        if ($request->filled('remove_attachments')) {
            JournalAttachment::whereIn('id', $request->remove_attachments)
                ->where('journal_entry_id', $journalEntry->id)
                ->get()
                ->each->delete();
        }

        // Handle new photo uploads
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($journalEntry, $request->file('photos'));
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            // Remove existing file first
            $journalEntry->attachments()->files()->get()->each->delete();
            $this->handleFileUpload($journalEntry, $request->file('file'));
        }

        return redirect()->route('journal.show', $journalEntry)
            ->with('success', 'Entry updated!');
    }

    /**
     * Remove the specified entry.
     */
    public function destroy(JournalEntry $journalEntry)
    {
        $user = Auth::user();

        if ($journalEntry->user_id !== $user->id) {
            abort(403);
        }

        // Delete attachments
        $journalEntry->attachments->each->delete();

        // Update tag usage counts
        foreach ($journalEntry->tags as $tag) {
            $tag->decrementUsage();
        }

        $journalEntry->delete();

        return redirect()->route('journal.index')
            ->with('success', 'Entry deleted.');
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(JournalEntry $journalEntry)
    {
        $user = Auth::user();

        if ($journalEntry->user_id !== $user->id) {
            abort(403);
        }

        $journalEntry->togglePin();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_pinned' => $journalEntry->is_pinned,
            ]);
        }

        $message = $journalEntry->is_pinned ? 'Entry pinned!' : 'Entry unpinned.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete an attachment.
     */
    public function destroyAttachment(JournalEntry $journalEntry, JournalAttachment $attachment)
    {
        $user = Auth::user();

        if ($journalEntry->user_id !== $user->id || $attachment->journal_entry_id !== $journalEntry->id) {
            abort(403);
        }

        $attachment->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Attachment removed.');
    }

    /**
     * Get tags for autocomplete.
     */
    public function searchTags(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q', '');

        $tags = JournalTag::where('tenant_id', $user->tenant_id)
            ->when($search, fn($q) => $q->search($search))
            ->popular(10)
            ->get(['id', 'name', 'color']);

        return response()->json($tags);
    }

    // ==================== PRIVATE METHODS ====================

    private function syncTags(JournalEntry $entry, array $existingTagIds, ?string $newTags, string $tenantId): void
    {
        $tagIds = array_map('intval', array_filter($existingTagIds));

        // Create new tags
        if ($newTags) {
            $newTagNames = array_map('trim', explode(',', $newTags));
            foreach ($newTagNames as $name) {
                if (!empty($name)) {
                    $tag = JournalTag::findOrCreateByName($name, $tenantId);
                    $tagIds[] = $tag->id;
                }
            }
        }

        $entry->syncTags(array_unique($tagIds));
    }

    private function handlePhotoUploads(JournalEntry $entry, array $files): void
    {
        $existingCount = $entry->attachments()->photos()->count();
        $maxNew = JournalAttachment::MAX_PHOTOS - $existingCount;

        $sortOrder = $entry->attachments()->max('sort_order') ?? 0;

        foreach (array_slice($files, 0, $maxNew) as $file) {
            $path = $file->store('journal/photos/' . $entry->id, 'do_spaces');

            $entry->attachments()->create([
                'tenant_id' => $entry->tenant_id,
                'type' => 'photo',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sort_order' => ++$sortOrder,
            ]);
        }
    }

    private function handleFileUpload(JournalEntry $entry, $file): void
    {
        $path = $file->store('journal/files/' . $entry->id, 'do_spaces');

        $entry->attachments()->create([
            'tenant_id' => $entry->tenant_id,
            'type' => 'file',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => 0,
        ]);
    }
}
