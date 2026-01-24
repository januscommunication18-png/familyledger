<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Goal;
use App\Models\TodoItem;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    /**
     * Get all goals.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Filter goals by tenant and created by the logged-in user
        $rawGoals = Goal::where('tenant_id', $tenant->id)
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform goals to match mobile app format
        $goals = $rawGoals->map(function ($goal) {
            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'target_date' => $goal->target_date?->format('Y-m-d'),
                'progress' => (float) $goal->current_progress,
                'status' => $goal->status,
                'priority' => 'medium', // Default priority
                'category' => $goal->category,
                'icon' => $goal->icon,
                'color' => $goal->color,
                'milestone_target' => $goal->milestone_target,
                'milestone_current' => $goal->milestone_current,
                'milestone_unit' => $goal->milestone_unit,
                'is_kid_goal' => $goal->is_kid_goal,
                'created_at' => $goal->created_at?->toISOString(),
                'updated_at' => $goal->updated_at?->toISOString(),
            ];
        });

        // Get all tasks for the combined view (filtered by user)
        $rawTasks = TodoItem::where('tenant_id', $tenant->id)
            ->where('created_by', $user->id)
            ->orderByRaw('CASE WHEN status IN ("open", "in_progress") THEN 0 ELSE 1 END')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $tasks = $rawTasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'due_time' => $task->due_time,
                'priority' => $task->priority ?? 'medium',
                'status' => $task->status,
                'is_recurring' => $task->is_recurring,
                'recurrence_pattern' => $task->recurrence_pattern,
                'goal_id' => $task->goal_id,
                'created_at' => $task->created_at?->toISOString(),
                'updated_at' => $task->updated_at?->toISOString(),
            ];
        });

        $activeGoals = $rawGoals->where('status', 'active')->count();
        $openTasks = $rawTasks->whereIn('status', ['open', 'in_progress'])->count();

        return $this->success([
            'goals' => $goals,
            'tasks' => $tasks,
            'active_goals_count' => $activeGoals,
            'open_tasks_count' => $openTasks,
            'stats' => [
                'total' => $rawGoals->count(),
                'active' => $activeGoals,
                'completed' => $rawGoals->where('status', 'completed')->count(),
            ],
        ]);
    }

    /**
     * Get a single goal.
     */
    public function show(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        // Get related tasks for this goal
        $tasks = TodoItem::where('goal_id', $goal->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->due_date?->format('Y-m-d'),
                    'due_time' => $task->due_time,
                    'priority' => $task->priority ?? 'medium',
                    'status' => $task->status,
                    'is_recurring' => $task->is_recurring,
                    'count_toward_goal' => $task->count_toward_goal ?? false,
                ];
            });

        // Count task stats
        $activeTasks = $tasks->whereIn('status', ['open', 'in_progress'])->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        return $this->success([
            'goal' => [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'target_date' => $goal->target_date?->format('Y-m-d'),
                'progress' => (float) $goal->current_progress,
                'status' => $goal->status,
                'priority' => $goal->priority ?? 'medium',
                'category' => $goal->category,
                'category_emoji' => $goal->category_emoji ?? '🎯',
                'category_color' => $goal->category_color ?? 'blue',
                // Goal type info
                'goal_type' => $goal->goal_type ?? 'one_time',
                'habit_frequency' => $goal->habit_frequency,
                'milestone_target' => $goal->milestone_target,
                'milestone_current' => $goal->milestone_current ?? 0,
                'milestone_unit' => $goal->milestone_unit,
                'milestone_progress' => $goal->milestone_target ? round(($goal->milestone_current ?? 0) / $goal->milestone_target * 100, 1) : 0,
                // Assignment
                'assignment_type' => $goal->assignment_type ?? 'family',
                'is_kid_goal' => $goal->is_kid_goal ?? false,
                // Check-in & Rewards
                'check_in_frequency' => $goal->check_in_frequency,
                'rewards_enabled' => $goal->rewards_enabled ?? false,
                'reward_type' => $goal->reward_type,
                'reward_custom' => $goal->reward_custom,
                'reward_claimed' => $goal->reward_claimed ?? false,
                // Stats
                'active_tasks_count' => $activeTasks,
                'completed_tasks_count' => $completedTasks,
                'total_tasks_count' => $tasks->count(),
                // Dates
                'created_at' => $goal->created_at?->format('M d, Y'),
                'updated_at' => $goal->updated_at?->format('M d, Y'),
            ],
            'tasks' => $tasks,
            'milestones' => [],
        ]);
    }

    /**
     * Store a new goal.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            // Goal type
            'goal_type' => 'nullable|in:one_time,habit,milestone',
            'habit_frequency' => 'nullable|in:daily,weekly,monthly',
            'milestone_target' => 'nullable|integer|min:1',
            'milestone_unit' => 'nullable|string|max:50',
            // Assignment
            'assignment_type' => 'nullable|in:family,parents,kids,individual',
            'is_kid_goal' => 'nullable|boolean',
            // Check-ins & Rewards
            'check_in_frequency' => 'nullable|in:daily,weekly,monthly',
            'rewards_enabled' => 'nullable|boolean',
            'reward_type' => 'nullable|in:sticker,points,treat,outing,custom',
            'reward_custom' => 'nullable|string|max:255',
            // Kid settings
            'visible_to_kids' => 'nullable|boolean',
            'kids_can_update' => 'nullable|boolean',
        ]);

        $goalData = [
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'category' => $validated['category'] ?? 'personal_growth',
            'status' => 'active',
            'current_progress' => 0,
            // Goal type
            'goal_type' => $validated['goal_type'] ?? 'one_time',
            'habit_frequency' => $validated['habit_frequency'] ?? null,
            'milestone_target' => $validated['milestone_target'] ?? null,
            'milestone_unit' => $validated['milestone_unit'] ?? null,
            // Assignment
            'assignment_type' => $validated['assignment_type'] ?? 'family',
            'is_kid_goal' => $validated['is_kid_goal'] ?? false,
            // Check-ins & Rewards
            'check_in_frequency' => $validated['check_in_frequency'] ?? null,
            'rewards_enabled' => $validated['rewards_enabled'] ?? false,
            'reward_type' => $validated['reward_type'] ?? null,
            'reward_custom' => $validated['reward_custom'] ?? null,
            // Kid settings
            'visible_to_kids' => $validated['visible_to_kids'] ?? true,
            'kids_can_update' => $validated['kids_can_update'] ?? false,
        ];

        $goal = Goal::create($goalData);

        return $this->success([
            'goal' => $goal,
            'message' => 'Goal created successfully',
        ], 201);
    }

    /**
     * Update a goal.
     */
    public function update(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            'status' => 'sometimes|in:active,completed,paused',
            'progress' => 'sometimes|numeric|min:0|max:100',
        ]);

        if (isset($validated['progress'])) {
            $validated['current_progress'] = $validated['progress'];
            unset($validated['progress']);
        }

        $goal->update($validated);

        return $this->success([
            'goal' => $goal->fresh(),
            'message' => 'Goal updated successfully',
        ]);
    }

    /**
     * Delete a goal.
     */
    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $goal->delete();

        return $this->success([
            'message' => 'Goal deleted successfully',
        ]);
    }

    /**
     * Pause a goal (uses 'archived' status).
     */
    public function pause(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $goal->update(['status' => 'archived']);

        return $this->success([
            'goal' => $goal->fresh(),
            'message' => 'Goal paused successfully',
        ]);
    }

    /**
     * Resume a goal.
     */
    public function resume(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $goal->update(['status' => 'active']);

        return $this->success([
            'goal' => $goal->fresh(),
            'message' => 'Goal resumed successfully',
        ]);
    }

    /**
     * Complete a goal (uses 'done' status).
     */
    public function complete(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();

        if ($goal->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $goal->update([
            'status' => 'done',
            'current_progress' => 100,
        ]);

        return $this->success([
            'goal' => $goal->fresh(),
            'message' => 'Goal completed successfully',
        ]);
    }

    /**
     * Toggle a task's completion status.
     */
    public function toggleTask(Request $request, TodoItem $task): JsonResponse
    {
        $user = $request->user();

        if ($task->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $newStatus = $task->status === 'completed' ? 'open' : 'completed';
        $task->update(['status' => $newStatus]);

        return $this->success([
            'task' => $task->fresh(),
            'message' => 'Task updated successfully',
        ]);
    }

    /**
     * Get all tasks/todos.
     */
    public function tasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Filter tasks by tenant and created by the logged-in user
        $rawTasks = TodoItem::where('tenant_id', $tenant->id)
            ->where('created_by', $user->id)
            ->with(['todoList', 'assignedTo'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Transform tasks to match mobile app format
        $tasks = $rawTasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'due_time' => $task->due_time,
                'priority' => $task->priority ?? 'medium',
                'status' => $task->status,
                'is_recurring' => $task->is_recurring,
                'recurrence_pattern' => $task->recurrence_pattern,
                'assigned_to' => $task->assignedTo?->name,
                'goal_id' => $task->goal_id,
                'list_name' => $task->todoList?->name,
                'created_at' => $task->created_at?->toISOString(),
                'updated_at' => $task->updated_at?->toISOString(),
            ];
        });

        $pendingTasks = $rawTasks->whereIn('status', ['open', 'in_progress'])->count();
        $completedTasks = $rawTasks->where('status', 'completed')->count();
        $overdueTasks = $rawTasks->filter(function ($task) {
            return $task->due_date && $task->due_date < now() && in_array($task->status, ['open', 'in_progress']);
        })->count();

        return $this->success([
            'tasks' => $tasks,
            'stats' => [
                'total' => $rawTasks->count(),
                'pending' => $pendingTasks,
                'completed' => $completedTasks,
                'overdue' => $overdueTasks,
            ],
        ]);
    }

    /**
     * Store a new task.
     */
    public function storeTask(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'goal_id' => 'nullable|exists:goals,id',
            'is_recurring' => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
        ]);

        $taskData = [
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'home_chores',
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'goal_id' => $validated['goal_id'] ?? null,
            'status' => 'open',
            'is_recurring' => $validated['is_recurring'] ?? false,
        ];

        if (!empty($validated['is_recurring'])) {
            $taskData['recurrence_frequency'] = $validated['recurrence_frequency'] ?? 'daily';
            $taskData['recurrence_interval'] = $validated['recurrence_interval'] ?? 1;
        }

        $task = TodoItem::create($taskData);

        return $this->success([
            'task' => $task,
            'message' => 'Task created successfully',
        ], 201);
    }

    /**
     * Get a single task.
     */
    public function showTask(Request $request, TodoItem $task): JsonResponse
    {
        $user = $request->user();

        if ($task->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        return $this->success([
            'task' => $task->load(['todoList', 'assignedTo', 'comments']),
        ]);
    }

    /**
     * Delete a task.
     */
    public function destroyTask(Request $request, TodoItem $task): JsonResponse
    {
        $user = $request->user();

        if ($task->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $task->delete();

        return $this->success([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Snooze a task.
     */
    public function snoozeTask(Request $request, TodoItem $task): JsonResponse
    {
        $user = $request->user();

        if ($task->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'until' => 'required|date',
        ]);

        $task->update([
            'due_date' => $validated['until'],
            'status' => 'pending',
        ]);

        return $this->success([
            'task' => $task->fresh(),
            'message' => 'Task snoozed successfully',
        ]);
    }
}
