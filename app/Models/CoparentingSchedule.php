<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoparentingSchedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'template_type',
        'begins_at',
        'ends_at',
        'has_end_date',
        'repeat_every',
        'repeat_unit',
        'primary_parent',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'begins_at' => 'date',
        'ends_at' => 'date',
        'has_end_date' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Schedule template types
    public const TEMPLATE_TYPES = [
        'every_other_week' => [
            'label' => 'Every other week',
            'ratio' => '50/50',
            'description' => 'Parents alternate full weeks with the children.',
            'cycle_days' => 14,
        ],
        '2_2_3' => [
            'label' => '2-2-3',
            'ratio' => '50/50',
            'description' => 'Parent A has 2 days, Parent B has 2 days, then Parent A has 3 days. The next week reverses.',
            'cycle_days' => 14,
        ],
        '2_2_5_5' => [
            'label' => '2-2-5-5',
            'ratio' => '50/50',
            'description' => 'Parent A has 2 days, Parent B has 2 days, Parent A has 5 days, Parent B has 5 days.',
            'cycle_days' => 14,
        ],
        '3_4_4_3' => [
            'label' => '3-4-4-3',
            'ratio' => '50/50',
            'description' => 'Parent A has 3 days, Parent B has 4 days, Parent A has 4 days, Parent B has 3 days.',
            'cycle_days' => 14,
        ],
        'every_weekend' => [
            'label' => 'Every weekend',
            'ratio' => '60/40',
            'description' => 'One parent has weekdays, the other has every weekend.',
            'cycle_days' => 7,
        ],
        'every_other_weekend' => [
            'label' => 'Every other weekend',
            'ratio' => '80/20',
            'description' => 'One parent has the children most of the time, the other has every other weekend.',
            'cycle_days' => 14,
        ],
        'same_weekends' => [
            'label' => 'Same weekends each month',
            'ratio' => '80/20',
            'description' => 'One parent has specific weekends each month (e.g., 1st and 3rd weekends).',
            'cycle_days' => 28,
        ],
        'all_to_one' => [
            'label' => 'All to one parent',
            'ratio' => '100/0',
            'description' => 'One parent has full custody with optional visitation.',
            'cycle_days' => 7,
        ],
        'custom' => [
            'label' => 'Custom repeating rate',
            'ratio' => 'Custom',
            'description' => 'Define your own custom schedule pattern.',
            'cycle_days' => null,
        ],
    ];

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(CoparentingScheduleBlock::class, 'schedule_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'coparenting_schedule_children', 'schedule_id', 'family_member_id')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getTemplateInfoAttribute(): array
    {
        return self::TEMPLATE_TYPES[$this->template_type] ?? self::TEMPLATE_TYPES['custom'];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->template_info['label'] ?? 'Custom Schedule';
    }

    public function getRatioAttribute(): string
    {
        return $this->template_info['ratio'] ?? 'Custom';
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('begins_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    // ==================== METHODS ====================

    /**
     * Generate calendar events for a date range based on the schedule template.
     */
    public function generateEventsForRange(Carbon $startDate, Carbon $endDate): array
    {
        $events = [];
        $currentDate = $startDate->copy();
        $scheduleStart = Carbon::parse($this->begins_at);

        // If schedule hasn't started yet, start from schedule begin date
        if ($currentDate->lt($scheduleStart)) {
            $currentDate = $scheduleStart->copy();
        }

        // If schedule has ended, return empty
        if ($this->has_end_date && $this->ends_at && $currentDate->gt($this->ends_at)) {
            return [];
        }

        $effectiveEndDate = $endDate->copy();
        if ($this->has_end_date && $this->ends_at && $effectiveEndDate->gt($this->ends_at)) {
            $effectiveEndDate = Carbon::parse($this->ends_at);
        }

        switch ($this->template_type) {
            case 'every_other_week':
                $events = $this->generateEveryOtherWeekEvents($currentDate, $effectiveEndDate, $scheduleStart);
                break;
            case '2_2_3':
                $events = $this->generate223Events($currentDate, $effectiveEndDate, $scheduleStart);
                break;
            case '2_2_5_5':
                $events = $this->generate2255Events($currentDate, $effectiveEndDate, $scheduleStart);
                break;
            case '3_4_4_3':
                $events = $this->generate3443Events($currentDate, $effectiveEndDate, $scheduleStart);
                break;
            case 'every_weekend':
                $events = $this->generateEveryWeekendEvents($currentDate, $effectiveEndDate);
                break;
            case 'every_other_weekend':
                $events = $this->generateEveryOtherWeekendEvents($currentDate, $effectiveEndDate, $scheduleStart);
                break;
            case 'all_to_one':
                $events = $this->generateAllToOneEvents($currentDate, $effectiveEndDate);
                break;
            case 'custom':
                $events = $this->generateCustomEvents($currentDate, $effectiveEndDate);
                break;
            default:
                // Use manual blocks
                $events = $this->getBlockEvents($currentDate, $effectiveEndDate);
        }

        return $events;
    }

    /**
     * Generate every other week events (50/50).
     */
    protected function generateEveryOtherWeekEvents(Carbon $start, Carbon $end, Carbon $scheduleStart): array
    {
        $events = [];
        $current = $start->copy()->startOfWeek();
        $weeksSinceStart = (int) $scheduleStart->diffInWeeks($current);
        $isParentAWeek = ($weeksSinceStart % 2) === 0;

        while ($current->lte($end)) {
            $weekEnd = $current->copy()->endOfWeek();
            $parent = $isParentAWeek ? $this->primary_parent : $this->getOtherParent();

            $events[] = [
                'start' => $current->format('Y-m-d'),
                'end' => $weekEnd->format('Y-m-d'),
                'parent' => $parent,
                'title' => ucfirst($parent) . "'s Week",
            ];

            $current->addWeek();
            $isParentAWeek = !$isParentAWeek;
        }

        return $events;
    }

    /**
     * Generate 2-2-3 pattern events (50/50).
     */
    protected function generate223Events(Carbon $start, Carbon $end, Carbon $scheduleStart): array
    {
        $events = [];
        $pattern = [2, 2, 3, 2, 2, 3]; // Pattern for a 2-week cycle
        $current = $start->copy();
        $daysSinceStart = (int) $scheduleStart->diffInDays($current);
        $cycleDay = $daysSinceStart % 14;

        // Determine where we are in the pattern
        $patternIndex = 0;
        $dayCount = 0;
        foreach ($pattern as $index => $days) {
            if ($dayCount + $days > $cycleDay) {
                $patternIndex = $index;
                break;
            }
            $dayCount += $days;
        }

        while ($current->lte($end)) {
            $days = $pattern[$patternIndex % count($pattern)];
            $parent = ($patternIndex % 2 === 0) ? $this->primary_parent : $this->getOtherParent();
            $blockEnd = $current->copy()->addDays($days - 1);

            if ($blockEnd->gt($end)) {
                $blockEnd = $end->copy();
            }

            $events[] = [
                'start' => $current->format('Y-m-d'),
                'end' => $blockEnd->format('Y-m-d'),
                'parent' => $parent,
                'title' => ucfirst($parent) . " ({$days} days)",
            ];

            $current = $blockEnd->copy()->addDay();
            $patternIndex++;
        }

        return $events;
    }

    /**
     * Generate 2-2-5-5 pattern events (50/50).
     */
    protected function generate2255Events(Carbon $start, Carbon $end, Carbon $scheduleStart): array
    {
        $events = [];
        $pattern = [2, 2, 5, 5]; // 14-day cycle
        $current = $start->copy();
        $daysSinceStart = (int) $scheduleStart->diffInDays($current);
        $cycleDay = $daysSinceStart % 14;

        $patternIndex = 0;
        $dayCount = 0;
        foreach ($pattern as $index => $days) {
            if ($dayCount + $days > $cycleDay) {
                $patternIndex = $index;
                break;
            }
            $dayCount += $days;
        }

        while ($current->lte($end)) {
            $days = $pattern[$patternIndex % count($pattern)];
            $parent = ($patternIndex % 2 === 0) ? $this->primary_parent : $this->getOtherParent();
            $blockEnd = $current->copy()->addDays($days - 1);

            if ($blockEnd->gt($end)) {
                $blockEnd = $end->copy();
            }

            $events[] = [
                'start' => $current->format('Y-m-d'),
                'end' => $blockEnd->format('Y-m-d'),
                'parent' => $parent,
                'title' => ucfirst($parent) . " ({$days} days)",
            ];

            $current = $blockEnd->copy()->addDay();
            $patternIndex++;
        }

        return $events;
    }

    /**
     * Generate 3-4-4-3 pattern events (50/50).
     */
    protected function generate3443Events(Carbon $start, Carbon $end, Carbon $scheduleStart): array
    {
        $events = [];
        $pattern = [3, 4, 4, 3]; // 14-day cycle
        $current = $start->copy();
        $daysSinceStart = (int) $scheduleStart->diffInDays($current);
        $cycleDay = $daysSinceStart % 14;

        $patternIndex = 0;
        $dayCount = 0;
        foreach ($pattern as $index => $days) {
            if ($dayCount + $days > $cycleDay) {
                $patternIndex = $index;
                break;
            }
            $dayCount += $days;
        }

        while ($current->lte($end)) {
            $days = $pattern[$patternIndex % count($pattern)];
            $parent = ($patternIndex % 2 === 0) ? $this->primary_parent : $this->getOtherParent();
            $blockEnd = $current->copy()->addDays($days - 1);

            if ($blockEnd->gt($end)) {
                $blockEnd = $end->copy();
            }

            $events[] = [
                'start' => $current->format('Y-m-d'),
                'end' => $blockEnd->format('Y-m-d'),
                'parent' => $parent,
                'title' => ucfirst($parent) . " ({$days} days)",
            ];

            $current = $blockEnd->copy()->addDay();
            $patternIndex++;
        }

        return $events;
    }

    /**
     * Generate every weekend events (60/40).
     */
    protected function generateEveryWeekendEvents(Carbon $start, Carbon $end): array
    {
        $events = [];
        $current = $start->copy();
        $primaryParent = $this->primary_parent;
        $otherParent = $this->getOtherParent();

        while ($current->lte($end)) {
            $dayOfWeek = $current->dayOfWeek;

            // Weekday (Mon-Fri) belongs to primary parent
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $weekdayEnd = $current->copy();
                while ($weekdayEnd->dayOfWeek >= 1 && $weekdayEnd->dayOfWeek <= 5 && $weekdayEnd->lte($end)) {
                    $weekdayEnd->addDay();
                }
                $weekdayEnd->subDay();

                $events[] = [
                    'start' => $current->format('Y-m-d'),
                    'end' => $weekdayEnd->format('Y-m-d'),
                    'parent' => $primaryParent,
                    'title' => ucfirst($primaryParent) . ' (Weekdays)',
                ];
                $current = $weekdayEnd->copy()->addDay();
            } else {
                // Weekend belongs to other parent
                $weekendEnd = $current->copy();
                while (($weekendEnd->dayOfWeek === 0 || $weekendEnd->dayOfWeek === 6) && $weekendEnd->lte($end)) {
                    $weekendEnd->addDay();
                }
                $weekendEnd->subDay();

                $events[] = [
                    'start' => $current->format('Y-m-d'),
                    'end' => $weekendEnd->format('Y-m-d'),
                    'parent' => $otherParent,
                    'title' => ucfirst($otherParent) . ' (Weekend)',
                ];
                $current = $weekendEnd->copy()->addDay();
            }
        }

        return $events;
    }

    /**
     * Generate every other weekend events (80/20).
     */
    protected function generateEveryOtherWeekendEvents(Carbon $start, Carbon $end, Carbon $scheduleStart): array
    {
        $events = [];
        $current = $start->copy();
        $primaryParent = $this->primary_parent;
        $otherParent = $this->getOtherParent();
        $weeksSinceStart = (int) $scheduleStart->diffInWeeks($current);
        $isOtherParentWeekend = ($weeksSinceStart % 2) === 0;

        while ($current->lte($end)) {
            $dayOfWeek = $current->dayOfWeek;

            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                // Weekdays
                $weekdayEnd = $current->copy();
                while ($weekdayEnd->dayOfWeek >= 1 && $weekdayEnd->dayOfWeek <= 5 && $weekdayEnd->lte($end)) {
                    $weekdayEnd->addDay();
                }
                $weekdayEnd->subDay();

                $events[] = [
                    'start' => $current->format('Y-m-d'),
                    'end' => $weekdayEnd->format('Y-m-d'),
                    'parent' => $primaryParent,
                    'title' => ucfirst($primaryParent) . ' (Weekdays)',
                ];
                $current = $weekdayEnd->copy()->addDay();
            } else {
                // Weekend
                $weekendEnd = $current->copy();
                while (($weekendEnd->dayOfWeek === 0 || $weekendEnd->dayOfWeek === 6) && $weekendEnd->lte($end)) {
                    $weekendEnd->addDay();
                }
                $weekendEnd->subDay();

                $parent = $isOtherParentWeekend ? $otherParent : $primaryParent;
                $events[] = [
                    'start' => $current->format('Y-m-d'),
                    'end' => $weekendEnd->format('Y-m-d'),
                    'parent' => $parent,
                    'title' => ucfirst($parent) . ' (Weekend)',
                ];

                // Toggle for next weekend
                if ($current->dayOfWeek === 0) { // Sunday
                    $isOtherParentWeekend = !$isOtherParentWeekend;
                }

                $current = $weekendEnd->copy()->addDay();
            }
        }

        return $events;
    }

    /**
     * Generate all to one parent events (100/0).
     */
    protected function generateAllToOneEvents(Carbon $start, Carbon $end): array
    {
        return [[
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'parent' => $this->primary_parent,
            'title' => ucfirst($this->primary_parent) . ' (Full Custody)',
        ]];
    }

    /**
     * Generate custom pattern events.
     */
    protected function generateCustomEvents(Carbon $start, Carbon $end): array
    {
        // For custom, use the manual blocks
        return $this->getBlockEvents($start, $end);
    }

    /**
     * Get events from manual blocks.
     */
    protected function getBlockEvents(Carbon $start, Carbon $end): array
    {
        $events = [];

        foreach ($this->blocks as $block) {
            $blockStart = Carbon::parse($block->starts_at);
            $blockEnd = Carbon::parse($block->ends_at);

            if ($blockStart->lte($end) && $blockEnd->gte($start)) {
                $events[] = [
                    'start' => $blockStart->format('Y-m-d'),
                    'end' => $blockEnd->format('Y-m-d'),
                    'parent' => $block->parent_role,
                    'title' => ucfirst($block->parent_role),
                ];
            }
        }

        return $events;
    }

    /**
     * Get the other parent role.
     */
    protected function getOtherParent(): string
    {
        return $this->primary_parent === 'mother' ? 'father' : 'mother';
    }
}
