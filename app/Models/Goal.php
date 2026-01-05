<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'color',
        'icon',
        // New family-friendly fields
        'category',
        'assignment_type',
        'assigned_to',
        'goal_type',
        'habit_frequency',
        'milestone_target',
        'milestone_current',
        'milestone_unit',
        'is_kid_goal',
        'show_emoji_status',
        'star_rating',
        'rewards_enabled',
        'reward_type',
        'reward_custom',
        'reward_claimed',
        'check_in_frequency',
        'last_check_in',
        'next_check_in',
        'visible_to_kids',
        'kids_can_update',
        'template_id',
        // Legacy fields (keep for compatibility)
        'target_type',
        'target_value',
        'target_date',
        'current_progress',
        'status',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_progress' => 'decimal:2',
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'last_check_in' => 'datetime',
        'next_check_in' => 'datetime',
        'is_kid_goal' => 'boolean',
        'show_emoji_status' => 'boolean',
        'rewards_enabled' => 'boolean',
        'reward_claimed' => 'boolean',
        'visible_to_kids' => 'boolean',
        'kids_can_update' => 'boolean',
    ];

    // ==================== CATEGORIES ====================

    public const CATEGORIES = [
        'learning' => [
            'label' => 'Learning & Education',
            'emoji' => '🧠',
            'color' => 'blue',
        ],
        'health' => [
            'label' => 'Health & Habits',
            'emoji' => '💪',
            'color' => 'emerald',
        ],
        'money' => [
            'label' => 'Money & Responsibility',
            'emoji' => '💰',
            'color' => 'amber',
        ],
        'family' => [
            'label' => 'Family Life',
            'emoji' => '🏠',
            'color' => 'rose',
        ],
        'personal_growth' => [
            'label' => 'Personal Growth',
            'emoji' => '🌱',
            'color' => 'teal',
        ],
        'fun' => [
            'label' => 'Fun & Experiences',
            'emoji' => '🎉',
            'color' => 'violet',
        ],
    ];

    // ==================== ASSIGNMENT TYPES ====================

    public const ASSIGNMENT_TYPES = [
        'individual' => [
            'label' => 'Individual',
            'emoji' => '👤',
            'description' => 'For one person (kid or parent)',
        ],
        'family' => [
            'label' => 'Family Goal',
            'emoji' => '👨‍👩‍👧‍👦',
            'description' => 'For the whole family',
        ],
        'parents' => [
            'label' => 'Parents Only',
            'emoji' => '👫',
            'description' => 'Just the parents',
        ],
        'kids' => [
            'label' => 'All Kids',
            'emoji' => '👧👦',
            'description' => 'All children',
        ],
        'parent_kid' => [
            'label' => 'Parent + Kid',
            'emoji' => '👨‍👧',
            'description' => 'Parent-child duo',
        ],
        'shared' => [
            'label' => 'Shared',
            'emoji' => '🤝',
            'description' => 'Shared responsibility',
        ],
    ];

    // ==================== GOAL TYPES ====================

    public const GOAL_TYPES = [
        'one_time' => [
            'label' => 'One-time Goal',
            'emoji' => '✅',
            'description' => 'Complete once and done',
        ],
        'habit' => [
            'label' => 'Habit Goal',
            'emoji' => '🔁',
            'description' => 'Build a daily, weekly, or monthly habit',
        ],
        'milestone' => [
            'label' => 'Milestone Goal',
            'emoji' => '🎯',
            'description' => 'Track progress towards a target',
        ],
    ];

    // ==================== HABIT FREQUENCIES ====================

    public const HABIT_FREQUENCIES = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    // ==================== STATUSES (Simplified) ====================

    public const STATUSES = [
        'active' => 'Active',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        'skipped' => 'Skipped',
        'archived' => 'Archived',
    ];

    // ==================== REWARD TYPES ====================

    public const REWARD_TYPES = [
        'family_treat' => [
            'label' => 'Family Treat',
            'emoji' => '🎉',
        ],
        'sticker' => [
            'label' => 'Sticker / Badge',
            'emoji' => '⭐',
        ],
        'parent_message' => [
            'label' => 'Parent Message',
            'emoji' => '💬',
        ],
        'custom' => [
            'label' => 'Custom Reward',
            'emoji' => '🎁',
        ],
    ];

    // ==================== CHECK-IN FREQUENCIES ====================

    public const CHECK_IN_FREQUENCIES = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    // ==================== LEGACY CONSTANTS (keep for compatibility) ====================

    public const TARGET_TYPES = [
        'none' => 'No Target',
        'count' => 'Count (Occurrences)',
        'amount' => 'Amount ($)',
        'date' => 'Complete by Date',
    ];

    public const COLORS = [
        'violet' => 'Violet',
        'indigo' => 'Indigo',
        'blue' => 'Blue',
        'sky' => 'Sky',
        'teal' => 'Teal',
        'emerald' => 'Emerald',
        'amber' => 'Amber',
        'orange' => 'Orange',
        'rose' => 'Rose',
        'pink' => 'Pink',
    ];

    public const ICONS = [
        'target' => 'Target',
        'star' => 'Star',
        'heart' => 'Heart',
        'flag' => 'Flag',
        'trophy' => 'Trophy',
        'rocket' => 'Rocket',
        'sun' => 'Sun',
        'home' => 'Home',
        'currency-dollar' => 'Money',
        'academic-cap' => 'Education',
        'heart-pulse' => 'Health',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get all tasks linked to this goal.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TodoItem::class);
    }

    /**
     * Get the user who created this goal.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assigned family member.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'assigned_to');
    }

    /**
     * Get the template this goal was created from.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(GoalTemplate::class, 'template_id');
    }

    /**
     * Get check-ins for this goal.
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(GoalCheckIn::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get category details.
     */
    public function getCategoryDetailsAttribute(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['personal_growth'];
    }

    /**
     * Get category emoji.
     */
    public function getCategoryEmojiAttribute(): string
    {
        return $this->category_details['emoji'] ?? '🎯';
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category_details['label'] ?? 'Personal Growth';
    }

    /**
     * Get category color.
     */
    public function getCategoryColorAttribute(): string
    {
        return $this->category_details['color'] ?? 'violet';
    }

    /**
     * Get goal type details.
     */
    public function getGoalTypeDetailsAttribute(): array
    {
        return self::GOAL_TYPES[$this->goal_type] ?? self::GOAL_TYPES['one_time'];
    }

    /**
     * Get goal type emoji.
     */
    public function getGoalTypeEmojiAttribute(): string
    {
        return $this->goal_type_details['emoji'] ?? '✅';
    }

    /**
     * Get assignment type details.
     */
    public function getAssignmentTypeDetailsAttribute(): array
    {
        return self::ASSIGNMENT_TYPES[$this->assignment_type] ?? self::ASSIGNMENT_TYPES['individual'];
    }

    /**
     * Get reward details.
     */
    public function getRewardDetailsAttribute(): ?array
    {
        if (!$this->rewards_enabled || !$this->reward_type) {
            return null;
        }
        return self::REWARD_TYPES[$this->reward_type] ?? null;
    }

    /**
     * Get the progress percentage for milestone goals.
     */
    public function getMilestoneProgressAttribute(): float
    {
        if ($this->goal_type !== 'milestone' || !$this->milestone_target || $this->milestone_target == 0) {
            return 0;
        }
        return min(100, ($this->milestone_current / $this->milestone_target) * 100);
    }

    /**
     * Get star display for kids (1-3 stars based on progress).
     */
    public function getStarDisplayAttribute(): string
    {
        if ($this->star_rating) {
            return str_repeat('⭐', $this->star_rating);
        }

        // Auto-calculate based on milestone progress
        if ($this->goal_type === 'milestone') {
            $progress = $this->milestone_progress;
            if ($progress >= 100) return '⭐⭐⭐';
            if ($progress >= 50) return '⭐⭐';
            if ($progress >= 25) return '⭐';
        }

        return '';
    }

    /**
     * Get status emoji for kid-friendly display.
     */
    public function getStatusEmojiAttribute(): string
    {
        return match ($this->status) {
            'done' => '🎉',
            'in_progress' => '💪',
            'skipped' => '⏭️',
            'archived' => '📦',
            default => '🎯',
        };
    }

    /**
     * Check if goal needs check-in.
     */
    public function getNeedsCheckInAttribute(): bool
    {
        if (!$this->check_in_frequency || $this->status === 'done' || $this->status === 'archived') {
            return false;
        }

        if (!$this->next_check_in) {
            return true;
        }

        return now()->gte($this->next_check_in);
    }

    /**
     * Get friendly check-in prompt.
     */
    public function getCheckInPromptAttribute(): string
    {
        $prompts = [
            "How did this go today?",
            "Want to mark progress?",
            "Ready to update this goal?",
        ];

        return $prompts[array_rand($prompts)];
    }

    /**
     * Check if the goal is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'done' || $this->status === 'completed';
    }

    // ==================== LEGACY ACCESSORS (keep for compatibility) ====================

    public function getProgressPercentageAttribute(): float
    {
        if ($this->goal_type === 'milestone') {
            return $this->milestone_progress;
        }

        if ($this->target_type === 'none' || !$this->target_value || $this->target_value == 0) {
            return 0;
        }

        return min(100, ($this->current_progress / $this->target_value) * 100);
    }

    public function getColorClassAttribute(): string
    {
        $color = $this->category_color ?? $this->color ?? 'violet';
        return "bg-{$color}-500";
    }

    public function getColorLightClassAttribute(): string
    {
        $color = $this->category_color ?? $this->color ?? 'violet';
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function getIconClassAttribute(): string
    {
        return 'icon-[tabler--' . ($this->icon ?? 'target') . ']';
    }

    public function getTargetDisplayAttribute(): string
    {
        if ($this->goal_type === 'milestone') {
            return "{$this->milestone_current} / {$this->milestone_target} " . ($this->milestone_unit ?? 'items');
        }

        return match ($this->target_type) {
            'count' => number_format($this->target_value ?? 0) . ' occurrences',
            'amount' => '$' . number_format($this->target_value ?? 0, 2),
            'date' => $this->target_date ? 'by ' . $this->target_date->format('M j, Y') : '',
            default => 'No target',
        };
    }

    public function getProgressDisplayAttribute(): string
    {
        if ($this->goal_type === 'milestone') {
            return "{$this->milestone_current} / {$this->milestone_target}";
        }

        return match ($this->target_type) {
            'count' => number_format($this->current_progress ?? 0) . ' / ' . number_format($this->target_value ?? 0),
            'amount' => '$' . number_format($this->current_progress ?? 0, 2) . ' / $' . number_format($this->target_value ?? 0, 2),
            'date' => $this->is_completed ? 'Completed' : 'In Progress',
            default => '',
        };
    }

    public function getActiveTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', '!=', 'completed')->count();
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }

    // ==================== METHODS ====================

    /**
     * Mark goal as done.
     */
    public function markDone(): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }

    /**
     * Skip this goal (no guilt!).
     */
    public function skip(): void
    {
        $this->update(['status' => 'skipped']);
    }

    /**
     * Archive the goal.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Reactivate the goal.
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => 'active',
            'completed_at' => null,
        ]);
    }

    /**
     * Add milestone progress.
     */
    public function addProgress(int $amount = 1): void
    {
        $this->increment('milestone_current', $amount);

        if ($this->milestone_current >= $this->milestone_target) {
            $this->markDone();
        }
    }

    /**
     * Record a check-in.
     */
    public function recordCheckIn(string $status, ?int $progressAdded = null, ?string $note = null): GoalCheckIn
    {
        $checkIn = $this->checkIns()->create([
            'tenant_id' => $this->tenant_id,
            'checked_in_by' => auth()->id(),
            'status' => $status,
            'note' => $note,
            'progress_added' => $progressAdded,
            'habit_completed' => $status === 'done',
            'check_in_date' => today(),
        ]);

        // Update goal's last check-in
        $this->update([
            'last_check_in' => now(),
            'next_check_in' => $this->calculateNextCheckIn(),
        ]);

        // Add progress if applicable
        if ($progressAdded && $this->goal_type === 'milestone') {
            $this->addProgress($progressAdded);
        }

        // Mark done if it was a one-time goal
        if ($this->goal_type === 'one_time' && $status === 'done') {
            $this->markDone();
        }

        return $checkIn;
    }

    /**
     * Calculate next check-in date.
     */
    public function calculateNextCheckIn(): ?Carbon
    {
        if (!$this->check_in_frequency) {
            return null;
        }

        return match ($this->check_in_frequency) {
            'daily' => now()->addDay()->startOfDay()->addHours(9),
            'weekly' => now()->addWeek()->startOfWeek()->addHours(9),
            'monthly' => now()->addMonth()->startOfMonth()->addHours(9),
            default => null,
        };
    }

    /**
     * Claim the reward.
     */
    public function claimReward(): bool
    {
        if (!$this->rewards_enabled || $this->reward_claimed || !$this->is_completed) {
            return false;
        }

        $this->update(['reward_claimed' => true]);
        return true;
    }

    /**
     * Legacy methods for compatibility.
     */
    public function incrementProgress(float $amount = 1): void
    {
        $this->addProgress((int) $amount);
    }

    public function decrementProgress(float $amount = 1): void
    {
        $newProgress = max(0, $this->milestone_current - (int) $amount);
        $this->update(['milestone_current' => $newProgress]);

        if ($this->status === 'done' && $newProgress < $this->milestone_target) {
            $this->reactivate();
        }
    }

    public function checkCompletion(): void
    {
        if ($this->goal_type === 'milestone' && $this->milestone_current >= $this->milestone_target) {
            $this->markDone();
        }
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active goals.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'in_progress']);
    }

    /**
     * Scope for family goals.
     */
    public function scopeFamily($query)
    {
        return $query->where('assignment_type', 'family');
    }

    /**
     * Scope for individual goals.
     */
    public function scopeIndividual($query)
    {
        return $query->where('assignment_type', 'individual');
    }

    /**
     * Scope for kid goals.
     */
    public function scopeKidGoals($query)
    {
        return $query->where('is_kid_goal', true);
    }

    /**
     * Scope for goals visible to kids.
     */
    public function scopeVisibleToKids($query)
    {
        return $query->where('visible_to_kids', true);
    }

    /**
     * Scope for goals needing check-in.
     */
    public function scopeNeedsCheckIn($query)
    {
        return $query->whereNotNull('check_in_frequency')
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($q) {
                $q->whereNull('next_check_in')
                    ->orWhere('next_check_in', '<=', now());
            });
    }

    /**
     * Scope for recently completed goals.
     */
    public function scopeRecentlyCompleted($query, int $days = 7)
    {
        return $query->where('status', 'done')
            ->where('completed_at', '>=', now()->subDays($days));
    }
}
