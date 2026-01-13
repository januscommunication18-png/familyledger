<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShoppingController extends Controller
{
    /**
     * Get all shopping lists.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawLists = ShoppingList::where('tenant_id', $tenant->id)
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform lists for mobile
        $lists = $rawLists->map(function ($list) {
            $totalItems = $list->items->count();
            $purchasedItems = $list->items->where('is_purchased', true)->count();
            $uncheckedItems = $totalItems - $purchasedItems;

            return [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'store_name' => $list->store_name,
                'color' => $list->color ?? 'emerald',
                'icon' => $list->icon ?? '🛒',
                'is_default' => $list->is_default ?? false,
                'items_count' => $totalItems,
                'purchased_count' => $purchasedItems,
                'unchecked_count' => $uncheckedItems,
                'progress_percentage' => $totalItems > 0 ? round(($purchasedItems / $totalItems) * 100) : 0,
                'created_at' => $list->created_at?->format('M d, Y'),
                'updated_at' => $list->updated_at?->format('M d, Y'),
            ];
        });

        // Calculate totals
        $totalItems = $rawLists->sum(fn($l) => $l->items->count());
        $completedItems = $rawLists->sum(fn($l) => $l->items->where('is_purchased', true)->count());

        return $this->success([
            'lists' => $lists,
            'stats' => [
                'total_lists' => $lists->count(),
                'total_items' => $totalItems,
                'completed_items' => $completedItems,
                'pending_items' => $totalItems - $completedItems,
            ],
        ]);
    }

    /**
     * Get a single shopping list.
     */
    public function show(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $list->load(['items']);

        // Transform items
        $items = $list->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'price' => $item->price,
                'formatted_price' => $item->price ? '$' . number_format($item->price, 2) : null,
                'category' => $item->category,
                'notes' => $item->notes,
                'is_checked' => $item->is_purchased ?? false,
                'is_purchased' => $item->is_purchased ?? false,
                'priority' => $item->priority ?? 'normal',
            ];
        })->sortBy([
            ['is_purchased', 'asc'],
            ['priority', 'desc'],
            ['name', 'asc'],
        ])->values();

        $totalItems = $items->count();
        $purchasedItems = $items->where('is_purchased', true)->count();

        return $this->success([
            'list' => [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'store_name' => $list->store_name,
                'color' => $list->color ?? 'emerald',
                'icon' => $list->icon ?? '🛒',
                'is_default' => $list->is_default ?? false,
            ],
            'items' => $items,
            'stats' => [
                'total_items' => $totalItems,
                'purchased_items' => $purchasedItems,
                'pending_items' => $totalItems - $purchasedItems,
                'progress_percentage' => $totalItems > 0 ? round(($purchasedItems / $totalItems) * 100) : 0,
            ],
        ]);
    }

    /**
     * Get items for a shopping list.
     */
    public function items(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $items = $list->items()
            ->orderBy('is_purchased', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'items' => $items,
            'total' => $items->count(),
            'purchased' => $items->where('is_purchased', true)->count(),
        ]);
    }

    /**
     * Create a new shopping list.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_name' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:10',
        ]);

        $list = ShoppingList::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'store_name' => $validated['store_name'] ?? null,
            'color' => $validated['color'] ?? 'emerald',
            'icon' => $validated['icon'] ?? '🛒',
            'is_default' => false,
        ]);

        return $this->success([
            'list' => [
                'id' => $list->id,
                'name' => $list->name,
                'store_name' => $list->store_name,
                'color' => $list->color,
                'icon' => $list->icon,
                'is_default' => $list->is_default,
                'items_count' => 0,
                'purchased_count' => 0,
                'progress_percentage' => 0,
            ],
        ], 'Shopping list created successfully', 201);
    }

    /**
     * Update a shopping list.
     */
    public function update(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'store_name' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:10',
        ]);

        $list->update($validated);

        return $this->success(['list' => $list], 'Shopping list updated');
    }

    /**
     * Delete a shopping list.
     */
    public function destroy(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        if ($list->is_default) {
            return $this->error('Cannot delete the default shopping list', 422);
        }

        $list->delete();

        return $this->success(null, 'Shopping list deleted');
    }

    /**
     * Add item to shopping list.
     */
    public function addItem(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = ShoppingItem::create([
            'tenant_id' => $user->tenant_id,
            'shopping_list_id' => $list->id,
            'name' => $validated['name'],
            'quantity' => $validated['quantity'] ?? 1,
            'category' => $validated['category'] ?? 'other',
            'notes' => $validated['notes'] ?? null,
            'is_checked' => false,
            'added_by' => $user->id,
        ]);

        return $this->success([
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'category' => $item->category,
                'notes' => $item->notes,
                'is_checked' => false,
                'is_purchased' => false,
            ],
        ], 'Item added successfully', 201);
    }

    /**
     * Update a shopping item.
     */
    public function updateItem(Request $request, ShoppingList $list, ShoppingItem $item): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id || $item->shopping_list_id !== $list->id) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->update($validated);

        return $this->success(['item' => $item], 'Item updated');
    }

    /**
     * Delete a shopping item.
     */
    public function deleteItem(Request $request, ShoppingList $list, ShoppingItem $item): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id || $item->shopping_list_id !== $list->id) {
            return $this->forbidden();
        }

        $item->delete();

        return $this->success(null, 'Item deleted');
    }

    /**
     * Toggle item checked status.
     */
    public function toggleItem(Request $request, ShoppingList $list, ShoppingItem $item): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id || $item->shopping_list_id !== $list->id) {
            return $this->forbidden();
        }

        $item->is_checked = !$item->is_checked;
        $item->checked_by = $item->is_checked ? $user->id : null;
        $item->checked_at = $item->is_checked ? now() : null;
        $item->save();

        return $this->success([
            'item' => [
                'id' => $item->id,
                'is_checked' => $item->is_checked,
                'is_purchased' => $item->is_checked,
            ],
        ], $item->is_checked ? 'Item checked' : 'Item unchecked');
    }

    /**
     * Clear all checked items from a list.
     */
    public function clearChecked(Request $request, ShoppingList $list): JsonResponse
    {
        $user = $request->user();

        if ($list->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $count = $list->items()->where('is_checked', true)->delete();

        return $this->success([
            'deleted_count' => $count,
        ], 'Checked items cleared');
    }
}
