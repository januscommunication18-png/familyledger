@extends('layouts.dashboard')

@section('page-name', 'Shared Expenses')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Shared Expenses</h1>
            <p class="text-sm text-slate-500">Expenses shared with your co-parent for your children</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Child Filter --}}
            <select id="childFilter" class="select select-bordered select-sm" onchange="applyChildFilter()">
                <option value="all" {{ $childFilter === 'all' ? 'selected' : '' }}>All Children</option>
                @foreach($children as $child)
                <option value="{{ $child->id }}" {{ $childFilter == $child->id ? 'selected' : '' }}>{{ $child->full_name }}</option>
                @endforeach
            </select>
            <a href="{{ route('expenses.dashboard') }}" class="btn btn-primary btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add Expense
            </a>
        </div>
    </div>

    @if($children->isEmpty())
    {{-- No Children with Co-Parenting --}}
    <div class="flex items-center justify-center min-h-[40vh]">
        <div class="text-center max-w-md">
            <div class="w-24 h-24 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">No Co-Parenting Set Up</h2>
            <p class="text-slate-500 mb-4">
                Set up co-parenting for your children to start tracking shared expenses.
            </p>
            <a href="{{ route('coparenting.index') }}" class="btn btn-primary">
                Go to Co-Parenting
            </a>
        </div>
    </div>
    @else
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Shared Expenses --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-500">Total Shared Expenses</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="1" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800">${{ number_format($totalExpenses, 2) }}</p>
                <p class="text-xs text-slate-500">{{ $childFilter === 'all' ? 'For all children' : 'For selected child' }}</p>
            </div>
        </div>

        {{-- Children Summary --}}
        @foreach($children->take(2) as $child)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-500">{{ $child->full_name }}</span>
                    <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                        <span class="text-sm font-bold text-violet-600">{{ strtoupper(substr($child->first_name, 0, 1)) }}</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800">${{ number_format($expensesByChild[$child->id] ?? 0, 2) }}</p>
                <p class="text-xs text-slate-500">Shared expenses</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Transactions List --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Shared Transactions</h3>

            @if($transactions->isEmpty())
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <h3 class="font-semibold text-slate-700 mb-1">No Shared Expenses Yet</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Start tracking shared expenses by adding transactions and marking them as "Share with Co-Parent".
                </p>
                <a href="{{ route('expenses.dashboard') }}" class="btn btn-primary btn-sm">
                    Go to Expenses
                </a>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Child</th>
                            <th>Category</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td class="whitespace-nowrap">
                                <span class="text-sm">{{ $transaction->transaction_date->format('M j, Y') }}</span>
                            </td>
                            <td>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $transaction->description }}</p>
                                    @if($transaction->payee)
                                    <p class="text-xs text-slate-500">{{ $transaction->payee }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary badge-sm">{{ $transaction->sharedForChild?->full_name ?? 'Unknown' }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $transaction->category?->display_icon ?? '📦' }}</span>
                                    <span class="text-sm">{{ $transaction->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <span class="font-medium {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $transaction->formatted_amount }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $transactions->appends(['child_id' => $childFilter])->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
function applyChildFilter() {
    const filter = document.getElementById('childFilter').value;
    const url = new URL(window.location.href);
    if (filter === 'all') {
        url.searchParams.delete('child_id');
    } else {
        url.searchParams.set('child_id', filter);
    }
    window.location.href = url.toString();
}
</script>
@endsection
