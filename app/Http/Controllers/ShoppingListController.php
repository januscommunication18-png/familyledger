<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Models\ShoppingItemHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingListController extends Controller
{
    /**
     * Display a listing of shopping lists.
     */
    public function index()
    {
        $lists = ShoppingList::where('tenant_id', Auth::user()->tenant_id)
            ->withCount(['items', 'items as unchecked_count' => function ($query) {
                $query->where('is_checked', false);
            }])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Create default list if none exists
        if ($lists->isEmpty()) {
            $defaultList = ShoppingList::create([
                'tenant_id' => Auth::user()->tenant_id,
                'name' => 'Family Shopping List',
                'color' => 'emerald',
                'is_default' => true,
            ]);
            $lists = collect([$defaultList]);
        }

        return view('shopping.index', [
            'lists' => $lists,
            'stores' => ShoppingList::STORES,
            'colors' => ShoppingList::COLORS,
        ]);
    }

    /**
     * Store a newly created shopping list.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $list = ShoppingList::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $validated['name'],
            'store' => $validated['store'] ?? null,
            'color' => $validated['color'] ?? 'emerald',
            'is_default' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'list' => $list]);
        }

        return redirect()->route('shopping.show', $list)
            ->with('success', 'Shopping list created successfully.');
    }

    /**
     * Display the specified shopping list.
     */
    public function show(ShoppingList $shoppingList)
    {
        // Ensure user has access
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $items = $shoppingList->items()
            ->with(['addedBy', 'checkedBy'])
            ->orderBy('is_checked')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recently purchased items for suggestions
        $recentItems = ShoppingItemHistory::where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('last_purchased_at')
            ->limit(10)
            ->get();

        // Get frequently bought items
        $frequentItems = ShoppingItemHistory::getFrequentItems(Auth::user()->tenant_id, 10);

        return view('shopping.show', [
            'list' => $shoppingList,
            'items' => $items,
            'categories' => ShoppingItem::CATEGORIES,
            'recentItems' => $recentItems,
            'frequentItems' => $frequentItems,
        ]);
    }

    /**
     * Store mode view for in-store shopping.
     */
    public function storeMode(ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $items = $shoppingList->items()
            ->with(['addedBy'])
            ->orderBy('is_checked')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return view('shopping.store-mode', [
            'list' => $shoppingList,
            'items' => $items,
            'categories' => ShoppingItem::CATEGORIES,
        ]);
    }

    /**
     * Update the specified shopping list.
     */
    public function update(Request $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $shoppingList->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Shopping list updated.');
    }

    /**
     * Remove the specified shopping list.
     */
    public function destroy(ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($shoppingList->is_default) {
            return back()->with('error', 'Cannot delete the default shopping list.');
        }

        $shoppingList->delete();

        return redirect()->route('shopping.index')
            ->with('success', 'Shopping list deleted.');
    }

    /**
     * Add item to shopping list.
     */
    public function addItem(Request $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = ShoppingItem::create([
            'tenant_id' => Auth::user()->tenant_id,
            'shopping_list_id' => $shoppingList->id,
            'name' => $validated['name'],
            'quantity' => $validated['quantity'] ?? null,
            'category' => $validated['category'] ?? 'other',
            'notes' => $validated['notes'] ?? null,
            'added_by' => Auth::id(),
            'sort_order' => $shoppingList->items()->max('sort_order') + 1,
        ]);

        $item->load('addedBy');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'html' => view('shopping.partials.item', ['item' => $item])->render(),
            ]);
        }

        return back()->with('success', 'Item added.');
    }

    /**
     * Toggle item checked status.
     */
    public function toggleItem(Request $request, ShoppingItem $item)
    {
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $item->toggleChecked(Auth::id());

        // Add to history when checked
        if ($item->is_checked) {
            ShoppingItemHistory::recordPurchase(
                Auth::user()->tenant_id,
                $item->name,
                $item->category,
                $item->quantity
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_checked' => $item->is_checked,
            ]);
        }

        return back();
    }

    /**
     * Update an item.
     */
    public function updateItem(Request $request, ShoppingItem $item)
    {
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', 'Item updated.');
    }

    /**
     * Delete an item.
     */
    public function deleteItem(ShoppingItem $item)
    {
        if ($item->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Item removed.');
    }

    /**
     * Clear all checked items from a list.
     */
    public function clearChecked(ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $shoppingList->items()->where('is_checked', true)->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Checked items cleared.');
    }

    /**
     * Get item suggestions based on history.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        $suggestions = ShoppingItemHistory::searchByName(
            Auth::user()->tenant_id,
            $query,
            10
        );

        return response()->json($suggestions);
    }
}
