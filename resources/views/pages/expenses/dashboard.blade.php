@extends('layouts.dashboard')

@section('page-name', 'Expenses Dashboard')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-slate-800">{{ $showAllBudgets ? 'All Budgets' : $budget->name }}</h1>
                <div class="relative">
                    <button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('budget-dropdown').classList.toggle('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <ul id="budget-dropdown" class="hidden absolute left-0 top-full mt-1 z-50 menu p-2 shadow-lg bg-base-100 rounded-box w-56 border border-slate-200">
                        {{-- All Budgets Option --}}
                        <li>
                            <a href="{{ route('expenses.dashboard', ['budget_id' => 'all']) }}" class="{{ $showAllBudgets ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <span class="truncate">All Budgets</span>
                                @if($showAllBudgets)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </a>
                        </li>
                        <li class="border-t my-1"></li>
                        @foreach($allBudgets as $b)
                        <li>
                            <a href="{{ route('expenses.dashboard', ['budget_id' => $b->id]) }}" class="{{ !$showAllBudgets && $budget && $b->id === $budget->id ? 'active' : '' }}">
                                <span class="truncate">{{ $b->name }}</span>
                                @if(!$showAllBudgets && $budget && $b->id === $budget->id)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </a>
                        </li>
                        @endforeach
                        <li class="border-t mt-2 pt-2">
                            <a href="{{ route('expenses.budget.create') }}" class="text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Create New Budget
                            </a>
                        </li>
                    </ul>
                </div>
                <script>
                    document.addEventListener('click', function(e) {
                        const dropdown = document.getElementById('budget-dropdown');
                        const button = dropdown?.previousElementSibling;
                        if (dropdown && !dropdown.contains(e.target) && !button?.contains(e.target)) {
                            dropdown.classList.add('hidden');
                        }
                    });
                </script>
            </div>
            <p class="text-sm text-slate-500">
                @if($showAllBudgets)
                    Combined view of {{ $allBudgets->count() }} budget(s)
                @else
                    {{ $budget->period_label }} Budget &bull; Started {{ $budget->start_date->format('M j, Y') }}
                @endif
            </p>

            {{-- Period Selector (only for single budget) --}}
            @if(!$showAllBudgets && $budget && $currentPeriodLabel)
            <div class="flex items-center gap-2 mt-2">
                {{-- Previous Period Button --}}
                @if(count($availablePeriods) > 1)
                <a href="{{ route('expenses.dashboard', ['budget_id' => $budget->id, 'period' => $periodOffset - 1]) }}"
                   class="btn btn-ghost btn-xs btn-square" title="Previous Period">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                @endif

                {{-- Period Dropdown --}}
                <div class="relative">
                    <button type="button" class="btn btn-ghost btn-sm gap-1" onclick="document.getElementById('period-dropdown').classList.toggle('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <span class="{{ $periodOffset === 0 ? 'text-primary font-medium' : '' }}">{{ $currentPeriodLabel }}</span>
                        @if($periodOffset !== 0)
                        <span class="badge badge-warning badge-xs">Past</span>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <ul id="period-dropdown" class="hidden absolute left-0 top-full mt-1 z-50 menu p-2 shadow-lg bg-base-100 rounded-box w-64 border border-slate-200 max-h-64 overflow-y-auto">
                        @foreach($availablePeriods as $period)
                        <li>
                            <a href="{{ route('expenses.dashboard', ['budget_id' => $budget->id, 'period' => $period['offset']]) }}"
                               class="{{ $period['offset'] === $periodOffset ? 'active' : '' }} {{ !$period['has_transactions'] ? 'opacity-50' : '' }}">
                                <span class="truncate">{{ $period['label'] }}</span>
                                @if($period['is_current'])
                                <span class="badge badge-primary badge-xs">Current</span>
                                @endif
                                @if($period['offset'] === $periodOffset)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Next Period Button (only if not current) --}}
                @if($periodOffset < 0)
                <a href="{{ route('expenses.dashboard', ['budget_id' => $budget->id, 'period' => $periodOffset + 1]) }}"
                   class="btn btn-ghost btn-xs btn-square" title="Next Period">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                @endif

                {{-- Return to Current Period (if viewing past) --}}
                @if($periodOffset !== 0)
                <a href="{{ route('expenses.dashboard', ['budget_id' => $budget->id, 'period' => 0]) }}"
                   class="btn btn-primary btn-xs gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                    Current
                </a>
                @endif
            </div>
            <script>
                document.addEventListener('click', function(e) {
                    const dropdown = document.getElementById('period-dropdown');
                    const button = dropdown?.previousElementSibling;
                    if (dropdown && !dropdown.contains(e.target) && !button?.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            </script>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('expenses.budget.create') }}" class="btn btn-outline btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                New Budget
            </a>
            @if(!$showAllBudgets)
            <a href="{{ route('expenses.transactions.create') }}" class="btn btn-primary btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add Transaction
            </a>
            <a href="{{ route('expenses.budget.edit', $budget) }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
            @endif
        </div>
    </div>

    {{-- Triggered Alerts --}}
    @if($triggeredAlerts->count() > 0)
    <div class="card bg-amber-50 border border-amber-200 mb-6">
        <div class="card-body py-4">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(217 119 6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-amber-800">Budget Alerts</h4>
                    @foreach($triggeredAlerts as $alert)
                    <p class="text-sm text-amber-700">{{ $alert->description }}</p>
                    @endforeach
                </div>
                <a href="{{ route('expenses.alerts') }}" class="btn btn-ghost btn-sm text-amber-700">Manage</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Budget --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-500">Total Budget</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="1" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800">${{ number_format($totalBudget, 2) }}</p>
                <p class="text-xs text-slate-500">{{ $showAllBudgets ? 'Combined budget' : $budget->period_label . ' budget' }}</p>
            </div>
        </div>

        {{-- Total Spent --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-500">Total Spent</span>
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(239 68 68)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="m5 12 7 7 7-7"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800">${{ number_format($totalSpent, 2) }}</p>
                <div class="flex items-center gap-2">
                    <progress class="progress progress-error w-full h-2" value="{{ $progress }}" max="100"></progress>
                    <span class="text-xs text-slate-500">{{ $progress }}%</span>
                </div>
            </div>
        </div>

        {{-- Remaining --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-500">Remaining</span>
                    <div class="w-10 h-10 rounded-xl {{ $remaining >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $remaining >= 0 ? 'rgb(16 185 129)' : 'rgb(239 68 68)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $remaining >= 0 ? 'text-emerald-600' : 'text-red-600' }}">${{ number_format(abs($remaining), 2) }}</p>
                <p class="text-xs {{ $remaining >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $remaining >= 0 ? 'Under budget' : 'Over budget' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Category Breakdown / Goals --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    @if($showAllBudgets)
                        <h3 class="font-semibold text-slate-800">Budget Breakdown</h3>
                    @elseif($budget && $budget->is_traditional)
                        <h3 class="font-semibold text-slate-800">Goals</h3>
                    @else
                        <h3 class="font-semibold text-slate-800">{{ $budget->is_envelope ? 'Envelopes' : 'Categories' }}</h3>
                        <a href="{{ route('expenses.categories', $budget) }}" class="btn btn-ghost btn-xs">Manage</a>
                    @endif
                </div>
                <div class="space-y-3">
                    @if($showAllBudgets)
                        {{-- Show budget breakdown instead of categories when viewing all --}}
                        @foreach($allBudgets as $b)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl bg-emerald-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700 truncate">{{ $b->name }}</span>
                                    <span class="text-sm text-slate-500">${{ number_format($b->getTotalSpent(), 2) }} / ${{ number_format($b->total_amount, 2) }}</span>
                                </div>
                                @php $bProgress = $b->getProgressPercentage(); @endphp
                                <div class="flex items-center gap-2">
                                    <progress class="progress w-full h-2 {{ $bProgress >= 100 ? 'progress-error' : ($bProgress >= 80 ? 'progress-warning' : 'progress-success') }}" value="{{ min($bProgress, 100) }}" max="100"></progress>
                                    <span class="text-xs text-slate-500 w-10 text-right">{{ $bProgress }}%</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @elseif($budget && $budget->is_traditional && $goals->count() > 0)
                        {{-- Show goals for traditional budgets --}}
                        @foreach($goals as $goal)
                        @php
                            $current = $goal->calculated_current ?? 0;
                            $target = $goal->target_amount;
                            $percentage = $target > 0 ? min(100, round(($current / $target) * 100, 1)) : 0;

                            // Different color logic based on goal type
                            if ($goal->type === 'expense') {
                                // For expense goals (spending limits), lower is better
                                $progressClass = $percentage > 100 ? 'progress-error' : ($percentage >= 80 ? 'progress-warning' : 'progress-success');
                                $statusColor = $percentage > 100 ? 'text-red-600' : ($percentage >= 80 ? 'text-amber-600' : 'text-emerald-600');
                            } else {
                                // For income/saving goals, higher is better
                                $progressClass = $percentage >= 100 ? 'progress-success' : 'progress-info';
                                $statusColor = $percentage >= 100 ? 'text-emerald-600' : 'text-blue-600';
                            }
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style="background-color: {{ $goal->display_color }}20">
                                {{ $goal->display_icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700 truncate">
                                        {{ $goal->name }}
                                        <span class="badge badge-xs ml-1" style="background-color: {{ $goal->display_color }}20; color: {{ $goal->display_color }}">
                                            {{ $goal->type_label }}
                                        </span>
                                    </span>
                                    <span class="text-sm {{ $statusColor }}">${{ number_format($current, 2) }} / ${{ number_format($target, 2) }}</span>
                                </div>
                                @if($goal->description)
                                <p class="text-xs text-slate-500 mb-1 truncate">{{ $goal->description }}</p>
                                @endif
                                <div class="flex items-center gap-2">
                                    <progress class="progress w-full h-2 {{ $progressClass }}" value="{{ min($percentage, 100) }}" max="100"></progress>
                                    <span class="text-xs text-slate-500 w-10 text-right">{{ $percentage }}%</span>
                                </div>
                                @if($goal->target_date && $goal->days_remaining !== null)
                                <p class="text-xs text-slate-400 mt-1">
                                    @if($goal->days_remaining > 0)
                                        {{ $goal->days_remaining }} days remaining
                                    @elseif($goal->days_remaining === 0)
                                        Due today
                                    @else
                                        Overdue
                                    @endif
                                </p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @elseif($budget && $budget->is_traditional)
                        <p class="text-sm text-slate-500 text-center py-4">No goals set up yet.</p>
                    @else
                        @forelse($categorySpending as $data)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style="background-color: {{ $data['category']->color }}20">
                                {{ $data['category']->display_icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-slate-700 truncate">{{ $data['category']->name }}</span>
                                    <span class="text-sm text-slate-500">${{ number_format($data['spent'], 2) }} / ${{ number_format($data['allocated'], 2) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <progress class="progress w-full h-2 {{ $data['percentage'] >= 100 ? 'progress-error' : ($data['percentage'] >= 80 ? 'progress-warning' : 'progress-success') }}" value="{{ min($data['percentage'], 100) }}" max="100"></progress>
                                    <span class="text-xs text-slate-500 w-10 text-right">{{ $data['percentage'] }}%</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500 text-center py-4">No categories set up yet.</p>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-800">Recent Transactions</h3>
                    <div class="flex items-center gap-2">
                        {{-- Shared Filter Dropdown --}}
                        <select id="sharedFilter" class="select select-bordered select-xs" onchange="applySharedFilter()">
                            <option value="all" {{ $sharedFilter === 'all' ? 'selected' : '' }}>All Expenses</option>
                            <option value="shared" {{ $sharedFilter === 'shared' ? 'selected' : '' }}>Shared Only</option>
                            @foreach($children as $child)
                            <option value="{{ $child->id }}" {{ $sharedFilter == $child->id ? 'selected' : '' }}>For {{ $child->full_name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('expenses.transactions') }}" class="btn btn-ghost btn-xs">View All</a>
                    </div>
                </div>
                <div class="space-y-2">
                    @forelse($recentTransactions as $transaction)
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm" style="background-color: {{ $transaction->category?->color ?? '#6b7280' }}20">
                            {{ $transaction->category?->display_icon ?? '📦' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">
                                {{ $transaction->description }}
                                @if($transaction->isFromMobile())
                                <span class="badge badge-info badge-xs ml-1 gap-0.5" title="Added via Mobile App">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                                    Mobile
                                </span>
                                @endif
                                @if($transaction->is_shared)
                                <span class="badge badge-primary badge-xs ml-1">Shared</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $transaction->formatted_date }} &bull; {{ $transaction->category_name }}
                                @if($showAllBudgets && $transaction->budget)
                                &bull; <span class="text-emerald-600">{{ $transaction->budget->name }}</span>
                                @endif
                            </p>
                            @if($transaction->is_shared && $transaction->shared_child_name)
                            <p class="text-xs text-violet-600 truncate">For: {{ $transaction->shared_child_name }}</p>
                            @endif
                        </div>
                        <span class="text-sm font-medium {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $transaction->formatted_amount }}
                        </span>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 text-center py-4">
                        @if($sharedFilter === 'shared')
                            No shared transactions found.
                        @elseif(is_numeric($sharedFilter))
                            No transactions for this child.
                        @else
                            No transactions yet.
                        @endif
                    </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Trend Chart --}}
    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800">Spending Trend</h3>
                <a href="{{ route('expenses.reports') }}" class="btn btn-ghost btn-xs">View Reports</a>
            </div>
            <div class="h-48 flex items-end justify-between gap-2">
                @php
                    $maxAmount = collect($monthlyTrend)->max('amount') ?: 1;
                @endphp
                @foreach($monthlyTrend as $month)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full bg-emerald-500 rounded-t transition-all" style="height: {{ ($month['amount'] / $maxAmount) * 150 }}px"></div>
                    <span class="text-xs text-slate-500">{{ $month['month'] }}</span>
                    <span class="text-xs font-medium text-slate-700">${{ number_format($month['amount']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        @if(!$showAllBudgets)
        <a href="{{ route('expenses.transactions.create') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">Add Transaction</span>
            </div>
        </a>
        @endif
        <a href="{{ route('expenses.import') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">Import CSV</span>
            </div>
        </a>
        @if(!$showAllBudgets)
        <a href="{{ route('expenses.categories', $budget) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(245 158 11)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">Manage Categories</span>
            </div>
        </a>
        @else
        <a href="{{ route('expenses.budget.create') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(245 158 11)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">New Budget</span>
            </div>
        </a>
        @endif
        <a href="{{ route('expenses.reports') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">View Reports</span>
            </div>
        </a>
        <a href="{{ route('expenses.transactions') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body items-center text-center py-4">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-700">All Transactions</span>
            </div>
        </a>
    </div>
</div>

{{-- Add Transaction Modal --}}
<div id="transactionModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Add Transaction</h3>
                <button onclick="closeModal()" class="btn btn-ghost btn-sm btn-square">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <form id="transactionForm" action="{{ route('expenses.transactions.store') }}" method="POST">
                @csrf

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
                        <input type="text" name="transaction_date" class="input input-bordered" value="{{ now()->format('Y-m-d') }}" data-datepicker placeholder="Select date" required>
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

function closeModal() {
    document.getElementById('transactionModal').classList.add('hidden');
}

function applySharedFilter() {
    const filter = document.getElementById('sharedFilter').value;
    const url = new URL(window.location.href);
    if (filter === 'all') {
        url.searchParams.delete('shared_filter');
    } else {
        url.searchParams.set('shared_filter', filter);
    }
    window.location.href = url.toString();
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
