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

        $rawGoals = Goal::where('tenant_id', $tenant->id)
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

        // Get tasks as well for the combined view
        $rawTasks = TodoItem::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->take(20)
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
        $openTasks = $rawTasks->count();

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

        return $this->success([
            'goal' => $goal->load('checkIns'),
        ]);
    }

    /**
     * Get all tasks/todos.
     */
    public function tasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawTasks = TodoItem::where('tenant_id', $tenant->id)
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

        $pendingTasks = $rawTasks->whereIn('status', ['pending', 'in_progress'])->count();
        $completedTasks = $rawTasks->where('status', 'completed')->count();
        $overdueTasks = $rawTasks->filter(function ($task) {
            return $task->due_date && $task->due_date < now() && in_array($task->status, ['pending', 'in_progress']);
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
}
