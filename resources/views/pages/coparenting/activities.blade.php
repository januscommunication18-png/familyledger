@extends('layouts.dashboard')

@section('page-name', 'Co-parenting Activities')

@section('content')
<div class="p-4 lg:p-6">
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
            <h1 class="text-2xl font-bold text-slate-800">Activities</h1>
            <p class="text-slate-500">Manage shared activities and events for your children.</p>
        </div>
        <button type="button" onclick="openActivityModal()" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Add Activity
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Upcoming Activities --}}
        <div class="xl:col-span-1">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 text-lg mb-4">Upcoming Activities</h3>

                    @forelse($upcomingActivities as $activity)
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 -mx-1 border-l-4" style="border-color: {{ $activity->color_hex }}">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 truncate">{{ $activity->title }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $activity->starts_at->format('M j, Y') }}
                                @if(!$activity->is_all_day)
                                    at {{ $activity->starts_at->format('g:i A') }}
                                @endif
                            </p>
                            @if($activity->is_recurring)
                                <span class="badge badge-ghost badge-xs mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                                    {{ $activity->recurrence_info['label'] ?? 'Recurring' }}
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <button type="button" onclick="editActivity({{ $activity->id }})" class="btn btn-ghost btn-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <p class="text-slate-500 text-sm">No upcoming activities</p>
                    </div>
                    @endforelse

                    @if($activities->count() > 5)
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <p class="text-sm text-slate-500">{{ $activities->count() }} total activities</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- All Activities List --}}
            <div class="card bg-base-100 shadow-sm mt-6">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 text-lg mb-4">All Activities</h3>

                    <div class="space-y-2 max-h-[400px] overflow-y-auto">
                        @forelse($activities as $activity)
                        <div class="flex items-center justify-between p-2 rounded hover:bg-slate-50 group">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $activity->color_hex }}"></div>
                                <span class="text-sm text-slate-700">{{ $activity->title }}</span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="editActivity({{ $activity->id }})" class="btn btn-ghost btn-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form action="{{ route('coparenting.activities.delete', $activity) }}" method="POST" class="inline" onsubmit="return confirm('Delete this activity?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500 text-center py-4">No activities yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Calendar View --}}
        <div class="xl:col-span-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    {{-- Calendar Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800 text-lg">{{ $currentDate->format('F Y') }}</h3>
                        <div class="flex items-center gap-2">
                            @php
                                $prevMonth = $currentDate->copy()->subMonth();
                                $nextMonth = $currentDate->copy()->addMonth();
                            @endphp
                            <a href="{{ route('coparenting.activities', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-sm btn-ghost">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                            <a href="{{ route('coparenting.activities', ['month' => now()->month, 'year' => now()->year]) }}" class="btn btn-sm btn-outline">Today</a>
                            <a href="{{ route('coparenting.activities', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-sm btn-ghost">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
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
                            $startOfMonth = $currentDate->copy()->startOfMonth();
                            $endOfMonth = $currentDate->copy()->endOfMonth();
                            $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon\Carbon::SUNDAY);
                            $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon\Carbon::SATURDAY);
                            $today = now()->format('Y-m-d');
                        @endphp

                        <div class="grid grid-cols-7">
                            @for($date = $startOfCalendar->copy(); $date->lte($endOfCalendar); $date->addDay())
                                @php
                                    $dateKey = $date->format('Y-m-d');
                                    $isCurrentMonth = $date->month === $currentDate->month;
                                    $isToday = $dateKey === $today;
                                    $dayEvents = $calendarEvents[$dateKey] ?? [];
                                @endphp
                                <div class="min-h-[100px] border-b border-r border-slate-200 p-1 {{ !$isCurrentMonth ? 'bg-slate-50' : '' }} {{ $isToday ? 'bg-primary/5' : '' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm {{ $isToday ? 'bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center font-bold' : ($isCurrentMonth ? 'text-slate-700' : 'text-slate-400') }}">
                                            {{ $date->day }}
                                        </span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach(array_slice($dayEvents, 0, 3) as $event)
                                        <div class="text-xs px-1.5 py-0.5 rounded truncate text-white cursor-pointer hover:opacity-80" style="background-color: {{ $event['color'] }}" title="{{ $event['title'] }}">
                                            {{ $event['title'] }}
                                        </div>
                                        @endforeach
                                        @if(count($dayEvents) > 3)
                                        <div class="text-xs text-slate-500 px-1">+{{ count($dayEvents) - 3 }} more</div>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Activity Modal (Custom) --}}
