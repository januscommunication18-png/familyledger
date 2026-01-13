@extends('layouts.dashboard')

@section('title', $task ? 'Edit Task' : 'New Task')
@section('page-name', 'Goals & To-Do')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('goals-todo.index', ['tab' => 'todos']) }}">Goals & To-Do</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $task ? 'Edit Task' : 'New Task' }}</li>
@endsection

@section('page-title', $task ? 'Edit Task' : 'New Task')
@section('page-description', $task ? 'Update task details and settings' : 'Create a new task for your family')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ $task ? route('goals-todo.tasks.update', $task) : route('goals-todo.tasks.store') }}" x-data="taskForm()">
                @csrf
                @if($task)
                    @method('PUT')
                @endif

                @if($errors->any())
                    <div class="alert alert-error mb-6">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <div>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-slate-900 border-b pb-2">Basic Information</h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $task?->title) }}" class="input input-bordered" placeholder="What needs to be done?" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea name="description" rows="3" class="textarea textarea-bordered" placeholder="Add details or notes...">{{ old('description', $task?->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Category <span class="text-error">*</span></span>
                            </label>
                            <select name="category" class="select select-bordered" required>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $task?->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Priority</span>
                            </label>
                            <select name="priority" class="select select-bordered">
                                @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}" {{ old('priority', $task?->priority ?? 'medium') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Due Date</span>
                            </label>
                            <input type="date" name="due_date" value="{{ old('due_date', $task?->due_date?->format('Y-m-d')) }}" class="input input-bordered">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Due Time</span>
                            </label>
                            <input type="time" name="due_time" value="{{ old('due_time', $task?->due_time) }}" class="input input-bordered">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Timezone</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ str_replace('_', ' ', $familyTimezone) }}" class="input input-bordered flex-1 bg-base-200" readonly>
                                <input type="hidden" name="timezone" value="{{ old('timezone', $task?->timezone ?? $familyTimezone) }}">
                            </div>
                            <label class="label">
                                <span class="label-text-alt text-slate-500">Using family timezone from settings</span>
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Goal Link -->
                @if($goals->count() > 0)
                    <div class="space-y-4 mt-8">
                        <h3 class="font-semibold text-slate-900 border-b pb-2">Goal Link</h3>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Link to Goal</span>
                            </label>
                            <select name="goal_id" class="select select-bordered" x-model="goalId" id="goalSelect"
                                @change="$nextTick(() => { if (window.handleGoalChange) window.handleGoalChange($el.value) })">
                                <option value="">No goal</option>
                                @foreach($goals as $goal)
                                    <option value="{{ $goal->id }}" {{ old('goal_id', $preselectedGoalId ?? $task?->goal_id) == $goal->id ? 'selected' : '' }}>{{ $goal->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control" x-show="goalId">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="count_toward_goal" value="1" class="checkbox checkbox-primary"
                                    {{ old('count_toward_goal', $task?->count_toward_goal ?? true) ? 'checked' : '' }}>
                                <span class="label-text">Count completions toward goal progress</span>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Assignment -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2">Assignment</h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Assign To</span>
                            <span class="label-text-alt text-slate-500">Select one or more family members</span>
                        </label>
                        @if($familyMembers->count() > 0)
                            <div x-data="assigneeSelect()" x-init="init()" class="space-y-3">
                                <!-- Search & Select Dropdown -->
                                <div class="relative">
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input type="text"
                                                   x-model="search"
                                                   @focus="openDropdown()"
                                                   @click.stop="openDropdown()"
                                                   placeholder="Search family members..."
                                                   class="input input-bordered w-full pl-10">
                                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <button type="button" @click="selectAll()" class="btn btn-outline btn-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Select All
                                        </button>
                                        <button type="button" @click="clearAll()" x-show="selected.length > 0" class="btn btn-ghost btn-sm text-error">
                                            Clear
                                        </button>
                                    </div>

                                    <!-- Dropdown List -->
                                    <div x-show="open"
                                         x-cloak
                                         @click.outside="closeDropdown()"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-2 w-full bg-base-100 border border-base-300 rounded-xl shadow-xl max-h-64 overflow-y-auto">
                                        <template x-for="member in filteredMembers" :key="member.id">
                                            <div @click="toggleMember(member.id)"
                                                 class="flex items-center gap-3 px-4 py-3 hover:bg-base-200 cursor-pointer transition-colors"
                                                 :class="{ 'bg-primary/10': selected.includes(member.id) }">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center text-sm font-medium text-primary flex-shrink-0"
                                                     x-text="member.initial">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-slate-800" x-text="member.name"></div>
                                                    <div class="text-xs text-slate-500" x-text="member.role" x-show="member.role"></div>
                                                </div>
                                                <div x-show="selected.includes(member.id)" class="w-5 h-5 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredMembers.length === 0" class="px-4 py-3 text-sm text-slate-500 text-center">
                                            No members found
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Members Tags -->
                                <div x-show="selected.length > 0" class="flex flex-wrap gap-2">
                                    <template x-for="memberId in selected" :key="memberId">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                            <span x-text="getMemberName(memberId)"></span>
                                            <button type="button" @click="toggleMember(memberId)" class="hover:bg-primary/20 rounded-full p-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <!-- Hidden inputs for form submission -->
                                <template x-for="memberId in selected" :key="'input-' + memberId">
                                    <input type="hidden" name="assignees[]" :value="memberId">
                                </template>
                            </div>
                        @else
                            <div class="flex items-center gap-3 p-4 bg-base-200/50 rounded-xl">
                                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-600">No family members found</p>
                                    <a href="{{ url('/family-circle') }}" class="text-sm text-primary hover:underline">Add family members first</a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Rotation Type</span>
                            </label>
                            <select name="rotation_type" class="select select-bordered">
                                @foreach($rotationTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('rotation_type', $task?->rotation_type ?? 'none') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-slate-500">Automatically rotate assignee for recurring tasks</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Completion Type</span>
                            </label>
                            <select name="completion_type" class="select select-bordered">
                                @foreach($completionTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('completion_type', $task?->completion_type ?? 'any_one') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Completion Proof -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2">Completion Proof</h3>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="proof_required" value="1" class="checkbox checkbox-primary" x-model="proofRequired"
                                   {{ old('proof_required', $task?->proof_required) ? 'checked' : '' }}>
                            <div>
                                <span class="label-text font-medium">Require proof for completion</span>
                                <p class="text-xs text-slate-500">User must attach proof (photo, receipt, etc.) to mark complete</p>
                            </div>
                        </label>
                    </div>

                    <div x-show="proofRequired" x-cloak x-transition class="form-control pl-4 border-l-2 border-primary/20">
                        <label class="label">
                            <span class="label-text">Proof Type</span>
                        </label>
                        <select name="proof_type" class="select select-bordered">
                            @foreach($proofTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('proof_type', $task?->proof_type ?? 'photo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Recurring Settings -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2">Recurring Task</h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Is this a recurring task?</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="is_recurring" value="0" class="radio radio-primary"
                                       x-on:change="isRecurring = false"
                                       {{ !old('is_recurring', $task?->is_recurring) ? 'checked' : '' }}>
                                <span class="label-text">No</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="is_recurring" value="1" class="radio radio-primary"
                                       x-on:change="isRecurring = true"
                                       {{ old('is_recurring', $task?->is_recurring) ? 'checked' : '' }}>
                                <span class="label-text">Yes</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="isRecurring" x-cloak x-transition class="space-y-4 p-4 bg-base-200/50 rounded-xl mt-4">
                        <!-- Start Date -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Starts On</span>
                            </label>
                            <input type="date" name="recurrence_start_date" value="{{ old('recurrence_start_date', $task?->recurrence_start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="input input-bordered w-auto">
                            <label class="label">
                                <span class="label-text-alt text-slate-500">When should the recurring series start?</span>
                            </label>
                        </div>

                        <!-- Frequency -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Repeat</span>
                                </label>
                                <select name="recurrence_frequency" class="select select-bordered" x-model="frequency">
                                    @foreach($recurrenceFrequencies as $key => $label)
                                        <option value="{{ $key }}" {{ old('recurrence_frequency', $task?->recurrence_frequency ?? 'daily') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Every</span>
                                </label>
                                <div class="join">
                                    <input type="number" name="recurrence_interval" value="{{ old('recurrence_interval', $task?->recurrence_interval ?? 1) }}" min="1" max="365" class="input input-bordered join-item w-20">
                                    <span class="join-item flex items-center px-3 bg-base-200" x-text="intervalLabel"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Days -->
                        <div x-show="frequency === 'weekly'" class="form-control">
                            <label class="label">
                                <span class="label-text">On these days</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($weekdays as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="recurrence_days[]" value="{{ $key }}" class="hidden peer"
                                               {{ in_array($key, old('recurrence_days', $task?->recurrence_days ?? [])) ? 'checked' : '' }}>
                                        <span class="inline-flex items-center justify-center w-10 h-10 border-2 rounded-lg peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white transition-colors">
                                            {{ substr($label, 0, 2) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Monthly Options -->
                        <div x-show="frequency === 'monthly'" class="space-y-3">
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="monthly_type" value="day_of_month" class="radio radio-primary" x-model="monthlyType"
                                           {{ old('monthly_type', $task?->monthly_type ?? 'day_of_month') === 'day_of_month' ? 'checked' : '' }}>
                                    <span class="label-text flex items-center gap-2">
                                        On day
                                        <input type="number" name="monthly_day" value="{{ old('monthly_day', $task?->monthly_day ?? 1) }}" min="1" max="31" class="input input-bordered input-sm w-16" :disabled="monthlyType !== 'day_of_month'">
                                        of the month
                                    </span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="monthly_type" value="day_of_week" class="radio radio-primary" x-model="monthlyType"
                                           {{ old('monthly_type', $task?->monthly_type) === 'day_of_week' ? 'checked' : '' }}>
                                    <span class="label-text flex items-center gap-2 flex-wrap">
                                        On the
                                        <select name="monthly_week" class="select select-bordered select-sm" :disabled="monthlyType !== 'day_of_week'">
                                            <option value="1" {{ old('monthly_week', $task?->monthly_week ?? 1) == 1 ? 'selected' : '' }}>1st</option>
                                            <option value="2" {{ old('monthly_week', $task?->monthly_week) == 2 ? 'selected' : '' }}>2nd</option>
                                            <option value="3" {{ old('monthly_week', $task?->monthly_week) == 3 ? 'selected' : '' }}>3rd</option>
                                            <option value="4" {{ old('monthly_week', $task?->monthly_week) == 4 ? 'selected' : '' }}>4th</option>
                                            <option value="5" {{ old('monthly_week', $task?->monthly_week) == 5 ? 'selected' : '' }}>Last</option>
                                        </select>
                                        <select name="monthly_weekday" class="select select-bordered select-sm" :disabled="monthlyType !== 'day_of_week'">
                                            @foreach($weekdays as $key => $label)
                                                <option value="{{ $key }}" {{ old('monthly_weekday', $task?->monthly_weekday ?? 'mon') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Yearly Options -->
                        <div x-show="frequency === 'yearly'" class="form-control">
                            <label class="label">
                                <span class="label-text">On</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <select name="yearly_month" class="select select-bordered">
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                        <option value="{{ $index + 1 }}" {{ old('yearly_month', $task?->yearly_month ?? 1) == $index + 1 ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="yearly_day" value="{{ old('yearly_day', $task?->yearly_day ?? 1) }}" min="1" max="31" class="input input-bordered w-20">
                            </div>
                        </div>

                        <!-- End Conditions -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Ends</span>
                            </label>
                            <div class="space-y-2">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="recurrence_end_type" value="never" class="radio radio-primary" x-model="endType"
                                           {{ old('recurrence_end_type', $task?->recurrence_end_type ?? 'never') === 'never' ? 'checked' : '' }}>
                                    <span class="label-text">Never</span>
                                </label>
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="recurrence_end_type" value="on_date" class="radio radio-primary" x-model="endType"
                                           {{ old('recurrence_end_type', $task?->recurrence_end_type) === 'on_date' ? 'checked' : '' }}>
                                    <span class="label-text flex items-center gap-2">
                                        On date
                                        <input type="date" name="recurrence_end_date" value="{{ old('recurrence_end_date', $task?->recurrence_end_date?->format('Y-m-d')) }}" class="input input-bordered input-sm" :disabled="endType !== 'on_date'">
                                    </span>
                                </label>
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="recurrence_end_type" value="after_occurrences" class="radio radio-primary" x-model="endType"
                                           {{ old('recurrence_end_type', $task?->recurrence_end_type) === 'after_occurrences' ? 'checked' : '' }}>
                                    <span class="label-text flex items-center gap-2">
                                        After
                                        <input type="number" name="recurrence_max_occurrences" value="{{ old('recurrence_max_occurrences', $task?->recurrence_max_occurrences ?? 10) }}" min="1" max="999" class="input input-bordered input-sm w-20" :disabled="endType !== 'after_occurrences'">
                                        occurrences
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="divider text-sm text-slate-500">Advanced Options</div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="skip_weekends" value="1" class="checkbox checkbox-primary"
                                       {{ old('skip_weekends', $task?->skip_weekends) ? 'checked' : '' }}>
                                <span class="label-text">Skip weekends</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Generate Mode</span>
                                </label>
                                <select name="generate_mode" class="select select-bordered" x-model="generateMode">
                                    @foreach($generateModes as $key => $label)
                                        <option value="{{ $key }}" {{ old('generate_mode', $task?->generate_mode ?? 'on_complete') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-slate-500" x-text="generateMode === 'on_complete' ? 'Next task appears after completion' : 'Tasks are scheduled in advance'"></span>
                                </label>
                            </div>

                            <div class="form-control" x-show="generateMode === 'schedule_ahead'">
                                <label class="label">
                                    <span class="label-text">Schedule Ahead (days)</span>
                                </label>
                                <input type="number" name="schedule_ahead_days" value="{{ old('schedule_ahead_days', $task?->schedule_ahead_days ?? 30) }}" min="1" max="365" class="input input-bordered">
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">If Missed</span>
                            </label>
                            <select name="missed_policy" class="select select-bordered">
                                @foreach($missedPolicies as $key => $label)
                                    <option value="{{ $key }}" {{ old('missed_policy', $task?->missed_policy ?? 'carryover') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-slate-500">What happens when a scheduled occurrence is missed</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Reminder & Notifications -->
                <div class="space-y-4 mt-8">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h3 class="font-semibold text-slate-900">Reminders & Notifications</h3>
                        <input type="checkbox" name="send_reminder" value="1" class="toggle toggle-primary" x-model="sendReminder"
                               {{ old('send_reminder', $task?->send_reminder) ? 'checked' : '' }}>
                    </div>

                    <div x-show="sendReminder" x-transition class="space-y-4 pl-4 border-l-2 border-primary/20">
                        <!-- Reminder Time -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Remind</span>
                            </label>
                            <select name="reminder_type" class="select select-bordered">
                                @foreach($reminderTimings as $key => $label)
                                    <option value="{{ $key }}" {{ old('reminder_type', $task?->reminder_type ?? 'at_time') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Escalation Settings -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="escalation_enabled" value="1" class="checkbox checkbox-warning" x-model="escalationEnabled"
                                       {{ old('escalation_enabled', $task?->escalation_settings['enabled'] ?? false) ? 'checked' : '' }}>
                                <div>
                                    <span class="label-text font-medium">Enable escalation</span>
                                    <p class="text-xs text-slate-500">Notify someone else if task isn't completed</p>
                                </div>
                            </label>
                        </div>

                        <div x-show="escalationEnabled" x-cloak x-transition class="space-y-3 p-3 bg-warning/10 rounded-lg">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Escalate after</span>
                                    </label>
                                    <div class="join">
                                        <input type="number" name="escalation_hours" value="{{ old('escalation_hours', $task?->escalation_settings['first_escalation_hours'] ?? 24) }}" min="1" max="168" class="input input-bordered join-item w-20">
                                        <span class="join-item flex items-center px-3 bg-base-200">hours</span>
                                    </div>
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Escalate to</span>
                                    </label>
                                    <select name="escalation_target" class="select select-bordered" x-model="escalationTarget">
                                        @foreach($escalationTargets as $key => $label)
                                            <option value="{{ $key }}" {{ old('escalation_target', $task?->escalation_settings['escalate_to'] ?? 'parents') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div x-show="escalationTarget === 'specific_member'" x-cloak class="form-control">
                                <label class="label">
                                    <span class="label-text">Select member</span>
                                </label>
                                <select name="escalation_member_id" class="select select-bordered">
                                    <option value="">Choose...</option>
                                    @foreach($familyMembers as $member)
                                        <option value="{{ $member->id }}" {{ old('escalation_member_id', $task?->escalation_settings['escalate_to_member_id'] ?? '') == $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Digest Mode (for recurring) -->
                        <div x-show="isRecurring" x-cloak class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="digest_mode" value="1" class="checkbox checkbox-info" x-model="digestMode"
                                       {{ old('digest_mode', $task?->digest_mode) ? 'checked' : '' }}>
                                <div>
                                    <span class="label-text font-medium">Morning digest</span>
                                    <p class="text-xs text-slate-500">Include in daily summary instead of individual reminders</p>
                                </div>
                            </label>
                        </div>

                        <div x-show="digestMode && isRecurring" x-cloak x-transition class="form-control pl-4">
                            <label class="label">
                                <span class="label-text">Digest time</span>
                            </label>
                            <input type="time" name="digest_time" value="{{ old('digest_time', $task?->digest_time ?? '08:00') }}" class="input input-bordered w-auto">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
                    <a href="{{ route('goals-todo.index', ['tab' => 'todos']) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        {{ $task ? 'Update Task' : 'Create Task' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function taskForm() {
    return {
        // Basic settings
        isRecurring: {{ old('is_recurring', $task?->is_recurring) ? 'true' : 'false' }},
        frequency: '{{ old('recurrence_frequency', $task?->recurrence_frequency ?? 'daily') }}',
        monthlyType: '{{ old('monthly_type', $task?->monthly_type ?? 'day_of_month') }}',
        endType: '{{ old('recurrence_end_type', $task?->recurrence_end_type ?? 'never') }}',
        generateMode: '{{ old('generate_mode', $task?->generate_mode ?? 'on_complete') }}',
        goalId: '{{ old('goal_id', $preselectedGoalId ?? $task?->goal_id ?? '') }}',

        // Proof settings
        proofRequired: {{ old('proof_required', $task?->proof_required) ? 'true' : 'false' }},

        // Reminder settings
        sendReminder: {{ old('send_reminder', $task?->send_reminder) ? 'true' : 'false' }},
        escalationEnabled: {{ old('escalation_enabled', $task?->escalation_settings['enabled'] ?? false) ? 'true' : 'false' }},
        escalationTarget: '{{ old('escalation_target', $task?->escalation_settings['escalate_to'] ?? 'parents') }}',
        digestMode: {{ old('digest_mode', $task?->digest_mode) ? 'true' : 'false' }},

        get intervalLabel() {
            const labels = {
                'daily': 'day(s)',
                'weekly': 'week(s)',
                'monthly': 'month(s)',
                'yearly': 'year(s)',
                'custom': 'unit(s)'
            };
            return labels[this.frequency] || 'unit(s)';
        }
    }
}

function assigneeSelect() {
    return {
        open: false,
        justOpened: false,
        search: '',
        selected: [
            @if($task && $task->assignees)
                @foreach($task->assignees as $assignee)
                    {{ $assignee->id }},
                @endforeach
            @endif
        ],
        members: [
            @foreach($familyMembers as $member)
            {
                id: {{ $member->id }},
                name: '{{ addslashes($member->first_name) }} {{ addslashes($member->last_name ?? '') }}',
                initial: '{{ strtoupper(substr($member->first_name, 0, 1)) }}',
                relationship: '{{ $member->relationship ?? '' }}',
                isMinor: {{ $member->is_minor ? 'true' : 'false' }}
            },
            @endforeach
        ],
        goalAssignments: {
            @foreach($goals as $goal)
            '{{ $goal->id }}': {
                type: '{{ $goal->assignment_type ?? 'individual' }}',
                assignedTo: {{ $goal->assigned_to ?? 'null' }}
            },
            @endforeach
        },
        parentIds: [
            @foreach($familyMembers->filter(fn($m) => in_array($m->relationship, ['spouse', 'self', 'parent', 'guardian'])) as $member)
                {{ $member->id }},
            @endforeach
        ],
        kidIds: [
            @foreach($familyMembers->filter(fn($m) => $m->relationship === 'child' || $m->is_minor) as $member)
                {{ $member->id }},
            @endforeach
        ],
        lastGoalId: null,
        initialized: false,

        init() {
            // Register global handler first
            this.registerGoalHandler();
            this.initialized = true;

            // Handle preselected goal on page load
            @if($preselectedGoalId ?? $task?->goal_id)
                setTimeout(() => {
                    this.onGoalChange('{{ $preselectedGoalId ?? $task?->goal_id }}');
                }, 100);
            @endif
        },

        registerGoalHandler() {
            // Register global handler for goal changes
            const self = this;
            window.handleGoalChange = function(goalId) {
                console.log('handleGoalChange called with goalId:', goalId);
                self.onGoalChange(goalId);
            };
            console.log('Goal handler registered');
        },

        onGoalChange(goalId) {
            console.log('onGoalChange called:', goalId, 'lastGoalId:', this.lastGoalId);

            // Convert to string for comparison
            goalId = String(goalId);

            if (goalId && goalId !== '' && goalId !== String(this.lastGoalId)) {
                const goalInfo = this.goalAssignments[goalId];
                console.log('Goal info for', goalId, ':', goalInfo);
                console.log('Available goals:', this.goalAssignments);

                if (goalInfo) {
                    let memberIds = [];

                    console.log('Assignment type:', goalInfo.type);

                    switch(goalInfo.type) {
                        case 'family':
                            // Select all family members
                            memberIds = this.members.map(m => m.id);
                            console.log('Family goal - selecting all members:', memberIds);
                            break;
                        case 'parents':
                            memberIds = [...this.parentIds];
                            console.log('Parents goal - selecting:', memberIds);
                            break;
                        case 'kids':
                            memberIds = [...this.kidIds];
                            console.log('Kids goal - selecting:', memberIds);
                            break;
                        case 'parent_kid':
                        case 'shared':
                            // Select both parents and kids
                            memberIds = [...this.parentIds, ...this.kidIds];
                            console.log('Shared goal - selecting parents + kids:', memberIds);
                            break;
                        case 'individual':
                            // Single assigned member
                            if (goalInfo.assignedTo) {
                                memberIds = [goalInfo.assignedTo];
                                console.log('Individual goal - selecting:', memberIds);
                            }
                            break;
                        default:
                            console.log('Unknown assignment type:', goalInfo.type);
                    }

                    // Add goal members to selection (don't remove existing)
                    if (memberIds.length > 0) {
                        memberIds.forEach(id => {
                            if (!this.selected.includes(id)) {
                                this.selected.push(id);
                                console.log('Added member to selection:', id);
                            }
                        });
                    }
                } else {
                    console.log('No goal info found for goalId:', goalId);
                }
                this.lastGoalId = goalId;
            } else if (!goalId || goalId === '') {
                console.log('Goal cleared');
                this.lastGoalId = null;
            }
        },

        get filteredMembers() {
            if (!this.search) return this.members;
            const searchLower = this.search.toLowerCase();
            return this.members.filter(m => m.name.toLowerCase().includes(searchLower));
        },

        toggleMember(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(id);
            }
        },

        selectAll() {
            this.selected = this.members.map(m => m.id);
        },

        clearAll() {
            this.selected = [];
        },

        getMemberName(id) {
            const member = this.members.find(m => m.id === id);
            return member ? member.name : '';
        },

        openDropdown() {
            this.open = true;
            this.justOpened = true;
            setTimeout(() => {
                this.justOpened = false;
            }, 150);
        },

        closeDropdown() {
            if (!this.justOpened) {
                this.open = false;
            }
        }
    }
}
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
