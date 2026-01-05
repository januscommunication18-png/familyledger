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

function toggleStoreItem(itemId) {
    const itemEl = document.getElementById(`store-item-${itemId}`);
    const wasChecked = itemEl.classList.contains('checked-item');

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
            // Update UI
            if (wasChecked) {
                itemEl.classList.remove('checked-item', 'bg-emerald-50', 'border-emerald-200');
                itemEl.classList.add('unchecked-item', 'bg-white', 'border-slate-200');
                itemEl.querySelector('button').classList.remove('opacity-60');
                itemEl.querySelector('.item-name').classList.remove('line-through', 'text-slate-500');
                itemEl.querySelector('.item-name').classList.add('text-slate-800');
                const checkbox = itemEl.querySelector('.flex-shrink-0');
                checkbox.classList.remove('bg-emerald-500', 'border-emerald-500');
                checkbox.classList.add('border-slate-300');
                checkbox.innerHTML = '';
                checkedCount--;
            } else {
                itemEl.classList.remove('unchecked-item', 'bg-white', 'border-slate-200');
                itemEl.classList.add('checked-item', 'bg-emerald-50', 'border-emerald-200');
                itemEl.querySelector('button').classList.add('opacity-60');
                itemEl.querySelector('.item-name').classList.add('line-through', 'text-slate-500');
                itemEl.querySelector('.item-name').classList.remove('text-slate-800');
                const checkbox = itemEl.querySelector('.flex-shrink-0');
                checkbox.classList.add('bg-emerald-500', 'border-emerald-500');
                checkbox.classList.remove('border-slate-300');
                checkbox.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                checkedCount++;

                // Move to bottom
                itemEl.parentNode.appendChild(itemEl);
            }

            // Update counts
            updateStoreCounts();

            // Hide if setting enabled
            if (document.getElementById('hidePurchased').checked && !wasChecked) {
                itemEl.classList.add('hidden');
            }
        }
    });
}

function updateStoreCounts() {
    const remaining = totalItems - checkedCount;
    document.getElementById('remainingCount').textContent = remaining;

    const progress = totalItems > 0 ? (checkedCount / totalItems) * 100 : 0;
    document.getElementById('progressBar').style.width = `${progress}%`;

    // Show completion message if all done
    if (remaining === 0 && totalItems > 0) {
        location.reload();
    }
}

function togglePurchasedVisibility() {
    const hide = document.getElementById('hidePurchased').checked;
    document.querySelectorAll('.checked-item').forEach(item => {
        item.classList.toggle('hidden', hide);
    });
}
</script>
@endpush
