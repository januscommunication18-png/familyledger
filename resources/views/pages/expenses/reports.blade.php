@extends('layouts.dashboard')

@section('page-name', 'Reports')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Reports</h1>
            <p class="text-sm text-slate-500">Analyze your spending patterns</p>
        </div>
        <a href="{{ route('expenses.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline btn-sm gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Export CSV
        </a>
    </div>

    {{-- Date Range Filter --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <form action="{{ route('expenses.reports') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="form-control">
                    <label class="label py-1"><span class="label-text text-xs">From</span></label>
                    <input type="text" name="start_date" value="{{ $startDate }}" class="input input-bordered input-sm" data-datepicker placeholder="Start date">
                </div>
                <div class="form-control">
                    <label class="label py-1"><span class="label-text text-xs">To</span></label>
                    <input type="text" name="end_date" value="{{ $endDate }}" class="input input-bordered input-sm" data-datepicker placeholder="End date">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <div class="flex gap-1 ml-auto">
                    <a href="{{ route('expenses.reports', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-ghost btn-xs">This Month</a>
                    <a href="{{ route('expenses.reports', ['start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-ghost btn-xs">Last Month</a>
                    <a href="{{ route('expenses.reports', ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->endOfYear()->format('Y-m-d')]) }}" class="btn btn-ghost btn-xs">This Year</a>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Category Breakdown --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-800 mb-4">Spending by Category</h3>
                @php
                    $totalSpent = $categoryBreakdown->sum('total');
                @endphp
                @if($categoryBreakdown->count() > 0)
                <div class="space-y-3">
                    @foreach($categoryBreakdown as $item)
                    @php
                        $category = $categories->get($item->category_id);
                        $percentage = $totalSpent > 0 ? ($item->total / $totalSpent) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background-color: {{ $category?->color ?? '#6b7280' }}20">
                            {{ $category?->display_icon ?? '📦' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-slate-700">{{ $category?->name ?? 'Uncategorized' }}</span>
                                <span class="text-sm text-slate-600">${{ number_format($item->total, 2) }}</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-2">
                                <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $category?->color ?? '#6b7280' }}"></div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 w-12 text-right">{{ round($percentage, 1) }}%</span>
                    </div>
                    @endforeach

                    <div class="border-t pt-3 mt-3">
                        <div class="flex items-center justify-between font-semibold">
                            <span>Total</span>
                            <span>${{ number_format($totalSpent, 2) }}</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-slate-500">No spending data for this period.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Daily Spending Chart --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-800 mb-4">Daily Spending</h3>
                @if($dailySpending->count() > 0)
                <div class="h-48 flex items-end gap-1">
                    @php
                        $maxDaily = $dailySpending->max('total') ?: 1;
                    @endphp
                    @foreach($dailySpending as $day)
                    <div class="flex-1 flex flex-col items-center" title="{{ \Carbon\Carbon::parse($day->transaction_date)->format('M j') }}: ${{ number_format($day->total, 2) }}">
                        <div class="w-full bg-emerald-500 rounded-t transition-all hover:bg-emerald-600" style="height: {{ ($day->total / $maxDaily) * 140 }}px"></div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-xs text-slate-500 mt-2">
                    <span>{{ \Carbon\Carbon::parse($startDate)->format('M j') }}</span>
                    <span>{{ \Carbon\Carbon::parse($endDate)->format('M j') }}</span>
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-slate-500">No spending data for this period.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Total Spent</p>
                <p class="text-xl font-bold text-slate-800">${{ number_format($totalSpent, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Days</p>
                <p class="text-xl font-bold text-slate-800">{{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Daily Average</p>
                @php
                    $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                    $dailyAvg = $days > 0 ? $totalSpent / $days : 0;
                @endphp
                <p class="text-xl font-bold text-slate-800">${{ number_format($dailyAvg, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4 text-center">
                <p class="text-sm text-slate-500">Categories Used</p>
                <p class="text-xl font-bold text-slate-800">{{ $categoryBreakdown->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
