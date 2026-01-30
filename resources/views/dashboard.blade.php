@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-name', 'Home')

@section('page-title', 'Welcome back, ' . (explode(' ', auth()->user()->name ?? 'User')[0]) . '!')
@section('page-description', 'Here\'s what\'s happening with your family today.')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .gradient-primary {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }
    .gradient-success {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }
    .gradient-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    .gradient-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    .gradient-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }
    .gradient-pink {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }
</style>
@endpush

@section('content')
{{-- Subscription Expiration Alert --}}
@if($subscriptionAlert)
<div
    x-data="{
        dismissed: sessionStorage.getItem('{{ $subscriptionAlert['dismissKey'] }}') === 'true',
        dismiss() {
            this.dismissed = true;
            sessionStorage.setItem('{{ $subscriptionAlert['dismissKey'] }}', 'true');
        }
    }"
    x-show="!dismissed"
    x-cloak
    class="mb-6"
>
    <div class="card shadow-sm border {{ $subscriptionAlert['severity'] === 'error' ? 'bg-gradient-to-r from-red-50 to-rose-50 border-red-200' : ($subscriptionAlert['severity'] === 'warning' ? 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200' : 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200') }}">
        <div class="card-body py-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-full shrink-0 {{ $subscriptionAlert['severity'] === 'error' ? 'bg-red-100' : ($subscriptionAlert['severity'] === 'warning' ? 'bg-amber-100' : 'bg-blue-100') }}">
                        @if($subscriptionAlert['type'] === 'expired')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $subscriptionAlert['severity'] === 'error' ? 'rgb(239 68 68)' : 'rgb(245 158 11)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $subscriptionAlert['severity'] === 'warning' ? 'rgb(245 158 11)' : 'rgb(59 130 246)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-semibold {{ $subscriptionAlert['severity'] === 'error' ? 'text-red-800' : ($subscriptionAlert['severity'] === 'warning' ? 'text-amber-800' : 'text-blue-800') }}">
                            {{ $subscriptionAlert['title'] }}
                        </h3>
                        <p class="text-sm mt-1 {{ $subscriptionAlert['severity'] === 'error' ? 'text-red-600' : ($subscriptionAlert['severity'] === 'warning' ? 'text-amber-600' : 'text-blue-600') }}">
                            {{ $subscriptionAlert['message'] }}
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('subscription.index') }}" class="btn btn-sm {{ $subscriptionAlert['severity'] === 'error' ? 'btn-error' : ($subscriptionAlert['severity'] === 'warning' ? 'btn-warning' : 'btn-primary') }}">
                                {{ $subscriptionAlert['cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
                <button
                    @click="dismiss()"
                    class="btn btn-ghost btn-sm btn-circle shrink-0 {{ $subscriptionAlert['severity'] === 'error' ? 'hover:bg-red-100' : ($subscriptionAlert['severity'] === 'warning' ? 'hover:bg-amber-100' : 'hover:bg-blue-100') }}"
                    title="Dismiss"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Pending Co-parent Invitations --}}
@if($pendingCoparentInvites->count() > 0)
<div class="mb-6">
    <div class="card bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 rounded-full bg-pink-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-pink-800">Co-parenting Invitations</h3>
                    <p class="text-sm text-pink-600">You have {{ $pendingCoparentInvites->count() }} pending co-parent {{ Str::plural('invitation', $pendingCoparentInvites->count()) }}</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($pendingCoparentInvites as $invite)
                <div class="flex items-center justify-between p-3 rounded-lg bg-white/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($invite->inviter->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ $invite->inviter->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $invite->familyMembers->count() }} {{ Str::plural('child', $invite->familyMembers->count()) }}:
                                {{ $invite->familyMembers->pluck('first_name')->take(2)->join(', ') }}{{ $invite->familyMembers->count() > 2 ? '...' : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('collaborator.accept', $invite->token) }}" class="btn btn-sm btn-primary">View Invite</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Main Stats Cards -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <!-- Family Members -->
    <a href="{{ route('family-circle.index') }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-primary">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Family</p>
                    <p class="text-2xl font-bold text-primary">{{ $familyMembers->count() }}</p>
                    <p class="text-xs text-base-content/50">{{ $familyCircles->count() }} {{ Str::plural('circle', $familyCircles->count()) }}</p>
                </div>
                <div class="p-2 rounded-xl gradient-primary">
                    <span class="icon-[tabler--users] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>

    <!-- Assets -->
    <a href="{{ route('assets.index') }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-success">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Assets</p>
                    <p class="text-2xl font-bold text-success">{{ $assets->count() }}</p>
                    <p class="text-xs text-base-content/50">${{ number_format($totalAssetValue, 0) }}</p>
                </div>
                <div class="p-2 rounded-xl gradient-success">
                    <span class="icon-[tabler--building-bank] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>

    <!-- Documents -->
    <a href="{{ route('legal.index') }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-info">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Documents</p>
                    <p class="text-2xl font-bold text-info">{{ $totalDocuments }}</p>
                    <p class="text-xs text-base-content/50">{{ $legalDocuments }} legal</p>
                </div>
                <div class="p-2 rounded-xl gradient-info">
                    <span class="icon-[tabler--file-text] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>

    <!-- Tasks -->
    <a href="{{ route('goals-todo.index') }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-warning">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Tasks</p>
                    <p class="text-2xl font-bold text-warning">{{ $pendingTasks }}</p>
                    <p class="text-xs text-base-content/50">{{ $overdueTasks }} overdue</p>
                </div>
                <div class="p-2 rounded-xl gradient-warning">
                    <span class="icon-[tabler--checklist] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>

    <!-- Goals -->
    <a href="{{ route('goals-todo.goals.index') }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-secondary">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Goals</p>
                    <p class="text-2xl font-bold text-secondary">{{ $activeGoals }}</p>
                    <p class="text-xs text-base-content/50">{{ $completedGoals }} done</p>
                </div>
                <div class="p-2 rounded-xl bg-gradient-to-br from-secondary to-purple-600">
                    <span class="icon-[tabler--target] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>

    <!-- Insurance -->
    <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="stat-card card bg-base-100 shadow-sm hover:shadow-lg border-l-4 border-l-pink-500">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Insurance</p>
                    <p class="text-2xl font-bold text-pink-500">{{ $activeInsurance }}</p>
                    <p class="text-xs text-base-content/50">{{ $expiringInsurance }} expiring</p>
                </div>
                <div class="p-2 rounded-xl gradient-pink">
                    <span class="icon-[tabler--shield-check] size-5 text-white"></span>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Income vs Expenses Chart -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg">Income vs Expenses</h2>
                    <p class="text-sm text-base-content/60">Last 6 months overview</p>
                </div>
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">
                    View All
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </a>
            </div>
            <div id="income-expense-chart" class="h-64"></div>
        </div>
    </div>

    <!-- Asset Allocation Chart -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg">Asset Allocation</h2>
                    <p class="text-sm text-base-content/60">Portfolio breakdown by category</p>
                </div>
                <a href="{{ route('assets.index') }}" class="btn btn-ghost btn-sm">
                    View All
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </a>
            </div>
            @if($assets->count() > 0)
            <div id="asset-allocation-chart" class="h-64"></div>
            @else
            <div class="flex flex-col items-center justify-center h-64 text-base-content/50">
                <span class="icon-[tabler--chart-pie] size-16 opacity-30"></span>
                <p class="mt-4">No assets tracked yet</p>
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm mt-3">Add Asset</a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Monthly Spending & Tasks Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Monthly Spending Breakdown -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg">This Month's Spending</h2>
                    <p class="text-sm text-base-content/60">{{ now()->format('F Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">${{ number_format($thisMonthTotal, 0) }}</p>
                    @php
                        $percentChange = $lastMonthTotal > 0 ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
                    @endphp
                    @if($percentChange != 0)
                    <p class="text-xs {{ $percentChange > 0 ? 'text-error' : 'text-success' }}">
                        <span class="icon-[tabler--trending-{{ $percentChange > 0 ? 'up' : 'down' }}] size-3"></span>
                        {{ abs(round($percentChange)) }}% vs last month
                    </p>
                    @endif
                </div>
            </div>
            @if(count($expenseCategoryLabels) > 0)
            <div id="expense-category-chart" class="h-48"></div>
            @else
            <div class="flex flex-col items-center justify-center h-48 text-base-content/50">
                <span class="icon-[tabler--receipt] size-12 opacity-30"></span>
                <p class="mt-2 text-sm">No expenses this month</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="card bg-base-100 shadow-sm lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg">Upcoming Tasks</h2>
                    <p class="text-sm text-base-content/60">Due in the next 7 days</p>
                </div>
                <a href="{{ route('goals-todo.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            @if($upcomingTasks->count() > 0)
            <div class="space-y-3">
                @foreach($upcomingTasks as $task)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors">
                    <div class="flex-shrink-0">
                        @php
                            $priorityColors = [
                                'high' => 'text-error',
                                'medium' => 'text-warning',
                                'low' => 'text-success',
                            ];
                        @endphp
                        <span class="icon-[tabler--circle-filled] size-3 {{ $priorityColors[$task->priority] ?? 'text-base-content/30' }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">{{ $task->title }}</p>
                        <p class="text-xs text-base-content/60">
                            Due {{ $task->due_date?->format('M j') }}
                            @if($task->due_date?->isToday()) <span class="badge badge-warning badge-xs">Today</span> @endif
                            @if($task->due_date?->isTomorrow()) <span class="badge badge-info badge-xs">Tomorrow</span> @endif
                        </p>
                    </div>
                    <a href="{{ route('goals-todo.index') }}" class="btn btn-ghost btn-xs btn-circle">
                        <span class="icon-[tabler--chevron-right] size-4"></span>
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-base-content/50">
                <span class="icon-[tabler--checklist] size-12 opacity-30"></span>
                <p class="mt-2">No upcoming tasks</p>
                <a href="{{ route('goals-todo.tasks.create') }}" class="btn btn-primary btn-sm mt-3">Add Task</a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Family Members Overview -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">Family Members</h2>
                <a href="{{ route('family-circle.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            @if($familyMembers->count() > 0)
            <div class="space-y-3">
                @foreach($familyMembers->take(5) as $member)
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder">
                        <div class="w-10 h-10 rounded-full {{ $member->profile_photo_url ? '' : 'bg-gradient-to-br from-primary to-secondary text-white' }}">
                            @if($member->profile_photo_url)
                            <img src="{{ $member->profile_photo_url }}" alt="{{ $member->full_name }}" class="rounded-full">
                            @else
                            <span class="text-sm font-medium">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">{{ $member->full_name }}</p>
                        <p class="text-xs text-base-content/60 capitalize">{{ str_replace('_', ' ', $member->relationship ?? 'Member') }}</p>
                    </div>
                    @if($member->date_of_birth)
                    <span class="badge badge-ghost badge-sm">{{ $member->date_of_birth->age }}y</span>
                    @endif
                </div>
                @endforeach
            </div>
            @if($familyMembers->count() > 5)
            <p class="text-center text-sm text-base-content/60 mt-3">+{{ $familyMembers->count() - 5 }} more members</p>
            @endif
            @else
            <div class="flex flex-col items-center justify-center py-6 text-base-content/50">
                <span class="icon-[tabler--users] size-12 opacity-30"></span>
                <p class="mt-2">No family members yet</p>
                <a href="{{ route('family-circle.index') }}" class="btn btn-primary btn-sm mt-3">Add Member</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Birthdays & Alerts -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <span class="icon-[tabler--cake] size-5 text-pink-500"></span>
                Upcoming Birthdays
            </h2>
            @if($upcomingBirthdays->count() > 0)
            <div class="space-y-3">
                @foreach($upcomingBirthdays as $member)
                <div class="flex items-center gap-3 p-2 rounded-lg bg-pink-50">
                    <div class="avatar placeholder">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 text-white">
                            <span class="text-xs font-medium">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-sm">{{ $member->first_name }}</p>
                        <p class="text-xs text-base-content/60">{{ $member->next_birthday->format('M j') }}</p>
                    </div>
                    <span class="badge badge-pink badge-sm">
                        @if($member->days_until == 0) Today!
                        @elseif($member->days_until == 1) Tomorrow
                        @else {{ $member->days_until }}d
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4 text-base-content/50">
                <span class="icon-[tabler--cake] size-10 opacity-30"></span>
                <p class="mt-2 text-sm">No upcoming birthdays</p>
            </div>
            @endif

            <!-- Expiring Documents Alert -->
            @if($expiringDocuments->count() > 0)
            <div class="divider my-4"></div>
            <h3 class="font-semibold text-sm flex items-center gap-2 mb-3">
                <span class="icon-[tabler--alert-triangle] size-4 text-warning"></span>
                Expiring Documents
            </h3>
            <div class="space-y-2">
                @foreach($expiringDocuments->take(3) as $doc)
                <div class="flex items-center gap-2 text-sm p-2 rounded bg-warning/10">
                    <span class="icon-[tabler--file-alert] size-4 text-warning"></span>
                    <div class="flex-1 min-w-0">
                        <p class="truncate">{{ $doc->familyMember->first_name }}'s {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</p>
                        <p class="text-xs text-base-content/60">Expires {{ $doc->expiry_date->format('M j, Y') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('family-circle.index') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--user-plus] size-4 text-primary"></span>
                        Add Member
                    </a>
                    <a href="{{ route('goals-todo.tasks.create') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--plus] size-4 text-warning"></span>
                        New Task
                    </a>
                    <a href="{{ route('legal.create') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--file-plus] size-4 text-info"></span>
                        Add Document
                    </a>
                    <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--receipt] size-4 text-success"></span>
                        Track Expense
                    </a>
                    <a href="{{ route('assets.create') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--home-plus] size-4 text-secondary"></span>
                        Add Asset
                    </a>
                    <a href="{{ route('goals-todo.goals.create') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                        <span class="icon-[tabler--target] size-4 text-pink-500"></span>
                        New Goal
                    </a>
                </div>
            </div>
        </div>

        <!-- Other Stats -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">At a Glance</h2>
                <div class="space-y-3">
                    <a href="{{ route('pets.index') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--paw] size-5 text-amber-500"></span>
                            <span class="text-sm">Pets</span>
                        </div>
                        <span class="badge badge-ghost">{{ $pets }}</span>
                    </a>
                    <a href="{{ route('people.index') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--address-book] size-5 text-blue-500"></span>
                            <span class="text-sm">Contacts</span>
                        </div>
                        <span class="badge badge-ghost">{{ $contacts }}</span>
                    </a>
                    <a href="{{ route('shopping.index') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--shopping-cart] size-5 text-green-500"></span>
                            <span class="text-sm">Shopping Items</span>
                        </div>
                        <span class="badge badge-ghost">{{ $pendingShoppingItems }}</span>
                    </a>
                    <a href="{{ route('journal.index') }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--notebook] size-5 text-purple-500"></span>
                            <span class="text-sm">Journal Entries</span>
                        </div>
                        <span class="badge badge-ghost">{{ $recentJournalEntries->count() }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Income vs Expense Chart
    const incomeExpenseOptions = {
        series: [{
            name: 'Income',
            data: @json($incomeData)
        }, {
            name: 'Expenses',
            data: @json($expenseData)
        }],
        chart: {
            type: 'bar',
            height: 256,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        colors: ['#22c55e', '#ef4444'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4,
            },
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: @json($chartLabels),
            labels: {
                style: {
                    fontSize: '12px',
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return '$' + value.toLocaleString();
                }
            }
        },
        fill: { opacity: 1 },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "$" + val.toLocaleString()
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4,
        }
    };

    new ApexCharts(document.querySelector("#income-expense-chart"), incomeExpenseOptions).render();

    // Asset Allocation Chart
    @if($assets->count() > 0)
    const assetAllocationOptions = {
        series: @json($assetChartData),
        chart: {
            type: 'donut',
            height: 256,
            fontFamily: 'inherit',
        },
        labels: @json($assetChartLabels),
        colors: ['#6366f1', '#22c55e', '#f59e0b', '#ec4899'],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Value',
                            formatter: function (w) {
                                return '$' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "$" + val.toLocaleString()
                }
            }
        }
    };

    new ApexCharts(document.querySelector("#asset-allocation-chart"), assetAllocationOptions).render();
    @endif

    // Expense Category Chart
    @if(count($expenseCategoryLabels) > 0)
    const expenseCategoryOptions = {
        series: @json($expenseCategoryData),
        chart: {
            type: 'pie',
            height: 192,
            fontFamily: 'inherit',
        },
        labels: @json($expenseCategoryLabels),
        colors: ['#6366f1', '#22c55e', '#f59e0b', '#ec4899', '#06b6d4', '#8b5cf6'],
        dataLabels: { enabled: false },
        legend: {
            position: 'right',
            fontSize: '12px',
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "$" + val.toLocaleString()
                }
            }
        }
    };

    new ApexCharts(document.querySelector("#expense-category-chart"), expenseCategoryOptions).render();
    @endif
});
</script>
@endpush
