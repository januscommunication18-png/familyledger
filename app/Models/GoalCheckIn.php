<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalCheckIn extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'goal_id',
        'checked_in_by',
        'status',
        'note',
        'progress_added',
        'habit_completed',
        'star_rating',
        'parent_message',
        'check_in_date',
    ];

    protected $casts = [
        'habit_completed' => 'boolean',
        'check_in_date' => 'date',
    ];

    // ==================== STATUSES ====================

    public const STATUSES = [
        'done' => [
            'label' => 'Done',
            'emoji' => '✅',
            'color' => 'success',
        ],
        'in_progress' => [
            'label' => 'In Progress',
            'emoji' => '💪',
            'color' => 'info',
        ],
        'skipped' => [
            'label' => 'Skipped',
            'emoji' => '⏭️',
            'color' => 'warning',
        ],
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the goal for this check-in.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the user who checked in.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get status details.
     */
    public function getStatusDetailsAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['done'];
    }

    /**
     * Get status emoji.
     */
    public function getStatusEmojiAttribute(): string
    {
        return $this->status_details['emoji'] ?? '✅';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_details['label'] ?? 'Done';
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status_details['color'] ?? 'success';
    }

    /**
     * Get star display.
     */
    public function getStarDisplayAttribute(): string
    {
        if (!$this->star_rating) {
            return '';
        }
        return str_repeat('⭐', $this->star_rating);
    }

    /**
     * Get check-in summary for display.
     */
    public function getSummaryAttribute(): string
    {
        $parts = [$this->status_emoji];

        if ($this->progress_added) {
            $parts[] = "+{$this->progress_added}";
        }

        if ($this->star_rating) {
            $parts[] = $this->star_display;
        }

        return implode(' ', $parts);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for check-ins on a specific date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('check_in_date', $date);
    }

    /**
     * Scope for check-ins in a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_in_date', [$startDate, $endDate]);
    }

    /**
     * Scope for check-ins this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('check_in_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope for completed check-ins.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope for check-ins by a specific user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('checked_in_by', $userId);
    }
}
