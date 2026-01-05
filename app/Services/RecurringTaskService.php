<?php

namespace App\Services;

use App\Models\TaskOccurrence;
use App\Models\TodoItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringTaskService
{
    /**
     * Calculate the next occurrence date based on recurrence settings.
     */
    public function calculateNextOccurrence(TodoItem $task, ?Carbon $fromDate = null): ?Carbon
    {
        $fromDate = $fromDate ?? Carbon::today();
        $frequency = $task->recurrence_frequency ?? $task->recurrence_pattern ?? 'daily';

        $nextDate = match ($frequency) {
            'daily' => $this->calculateDailyNext($task, $fromDate),
            'weekly' => $this->calculateWeeklyNext($task, $fromDate),
            'monthly' => $this->calculateMonthlyNext($task, $fromDate),
            'yearly' => $this->calculateYearlyNext($task, $fromDate),
            default => $fromDate->copy()->addDay(),
        };

        // Check end conditions
        if ($this->hasReachedEndCondition($task, $nextDate)) {
            return null;
        }

        return $nextDate;
    }

    /**
     * Generate the next occurrence for a recurring task.
     */
    public function generateNextOccurrence(TodoItem $task): ?TaskOccurrence
    {
        if (!$task->is_recurring || $task->series_status === 'paused') {
            return null;
        }

        // Get the last occurrence date
        $lastOccurrence = $task->occurrences()->orderBy('scheduled_date', 'desc')->first();
        $fromDate = $lastOccurrence ? $lastOccurrence->scheduled_date : ($task->due_date ?? Carbon::today());

        $nextDate = $this->calculateNextOccurrence($task, $fromDate);

        if (!$nextDate) {
            return null;
        }

        // Get next assignee if rotation is enabled
        $assigneeId = null;
        if ($task->rotation_type !== 'none' && $task->assignees->isNotEmpty()) {
            $assignee = $task->getNextRotationAssignee();
            $assigneeId = $assignee?->id;
        }

        $occurrenceNumber = ($task->occurrences()->max('occurrence_number') ?? 0) + 1;

        return TaskOccurrence::create([
            'tenant_id' => $task->tenant_id,
            'todo_item_id' => $task->id,
            'occurrence_number' => $occurrenceNumber,
            'scheduled_date' => $nextDate,
            'scheduled_time' => $task->due_time,
            'assigned_to' => $assigneeId,
            'status' => 'open',
        ]);
    }

    /**
     * Generate scheduled occurrences for schedule_ahead mode.
     */
    public function generateScheduledOccurrences(TodoItem $task, int $daysAhead): Collection
    {
        $occurrences = collect();

        if (!$task->is_recurring || $task->series_status === 'paused') {
            return $occurrences;
        }

        $startDate = $task->due_date ?? Carbon::today();
        $endDate = Carbon::today()->addDays($daysAhead);
        $currentDate = $startDate->copy();

        // Get existing occurrence dates to avoid duplicates
        $existingDates = $task->occurrences()
            ->pluck('scheduled_date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        while ($currentDate->lte($endDate)) {
            if ($this->shouldOccurOn($task, $currentDate)) {
                $dateString = $currentDate->format('Y-m-d');

                if (!in_array($dateString, $existingDates)) {
                    $occurrence = $this->createOccurrenceForDate($task, $currentDate->copy());
                    if ($occurrence) {
                        $occurrences->push($occurrence);
                        $existingDates[] = $dateString;
                    }
                }
            }

            // Move to next potential date
            $currentDate = $this->getNextPotentialDate($task, $currentDate);
        }

        return $occurrences;
    }

    /**
     * Handle missed occurrences based on policy.
     */
    public function handleMissedOccurrences(TodoItem $task): void
    {
        $missedOccurrences = $task->occurrences()
            ->where('status', 'open')
            ->where('scheduled_date', '<', Carbon::today())
            ->get();

        foreach ($missedOccurrences as $occurrence) {
            match ($task->missed_policy) {
                'skip' => $occurrence->update([
                    'status' => 'skipped',
                    'skipped_reason' => 'Auto-skipped due to missed policy',
                ]),
                'reschedule' => $occurrence->update([
                    'scheduled_date' => Carbon::today(),
                ]),
                default => $occurrence->update(['status' => 'overdue']), // carryover
            };
        }
    }

    /**
     * Wake up snoozed occurrences that have passed their snooze time.
     */
    public function wakeUpSnoozedOccurrences(): void
    {
        TaskOccurrence::where('status', 'snoozed')
            ->where('snoozed_until', '<=', now())
            ->update([
                'status' => 'open',
                'snoozed_until' => null,
            ]);
    }

    /**
     * Check if task should occur on a specific date.
     */
    private function shouldOccurOn(TodoItem $task, Carbon $date): bool
    {
        $frequency = $task->recurrence_frequency ?? $task->recurrence_pattern ?? 'daily';
        $interval = $task->recurrence_interval ?? 1;
        $startDate = $task->due_date ?? $task->created_at->startOfDay();

        // Skip weekends if enabled
        if ($task->skip_weekends && $date->isWeekend()) {
            return false;
        }

        return match ($frequency) {
            'daily' => $this->matchesDailyPattern($date, $startDate, $interval),
            'weekly' => $this->matchesWeeklyPattern($task, $date, $startDate, $interval),
            'monthly' => $this->matchesMonthlyPattern($task, $date, $startDate, $interval),
            'yearly' => $this->matchesYearlyPattern($task, $date, $startDate, $interval),
            default => false,
        };
    }

    /**
     * Calculate next daily occurrence.
     */
    private function calculateDailyNext(TodoItem $task, Carbon $from): Carbon
    {
        $interval = $task->recurrence_interval ?? 1;
        $next = $from->copy()->addDays($interval);

        if ($task->skip_weekends) {
            while ($next->isWeekend()) {
                $next->addDay();
            }
        }

        return $next;
    }

    /**
     * Calculate next weekly occurrence.
     */
    private function calculateWeeklyNext(TodoItem $task, Carbon $from): Carbon
    {
        $interval = $task->recurrence_interval ?? 1;
        $days = $task->recurrence_days ?? [];

        if (empty($days)) {
            return $from->copy()->addWeeks($interval);
        }

        // Find the next matching day
        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $selectedDays = array_map(fn($d) => $dayMap[$d] ?? 0, $days);
        sort($selectedDays);

        $next = $from->copy()->addDay();
        $weeksChecked = 0;
        $maxIterations = 365; // Safety limit

        for ($i = 0; $i < $maxIterations; $i++) {
            if (in_array($next->dayOfWeek, $selectedDays)) {
                // Check if we've passed enough weeks
                $weeksDiff = $from->diffInWeeks($next);
                if ($weeksDiff >= $interval - 1 || $next->weekOfYear !== $from->weekOfYear) {
                    if ($task->skip_weekends && $next->isWeekend()) {
                        $next->addDay();
                        continue;
                    }
                    return $next;
                }
            }
            $next->addDay();
        }

        return $from->copy()->addWeeks($interval);
    }

    /**
     * Calculate next monthly occurrence.
     */
    private function calculateMonthlyNext(TodoItem $task, Carbon $from): Carbon
    {
        $interval = $task->recurrence_interval ?? 1;
        $monthlyType = $task->monthly_type ?? 'day_of_month';

        if ($monthlyType === 'day_of_month') {
            $day = $task->monthly_day ?? $from->day;
            $next = $from->copy()->addMonths($interval);
            $next->day = min($day, $next->daysInMonth);
        } else {
            // day_of_week: e.g., "2nd Tuesday"
            $week = $task->monthly_week ?? 1;
            $weekday = $task->monthly_weekday ?? 'mon';
            $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
            $dayOfWeek = $dayMap[$weekday] ?? 1;

            $next = $from->copy()->addMonths($interval)->startOfMonth();
            $next = $this->getNthWeekdayOfMonth($next, $week, $dayOfWeek);
        }

        if ($task->skip_weekends && $next->isWeekend()) {
            $next = $next->isMonday() ? $next : $next->next(Carbon::MONDAY);
        }

        return $next;
    }

    /**
     * Calculate next yearly occurrence.
     */
    private function calculateYearlyNext(TodoItem $task, Carbon $from): Carbon
    {
        $interval = $task->recurrence_interval ?? 1;
        $month = $task->yearly_month ?? $from->month;
        $day = $task->yearly_day ?? $from->day;

        $next = $from->copy()->addYears($interval);
        $next->month = $month;
        $next->day = min($day, Carbon::createFromDate($next->year, $month, 1)->daysInMonth);

        if ($task->skip_weekends && $next->isWeekend()) {
            $next = $next->isMonday() ? $next : $next->next(Carbon::MONDAY);
        }

        return $next;
    }

    /**
     * Check if the recurrence has reached its end condition.
     */
    private function hasReachedEndCondition(TodoItem $task, Carbon $nextDate): bool
    {
        $endType = $task->recurrence_end_type ?? 'never';

        return match ($endType) {
            'on_date' => $task->recurrence_end_date && $nextDate->gt($task->recurrence_end_date),
            'after_occurrences' => $task->recurrence_max_occurrences &&
                $task->occurrences()->count() >= $task->recurrence_max_occurrences,
            default => false,
        };
    }

    /**
     * Get the nth weekday of a month.
     */
    private function getNthWeekdayOfMonth(Carbon $month, int $n, int $dayOfWeek): Carbon
    {
        $date = $month->copy()->startOfMonth();

        // Move to first occurrence of the weekday
        while ($date->dayOfWeek !== $dayOfWeek) {
            $date->addDay();
        }

        // Move to nth occurrence
        if ($n === 5) {
            // Last occurrence of the month
            while ($date->copy()->addWeek()->month === $month->month) {
                $date->addWeek();
            }
        } else {
            $date->addWeeks($n - 1);
        }

        return $date;
    }

    /**
     * Check if date matches daily pattern.
     */
    private function matchesDailyPattern(Carbon $date, Carbon $start, int $interval): bool
    {
        $daysDiff = $start->diffInDays($date);
        return $daysDiff % $interval === 0;
    }

    /**
     * Check if date matches weekly pattern.
     */
    private function matchesWeeklyPattern(TodoItem $task, Carbon $date, Carbon $start, int $interval): bool
    {
        $days = $task->recurrence_days ?? [];

        if (empty($days)) {
            $weeksDiff = $start->diffInWeeks($date);
            return $weeksDiff % $interval === 0 && $date->dayOfWeek === $start->dayOfWeek;
        }

        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $selectedDays = array_map(fn($d) => $dayMap[$d] ?? 0, $days);

        if (!in_array($date->dayOfWeek, $selectedDays)) {
            return false;
        }

        // Check week interval
        $weeksDiff = $start->startOfWeek()->diffInWeeks($date->startOfWeek());
        return $weeksDiff % $interval === 0;
    }

    /**
     * Check if date matches monthly pattern.
     */
    private function matchesMonthlyPattern(TodoItem $task, Carbon $date, Carbon $start, int $interval): bool
    {
        $monthsDiff = $start->diffInMonths($date);
        if ($monthsDiff % $interval !== 0) {
            return false;
        }

        $monthlyType = $task->monthly_type ?? 'day_of_month';

        if ($monthlyType === 'day_of_month') {
            $targetDay = $task->monthly_day ?? $start->day;
            return $date->day === min($targetDay, $date->daysInMonth);
        }

        // day_of_week
        $week = $task->monthly_week ?? 1;
        $weekday = $task->monthly_weekday ?? 'mon';
        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $targetDayOfWeek = $dayMap[$weekday] ?? 1;

        if ($date->dayOfWeek !== $targetDayOfWeek) {
            return false;
        }

        $expectedDate = $this->getNthWeekdayOfMonth($date->copy(), $week, $targetDayOfWeek);
        return $date->isSameDay($expectedDate);
    }

    /**
     * Check if date matches yearly pattern.
     */
    private function matchesYearlyPattern(TodoItem $task, Carbon $date, Carbon $start, int $interval): bool
    {
        $yearsDiff = $start->diffInYears($date);
        if ($yearsDiff % $interval !== 0) {
            return false;
        }

        $targetMonth = $task->yearly_month ?? $start->month;
        $targetDay = $task->yearly_day ?? $start->day;

        return $date->month === $targetMonth &&
            $date->day === min($targetDay, Carbon::createFromDate($date->year, $targetMonth, 1)->daysInMonth);
    }

    /**
     * Get the next potential date for occurrence checking.
     */
    private function getNextPotentialDate(TodoItem $task, Carbon $current): Carbon
    {
        $frequency = $task->recurrence_frequency ?? $task->recurrence_pattern ?? 'daily';

        return match ($frequency) {
            'daily' => $current->copy()->addDay(),
            'weekly' => $current->copy()->addDay(),
            'monthly' => $current->copy()->addDay(),
            'yearly' => $current->copy()->addDay(),
            default => $current->copy()->addDay(),
        };
    }

    /**
     * Create an occurrence for a specific date.
     */
    private function createOccurrenceForDate(TodoItem $task, Carbon $date): ?TaskOccurrence
    {
        if ($this->hasReachedEndCondition($task, $date)) {
            return null;
        }

        // Get next assignee if rotation is enabled
        $assigneeId = null;
        if ($task->rotation_type !== 'none' && $task->assignees->isNotEmpty()) {
            $assignee = $task->getNextRotationAssignee();
            $assigneeId = $assignee?->id;
        }

        $occurrenceNumber = ($task->occurrences()->max('occurrence_number') ?? 0) + 1;

        return TaskOccurrence::create([
            'tenant_id' => $task->tenant_id,
            'todo_item_id' => $task->id,
            'occurrence_number' => $occurrenceNumber,
            'scheduled_date' => $date,
            'scheduled_time' => $task->due_time,
            'assigned_to' => $assigneeId,
            'status' => 'open',
        ]);
    }
}
