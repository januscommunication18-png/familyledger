<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\TodoComment;
use App\Models\TodoItem;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{
    /**
     * Display the lists page with tabs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get todo lists with items
        $todoLists = TodoList::where('tenant_id', $tenantId)
            ->with(['items' => function ($query) {
                $query->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
                    ->orderBy('priority', 'desc')
                    ->orderBy('due_date', 'asc');
            }, 'items.assignees', 'items.createdBy', 'items.comments'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get family members for assignment
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name', 'asc')
            ->get();

        // Get active list ID from request or default
        $activeTodoListId = $request->get('todo_list', $todoLists->first()?->id);

        return view('pages.lists.index', [
            'todoLists' => $todoLists,
            'familyMembers' => $familyMembers,
            'activeTodoListId' => $activeTodoListId,
            'todoCategories' => TodoItem::CATEGORIES,
            'todoPriorities' => TodoItem::PRIORITIES,
            'todoStatuses' => TodoItem::STATUSES,
            'recurrencePatterns' => TodoItem::RECURRENCE_PATTERNS,
            'listColors' => TodoList::COLORS,
        ]);
    }

    // ==================== TODO LIST METHODS ====================

    /**
     * Show form to create a new todo list.
     */
    public function createTodoList()
    {
        return view('pages.lists.todo-list-form', [
            'listColors' => TodoList::COLORS,
        ]);
    }

    /**
     * Store a new todo list.
     */
    public function storeTodoList(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        TodoList::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'color' => $request->color ?? 'violet',
        ]);

        return redirect()->route('lists.index', ['tab' => 'todos'])
            ->with('success', 'List created successfully.');
    }

    /**
     * Show form to create a new todo item.
     */
    public function createTodoItem(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $todoListId = $request->get('todo_list');
        $todoList = TodoList::where('tenant_id', $tenantId)->findOrFail($todoListId);

        $familyMembers = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name', 'asc')
            ->get();

        return view('pages.lists.todo-form', [
            'todoList' => $todoList,
            'item' => null,
            'familyMembers' => $familyMembers,
            'categories' => TodoItem::CATEGORIES,
            'priorities' => TodoItem::PRIORITIES,
            'statuses' => TodoItem::STATUSES,
        ]);
    }

    /**
     * Show form to edit a todo item.
     */
    public function editTodoItem(TodoItem $item)
    {
        $user = Auth::user();

        if ($item->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        // Load assignees relationship
        $item->load('assignees');

        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name', 'asc')
            ->get();

        return view('pages.lists.todo-form', [
            'todoList' => $item->todoList,
            'item' => $item,
            'familyMembers' => $familyMembers,
            'categories' => TodoItem::CATEGORIES,
            'priorities' => TodoItem::PRIORITIES,
            'statuses' => TodoItem::STATUSES,
        ]);
    }

    /**
     * Store a new todo item.
     */
    public function storeTodoItem(Request $request)
    {
        $request->validate([
            'todo_list_id' => 'required|exists:todo_lists,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(TodoItem::CATEGORIES)),
            'priority' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::PRIORITIES)),
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:family_members,id',
            'due_date' => 'nullable|date',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string|in:' . implode(',', array_keys(TodoItem::RECURRENCE_PATTERNS)),
        ]);

        $user = Auth::user();

        $item = TodoItem::create([
            'tenant_id' => $user->tenant_id,
            'todo_list_id' => $request->todo_list_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority ?? 'medium',
            'created_by' => $user->id,
            'due_date' => $request->due_date,
            'is_recurring' => $request->is_recurring ?? false,
            'recurrence_pattern' => $request->recurrence_pattern,
        ]);

        // Sync assignees
        if ($request->has('assignees')) {
            $item->assignees()->sync($request->assignees);
        }

        return redirect()->route('lists.index', ['tab' => 'todos', 'todo_list' => $request->todo_list_id])
            ->with('success', 'Task added successfully.');
    }

    /**
     * Update a todo item.
     */
    public function updateTodoItem(Request $request, TodoItem $item)
    {
        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== Auth::user()->tenant_id) {
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
        ]);

        $item->update($request->only([
            'title', 'description', 'category', 'priority', 'status', 'due_date'
        ]));

        // Sync assignees
        $item->assignees()->sync($request->assignees ?? []);

        return redirect()->route('lists.index', ['tab' => 'todos', 'todo_list' => $item->todo_list_id])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Toggle todo item status (complete/incomplete).
     */
    public function toggleTodoItem(TodoItem $item)
    {
        $user = Auth::user();

        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        if ($item->status === 'completed') {
            $item->markIncomplete();
        } else {
            $item->markComplete($user->id);
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'status' => $item->fresh()->status]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    /**
     * Delete a todo item.
     */
    public function destroyTodoItem(TodoItem $item)
    {
        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $listId = $item->todo_list_id;
        $item->delete();

        return redirect()->route('lists.index', ['tab' => 'todos', 'todo_list' => $listId])
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Store a comment on a todo item.
     */
    public function storeTodoComment(Request $request, TodoItem $item)
    {
        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        TodoComment::create([
            'todo_item_id' => $item->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }
}
