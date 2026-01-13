<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Goal;
use App\Models\TaskOccurrence;
use App\Models\TodoComment;
use App\Models\TodoItem;
use App\Services\RecurringTaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected RecurringTaskService $recurringService;

    public function __construct(RecurringTaskService $recurringService)
    {
        $this->recurringService = $recurringService;
    }

    /**
     * Display the main Goals & To-Do page with tabs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $tab = $request->get('tab', 'todos');

        // Get goals for the Goals tab
        $goals = Goal::where('tenant_id', $tenantId)
            ->withCount(['tasks as active_tasks_count' => function ($query) {
                $query->where('status', '!=', 'completed');
            }])
            ->orderByRaw("FIELD(status, 'active', 'paused', 'completed', 'archived')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Get tasks (flat list, no lists grouping)
        $tasksQuery = TodoItem::where('tenant_id', $tenantId)
            ->whereNull('parent_task_id')
            ->with(['assignees', 'goal', 'createdBy', 'comments']);

        // Apply filters
        if ($request->has('category') && $request->category !== 'all') {
            $tasksQuery->where('category', $request->category);
        }
        if ($request->has('priority') && $request->priority !== 'all') {
            $tasksQuery->where('priority', $request->priority);
        }
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'overdue') {
                $tasksQuery->overdue();
            } elseif ($request->status === 'due_today') {
                $tasksQuery->dueToday();
            } else {
                $tasksQuery->where('status', $request->status);
            }
        }
        if ($request->has('goal_id') && $request->goal_id !== 'all') {
            $tasksQuery->where('goal_id', $request->goal_id);
        }
        if ($request->boolean('recurring_only')) {
            $tasksQuery->recurring();
        }
        if ($request->boolean('missed_recurring')) {
            // Include both: recurring tasks with missed occurrences AND overdue non-recurring tasks
            $tasksQuery->where(function ($q) {
                $q->missedRecurring()
                  ->orWhere(function ($q2) {
                      $q2->where('is_recurring', false)
                         ->whereNotNull('due_date')
                         ->where('due_date', '<', now()->startOfDay())
                         ->whereNotIn('status', ['completed', 'skipped']);
                  });
            });
        }
        if ($request->boolean('upcoming_this_week')) {
            $tasksQuery->upcomingThisWeek();
        }
        if ($request->has('assignee') && $request->assignee !== 'all') {
            $tasksQuery->whereHas('assignees', function ($q) use ($request) {
                $q->where('family_members.id', $request->assignee);
            });
        }

        // Default ordering
        $tasks = $tasksQuery
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'snoozed', 'completed', 'skipped')")
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Separate tasks by status for display
        $openTasks = $tasks->whereIn('status', ['open', 'in_progress', 'snoozed']);
        $completedTasks = $tasks->where('status', 'completed');

        // Get today's occurrences for recurring tasks
        $todayOccurrences = TaskOccurrence::where('tenant_id', $tenantId)
            ->whereDate('scheduled_date', today())
            ->with(['task', 'assignee'])
            ->orderBy('scheduled_time')
            ->get();

        // Get family members for filters and assignment
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name', 'asc')
            ->get();

        return view('pages.goals-todo.index', [
            'tab' => $tab,
            'goals' => $goals,
            'tasks' => $tasks,
            'openTasks' => $openTasks,
            'completedTasks' => $completedTasks,
            'todayOccurrences' => $todayOccurrences,
            'familyMembers' => $familyMembers,
            'categories' => TodoItem::CATEGORIES,
            'priorities' => TodoItem::PRIORITIES,
            'statuses' => TodoItem::STATUSES,
            'recurrenceFrequencies' => TodoItem::RECURRENCE_FREQUENCIES,
            'generateModes' => TodoItem::GENERATE_MODES,
            'missedPolicies' => TodoItem::MISSED_POLICIES,
            'rotationTypes' => TodoItem::ROTATION_TYPES,
            'completionTypes' => TodoItem::COMPLETION_TYPES,
            'weekdays' => TodoItem::WEEKDAYS,
            'goalColors' => Goal::COLORS,
            'goalIcons' => Goal::ICONS,
            'goalTargetTypes' => Goal::TARGET_TYPES,
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $goals = Goal::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        $familyMembers = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name', 'asc')
            ->get();

        $preselectedGoalId = $request->get('goal_id');

        // Get family timezone from tenant
        $familyTimezone = $user->tenant->timezone ?? config('app.timezone', 'UTC');

        return view('pages.goals-todo.task-form', [
            'task' => null,
            'goals' => $goals,
            'familyMembers' => $familyMembers,
            'familyTimezone' => $familyTimezone,
            'categories' => TodoItem::CATEGORIES,
            'priorities' => TodoItem::PRIORITIES,
            'recurrenceFrequencies' => TodoItem::RECURRENCE_FREQUENCIES,
            'generateModes' => TodoItem::GENERATE_MODES,
            'missedPolicies' => TodoItem::MISSED_POLICIES,
            'rotationTypes' => TodoItem::ROTATION_TYPES,
            'completionTypes' => TodoItem::COMPLETION_TYPES,
            'weekdays' => TodoItem::WEEKDAYS,
            'proofTypes' => TodoItem::PROOF_TYPES,
            'escalationTargets' => TodoItem::ESCALATION_TARGETS,
            'reminderTimings' => TodoItem::REMINDER_TIMINGS,
            'preselectedGoalId' => $preselectedGoalId,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(TodoItem::CATEGORIES)),
            'priority' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::PRIORITIES)),
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:family_members,id',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string|timezone',
            'goal_id' => 'nullable|exists:goals,id',
            'count_toward_goal' => 'nullable|boolean',
            // Proof fields
            'proof_required' => 'nullable|boolean',
            'proof_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::PROOF_TYPES)),
            // Recurring fields
            'is_recurring' => 'nullable|boolean',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_frequency' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::RECURRENCE_FREQUENCIES)),
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'string|in:' . implode(',', array_keys(TodoItem::WEEKDAYS)),
            'monthly_type' => 'nullable|string|in:day_of_month,day_of_week',
            'monthly_day' => 'nullable|integer|min:1|max:31',
            'monthly_week' => 'nullable|integer|min:1|max:5',
            'monthly_weekday' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::WEEKDAYS)),
            'yearly_month' => 'nullable|integer|min:1|max:12',
            'yearly_day' => 'nullable|integer|min:1|max:31',
            'recurrence_end_type' => 'nullable|string|in:never,on_date,after_occurrences',
            'recurrence_end_date' => 'nullable|date|after:due_date',
            'recurrence_max_occurrences' => 'nullable|integer|min:1|max:999',
            'skip_weekends' => 'nullable|boolean',
            'generate_mode' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::GENERATE_MODES)),
            'schedule_ahead_days' => 'nullable|integer|min:1|max:365',
            'missed_policy' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::MISSED_POLICIES)),
            'rotation_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::ROTATION_TYPES)),
            'completion_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::COMPLETION_TYPES)),
            // Reminder & escalation
            'send_reminder' => 'nullable|boolean',
            'reminder_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::REMINDER_TIMINGS)),
            'escalation_enabled' => 'nullable|boolean',
            'escalation_hours' => 'nullable|integer|min:1|max:168',
            'escalation_target' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::ESCALATION_TARGETS)),
            'escalation_member_id' => 'nullable|exists:family_members,id',
            'digest_mode' => 'nullable|boolean',
            'digest_time' => 'nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $isRecurring = $request->boolean('is_recurring');

        // Build escalation settings array
        $escalationSettings = null;
        if ($request->boolean('escalation_enabled')) {
            $escalationSettings = [
                'enabled' => true,
                'first_escalation_hours' => $request->escalation_hours ?? 24,
                'escalate_to' => $request->escalation_target ?? 'parents',
                'escalate_to_member_id' => $request->escalation_member_id,
                'max_escalations' => 2,
            ];
        }

        $task = TodoItem::create([
            'tenant_id' => $user->tenant_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
            'created_by' => $user->id,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'timezone' => $request->timezone,
            'goal_id' => $request->goal_id,
            'count_toward_goal' => $request->goal_id ? $request->boolean('count_toward_goal', true) : false,
            // Proof fields
            'proof_required' => $request->boolean('proof_required'),
            'proof_type' => $request->proof_required ? $request->proof_type : null,
            // Recurring fields
            'is_recurring' => $isRecurring,
            'recurrence_start_date' => $isRecurring ? $request->recurrence_start_date : null,
            'recurrence_frequency' => $request->recurrence_frequency ?? 'daily',
            'recurrence_interval' => $request->recurrence_interval ?? 1,
            'recurrence_days' => $isRecurring && $request->recurrence_frequency === 'weekly'
                ? $request->recurrence_days
                : null,
            'monthly_type' => $isRecurring && $request->recurrence_frequency === 'monthly'
                ? ($request->monthly_type ?? 'day_of_month')
                : null,
            'monthly_day' => $isRecurring ? $request->monthly_day : null,
            'monthly_week' => $isRecurring ? $request->monthly_week : null,
            'monthly_weekday' => $isRecurring ? $request->monthly_weekday : null,
            'yearly_month' => $isRecurring ? $request->yearly_month : null,
            'yearly_day' => $isRecurring ? $request->yearly_day : null,
            'recurrence_end_type' => $request->recurrence_end_type ?? 'never',
            'recurrence_end_date' => $isRecurring ? $request->recurrence_end_date : null,
            'recurrence_max_occurrences' => $isRecurring ? $request->recurrence_max_occurrences : null,
            'skip_weekends' => $request->boolean('skip_weekends'),
            'generate_mode' => $request->generate_mode ?? 'on_complete',
            'schedule_ahead_days' => $isRecurring && $request->generate_mode === 'schedule_ahead'
                ? ($request->schedule_ahead_days ?? 30)
                : null,
            'missed_policy' => $request->missed_policy ?? 'carryover',
            'rotation_type' => $request->rotation_type ?? 'none',
            'rotation_current_index' => 0,
            'completion_type' => $request->completion_type ?? 'any_one',
            'series_status' => $isRecurring ? 'active' : 'inactive',
            'is_series_template' => $isRecurring,
            // Reminder & escalation
            'send_reminder' => $request->boolean('send_reminder'),
            'reminder_type' => $request->reminder_type,
            'escalation_settings' => $escalationSettings,
            'digest_mode' => $request->boolean('digest_mode'),
            'digest_time' => $request->digest_mode ? $request->digest_time : null,
        ]);

        // Sync assignees
        if ($request->has('assignees')) {
            $task->assignees()->sync($request->assignees);
        }

        // Generate initial occurrences for schedule_ahead mode
        if ($isRecurring && $request->generate_mode === 'schedule_ahead') {
            $this->recurringService->generateScheduledOccurrences(
                $task,
                $request->schedule_ahead_days ?? 30
            );
        }

        return redirect()->route('goals-todo.index', ['tab' => 'todos'])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task (detail view with series info).
     */
    public function show(TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $task->load(['assignees', 'goal', 'comments.user', 'occurrences' => function ($q) {
            $q->orderBy('scheduled_date', 'desc')->limit(20);
        }]);

        // Get upcoming occurrences (next 5)
        $upcomingOccurrences = $task->is_recurring
            ? $task->occurrences()
                ->where('scheduled_date', '>=', now()->startOfDay())
                ->whereIn('status', ['open', 'snoozed'])
                ->orderBy('scheduled_date')
                ->limit(5)
                ->get()
            : collect();

        // Get completed history (last 10)
        $completedHistory = $task->is_recurring
            ? $task->occurrences()
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get()
            : collect();

        // Get missed occurrences
        $missedOccurrences = $task->is_recurring
            ? $task->occurrences()
                ->where('scheduled_date', '<', now()->startOfDay())
                ->where('status', 'open')
                ->orderBy('scheduled_date', 'desc')
                ->get()
            : collect();

        return view('pages.goals-todo.task-show', [
            'task' => $task,
            'upcomingOccurrences' => $upcomingOccurrences,
            'completedHistory' => $completedHistory,
            'missedOccurrences' => $missedOccurrences,
        ]);
    }

    /**
     * Show the form for editing a task.
     */
    public function edit(TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $task->load('assignees');

        $goals = Goal::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name', 'asc')
            ->get();

        // Get family timezone from tenant
        $familyTimezone = $user->tenant->timezone ?? config('app.timezone', 'UTC');

        return view('pages.goals-todo.task-form', [
            'task' => $task,
            'goals' => $goals,
            'familyMembers' => $familyMembers,
            'familyTimezone' => $familyTimezone,
            'categories' => TodoItem::CATEGORIES,
            'priorities' => TodoItem::PRIORITIES,
            'recurrenceFrequencies' => TodoItem::RECURRENCE_FREQUENCIES,
            'generateModes' => TodoItem::GENERATE_MODES,
            'missedPolicies' => TodoItem::MISSED_POLICIES,
            'rotationTypes' => TodoItem::ROTATION_TYPES,
            'completionTypes' => TodoItem::COMPLETION_TYPES,
            'weekdays' => TodoItem::WEEKDAYS,
            'proofTypes' => TodoItem::PROOF_TYPES,
            'escalationTargets' => TodoItem::ESCALATION_TARGETS,
            'reminderTimings' => TodoItem::REMINDER_TIMINGS,
            'preselectedGoalId' => null,
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(TodoItem::CATEGORIES)),
            'priority' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::PRIORITIES)),
            'status' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::STATUSES)),
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:family_members,id',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'goal_id' => 'nullable|exists:goals,id',
            'count_toward_goal' => 'nullable|boolean',
            // Recurring fields (same as store)
            'is_recurring' => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::RECURRENCE_FREQUENCIES)),
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
            'recurrence_days' => 'nullable|array',
            'monthly_type' => 'nullable|string|in:day_of_month,day_of_week',
            'monthly_day' => 'nullable|integer|min:1|max:31',
            'monthly_week' => 'nullable|integer|min:1|max:5',
            'monthly_weekday' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::WEEKDAYS)),
            'yearly_month' => 'nullable|integer|min:1|max:12',
            'yearly_day' => 'nullable|integer|min:1|max:31',
            'recurrence_end_type' => 'nullable|string|in:never,on_date,after_occurrences',
            'recurrence_end_date' => 'nullable|date',
            'recurrence_max_occurrences' => 'nullable|integer|min:1|max:999',
            'skip_weekends' => 'nullable|boolean',
            'generate_mode' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::GENERATE_MODES)),
            'schedule_ahead_days' => 'nullable|integer|min:1|max:365',
            'missed_policy' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::MISSED_POLICIES)),
            'rotation_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::ROTATION_TYPES)),
            'completion_type' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::COMPLETION_TYPES)),
            'send_reminder' => 'nullable|boolean',
            'reminder_type' => 'nullable|string|in:at_time,before',
            'reminder_settings' => 'nullable|array',
            // Edit scope for recurring
            'edit_scope' => 'nullable|string|in:this,future,all',
        ]);

        $isRecurring = $request->boolean('is_recurring');

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? $task->status,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'goal_id' => $request->goal_id,
            'count_toward_goal' => $request->goal_id ? $request->boolean('count_toward_goal', true) : false,
            // Recurring fields
            'is_recurring' => $isRecurring,
            'recurrence_frequency' => $request->recurrence_frequency ?? 'daily',
            'recurrence_interval' => $request->recurrence_interval ?? 1,
            'recurrence_days' => $isRecurring && $request->recurrence_frequency === 'weekly'
                ? $request->recurrence_days
                : null,
            'monthly_type' => $isRecurring && $request->recurrence_frequency === 'monthly'
                ? ($request->monthly_type ?? 'day_of_month')
                : null,
            'monthly_day' => $isRecurring ? $request->monthly_day : null,
            'monthly_week' => $isRecurring ? $request->monthly_week : null,
            'monthly_weekday' => $isRecurring ? $request->monthly_weekday : null,
            'yearly_month' => $isRecurring ? $request->yearly_month : null,
            'yearly_day' => $isRecurring ? $request->yearly_day : null,
            'recurrence_end_type' => $request->recurrence_end_type ?? 'never',
            'recurrence_end_date' => $isRecurring ? $request->recurrence_end_date : null,
            'recurrence_max_occurrences' => $isRecurring ? $request->recurrence_max_occurrences : null,
            'skip_weekends' => $request->boolean('skip_weekends'),
            'generate_mode' => $request->generate_mode ?? 'on_complete',
            'schedule_ahead_days' => $isRecurring && $request->generate_mode === 'schedule_ahead'
                ? ($request->schedule_ahead_days ?? 30)
                : null,
            'missed_policy' => $request->missed_policy ?? 'carryover',
            'rotation_type' => $request->rotation_type ?? 'none',
            'completion_type' => $request->completion_type ?? 'any_one',
            'series_status' => $isRecurring ? 'active' : 'inactive',
            'is_series_template' => $isRecurring,
            'send_reminder' => $request->boolean('send_reminder'),
            'reminder_type' => $request->reminder_type,
            'reminder_settings' => $request->reminder_settings,
        ]);

        // Sync assignees
        $task->assignees()->sync($request->assignees ?? []);

        return redirect()->route('goals-todo.index', ['tab' => 'todos'])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Toggle task status (complete/incomplete).
     */
    public function toggle(TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        if ($task->status === 'completed') {
            $task->markIncomplete();
        } else {
            $task->markComplete($user->id);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $task->fresh()->status,
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    /**
     * Toggle recurring series (pause/resume).
     */
    public function toggleSeries(TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        if (!$task->is_recurring) {
            return response()->json(['error' => 'Not a recurring task'], 400);
        }

        if ($task->series_status === 'paused') {
            $task->resumeSeries();
        } else {
            $task->pauseSeries();
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'series_status' => $task->fresh()->series_status,
            ]);
        }

        return redirect()->back()->with('success', 'Series status updated.');
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $deleteScope = $request->get('scope', 'this');

        if ($task->is_recurring && $deleteScope === 'all') {
            // Delete all occurrences
            $task->occurrences()->delete();
        }

        $task->delete();

        return redirect()->route('goals-todo.index', ['tab' => 'todos'])
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Get occurrences for a recurring task.
     */
    public function getOccurrences(TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $upcoming = $task->occurrences()
            ->where('scheduled_date', '>=', now()->startOfDay())
            ->whereIn('status', ['open', 'snoozed'])
            ->with('assignee')
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();

        $history = $task->occurrences()
            ->where(function ($q) {
                $q->where('scheduled_date', '<', now()->startOfDay())
                    ->orWhereIn('status', ['completed', 'skipped']);
            })
            ->with(['assignee', 'completedByUser'])
            ->orderBy('scheduled_date', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'upcoming' => $upcoming,
            'history' => $history,
            'next_date' => $task->next_occurrence_date?->format('Y-m-d'),
            'upcoming_count' => $task->upcoming_occurrences_count,
            'completed_count' => $task->completed_occurrences_count,
        ]);
    }

    // ==================== OCCURRENCE METHODS ====================

    /**
     * Complete an occurrence.
     */
    public function completeOccurrence(TaskOccurrence $occurrence)
    {
        $user = Auth::user();

        if ($occurrence->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $occurrence->markComplete($user->id);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'completed',
            ]);
        }

        return redirect()->back()->with('success', 'Task completed.');
    }

    /**
     * Reopen a completed occurrence.
     */
    public function reopenOccurrence(TaskOccurrence $occurrence)
    {
        $user = Auth::user();

        if ($occurrence->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $occurrence->markIncomplete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'open',
            ]);
        }

        return redirect()->back()->with('success', 'Task reopened.');
    }

    /**
     * Skip an occurrence.
     */
    public function skipOccurrence(Request $request, TaskOccurrence $occurrence)
    {
        $user = Auth::user();

        if ($occurrence->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $occurrence->skip($request->get('reason'));

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'skipped',
            ]);
        }

        return redirect()->back()->with('success', 'Task skipped.');
    }

    /**
     * Snooze an occurrence.
     */
    public function snoozeOccurrence(Request $request, TaskOccurrence $occurrence)
    {
        $user = Auth::user();

        if ($occurrence->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'until' => 'required|date|after:now',
        ]);

        $occurrence->snooze(Carbon::parse($request->until));

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'snoozed',
                'snoozed_until' => $occurrence->snoozed_until->format('Y-m-d H:i'),
            ]);
        }

        return redirect()->back()->with('success', 'Task snoozed.');
    }

    // ==================== COMMENT METHODS ====================

    /**
     * Store a comment on a task.
     */
    public function storeComment(Request $request, TodoItem $task)
    {
        $user = Auth::user();

        if ($task->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        TodoComment::create([
            'todo_item_id' => $task->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }
}
