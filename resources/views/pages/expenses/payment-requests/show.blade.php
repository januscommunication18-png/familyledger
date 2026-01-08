@extends('layouts.dashboard')

@section('page-name', 'Payment Request')

@section('content')
<div class="p-4 lg:p-6 max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('expenses.payment-requests') }}" class="btn btn-ghost btn-sm btn-square">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Payment Request</h1>
            <p class="text-sm text-slate-500">Review and respond to this payment request</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Request Details --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-slate-800">Expense Details</h2>
                    <span class="badge badge-{{ $payment->status_color }}">{{ $payment->status_label }}</span>
                </div>

                <div class="space-y-4">
                    {{-- Amount --}}
                    <div class="bg-violet-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-violet-600 mb-1">Amount Requested</p>
                        <p class="text-3xl font-bold text-violet-700">{{ $payment->formatted_amount }}</p>
                        <p class="text-xs text-violet-500 mt-1">
                            {{ $payment->split_percentage }}% of ${{ number_format($payment->transaction->amount, 2) }} total
                        </p>
                    </div>

                    {{-- Transaction Info --}}
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Description</span>
                            <span class="font-medium text-slate-800">{{ $payment->transaction->description }}</span>
                        </div>
                        @if($payment->transaction->payee)
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Payee</span>
                            <span class="text-slate-700">{{ $payment->transaction->payee }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Category</span>
                            <span class="text-slate-700">
                                {{ $payment->transaction->category?->display_icon ?? '📦' }}
                                {{ $payment->transaction->category_name }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Transaction Date</span>
                            <span class="text-slate-700">{{ $payment->transaction->transaction_date->format('M j, Y') }}</span>
                        </div>
                        @if($payment->child)
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">For Child</span>
                            <span class="text-slate-700">{{ $payment->child->full_name }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Requester Info --}}
                    <div class="divider my-2"></div>
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-violet-200 text-violet-700 rounded-full w-10 h-10">
                                <span>{{ substr($payment->requester->name ?? 'U', 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Requested by</p>
                            <p class="font-medium text-slate-700">{{ $payment->requester->name }}</p>
                        </div>
                        <div class="ml-auto text-right">
                            <p class="text-xs text-slate-500">Requested on</p>
                            <p class="text-sm text-slate-700">{{ $payment->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>

                    @if($payment->note)
                    <div class="bg-slate-50 rounded-lg p-3 mt-3">
                        <p class="text-xs text-slate-500 mb-1">Note from requester</p>
                        <p class="text-sm text-slate-700 italic">"{{ $payment->note }}"</p>
                    </div>
                    @endif

                    {{-- Original Receipt --}}
                    @if($payment->transaction->hasReceipt())
                    <div class="divider my-2"></div>
                    <div>
                        <p class="text-sm text-slate-500 mb-2">Original Receipt</p>
                        <a href="{{ asset('storage/' . $payment->transaction->receipt_path) }}" target="_blank" class="btn btn-ghost btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                            View Receipt
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Form / Status --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                @if($payment->isPending() && $payment->requested_from === Auth::id())
                    {{-- Payment Form --}}
                    <h2 class="font-semibold text-slate-800 mb-4">Submit Payment</h2>

                    <form action="{{ route('expenses.payment-requests.pay', $payment) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-4">
                            {{-- Payment Method --}}
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Payment Method</span></label>
                                <select name="payment_method" class="select select-bordered" required>
                                    <option value="">Select payment method...</option>
                                    @foreach(\App\Models\SharedExpensePayment::PAYMENT_METHODS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Receipt Upload --}}
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Payment Receipt (optional)</span>
                                </label>
                                <div id="paymentReceiptDropZone" class="border-2 border-dashed border-slate-300 rounded-lg p-4 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                    </div>
                                    <p class="text-sm text-slate-600 mb-1">Upload payment confirmation</p>
                                    <p class="text-xs text-slate-400">Image or PDF (max 5MB)</p>
                                    <input type="file" name="receipt" id="paymentReceiptInput" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                </div>
                                <div id="paymentReceiptPreview" class="hidden mt-2">
                                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                                        <span id="paymentReceiptFileName" class="text-sm text-slate-600 truncate flex-1"></span>
                                        <button type="button" onclick="clearPaymentReceipt()" class="btn btn-ghost btn-xs btn-square text-slate-400 hover:text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Note --}}
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Note (optional)</span></label>
                                <textarea name="response_note" class="textarea textarea-bordered" rows="2" placeholder="Add a note about this payment..."></textarea>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="btn btn-primary flex-1 gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Confirm Payment
                            </button>
                        </div>
                    </form>

                    <div class="divider">OR</div>

                    <form action="{{ route('expenses.payment-requests.decline', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to decline this payment request?')">
                        @csrf
                        <div class="form-control mb-3">
                            <label class="label"><span class="label-text text-sm">Reason for declining (optional)</span></label>
                            <input type="text" name="response_note" class="input input-bordered input-sm" placeholder="e.g., Already paid separately">
                        </div>
                        <button type="submit" class="btn btn-ghost btn-sm w-full text-error">
                            Decline Request
                        </button>
                    </form>

                @elseif($payment->isPaid())
                    {{-- Paid Status --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <h3 class="font-semibold text-emerald-700 mb-2">Payment Completed</h3>
                        <p class="text-sm text-slate-500">Paid on {{ $payment->paid_at?->format('M j, Y g:i A') }}</p>
                    </div>

                    <div class="space-y-3 mt-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Payment Method</span>
                            <span class="font-medium text-slate-700">{{ $payment->payment_method_label }}</span>
                        </div>
                        @if($payment->response_note)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-500 mb-1">Payment Note</p>
                            <p class="text-sm text-slate-700">"{{ $payment->response_note }}"</p>
                        </div>
                        @endif
                        @if($payment->hasReceipt())
                        <div>
                            <p class="text-sm text-slate-500 mb-2">Payment Receipt</p>
                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="btn btn-primary btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                View Receipt
                            </a>
                        </div>
                        @endif
                    </div>

                @elseif($payment->isDeclined())
                    {{-- Declined Status --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(239 68 68)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </div>
                        <h3 class="font-semibold text-red-700 mb-2">Request Declined</h3>
                        <p class="text-sm text-slate-500">Declined on {{ $payment->responded_at?->format('M j, Y g:i A') }}</p>
                    </div>

                    @if($payment->response_note)
                    <div class="bg-slate-50 rounded-lg p-3 mt-4">
                        <p class="text-xs text-slate-500 mb-1">Reason</p>
                        <p class="text-sm text-slate-700">"{{ $payment->response_note }}"</p>
                    </div>
                    @endif

                @elseif($payment->isCancelled())
                    {{-- Cancelled Status --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-600 mb-2">Request Cancelled</h3>
                        <p class="text-sm text-slate-500">This request was cancelled by the requester</p>
                    </div>

                @else
                    {{-- Pending but user is the requester --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(245 158 11)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <h3 class="font-semibold text-amber-700 mb-2">Awaiting Response</h3>
                        <p class="text-sm text-slate-500">Waiting for {{ $payment->payer->name ?? 'co-parent' }} to respond</p>
                    </div>

                    <form action="{{ route('expenses.payment-requests.cancel', $payment) }}" method="POST" class="mt-4" onsubmit="return confirm('Cancel this payment request?')">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm w-full text-error">
                            Cancel Request
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Payment receipt upload handling
const paymentReceiptDropZone = document.getElementById('paymentReceiptDropZone');
const paymentReceiptInput = document.getElementById('paymentReceiptInput');
const paymentReceiptPreview = document.getElementById('paymentReceiptPreview');
const paymentReceiptFileName = document.getElementById('paymentReceiptFileName');

if (paymentReceiptDropZone) {
    paymentReceiptDropZone.addEventListener('click', () => paymentReceiptInput.click());

    paymentReceiptDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        paymentReceiptDropZone.classList.add('border-primary', 'bg-primary/5');
    });

    paymentReceiptDropZone.addEventListener('dragleave', () => {
        paymentReceiptDropZone.classList.remove('border-primary', 'bg-primary/5');
    });

    paymentReceiptDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        paymentReceiptDropZone.classList.remove('border-primary', 'bg-primary/5');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            paymentReceiptInput.files = dt.files;
            handlePaymentReceiptFile(file);
        }
    });
}

if (paymentReceiptInput) {
    paymentReceiptInput.addEventListener('change', function() {
        handlePaymentReceiptFile(this.files[0]);
    });
}

function handlePaymentReceiptFile(file) {
    if (file) {
        paymentReceiptFileName.textContent = file.name;
        paymentReceiptPreview.classList.remove('hidden');
        paymentReceiptDropZone.classList.add('hidden');
    }
}

function clearPaymentReceipt() {
    if (paymentReceiptInput) paymentReceiptInput.value = '';
    if (paymentReceiptPreview) paymentReceiptPreview.classList.add('hidden');
    if (paymentReceiptDropZone) paymentReceiptDropZone.classList.remove('hidden');
}
</script>
@endsection
