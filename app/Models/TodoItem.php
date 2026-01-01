<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TodoItem extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'todo_list_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'due_date',
        'due_time',
        'send_reminder',
        'reminder_type',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_days',
        'recurrence_end_date',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'recurrence_end_date' => 'date',
        'completed_at' => 'datetime',
        'send_reminder' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_days' => 'array',
    ];

    /**
     * Task categories.
     */
    public const CATEGORIES = [
        'home' => 'Home',
        'school' => 'School',
        'health' => 'Health',
        'finance' => 'Finance',
        'personal' => 'Personal',
        'work' => 'Work',
        'errands' => 'Errands',
        'family' => 'Family',
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
        'pending' => 'To Do',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    /**
     * Recurrence patterns.
     */
    public const RECURRENCE_PATTERNS = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    /**
     * Get the list that owns this item.
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
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
    public function completedBy(): BelongsTo
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

    /**
     * Get the category name.
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the priority name.
     */
    public function getPriorityNameAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Get the status name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the priority color class.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'text-slate-500',
            'medium' => 'text-blue-500',
            'high' => 'text-amber-500',
            'urgent' => 'text-rose-500',
            default => 'text-slate-500',
        };
    }

    /**
     * Get the category color class.
     */
    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'home' => 'bg-violet-100 text-violet-700',
            'school' => 'bg-blue-100 text-blue-700',
            'health' => 'bg-rose-100 text-rose-700',
            'finance' => 'bg-emerald-100 text-emerald-700',
            'personal' => 'bg-indigo-100 text-indigo-700',
            'work' => 'bg-amber-100 text-amber-700',
            'errands' => 'bg-orange-100 text-orange-700',
            'family' => 'bg-pink-100 text-pink-700',
            default => 'bg-slate-100 text-slate-700',
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
     * Mark task as complete.
     */
    public function markComplete(int $userId): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);
    }

    /**
     * Mark task as incomplete.
     */
    public function markIncomplete(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }
}
