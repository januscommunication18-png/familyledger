@extends('layouts.dashboard')

@section('page-name', 'Actual Time')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6" x-data>
    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Actual Time</h1>
            <p class="text-slate-500">Track and compare actual custody time with your planned schedule.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <form id="date-filter-form" method="GET" class="flex flex-wrap items-end gap-4">
                {{-- Date Selector --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Date</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="date-picker" name="date" value="{{ $selectedDate->format('Y-m-d') }}" class="input input-bordered input-sm pl-10 w-48" readonly>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center gap-2">
                    @php
                        $prevDay = $selectedDate->copy()->subDay();
                        $nextDay = $selectedDate->copy()->addDay();
                    @endphp
                    <a href="{{ route('coparenting.actual-time', ['date' => $prevDay->format('Y-m-d')]) }}" class="btn btn-sm btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <a href="{{ route('coparenting.actual-time', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline">Today</a>
                    <a href="{{ route('coparenting.actual-time', ['date' => $nextDay->format('Y-m-d')]) }}" class="btn btn-sm btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Statistics Cards --}}
        <div class="xl:col-span-1 space-y-6">
            {{-- Time Split --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 mb-4">Time Split - {{ $monthStart->format('F Y') }}</h3>

                    @if($stats['total_days'] > 0)
                    {{-- Visual Bar --}}
                    <div class="flex h-8 rounded-lg overflow-hidden mb-4">
                        <div class="bg-pink-500 flex items-center justify-center text-white text-xs font-medium" style="width: {{ $stats['mother']['percentage'] }}%">
                            @if($stats['mother']['percentage'] >= 15)
                                {{ $stats['mother']['percentage'] }}%
                            @endif
                        </div>
                        <div class="bg-blue-500 flex items-center justify-center text-white text-xs font-medium" style="width: {{ $stats['father']['percentage'] }}%">
                            @if($stats['father']['percentage'] >= 15)
                                {{ $stats['father']['percentage'] }}%
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 rounded-lg bg-pink-50 border border-pink-200">
                            <p class="text-2xl font-bold text-pink-600">{{ $stats['mother']['days'] }}</p>
                            <p class="text-sm text-pink-700">Mother's Days</p>
                            <p class="text-xs text-pink-500">{{ $stats['mother']['percentage'] }}%</p>
                        </div>
                        <div class="text-center p-3 rounded-lg bg-blue-50 border border-blue-200">
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['father']['days'] }}</p>
                            <p class="text-sm text-blue-700">Father's Days</p>
                            <p class="text-xs text-blue-500">{{ $stats['father']['percentage'] }}%</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <p class="text-slate-500 text-sm">No check-ins recorded this month</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actual vs Planned --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 mb-4">Actual vs Planned</h3>

                    @if($comparison['planned']['total_days'] > 0)
                    <div class="space-y-4">
                        {{-- Mother --}}
                        <div class="p-3 rounded-lg bg-pink-50 border border-pink-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-pink-700">Mother</span>
                                @php
                                    $motherVar = $comparison['variance']['mother']['days'];
                                @endphp
                                <span class="badge {{ $motherVar >= 0 ? 'badge-success' : 'badge-error' }} badge-sm">
                                    {{ $motherVar >= 0 ? '+' : '' }}{{ $motherVar }} days
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-pink-500">Actual:</span>
                                    <span class="font-medium text-pink-700">{{ $comparison['actual']['mother']['days'] }} days</span>
                                </div>
                                <div>
                                    <span class="text-pink-500">Planned:</span>
                                    <span class="font-medium text-pink-700">{{ $comparison['planned']['mother']['days'] }} days</span>
                                </div>
                            </div>
                        </div>

                        {{-- Father --}}
                        <div class="p-3 rounded-lg bg-blue-50 border border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-blue-700">Father</span>
                                @php
                                    $fatherVar = $comparison['variance']['father']['days'];
                                @endphp
                                <span class="badge {{ $fatherVar >= 0 ? 'badge-success' : 'badge-error' }} badge-sm">
                                    {{ $fatherVar >= 0 ? '+' : '' }}{{ $fatherVar }} days
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-blue-500">Actual:</span>
                                    <span class="font-medium text-blue-700">{{ $comparison['actual']['father']['days'] }} days</span>
                                </div>
                                <div>
                                    <span class="text-blue-500">Planned:</span>
                                    <span class="font-medium text-blue-700">{{ $comparison['planned']['father']['days'] }} days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <p class="text-slate-500 text-sm">No schedule set up for comparison</p>
                        <a href="{{ route('coparenting.calendar') }}" class="btn btn-sm btn-primary mt-2">Create Schedule</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Check-ins for Selected Date --}}
        <div class="xl:col-span-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-slate-800 text-lg">
                                @if($selectedDate->isToday())
                                    Today's Check-ins
                                @else
                                    Check-ins
                                @endif
                            </h3>
                            <p class="text-sm text-slate-500">{{ $selectedDate->format('l, F j, Y') }}</p>
                        </div>
                        @if($selectedDate->isToday())
                        <button
                            type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-daily-checkin'))"
                            class="btn btn-primary gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Daily Check-in
                        </button>
                        @endif
                    </div>

                    @if($dateCheckins->count() > 0)
                        {{-- Check-ins List --}}
                        <div class="space-y-3">
                            @foreach($dateCheckins as $checkin)
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex items-start gap-4">
                                    {{-- Mood Emoji --}}
                                    <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-3xl shrink-0">
                                        {{ $checkin->mood_emoji }}
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-slate-800">{{ $checkin->mood_label }}</span>
                                            @if($checkin->child)
                                            <span class="text-slate-400">&bull;</span>
                                            <span class="text-sm text-slate-600">{{ $checkin->child->first_name }}</span>
                                            @endif
                                        </div>

                                        @if($checkin->notes)
                                        <p class="text-slate-600 text-sm mb-2">{{ $checkin->notes }}</p>
                                        @endif

                                        <div class="flex items-center gap-4 text-sm text-slate-500">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                {{ $checkin->created_at->format('g:i A') }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                {{ $checkin->checkedBy->name ?? 'Unknown' }}
                                            </span>
                                            @if($checkin->parent_role)
                                            <span class="badge badge-sm" style="background-color: {{ $checkin->parent_role === 'mother' ? '#fce7f3' : '#dbeafe' }}; color: {{ $checkin->parent_role === 'mother' ? '#be185d' : '#1d4ed8' }};">
                                                {{ ucfirst($checkin->parent_role) }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <p class="text-slate-500 mb-1">No check-ins recorded for this date</p>
                            @if($selectedDate->isToday())
                            <p class="text-sm text-slate-400">Click the button above to add a check-in</p>
                            @endif
                        </div>
                    @endif

                    {{-- View History Link --}}
                    <div class="mt-4 pt-4 border-t border-slate-200 text-center">
                        <a href="{{ route('coparenting.checkins') }}" class="text-primary hover:underline text-sm">
                            View Check-in History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Month Calendar --}}
    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 text-lg">{{ $monthStart->format('F Y') }}</h3>
                <div class="flex items-center gap-3 text-sm">
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded bg-pink-500"></div>
                        <span class="text-slate-600">Mother</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded bg-blue-500"></div>
                        <span class="text-slate-600">Father</span>
                    </div>
                </div>
            </div>

            {{-- Calendar Grid --}}
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                {{-- Day Headers --}}
                <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="py-3 text-center text-sm font-medium text-slate-600">{{ $day }}</div>
                    @endforeach
                </div>

                {{-- Calendar Days --}}
                @php
                    $startOfCalendar = $monthStart->copy()->startOfWeek(Carbon\Carbon::SUNDAY);
                    $endOfCalendar = $monthEnd->copy()->endOfWeek(Carbon\Carbon::SATURDAY);
                    $today = now()->format('Y-m-d');
                @endphp

                <div class="grid grid-cols-7">
                    @for($date = $startOfCalendar->copy(); $date->lte($endOfCalendar); $date->addDay())
                        @php
                            $dateKey = $date->format('Y-m-d');
                            $isCurrentMonth = $date->month === $monthStart->month;
                            $isToday = $dateKey === $today;
                            $isSelected = $dateKey === $selectedDate->format('Y-m-d');
                            $dayCheckins = $monthCheckins[$dateKey] ?? collect();
                            $hasCheckins = $dayCheckins->isNotEmpty();
                            $parentRole = $hasCheckins ? $dayCheckins->first()->parent_role : null;
                        @endphp
                        <a href="{{ route('coparenting.actual-time', ['date' => $dateKey]) }}"
                           class="min-h-[80px] border-b border-r border-slate-200 p-2 transition-colors hover:bg-slate-50 {{ !$isCurrentMonth ? 'bg-slate-50/50' : '' }} {{ $isSelected ? 'ring-2 ring-primary ring-inset' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm {{ $isToday ? 'bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center font-bold' : ($isCurrentMonth ? 'text-slate-700' : 'text-slate-400') }}">
                                    {{ $date->day }}
                                </span>
                                @if($hasCheckins)
                                <span class="w-2 h-2 rounded-full {{ $parentRole === 'mother' ? 'bg-pink-500' : 'bg-blue-500' }}"></span>
                                @endif
                            </div>
                            @if($hasCheckins)
                            <div class="space-y-1">
                                @foreach($dayCheckins->take(2) as $checkin)
                                <div class="text-xs px-1.5 py-0.5 rounded truncate {{ $checkin->parent_role === 'mother' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $checkin->mood_emoji }} {{ $checkin->child->first_name ?? '' }}
                                </div>
                                @endforeach
                                @if($dayCheckins->count() > 2)
                                <div class="text-xs text-slate-500 px-1">+{{ $dayCheckins->count() - 2 }} more</div>
                                @endif
                            </div>
                            @endif
                        </a>
                    @endfor
                </div>
            </div>

            {{-- Month Navigation --}}
            <div class="flex items-center justify-center gap-4 mt-4">
                @php
                    $prevMonth = $monthStart->copy()->subMonth();
                    $nextMonth = $monthStart->copy()->addMonth();
                @endphp
                <a href="{{ route('coparenting.actual-time', ['date' => $prevMonth->format('Y-m-d')]) }}" class="btn btn-sm btn-ghost gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    {{ $prevMonth->format('M') }}
                </a>
                <a href="{{ route('coparenting.actual-time', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline">This Month</a>
                <a href="{{ route('coparenting.actual-time', ['date' => $nextMonth->format('Y-m-d')]) }}" class="btn btn-sm btn-ghost gap-1">
                    {{ $nextMonth->format('M') }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Daily Check-in Modal --}}
@include('partials.modals.daily-checkin-modal')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('#date-picker', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            monthSelectorType: 'static',
            defaultDate: '{{ $selectedDate->format('Y-m-d') }}',
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    document.getElementById('date-filter-form').submit();
                }
            }
        });
    });
</script>

<style>
    /* Flatpickr custom styling */
    .flatpickr-calendar {
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        border: 1px solid #e2e8f0;
    }
    .flatpickr-calendar.open {
        z-index: 9999;
    }
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
        background: #6366f1;
        border-color: #6366f1;
    }
    .flatpickr-day:hover {
        background: #e0e7ff;
    }
    .flatpickr-day.today {
        border-color: #6366f1;
    }
    .flatpickr-months .flatpickr-month {
        background: #f8fafc;
        border-radius: 12px 12px 0 0;
    }
    .flatpickr-current-month {
        font-weight: 600;
    }
    .flatpickr-weekdays {
        background: #f8fafc;
    }
</style>
@endsection
