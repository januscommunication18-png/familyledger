<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\JournalEntry;
use App\Models\JournalTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Get all journal entries.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawEntries = JournalEntry::where('tenant_id', $tenant->id)
            ->with(['tags', 'attachments'])
            ->orderBy('entry_datetime', 'desc')
            ->take(50)
            ->get();

        // Transform entries to match mobile app format
        $entries = $rawEntries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'title' => $entry->title,
                'content' => $entry->body,
                'type' => $entry->type ?? 'journal',
                'mood' => $entry->mood,
                'date' => $entry->entry_datetime?->format('M d, Y'),
                'is_pinned' => $entry->is_pinned ?? false,
                'is_draft' => $entry->status === 'draft',
                'tags' => $entry->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
                'photos' => $entry->attachments->pluck('file_path')->toArray(),
                'created_at' => $entry->created_at?->toISOString(),
                'updated_at' => $entry->updated_at?->toISOString(),
            ];
        });

        $pinnedEntries = $entries->filter(fn($e) => $e['is_pinned'])->values();
        $tags = JournalTag::where('tenant_id', $tenant->id)->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name]);

        $drafts = $rawEntries->where('status', 'draft')->count();
        $thisMonth = $rawEntries->filter(fn($e) => $e->entry_datetime && $e->entry_datetime >= now()->startOfMonth())->count();

        return $this->success([
            'entries' => $entries,
            'pinned_entries' => $pinnedEntries,
            'tags' => $tags,
            'stats' => [
                'total' => $rawEntries->count(),
                'drafts' => $drafts,
                'this_month' => $thisMonth,
            ],
        ]);
    }

    /**
     * Get entries by type.
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $entries = JournalEntry::where('tenant_id', $tenant->id)
            ->where('entry_type', $type)
            ->with(['tags', 'attachments'])
            ->orderBy('entry_date', 'desc')
            ->get();

        return $this->success([
            'entries' => $entries,
            'total' => $entries->count(),
        ]);
    }

    /**
     * Get a single journal entry.
     */
    public function show(Request $request, JournalEntry $entry): JsonResponse
    {
        $user = $request->user();

        if ($entry->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $entry->load(['tags', 'attachments', 'user']);

        // Get mood info
        $moodEmoji = null;
        $moodLabel = null;
        if ($entry->mood && isset(JournalEntry::MOODS[$entry->mood])) {
            $moodEmoji = JournalEntry::MOODS[$entry->mood]['emoji'];
            $moodLabel = JournalEntry::MOODS[$entry->mood]['label'];
        }

        // Get type info
        $typeInfo = JournalEntry::TYPES[$entry->type] ?? JournalEntry::TYPES['journal'];

        return $this->success([
            'entry' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'content' => $entry->body,
                'type' => $entry->type,
                'type_label' => $typeInfo['label'],
                'mood' => $entry->mood,
                'mood_emoji' => $moodEmoji,
                'mood_label' => $moodLabel,
                'date' => $entry->entry_datetime?->format('M d, Y'),
                'time' => $entry->entry_datetime?->format('g:i A'),
                'datetime' => $entry->entry_datetime?->toISOString(),
                'formatted_date' => $entry->entry_datetime?->format('l, F j, Y'),
                'is_pinned' => $entry->is_pinned ?? false,
                'is_draft' => $entry->status === 'draft',
                'status' => $entry->status,
                'visibility' => $entry->visibility,
                'visibility_label' => JournalEntry::VISIBILITY[$entry->visibility]['label'] ?? 'Private',
                'tags' => $entry->tags->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                ]),
                'attachments' => $entry->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'file_path' => $a->file_path,
                    'file_name' => $a->file_name,
                    'url' => $a->file_path ? url('storage/' . $a->file_path) : null,
                ]),
                'author' => [
                    'id' => $entry->user->id,
                    'name' => $entry->user->name,
                    'avatar' => $entry->user->avatar,
                ],
                'created_at' => $entry->created_at?->toISOString(),
                'updated_at' => $entry->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Get all journal tags.
     */
    public function tags(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $tags = JournalTag::where('tenant_id', $tenant->id)
            ->withCount('entries')
            ->get();

        return $this->success([
            'tags' => $tags,
        ]);
    }
}
