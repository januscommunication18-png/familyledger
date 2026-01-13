@extends('layouts.dashboard')

@section('title', $goal ? 'Edit Goal' : 'New Goal')
@section('page-name', 'Goals & To-Do')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('goals-todo.index', ['tab' => 'goals']) }}">Goals & To-Do</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $goal ? 'Edit Goal' : 'New Goal' }}</li>
@endsection

@section('page-title', $goal ? 'Edit Goal' : 'New Goal')
@section('page-description', $goal ? 'Update goal details' : 'Create a family goal to track progress together')

<style>[x-cloak] { display: none !important; }</style>

@section('content')
<div class="max-w-2xl mx-auto" x-data="goalForm()">

    @if(!$goal && $templates->isNotEmpty())
    <!-- STEP 1: Choose Method (Create mode only with templates available) -->
    <div x-show="step === 'choose'" class="space-y-6">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-slate-900">How would you like to start?</h2>
            <p class="text-slate-500 mt-2">Choose to create from scratch or use a pre-made template</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Create from Scratch -->
            <button type="button"
                    @click="step = 'form'"
                    class="card bg-base-100 shadow-sm hover:shadow-md transition-all cursor-pointer border-2 border-transparent hover:border-primary">
                <div class="card-body items-center text-center py-8">
                    <div class="text-5xl mb-4">&#9997;</div>
                    <h3 class="card-title">Create from Scratch</h3>
                    <p class="text-slate-500 text-sm">Start with a blank goal and customize everything</p>
                </div>
            </button>

            <!-- Use Template -->
            <button type="button"
                    @click="step = 'templates'"
                    class="card bg-base-100 shadow-sm hover:shadow-md transition-all cursor-pointer border-2 border-transparent hover:border-primary">
                <div class="card-body items-center text-center py-8">
                    <div class="text-5xl mb-4">&#128161;</div>
                    <h3 class="card-title">Use a Template</h3>
                    <p class="text-slate-500 text-sm">Pick from pre-made goals for families & kids</p>
                    <div class="badge badge-primary badge-sm mt-2">{{ $templates->flatten()->count() }} templates</div>
                </div>
            </button>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('goals-todo.index', ['tab' => 'goals']) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Goals
            </a>
        </div>
    </div>

    <!-- STEP 2A: Template Selection -->
    <div x-show="step === 'templates'" x-cloak class="space-y-6">
        <div class="flex items-center justify-between mb-6">
            <button type="button" @click="step = 'choose'" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </button>
            <h2 class="text-xl font-bold text-slate-900">Choose a Template</h2>
            <div class="w-20"></div>
        </div>

        <div class="space-y-4">
            @foreach($templates as $audience => $audienceTemplates)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-base">
                            {{ \App\Models\GoalTemplate::AUDIENCES[$audience]['emoji'] ?? '' }}
                            {{ \App\Models\GoalTemplate::AUDIENCES[$audience]['label'] ?? ucfirst($audience) }}
                            <span class="badge badge-ghost badge-sm">{{ $audienceTemplates->count() }}</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3">
                            @foreach($audienceTemplates as $template)
                                <button type="button"
                                        class="btn btn-ghost justify-start text-left h-auto py-3 px-4 border border-base-300 hover:border-primary hover:bg-primary/5"
                                        @click="selectTemplate({
                                            id: {{ $template->id }},
                                            title: {{ json_encode($template->title) }},
                                            description: {{ json_encode($template->description ?? '') }},
                                            category: {{ json_encode($template->category) }},
                                            goalType: {{ json_encode($template->goal_type) }},
                                            habitFrequency: {{ json_encode($template->habit_frequency ?? '') }},
                                            milestoneTarget: {{ json_encode($template->milestone_target ?? '') }},
                                            milestoneUnit: {{ json_encode($template->milestone_unit ?? '') }},
                                            checkInFrequency: {{ json_encode($template->suggested_check_in_frequency ?? '') }},
                                            rewardsEnabled: {{ $template->suggested_rewards ? 'true' : 'false' }},
                                            rewardType: {{ json_encode($template->suggested_reward_type ?? '') }},
                                            isKidGoal: {{ $audience === 'kids' ? 'true' : 'false' }}
                                        })">
                                    <span class="text-2xl mr-3">{{ $template->emoji ?? $template->category_emoji }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $template->title }}</div>
                                        @if($template->description)
                                            <div class="text-xs text-slate-500 truncate">{{ Str::limit($template->description, 50) }}</div>
                                        @endif
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-slate-400"></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- STEP 2B: Goal Form -->
    <div x-show="step === 'form'" {!! (!$goal && $templates->isNotEmpty()) ? 'x-cloak' : '' !!}>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                @if(!$goal && $templates->isNotEmpty())
                <!-- Back button when coming from step 1 -->
                <div class="mb-4">
                    <button type="button" @click="goBack()" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back
                    </button>
                    <span x-show="selectedTemplate" class="ml-2 text-sm text-slate-500">
                        Creating from template: <span class="font-medium text-slate-700" x-text="selectedTemplate?.title || ''"></span>
                    </span>
                </div>
                @endif

                <form method="POST" action="{{ $goal ? route('goals-todo.goals.update', $goal) : route('goals-todo.goals.store') }}" x-ref="goalForm">
                    @csrf
                    @if($goal)
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

                    <!-- Hidden template_id -->
                    <input type="hidden" name="template_id" x-model="templateId">

                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Goal Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="goalTitle" value="{{ old('title', $goal?->title ?? '') }}" class="input input-bordered" placeholder="What do you want to achieve?" required>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Description</span>
                            </label>
                            <textarea name="description" id="goalDescription" rows="2" class="textarea textarea-bordered" placeholder="Why is this goal important?">{{ old('description', $goal?->description ?? '') }}</textarea>
                        </div>

                        <!-- Category -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Category <span class="text-error">*</span></span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($categories as $key => $cat)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="{{ $key }}" x-model="category" class="hidden peer">
                                        <div class="flex items-center gap-2 p-3 rounded-lg border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                            <span class="text-xl">{{ $cat['emoji'] }}</span>
                                            <span class="text-sm font-medium">{{ $cat['label'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Assignment -->
                    <div class="space-y-4 mt-8" x-data="goalAssigneeSelect()">
                        <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                            <span class="text-lg">&#128101;</span> Who is this for?
                        </h3>

                        <!-- Quick Select Cards -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Quick Select</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="group in groups" :key="group.id">
                                    <div @click="selectGroup(group.id)"
                                         class="text-center p-4 rounded-xl border-2 cursor-pointer transition-all"
                                         :class="selectedGroup === group.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-base-300 hover:border-primary/50'">
                                        <div class="text-2xl mb-1" x-text="group.emoji"></div>
                                        <div class="text-sm font-medium" x-text="group.label"></div>
                                        <div class="text-xs text-slate-500 mt-1" x-text="group.description"></div>
                                    </div>
                                </template>
                            </div>

                            <!-- Show included members for selected group -->
                            <div x-show="selectedGroup && groupMembers.length > 0" x-cloak class="mt-3 p-3 bg-primary/5 rounded-xl border border-primary/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-primary">Included Members:</span>
                                    <button type="button" @click="selectedGroup = null; assignmentType = 'individual'" class="btn btn-ghost btn-xs text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Clear
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="member in groupMembers" :key="'group-member-' + member.id">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-slate-700 rounded-full text-sm border border-primary/20">
                                            <span class="w-5 h-5 rounded-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center text-xs font-medium text-primary" x-text="member.initial"></span>
                                            <span x-text="member.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Members Selection -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Or Select Individual Members</span>
                                <span class="label-text-alt text-slate-500">Multi-select allowed</span>
                            </label>

                            <div class="relative">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text"
                                               x-model="search"
                                               @focus="open = true"
                                               @click="open = true"
                                               placeholder="Search family members..."
                                               class="input input-bordered w-full pl-10">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <button type="button" @click="selectAllMembers()" class="btn btn-outline btn-sm">
                                        Select All
                                    </button>
                                    <button type="button" @click="clearAll()" x-show="selectedMembers.length > 0" class="btn btn-ghost btn-sm text-error">
                                        Clear
                                    </button>
                                </div>

                                <!-- Dropdown List -->
                                <div x-show="open"
                                     x-cloak
                                     @click.away="open = false"
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
                                             :class="{ 'bg-primary/10': selectedMembers.includes(member.id) }">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center text-sm font-medium text-primary flex-shrink-0"
                                                 x-text="member.initial">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-slate-800" x-text="member.name"></div>
                                                <div class="text-xs text-slate-500" x-text="member.role"></div>
                                            </div>
                                            <div x-show="selectedMembers.includes(member.id)" class="w-5 h-5 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
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
                            <div x-show="selectedMembers.length > 0" class="flex flex-wrap gap-2 mt-3">
                                <template x-for="memberId in selectedMembers" :key="'tag-' + memberId">
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
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <input type="hidden" name="assignment_type" x-model="assignmentType">
                        <template x-for="memberId in selectedMembers" :key="'input-' + memberId">
                            <input type="hidden" name="assigned_members[]" :value="memberId">
                        </template>

                        <!-- Is Kid Goal -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="is_kid_goal" value="1" x-model="isKidGoal" class="checkbox checkbox-primary">
                                <div>
                                    <span class="label-text font-medium">Kid-friendly goal</span>
                                    <p class="text-xs text-slate-500">Show emoji status and star ratings</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Goal Type -->
                    <div class="space-y-4 mt-8">
                        <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                            <span class="text-lg">&#127919;</span> Goal Type
                        </h3>

                        <div class="form-control">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach($goalTypes as $key => $type)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="goal_type" value="{{ $key }}" x-model="goalType" class="hidden peer">
                                        <div class="text-center p-4 rounded-lg border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                            <div class="text-2xl mb-1">{{ $type['emoji'] }}</div>
                                            <div class="text-sm font-medium">{{ $type['label'] }}</div>
                                            <div class="text-xs text-slate-500 mt-1">{{ $type['description'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Habit Frequency -->
                        <div x-show="goalType === 'habit'" x-cloak x-transition class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">How often?</span>
                            </label>
                            <select name="habit_frequency" x-model="habitFrequency" class="select select-bordered">
                                <option value="">Select frequency...</option>
                                @foreach($habitFrequencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Milestone Target -->
                        <div x-show="goalType === 'milestone'" x-cloak x-transition class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Target number</span>
                                </label>
                                <input type="number" name="milestone_target" x-model="milestoneTarget" min="1" class="input input-bordered" placeholder="e.g., 50">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Unit</span>
                                </label>
                                <input type="text" name="milestone_unit" x-model="milestoneUnit" class="input input-bordered" placeholder="e.g., dollars, books, days">
                            </div>
                        </div>
                    </div>

                    <!-- Check-ins & Rewards -->
                    <div class="space-y-4 mt-8">
                        <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                            <span class="text-lg">&#10024;</span> Check-ins & Rewards
                        </h3>

                        <!-- Check-in Frequency -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Check-in reminders</span>
                            </label>
                            <select name="check_in_frequency" x-model="checkInFrequency" class="select select-bordered">
                                <option value="">No check-ins</option>
                                @foreach($checkInFrequencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-slate-500">Get gentle prompts to update progress</span>
                            </label>
                        </div>

                        <!-- Rewards -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="rewards_enabled" value="1" x-model="rewardsEnabled" @change="toggleRewards()" class="checkbox checkbox-primary">
                                <div>
                                    <span class="label-text font-medium">Enable reward</span>
                                    <p class="text-xs text-slate-500">Motivate with a special reward when done!</p>
                                </div>
                            </label>
                        </div>

                        <!-- Reward Type -->
                        <div x-show="rewardsEnabled" x-cloak x-transition class="space-y-4 p-4 bg-amber-50 rounded-lg">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Reward type</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($rewardTypes as $key => $reward)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="reward_type" value="{{ $key }}" x-model="rewardType" class="hidden peer">
                                            <div class="flex items-center gap-2 p-3 rounded-lg border-2 border-amber-200 peer-checked:border-amber-500 peer-checked:bg-amber-100 transition-all">
                                                <span class="text-xl">{{ $reward['emoji'] }}</span>
                                                <span class="text-sm font-medium">{{ $reward['label'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Custom Reward -->
                            <div x-show="rewardType === 'custom'" x-cloak class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Custom reward</span>
                                </label>
                                <input type="text" name="reward_custom" x-model="rewardCustom" class="input input-bordered" placeholder="e.g., Ice cream trip!">
                            </div>
                        </div>
                    </div>

                    <!-- Visibility (kid-friendly settings) -->
                    <div x-show="isKidGoal" x-cloak x-transition class="space-y-4 mt-8">
                        <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                            <span class="text-lg">&#128065;</span> Kid Settings
                        </h3>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="visible_to_kids" value="1" x-model="visibleToKids" class="checkbox checkbox-primary">
                                <div>
                                    <span class="label-text font-medium">Visible to kids</span>
                                    <p class="text-xs text-slate-500">Show this goal in kid-friendly view</p>
                                </div>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="kids_can_update" value="1" x-model="kidsCanUpdate" class="checkbox checkbox-primary">
                                <div>
                                    <span class="label-text font-medium">Kids can mark progress</span>
                                    <p class="text-xs text-slate-500">Allow kids to check in on their own</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Status (edit mode only) -->
                    @if($goal)
                        <div class="space-y-4 mt-8">
                            <h3 class="font-semibold text-slate-900 border-b pb-2">Status</h3>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Goal Status</span>
                                </label>
                                <select name="status" class="select select-bordered">
                                    @foreach(\App\Models\Goal::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $goal->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
                        <a href="{{ route('goals-todo.index', ['tab' => 'goals']) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $goal ? 'Update Goal' : 'Create Goal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $hasTemplates = !$goal && $templates->isNotEmpty();
    $initialStep = $goal ? 'form' : ($templates->isEmpty() ? 'form' : 'choose');
@endphp

<script>
function goalForm() {
    return {
        // Step management
        step: '{{ $initialStep }}',
        selectedTemplate: null,
        templateId: '{{ $fromTemplate?->id ?? "" }}',

        // Basic Info
        category: '{{ old('category', $fromTemplate?->category ?? $goal?->category ?? 'personal_growth') }}',

        // Assignment
        assignmentType: '{{ old('assignment_type', $goal?->assignment_type ?? 'individual') }}',
        assignedTo: '{{ old('assigned_to', $goal?->assigned_to ?? '') }}',
        isKidGoal: {{ old('is_kid_goal', $goal?->is_kid_goal ?? false) ? 'true' : 'false' }},

        // Goal Type
        goalType: '{{ old('goal_type', $fromTemplate?->goal_type ?? $goal?->goal_type ?? 'one_time') }}',
        habitFrequency: '{{ old('habit_frequency', $fromTemplate?->habit_frequency ?? $goal?->habit_frequency ?? '') }}',
        milestoneTarget: '{{ old('milestone_target', $fromTemplate?->milestone_target ?? $goal?->milestone_target ?? '') }}',
        milestoneUnit: '{{ old('milestone_unit', $fromTemplate?->milestone_unit ?? $goal?->milestone_unit ?? '') }}',

        // Check-ins & Rewards
        checkInFrequency: '{{ old('check_in_frequency', $fromTemplate?->suggested_check_in_frequency ?? $goal?->check_in_frequency ?? '') }}',
        rewardsEnabled: {{ old('rewards_enabled', $fromTemplate?->suggested_rewards ?? $goal?->rewards_enabled ?? false) ? 'true' : 'false' }},
        rewardType: '{{ old('reward_type', $fromTemplate?->suggested_reward_type ?? $goal?->reward_type ?? '') }}',
        rewardCustom: @json(old('reward_custom', $goal?->reward_custom ?? '')),

        // Kid Settings
        visibleToKids: {{ old('visible_to_kids', $goal?->visible_to_kids ?? true) ? 'true' : 'false' }},
        kidsCanUpdate: {{ old('kids_can_update', $goal?->kids_can_update ?? false) ? 'true' : 'false' }},

        init() {
            // If coming from a template via URL, go directly to form
            @if($fromTemplate)
                this.step = 'form';
                this.selectedTemplate = true;
            @endif
        },

        selectTemplate(template) {
            this.selectedTemplate = template;
            this.templateId = template.id || '';
            this.category = template.category || 'personal_growth';
            this.goalType = template.goalType || 'one_time';
            this.habitFrequency = template.habitFrequency || '';
            this.milestoneTarget = template.milestoneTarget || '';
            this.milestoneUnit = template.milestoneUnit || '';
            this.checkInFrequency = template.checkInFrequency || '';
            this.rewardsEnabled = template.rewardsEnabled || false;
            this.rewardType = template.rewardType || (this.rewardsEnabled ? 'sticker' : '');
            this.isKidGoal = template.isKidGoal || false;

            // Go to form step
            this.step = 'form';

            // Set title and description via DOM
            this.$nextTick(() => {
                setTimeout(() => {
                    const titleInput = document.getElementById('goalTitle');
                    const descInput = document.getElementById('goalDescription');
                    if (titleInput) {
                        titleInput.value = template.title || '';
                        titleInput.focus();
                    }
                    if (descInput) {
                        descInput.value = template.description || '';
                    }
                }, 100);
            });
        },

        goBack() {
            if (this.selectedTemplate) {
                this.step = 'templates';
            } else {
                this.step = 'choose';
            }
        },

        // Auto-select default reward type when enabling rewards
        toggleRewards() {
            if (this.rewardsEnabled && !this.rewardType) {
                this.rewardType = 'sticker';
            }
        }
    }
}

function goalAssigneeSelect() {
    return {
        open: false,
        search: '',
        selectedGroup: '{{ old('assignment_type', $goal?->assignment_type ?? '') }}',
        selectedMembers: [
            @if($goal && $goal->assignedMembers)
                @foreach($goal->assignedMembers as $member)
                    {{ $member->id }},
                @endforeach
            @endif
        ],
        assignmentType: '{{ old('assignment_type', $goal?->assignment_type ?? 'individual') }}',

        groups: [
            { id: 'family', emoji: '👨‍👩‍👧‍👦', label: 'Entire Family', description: 'All family members' },
            { id: 'parents', emoji: '👫', label: 'Parents Only', description: 'Just the parents' },
            { id: 'kids', emoji: '👧👦', label: 'All Kids', description: 'All children' },
            { id: 'parent_kid', emoji: '👨‍👧', label: 'Parent + Kid', description: 'Parent-child duo' }
        ],

        members: [
            @foreach($familyMembers as $member)
            {
                id: {{ $member->id }},
                name: '{{ $member->first_name }} {{ $member->last_name ?? '' }}',
                initial: '{{ strtoupper(substr($member->first_name, 0, 1)) }}',
                role: '{{ $member->role ? ucfirst($member->role) : ($member->relationship_name ?? '') }}',
                isParent: {{ in_array($member->role, ['parent', 'guardian', 'owner']) ? 'true' : 'false' }},
                isKid: {{ in_array($member->role, ['child', 'kid']) ? 'true' : 'false' }}
            },
            @endforeach
        ],

        get filteredMembers() {
            if (!this.search) return this.members;
            const searchLower = this.search.toLowerCase();
            return this.members.filter(m => m.name.toLowerCase().includes(searchLower));
        },

        get groupMembers() {
            if (!this.selectedGroup) return [];
            switch(this.selectedGroup) {
                case 'family':
                    return this.members;
                case 'parents':
                    return this.members.filter(m => m.isParent);
                case 'kids':
                    return this.members.filter(m => m.isKid);
                case 'parent_kid':
                    // Return first parent and first kid
                    const parent = this.members.find(m => m.isParent);
                    const kid = this.members.find(m => m.isKid);
                    return [parent, kid].filter(Boolean);
                default:
                    return [];
            }
        },

        selectGroup(groupId) {
            if (this.selectedGroup === groupId) {
                this.selectedGroup = null;
                this.assignmentType = 'individual';
            } else {
                this.selectedGroup = groupId;
                this.selectedMembers = [];
                this.assignmentType = groupId;
            }
        },

        toggleMember(id) {
            this.selectedGroup = null;
            this.assignmentType = 'individual';

            const index = this.selectedMembers.indexOf(id);
            if (index > -1) {
                this.selectedMembers.splice(index, 1);
            } else {
                this.selectedMembers.push(id);
            }
        },

        selectAllMembers() {
            this.selectedGroup = null;
            this.assignmentType = 'individual';
            this.selectedMembers = this.members.map(m => m.id);
        },

        clearAll() {
            this.selectedGroup = null;
            this.selectedMembers = [];
            this.assignmentType = 'individual';
        },

        getMemberName(id) {
            const member = this.members.find(m => m.id === id);
            return member ? member.name : '';
        },

        getGroupLabel(groupId) {
            const group = this.groups.find(g => g.id === groupId);
            return group ? group.label : '';
        },

        getGroupEmoji(groupId) {
            const group = this.groups.find(g => g.id === groupId);
            return group ? group.emoji : '';
        }
    }
}
</script>
@endsection
