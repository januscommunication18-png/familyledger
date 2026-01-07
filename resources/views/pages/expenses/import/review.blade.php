@extends('layouts.dashboard')

@section('page-name', 'Review Import')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Review Import</h1>
        <p class="text-sm text-slate-500">Review transactions before importing</p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Total Transactions</p>
                <p class="text-2xl font-bold text-slate-800">{{ $total }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Will Import</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $total - $duplicates }}</p>
            </div>
        </div>
        <div class="card bg-amber-50 border border-amber-200">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-amber-700">Duplicates (skipped)</p>
                <p class="text-2xl font-bold text-amber-600">{{ $duplicates }}</p>
            </div>
        </div>
    </div>

    {{-- Transactions Preview --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-3">Transactions to Import</h3>
            <div class="overflow-x-auto max-h-96">
                <table class="table table-sm">
                    <thead class="sticky top-0 bg-base-100">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr class="{{ ($t['is_duplicate'] ?? false) ? 'opacity-50' : '' }}">
                            <td class="text-sm">{{ $t['transaction_date'] }}</td>
                            <td class="text-sm">{{ Str::limit($t['description'], 50) }}</td>
                            <td class="text-sm text-right font-medium">${{ number_format($t['amount'], 2) }}</td>
                            <td>
                                @if($t['is_duplicate'] ?? false)
                                <span class="badge badge-warning badge-sm">Duplicate</span>
                                @else
                                <span class="badge badge-success badge-sm">New</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex gap-2">
        <a href="{{ route('expenses.import') }}" class="btn btn-ghost">Start Over</a>
        <form action="{{ route('expenses.import.process') }}" method="POST" class="flex-1">
            @csrf
            <button type="submit" class="btn btn-primary w-full">
                Import {{ $total - $duplicates }} Transactions
            </button>
        </form>
    </div>
</div>
@endsection
