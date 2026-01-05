<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskOccurrence extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'todo_item_id',
        'occurrence_number',
        'scheduled_date',
        'scheduled_time',
        'status',
        'completed_at',
        'completed_by',
        'snoozed_until',
        'skipped_reason',
        'assigned_to',
        'notes',
        'proof_path',
        'proof_type',
        'proof_submitted_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'proof_submitted_at' => 'datetime',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'completed' => 'Completed',
        'skipped' => 'Skipped',
        'snoozed' => 'Snoozed',
        'overdue' => 'Overdue',
    ];

    /**
     * Get the parent task.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TodoItem::class, 'todo_item_id');
    }

    /**
     * Get the assigned family member.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'assigned_to');
    }

    /**
     * Get the user who completed this occurrence.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Check if the occurrence is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        return $this->scheduled_date->isPast();
    }

    /**
     * Check if the occurrence is due today.
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->scheduled_date->isToday();
    }

    /**
     * Check if the occurrence is snoozed and still in snooze period.
     */
    public function getIsSnoozedAttribute(): bool
    {
        if ($this->status !== 'snoozed') {
            return false;
        }

        return $this->snoozed_until && $this->snoozed_until->isFuture();
    }

    /**
     * Get the status display name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'badge-primary',
            'completed' => 'badge-success',
            'skipped' => 'badge-ghost',
            'snoozed' => 'badge-warning',
            'overdue' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Mark this occurrence as complete.
     */
    public function markComplete(int $userId): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);

        // Update goal progress if applicable
        $task = $this->task;
        if ($task->goal_id && $task->count_toward_goal) {
            $task->goal->incrementProgress();
        }

        // Generate next occurrence if on_complete mode
        if ($task->is_recurring && $task->generate_mode === 'on_complete') {
            app(\App\Services\RecurringTaskService::class)->generateNextOccurrence($task);
        }
    }

    /**
     * Mark this occurrence as incomplete (reopen).
     */
    public function markIncomplete(): void
    {
        // Decrement goal progress if was counted
        if ($this->status === 'completed') {
            $task = $this->task;
            if ($task->goal_id && $task->count_toward_goal) {
                $task->goal->decrementProgress();
            }
        }

        $this->update([
            'status' => 'open',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    /**
     * Snooze this occurrence until a specific date/time.
     */
    public function snooze(Carbon $until): void
    {
        $this->update([
            'status' => 'snoozed',
            'snoozed_until' => $until,
        ]);
    }

    /**
     * Skip this occurrence.
     */
    public function skip(?string $reason = null): void
    {
        $this->update([
            'status' => 'skipped',
            'skipped_reason' => $reason,
        ]);

        // Generate next occurrence if on_complete mode
        $task = $this->task;
        if ($task->is_recurring && $task->generate_mode === 'on_complete') {
            app(\App\Services\RecurringTaskService::class)->generateNextOccurrence($task);
        }
    }

    /**
     * Wake up from snooze (revert to open).
     */
    public function wakeFromSnooze(): void
    {
        if ($this->status === 'snoozed') {
            $this->update([
                'status' => 'open',
                'snoozed_until' => null,
            ]);
        }
    }

    /**
     * Scope for open occurrences.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for upcoming occurrences.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now()->startOfDay())
            ->whereIn('status', ['open', 'snoozed'])
            ->orderBy('scheduled_date');
    }

    /**
     * Scope for overdue occurrences.
     */
    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now()->startOfDay())
            ->where('status', 'open');
    }

    /**
     * Scope for occurrences due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('scheduled_date', now()->toDateString())
            ->where('status', 'open');
    }

    /**
     * Scope for completed occurrences.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
            ->orderBy('completed_at', 'desc');
    }
}