<div id="activity-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeActivityModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl transform transition-all">
                {{-- Close Button --}}
                <button type="button" onclick="closeActivityModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <div class="p-6">
                    <h3 class="font-bold text-xl text-slate-800 mb-6" id="activity-modal-title">Add Activity</h3>

                    <form id="activity-form" action="{{ route('coparenting.activities.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="activity-method" value="POST">

                        {{-- Title --}}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="activity-title" class="input input-bordered w-full" placeholder="e.g., Soccer Practice" required>
                        </div>

                        {{-- Description --}}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Description</span>
                            </label>
                            <textarea name="description" id="activity-description" class="textarea textarea-bordered w-full" rows="2" placeholder="Optional details..."></textarea>
                        </div>

                        {{-- Date & Time --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Start <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="starts_at" id="activity-starts" class="input input-bordered w-full" placeholder="Select date & time" required>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">End <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="ends_at" id="activity-ends" class="input input-bordered w-full" placeholder="Select date & time" required>
                            </div>
                        </div>

                        {{-- All Day --}}
                        <div class="form-control mb-4">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="is_all_day" id="activity-all-day" value="1" class="checkbox checkbox-primary" onchange="toggleTimeInputs()">
                                <span class="label-text">All day event</span>
                            </label>
                        </div>

                        {{-- Recurring --}}
                        <div class="form-control mb-4">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="is_recurring" id="activity-recurring" value="1" class="checkbox checkbox-primary" onchange="toggleRecurringFields()">
                                <span class="label-text">Recurring event</span>
                            </label>
                        </div>

                        {{-- Recurring Options --}}
                        <div id="recurring-options" class="hidden space-y-4 p-4 rounded-xl bg-slate-50 border border-slate-200 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Repeats</span>
                                </label>
                                <select name="recurrence_frequency" id="recurrence-frequency" class="select select-bordered w-full">
                                    <option value="day">Daily</option>
                                    <option value="week">Weekly</option>
                                    <option value="month">Monthly</option>
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">End</span>
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="recurrence_end_type" value="never" class="radio radio-sm" checked onchange="toggleEndOptions()">
                                        <span class="text-sm">Never</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="recurrence_end_type" value="after" class="radio radio-sm" onchange="toggleEndOptions()">
                                        <span class="text-sm">After</span>
                                        <input type="number" name="recurrence_end_after" id="recurrence-end-after" class="input input-bordered input-sm w-20" min="1" value="10" disabled>
                                        <span class="text-sm">occurrences</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="recurrence_end_type" value="on" class="radio radio-sm" onchange="toggleEndOptions()">
                                        <span class="text-sm">On</span>
                                        <input type="date" name="recurrence_end_on" id="recurrence-end-on" class="input input-bordered input-sm" disabled>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Reminder --}}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Reminder</span>
                            </label>
                            <select name="reminder_type" class="select select-bordered w-full">
                                <option value="default">Default (60 minutes before)</option>
                                <option value="none">No reminder</option>
                            </select>
                        </div>

                        {{-- Color --}}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Color</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($activityColors as $name => $hex)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="{{ $name }}" class="hidden peer" {{ $name === 'blue' ? 'checked' : '' }}>
                                    <div class="w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-slate-800 peer-checked:ring-2 peer-checked:ring-offset-2 transition-all" style="background-color: {{ $hex }}"></div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Children --}}
                        @if($children->count() > 0)
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">For which children?</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($children as $child)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="checkbox" name="children[]" value="{{ $child->id }}" class="checkbox checkbox-sm checkbox-primary">
                                    <span class="text-sm">{{ $child->first_name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-200">
                            <button type="button" onclick="closeActivityModal()" class="btn btn-ghost">Cancel</button>
                            <button type="submit" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Activity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let startPicker, endPicker, recurrenceEndPicker;

    // Initialize Flatpickr date pickers
    document.addEventListener('DOMContentLoaded', function() {
        startPicker = flatpickr('#activity-starts', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'M j, Y h:i K',
            monthSelectorType: 'static',
            time_24hr: false,
            minuteIncrement: 15,
            defaultDate: new Date(),
            onChange: function(selectedDates) {
                if (selectedDates[0] && endPicker) {
                    endPicker.set('minDate', selectedDates[0]);
                }
            }
        });

        endPicker = flatpickr('#activity-ends', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'M j, Y h:i K',
            monthSelectorType: 'static',
            time_24hr: false,
            minuteIncrement: 15,
            defaultDate: new Date(Date.now() + 3600000) // 1 hour from now
        });

        recurrenceEndPicker = flatpickr('#recurrence-end-on', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            monthSelectorType: 'static'
        });
    });

    function openActivityModal() {
        // Reset form
        document.getElementById('activity-form').reset();
        document.getElementById('activity-form').action = '{{ route("coparenting.activities.store") }}';
        document.getElementById('activity-method').value = 'POST';
        document.getElementById('activity-modal-title').textContent = 'Add Activity';
        document.getElementById('recurring-options').classList.add('hidden');

        // Reset Flatpickr to current time
        if (startPicker) {
            startPicker.setDate(new Date(), true);
        }
        if (endPicker) {
            endPicker.setDate(new Date(Date.now() + 3600000), true);
        }

        // Show modal
        document.getElementById('activity-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeActivityModal() {
        document.getElementById('activity-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    async function editActivity(activityId) {
        // Show modal first with loading state
        document.getElementById('activity-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('activity-modal-title').textContent = 'Edit Activity';

        try {
            const response = await fetch(`/coparenting/activities/${activityId}`);
            const data = await response.json();
            const activity = data.activity;

            // Update form action and method
            document.getElementById('activity-form').action = `/coparenting/activities/${activityId}`;
            document.getElementById('activity-method').value = 'PUT';

            // Populate form fields
            document.getElementById('activity-title').value = activity.title || '';
            document.getElementById('activity-description').value = activity.description || '';

            // Handle all day checkbox and date pickers
            const isAllDay = activity.is_all_day;
            document.getElementById('activity-all-day').checked = isAllDay;

            // Reinitialize date pickers with correct format based on is_all_day
            if (startPicker) startPicker.destroy();
            if (endPicker) endPicker.destroy();

            if (isAllDay) {
                startPicker = flatpickr('#activity-starts', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    monthSelectorType: 'static',
                    defaultDate: activity.starts_at
                });
                endPicker = flatpickr('#activity-ends', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    monthSelectorType: 'static',
                    defaultDate: activity.ends_at
                });
            } else {
                startPicker = flatpickr('#activity-starts', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    altInput: true,
                    altFormat: 'M j, Y h:i K',
                    monthSelectorType: 'static',
                    time_24hr: false,
                    minuteIncrement: 15,
                    defaultDate: activity.starts_at
                });
                endPicker = flatpickr('#activity-ends', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    altInput: true,
                    altFormat: 'M j, Y h:i K',
                    monthSelectorType: 'static',
                    time_24hr: false,
                    minuteIncrement: 15,
                    defaultDate: activity.ends_at
                });
            }

            // Handle recurring
            const isRecurring = activity.is_recurring;
            document.getElementById('activity-recurring').checked = isRecurring;
            document.getElementById('recurring-options').classList.toggle('hidden', !isRecurring);

            if (isRecurring) {
                document.getElementById('recurrence-frequency').value = activity.recurrence_frequency || 'week';

                // Set recurrence end type
                const endType = activity.recurrence_end_type || 'never';
                document.querySelectorAll('input[name="recurrence_end_type"]').forEach(radio => {
                    radio.checked = radio.value === endType;
                });

                document.getElementById('recurrence-end-after').value = activity.recurrence_end_after || 10;
                document.getElementById('recurrence-end-after').disabled = endType !== 'after';

                if (activity.recurrence_end_on) {
                    recurrenceEndPicker.setDate(activity.recurrence_end_on, true);
                }
                document.getElementById('recurrence-end-on').disabled = endType !== 'on';
            }

            // Set color
            document.querySelectorAll('input[name="color"]').forEach(radio => {
                radio.checked = radio.value === activity.color;
            });

            // Set children checkboxes
            document.querySelectorAll('input[name="children[]"]').forEach(checkbox => {
                checkbox.checked = activity.children.includes(parseInt(checkbox.value));
            });

        } catch (error) {
            console.error('Error fetching activity:', error);
            alert('Failed to load activity data');
            closeActivityModal();
        }
    }

    function toggleTimeInputs() {
        const isAllDay = document.getElementById('activity-all-day').checked;

        if (startPicker && endPicker) {
            // Destroy existing pickers
            startPicker.destroy();
            endPicker.destroy();

            if (isAllDay) {
                // Date only pickers
                startPicker = flatpickr('#activity-starts', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    monthSelectorType: 'static',
                    defaultDate: new Date()
                });

                endPicker = flatpickr('#activity-ends', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    monthSelectorType: 'static',
                    defaultDate: new Date()
                });
            } else {
                // Date and time pickers
                startPicker = flatpickr('#activity-starts', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    altInput: true,
                    altFormat: 'M j, Y h:i K',
                    monthSelectorType: 'static',
                    time_24hr: false,
                    minuteIncrement: 15,
                    defaultDate: new Date()
                });

                endPicker = flatpickr('#activity-ends', {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    altInput: true,
                    altFormat: 'M j, Y h:i K',
                    monthSelectorType: 'static',
                    time_24hr: false,
                    minuteIncrement: 15,
                    defaultDate: new Date(Date.now() + 3600000)
                });
            }
        }
    }

    function toggleRecurringFields() {
        const isRecurring = document.getElementById('activity-recurring').checked;
        document.getElementById('recurring-options').classList.toggle('hidden', !isRecurring);
    }

    function toggleEndOptions() {
        const selectedEnd = document.querySelector('input[name="recurrence_end_type"]:checked').value;
        document.getElementById('recurrence-end-after').disabled = selectedEnd !== 'after';
        document.getElementById('recurrence-end-on').disabled = selectedEnd !== 'on';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeActivityModal();
        }
    });
</script>
@endpush

<style>
    /* Remove last row border */
    .grid > div:nth-last-child(-n+7) {
        border-bottom: none;
    }
    /* Remove right border on last column */
    .grid > div:nth-child(7n) {
        border-right: none;
    }

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
    .flatpickr-time input {
        font-size: 14px;
    }
</style>
@endsection
