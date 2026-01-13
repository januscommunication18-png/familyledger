@extends('layouts.dashboard')

@section('title', 'Store Mode - ' . $list->name)
@section('page-name', 'Store Mode')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('shopping.index') }}" class="hover:text-emerald-600">Shopping Lists</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('shopping.show', $list) }}" class="hover:text-emerald-600">{{ $list->name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Store Mode</li>
@endsection

@section('content')
<div class="max-w-lg mx-auto space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between bg-emerald-500 text-white rounded-xl p-4 shadow-lg">
        <div class="flex items-center gap-3">
            <a href="{{ route('shopping.show', $list) }}" class="btn btn-ghost btn-sm btn-circle text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold">{{ $list->name }}</h1>
                <p class="text-sm text-emerald-100">Store Mode</p>
            </div>
        </div>
        <div class="text-right">
            <div class="text-3xl font-bold" id="remainingCount">{{ $items->where('is_checked', false)->count() }}</div>
            <div class="text-sm text-emerald-100">items left</div>
        </div>
    </div>

    <!-- Progress Bar -->
    @php
        $total = $items->count();
        $checked = $items->where('is_checked', true)->count();
        $progress = $total > 0 ? ($checked / $total) * 100 : 0;
    @endphp
    <div class="bg-slate-200 rounded-full h-3 overflow-hidden">
        <div id="progressBar" class="bg-emerald-500 h-full transition-all duration-300" style="width: {{ $progress }}%"></div>
    </div>

    <!-- Toggle Purchased -->
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="hidePurchased" class="toggle toggle-sm toggle-success" onchange="togglePurchasedVisibility()">
            <span class="text-sm text-slate-600">Hide purchased items</span>
        </label>
        @if($checked > 0)
            <form action="{{ route('shopping.clear-checked', $list) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm text-slate-500">Clear Done</button>
            </form>
        @endif
    </div>

    <!-- Items List - Large Touch Targets -->
    <div id="storeItemsList" class="space-y-2">
        @php
            $uncheckedItems = $items->where('is_checked', false)->sortBy('category');
            $checkedItems = $items->where('is_checked', true);
        @endphp

        @foreach($uncheckedItems as $item)
            <div id="store-item-{{ $item->id }}" class="store-item unchecked-item bg-white rounded-xl shadow-sm border-2 border-slate-200 hover:border-emerald-300 transition-all">
                <button type="button" onclick="toggleStoreItem({{ $item->id }})" class="w-full p-4 flex items-center gap-4 text-left">
                    <!-- Large Checkbox -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl border-3 border-slate-300 flex items-center justify-center transition-colors">
                        <svg class="hidden check-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>

                    <!-- Item Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="item-name text-lg font-semibold text-slate-800">{{ $item->name }}</span>
                            @if($item->quantity)
                                <span class="badge badge-primary">{{ $item->quantity }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-sm {{ $item->category_color }}">{{ $item->category_name }}</span>
                            @if($item->notes)
                                <span class="text-sm text-slate-400">{{ $item->notes }}</span>
                            @endif
                        </div>
                    </div>
                </button>
            </div>
        @endforeach

        <!-- Checked Items -->
        @foreach($checkedItems as $item)
            <div id="store-item-{{ $item->id }}" class="store-item checked-item bg-emerald-50 rounded-xl shadow-sm border-2 border-emerald-200">
                <button type="button" onclick="toggleStoreItem({{ $item->id }})" class="w-full p-4 flex items-center gap-4 text-left opacity-60">
                    <!-- Large Checkbox - Checked -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-500 border-3 border-emerald-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>

                    <!-- Item Info -->
                    <div class="flex-1 min-w-0">
                        <span class="item-name text-lg font-semibold text-slate-500 line-through">{{ $item->name }}</span>
                        @if($item->quantity)
                            <span class="badge badge-ghost ml-2">{{ $item->quantity }}</span>
                        @endif
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    <!-- All Done Message -->
    @if($items->where('is_checked', false)->count() === 0 && $items->count() > 0)
        <div class="bg-emerald-100 rounded-xl p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-500 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-800 mb-1">All Done!</h3>
            <p class="text-emerald-600">You've completed your shopping list.</p>
            <form action="{{ route('shopping.clear-checked', $list) }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-success">Clear List for Next Time</button>
            </form>
        </div>
    @endif

    @if($items->count() === 0)
        <div class="bg-slate-100 rounded-xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-1">Empty List</h3>
            <p class="text-slate-500">Add items to your list first.</p>
            <a href="{{ route('shopping.show', $list) }}" class="btn btn-primary mt-4">Add Items</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const totalItems = {{ $items->count() }};
let checkedCount = {{ $items->where('is_checked', true)->count() }};
const processingItems = new Set();

function toggleStoreItem(itemId) {
    // Prevent double-clicks while processing
    if (processingItems.has(itemId)) {
        return;
    }

    const itemEl = document.getElementById(`store-item-${itemId}`);
    const wasChecked = itemEl.classList.contains('checked-item');

    // Mark as processing
    processingItems.add(itemId);

    // Immediate visual feedback (optimistic UI update)
    applyToggleUI(itemEl, wasChecked);

    fetch(`/shopping/items/${itemId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if all items are done and reload
            if (checkedCount === totalItems && totalItems > 0) {
                location.reload();
            }
        } else {
            // Revert on failure
            applyToggleUI(itemEl, !wasChecked);
        }
    })
    .catch(() => {
        // Revert on error
        applyToggleUI(itemEl, !wasChecked);
    })
    .finally(() => {
        processingItems.delete(itemId);
    });
}

function applyToggleUI(itemEl, wasChecked) {
    try {
        if (!itemEl) {
            console.error('itemEl is null');
            return;
        }

        const button = itemEl.querySelector('button');
        const itemName = itemEl.querySelector('.item-name');
        const checkbox = itemEl.querySelector('.flex-shrink-0');

        console.log('applyToggleUI called', { wasChecked, button, itemName, checkbox });

        if (!button || !itemName || !checkbox) {
            console.error('Missing elements:', { button, itemName, checkbox });
            return;
        }

        if (wasChecked) {
            // Unchecking item
            itemEl.className = 'store-item unchecked-item bg-white rounded-xl shadow-sm border-2 border-slate-200 hover:border-emerald-300 transition-all';
            button.className = 'w-full p-4 flex items-center gap-4 text-left';

            // Remove strikethrough
            itemName.className = 'item-name text-lg font-semibold text-slate-800';
            itemName.style.textDecoration = 'none';

            // Hide checkbox
            checkbox.className = 'flex-shrink-0 w-10 h-10 rounded-xl border-3 border-slate-300 flex items-center justify-center transition-colors';
            checkbox.style.backgroundColor = '';
            checkbox.style.borderColor = '';
            checkbox.innerHTML = '';
            checkedCount--;
        } else {
            // Checking item
            itemEl.className = 'store-item checked-item bg-emerald-50 rounded-xl shadow-sm border-2 border-emerald-200';
            button.className = 'w-full p-4 flex items-center gap-4 text-left opacity-60';

            // Add strikethrough
            itemName.className = 'item-name text-lg font-semibold text-slate-500 line-through';
            itemName.style.textDecoration = 'line-through';

            // Show checkbox with checkmark
            checkbox.className = 'flex-shrink-0 w-10 h-10 rounded-xl border-3 border-emerald-500 bg-emerald-500 flex items-center justify-center';
            checkbox.style.backgroundColor = '#10b981';
            checkbox.style.borderColor = '#10b981';
            checkbox.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
            checkedCount++;

            // Move to bottom
            if (itemEl.parentNode) {
                itemEl.parentNode.appendChild(itemEl);
            }

            // Hide if setting enabled
            const hidePurchasedCheckbox = document.getElementById('hidePurchased');
            if (hidePurchasedCheckbox && hidePurchasedCheckbox.checked) {
                itemEl.classList.add('hidden');
            }
        }

        // Update counts
        updateStoreCounts();
        console.log('applyToggleUI completed successfully');
    } catch (error) {
        console.error('Error in applyToggleUI:', error);
    }
}

function updateStoreCounts() {
    const remaining = totalItems - checkedCount;
    document.getElementById('remainingCount').textContent = remaining;

    const progress = totalItems > 0 ? (checkedCount / totalItems) * 100 : 0;
    document.getElementById('progressBar').style.width = `${progress}%`;
}

function togglePurchasedVisibility() {
    const hide = document.getElementById('hidePurchased').checked;
    document.querySelectorAll('.checked-item').forEach(item => {
        item.classList.toggle('hidden', hide);
    });
}
</script>
@endpush
