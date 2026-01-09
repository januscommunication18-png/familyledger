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
}
