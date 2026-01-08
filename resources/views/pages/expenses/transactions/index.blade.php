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
        <a href="{{ route('expenses.transactions.create') }}" class="btn btn-primary gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Add Transaction
        </a>
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
                                <a href="{{ route('expenses.transactions.show', $transaction) }}" class="flex items-center gap-2 hover:bg-slate-50 -m-2 p-2 rounded-lg transition-colors">
                                    <span class="text-lg">{{ $transaction->category?->display_icon ?? '📦' }}</span>
                                    <div>
                                        <p class="font-medium text-slate-800">
                                            {{ $transaction->description }}
                                            @if($transaction->is_shared)
                                            <span class="badge badge-primary badge-xs ml-1">Shared</span>
                                            @endif
                                            @if($transaction->hasReceipt())
                                            <span class="badge badge-ghost badge-xs ml-1" title="Has receipt">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                            </span>
                                            @endif
                                        </p>
                                        @if($transaction->payee)
                                        <p class="text-xs text-slate-500">{{ $transaction->payee }}</p>
                                        @endif
                                        @if($transaction->is_shared && $transaction->shared_child_name)
                                        <p class="text-xs text-violet-600">For: {{ $transaction->shared_child_name }}</p>
                                        @endif
                                    </div>
                                </a>
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
                                    <li><a href="{{ route('expenses.transactions.show', $transaction) }}">View</a></li>
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
                <a href="{{ route('expenses.transactions.create') }}" class="btn btn-primary btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add Transaction
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add/Edit Transaction Modal --}}
<div id="transactionModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4">
        {{-- Header --}}
        <div class="flex items-center justify-between p-4 border-b border-slate-200">
            <h3 id="modalTitle" class="text-lg font-semibold text-slate-800">Add Transaction</h3>
            <button onclick="closeModal()" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <form id="transactionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        {{-- Transaction Type --}}
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium text-sm">Type</span></label>
                            <div class="flex gap-2">
                                <label class="flex-1">
                                    <input type="radio" name="type" value="expense" class="peer hidden" checked>
                                    <div class="btn btn-sm btn-block peer-checked:btn-error peer-checked:text-white">Expense</div>
                                </label>
                                <label class="flex-1">
                                    <input type="radio" name="type" value="income" class="peer hidden">
                                    <div class="btn btn-sm btn-block peer-checked:btn-success peer-checked:text-white">Income</div>
                                </label>
                            </div>
                        </div>

                        {{-- Amount & Date Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control">
                                <label class="label py-1"><span class="label-text font-medium text-sm">Amount</span></label>
                                <div class="join">
                                    <span class="join-item flex items-center px-3 bg-base-200 border border-base-300 text-sm">$</span>
                                    <input type="number" name="amount" step="0.01" min="0.01" class="input input-bordered input-sm join-item flex-1 w-full" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="form-control">
                                <label class="label py-1"><span class="label-text font-medium text-sm">Date</span></label>
                                <input type="date" name="transaction_date" class="input input-bordered input-sm" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium text-sm">Description</span></label>
                            <input type="text" name="description" class="input input-bordered input-sm" placeholder="e.g., Grocery shopping" required>
                        </div>

                        {{-- Payee & Category Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control">
                                <label class="label py-1"><span class="label-text font-medium text-sm">Payee</span></label>
                                <input type="text" name="payee" class="input input-bordered input-sm" placeholder="e.g., Walmart">
                            </div>
                            <div class="form-control">
                                <label class="label py-1"><span class="label-text font-medium text-sm">Category</span></label>
                                <select name="category_id" class="select select-bordered select-sm">
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->display_icon }} {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Receipt Upload --}}
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium text-sm">Receipt (optional)</span></label>
                            <div id="receiptDropZone" class="border-2 border-dashed border-slate-300 rounded-lg p-3 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors">
                                <div id="receiptUploadPrompt" class="flex items-center justify-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm text-slate-600">Click to upload or drag and drop</p>
                                        <p class="text-xs text-slate-400">Image or PDF (max 5MB)</p>
                                    </div>
                                </div>
                                <input type="file" name="receipt" id="receiptInput" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                            </div>
                            <div id="receiptPreview" class="hidden mt-2">
                                <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                                    <span id="receiptFileName" class="text-sm text-slate-600 truncate flex-1"></span>
                                    <button type="button" onclick="clearReceipt()" class="btn btn-ghost btn-xs btn-square text-slate-400 hover:text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column - Co-Parenting --}}
                    <div class="space-y-4">
                        @if($children->count() > 0)
                        <div class="bg-slate-50 rounded-lg p-4 space-y-4">
                            <p class="text-sm font-medium text-slate-700 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                Co-Parenting Options
                            </p>

                            {{-- Shared Expense Checkbox --}}
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_shared" value="1" id="isSharedCheckbox" class="checkbox checkbox-primary checkbox-sm" onchange="toggleSharedWith()">
                                <span class="text-sm">Share with Co-Parent</span>
                            </label>

                            {{-- Select Child --}}
                            <div id="sharedWithContainer" class="form-control hidden">
                                <label class="label py-1"><span class="label-text text-sm">For Which Child?</span></label>
                                <select name="shared_for_child_id" id="sharedForChildSelect" class="select select-bordered select-sm" onchange="updatePaymentAmount()">
                                    <option value="">Select a child...</option>
                                    @foreach($children as $child)
                                    @php
                                        // Determine the other parent's name based on relationship
                                        if (!empty($child->isCoparentChild)) {
                                            $otherParentName = $child->otherParentName ?? 'Parent';
                                            $hasOtherParent = !empty($child->otherParentId);
                                        } else {
                                            $otherParentName = $child->coparents->first()?->user?->name ?? '';
                                            $hasOtherParent = $child->coparents->count() > 0;
                                        }
                                    @endphp
                                    <option value="{{ $child->id }}"
                                            data-coparents="{{ $hasOtherParent ? 1 : 0 }}"
                                            data-coparent-name="{{ $otherParentName }}">
                                        {{ $child->full_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Request Payment Section --}}
                            <div id="paymentRequestContainer" class="hidden space-y-3 p-3 bg-violet-50 rounded-lg border border-violet-200">
                                {{-- Co-parent name display --}}
                                <div class="flex items-center gap-2 pb-2 border-b border-violet-200">
                                    <div class="avatar placeholder">
                                        <div class="bg-violet-200 text-violet-700 rounded-full w-8 h-8">
                                            <span id="coparentInitial" class="text-xs">?</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-violet-600">Request from</p>
                                        <p id="coparentNameDisplay" class="text-sm font-medium text-violet-800">Co-Parent</p>
                                    </div>
                                </div>

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="request_payment" value="1" id="requestPaymentCheckbox" class="checkbox checkbox-primary checkbox-sm" onchange="togglePaymentOptions()">
                                    <span class="text-sm font-medium text-violet-800">Request Payment</span>
                                </label>

                                <div id="paymentOptionsContainer" class="hidden space-y-3">
                                    {{-- Split Percentage --}}
                                    <div class="form-control">
                                        <label class="label py-1"><span class="label-text text-xs">Split</span></label>
                                        <div class="flex gap-2">
                                            <label class="flex-1">
                                                <input type="radio" name="split_percentage" value="50" class="peer hidden" checked onchange="updatePaymentAmount()">
                                                <div class="btn btn-xs btn-block peer-checked:btn-primary peer-checked:text-white">50/50</div>
                                            </label>
                                            <label class="flex-1">
                                                <input type="radio" name="split_percentage" value="custom" class="peer hidden" onchange="toggleCustomSplit()">
                                                <div class="btn btn-xs btn-block peer-checked:btn-primary peer-checked:text-white">Custom</div>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Custom Split Input --}}
                                    <div id="customSplitContainer" class="form-control hidden">
                                        <label class="label py-1"><span class="label-text text-xs">Co-Parent Pays (%)</span></label>
                                        <input type="number" name="custom_split_percentage" id="customSplitInput" min="1" max="100" value="50" class="input input-bordered input-xs" onchange="updatePaymentAmount()">
                                    </div>

                                    {{-- Payment Amount Preview --}}
                                    <div class="bg-white rounded p-2 border border-violet-100">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-slate-600">Request amount:</span>
                                            <span id="paymentAmountPreview" class="text-sm font-bold text-violet-700">$0.00</span>
                                        </div>
                                    </div>

                                    {{-- Note --}}
                                    <div class="form-control">
                                        <input type="text" name="payment_note" class="input input-bordered input-xs" placeholder="Note (optional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-slate-50 rounded-lg p-4 text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <p class="text-sm text-slate-500">No children with co-parents</p>
                            <p class="text-xs text-slate-400 mt-1">Add co-parenting relationships to share expenses</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-2 p-4 border-t border-slate-200 bg-slate-50 rounded-b-xl">
                <button type="button" onclick="closeModal()" class="btn btn-ghost flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">Save Transaction</button>
            </div>
        </form>
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
    const paymentContainer = document.getElementById('paymentRequestContainer');

    if (checkbox && container) {
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            // Reset the child select
            const childSelect = container.querySelector('select[name="shared_for_child_id"]');
            if (childSelect) childSelect.value = '';
            // Hide payment request section
            if (paymentContainer) {
                paymentContainer.classList.add('hidden');
                resetPaymentOptions();
            }
        }
    }
}

