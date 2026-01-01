<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\ShoppingItem;
use App\Models\ShoppingItemHistory;
use App\Models\ShoppingList;
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
        $tab = $request->get('tab', 'todos');

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

        // Get shopping lists with items
        $shoppingLists = ShoppingList::where('tenant_id', $tenantId)
            ->with(['items' => function ($query) {
                $query->orderBy('is_checked', 'asc')
                    ->orderBy('category', 'asc')
                    ->orderBy('sort_order', 'asc');
            }, 'items.addedBy'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get family members for assignment
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name', 'asc')
            ->get();

        // Get frequently bought items for suggestions
        $frequentItems = ShoppingItemHistory::getFrequentItems($tenantId, 10);

        // Get active list IDs from request or defaults
        $activeTodoListId = $request->get('todo_list', $todoLists->first()?->id);
        $activeShoppingListId = $request->get('shopping_list', $shoppingLists->first()?->id);

        return view('pages.lists.index', [
            'tab' => $tab,
            'todoLists' => $todoLists,
            'shoppingLists' => $shoppingLists,
            'familyMembers' => $familyMembers,
            'frequentItems' => $frequentItems,
            'activeTodoListId' => $activeTodoListId,
            'activeShoppingListId' => $activeShoppingListId,
            'todoCategories' => TodoItem::CATEGORIES,
            'todoPriorities' => TodoItem::PRIORITIES,
            'todoStatuses' => TodoItem::STATUSES,
            'recurrencePatterns' => TodoItem::RECURRENCE_PATTERNS,
            'shoppingCategories' => ShoppingItem::CATEGORIES,
            'stores' => ShoppingList::STORES,
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

    // ==================== SHOPPING LIST METHODS ====================

    /**
     * Show form to create a new shopping list.
     */
    public function createShoppingList()
    {
        return view('pages.lists.shopping-list-form', [
            'stores' => ShoppingList::STORES,
            'listColors' => TodoList::COLORS,
        ]);
    }

    /**
     * Store a new shopping list.
     */
    public function storeShoppingList(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'store' => 'nullable|string|in:' . implode(',', array_keys(ShoppingList::STORES)),
            'color' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        ShoppingList::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'store' => $request->store,
            'color' => $request->color ?? 'emerald',
        ]);

        return redirect()->route('lists.index', ['tab' => 'shopping'])
            ->with('success', 'Shopping list created successfully.');
    }

    /**
     * Show form to create a new shopping item.
     */
    public function createShoppingItem(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $shoppingListId = $request->get('shopping_list');
        $shoppingList = ShoppingList::where('tenant_id', $tenantId)
            ->with(['items' => function ($query) {
                $query->orderBy('is_checked', 'asc')
                    ->orderBy('category', 'asc')
                    ->orderBy('name', 'asc');
            }])
            ->findOrFail($shoppingListId);

        $frequentItems = ShoppingItemHistory::getFrequentItems($tenantId, 10);

        // Get existing item names for duplicate check
        $existingItemNames = $shoppingList->items->pluck('name')->map(fn($n) => strtolower($n))->toArray();

        return view('pages.lists.shopping-form', [
            'shoppingList' => $shoppingList,
            'categories' => ShoppingItem::CATEGORIES,
            'frequentItems' => $frequentItems,
            'existingItemNames' => $existingItemNames,
        ]);
    }

    /**
     * Store a new shopping item.
     */
    public function storeShoppingItem(Request $request)
    {
        $request->validate([
            'shopping_list_id' => 'required|exists:shopping_lists,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|in:' . implode(',', array_keys(ShoppingItem::CATEGORIES)),
            'quantity' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Check for duplicate item in the same list
        $exists = ShoppingItem::where('shopping_list_id', $request->shopping_list_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'This item is already in your list.']);
        }

        ShoppingItem::create([
            'tenant_id' => $user->tenant_id,
            'shopping_list_id' => $request->shopping_list_id,
            'name' => $request->name,
            'category' => $request->category ?? 'other',
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'added_by' => $user->id,
        ]);

        if ($request->has('add_another')) {
            return redirect()->route('lists.shopping.items.create', ['shopping_list' => $request->shopping_list_id])
                ->with('success', 'Item added! Add another one.');
        }

        return redirect()->route('lists.index', ['tab' => 'shopping', 'shopping_list' => $request->shopping_list_id])
            ->with('success', 'Item added to list.');
    }

    /**
     * Update a shopping item.
     */
    public function updateShoppingItem(Request $request, ShoppingItem $item)
    {
        $user = Auth::user();

        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|in:' . implode(',', array_keys(ShoppingItem::CATEGORIES)),
            'quantity' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate item in the same list (excluding current item)
        $exists = ShoppingItem::where('shopping_list_id', $item->shopping_list_id)
            ->where('id', '!=', $item->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'This item is already in your list.']);
        }

        $item->update([
            'name' => $request->name,
            'category' => $request->category ?? 'other',
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        return redirect()->route('lists.shopping.items.create', ['shopping_list' => $item->shopping_list_id])
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Toggle shopping item checked status.
     */
    public function toggleShoppingItem(ShoppingItem $item)
    {
        $user = Auth::user();

        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== $user->tenant_id) {
            abort(403);
        }
        $wasChecked = $item->is_checked;

        $item->toggleChecked($user->id);

        // If just checked (purchased), record in history
        if (!$wasChecked) {
            ShoppingItemHistory::recordPurchase(
                $user->tenant_id,
                $item->name,
                $item->category,
                $item->quantity
            );
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'is_checked' => $item->fresh()->is_checked]);
        }

        return redirect()->back()->with('success', 'Item updated.');
    }

    /**
     * Delete a shopping item.
     */
    public function destroyShoppingItem(ShoppingItem $item)
    {
        // Ensure item belongs to user's tenant
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $listId = $item->shopping_list_id;
        $item->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('lists.index', ['tab' => 'shopping', 'shopping_list' => $listId])
            ->with('success', 'Item removed from list.');
    }

    /**
     * Clear all checked items from a shopping list.
     */
    public function clearCheckedItems(ShoppingList $list)
    {
        // Ensure list belongs to user's tenant
        if ($list->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $list->items()->where('is_checked', true)->delete();

        return redirect()->route('lists.index', ['tab' => 'shopping', 'shopping_list' => $list->id])
            ->with('success', 'Checked items cleared.');
    }

    /**
     * Get item suggestions from history.
     */
    public function getItemSuggestions(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            $items = ShoppingItemHistory::getFrequentItems($user->tenant_id, 10);
        } else {
            $items = ShoppingItemHistory::searchByName($user->tenant_id, $query, 10);
        }

        return response()->json($items->map(function ($item) {
            return [
                'name' => $item->name,
                'category' => $item->category,
                'quantity' => $item->quantity,
                'purchase_count' => $item->purchase_count,
            ];
        }));
    }
}
