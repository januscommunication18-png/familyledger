@extends('layouts.dashboard')

@section('page-name', 'Transaction Details')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Transaction Details</h1>
                <p class="text-sm text-slate-500">View transaction information</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Transactions
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Transaction Info Card --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-2xl">
                                {{ $transaction->category?->display_icon ?? '📦' }}
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-800">{{ $transaction->description }}</h2>
                                @if($transaction->payee)
                                <p class="text-sm text-slate-500">{{ $transaction->payee }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $transaction->formatted_amount }}
                            </p>
                            <span class="badge badge-sm {{ $transaction->type === 'expense' ? 'badge-error' : ($transaction->type === 'income' ? 'badge-success' : 'badge-info') }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </div>
                    </div>

                    <div class="divider my-4"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Date</p>
                            <p class="font-medium text-slate-800">{{ $transaction->transaction_date->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Category</p>
                            <p class="font-medium text-slate-800">
                                <span class="mr-1">{{ $transaction->category?->display_icon ?? '📦' }}</span>
                                {{ $transaction->category_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Source</p>
                            <p class="font-medium text-slate-800 flex items-center gap-2">
                                {{ $transaction->source_label }}
                                @if($transaction->isFromMobile())
                                <span class="badge badge-info badge-sm gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                                    Mobile
                                </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Created</p>
                            <p class="font-medium text-slate-800">{{ $transaction->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>

                    @if($transaction->is_shared)
                    <div class="divider my-4"></div>
                    <div class="bg-violet-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="badge badge-primary badge-sm">Shared Expense</span>
                            @if($paymentRequest)
                                <span class="badge badge-{{ $paymentRequest->status_color }} badge-sm">{{ $paymentRequest->status_label }}</span>
                            @endif
                        </div>
                        @if($transaction->shared_child_name)
                        <p class="text-sm text-slate-700">
                            <span class="font-medium">For:</span> {{ $transaction->shared_child_name }}
                        </p>
                        @endif

                        @if($paymentRequest)
                            <div class="mt-3 pt-3 border-t border-violet-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-slate-600">Payment Request</span>
                                    <span class="font-bold text-violet-700">{{ $paymentRequest->formatted_amount }}</span>
                                </div>
                                <p class="text-xs text-slate-500">
                                    {{ $paymentRequest->split_percentage }}% split requested by {{ $paymentRequest->requester->name }}
                                </p>

                                @if($paymentRequest->isPending() && $paymentRequest->requested_from === Auth::id())
                                    {{-- Show accept/reject for the payer --}}
                                    <div class="flex gap-2 mt-3">
                                        <a href="{{ route('expenses.payment-requests.show', $paymentRequest) }}" class="btn btn-primary btn-sm flex-1">
                                            View & Pay
                                        </a>
                                        <form action="{{ route('expenses.payment-requests.decline', $paymentRequest) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to decline this payment request?')">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm text-error">Decline</button>
                                        </form>
                                    </div>
                                @elseif($paymentRequest->isPending() && $paymentRequest->requested_by === Auth::id())
                                    {{-- Show waiting status for the requester --}}
                                    <p class="text-sm text-amber-600 mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Waiting for {{ $paymentRequest->payer->name ?? 'co-parent' }} to respond
                                    </p>
                                @elseif($paymentRequest->isPaid())
                                    <p class="text-sm text-emerald-600 mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M20 6 9 17l-5-5"/></svg>
                                        Paid via {{ $paymentRequest->payment_method_label }} on {{ $paymentRequest->paid_at?->format('M j, Y') }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-500 mt-1">This expense is shared with co-parent(s)</p>
                        @endif
                    </div>
                    @endif

                    @if($transaction->creator)
                    <div class="divider my-4"></div>
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-slate-200 text-slate-600 rounded-full w-8 h-8">
                                <span class="text-xs">{{ substr($transaction->creator->name ?? 'U', 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Created by</p>
                            <p class="text-sm font-medium text-slate-700">{{ $transaction->creator->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Receipt Sidebar --}}
        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 mb-4">Receipt</h3>

                    @if($transaction->hasReceipt())
                        <div class="space-y-4">
                            @if($transaction->isReceiptImage())
                                <div class="rounded-lg overflow-hidden border border-slate-200">
                                    <a href="{{ asset('storage/' . $transaction->receipt_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $transaction->receipt_path) }}" alt="Receipt" class="w-full h-auto">
                                    </a>
                                </div>
                            @elseif($transaction->isReceiptPdf())
                                <div class="bg-slate-50 rounded-lg p-6 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-red-500 mb-3"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                    <p class="text-sm font-medium text-slate-700">PDF Document</p>
                                </div>
                            @endif

                            <div class="flex items-center gap-2 text-sm text-slate-600 bg-slate-50 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                <span class="truncate flex-1">{{ $transaction->receipt_original_filename }}</span>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ asset('storage/' . $transaction->receipt_path) }}" target="_blank" class="btn btn-primary btn-sm flex-1 gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    View
                                </a>
                                <a href="{{ asset('storage/' . $transaction->receipt_path) }}" download="{{ $transaction->receipt_original_filename }}" class="btn btn-ghost btn-sm flex-1 gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download
                                </a>
                            </div>

                            <form action="{{ route('expenses.transactions.receipt.delete', $transaction) }}" method="POST" onsubmit="return confirm('Delete this receipt?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm w-full text-error gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    Delete Receipt
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M12 12v6"/><path d="m15 15-3-3-3 3"/></svg>
                            </div>
                            <p class="text-sm text-slate-500 mb-4">No receipt attached</p>
                            <p class="text-xs text-slate-400">Edit the transaction to add a receipt</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <form action="{{ route('expenses.transactions.delete', $transaction) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-error btn-outline btn-sm w-full gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                Delete Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