function togglePaymentOptions() {
    const checkbox = document.getElementById('requestPaymentCheckbox');
    const container = document.getElementById('paymentOptionsContainer');
    if (checkbox && container) {
        if (checkbox.checked) {
            container.classList.remove('hidden');
            updatePaymentAmount();
        } else {
            container.classList.add('hidden');
        }
    }
}

function toggleCustomSplit() {
    const customContainer = document.getElementById('customSplitContainer');
    const splitRadio = document.querySelector('input[name="split_percentage"][value="custom"]');
    if (customContainer && splitRadio) {
        if (splitRadio.checked) {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
        }
        updatePaymentAmount();
    }
}

function updatePaymentAmount() {
    const amountInput = document.querySelector('input[name="amount"]');
    const preview = document.getElementById('paymentAmountPreview');
    const childSelect = document.getElementById('sharedForChildSelect');
    const paymentContainer = document.getElementById('paymentRequestContainer');
    const coparentNameDisplay = document.getElementById('coparentNameDisplay');
    const coparentInitial = document.getElementById('coparentInitial');

    if (!amountInput || !preview) return;

    const amount = parseFloat(amountInput.value) || 0;

    // Show/hide payment request container based on child selection
    if (childSelect && paymentContainer) {
        const selectedOption = childSelect.options[childSelect.selectedIndex];
        const hasCoparents = selectedOption && parseInt(selectedOption.dataset.coparents || 0) > 0;
        const coparentName = selectedOption?.dataset.coparentName || '';

        if (childSelect.value && hasCoparents) {
            paymentContainer.classList.remove('hidden');

            // Update co-parent name display
            if (coparentNameDisplay && coparentName) {
                coparentNameDisplay.textContent = coparentName;
            } else if (coparentNameDisplay) {
                coparentNameDisplay.textContent = 'Co-Parent';
            }

            // Update co-parent initial
            if (coparentInitial && coparentName) {
                coparentInitial.textContent = coparentName.charAt(0).toUpperCase();
            } else if (coparentInitial) {
                coparentInitial.textContent = '?';
            }
        } else {
            paymentContainer.classList.add('hidden');
        }
    }

    // Calculate split amount
    let splitPercentage = 50;
    const customRadio = document.querySelector('input[name="split_percentage"][value="custom"]');
    if (customRadio && customRadio.checked) {
        const customInput = document.getElementById('customSplitInput');
        splitPercentage = parseFloat(customInput?.value) || 50;
    }

    const requestAmount = (amount * splitPercentage / 100);
    preview.textContent = '$' + requestAmount.toFixed(2);
}

