<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use App\Models\CoparentingDailyCheckin;

class CoparentingActualTime extends Model
{
    use BelongsToTenant;

    protected $table = 'coparenting_actual_time';

    protected $fillable = [
        'tenant_id',
        'checked_by',
        'family_member_id',
        'date',
        'parent_role',
        'check_in_time',
        'check_out_time',
        'is_full_day',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_full_day' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'family_member_id');
    }

    // ==================== ACCESSORS ====================

    public function getParentLabelAttribute(): string
    {
        return ucfirst($this->parent_role);
    }

    public function getHoursAttribute(): float
    {
        if ($this->is_full_day) {
            return 24;
        }

        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = Carbon::parse($this->check_in_time);
            $checkOut = Carbon::parse($this->check_out_time);
            return round($checkIn->diffInMinutes($checkOut) / 60, 2);
        }

        return 0;
    }

    // ==================== SCOPES ====================

    public function scopeForChild($query, $childId)
    {
        return $query->where('family_member_id', $childId);
    }

    public function scopeForParent($query, string $parentRole)
    {
        return $query->where('parent_role', $parentRole);
    }

    public function scopeInDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    // ==================== STATIC METHODS ====================

    /**
     * Calculate time statistics for a date range.
     * Combines data from both Actual Time records AND Daily Check-ins.
     */
    public static function calculateStats(string $tenantId, ?int $childId, Carbon $start, Carbon $end): array
    {
        // Get Actual Time records
        $actualTimeQuery = self::where('tenant_id', $tenantId)
            ->inDateRange($start, $end);

        if ($childId) {
            $actualTimeQuery->forChild($childId);
        }

        $actualTimeRecords = $actualTimeQuery->get();

        // Get Daily Check-in records
        $checkinQuery = CoparentingDailyCheckin::where('tenant_id', $tenantId)
            ->whereBetween('checkin_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);

        if ($childId) {
            $checkinQuery->where('family_member_id', $childId);
        }

        $checkinRecords = $checkinQuery->get();

        // Combine unique days from both sources (prefer actual time if both exist for same day)
        $daysByParent = [];

        // First add actual time records
        foreach ($actualTimeRecords as $record) {
            $dateKey = $record->date->format('Y-m-d');
            $daysByParent[$dateKey] = $record->parent_role;
        }

        // Then add daily check-ins (only if no actual time record exists for that day)
        foreach ($checkinRecords as $checkin) {
            $dateKey = $checkin->checkin_date->format('Y-m-d');
            if (!isset($daysByParent[$dateKey])) {
                $daysByParent[$dateKey] = $checkin->parent_role;
            }
        }

        // Calculate stats from combined data
        $motherDays = count(array_filter($daysByParent, fn($role) => $role === 'mother'));
        $fatherDays = count(array_filter($daysByParent, fn($role) => $role === 'father'));
        $totalDays = $motherDays + $fatherDays;

        // Hours only from actual time records (check-ins don't track hours)
        $motherHours = $actualTimeRecords->where('parent_role', 'mother')->sum('hours');
        $fatherHours = $actualTimeRecords->where('parent_role', 'father')->sum('hours');

        return [
            'mother' => [
                'days' => $motherDays,
                'hours' => $motherHours,
                'percentage' => $totalDays > 0 ? round(($motherDays / $totalDays) * 100, 1) : 0,
            ],
            'father' => [
                'days' => $fatherDays,
                'hours' => $fatherHours,
                'percentage' => $totalDays > 0 ? round(($fatherDays / $totalDays) * 100, 1) : 0,
            ],
            'total_days' => $totalDays,
            'date_range' => [
                'start' => $start->format('M j, Y'),
                'end' => $end->format('M j, Y'),
            ],
        ];
    }

    /**
     * Get daily breakdown for a month.
     */
    public static function getDailyBreakdown(string $tenantId, ?int $childId, int $year, int $month): Collection
    {
        $query = self::where('tenant_id', $tenantId)
            ->forMonth($year, $month)
            ->orderBy('date', 'asc');

        if ($childId) {
            $query->forChild($childId);
        }

        return $query->get()->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });
    }

    /**
     * Compare actual time vs planned schedule.
     */
    public static function compareWithSchedule(string $tenantId, ?int $childId, Carbon $start, Carbon $end, array $plannedEvents): array
    {
        $actualStats = self::calculateStats($tenantId, $childId, $start, $end);

        // Calculate planned statistics
        $plannedMotherDays = 0;
        $plannedFatherDays = 0;

        foreach ($plannedEvents as $event) {
            $eventStart = Carbon::parse($event['start']);
            $eventEnd = Carbon::parse($event['end']);
            $days = $eventStart->diffInDays($eventEnd) + 1;

            if ($event['parent'] === 'mother') {
                $plannedMotherDays += $days;
            } else {
                $plannedFatherDays += $days;
            }
        }

        $plannedTotal = $plannedMotherDays + $plannedFatherDays;

        return [
            'actual' => $actualStats,
            'planned' => [
                'mother' => [
                    'days' => $plannedMotherDays,
                    'percentage' => $plannedTotal > 0 ? round(($plannedMotherDays / $plannedTotal) * 100, 1) : 0,
                ],
                'father' => [
                    'days' => $plannedFatherDays,
                    'percentage' => $plannedTotal > 0 ? round(($plannedFatherDays / $plannedTotal) * 100, 1) : 0,
                ],
                'total_days' => $plannedTotal,
            ],
            'variance' => [
                'mother' => [
                    'days' => $actualStats['mother']['days'] - $plannedMotherDays,
                    'percentage' => $actualStats['mother']['percentage'] - ($plannedTotal > 0 ? round(($plannedMotherDays / $plannedTotal) * 100, 1) : 0),
                ],
                'father' => [
                    'days' => $actualStats['father']['days'] - $plannedFatherDays,
                    'percentage' => $actualStats['father']['percentage'] - ($plannedTotal > 0 ? round(($plannedFatherDays / $plannedTotal) * 100, 1) : 0),
                ],
            ],
        ];
    }
}
