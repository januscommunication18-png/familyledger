<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Person;
use App\Models\PersonImportantDate;
use App\Models\TodoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    private function transformReminder($reminder): array
    {
        $dueText = $this->getDueText($reminder->due_date);

        return [
            'id' => $reminder->id,
            'title' => $reminder->title,
            'description' => $reminder->description,
            'due_date' => $reminder->due_date?->format('Y-m-d'),
            'due_date_formatted' => $reminder->due_date?->format('M j, Y'),
            'due_date_day' => $reminder->due_date?->format('D, M j'),
            'due_text' => $dueText,
            'due_time' => $reminder->due_time,
            'due_time_formatted' => $reminder->due_time ? \Carbon\Carbon::parse($reminder->due_time)->format('g:i A') : null,
            'priority' => $reminder->priority ?? 'medium',
            'status' => $reminder->status,
            'is_recurring' => $reminder->is_recurring ?? false,
            'recurrence_pattern' => $reminder->recurrence_pattern,
            'category' => $reminder->category,
            'category_icon' => $this->getCategoryIcon($reminder->category),
            'assigned_to' => $reminder->assignedTo?->name,
            'completed_at' => $reminder->completed_at?->diffForHumans(),
            'created_at' => $reminder->created_at?->toISOString(),
            'updated_at' => $reminder->updated_at?->toISOString(),
        ];
    }

    private function getDueText($dueDate): string
    {
        if (!$dueDate) return 'No due date';

        $now = now()->startOfDay();
        $due = $dueDate->startOfDay();
        $diff = $now->diffInDays($due, false);

        if ($diff < 0) {
            $days = abs($diff);
            return $days == 1 ? 'Overdue by 1 day' : "Overdue by {$days} days";
        } elseif ($diff == 0) {
            return 'Due today';
        } elseif ($diff == 1) {
            return 'Due tomorrow';
        } elseif ($diff <= 7) {
            return "Due in {$diff} days";
        } else {
            return 'Due ' . $dueDate->format('M j, Y');
        }
    }

    private function getCategoryIcon($category): string
    {
        return match($category) {
            'home_chores' => 'house.fill',
            'bills' => 'doc.text.fill',
            'health' => 'heart.fill',
            'kids' => 'figure.2.and.child.holdinghands',
            'car' => 'car.fill',
            'pet_care' => 'pawprint.fill',
            'family_rituals' => 'person.3.fill',
            'appointments' => 'calendar',
            'groceries' => 'cart.fill',
            'school' => 'book.fill',
            default => 'bell.fill',
        };
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

        // Separate into categories
        $overdue = $rawReminders->filter(function ($r) {
            return $r->due_date < now()->startOfDay() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $today = $rawReminders->filter(function ($r) {
            return $r->due_date->isToday() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $tomorrow = $rawReminders->filter(function ($r) {
            return $r->due_date->isTomorrow() && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $thisWeek = $rawReminders->filter(function ($r) {
            $dueDate = $r->due_date;
            return $dueDate > now()->addDay()
                && $dueDate <= now()->addDays(7)
                && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $upcoming = $rawReminders->filter(function ($r) {
            return $r->due_date > now()->addDays(7) && !in_array($r->status, ['completed', 'cancelled']);
        })->values();

        $completed = $rawReminders->filter(function ($r) {
            return in_array($r->status, ['completed']);
        })->values();

        // Get birthday reminders
        $birthdayReminders = $this->getBirthdayReminders($tenant->id);

        // Get important date reminders
        $importantDateReminders = $this->getImportantDateReminders($tenant->id);

        return $this->success([
            'reminders' => $rawReminders->map(fn($r) => $this->transformReminder($r)),
            'overdue' => $overdue->map(fn($r) => $this->transformReminder($r))->values(),
            'today' => $today->map(fn($r) => $this->transformReminder($r))->values(),
            'tomorrow' => $tomorrow->map(fn($r) => $this->transformReminder($r))->values(),
            'this_week' => $thisWeek->map(fn($r) => $this->transformReminder($r))->values(),
            'upcoming' => $upcoming->map(fn($r) => $this->transformReminder($r))->values(),
            'completed' => $completed->map(fn($r) => $this->transformReminder($r))->take(10)->values(),
            'birthday_reminders' => $birthdayReminders,
            'important_date_reminders' => $importantDateReminders,
            'stats' => [
                'total' => $rawReminders->whereNotIn('status', ['completed', 'cancelled'])->count()
                    + $birthdayReminders->count()
                    + $importantDateReminders->count(),
                'overdue' => $overdue->count(),
                'today' => $today->count()
                    + $birthdayReminders->filter(fn($b) => $b['days_until'] === 0)->count()
                    + $importantDateReminders->filter(fn($d) => $d['days_until'] === 0)->count(),
                'completed' => $completed->count(),
                'high_priority' => $rawReminders->where('priority', 'high')
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),
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
            $birthdayThisYear = $person->birthday->copy()->year($today->year);

            if ($birthdayThisYear->lt($today)) {
                $birthdayThisYear->addYear();
            }

            $daysUntil = $today->diffInDays($birthdayThisYear, false);

            if ($daysUntil <= 90) {
                $reminders->push([
                    'id' => 'birthday_' . $person->id,
                    'person_id' => $person->id,
                    'person_name' => $person->full_name,
                    'person_initials' => $person->initials,
                    'person_image_url' => $person->profile_image_url,
                    'relationship' => $person->relationship_name ?? ucfirst($person->relationship ?? 'Contact'),
                    'birthday_date' => $birthdayThisYear->format('M j'),
                    'birthday_date_full' => $birthdayThisYear->format('M j, Y'),
                    'days_until' => $daysUntil,
                    'days_until_text' => $this->getDaysUntilText($daysUntil),
                    'turning_age' => $birthdayThisYear->year - $person->birthday->year,
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
                $dateThisYear = $date->date->copy()->year($today->year);

                if ($dateThisYear->lt($today)) {
                    $dateThisYear->addYear();
                }

                $nextOccurrence = $dateThisYear;
            } else {
                if ($date->date->gte($today)) {
                    $nextOccurrence = $date->date;
                }
            }

            if ($nextOccurrence) {
                $daysUntil = $today->diffInDays($nextOccurrence, false);

                if ($daysUntil <= 90) {
                    $reminders->push([
                        'id' => 'date_' . $date->id,
                        'label' => $date->label,
                        'person_id' => $date->person->id,
                        'person_name' => $date->person->full_name,
                        'person_initials' => $date->person->initials,
                        'person_image_url' => $date->person->profile_image_url,
                        'next_date' => $nextOccurrence->format('M j, Y'),
                        'days_until' => $daysUntil,
                        'days_until_text' => $this->getDaysUntilText($daysUntil),
                        'is_recurring' => $date->recurring_yearly ?? false,
                    ]);
                }
            }
        }

        return $reminders->sortBy('days_until')->values();
    }

    private function getDaysUntilText(int $daysUntil): string
    {
        if ($daysUntil === 0) {
            return 'Today!';
        } elseif ($daysUntil === 1) {
            return 'Tomorrow';
        } else {
            return "In {$daysUntil} days";
        }
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
