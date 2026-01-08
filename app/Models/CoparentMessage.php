<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoparentMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'coparent_messages';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'sender_id',
        'category',
        'content',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'content' => 'encrypted',
    ];

    // Message categories (same as templates)
    public const CATEGORY_GENERAL = 'General';
    public const CATEGORY_SCHEDULE = 'Schedule';
    public const CATEGORY_MEDICAL = 'Medical';
    public const CATEGORY_EXPENSE = 'Expense';
    public const CATEGORY_EMERGENCY = 'Emergency';

    public const CATEGORIES = [
        self::CATEGORY_GENERAL => ['label' => 'General', 'icon' => '💬', 'color' => '#3b82f6'],
        self::CATEGORY_SCHEDULE => ['label' => 'Schedule', 'icon' => '📅', 'color' => '#8b5cf6'],
        self::CATEGORY_MEDICAL => ['label' => 'Medical', 'icon' => '🏥', 'color' => '#ef4444'],
        self::CATEGORY_EXPENSE => ['label' => 'Expense', 'icon' => '💰', 'color' => '#22c55e'],
        self::CATEGORY_EMERGENCY => ['label' => 'Emergency', 'icon' => '🚨', 'color' => '#f97316'],
    ];

    // ==================== RELATIONSHIPS ====================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CoparentConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CoparentMessageAttachment::class, 'message_id');
    }

    public function edits(): HasMany
    {
        return $this->hasMany(CoparentMessageEdit::class, 'message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(CoparentMessageRead::class, 'message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CoparentMessageReaction::class, 'message_id');
    }

    // ==================== SCOPES ====================

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ==================== ACCESSORS ====================

    public function getCategoryInfoAttribute(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES[self::CATEGORY_GENERAL];
    }

    public function getCategoryIconAttribute(): string
    {
        return $this->category_info['icon'];
    }

    public function getCategoryColorAttribute(): string
    {
        return $this->category_info['color'];
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category_info['label'];
    }

    // ==================== METHODS ====================

    /**
     * Check if the message has been read by a specific user.
     */
    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Mark the message as read by a user.
     */
    public function markAsReadBy(int $userId, ?string $ipAddress = null): void
    {
        if (!$this->isReadBy($userId)) {
            $this->reads()->create([
                'user_id' => $userId,
                'read_at' => now(),
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Check if the message was edited.
     */
    public function wasEdited(): bool
    {
        return $this->edits()->exists();
    }

    /**
     * Get edit count.
     */
    public function getEditCountAttribute(): int
    {
        return $this->edits()->count();
    }

    /**
     * Get the timestamp when message was last edited.
     */
    public function getLastEditedAtAttribute()
    {
        return $this->edits()->orderByDesc('created_at')->first()?->created_at;
    }

    /**
     * Check if user can edit this message.
     */
    public function canBeEditedBy(int $userId): bool
    {
        // Only sender can edit their own messages
        if ($this->sender_id !== $userId) {
            return false;
        }

        // Cannot edit if message has been read by anyone other than the sender
        if ($this->hasBeenReadByOthers()) {
            return false;
        }

        return true;
    }

    /**
     * Check if message has been read by anyone other than the sender.
     */
    public function hasBeenReadByOthers(): bool
    {
        return $this->reads()->where('user_id', '!=', $this->sender_id)->exists();
    }

    /**
     * Update message content and log the edit.
     */
    public function updateContent(string $newContent, ?string $ipAddress = null): void
    {
        // Log the edit
        $this->edits()->create([
            'tenant_id' => $this->tenant_id,
            'previous_content' => $this->content,
            'new_content' => $newContent,
            'ip_address' => $ipAddress,
        ]);

        // Update the content
        $this->update(['content' => $newContent]);
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M j, Y \a\t g:i A');
    }
}
