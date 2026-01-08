@extends('layouts.dashboard')

@section('page-name', 'Payment Requests')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Payment Requests</h1>
            <p class="text-sm text-slate-500">Manage shared expense payments with co-parents</p>
        </div>
    </div>

    {{-- Pending Requests (Payments you need to make) --}}
    @if($pendingRequests->count() > 0)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-2 mb-4">
                <span class="badge badge-warning badge-lg">{{ $pendingRequests->count() }}</span>
                <h2 class="text-lg font-semibold text-slate-800">Pending Payments</h2>
            </div>
            <p class="text-sm text-slate-500 mb-4">These payment requests are waiting for your response</p>

            <div class="space-y-3">
                @foreach($pendingRequests as $request)
                <div class="border border-slate-200 rounded-lg p-4 hover:border-primary transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-slate-800">{{ $request->transaction->description }}</span>
                                <span class="badge badge-warning badge-sm">Pending</span>
                            </div>
                            <p class="text-sm text-slate-500">
                                Requested by {{ $request->requester->name }}
                                @if($request->child)
                                    for {{ $request->child->full_name }}
                                @endif
                            </p>
                            @if($request->note)
                            <p class="text-sm text-slate-600 mt-1 italic">"{{ $request->note }}"</p>
                            @endif
                            <p class="text-xs text-slate-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-violet-700">{{ $request->formatted_amount }}</p>
                            <p class="text-xs text-slate-500">{{ $request->split_percentage }}% of ${{ number_format($request->transaction->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('expenses.payment-requests.show', $request) }}" class="btn btn-primary btn-sm flex-1">
                            View & Pay
                        </a>
                        <form action="{{ route('expenses.payment-requests.decline', $request) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to decline this payment request?')">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm text-error">Decline</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Sent Requests --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Requests You Sent</h2>

            @if($sentRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>To</th>
                            <th>For</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sentRequests as $request)
                        <tr class="hover">
                            <td class="font-medium">{{ $request->transaction->description }}</td>
                            <td>{{ $request->payer->name ?? 'Unknown' }}</td>
                            <td>{{ $request->child->full_name ?? '-' }}</td>
                            <td class="font-semibold">{{ $request->formatted_amount }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status_color }} badge-sm">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td class="text-sm text-slate-500">{{ $request->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="flex gap-1">
                                    <a href="{{ route('expenses.payment-requests.show', $request) }}" class="btn btn-ghost btn-xs">View</a>
                                    @if($request->isPending())
                                    <form action="{{ route('expenses.payment-requests.cancel', $request) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this payment request?')">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-xs text-error">Cancel</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <p class="text-slate-500">No payment requests sent yet</p>
                <p class="text-sm text-slate-400 mt-1">When you create a shared expense and request payment, it will appear here</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment History --}}
    @if($receivedHistory->count() > 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Payment History</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>From</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receivedHistory as $request)
                        <tr class="hover">
                            <td class="font-medium">{{ $request->transaction->description }}</td>
                            <td>{{ $request->requester->name }}</td>
                            <td class="font-semibold">{{ $request->formatted_amount }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status_color }} badge-sm">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td class="text-sm">{{ $request->payment_method_label }}</td>
                            <td class="text-sm text-slate-500">{{ $request->responded_at?->format('M j, Y') ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($pendingRequests->count() === 0 && $sentRequests->count() === 0 && $receivedHistory->count() === 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto rounded-full bg-violet-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">No Payment Requests</h3>
                <p class="text-slate-500 max-w-md mx-auto">
                    Payment requests help you split shared expenses with co-parents.
                    When adding a transaction, check "Share with Co-Parent" and "Request Payment" to get started.
                </p>
                <a href="{{ route('expenses.transactions') }}" class="btn btn-primary btn-sm mt-4">
                    Go to Transactions
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
