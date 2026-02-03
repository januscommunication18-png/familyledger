<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonImportantDate;
use App\Models\TodoItem;
use Illuminate\Http\Request;

class RemindersController extends Controller
{
    /**
     * Display the reminders page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get all reminders (tasks with due dates)
        $allReminders = TodoItem::where('tenant_id', $tenant->id)
            ->whereNotNull('due_date')
            ->with(['todoList', 'assignedTo'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Separate into categories
        $overdue = $allReminders->filter(function ($r) {
            return $r->due_date < now()->startOfDay() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $today = $allReminders->filter(function ($r) {
            return $r->due_date->isToday() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $tomorrow = $allReminders->filter(function ($r) {
            return $r->due_date->isTomorrow() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $thisWeek = $allReminders->filter(function ($r) {
            $dueDate = $r->due_date;
            return $dueDate > now()->addDay()
                && $dueDate <= now()->addDays(7)
                && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $upcoming = $allReminders->filter(function ($r) {
            return $r->due_date > now()->addDays(7) && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $completed = $allReminders->filter(function ($r) {
            return in_array($r->status, ['completed']);
        })->values();

        // Get birthday reminders
        $birthdayReminders = $this->getBirthdayReminders($tenant->id);

        // Get important date reminders
        $importantDateReminders = $this->getImportantDateReminders($tenant->id);

        return view('pages.reminders.index', [
            'overdue' => $overdue,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'thisWeek' => $thisWeek,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'birthdayReminders' => $birthdayReminders,
            'importantDateReminders' => $importantDateReminders,
            'stats' => [
                'total' => $allReminders->whereNotIn('status', ['completed', 'cancelled'])->count()
                    + $birthdayReminders->count()
                    + $importantDateReminders->count(),
                'overdue' => $overdue->count(),
                'today' => $today->count()
                    + $birthdayReminders->filter(fn($b) => $b['days_until'] === 0)->count()
                    + $importantDateReminders->filter(fn($d) => $d['days_until'] === 0)->count(),
                'completed' => $completed->count(),
            ],
        ]);
    }

    /**
     * Get upcoming birthday reminders.
     */
    private function getBirthdayReminders(string $tenantId)
    {
        $people = Person::where('tenant_id', $tenantId)
            ->where('birthday_reminder', true)
            ->whereNotNull('birthday')
            ->get();

        $reminders = collect();
        $today = now()->startOfDay();

        foreach ($people as $person) {
            // Calculate this year's birthday
            $birthdayThisYear = $person->birthday->copy()->year($today->year);

            // If birthday has passed this year, use next year's
            if ($birthdayThisYear->lt($today)) {
                $birthdayThisYear->addYear();
            }

            // Only include birthdays within the next 90 days
            $daysUntil = $today->diffInDays($birthdayThisYear, false);

            if ($daysUntil <= 90) {
                $reminders->push([
                    'person' => $person,
                    'birthday_date' => $birthdayThisYear,
                    'days_until' => $daysUntil,
                    'age' => $birthdayThisYear->year - $person->birthday->year,
                ]);
            }
        }

        return $reminders->sortBy('days_until')->values();
    }

    /**
     * Get upcoming important date reminders.
     */
    private function getImportantDateReminders(string $tenantId)
    {
        $importantDates = PersonImportantDate::whereHas('person', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->with('person')
            ->get();

        $reminders = collect();
        $today = now()->startOfDay();

        foreach ($importantDates as $date) {
            $nextOccurrence = null;

            if ($date->recurring_yearly) {
                // Calculate this year's occurrence
                $dateThisYear = $date->date->copy()->year($today->year);

                // If date has passed this year, use next year's
                if ($dateThisYear->lt($today)) {
                    $dateThisYear->addYear();
                }

                $nextOccurrence = $dateThisYear;
            } else {
                // Non-recurring: only show if in the future
                if ($date->date->gte($today)) {
                    $nextOccurrence = $date->date;
                }
            }

            if ($nextOccurrence) {
                $daysUntil = $today->diffInDays($nextOccurrence, false);

                // Only include dates within the next 90 days
                if ($daysUntil <= 90) {
                    $reminders->push([
                        'important_date' => $date,
                        'person' => $date->person,
                        'next_date' => $nextOccurrence,
                        'days_until' => $daysUntil,
                        'is_recurring' => $date->recurring_yearly,
                    ]);
                }
            }
        }

        return $reminders->sortBy('days_until')->values();
    }

    /**
     * Mark a reminder as complete.
     */
    public function complete(Request $request, TodoItem $reminder)
    {
        $user = $request->user();

        if ($reminder->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $reminder->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Reminder marked as complete.');
    }

    /**
     * Snooze a reminder.
     */
    public function snooze(Request $request, TodoItem $reminder)
    {
        $user = $request->user();

        if ($reminder->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $snoozeUntil = match($request->input('duration', '1day')) {
            '1hour' => now()->addHour(),
            '3hours' => now()->addHours(3),
            'tomorrow' => now()->addDay()->startOfDay()->addHours(9),
            '1day' => now()->addDay(),
            '1week' => now()->addWeek(),
            default => now()->addDay(),
        };

        $reminder->update([
            'due_date' => $snoozeUntil->toDateString(),
            'due_time' => $snoozeUntil->format('H:i'),
        ]);

        return redirect()->back()->with('success', 'Reminder snoozed.');
    }
}
