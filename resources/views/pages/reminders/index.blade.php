@extends('layouts.dashboard')

@section('title', 'Reminders')
@section('page-name', 'Reminders')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Reminders</li>
@endsection

@section('page-title', 'Reminders')
@section('page-description', 'Never miss important dates, renewals, and deadlines.')

@php
    function getDueText($dueDate) {
        $now = now()->startOfDay();
        $due = $dueDate->startOfDay();
        $diff = $now->diffInDays($due, false);

        if ($diff < 0) {
            $days = abs($diff);
            return $days == 1 ? 'Overdue by 1 day' : "Overdue by {$days} days";
        } elseif ($diff == 0) {
            return 'Due today';
        } elseif ($diff == 1) {
            return 'Due tomorrow';
        } elseif ($diff <= 7) {
            return "Due in {$diff} days";
        } else {
            return 'Due ' . $dueDate->format('M j, Y');
        }
    }

    function getDueClass($dueDate) {
        $now = now()->startOfDay();
        $due = $dueDate->startOfDay();
        $diff = $now->diffInDays($due, false);

        if ($diff < 0) {
            return 'text-error';
        } elseif ($diff == 0) {
            return 'text-warning';
        } elseif ($diff == 1) {
            return 'text-info';
        } else {
            return 'text-base-content/60';
        }
    }

    function getPriorityBadge($priority) {
        return match($priority) {
            'high' => '<span class="badge badge-error badge-sm">High</span>',
            'medium' => '<span class="badge badge-warning badge-sm">Medium</span>',
            'low' => '<span class="badge badge-info badge-sm">Low</span>',
            default => '',
        };
    }

    function getCategoryIcon($category) {
        return match($category) {
            'home_chores' => 'icon-[tabler--home]',
            'bills' => 'icon-[tabler--receipt]',
            'health' => 'icon-[tabler--heart]',
            'kids' => 'icon-[tabler--friends]',
            'car' => 'icon-[tabler--car]',
            'pet_care' => 'icon-[tabler--paw]',
            'family_rituals' => 'icon-[tabler--users]',
            'appointments' => 'icon-[tabler--calendar-event]',
            'groceries' => 'icon-[tabler--shopping-cart]',
            'school' => 'icon-[tabler--school]',
            default => 'icon-[tabler--bell]',
        };
    }
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                    <div class="text-sm text-base-content/60">Active</div>
                </div>
            </div>
            <div class="card bg-error/10 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-2xl font-bold text-error">{{ $stats['overdue'] }}</div>
                    <div class="text-sm text-error/80">Overdue</div>
                </div>
            </div>
            <div class="card bg-warning/10 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-2xl font-bold text-warning">{{ $stats['today'] }}</div>
                    <div class="text-sm text-warning/80">Due Today</div>
                </div>
            </div>
            <div class="card bg-success/10 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-2xl font-bold text-success">{{ $stats['completed'] }}</div>
                    <div class="text-sm text-success/80">Completed</div>
                </div>
            </div>
        </div>

        <!-- Birthday Reminders -->
        @if($birthdayReminders->count() > 0)
        <div class="card bg-base-100 shadow-sm border-l-4 border-pink-500">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-pink-500"><path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/></svg>
                    <h2 class="card-title text-pink-500">Upcoming Birthdays ({{ $birthdayReminders->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($birthdayReminders as $birthday)
                    <div class="flex items-center justify-between p-3 rounded-lg
                        @if($birthday['days_until'] === 0) bg-pink-100 border border-pink-300
                        @elseif($birthday['days_until'] === 1) bg-pink-50 border border-pink-200
                        @else bg-base-200/50 @endif">
                        <div class="flex items-center gap-3">
                            @if($birthday['person']->profile_image_url)
                                <img src="{{ $birthday['person']->profile_image_url }}" alt="{{ $birthday['person']->full_name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center">
                                    <span class="text-pink-600 font-semibold text-sm">{{ $birthday['person']->initials }}</span>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('people.show', $birthday['person']) }}" class="font-medium hover:text-pink-600 transition-colors">
                                    {{ $birthday['person']->full_name }}
                                </a>
                                <div class="text-sm text-base-content/60">
                                    @if($birthday['days_until'] === 0)
                                        <span class="text-pink-600 font-semibold">Today!</span>
                                    @elseif($birthday['days_until'] === 1)
                                        <span class="text-pink-500 font-medium">Tomorrow</span>
                                    @else
                                        In {{ $birthday['days_until'] }} days
                                    @endif
                                    <span class="mx-1">&bull;</span>
                                    {{ $birthday['birthday_date']->format('M j') }}
                                    <span class="mx-1">&bull;</span>
                                    Turning {{ $birthday['age'] }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-sm bg-pink-100 text-pink-700 border-pink-200">
                                {{ $birthday['person']->relationship_name }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Important Date Reminders -->
        @if($importantDateReminders->count() > 0)
        <div class="card bg-base-100 shadow-sm border-l-4 border-rose-500">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-500"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <h2 class="card-title text-rose-500">Important Dates ({{ $importantDateReminders->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($importantDateReminders as $dateReminder)
                    <div class="flex items-center justify-between p-3 rounded-lg
                        @if($dateReminder['days_until'] === 0) bg-rose-100 border border-rose-300
                        @elseif($dateReminder['days_until'] === 1) bg-rose-50 border border-rose-200
                        @else bg-base-200/50 @endif">
                        <div class="flex items-center gap-3">
                            @if($dateReminder['person']->profile_image_url)
                                <img src="{{ $dateReminder['person']->profile_image_url }}" alt="{{ $dateReminder['person']->full_name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
                                    <span class="text-rose-600 font-semibold text-sm">{{ $dateReminder['person']->initials }}</span>
                                </div>
                            @endif
                            <div>
                                <div class="font-medium">{{ $dateReminder['important_date']->label }}</div>
                                <div class="text-sm text-base-content/60">
                                    <a href="{{ route('people.show', $dateReminder['person']) }}" class="hover:text-rose-600 transition-colors">
                                        {{ $dateReminder['person']->full_name }}
                                    </a>
                                    <span class="mx-1">&bull;</span>
                                    @if($dateReminder['days_until'] === 0)
                                        <span class="text-rose-600 font-semibold">Today!</span>
                                    @elseif($dateReminder['days_until'] === 1)
                                        <span class="text-rose-500 font-medium">Tomorrow</span>
                                    @else
                                        In {{ $dateReminder['days_until'] }} days
                                    @endif
                                    <span class="mx-1">&bull;</span>
                                    {{ $dateReminder['next_date']->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($dateReminder['is_recurring'])
                                <span class="badge badge-sm bg-blue-100 text-blue-700 border-blue-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                                    Yearly
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Overdue Reminders -->
        @if($overdue->count() > 0)
        <div class="card bg-base-100 shadow-sm border-l-4 border-error">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--alert-circle] size-5 text-error"></span>
                    <h2 class="card-title text-error">Overdue ({{ $overdue->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($overdue as $reminder)
                    <div class="flex items-center justify-between p-3 bg-error/5 rounded-lg border border-error/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                                <span class="{{ getCategoryIcon($reminder->category) }} size-5 text-error"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reminder->title }}</div>
                                <div class="text-sm {{ getDueClass($reminder->due_date) }} font-medium">
                                    {{ getDueText($reminder->due_date) }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! getPriorityBadge($reminder->priority) !!}
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </label>
                                <ul tabindex="0" class="dropdown-menu dropdown-menu-sm">
                                    <li>
                                        <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full text-left">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                Mark Complete
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('reminders.snooze', $reminder) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="duration" value="1day">
                                            <button type="submit" class="w-full text-left">
                                                <span class="icon-[tabler--clock] size-4"></span>
                                                Snooze 1 Day
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Today's Reminders -->
        @if($today->count() > 0)
        <div class="card bg-base-100 shadow-sm border-l-4 border-warning">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-event] size-5 text-warning"></span>
                    <h2 class="card-title text-warning">Today ({{ $today->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($today as $reminder)
                    <div class="flex items-center justify-between p-3 bg-warning/5 rounded-lg border border-warning/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                                <span class="{{ getCategoryIcon($reminder->category) }} size-5 text-warning"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reminder->title }}</div>
                                <div class="text-sm {{ getDueClass($reminder->due_date) }} font-medium">
                                    {{ getDueText($reminder->due_date) }}
                                    @if($reminder->due_time)
                                        <span class="text-base-content/60">at {{ \Carbon\Carbon::parse($reminder->due_time)->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! getPriorityBadge($reminder->priority) !!}
                            <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm btn-circle text-success">
                                    <span class="icon-[tabler--check] size-5"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Tomorrow's Reminders -->
        @if($tomorrow->count() > 0)
        <div class="card bg-base-100 shadow-sm border-l-4 border-info">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-plus] size-5 text-info"></span>
                    <h2 class="card-title text-info">Tomorrow ({{ $tomorrow->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($tomorrow as $reminder)
                    <div class="flex items-center justify-between p-3 bg-info/5 rounded-lg border border-info/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-info/10 flex items-center justify-center">
                                <span class="{{ getCategoryIcon($reminder->category) }} size-5 text-info"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reminder->title }}</div>
                                <div class="text-sm {{ getDueClass($reminder->due_date) }} font-medium">
                                    {{ getDueText($reminder->due_date) }}
                                    @if($reminder->due_time)
                                        <span class="text-base-content/60">at {{ \Carbon\Carbon::parse($reminder->due_time)->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! getPriorityBadge($reminder->priority) !!}
                            <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm btn-circle text-success">
                                    <span class="icon-[tabler--check] size-5"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- This Week's Reminders -->
        @if($thisWeek->count() > 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-week] size-5 text-primary"></span>
                    <h2 class="card-title">This Week ({{ $thisWeek->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($thisWeek as $reminder)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="{{ getCategoryIcon($reminder->category) }} size-5 text-primary"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reminder->title }}</div>
                                <div class="text-sm {{ getDueClass($reminder->due_date) }}">
                                    {{ getDueText($reminder->due_date) }} ({{ $reminder->due_date->format('D, M j') }})
                                    @if($reminder->due_time)
                                        <span class="text-base-content/60">at {{ \Carbon\Carbon::parse($reminder->due_time)->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! getPriorityBadge($reminder->priority) !!}
                            <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm btn-circle text-success">
                                    <span class="icon-[tabler--check] size-5"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Upcoming Reminders -->
        @if($upcoming->count() > 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-month] size-5 text-base-content/60"></span>
                    <h2 class="card-title text-base-content/80">Upcoming ({{ $upcoming->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($upcoming as $reminder)
                    <div class="flex items-center justify-between p-3 bg-base-200/30 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="{{ getCategoryIcon($reminder->category) }} size-5 text-base-content/60"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $reminder->title }}</div>
                                <div class="text-sm text-base-content/60">
                                    {{ $reminder->due_date->format('D, M j, Y') }}
                                    @if($reminder->due_time)
                                        at {{ \Carbon\Carbon::parse($reminder->due_time)->format('g:i A') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! getPriorityBadge($reminder->priority) !!}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if($stats['total'] == 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="text-center py-12 text-base-content/60">
                    <span class="icon-[tabler--bell] size-16 opacity-30"></span>
                    <p class="mt-4 text-lg font-medium">No reminders set</p>
                    <p class="text-sm">Create reminders for important dates and deadlines</p>
                    <a href="{{ route('goals-todo.tasks.create') }}" class="btn btn-primary mt-4">
                        <span class="icon-[tabler--bell-plus] size-4"></span>
                        Create Your First Reminder
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Completed Reminders (Collapsible) -->
        @if($completed->count() > 0)
        <div class="collapse collapse-arrow bg-base-100 shadow-sm">
            <input type="checkbox" />
            <div class="collapse-title">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                    <span class="font-medium">Completed ({{ $completed->count() }})</span>
                </div>
            </div>
            <div class="collapse-content">
                <div class="space-y-2 pt-2">
                    @foreach($completed->take(10) as $reminder)
                    <div class="flex items-center justify-between p-3 bg-base-200/30 rounded-lg opacity-60">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                            <div>
                                <div class="font-medium line-through">{{ $reminder->title }}</div>
                                <div class="text-sm text-base-content/60">
                                    Completed {{ $reminder->completed_at?->diffForHumans() ?? 'recently' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Add</h2>
                <div class="space-y-2">
                    <a href="{{ route('goals-todo.tasks.create') }}?category=bills" class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--receipt] size-4"></span>
                        Bill Payment
                    </a>
                    <a href="{{ route('goals-todo.tasks.create') }}?category=health" class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--vaccine] size-4"></span>
                        Medical Appointment
                    </a>
                    <a href="{{ route('goals-todo.tasks.create') }}?category=kids" class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--school] size-4"></span>
                        School Event
                    </a>
                    <a href="{{ route('goals-todo.tasks.create') }}?category=car" class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--car] size-4"></span>
                        Car Maintenance
                    </a>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--bulb] size-5 text-warning"></span>
                    Pro Tip
                </h2>
                <p class="text-sm text-base-content/70">
                    Create tasks with due dates in the Tasks section to have them appear here as reminders.
                </p>
                <a href="{{ route('goals-todo.index') }}" class="btn btn-sm btn-primary mt-3">
                    Go to Tasks
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
