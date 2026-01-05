<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'body',
        'entry_datetime',
        'type',
        'mood',
        'status',
        'visibility',
        'shared_with_user_ids',
        'linked_entities',
        'is_pinned',
        'pin_order',
    ];

    protected $casts = [
        'entry_datetime' => 'datetime',
        'shared_with_user_ids' => 'array',
        'linked_entities' => 'array',
        'is_pinned' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    public const TYPES = [
        'journal' => ['label' => 'Journal Entry', 'icon' => 'tabler--notebook', 'color' => 'primary'],
        'memory' => ['label' => 'Memory', 'icon' => 'tabler--heart', 'color' => 'pink'],
        'note' => ['label' => 'Quick Note', 'icon' => 'tabler--note', 'color' => 'amber'],
        'milestone' => ['label' => 'Milestone', 'icon' => 'tabler--trophy', 'color' => 'success'],
    ];

    public const MOODS = [
        'happy' => ['label' => 'Happy', 'emoji' => '😀'],
        'neutral' => ['label' => 'Neutral', 'emoji' => '😐'],
        'sad' => ['label' => 'Sad', 'emoji' => '😟'],
        'angry' => ['label' => 'Angry', 'emoji' => '😡'],
        'tired' => ['label' => 'Tired', 'emoji' => '😴'],
    ];

    public const STATUSES = [
        'draft' => ['label' => 'Draft', 'color' => 'warning'],
        'published' => ['label' => 'Published', 'color' => 'success'],
    ];

    public const VISIBILITY = [
        'private' => ['label' => 'Only Me', 'icon' => 'tabler--lock'],
        'family' => ['label' => 'Family Circle', 'icon' => 'tabler--users'],
        'specific' => ['label' => 'Specific People', 'icon' => 'tabler--user-check'],
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(JournalTag::class, 'journal_entry_tag')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(JournalAttachment::class)->orderBy('sort_order');
    }

    // ==================== ACCESSORS ====================

    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['journal'];
    }

    public function getMoodInfoAttribute(): ?array
    {
        return $this->mood ? self::MOODS[$this->mood] : null;
    }

    public function getMoodEmojiAttribute(): ?string
    {
        return $this->mood ? (self::MOODS[$this->mood]['emoji'] ?? null) : null;
    }

    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['draft'];
    }

    public function getVisibilityInfoAttribute(): array
    {
        return self::VISIBILITY[$this->visibility] ?? self::VISIBILITY['private'];
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIsPrivateAttribute(): bool
    {
        return $this->visibility === 'private';
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->body), 150);
    }

    public function getPhotoCountAttribute(): int
    {
        return $this->attachments()->where('type', 'photo')->count();
    }

    public function getHasAttachmentsAttribute(): bool
    {
        return $this->attachments()->exists();
    }

    // ==================== SCOPES ====================

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true)->orderBy('pin_order');
    }

    public function scopeNotPinned($query)
    {
        return $query->where('is_pinned', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithMood($query, string $mood)
    {
        return $query->where('mood', $mood);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id) // Owner can see all
                ->orWhere('visibility', 'family') // Family visibility
                ->orWhere(function ($q2) use ($user) {
                    // Specific users
                    $q2->where('visibility', 'specific')
                        ->whereJsonContains('shared_with_user_ids', $user->id);
                });
        });
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('entry_datetime', '>=', now()->subDays($days));
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('entry_datetime', [$start, $end]);
    }

    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', fn($q) => $q->where('journal_tags.id', $tagId));
    }

    // ==================== METHODS ====================

    public function publish(): void
    {
        $this->update(['status' => 'published']);
    }

    public function saveDraft(): void
    {
        $this->update(['status' => 'draft']);
    }

    public function pin(int $order = 1): void
    {
        // Ensure max 3 pinned entries
        $pinnedCount = static::where('tenant_id', $this->tenant_id)
            ->where('user_id', $this->user_id)
            ->where('is_pinned', true)
            ->where('id', '!=', $this->id)
            ->count();

        if ($pinnedCount >= 3) {
            // Unpin the oldest pinned entry
            static::where('tenant_id', $this->tenant_id)
                ->where('user_id', $this->user_id)
                ->where('is_pinned', true)
                ->orderBy('pin_order', 'desc')
                ->first()
                ?->unpin();
        }

        $this->update(['is_pinned' => true, 'pin_order' => $order]);
    }

    public function unpin(): void
    {
        $this->update(['is_pinned' => false, 'pin_order' => null]);
    }

    public function togglePin(): void
    {
        $this->is_pinned ? $this->unpin() : $this->pin();
    }

    public function syncTags(array $tagIds): void
    {
        $oldTags = $this->tags()->pluck('journal_tags.id')->toArray();
        $this->tags()->sync($tagIds);

        // Update usage counts
        $added = array_diff($tagIds, $oldTags);
        $removed = array_diff($oldTags, $tagIds);

        JournalTag::whereIn('id', $added)->increment('usage_count');
        JournalTag::whereIn('id', $removed)->decrement('usage_count');
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->visibility === 'private') {
            return false;
        }

        if ($this->visibility === 'family') {
            return $this->tenant_id === $user->tenant_id;
        }

        if ($this->visibility === 'specific') {
            return in_array($user->id, $this->shared_with_user_ids ?? []);
        }

        return false;
    }
}
