<?php

namespace App\Http\Controllers;

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

        return view('pages.reminders.index', [
            'overdue' => $overdue,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'thisWeek' => $thisWeek,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'stats' => [
                'total' => $allReminders->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'overdue' => $overdue->count(),
                'today' => $today->count(),
                'completed' => $completed->count(),
            ],
        ]);
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
