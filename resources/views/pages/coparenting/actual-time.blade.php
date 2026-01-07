@extends('layouts.dashboard')

@section('page-name', 'Actual Time')

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
            <h1 class="text-2xl font-bold text-slate-800">Actual Time</h1>
            <p class="text-slate-500">Track and compare actual custody time with your planned schedule.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                {{-- Child Selector --}}
                @if($children->count() > 1)
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Child</span>
                    </label>
                    <select name="child_id" class="select select-bordered select-sm" onchange="this.form.submit()">
                        @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ $selectedChildId == $child->id ? 'selected' : '' }}>{{ $child->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Month Selector --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Month</span>
                    </label>
                    <input type="month" name="month_picker" value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" class="input input-bordered input-sm" onchange="updateMonthYear(this)">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                </div>

                {{-- Navigation --}}
                <div class="flex items-center gap-2">
                    @php
                        $prevMonth = $monthStart->copy()->subMonth();
                        $nextMonth = $monthStart->copy()->addMonth();
                    @endphp
                    <a href="{{ route('coparenting.actual-time', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'child_id' => $selectedChildId]) }}" class="btn btn-sm btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <a href="{{ route('coparenting.actual-time', ['year' => now()->year, 'month' => now()->month, 'child_id' => $selectedChildId]) }}" class="btn btn-sm btn-outline">Today</a>
                    <a href="{{ route('coparenting.actual-time', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'child_id' => $selectedChildId]) }}" class="btn btn-sm btn-ghost">
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

        {{-- Daily Check-in Calendar --}}
        <div class="xl:col-span-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800 text-lg">Daily Check-in - {{ $monthStart->format('F Y') }}</h3>
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
                    <div class="grid grid-cols-7 gap-1">
                        {{-- Day Headers --}}
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="text-center text-sm font-medium text-slate-500 py-2">{{ $day }}</div>
                        @endforeach

                        {{-- Empty cells for start of month --}}
                        @for($i = 0; $i < $monthStart->dayOfWeek; $i++)
                        <div class="aspect-square"></div>
                        @endfor

                        {{-- Days of the month --}}
                        @foreach($calendarDays as $day)
                        @php
                            $isWeekend = $day['date']->isWeekend();
                            $bgClass = 'bg-slate-50';
                            $textClass = 'text-slate-400';

                            if ($day['checkin']) {
                                $bgClass = $day['checkin']->parent_role === 'mother' ? 'bg-pink-500' : 'bg-blue-500';
                                $textClass = 'text-white';
                            } elseif ($day['is_today']) {
                                $bgClass = 'bg-violet-100 ring-2 ring-violet-500';
                                $textClass = 'text-violet-700';
                            } elseif ($day['is_past']) {
                                $bgClass = 'bg-slate-100';
                                $textClass = 'text-slate-500';
                            }
                        @endphp
                        <button
                            type="button"
                            onclick="openCheckinModal('{{ $day['date']->format('Y-m-d') }}', '{{ $day['checkin']?->parent_role ?? '' }}')"
                            class="aspect-square rounded-lg {{ $bgClass }} {{ $textClass }} flex flex-col items-center justify-center text-sm font-medium hover:ring-2 hover:ring-slate-400 transition-all relative group"
                        >
                            <span>{{ $day['date']->day }}</span>
                            @if($day['checkin'])
                            <span class="text-[10px] opacity-75">{{ ucfirst(substr($day['checkin']->parent_role, 0, 1)) }}</span>
                            @endif

                            {{-- Tooltip --}}
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-10 pointer-events-none">
                                <div class="bg-slate-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
                                    {{ $day['date']->format('M j, Y') }}
                                    @if($day['checkin'])
                                        - {{ ucfirst($day['checkin']->parent_role) }}
                                    @else
                                        - Not recorded
                                    @endif
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <p class="text-sm text-slate-500">Click on any day to record or update who had the child that day.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Check-in Modal --}}
<dialog id="checkin-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">X</button>
        </form>

        <h3 class="font-bold text-lg mb-2">Record Check-in</h3>
        <p class="text-slate-500 text-sm mb-4" id="checkin-date-display"></p>

        <form id="checkin-form" action="{{ route('coparenting.actual-time.store') }}" method="POST">
            @csrf
            <input type="hidden" name="family_member_id" value="{{ $selectedChildId }}">
            <input type="hidden" name="date" id="checkin-date">

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Who had the child? <span class="text-error">*</span></span>
                </label>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="parent_role" value="mother" class="hidden peer" required>
                        <div class="p-4 rounded-lg border-2 border-slate-200 peer-checked:border-pink-500 peer-checked:bg-pink-50 text-center transition-all">
                            <div class="w-12 h-12 mx-auto rounded-full bg-pink-100 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </div>
                            <span class="font-medium text-slate-700">Mother</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="parent_role" value="father" class="hidden peer">
                        <div class="p-4 rounded-lg border-2 border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 text-center transition-all">
                            <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </div>
                            <span class="font-medium text-slate-700">Father</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="is_full_day" id="checkin-full-day" class="checkbox checkbox-primary" checked onchange="toggleTimeFields()">
                    <span class="label-text">Full day</span>
                </label>
            </div>

            <div id="time-fields" class="hidden grid grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Check-in Time</span>
                    </label>
                    <input type="time" name="check_in_time" class="input input-bordered">
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Check-out Time</span>
                    </label>
                    <input type="time" name="check_out_time" class="input input-bordered">
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Notes</span>
                </label>
                <textarea name="notes" class="textarea textarea-bordered" rows="2" placeholder="Optional notes..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('checkin-modal').close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function updateMonthYear(input) {
        const [year, month] = input.value.split('-');
        document.querySelector('input[name="year"]').value = year;
        document.querySelector('input[name="month"]').value = parseInt(month);
        input.form.submit();
    }

    function openCheckinModal(date, currentParent) {
        const dateObj = new Date(date + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        document.getElementById('checkin-date').value = date;
        document.getElementById('checkin-date-display').textContent = formattedDate;

        // Pre-select parent if exists
        if (currentParent) {
            document.querySelector(`input[name="parent_role"][value="${currentParent}"]`).checked = true;
        } else {
            document.querySelectorAll('input[name="parent_role"]').forEach(r => r.checked = false);
        }

        document.getElementById('checkin-modal').showModal();
    }

    function toggleTimeFields() {
        const isFullDay = document.getElementById('checkin-full-day').checked;
        document.getElementById('time-fields').classList.toggle('hidden', isFullDay);
    }
</script>
@endsection
