@extends('layouts.dashboard')

@section('title', 'Add Item')
@section('page-name', 'Lists')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('lists.index', ['tab' => 'shopping']) }}" class="hover:text-primary">Lists</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Add Item</li>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('lists.index', ['tab' => 'shopping', 'shopping_list' => $shoppingList->id]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $shoppingList->name }}</h1>
                <p class="text-slate-500">Add items to your shopping list</p>
            </div>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Left Column: Add Item Form -->
        <div class="lg:col-span-3 space-y-4">
            @if($errors->any())
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    <div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Quick Add: Frequently Bought -->
            @if($frequentItems->count() > 0)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body py-4">
                    <p class="text-sm font-medium text-slate-600 mb-3">Quick Add - Frequently Bought</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($frequentItems as $historyItem)
                            @php
                                $isInList = in_array(strtolower($historyItem->name), $existingItemNames);
                            @endphp
                            <button type="button"
                                onclick="fillItem('{{ addslashes($historyItem->name) }}', '{{ $historyItem->category }}', '{{ addslashes($historyItem->quantity ?? '') }}')"
                                class="btn btn-sm {{ $isInList ? 'btn-disabled opacity-50' : 'btn-ghost border border-base-300' }} gap-1"
                                {{ $isInList ? 'disabled' : '' }}>
                                @if($isInList)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                @endif
                                {{ ucfirst($historyItem->name) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('lists.shopping.items.store') }}" method="POST" id="addItemForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="shopping_list_id" value="{{ $shoppingList->id }}">
                <input type="hidden" name="editing_item_id" id="editingItemId" value="">

                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <!-- Edit Mode Banner -->
                        <div id="editModeBanner" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2 text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                                <span class="font-medium">Editing: <span id="editingItemName"></span></span>
                            </div>
                            <button type="button" onclick="cancelEdit()" class="btn btn-ghost btn-xs">Cancel</button>
                        </div>

                        <!-- Item Name - Large Input -->
                        <div class="relative">
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="input input-bordered input-lg w-full text-lg"
                                placeholder="What do you need to buy?"
                                id="itemNameInput" autocomplete="off">
                            <div id="duplicateWarning" class="hidden mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <span>This item is already in your list</span>
                            </div>
                            <div id="itemSuggestions" class="hidden absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
                        </div>

                        <!-- Category & Quantity - Inline -->
                        <div class="flex flex-wrap gap-3 mt-4">
                            <div class="flex-1 min-w-[150px]">
                                <select name="category" id="categorySelect" class="select select-bordered w-full">
                                    <option value="" disabled>Category</option>
                                    @foreach($categories as $value => $label)
                                        <option value="{{ $value }}" {{ old('category', 'other') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <input type="text" name="quantity" value="{{ old('quantity') }}" id="quantityInput"
                                    class="input input-bordered w-full"
                                    placeholder="Qty (e.g., 2 lbs, 1 gallon)">
                            </div>
                        </div>

                        <!-- Notes - Optional -->
                        <div class="mt-3">
                            <input type="text" name="notes" value="{{ old('notes') }}" id="notesInput"
                                class="input input-bordered w-full input-sm"
                                placeholder="Notes (optional) - e.g., Buy organic, Check expiry">
                        </div>

                        <!-- Action Buttons - Add Mode -->
                        <div id="addModeButtons" class="flex gap-2 mt-4">
                            <button type="submit" name="add_another" value="1" class="btn btn-primary flex-1 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add & Continue
                            </button>
                            <button type="submit" class="btn btn-outline btn-primary">
                                Add & Done
                            </button>
                        </div>

                        <!-- Action Buttons - Edit Mode -->
                        <div id="editModeButtons" class="hidden flex gap-2 mt-4">
                            <button type="button" onclick="cancelEdit()" class="btn btn-ghost">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary flex-1 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Current Items -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-sm sticky top-4">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-slate-800">In Your List</h3>
                        <span class="badge badge-ghost">{{ $shoppingList->items->count() }} items</span>
                    </div>

                    @if($shoppingList->items->count() > 0)
                        <div class="max-h-[400px] overflow-y-auto space-y-1 -mx-2 px-2">
                            @php
                                $uncheckedItems = $shoppingList->items->where('is_checked', false);
                                $checkedItems = $shoppingList->items->where('is_checked', true);
                            @endphp

                            @foreach($uncheckedItems as $item)
                                <div class="flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-base-200/50 group">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                    <span class="flex-1 text-sm truncate">{{ $item->name }}</span>
                                    @if($item->quantity)
                                        <span class="text-xs text-slate-400 flex-shrink-0">{{ $item->quantity }}</span>
                                    @endif
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                        <button type="button" onclick="editItem({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->category }}', '{{ addslashes($item->quantity ?? '') }}', '{{ addslashes($item->notes ?? '') }}')"
                                            class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                                        </button>
                                        <button type="button" onclick="deleteItem({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                            class="btn btn-ghost btn-xs btn-square" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if($checkedItems->count() > 0)
                                <div class="pt-2 mt-2 border-t border-base-200">
                                    <p class="text-xs text-slate-400 mb-1 px-2">Already checked off</p>
                                    @foreach($checkedItems as $item)
                                        <div class="flex items-center gap-2 py-1.5 px-2 opacity-50 group">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span class="flex-1 text-sm line-through text-slate-400 truncate">{{ $item->name }}</span>
                                            <button type="button" onclick="deleteItem({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                                class="btn btn-ghost btn-xs btn-square opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-400">
                            <span class="icon-[tabler--shopping-cart] size-10 opacity-30 mb-2"></span>
                            <p class="text-sm">No items yet</p>
                            <p class="text-xs">Start adding items above</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteItemForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
// Existing item names for duplicate check (lowercase)
const existingItems = @json($existingItemNames);
let currentEditingItemId = null;

function fillItem(name, category, quantity) {
    const lowerName = name.toLowerCase();
    if (existingItems.includes(lowerName)) {
        return; // Don't fill if already in list
    }
    document.getElementById('itemNameInput').value = name;
    document.getElementById('categorySelect').value = category;
    document.getElementById('quantityInput').value = quantity;
    checkDuplicate();
}

// Duplicate check
const itemNameInput = document.getElementById('itemNameInput');
const duplicateWarning = document.getElementById('duplicateWarning');
const addItemForm = document.getElementById('addItemForm');

function checkDuplicate() {
    const name = itemNameInput.value.trim().toLowerCase();
    if (name && existingItems.includes(name)) {
        duplicateWarning.classList.remove('hidden');
        return true;
    } else {
        duplicateWarning.classList.add('hidden');
        return false;
    }
}

if (itemNameInput) {
    itemNameInput.addEventListener('input', checkDuplicate);

    // Prevent form submission if duplicate
    addItemForm.addEventListener('submit', function(e) {
        if (checkDuplicate()) {
            e.preventDefault();
            itemNameInput.focus();
        }
    });
}

// Item suggestions
const suggestionsDiv = document.getElementById('itemSuggestions');

if (itemNameInput && suggestionsDiv) {
    let debounceTimer;
    itemNameInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = this.value;
            if (query.length >= 2) {
                fetch(`{{ route('lists.shopping.suggestions') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(items => {
                        // Filter out items already in list
                        const filteredItems = items.filter(item =>
                            !existingItems.includes(item.name.toLowerCase())
                        );

                        if (filteredItems.length > 0) {
                            suggestionsDiv.innerHTML = filteredItems.map(item =>
                                `<div class="px-3 py-2 hover:bg-base-200 cursor-pointer" onclick="selectSuggestion('${item.name.replace(/'/g, "\\'")}', '${item.category}', '${(item.quantity || '').replace(/'/g, "\\'")}')">
                                    <div class="font-medium">${item.name}</div>
                                    <div class="text-xs text-base-content/60">${item.category} ${item.quantity ? '- ' + item.quantity : ''}</div>
                                </div>`
                            ).join('');
                            suggestionsDiv.classList.remove('hidden');
                        } else {
                            suggestionsDiv.classList.add('hidden');
                        }
                    });
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!suggestionsDiv.contains(e.target) && e.target !== itemNameInput) {
            suggestionsDiv.classList.add('hidden');
        }
    });
}

function selectSuggestion(name, category, quantity) {
    document.getElementById('itemNameInput').value = name;
    document.getElementById('categorySelect').value = category;
    if (quantity) {
        document.getElementById('quantityInput').value = quantity;
    }
    document.getElementById('itemSuggestions').classList.add('hidden');
    checkDuplicate();
}

// Edit item - fills the main form
function editItem(id, name, category, quantity, notes) {
    currentEditingItemId = id;

    // Fill the form fields
    document.getElementById('itemNameInput').value = name;
    document.getElementById('categorySelect').value = category;
    document.getElementById('quantityInput').value = quantity || '';
    document.getElementById('notesInput').value = notes || '';

    // Update form for edit mode
    document.getElementById('editingItemId').value = id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('addItemForm').action = `/lists/shopping/items/${id}`;

    // Show edit mode UI
    document.getElementById('editModeBanner').classList.remove('hidden');
    document.getElementById('editingItemName').textContent = name;
    document.getElementById('addModeButtons').classList.add('hidden');
    document.getElementById('editModeButtons').classList.remove('hidden');

    // Hide duplicate warning during edit
    document.getElementById('duplicateWarning').classList.add('hidden');

    // Scroll to form and focus
    document.getElementById('itemNameInput').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('itemNameInput').focus();
}

// Cancel edit - reset form to add mode
function cancelEdit() {
    currentEditingItemId = null;

    // Clear the form fields
    document.getElementById('itemNameInput').value = '';
    document.getElementById('categorySelect').value = 'other';
    document.getElementById('quantityInput').value = '';
    document.getElementById('notesInput').value = '';

    // Reset form for add mode
    document.getElementById('editingItemId').value = '';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('addItemForm').action = '{{ route("lists.shopping.items.store") }}';

    // Show add mode UI
    document.getElementById('editModeBanner').classList.add('hidden');
    document.getElementById('addModeButtons').classList.remove('hidden');
    document.getElementById('editModeButtons').classList.add('hidden');
    document.getElementById('duplicateWarning').classList.add('hidden');
}

// Delete item (instant, no confirmation)
function deleteItem(id, name) {
    const form = document.getElementById('deleteItemForm');
    form.action = `/shopping/items/${id}`;
    form.submit();
}

// Show toastr for success messages
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}');
        }
    });
@endif
</script>
@endsection
