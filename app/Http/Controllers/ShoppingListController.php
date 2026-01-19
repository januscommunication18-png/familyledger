<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Models\ShoppingItemHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ShoppingListController extends Controller
{
    /**
     * Check if the current user has access to a shopping list.
     * User has access if they own the list OR if the list is shared with them.
     */
    private function userHasAccess(ShoppingList $shoppingList): bool
    {
        $user = Auth::user();

        // User owns the list
        if ($shoppingList->tenant_id === $user->tenant_id) {
            return true;
        }

        // Check if list is shared with user via their linked family member
        $linkedMember = FamilyMember::where('linked_user_id', $user->id)->first();
        if ($linkedMember) {
            return $shoppingList->sharedWithMembers()
                ->where('family_member_id', $linkedMember->id)
                ->exists();
        }

        return false;
    }

    /**
     * Check if user owns the shopping list (not just has access).
     */
    private function userOwns(ShoppingList $shoppingList): bool
    {
        return $shoppingList->tenant_id === Auth::user()->tenant_id;
    }

    /**
     * Display a listing of shopping lists.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's own lists
        $ownLists = ShoppingList::where('tenant_id', $user->tenant_id)
            ->withCount(['items', 'items as unchecked_count' => function ($query) {
                $query->where('is_checked', false);
            }])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Get lists shared with this user (via their linked family member)
        $linkedMember = FamilyMember::where('linked_user_id', $user->id)->first();
        $sharedLists = collect();

        if ($linkedMember) {
            $sharedLists = ShoppingList::whereHas('sharedWithMembers', function ($query) use ($linkedMember) {
                $query->where('family_member_id', $linkedMember->id);
            })
            ->where('tenant_id', '!=', $user->tenant_id) // Exclude own tenant's lists (already shown)
            ->withCount(['items', 'items as unchecked_count' => function ($query) {
                $query->where('is_checked', false);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($list) {
                $list->is_shared = true;
                return $list;
            });
        }

        // Merge own lists and shared lists
        $lists = $ownLists->merge($sharedLists);

        // Create default list if no own lists exist
        if ($ownLists->isEmpty()) {
            $defaultList = ShoppingList::create([
                'tenant_id' => $user->tenant_id,
                'name' => 'Family Shopping List',
                'color' => 'emerald',
                'is_default' => true,
            ]);
            $lists = collect([$defaultList])->merge($sharedLists);
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
            'recurring' => 'nullable|string|max:20',
        ]);

        $list = ShoppingList::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $validated['name'],
            'store' => $validated['store'] ?? null,
            'color' => $validated['color'] ?? 'emerald',
            'recurring' => $validated['recurring'] ?? null,
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
        // Ensure user has access (owns or has shared access)
        if (!$this->userHasAccess($shoppingList)) {
            abort(403);
        }

        $isOwner = $this->userOwns($shoppingList);

        $items = $shoppingList->items()
            ->with(['addedBy', 'checkedBy'])
            ->orderBy('is_checked')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recently purchased items for suggestions (only for owner)
        $recentItems = collect();
        $frequentItems = collect();
        $familyMembers = collect();
        $sharedWithMembers = [];

        if ($isOwner) {
            $recentItems = ShoppingItemHistory::where('tenant_id', Auth::user()->tenant_id)
                ->orderByDesc('last_purchased_at')
                ->limit(10)
                ->get();

            $frequentItems = ShoppingItemHistory::getFrequentItems(Auth::user()->tenant_id, 10);

            // Get family members for sharing
            $familyMembers = FamilyMember::where('tenant_id', Auth::user()->tenant_id)->get();

            // Get shared members IDs
            $sharedWithMembers = $shoppingList->sharedWithMembers()->pluck('family_member_id')->toArray();
        }

        return view('shopping.show', [
            'list' => $shoppingList,
            'items' => $items,
            'categories' => ShoppingItem::CATEGORIES,
            'recentItems' => $recentItems,
            'frequentItems' => $frequentItems,
            'familyMembers' => $familyMembers,
            'sharedWithMembers' => $sharedWithMembers,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Store mode view for in-store shopping.
     */
    public function storeMode(ShoppingList $shoppingList)
    {
        if (!$this->userHasAccess($shoppingList)) {
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
            'isOwner' => $this->userOwns($shoppingList),
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
        if (!$this->userHasAccess($shoppingList)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Use the list's tenant_id (not the current user's) so items belong to the list owner
        $item = ShoppingItem::create([
            'tenant_id' => $shoppingList->tenant_id,
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
        // Check access via the shopping list
        if (!$this->userHasAccess($item->shoppingList)) {
            abort(403);
        }

        $item->toggleChecked(Auth::id());

        // Add to history when checked (record on the list owner's tenant)
        if ($item->is_checked) {
            ShoppingItemHistory::recordPurchase(
                $item->tenant_id,
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
        // Check access via the shopping list
        if (!$this->userHasAccess($item->shoppingList)) {
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
        // Check access via the shopping list
        if (!$this->userHasAccess($item->shoppingList)) {
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
        if (!$this->userHasAccess($shoppingList)) {
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

    /**
     * Share shopping list with family members.
     */
    public function share(Request $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'members' => 'nullable|array',
            'members.*' => 'exists:family_members,id',
        ]);

        // Sync the shared members
        $memberIds = $validated['members'] ?? [];
        $shoppingList->sharedWithMembers()->sync($memberIds);

        return back()->with('success', 'Shopping list sharing updated successfully.');
    }

    /**
     * Email shopping list to selected members.
     */
    public function email(Request $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => 'exists:family_members,id',
            'message' => 'nullable|string|max:500',
        ]);

        // Get family members with emails
        $members = FamilyMember::whereIn('id', $validated['members'])
            ->whereNotNull('email')
            ->get();

        if ($members->isEmpty()) {
            return back()->with('error', 'No members with valid email addresses selected.');
        }

        // Get items for the list
        $items = $shoppingList->items()
            ->where('is_checked', false)
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        // Send email to each member
        foreach ($members as $member) {
            Mail::send('emails.shopping-list', [
                'list' => $shoppingList,
                'items' => $items,
                'categories' => ShoppingItem::CATEGORIES,
                'member' => $member,
                'senderName' => Auth::user()->name,
                'personalMessage' => $validated['message'] ?? null,
            ], function ($mail) use ($member, $shoppingList) {
                $mail->to($member->email, $member->full_name)
                    ->subject('Shopping List: ' . $shoppingList->name);
            });
        }

        return back()->with('success', 'Shopping list emailed to ' . $members->count() . ' member(s).');
    }
}
