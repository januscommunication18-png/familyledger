@extends('layouts.dashboard')

@section('page-name', 'Co-parenting Calendar')

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
            <h1 class="text-2xl font-bold text-slate-800">Co-parenting Calendar</h1>
            <p class="text-slate-500">Plan and visualize your custody schedule.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Card 1: Schedule Planning --}}
        <div class="xl:col-span-1">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 text-lg mb-4">Schedule Planning</h3>
                    <p class="text-sm text-slate-500 mb-4">A schedule shows when each parent will look after the children.</p>

                    {{-- Active Schedule (only one allowed) --}}
                    @if($schedules->count() > 0)
                        @php $schedule = $schedules->first(); @endphp
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-slate-700 mb-2">Active Schedule</h4>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-primary/5 to-primary/10 border border-primary/20">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $schedule->display_name }}</p>
                                        <p class="text-sm text-slate-500 mt-1">{{ $schedule->ratio }}</p>
                                    </div>
                                    <span class="badge badge-success badge-sm">Active</span>
                                </div>
                                <div class="text-xs text-slate-500 mb-3">
                                    Started {{ $schedule->begins_at->format('M j, Y') }}
                                    @if($schedule->ends_at)
                                        &bull; Ends {{ $schedule->ends_at->format('M j, Y') }}
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="openEditModal({{ $schedule->id }})" class="btn btn-sm btn-ghost gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('coparenting.schedule.delete', $schedule) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost text-error gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Replace Schedule --}}
                        <div class="space-y-3">
                            <button type="button" onclick="openTemplateModal()" class="btn btn-outline btn-block gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                                Replace Schedule
                            </button>
                            <button type="button" onclick="openManualModal()" class="btn btn-ghost btn-block gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Add Manual Time Block
                            </button>
                        </div>
                    @else
                        {{-- No Schedule - Create New --}}
                        <div class="space-y-3">
                            <button type="button" onclick="openTemplateModal()" class="btn btn-primary btn-block gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                Choose a Template
                            </button>
                        </div>
                    @endif

                    {{-- Legend --}}
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <h4 class="text-sm font-medium text-slate-700 mb-3">Legend</h4>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-pink-500"></div>
                                <span class="text-sm text-slate-600">Mother</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-blue-500"></div>
                                <span class="text-sm text-slate-600">Father</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Calendar View --}}
        <div class="xl:col-span-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 text-lg mb-4">Calendar View</h3>

                    @if($schedules->count() > 0)
                    <div id="coparenting-calendar" class="min-h-[500px]"></div>
                    @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-lg font-medium text-slate-700 mb-2">No Schedule Yet</h4>
                        <p class="text-slate-500 mb-4 max-w-sm">Create your first custody schedule to see it displayed on the calendar.</p>
                        <button type="button" onclick="openTemplateModal()" class="btn btn-primary gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            Create Schedule
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template Selection Modal (Custom) --}}
<div id="template-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeTemplateModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl transform transition-all">
                {{-- Close Button --}}
                <button type="button" onclick="closeTemplateModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <div class="p-6">
                    <h3 class="font-bold text-xl text-slate-800 mb-6">New Schedule</h3>

                    @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <p class="font-semibold">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-sm mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <form id="template-form" action="{{ route('coparenting.schedule.store') }}" method="POST">
                        @csrf

                        {{-- Step 1: Date Range --}}
                        <div id="template-step-1">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">1</div>
                                <h4 class="font-semibold text-slate-700">Set Date Range</h4>
                            </div>

                            <div class="space-y-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Begins <span class="text-error">*</span></span>
                                    </label>
                                    <input type="date" name="begins_at" class="input input-bordered w-full" value="{{ now()->format('Y-m-d') }}" required>
                                </div>

                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="checkbox" name="has_end_date" id="has-end-date" class="checkbox checkbox-primary" onchange="toggleEndDate()">
                                        <span class="label-text">Set an end date</span>
                                    </label>
                                </div>

                                <div id="end-date-field" class="form-control hidden">
                                    <label class="label">
                                        <span class="label-text font-medium">Ends</span>
                                    </label>
                                    <input type="date" name="ends_at" class="input input-bordered w-full">
                                </div>
                            </div>

                            <div class="flex justify-end mt-6 pt-4 border-t border-slate-200">
                                <button type="button" onclick="goToStep2()" class="btn btn-primary gap-2">
                                    Next
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Template Selection --}}
                        <div id="template-step-2" class="hidden">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">2</div>
                                <h4 class="font-semibold text-slate-700">Select Schedule Pattern</h4>
                            </div>

                            <div class="space-y-2 max-h-[280px] overflow-y-auto mb-4 pr-2">
                                @foreach($templateTypes as $key => $template)
                                <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-primary/50 hover:bg-primary/5 cursor-pointer transition-all template-option">
                                    <input type="radio" name="template_type" value="{{ $key }}" class="radio radio-primary mt-0.5" {{ $key === 'every_other_week' ? 'checked' : '' }} onchange="handleTemplateChange('{{ $key }}')">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-slate-800">{{ $template['label'] }}</span>
                                            <span class="badge badge-sm {{ str_contains($template['ratio'], '50') ? 'badge-success' : (str_contains($template['ratio'], '60') || str_contains($template['ratio'], '80') ? 'badge-warning' : 'badge-info') }}">{{ $template['ratio'] }}</span>
                                        </div>
                                        <p class="text-sm text-slate-500 mt-1">{{ $template['description'] }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>

                            {{-- Custom Repeating Rate Fields --}}
                            <div id="custom-fields" class="hidden space-y-4 p-4 rounded-xl bg-slate-50 border border-slate-200 mb-4">
                                <h5 class="font-medium text-slate-700">Repeating Rate</h5>
                                <p class="text-sm text-slate-500">This is how long it takes for both parents to complete one visitation period with the children.</p>

                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-slate-600">Repeats every</span>
                                    <input type="number" name="repeat_every" class="input input-bordered input-sm w-20" min="1" value="7">
                                    <select name="repeat_unit" class="select select-bordered select-sm">
                                        <option value="days">Days</option>
                                        <option value="weeks">Weeks</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Primary Parent Selection --}}
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Who starts first? <span class="text-error">*</span></span>
                                </label>
                                <div class="flex gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 border-slate-200 hover:border-pink-300 transition-colors">
                                        <input type="radio" name="primary_parent" value="mother" class="radio radio-primary" checked>
                                        <div class="w-3 h-3 rounded-full bg-pink-500"></div>
                                        <span class="text-slate-700 font-medium">Mother</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 border-slate-200 hover:border-blue-300 transition-colors">
                                        <input type="radio" name="primary_parent" value="father" class="radio radio-primary">
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                        <span class="text-slate-700 font-medium">Father</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-between mt-6 pt-4 border-t border-slate-200">
                                <button type="button" onclick="goToStep1()" class="btn btn-ghost gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    Back
                                </button>
                                <div class="flex gap-2">
                                    <button type="button" onclick="closeTemplateModal()" class="btn btn-ghost">Cancel</button>
                                    <button type="submit" class="btn btn-primary gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        Save Schedule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Manual Time Entry Modal (Custom) --}}
