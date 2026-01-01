<div class="space-y-4">
    <!-- Header with List Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold">Shopping List</h2>
            @if($shoppingLists->count() > 1)
                <select id="shoppingListSelector" onchange="window.location.href='{{ route('lists.index', ['tab' => 'shopping']) }}&shopping_list=' + this.value" class="select select-bordered select-sm">
                    @foreach($shoppingLists as $list)
                        <option value="{{ $list->id }}" {{ $activeShoppingListId == $list->id ? 'selected' : '' }}>
                            {{ $list->name }}
                            @if($list->store_name)
                                ({{ $list->store_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
            @elseif($shoppingLists->count() === 1)
                <span class="badge badge-lg badge-ghost">{{ $shoppingLists->first()->name }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('lists.shopping.create') }}" class="btn btn-ghost btn-sm gap-1">
                <span class="icon-[tabler--plus] size-4"></span>
                New List
            </a>
            @if($shoppingLists->count() > 0)
                <a href="{{ route('lists.shopping.items.create', ['shopping_list' => $activeShoppingListId]) }}" class="btn btn-primary btn-sm gap-1">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Item
                </a>
            @endif
        </div>
    </div>

    @if($shoppingLists->count() === 0)
        <!-- Empty state - no lists -->
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--shopping-cart] size-8 text-emerald-600"></span>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No shopping lists yet</h3>
            <p class="text-slate-500 mb-4">Create your first shopping list to start organizing your grocery runs.</p>
            <a href="{{ route('lists.shopping.create') }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Create First List
            </a>
        </div>
    @else
        @php
            $activeList = $shoppingLists->firstWhere('id', $activeShoppingListId) ?? $shoppingLists->first();
            $items = $activeList ? $activeList->items : collect();
            $uncheckedItems = $items->where('is_checked', false);
            $checkedItems = $items->where('is_checked', true);
            $groupedItems = $uncheckedItems->groupBy('category');
        @endphp

        @if($activeList && $activeList->store_name)
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span class="{{ $activeList->store_icon }} size-4"></span>
                {{ $activeList->store_name }}
            </div>
        @endif

        @if($items->count() === 0)
            <!-- Empty state - no items -->
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--basket] size-8 text-emerald-600"></span>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Your list is empty</h3>
                <p class="text-slate-500 mb-4">Add items you need to buy.</p>
                <a href="{{ route('lists.shopping.items.create', ['shopping_list' => $activeList->id]) }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add First Item
                </a>
            </div>
        @else
            <!-- Item Stats -->
            <div class="flex items-center justify-between">
                <div class="flex gap-4 text-sm">
                    <span class="text-slate-500">
                        <span class="font-medium text-slate-700">{{ $uncheckedItems->count() }}</span> items to get
                    </span>
                    @if($checkedItems->count() > 0)
                        <span class="text-slate-500">
                            <span class="font-medium text-emerald-600">{{ $checkedItems->count() }}</span> in cart
                        </span>
                    @endif
                </div>
                @if($checkedItems->count() > 0)
                    <form action="{{ route('lists.shopping.clear-checked', $activeList) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-slate-500 hover:text-error gap-1">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Clear checked
                        </button>
                    </form>
                @endif
            </div>

            <!-- Items grouped by category -->
            <div class="space-y-4">
                @foreach($groupedItems as $category => $categoryItems)
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="{{ \App\Models\ShoppingItem::CATEGORIES[$category] ?? 'Other' }}"></span>
                            <h3 class="text-sm font-medium text-slate-700">{{ \App\Models\ShoppingItem::CATEGORIES[$category] ?? 'Other' }}</h3>
                            <span class="text-xs text-slate-400">({{ $categoryItems->count() }})</span>
                        </div>
                        <div class="space-y-1">
                            @foreach($categoryItems as $item)
                                <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-base-200/50 transition-colors group">
                                    <!-- Checkbox -->
                                    <button onclick="toggleShoppingItem({{ $item->id }})" class="w-5 h-5 rounded-full border-2 border-slate-300 hover:border-emerald-500 flex items-center justify-center transition-colors">
                                    </button>

                                    <!-- Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <span class="text-slate-900">{{ $item->name }}</span>
                                        @if($item->quantity)
                                            <span class="text-slate-400 ml-2">{{ $item->quantity }}</span>
                                        @endif
                                        @if($item->notes)
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $item->notes }}</p>
                                        @endif
                                    </div>

                                    <!-- Delete -->
                                    <button onclick="confirmDelete('{{ route('lists.shopping.items.destroy', $item) }}')" class="btn btn-ghost btn-xs btn-square text-error opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Checked Items -->
                @if($checkedItems->count() > 0)
                    <div class="pt-4 border-t border-base-200">
                        <button onclick="document.getElementById('checkedItems').classList.toggle('hidden')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 mb-2">
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                            In Cart ({{ $checkedItems->count() }})
                        </button>
                        <div id="checkedItems" class="space-y-1">
                            @foreach($checkedItems as $item)
                                <div class="flex items-center gap-3 py-2 px-3 rounded-lg opacity-50 group">
                                    <!-- Checkbox (checked) -->
                                    <button onclick="toggleShoppingItem({{ $item->id }})" class="w-5 h-5 rounded-full bg-emerald-500 border-2 border-emerald-500 flex items-center justify-center">
                                        <span class="icon-[tabler--check] size-3 text-white"></span>
                                    </button>

                                    <!-- Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <span class="text-slate-400 line-through">{{ $item->name }}</span>
                                        @if($item->quantity)
                                            <span class="text-slate-300 ml-2 line-through">{{ $item->quantity }}</span>
                                        @endif
                                    </div>

                                    <!-- Delete -->
                                    <button onclick="confirmDelete('{{ route('lists.shopping.items.destroy', $item) }}')" class="btn btn-ghost btn-xs btn-square text-error opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Frequently Bought Suggestions -->
            @if($frequentItems->count() > 0 && $uncheckedItems->count() < 5)
                <div class="pt-4 border-t border-base-200">
                    <h3 class="text-sm font-medium text-slate-700 mb-2">Frequently Bought</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($frequentItems->take(8) as $historyItem)
                            <form action="{{ route('lists.shopping.items.store') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="shopping_list_id" value="{{ $activeList->id }}">
                                <input type="hidden" name="name" value="{{ $historyItem->name }}">
                                <input type="hidden" name="category" value="{{ $historyItem->category }}">
                                <input type="hidden" name="quantity" value="{{ $historyItem->quantity }}">
                                <button type="submit" class="btn btn-sm btn-ghost border border-base-300 gap-1">
                                    <span class="icon-[tabler--plus] size-3"></span>
                                    {{ ucfirst($historyItem->name) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>
