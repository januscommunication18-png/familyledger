<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CoparentingActivity extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'is_all_day',
        'is_recurring',
        'recurrence_frequency',
        'recurrence_end_type',
        'recurrence_end_after',
        'recurrence_end_on',
        'reminder_type',
        'reminder_minutes',
        'color',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_end_on' => 'date',
    ];

    // Recurrence frequencies
    public const RECURRENCE_FREQUENCIES = [
        'day' => ['label' => 'Daily', 'description' => 'Repeats every day'],
        'week' => ['label' => 'Weekly', 'description' => 'Repeats every week'],
        'month' => ['label' => 'Monthly', 'description' => 'Repeats every month'],
    ];

    // Recurrence end types
    public const RECURRENCE_END_TYPES = [
        'never' => ['label' => 'Never', 'description' => 'Continues indefinitely'],
        'after' => ['label' => 'After', 'description' => 'After a number of occurrences'],
        'on' => ['label' => 'On', 'description' => 'On a specific date'],
    ];

    // Reminder types
    public const REMINDER_TYPES = [
        'default' => ['label' => 'Default (60 min before)', 'minutes' => 60],
        'custom' => ['label' => 'Custom', 'minutes' => null],
        'none' => ['label' => 'No reminder', 'minutes' => null],
    ];

    // Activity colors for calendar
    public const COLORS = [
        'blue' => '#3b82f6',
        'green' => '#22c55e',
        'purple' => '#a855f7',
        'orange' => '#f97316',
        'pink' => '#ec4899',
        'teal' => '#14b8a6',
        'red' => '#ef4444',
        'yellow' => '#eab308',
    ];

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'coparenting_activity_children', 'activity_id', 'family_member_id')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getColorHexAttribute(): string
    {
        return self::COLORS[$this->color] ?? self::COLORS['blue'];
    }

    public function getRecurrenceInfoAttribute(): ?array
    {
        if (!$this->is_recurring) {
            return null;
        }

        return self::RECURRENCE_FREQUENCIES[$this->recurrence_frequency] ?? null;
    }

    public function getReminderInfoAttribute(): array
    {
        return self::REMINDER_TYPES[$this->reminder_type] ?? self::REMINDER_TYPES['default'];
    }

    public function getDurationAttribute(): string
    {
        if ($this->is_all_day) {
            $days = $this->starts_at->diffInDays($this->ends_at) + 1;
            return $days === 1 ? 'All day' : "{$days} days";
        }

        $hours = $this->starts_at->diffInHours($this->ends_at);
        $minutes = $this->starts_at->diffInMinutes($this->ends_at) % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    // ==================== SCOPES ====================

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc');
    }

    public function scopeInDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('starts_at', [$start, $end])
                ->orWhereBetween('ends_at', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('starts_at', '<=', $start)
                        ->where('ends_at', '>=', $end);
                });
        });
    }

    // ==================== METHODS ====================

    /**
     * Generate all occurrences of this activity within a date range.
     */
    public function generateOccurrences(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $occurrences = [];

        if (!$this->is_recurring) {
            // Single occurrence
            if ($this->starts_at->between($rangeStart, $rangeEnd) ||
                $this->ends_at->between($rangeStart, $rangeEnd)) {
                $occurrences[] = [
                    'id' => $this->id,
                    'title' => $this->title,
                    'start' => $this->starts_at->toIso8601String(),
                    'end' => $this->ends_at->toIso8601String(),
                    'allDay' => $this->is_all_day,
                    'color' => $this->color_hex,
                    'extendedProps' => [
                        'description' => $this->description,
                        'activity_id' => $this->id,
                    ],
                ];
            }
            return $occurrences;
        }

        // Calculate recurring occurrences
        $currentStart = $this->starts_at->copy();
        $duration = $this->starts_at->diffInMinutes($this->ends_at);
        $occurrenceCount = 0;
        $maxOccurrences = $this->recurrence_end_type === 'after' ? $this->recurrence_end_after : 365; // Max 1 year ahead

        while ($currentStart->lte($rangeEnd) && $occurrenceCount < $maxOccurrences) {
            // Check if we've passed the end date
            if ($this->recurrence_end_type === 'on' && $currentStart->gt($this->recurrence_end_on)) {
                break;
            }

            // Add occurrence if within range
            if ($currentStart->gte($rangeStart)) {
                $currentEnd = $currentStart->copy()->addMinutes($duration);

                $occurrences[] = [
                    'id' => $this->id . '-' . $currentStart->format('Y-m-d'),
                    'title' => $this->title,
                    'start' => $currentStart->toIso8601String(),
                    'end' => $currentEnd->toIso8601String(),
                    'allDay' => $this->is_all_day,
                    'color' => $this->color_hex,
                    'extendedProps' => [
                        'description' => $this->description,
                        'activity_id' => $this->id,
                        'is_recurring' => true,
                    ],
                ];
            }

            // Move to next occurrence
            switch ($this->recurrence_frequency) {
                case 'day':
                    $currentStart->addDay();
                    break;
                case 'week':
                    $currentStart->addWeek();
                    break;
                case 'month':
                    $currentStart->addMonth();
                    break;
            }

            $occurrenceCount++;
        }

        return $occurrences;
    }

    /**
     * Get the reminder datetime.
     */
    public function getReminderTime(): ?Carbon
    {
        if ($this->reminder_type === 'none') {
            return null;
        }

        $minutes = $this->reminder_type === 'default' ? 60 : $this->reminder_minutes;
        return $this->starts_at->copy()->subMinutes($minutes);
    }
}
