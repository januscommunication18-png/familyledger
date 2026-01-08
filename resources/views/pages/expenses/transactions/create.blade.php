@extends('layouts.app')

@section('title', 'Add Transaction')

@section('content')
<div class="min-h-screen bg-slate-100">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-lg mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-slate-800">Add Transaction</h1>
            <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                Close
            </a>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <form action="{{ route('expenses.transactions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6 space-y-5">
                    {{-- Transaction Type --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Type</span></label>
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

                    {{-- Amount & Date Row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Amount</span></label>
                            <div class="join">
                                <span class="join-item flex items-center px-3 bg-base-200 border border-base-300">$</span>
                                <input type="number" name="amount" step="0.01" min="0.01" class="input input-bordered join-item flex-1 w-full" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Date</span></label>
                            <input type="text" name="transaction_date" class="input input-bordered" value="{{ now()->format('Y-m-d') }}" data-datepicker placeholder="Select date" required>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Description</span></label>
                        <input type="text" name="description" class="input input-bordered" placeholder="e.g., Grocery shopping" required>
                    </div>

                    {{-- Payee --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Payee</span></label>
                        <input type="text" name="payee" class="input input-bordered" placeholder="e.g., Walmart">
                    </div>

                    {{-- Category --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Category</span></label>
                        <select name="category_id" class="select select-bordered">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->display_icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Receipt Upload --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Receipt (optional)</span></label>
                        <div id="receiptDropZone" class="border-2 border-dashed border-slate-300 rounded-lg p-4 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors">
                            <div id="receiptUploadPrompt" class="flex items-center justify-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm text-slate-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-slate-400">Image or PDF (max 5MB)</p>
                                </div>
                            </div>
                            <input type="file" name="receipt" id="receiptInput" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                        </div>
                        <div id="receiptPreview" class="hidden mt-2">
                            <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                                <span id="receiptFileName" class="text-sm text-slate-600 truncate flex-1"></span>
                                <button type="button" onclick="clearReceipt()" class="btn btn-ghost btn-xs btn-square text-slate-400 hover:text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Co-Parenting Options --}}
                    @if($children->count() > 0)
                    <div class="bg-slate-50 rounded-lg p-5 space-y-4">
                        <p class="font-medium text-slate-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Co-Parenting Options
                        </p>

                        {{-- Shared Expense Checkbox --}}
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_shared" value="1" id="isSharedCheckbox" class="checkbox checkbox-primary" onchange="toggleSharedWith()">
                            <span>Share with Co-Parent</span>
                        </label>

                        {{-- Select Child --}}
                        <div id="sharedWithContainer" class="form-control hidden">
                            <label class="label py-1"><span class="label-text">For Which Child?</span></label>
                            <select name="shared_for_child_id" id="sharedForChildSelect" class="select select-bordered" onchange="updatePaymentAmount()">
                                <option value="">Select a child...</option>
                                @foreach($children as $child)
                                @php
                                    // Determine the other parent's name based on relationship
                                    if (!empty($child->isCoparentChild)) {
                                        // User is co-parent, other parent is the owner
                                        $otherParentName = $child->otherParentName ?? 'Parent';
                                        $hasOtherParent = !empty($child->otherParentId);
                                    } else {
                                        // User is owner, other parent is the co-parent
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
                        <div id="paymentRequestContainer" class="hidden space-y-4 p-4 bg-violet-50 rounded-lg border border-violet-200">
                            {{-- Co-parent name display --}}
                            <div class="flex items-center gap-3 pb-3 border-b border-violet-200">
                                <div class="avatar placeholder">
                                    <div class="bg-violet-200 text-violet-700 rounded-full w-10 h-10">
                                        <span id="coparentInitial" class="text-sm">?</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-violet-600">Request from</p>
                                    <p id="coparentNameDisplay" class="font-medium text-violet-800">Co-Parent</p>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="request_payment" value="1" id="requestPaymentCheckbox" class="checkbox checkbox-primary" onchange="togglePaymentOptions()">
                                <span class="font-medium text-violet-800">Request Payment</span>
                            </label>

                            <div id="paymentOptionsContainer" class="hidden space-y-4">
                                {{-- Split Percentage --}}
                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-sm">Split</span></label>
                                    <div class="flex gap-2">
                                        <label class="flex-1">
                                            <input type="radio" name="split_percentage" value="50" class="peer hidden" checked onchange="updatePaymentAmount()">
                                            <div class="btn btn-sm btn-block peer-checked:btn-primary peer-checked:text-white">50/50</div>
                                        </label>
                                        <label class="flex-1">
                                            <input type="radio" name="split_percentage" value="custom" class="peer hidden" onchange="toggleCustomSplit()">
                                            <div class="btn btn-sm btn-block peer-checked:btn-primary peer-checked:text-white">Custom</div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Custom Split Input --}}
                                <div id="customSplitContainer" class="form-control hidden">
                                    <label class="label py-1"><span class="label-text text-sm">Co-Parent Pays (%)</span></label>
                                    <input type="number" name="custom_split_percentage" id="customSplitInput" min="1" max="100" value="50" class="input input-bordered input-sm" onchange="updatePaymentAmount()">
                                </div>

                                {{-- Payment Amount Preview --}}
                                <div class="bg-white rounded-lg p-3 border border-violet-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-slate-600">Request amount:</span>
                                        <span id="paymentAmountPreview" class="text-lg font-bold text-violet-700">$0.00</span>
                                    </div>
                                </div>

                                {{-- Note --}}
                                <div class="form-control">
                                    <input type="text" name="payment_note" class="input input-bordered input-sm" placeholder="Note (optional)">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex gap-3 p-6 border-t border-slate-200 bg-slate-50 rounded-b-xl">
                    <button type="submit" class="btn btn-primary flex-1">Save Transaction</button>
                    <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost flex-1">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Receipt upload handling
const dropZone = document.getElementById('receiptDropZone');
const receiptInput = document.getElementById('receiptInput');
const receiptPreview = document.getElementById('receiptPreview');
const receiptUploadPrompt = document.getElementById('receiptUploadPrompt');
const receiptFileName = document.getElementById('receiptFileName');

if (dropZone && receiptInput) {
    dropZone.addEventListener('click', () => receiptInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
        if (e.dataTransfer.files.length) {
            receiptInput.files = e.dataTransfer.files;
            showReceiptPreview(e.dataTransfer.files[0]);
        }
    });

    receiptInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showReceiptPreview(e.target.files[0]);
        }
    });
}

function showReceiptPreview(file) {
    if (receiptPreview && receiptUploadPrompt && receiptFileName) {
        receiptFileName.textContent = file.name;
        receiptUploadPrompt.classList.add('hidden');
        receiptPreview.classList.remove('hidden');
        dropZone.classList.add('hidden');
    }
}

function clearReceipt() {
    if (receiptInput) receiptInput.value = '';
    if (receiptPreview) receiptPreview.classList.add('hidden');
    if (receiptUploadPrompt) receiptUploadPrompt.classList.remove('hidden');
    if (dropZone) dropZone.classList.remove('hidden');
}

// Co-parenting options
function toggleSharedWith() {
    const checkbox = document.getElementById('isSharedCheckbox');
    const container = document.getElementById('sharedWithContainer');
    const paymentContainer = document.getElementById('paymentRequestContainer');

    if (checkbox && container) {
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            const childSelect = container.querySelector('select[name="shared_for_child_id"]');
            if (childSelect) childSelect.value = '';
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

    if (childSelect && paymentContainer) {
        const selectedOption = childSelect.options[childSelect.selectedIndex];
        const hasCoparents = selectedOption && parseInt(selectedOption.dataset.coparents || 0) > 0;
        const coparentName = selectedOption?.dataset.coparentName || '';

        if (childSelect.value && hasCoparents) {
            paymentContainer.classList.remove('hidden');

            if (coparentNameDisplay && coparentName) {
                coparentNameDisplay.textContent = coparentName;
            } else if (coparentNameDisplay) {
                coparentNameDisplay.textContent = 'Co-Parent';
            }

            if (coparentInitial && coparentName) {
                coparentInitial.textContent = coparentName.charAt(0).toUpperCase();
            } else if (coparentInitial) {
                coparentInitial.textContent = '?';
            }
        } else {
            paymentContainer.classList.add('hidden');
        }
    }

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
</script>
@endsection
