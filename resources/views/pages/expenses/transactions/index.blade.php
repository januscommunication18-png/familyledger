@extends('layouts.dashboard')

@section('page-name', 'Transactions')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Transactions</h1>
            <p class="text-sm text-slate-500">Manage your income and expenses</p>
        </div>
        <button onclick="openAddModal()" class="btn btn-primary gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Add Transaction
        </button>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <form action="{{ route('expenses.transactions') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1"><span class="label-text text-xs">Search</span></label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input input-bordered input-sm w-full sm:w-48" placeholder="Description or payee">
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1"><span class="label-text text-xs">Category</span></label>
                    <select name="category_id" class="select select-bordered select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1"><span class="label-text text-xs">Type</span></label>
                    <select name="type" class="select select-bordered select-sm">
                        <option value="">All Types</option>
                        <option value="expense" {{ ($filters['type'] ?? '') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="income" {{ ($filters['type'] ?? '') == 'income' ? 'selected' : '' }}>Income</option>
                    </select>
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1"><span class="label-text text-xs">From</span></label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="input input-bordered input-sm">
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label py-1"><span class="label-text text-xs">To</span></label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="input input-bordered input-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions List --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            @if($transactions->count() > 0)
            <div class="overflow-x-auto overflow-y-visible">
                <table class="table overflow-visible">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr class="hover">
                            <td class="text-sm text-slate-600">{{ $transaction->transaction_date->format('M j, Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $transaction->category?->display_icon ?? '📦' }}</span>
                                    <div>
                                        <p class="font-medium text-slate-800">
                                            {{ $transaction->description }}
                                            @if($transaction->is_shared)
                                            <span class="badge badge-primary badge-xs ml-1">Shared</span>
                                            @endif
                                        </p>
                                        @if($transaction->payee)
                                        <p class="text-xs text-slate-500">{{ $transaction->payee }}</p>
                                        @endif
                                        @if($transaction->is_shared && $transaction->shared_child_name)
                                        <p class="text-xs text-violet-600">For: {{ $transaction->shared_child_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-ghost badge-sm">{{ $transaction->category_name }}</span>
                            </td>
                            <td>
                                <span class="badge badge-sm {{ $transaction->type === 'expense' ? 'badge-error' : ($transaction->type === 'income' ? 'badge-success' : 'badge-info') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="text-right font-semibold {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $transaction->formatted_amount }}
                            </td>
                            <td class="relative">
                                <button type="button" class="btn btn-ghost btn-xs btn-square dropdown-toggle" data-dropdown="txn-dropdown-{{ $transaction->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                <ul id="txn-dropdown-{{ $transaction->id }}" class="hidden fixed z-[9999] menu p-2 shadow-lg bg-base-100 rounded-box w-36 border border-slate-200" data-transaction="{{ $transaction->id }}" data-transaction-data="{{ json_encode($transaction) }}">
                                    <li><a href="#" class="edit-btn">Edit</a></li>
                                    <li>
                                        <form action="{{ route('expenses.transactions.delete', $transaction) }}" method="POST" onsubmit="return confirm('Delete this transaction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error w-full text-left">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-1">No transactions yet</h3>
                <p class="text-sm text-slate-500 mb-4">Start tracking your expenses by adding your first transaction.</p>
                <button onclick="openAddModal()" class="btn btn-primary btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add Transaction
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add/Edit Transaction Modal --}}
<div id="transactionModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-slate-800">Add Transaction</h3>
                <button onclick="closeModal()" class="btn btn-ghost btn-sm btn-square">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <form id="transactionForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-4">
                    {{-- Transaction Type --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Type</span></label>
                        <div class="flex gap-2">
                            <label class="flex-1">
                                <input type="radio" name="type" value="expense" class="peer hidden" checked>
                                <div class="btn btn-block peer-checked:btn-error peer-checked:text-white">Expense</div>
                            </label>
                            <label class="flex-1">
                                <input type="radio" name="type" value="income" class="peer hidden">
                                <div class="btn btn-block peer-checked:btn-success peer-checked:text-white">Income</div>
                            </label>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Amount</span></label>
                        <label class="input-group">
                            <span class="bg-base-200">$</span>
                            <input type="number" name="amount" step="0.01" min="0.01" class="input input-bordered flex-1" placeholder="0.00" required>
                        </label>
                    </div>

                    {{-- Description --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Description</span></label>
                        <input type="text" name="description" class="input input-bordered" placeholder="e.g., Grocery shopping" required>
                    </div>

                    {{-- Payee --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Payee (optional)</span></label>
                        <input type="text" name="payee" class="input input-bordered" placeholder="e.g., Walmart">
                    </div>

                    {{-- Category --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Category</span></label>
                        <select name="category_id" class="select select-bordered">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->display_icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Shared Expense (Co-Parenting) --}}
                    @if($children->count() > 0)
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="is_shared" value="1" id="isSharedCheckbox" class="checkbox checkbox-primary checkbox-sm" onchange="toggleSharedWith()">
                            <span class="label-text font-medium">Share with Co-Parent</span>
                        </label>
                        <span class="text-xs text-slate-500 ml-7">This expense will be visible in co-parenting section</span>
                    </div>

                    {{-- Select Child (shown when checkbox is checked) --}}
                    <div id="sharedWithContainer" class="form-control hidden">
                        <label class="label"><span class="label-text font-medium">For Which Child?</span></label>
                        <select name="shared_for_child_id" class="select select-bordered">
                            <option value="">Select a child...</option>
                            @foreach($children as $child)
                            <option value="{{ $child->id }}">{{ $child->full_name }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-slate-500 mt-1">This expense will be shared with the co-parent of this child</span>
                    </div>
                    @endif

                    {{-- Date --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date</span></label>
                        <input type="date" name="transaction_date" class="input input-bordered" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-ghost flex-1">Cancel</button>
                    <button type="submit" class="btn btn-primary flex-1">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Dropdown handling with fixed positioning
document.querySelectorAll('.dropdown-toggle').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdownId = this.dataset.dropdown;
        const dropdown = document.getElementById(dropdownId);
        const isHidden = dropdown.classList.contains('hidden');

        closeAllDropdowns();

        if (isHidden) {
            // Position the dropdown relative to the button
            const rect = this.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.left = (rect.right - 144) + 'px'; // 144 = w-36 (9rem)
            dropdown.classList.remove('hidden');
        }
    });
});

// Edit button handlers
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const dropdown = this.closest('ul');
        const id = dropdown.dataset.transaction;
        const data = JSON.parse(dropdown.dataset.transactionData);
        closeAllDropdowns();
        editTransaction(id, data);
    });
});

function closeAllDropdowns() {
    document.querySelectorAll('[id^="txn-dropdown-"]').forEach(el => el.classList.add('hidden'));
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="txn-dropdown-"]') && !e.target.closest('.dropdown-toggle')) {
        closeAllDropdowns();
    }
});

function toggleSharedWith() {
    const checkbox = document.getElementById('isSharedCheckbox');
    const container = document.getElementById('sharedWithContainer');
    if (checkbox && container) {
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            // Reset the child select
            const childSelect = container.querySelector('select[name="shared_for_child_id"]');
            if (childSelect) childSelect.value = '';
        }
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Transaction';
    document.getElementById('transactionForm').action = '{{ route('expenses.transactions.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('transactionForm').reset();
    document.querySelector('input[name="transaction_date"]').value = '{{ now()->format('Y-m-d') }}';
    // Reset shared expense state
    const isSharedCheckbox = document.getElementById('isSharedCheckbox');
    const sharedWithContainer = document.getElementById('sharedWithContainer');
    if (isSharedCheckbox) isSharedCheckbox.checked = false;
    if (sharedWithContainer) {
        sharedWithContainer.classList.add('hidden');
        const childSelect = sharedWithContainer.querySelector('select[name="shared_for_child_id"]');
        if (childSelect) childSelect.value = '';
    }
    document.getElementById('transactionModal').classList.remove('hidden');
}

function editTransaction(id, data) {
    document.getElementById('modalTitle').textContent = 'Edit Transaction';
    document.getElementById('transactionForm').action = '/expenses/transactions/' + id;
    document.getElementById('formMethod').value = 'PUT';

    document.querySelector('input[name="type"][value="' + data.type + '"]').checked = true;
    document.querySelector('input[name="amount"]').value = data.amount;
    document.querySelector('input[name="description"]').value = data.description;
    document.querySelector('input[name="payee"]').value = data.payee || '';
    document.querySelector('select[name="category_id"]').value = data.category_id || '';
    document.querySelector('input[name="transaction_date"]').value = data.transaction_date;

    // Handle shared expense
    const isSharedCheckbox = document.getElementById('isSharedCheckbox');
    const sharedWithContainer = document.getElementById('sharedWithContainer');
    if (isSharedCheckbox && sharedWithContainer) {
        isSharedCheckbox.checked = data.is_shared || false;
        const childSelect = sharedWithContainer.querySelector('select[name="shared_for_child_id"]');
        if (data.is_shared) {
            sharedWithContainer.classList.remove('hidden');
            // Set the selected child
            if (childSelect) childSelect.value = data.shared_for_child_id || '';
        } else {
            sharedWithContainer.classList.add('hidden');
            if (childSelect) childSelect.value = '';
        }
    }

    document.getElementById('transactionModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('transactionModal').classList.add('hidden');
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