<div id="manual-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeManualModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl transform transition-all">
                {{-- Close Button --}}
                <button type="button" onclick="closeManualModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <div class="p-6">
                    <h3 class="font-bold text-xl text-slate-800 mb-6">Add Time Manually</h3>

                    @if($schedules->count() > 0)
                    <form action="{{ route('coparenting.schedule.block.store', $schedules->first()) }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Parent <span class="text-error">*</span></span>
                                </label>
                                <select name="parent_role" class="select select-bordered w-full" required>
                                    <option value="mother">Mother</option>
                                    <option value="father">Father</option>
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">From <span class="text-error">*</span></span>
                                </label>
                                <input type="datetime-local" name="starts_at" class="input input-bordered w-full" required>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">To <span class="text-error">*</span></span>
                                </label>
                                <input type="datetime-local" name="ends_at" class="input input-bordered w-full" required>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-200">
                            <button type="button" onclick="closeManualModal()" class="btn btn-ghost">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <p class="text-slate-500 mb-4">Please create a schedule template first before adding manual time blocks.</p>
                        <button type="button" onclick="closeManualModal(); setTimeout(openTemplateModal, 300);" class="btn btn-primary">Create Schedule</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Schedule Modal --}}
@if($schedules->count() > 0)
@php $editSchedule = $schedules->first(); @endphp
<div id="edit-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl transform transition-all">
                <button type="button" onclick="closeEditModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <div class="p-6">
                    <h3 class="font-bold text-xl text-slate-800 mb-6">Edit Schedule</h3>

                    <form action="{{ route('coparenting.schedule.update', $editSchedule) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Begins <span class="text-error">*</span></span>
                                </label>
                                <input type="date" name="begins_at" class="input input-bordered w-full" value="{{ $editSchedule->begins_at->format('Y-m-d') }}" required>
                            </div>

                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="has_end_date" id="edit-has-end-date" class="checkbox checkbox-primary" onchange="toggleEditEndDate()" {{ $editSchedule->has_end_date ? 'checked' : '' }}>
                                    <span class="label-text">Set an end date</span>
                                </label>
                            </div>

                            <div id="edit-end-date-field" class="form-control {{ $editSchedule->has_end_date ? '' : 'hidden' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Ends</span>
                                </label>
                                <input type="date" name="ends_at" class="input input-bordered w-full" value="{{ $editSchedule->ends_at?->format('Y-m-d') }}">
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Schedule Pattern <span class="text-error">*</span></span>
                                </label>
                                <select name="template_type" class="select select-bordered w-full">
                                    @foreach($templateTypes as $key => $template)
                                        <option value="{{ $key }}" {{ $editSchedule->template_type === $key ? 'selected' : '' }}>
                                            {{ $template['label'] }} ({{ $template['ratio'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Who starts first? <span class="text-error">*</span></span>
                                </label>
                                <div class="flex gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 border-slate-200 hover:border-pink-300 transition-colors">
                                        <input type="radio" name="primary_parent" value="mother" class="radio radio-primary" {{ $editSchedule->primary_parent === 'mother' ? 'checked' : '' }}>
                                        <div class="w-3 h-3 rounded-full bg-pink-500"></div>
                                        <span class="text-slate-700 font-medium">Mother</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 border-slate-200 hover:border-blue-300 transition-colors">
                                        <input type="radio" name="primary_parent" value="father" class="radio radio-primary" {{ $editSchedule->primary_parent === 'father' ? 'checked' : '' }}>
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                        <span class="text-slate-700 font-medium">Father</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-200">
                            <button type="button" onclick="closeEditModal()" class="btn btn-ghost">Cancel</button>
                            <button type="submit" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
{{-- FullCalendar CDN --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    // Modal Functions
    function openTemplateModal() {
        document.getElementById('template-step-1').classList.remove('hidden');
        document.getElementById('template-step-2').classList.add('hidden');
        document.getElementById('template-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeTemplateModal() {
        document.getElementById('template-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openManualModal() {
        document.getElementById('manual-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeManualModal() {
        document.getElementById('manual-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openEditModal(scheduleId) {
        const modal = document.getElementById('edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function toggleEndDate() {
        const checkbox = document.getElementById('has-end-date');
        const field = document.getElementById('end-date-field');
        field.classList.toggle('hidden', !checkbox.checked);
    }

    function toggleEditEndDate() {
        const checkbox = document.getElementById('edit-has-end-date');
        const field = document.getElementById('edit-end-date-field');
        field.classList.toggle('hidden', !checkbox.checked);
    }

    function goToStep1() {
        document.getElementById('template-step-1').classList.remove('hidden');
        document.getElementById('template-step-2').classList.add('hidden');
    }

    function goToStep2() {
        document.getElementById('template-step-1').classList.add('hidden');
        document.getElementById('template-step-2').classList.remove('hidden');
    }

    function handleTemplateChange(templateType) {
        const customFields = document.getElementById('custom-fields');
        if (templateType === 'custom') {
            customFields.classList.remove('hidden');
        } else {
            customFields.classList.add('hidden');
        }
    }

    function editSchedule(scheduleId) {
        // For now, just open the template modal - could be enhanced later
        openTemplateModal();
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTemplateModal();
            closeManualModal();
            closeEditModal();
        }
    });

    // Auto-open modal if there are validation errors
    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openTemplateModal();
        // Show step 2 since that's where most fields are
        goToStep2();
    });
    @endif

    // Initialize Calendar
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('coparenting-calendar');

        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: '{{ route("coparenting.calendar.events") }}',
                eventDisplay: 'block',
                displayEventTime: false,
                eventDidMount: function(info) {
                    // Add tooltip
                    info.el.title = info.event.title;
                },
                height: 'auto',
                aspectRatio: 1.5,
            });

            calendar.render();
        }
    });
</script>
@endpush

<style>
    /* Calendar Customization */
    .fc {
        font-family: inherit;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e2e8f0;
    }
    .fc-theme-standard .fc-scrollgrid {
        border-color: #e2e8f0;
    }
    .fc .fc-button-primary {
        background-color: #6366f1;
        border-color: #6366f1;
    }
    .fc .fc-button-primary:hover {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
    .fc .fc-button-primary:disabled {
        background-color: #6366f1;
        border-color: #6366f1;
    }
    .fc .fc-button-primary:not(:disabled):active,
    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #4338ca;
        border-color: #4338ca;
    }
    .fc-event {
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.75rem;
    }
    .fc .fc-daygrid-day-number {
        padding: 8px;
        color: #475569;
    }
    .fc .fc-col-header-cell-cushion {
        padding: 8px;
        color: #64748b;
        font-weight: 500;
    }

    /* Template option styling */
    .template-option:has(input:checked) {
        border-color: #6366f1;
        background-color: rgba(99, 102, 241, 0.05);
    }
</style>
@endsection