function resetPaymentOptions() {
    const requestCheckbox = document.getElementById('requestPaymentCheckbox');
    const optionsContainer = document.getElementById('paymentOptionsContainer');
    const customContainer = document.getElementById('customSplitContainer');
    const splitRadio = document.querySelector('input[name="split_percentage"][value="50"]');

    if (requestCheckbox) requestCheckbox.checked = false;
    if (optionsContainer) optionsContainer.classList.add('hidden');
    if (customContainer) customContainer.classList.add('hidden');
    if (splitRadio) splitRadio.checked = true;
}

// Update payment amount when transaction amount changes
document.querySelector('input[name="amount"]')?.addEventListener('input', updatePaymentAmount);

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
    clearReceipt();
}

// Receipt file handling
const receiptInput = document.getElementById('receiptInput');
const receiptPreview = document.getElementById('receiptPreview');
const receiptFileName = document.getElementById('receiptFileName');
const receiptDropZone = document.getElementById('receiptDropZone');
const receiptUploadPrompt = document.getElementById('receiptUploadPrompt');

// Click to upload
if (receiptDropZone) {
    receiptDropZone.addEventListener('click', function() {
        receiptInput.click();
    });
}

if (receiptInput) {
    receiptInput.addEventListener('change', function() {
        handleReceiptFile(this.files[0]);
    });
}

function handleReceiptFile(file) {
    if (file) {
        receiptFileName.textContent = file.name;
        receiptPreview.classList.remove('hidden');
        receiptDropZone.classList.add('hidden');
    }
}

function clearReceipt() {
    if (receiptInput) {
        receiptInput.value = '';
    }
    if (receiptPreview) {
        receiptPreview.classList.add('hidden');
    }
    if (receiptDropZone) {
        receiptDropZone.classList.remove('hidden');
    }
}

// Drag and drop for receipt
if (receiptDropZone) {
    receiptDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        receiptDropZone.classList.add('border-primary', 'bg-primary/5');
    });

    receiptDropZone.addEventListener('dragleave', () => {
        receiptDropZone.classList.remove('border-primary', 'bg-primary/5');
    });

    receiptDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        receiptDropZone.classList.remove('border-primary', 'bg-primary/5');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            receiptInput.files = dt.files;
            handleReceiptFile(file);
        }
    });
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
