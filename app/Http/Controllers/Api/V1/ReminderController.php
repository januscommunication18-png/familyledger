<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TodoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    private function transformReminder($reminder): array
    {
        return [
            'id' => $reminder->id,
            'title' => $reminder->title,
            'description' => $reminder->description,
            'due_date' => $reminder->due_date?->format('Y-m-d'),
            'due_time' => $reminder->due_time,
            'priority' => $reminder->priority ?? 'medium',
            'status' => $reminder->status,
            'is_recurring' => $reminder->is_recurring ?? false,
            'recurrence_pattern' => $reminder->recurrence_pattern,
            'category' => $reminder->category,
            'assigned_to' => $reminder->assignedTo?->name,
            'created_at' => $reminder->created_at?->toISOString(),
            'updated_at' => $reminder->updated_at?->toISOString(),
        ];
    }

    /**
     * Get all reminders (tasks with due dates).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawReminders = TodoItem::where('tenant_id', $tenant->id)
            ->whereNotNull('due_date')
            ->with(['todoList', 'assignedTo'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Transform reminders
        $reminders = $rawReminders->map(fn($r) => $this->transformReminder($r));

        // Get overdue reminders
        $overdueItems = $rawReminders->filter(function ($r) {
            return $r->due_date < now() && !in_array($r->status, ['completed', 'cancelled']);
        });

        // Get upcoming reminders (next 7 days)
        $upcomingItems = $rawReminders->filter(function ($r) {
            return $r->due_date >= now() && $r->due_date <= now()->addDays(7) && !in_array($r->status, ['completed', 'cancelled']);
        });

        return $this->success([
            'reminders' => $reminders,
            'overdue' => $overdueItems->map(fn($r) => $this->transformReminder($r))->values(),
            'upcoming' => $upcomingItems->map(fn($r) => $this->transformReminder($r))->values(),
            'total' => $rawReminders->count(),
            'stats' => [
                'total' => $rawReminders->count(),
                'overdue' => $overdueItems->count(),
                'upcoming_week' => $upcomingItems->count(),
                'high_priority' => $rawReminders->where('priority', 'high')
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),
            ],
        ]);
    }

    /**
     * Get overdue reminders.
     */
    public function overdue(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $reminders = TodoItem::where('tenant_id', $tenant->id)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['todoList', 'assignedTo'])
            ->orderBy('due_date', 'asc')
            ->get();

        return $this->success([
            'reminders' => $reminders,
            'total' => $reminders->count(),
        ]);
    }

    /**
     * Get upcoming reminders.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $reminders = TodoItem::where('tenant_id', $tenant->id)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['todoList', 'assignedTo'])
            ->orderBy('due_date', 'asc')
            ->get();

        return $this->success([
            'reminders' => $reminders,
            'total' => $reminders->count(),
        ]);
    }

    /**
     * Get a single reminder.
     */
    public function show(Request $request, TodoItem $reminder): JsonResponse
    {
        $user = $request->user();

        if ($reminder->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        return $this->success([
            'reminder' => $reminder->load(['todoList', 'assignedTo']),
        ]);
    }
}
