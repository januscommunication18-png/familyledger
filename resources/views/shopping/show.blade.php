@extends('layouts.dashboard')

@section('title', $list->name)
@section('page-name', $list->name)

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('shopping.index') }}" class="hover:text-emerald-600">Shopping Lists</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $list->name }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('shopping.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $list->name }}</h1>
                @if($list->store_name)
                    <p class="text-sm text-slate-500">{{ $list->store_name }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($isOwner)
                <button type="button" onclick="toggleShareModal()" class="btn btn-ghost btn-sm gap-1" title="Share">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                    <span class="hidden sm:inline">Share</span>
                </button>
            @endif
            <button type="button" onclick="printList()" class="btn btn-ghost btn-sm gap-1" title="Print">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                <span class="hidden sm:inline">Print</span>
            </button>
            @if($isOwner)
                <button type="button" onclick="toggleEmailModal()" class="btn btn-ghost btn-sm gap-1" title="Email">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <span class="hidden sm:inline">Email</span>
                </button>
            @endif
            <a href="{{ route('shopping.store-mode', $list) }}" class="btn btn-outline btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Store Mode
            </a>
            @if($isOwner)
                <div class="relative">
                    <button type="button" onclick="toggleOptionsMenu()" class="btn btn-ghost btn-sm btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div id="optionsMenu" class="hidden absolute right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg min-w-40 py-1 z-50">
                        <button type="button" onclick="toggleEditModal(); toggleOptionsMenu();" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            Edit List
                        </button>
                        @if(!$list->is_default)
                            <form action="{{ route('shopping.destroy', $list) }}" method="POST" onsubmit="return confirm('Delete this shopping list?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    Delete List
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Add -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <form id="quickAddForm" action="{{ route('shopping.items.store', $list) }}" method="POST" class="flex gap-2">
                @csrf
                <div class="flex-1 relative">
                    <input type="text" name="name" id="itemInput" required autocomplete="off"
                        placeholder="Add item... (e.g., Milk x2)"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <div id="suggestions" class="hidden absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto"></div>
                </div>
                <button type="submit" class="btn btn-primary px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                </button>
            </form>

            <!-- Category Quick Select -->
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs text-slate-400">Category:</span>
                @foreach(['produce' => 'Produce', 'dairy' => 'Dairy', 'meat' => 'Meat', 'household' => 'Household', 'pharmacy' => 'Pharmacy', 'other' => 'Other'] as $key => $label)
                    <button type="button" onclick="setCategory('{{ $key }}')" class="category-btn badge badge-sm {{ $key === 'other' ? 'badge-primary' : 'badge-outline' }} cursor-pointer hover:badge-primary" data-category="{{ $key }}">
                        {{ $label }}
                    </button>
                @endforeach
                <input type="hidden" name="category" id="selectedCategory" form="quickAddForm" value="other">
            </div>
        </div>
    </div>

    <!-- Frequently Bought / Recent Items -->
    @if($frequentItems->count() > 0 || $recentItems->count() > 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-sm font-medium text-slate-700 mb-2">Quick Add from History</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($frequentItems->take(8) as $item)
                        <button type="button" onclick="quickAddItem('{{ $item->name }}', '{{ $item->category }}')"
                            class="badge badge-lg badge-outline hover:badge-primary cursor-pointer transition-colors">
                            {{ $item->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Items List -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <!-- Stats Bar -->
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600">
                        <span id="uncheckedCount" class="font-bold text-lg text-amber-600">{{ $items->where('is_checked', false)->count() }}</span> to buy
                    </span>
                    <span class="text-sm text-slate-600">
                        <span id="checkedCount" class="font-bold text-lg text-emerald-600">{{ $items->where('is_checked', true)->count() }}</span> done
                    </span>
                </div>
                @if($items->where('is_checked', true)->count() > 0)
                    <form action="{{ route('shopping.clear-checked', $list) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-xs text-slate-500">Clear Purchased</button>
                    </form>
                @endif
            </div>

            <!-- Category Filter -->
            @if($items->count() > 0)
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 overflow-x-auto">
                <span class="text-xs text-slate-400 flex-shrink-0">Filter:</span>
                <button type="button" onclick="filterByCategory('all')" class="filter-btn badge badge-sm badge-primary cursor-pointer flex-shrink-0" data-filter="all">
                    All
                </button>
                @foreach(['produce' => 'Produce', 'dairy' => 'Dairy', 'meat' => 'Meat', 'household' => 'Household', 'pharmacy' => 'Pharmacy', 'other' => 'Other'] as $key => $label)
                    @if($items->where('category', $key)->count() > 0)
                        <button type="button" onclick="filterByCategory('{{ $key }}')" class="filter-btn badge badge-sm badge-outline cursor-pointer hover:badge-primary flex-shrink-0" data-filter="{{ $key }}">
                            {{ $label }} ({{ $items->where('category', $key)->count() }})
                        </button>
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Items by Category -->
            <div id="itemsList" class="space-y-4">
                @php
                    $uncheckedItems = $items->where('is_checked', false)->groupBy('category');
                    $checkedItems = $items->where('is_checked', true);
                @endphp

                <!-- Unchecked Items -->
                @foreach($uncheckedItems as $category => $categoryItems)
                    <div class="category-group" data-category="{{ $category }}">
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="w-5 h-5 rounded {{ $categoryItems->first()->category_color }} flex items-center justify-center">
                                <span class="{{ $categoryItems->first()->category_icon }} size-3"></span>
                            </span>
                            {{ $categories[$category] ?? 'Other' }}
                        </h4>
                        <div class="space-y-1">
                            @foreach($categoryItems as $item)
                                @include('shopping.partials.item', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Checked Items (Collapsible) -->
                @if($checkedItems->count() > 0)
                    <div class="border-t border-slate-100 pt-4 mt-4">
                        <button type="button" onclick="toggleCheckedItems()" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
                            <svg id="checkedArrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transform transition-transform"><path d="m9 18 6-6-6-6"/></svg>
                            <span>Purchased ({{ $checkedItems->count() }})</span>
                        </button>
                        <div id="checkedItemsList" class="hidden mt-2 space-y-1 opacity-60">
                            @foreach($checkedItems as $item)
                                @include('shopping.partials.item', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($items->count() === 0)
                    <div class="text-center py-8">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        </div>
                        <p class="text-slate-500">Your list is empty</p>
                        <p class="text-sm text-slate-400">Add items using the input above</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit List Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="toggleEditModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <form action="{{ route('shopping.update', $list) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Edit Shopping List</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">List Name</label>
                            <input type="text" name="name" value="{{ $list->name }}" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Store</label>
                            <select name="store" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white">
                                <option value="">No specific store</option>
                                @foreach(\App\Models\ShoppingList::STORES as $key => $label)
                                    <option value="{{ $key }}" {{ $list->store === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 p-6 pt-0">
                    <button type="button" onclick="toggleEditModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="submit" class="flex-1 btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share List Modal -->
<div id="shareModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="toggleShareModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <form action="{{ route('shopping.share', $list) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Share Shopping List</h3>
                            <p class="text-sm text-slate-500">Selected members will see this list in their portal</p>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @forelse($familyMembers ?? [] as $member)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="members[]" value="{{ $member->id }}" class="checkbox checkbox-sm checkbox-primary"
                                    {{ in_array($member->id, $sharedWithMembers ?? []) ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-emerald-400 to-teal-500">
                                    @if($member->profile_image_url)
                                        <img src="{{ $member->profile_image_url }}" alt="{{ $member->first_name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <span class="text-sm font-semibold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900">{{ $member->full_name }}</p>
                                    @if($member->email)
                                        <p class="text-xs text-slate-500">{{ $member->email }}</p>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-4 text-slate-500">
                                <p>No family members found.</p>
                                <a href="{{ route('family-circle.index') }}" class="text-sm text-emerald-600 hover:underline">Add family members first</a>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="flex gap-3 p-6 pt-0">
                    <button type="button" onclick="toggleShareModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="submit" class="flex-1 btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                        Share List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email List Modal -->
<div id="emailModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="toggleEmailModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <form action="{{ route('shopping.email', $list) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Email Shopping List</h3>
                            <p class="text-sm text-slate-500">Send this list via email to selected members</p>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @forelse($familyMembers ?? [] as $member)
                            @if($member->email)
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="members[]" value="{{ $member->id }}" class="checkbox checkbox-sm checkbox-violet">
                                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-violet-400 to-purple-500">
                                        @if($member->profile_image_url)
                                            <img src="{{ $member->profile_image_url }}" alt="{{ $member->first_name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <span class="text-sm font-semibold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-900">{{ $member->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $member->email }}</p>
                                    </div>
                                </label>
                            @endif
                        @empty
                            <div class="text-center py-4 text-slate-500">
                                <p>No family members with email found.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Additional Message (Optional)</label>
                        <textarea name="message" rows="2" placeholder="Add a personal note..."
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 p-6 pt-0">
                    <button type="button" onclick="toggleEmailModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="submit" class="flex-1 btn bg-violet-600 hover:bg-violet-700 text-white gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<!-- Hidden Print Area -->
<div id="printArea" class="hidden print:block">
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-2">{{ $list->name }}</h1>
        @if($list->store_name)
            <p class="text-gray-600 mb-4">{{ $list->store_name }}</p>
        @endif
        <p class="text-sm text-gray-500 mb-6">{{ now()->format('F j, Y') }}</p>

        <div class="space-y-4">
            @php
                $printUnchecked = $items->where('is_checked', false)->groupBy('category');
            @endphp

            @foreach($printUnchecked as $category => $categoryItems)
                <div>
                    <h3 class="font-semibold text-gray-700 border-b pb-1 mb-2">{{ $categories[$category] ?? 'Other' }}</h3>
                    <ul class="space-y-1">
                        @foreach($categoryItems as $item)
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-4 border border-gray-400 rounded"></span>
                                <span>{{ $item->name }}@if($item->quantity > 1) ({{ $item->quantity }})@endif</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-gray-400 mt-8">Printed from FamilyLedger</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
const listId = {{ $list->id }};

// Category selection for adding items
function setCategory(category) {
    document.getElementById('selectedCategory').value = category;
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('badge-primary');
        btn.classList.add('badge-outline');
    });
    document.querySelector(`[data-category="${category}"]`).classList.remove('badge-outline');
    document.querySelector(`[data-category="${category}"]`).classList.add('badge-primary');
}

// Filter items by category
function filterByCategory(category) {
    // Update filter button styles
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('badge-primary');
        btn.classList.add('badge-outline');
    });
    document.querySelector(`[data-filter="${category}"]`).classList.remove('badge-outline');
    document.querySelector(`[data-filter="${category}"]`).classList.add('badge-primary');

    // Show/hide category groups
    const categoryGroups = document.querySelectorAll('.category-group');
    categoryGroups.forEach(group => {
        if (category === 'all' || group.dataset.category === category) {
            group.classList.remove('hidden');
        } else {
            group.classList.add('hidden');
        }
    });

    // Also filter checked items if visible
    const checkedItemsList = document.getElementById('checkedItemsList');
    if (checkedItemsList) {
        const checkedItems = checkedItemsList.querySelectorAll('.item-row');
        checkedItems.forEach(item => {
            const itemCategory = item.dataset.category;
            if (category === 'all' || itemCategory === category) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }
}

// Quick add from history
function quickAddItem(name, category) {
    document.getElementById('itemInput').value = name;
    setCategory(category || 'other');
    document.getElementById('quickAddForm').submit();
}

// Toggle checked items visibility
function toggleCheckedItems() {
    const list = document.getElementById('checkedItemsList');
    const arrow = document.getElementById('checkedArrow');
    list.classList.toggle('hidden');
    arrow.classList.toggle('rotate-90');
}

// Toggle item checked status
function toggleItem(itemId) {
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
            location.reload();
        }
    });
}

// Delete item
function deleteItem(itemId) {
    if (!confirm('Remove this item?')) return;

    fetch(`/shopping/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`item-${itemId}`).remove();
            updateCounts();
        }
    });
}

// Update counts
function updateCounts() {
    const unchecked = document.querySelectorAll('.item-row:not(.item-checked)').length;
    const checked = document.querySelectorAll('.item-row.item-checked').length;
    document.getElementById('uncheckedCount').textContent = unchecked;
    document.getElementById('checkedCount').textContent = checked;
}

// Options menu
function toggleOptionsMenu() {
    const menu = document.getElementById('optionsMenu');
    menu.classList.toggle('hidden');
}

// Close options menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('optionsMenu');
    const button = menu?.previousElementSibling;
    if (menu && !menu.contains(e.target) && !button?.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

// Edit modal
function toggleEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.toggle('hidden');
    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
}

// Share modal
function toggleShareModal() {
    const modal = document.getElementById('shareModal');
    modal.classList.toggle('hidden');
    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
}

// Email modal
function toggleEmailModal() {
    const modal = document.getElementById('emailModal');
    modal.classList.toggle('hidden');
    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
}

// Print list
function printList() {
    const printArea = document.getElementById('printArea');
    printArea.classList.remove('hidden');
    window.print();
    printArea.classList.add('hidden');
}

// Escape key handler
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const editModal = document.getElementById('editModal');
        const shareModal = document.getElementById('shareModal');
        const emailModal = document.getElementById('emailModal');

        if (!editModal.classList.contains('hidden')) {
            toggleEditModal();
        }
        if (!shareModal.classList.contains('hidden')) {
            toggleShareModal();
        }
        if (!emailModal.classList.contains('hidden')) {
            toggleEmailModal();
        }
    }
});

// Auto-submit on Enter in quick add
document.getElementById('itemInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('quickAddForm').submit();
    }
});
</script>
@endpush
