<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodoItem extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'todo_list_id',
        'goal_id',
        'count_toward_goal',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'due_date',
        'due_time',
        'timezone',
        'send_reminder',
        'reminder_type',
        'reminder_settings',
        'escalation_settings',
        'digest_mode',
        'digest_time',
        // Recurring fields
        'is_recurring',
        'recurrence_start_date',
        'recurrence_pattern',
        'recurrence_frequency',
        'recurrence_interval',
        'recurrence_days',
        'monthly_type',
        'monthly_day',
        'monthly_week',
        'monthly_weekday',
        'yearly_month',
        'yearly_day',
        'recurrence_end_type',
        'recurrence_end_date',
        'recurrence_max_occurrences',
        'skip_weekends',
        'generate_mode',
        'schedule_ahead_days',
        'missed_policy',
        'parent_task_id',
        'is_series_template',
        'rotation_type',
        'rotation_current_index',
        'completion_type',
        'proof_required',
        'proof_type',
        'series_status',
        // Completion fields
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'recurrence_start_date' => 'date',
        'recurrence_end_date' => 'date',
        'completed_at' => 'datetime',
        'send_reminder' => 'boolean',
        'is_recurring' => 'boolean',
        'is_series_template' => 'boolean',
        'count_toward_goal' => 'boolean',
        'skip_weekends' => 'boolean',
        'proof_required' => 'boolean',
        'digest_mode' => 'boolean',
        'recurrence_days' => 'array',
        'reminder_settings' => 'array',
        'escalation_settings' => 'array',
        'recurrence_interval' => 'integer',
        'rotation_current_index' => 'integer',
    ];

    /**
     * Task categories - Family focused.
     */
    public const CATEGORIES = [
        'home_chores' => 'Home Chores',
        'bills' => 'Bills & Payments',
        'health' => 'Health',
        'kids' => 'Kids',
        'car' => 'Car',
        'pet_care' => 'Pet Care',
        'family_rituals' => 'Family Rituals',
        'admin' => 'Admin',
    ];

    /**
     * Task priorities.
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    /**
     * Task statuses.
     */
    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'skipped' => 'Skipped',
        'snoozed' => 'Snoozed',
    ];

    /**
     * Recurrence frequencies.
     */
    public const RECURRENCE_FREQUENCIES = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'custom' => 'Custom',
    ];

    /**
     * Legacy recurrence patterns (for backwards compatibility).
     */
    public const RECURRENCE_PATTERNS = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    /**
     * Generate modes for recurring tasks.
     */
    public const GENERATE_MODES = [
        'on_complete' => 'When Completed',
        'schedule_ahead' => 'Schedule Ahead',
    ];

    /**
     * Missed task policies.
     */
    public const MISSED_POLICIES = [
        'carryover' => 'Carry Over',
        'skip' => 'Auto-Skip',
        'reschedule' => 'Reschedule',
    ];

    /**
     * Assignment rotation types.
     */
    public const ROTATION_TYPES = [
        'none' => 'No Rotation',
        'ordered' => 'Rotate in Order',
        'round_robin' => 'Round Robin',
    ];

    /**
     * Completion types for multiple assignees.
     */
    public const COMPLETION_TYPES = [
        'any_one' => 'Any One Can Complete',
        'everyone' => 'Everyone Must Complete',
    ];

    /**
     * Series statuses for recurring tasks.
     */
    public const SERIES_STATUSES = [
        'active' => 'Active',
        'paused' => 'Paused',
        'archived' => 'Archived',
    ];

    /**
     * Proof types for task completion.
     */
    public const PROOF_TYPES = [
        'photo' => 'Photo',
        'receipt' => 'Receipt',
        'signature' => 'Signature',
        'document' => 'Document',
    ];

    /**
     * Escalation targets.
     */
    public const ESCALATION_TARGETS = [
        'parents' => 'Parents/Guardians',
        'admins' => 'Family Admins',
        'specific_member' => 'Specific Member',
    ];

    /**
     * Reminder timing options.
     */
    public const REMINDER_TIMINGS = [
        'at_time' => 'At due time',
        '15_min' => '15 minutes before',
        '30_min' => '30 minutes before',
        '1_hour' => '1 hour before',
        '2_hours' => '2 hours before',
        '1_day' => '1 day before',
        '2_days' => '2 days before',
        '1_week' => '1 week before',
    ];

    /**
     * Days of the week.
     */
    public const WEEKDAYS = [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
        'sun' => 'Sunday',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the list that owns this item (optional).
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /**
     * Get the goal this task is linked to.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the parent task (for recurring instances).
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(TodoItem::class, 'parent_task_id');
    }

    /**
     * Get child occurrences (for recurring series).
     */
    public function childOccurrences(): HasMany
    {
        return $this->hasMany(TodoItem::class, 'parent_task_id');
    }

    /**
     * Get task occurrences.
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(TaskOccurrence::class, 'todo_item_id');
    }

    /**
     * Get the family member this is assigned to (legacy single assignment).
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'assigned_to');
    }

    /**
     * Get all family members this is assigned to.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'todo_item_assignees')
            ->withTimestamps();
    }

    /**
     * Get the user who created this item.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who completed this item.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the comments for this item.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TodoComment::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the category name.
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category ?? 'Other');
    }

    /**
     * Get the priority name.
     */
    public function getPriorityNameAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst($this->priority ?? 'Medium');
    }

    /**
     * Get the status name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status ?? 'Open');
    }

    /**
     * Get the priority color class.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'text-slate-500',
            'medium' => 'text-blue-500',
            'high' => 'text-amber-500',
            'urgent' => 'text-rose-500',
            default => 'text-slate-500',
        };
    }

    /**
     * Get the priority badge class.
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'badge-ghost',
            'medium' => 'badge-info',
            'high' => 'badge-warning',
            'urgent' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Get the category color class.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'home_chores' => 'bg-violet-100 text-violet-700',
            'bills' => 'bg-emerald-100 text-emerald-700',
            'health' => 'bg-rose-100 text-rose-700',
            'kids' => 'bg-blue-100 text-blue-700',
            'car' => 'bg-amber-100 text-amber-700',
            'pet_care' => 'bg-orange-100 text-orange-700',
            'family_rituals' => 'bg-pink-100 text-pink-700',
            'admin' => 'bg-slate-100 text-slate-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    /**
     * Get the category icon class.
     */
    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'home_chores' => 'icon-[tabler--home]',
            'bills' => 'icon-[tabler--currency-dollar]',
            'health' => 'icon-[tabler--heart-pulse]',
            'kids' => 'icon-[tabler--mood-kid]',
            'car' => 'icon-[tabler--car]',
            'pet_care' => 'icon-[tabler--paw]',
            'family_rituals' => 'icon-[tabler--users]',
            'admin' => 'icon-[tabler--file-text]',
            default => 'icon-[tabler--checkbox]',
        };
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'open' => 'badge-primary',
            'in_progress' => 'badge-warning',
            'completed' => 'badge-success',
            'skipped' => 'badge-ghost',
            'snoozed' => 'badge-info',
            default => 'badge-ghost',
        };
    }

    /**
     * Check if the task is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }

        return $this->due_date->isPast();
    }

    /**
     * Check if due today.
     */
    public function getIsDueTodayAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isToday();
    }

    /**
     * Check if series is paused.
     */
    public function getIsSeriesPausedAttribute(): bool
    {
        return $this->series_status === 'paused';
    }

    /**
     * Get the recurrence summary text.
     */
    public function getRecurrenceSummaryAttribute(): ?string
    {
        if (!$this->is_recurring) {
            return null;
        }

        $frequency = $this->recurrence_frequency ?? $this->recurrence_pattern ?? 'daily';
        $interval = $this->recurrence_interval ?? 1;

        if ($interval === 1) {
            $summary = ucfirst($frequency);
        } else {
            $unit = match ($frequency) {
                'daily' => 'days',
                'weekly' => 'weeks',
                'monthly' => 'months',
                'yearly' => 'years',
                default => $frequency,
            };
            $summary = "Every {$interval} {$unit}";
        }

        // Add day info for weekly
        if ($frequency === 'weekly' && !empty($this->recurrence_days)) {
            $days = array_map(fn($d) => substr(ucfirst($d), 0, 3), $this->recurrence_days);
            $summary .= ' (' . implode(', ', $days) . ')';
        }

        return $summary;
    }

    /**
     * Get the next occurrence date.
     */
    public function getNextOccurrenceDateAttribute(): ?Carbon
    {
        if (!$this->is_recurring) {
            return null;
        }

        $nextOccurrence = $this->occurrences()
            ->where('status', 'open')
            ->orderBy('scheduled_date')
            ->first();

        return $nextOccurrence?->scheduled_date;
    }

    /**
     * Get count of upcoming occurrences.
     */
    public function getUpcomingOccurrencesCountAttribute(): int
    {
        return $this->occurrences()
            ->where('scheduled_date', '>=', now()->startOfDay())
            ->whereIn('status', ['open', 'snoozed'])
            ->count();
    }

    /**
     * Get count of completed occurrences.
     */
    public function getCompletedOccurrencesCountAttribute(): int
    {
        return $this->occurrences()
            ->where('status', 'completed')
            ->count();
    }

    // ==================== METHODS ====================

    /**
     * Mark task as complete.
     */
    public function markComplete(int $userId): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);

        // Update goal progress if linked
        if ($this->goal_id && $this->count_toward_goal) {
            $this->goal->incrementProgress();
        }

        // Generate next occurrence if recurring and on_complete mode
        if ($this->is_recurring && $this->generate_mode === 'on_complete') {
            app(\App\Services\RecurringTaskService::class)->generateNextOccurrence($this);
        }
    }

    /**
     * Mark task as incomplete.
     */
    public function markIncomplete(): void
    {
        // Decrement goal progress if was counted
        if ($this->status === 'completed' && $this->goal_id && $this->count_toward_goal) {
            $this->goal->decrementProgress();
        }

        $this->update([
            'status' => 'open',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    /**
     * Pause the recurring series.
     */
    public function pauseSeries(): void
    {
        if ($this->is_recurring) {
            $this->update(['series_status' => 'paused']);
        }
    }

    /**
     * Resume the recurring series.
     */
    public function resumeSeries(): void
    {
        if ($this->is_recurring) {
            $this->update(['series_status' => 'active']);
        }
    }

    /**
     * Archive the recurring series.
     */
    public function archiveSeries(): void
    {
        if ($this->is_recurring) {
            $this->update(['series_status' => 'archived']);
        }
    }

    /**
     * Get the next assignee based on rotation.
     */
    public function getNextRotationAssignee(): ?FamilyMember
    {
        if ($this->rotation_type === 'none') {
            return null;
        }

        $assignees = $this->assignees;
        if ($assignees->isEmpty()) {
            return null;
        }

        $currentIndex = $this->rotation_current_index ?? 0;
        $nextIndex = ($currentIndex + 1) % $assignees->count();

        $this->update(['rotation_current_index' => $nextIndex]);

        return $assignees->get($nextIndex);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for tasks without a parent (standalone or series templates).
     */
    public function scopeStandalone($query)
    {
        return $query->whereNull('parent_task_id');
    }

    /**
     * Scope for recurring tasks.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope for active recurring series.
     */
    public function scopeActiveRecurring($query)
    {
        return $query->where('is_recurring', true)
            ->where(function ($q) {
                $q->whereNull('series_status')
                    ->orWhere('series_status', 'active');
            });
    }

    /**
     * Scope for open tasks.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'skipped']);
    }

    /**
     * Scope for tasks due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString())
            ->whereNotIn('status', ['completed', 'skipped']);
    }

    /**
     * Scope for tasks linked to a goal.
     */
    public function scopeLinkedToGoal($query, ?int $goalId = null)
    {
        if ($goalId) {
            return $query->where('goal_id', $goalId);
        }

        return $query->whereNotNull('goal_id');
    }

    /**
     * Scope for missed recurring tasks (has overdue occurrences).
     */
    public function scopeMissedRecurring($query)
    {
        return $query->where('is_recurring', true)
            ->whereHas('occurrences', function ($q) {
                $q->where('scheduled_date', '<', now()->startOfDay())
                    ->where('status', 'open');
            });
    }

    /**
     * Scope for tasks with occurrences due this week.
     */
    public function scopeUpcomingThisWeek($query)
    {
        return $query->where('is_recurring', true)
            ->whereHas('occurrences', function ($q) {
                $q->whereBetween('scheduled_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->whereIn('status', ['open', 'snoozed']);
            });
    }

    /**
     * Scope for tasks due this week (non-recurring).
     */
    public function scopeDueThisWeek($query)
    {
        return $query->whereNotNull('due_date')
            ->whereBetween('due_date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->whereNotIn('status', ['completed', 'skipped']);
    }

    /**
     * Scope for tasks requiring proof.
     */
    public function scopeRequiresProof($query)
    {
        return $query->where('proof_required', true);
    }
}
