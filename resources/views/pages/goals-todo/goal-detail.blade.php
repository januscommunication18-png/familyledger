@extends('layouts.dashboard')

@section('title', $goal->title)
@section('page-name', 'Goals & To-Do')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('goals-todo.index', ['tab' => 'goals']) }}">Goals & To-Do</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ Str::limit($goal->title, 30) }}</li>
@endsection

@section('page-title', $goal->title)
@section('page-description', $goal->description ?? 'Track progress towards this goal')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Goal Header Card -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                <!-- Category & Title -->
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-16 h-16 rounded-xl bg-{{ $goal->category_color }}-100 flex items-center justify-center text-4xl shrink-0">
                        {{ $goal->category_emoji }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $goal->title }}</h2>
                            <span class="badge badge-{{ $goal->status === 'done' ? 'success' : ($goal->status === 'archived' ? 'ghost' : 'primary') }}">
                                {{ $goal->status_emoji }} {{ ucfirst($goal->status) }}
                            </span>
                            @if($goal->is_kid_goal)
                                <span class="badge badge-warning badge-outline">Kid Goal</span>
                            @endif
                        </div>
                        @if($goal->description)
                            <p class="text-slate-600 mt-2">{{ $goal->description }}</p>
                        @endif

                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-4 mt-3 text-sm text-slate-500">
                            <span class="flex items-center gap-1">
                                <span class="text-base">{{ $goal->goal_type_emoji }}</span>
                                {{ $goal->goal_type_details['label'] }}
                                @if($goal->goal_type === 'habit' && $goal->habit_frequency)
                                    ({{ ucfirst($goal->habit_frequency) }})
                                @endif
                            </span>
                            <span class="flex items-center gap-1">
                                {{ $goal->assignment_type_details['emoji'] }}
                                {{ $goal->assignment_type_details['label'] }}
                                @if($goal->assignedTo)
                                    - {{ $goal->assignedTo->full_name }}
                                @endif
                            </span>
                            @if($goal->check_in_frequency)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--calendar-check] size-4"></span>
                                    {{ ucfirst($goal->check_in_frequency) }} check-ins
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    @if($goal->status === 'active' || $goal->status === 'in_progress')
                        <button onclick="openCheckInModal()" class="btn {{ $goal->needs_check_in ? 'btn-primary' : 'btn-outline btn-primary' }} btn-sm gap-1">
                            <span class="icon-[tabler--message-check] size-4"></span>
                            Check In
                            @if($goal->needs_check_in)
                                <span class="badge badge-xs badge-warning">Due</span>
                            @endif
                        </button>
                    @endif
                    <a href="{{ route('goals-todo.goals.edit', $goal) }}" class="btn btn-ghost btn-sm gap-1">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                    <div class="relative" id="goalActionsDropdown">
                        <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="toggleDropdown('goalActionsDropdown', event)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/>
                                <circle cx="12" cy="12" r="2"/>
                                <circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <ul class="dropdown-content hidden absolute right-0 top-full mt-1 z-50 p-2 shadow-xl bg-base-100 rounded-xl w-52 border border-base-200">
                            @if($goal->status !== 'done')
                                <li>
                                    <a href="javascript:void(0)" onclick="markGoalDone()" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                        <span class="icon-[tabler--check] shrink-0 size-4 text-success"></span>
                                        Mark Done
                                    </a>
                                </li>
                            @endif
                            @if($goal->status !== 'skipped')
                                <li>
                                    <a href="javascript:void(0)" onclick="skipGoal()" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                        <span class="icon-[tabler--player-skip-forward] shrink-0 size-4 text-slate-400"></span>
                                        Skip
                                    </a>
                                </li>
                            @endif
                            @if($goal->status === 'done' || $goal->status === 'skipped')
                                <li>
                                    <a href="javascript:void(0)" onclick="updateGoalStatus('active')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                        <span class="icon-[tabler--rotate] shrink-0 size-4 text-slate-400"></span>
                                        Reactivate
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="javascript:void(0)" onclick="updateGoalStatus('archived')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                    <span class="icon-[tabler--archive] shrink-0 size-4 text-slate-400"></span>
                                    Archive
                                </a>
                            </li>
                            <li class="my-1 border-t border-base-200"></li>
                            <li>
                                <a href="javascript:void(0)" onclick="confirmDelete('{{ route('goals-todo.goals.destroy', $goal) }}', 'Are you sure you want to delete this goal?')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error/10">
                                    <span class="icon-[tabler--trash] shrink-0 size-4"></span>
                                    Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Progress Section (Milestone goals) -->
            @if($goal->goal_type === 'milestone')
                <div class="mt-6 p-4 bg-base-200/50 rounded-xl">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-sm text-slate-600">Progress</span>
                            <p class="text-xl font-bold text-slate-900">{{ $goal->milestone_current }} / {{ $goal->milestone_target }} {{ $goal->milestone_unit ?? 'units' }}</p>
                        </div>
                        <div class="text-right">
                            @if($goal->show_emoji_status && $goal->star_display)
                                <div class="text-2xl">{{ $goal->star_display }}</div>
                            @endif
                            <span class="text-3xl font-bold {{ $goal->status === 'done' ? 'text-success' : '' }}">{{ number_format($goal->milestone_progress, 0) }}%</span>
                        </div>
                    </div>
                    <div class="h-3 bg-base-300 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $goal->category_color }}-500 transition-all duration-500" style="width: {{ min(100, $goal->milestone_progress) }}%"></div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <span class="text-sm text-slate-600">Add progress:</span>
                        <input type="number" id="progressToAdd" min="1" value="1" class="input input-bordered input-sm w-24" placeholder="+1">
                        <button onclick="addProgress()" class="btn btn-primary btn-sm">Add</button>
                    </div>
                </div>
            @endif

            <!-- Reward Section (if enabled) -->
            @if($goal->rewards_enabled)
                <div class="mt-6 p-4 rounded-xl {{ $goal->reward_claimed ? 'bg-success/10' : 'bg-amber-50' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $goal->reward_details['emoji'] ?? '🎁' }}</span>
                            <div>
                                <p class="font-medium">Reward: {{ $goal->reward_details['label'] ?? 'Custom Reward' }}</p>
                                @if($goal->reward_custom)
                                    <p class="text-sm text-slate-600">{{ $goal->reward_custom }}</p>
                                @endif
                            </div>
                        </div>
                        @if($goal->status === 'done' && !$goal->reward_claimed)
                            <button onclick="claimReward()" class="btn btn-warning btn-sm">Claim Reward!</button>
                        @elseif($goal->reward_claimed)
                            <span class="badge badge-success">Claimed!</span>
                        @else
                            <span class="badge badge-ghost">Complete goal to claim</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Stats -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-base-200/30 rounded-lg">
                    <p class="text-2xl font-bold text-slate-900">{{ $goal->checkIns->count() }}</p>
                    <p class="text-xs text-slate-500">Total Check-ins</p>
                </div>
                <div class="text-center p-3 bg-base-200/30 rounded-lg">
                    <p class="text-2xl font-bold text-emerald-600">{{ $weeklyCheckIns }}</p>
                    <p class="text-xs text-slate-500">This Week</p>
                </div>
                <div class="text-center p-3 bg-base-200/30 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $goal->tasks->whereIn('status', ['open', 'in_progress'])->count() }}</p>
                    <p class="text-xs text-slate-500">Active Tasks</p>
                </div>
                <div class="text-center p-3 bg-base-200/30 rounded-lg">
                    <p class="text-2xl font-bold text-violet-600">{{ $goal->tasks->where('status', 'completed')->count() }}</p>
                    <p class="text-xs text-slate-500">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Modal -->
    <div id="checkInModal" class="hidden fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black/50" onclick="closeCheckInModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-base-100 rounded-2xl max-w-md w-full p-6 shadow-2xl pointer-events-auto">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <span class="text-2xl">{{ $goal->category_emoji }}</span>
                    Check In
                </h3>
                <p class="text-slate-600 mb-6">{{ $goal->check_in_prompt }}</p>

                <form id="checkInForm" onsubmit="submitCheckIn(event)">
                    <!-- Status Selection -->
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <label class="cursor-pointer">
                            <input type="radio" name="check_in_status" value="done" class="hidden peer">
                            <div class="text-center p-4 rounded-lg border-2 border-base-300 peer-checked:border-success peer-checked:bg-success/10 transition-all hover:border-success/50">
                                <div class="text-2xl mb-1">✅</div>
                                <div class="text-sm font-medium">Done!</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="check_in_status" value="in_progress" class="hidden peer">
                            <div class="text-center p-4 rounded-lg border-2 border-base-300 peer-checked:border-info peer-checked:bg-info/10 transition-all hover:border-info/50">
                                <div class="text-2xl mb-1">💪</div>
                                <div class="text-sm font-medium">In Progress</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="check_in_status" value="skipped" class="hidden peer">
                            <div class="text-center p-4 rounded-lg border-2 border-base-300 peer-checked:border-warning peer-checked:bg-warning/10 transition-all hover:border-warning/50">
                                <div class="text-2xl mb-1">⏭️</div>
                                <div class="text-sm font-medium">Skip</div>
                            </div>
                        </label>
                    </div>

                    @if($goal->goal_type === 'milestone')
                        <!-- Progress Added -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Progress to add</span>
                            </label>
                            <input type="number" name="check_in_progress" min="0" class="input input-bordered" placeholder="e.g., 5">
                        </div>
                    @endif

                    @if($goal->is_kid_goal)
                        <!-- Star Rating -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">How did it go?</span>
                            </label>
                            <div class="flex gap-2" id="starRating">
                                <button type="button" onclick="setStars(1)" class="text-3xl transition-transform hover:scale-110 opacity-30" data-star="1">⭐</button>
                                <button type="button" onclick="setStars(2)" class="text-3xl transition-transform hover:scale-110 opacity-30" data-star="2">⭐</button>
                                <button type="button" onclick="setStars(3)" class="text-3xl transition-transform hover:scale-110 opacity-30" data-star="3">⭐</button>
                            </div>
                            <input type="hidden" name="star_rating" id="starRatingInput" value="">
                        </div>
                    @endif

                    <!-- Note -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Add a note (optional)</span>
                        </label>
                        <textarea name="check_in_note" rows="2" class="textarea textarea-bordered" placeholder="How did it go?"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeCheckInModal()" class="btn btn-ghost flex-1">Cancel</button>
                        <button type="submit" class="btn btn-primary flex-1">Save Check-in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Check-ins -->
    @if($goal->checkIns->count() > 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span class="icon-[tabler--history] size-5"></span>
                    Recent Check-ins
                </h3>
                <div class="space-y-3">
                    @foreach($goal->checkIns as $checkIn)
                        <div class="flex items-start gap-3 p-3 bg-base-200/50 rounded-lg">
                            <span class="text-xl">{{ $checkIn->status_emoji }}</span>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $checkIn->status_label }}</span>
                                    @if($checkIn->star_rating)
                                        <span>{{ $checkIn->star_display }}</span>
                                    @endif
                                    @if($checkIn->progress_added)
                                        <span class="badge badge-sm badge-success">+{{ $checkIn->progress_added }}</span>
                                    @endif
                                </div>
                                @if($checkIn->note)
                                    <p class="text-sm text-slate-600 mt-1">{{ $checkIn->note }}</p>
                                @endif
                                @if($checkIn->parent_message)
                                    <p class="text-sm text-primary mt-1">💬 {{ $checkIn->parent_message }}</p>
                                @endif
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $checkIn->check_in_date->format('M j, Y') }}
                                    @if($checkIn->checkedInBy)
                                        by {{ $checkIn->checkedInBy->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Linked Tasks -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Linked Tasks</h3>
                <a href="{{ route('goals-todo.tasks.create', ['goal_id' => $goal->id]) }}" class="btn btn-primary btn-sm gap-1">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Task
                </a>
            </div>

            @if($goal->tasks->count() === 0)
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="icon-[tabler--checkbox] size-6 text-slate-400"></span>
                    </div>
                    <p class="text-slate-500 mb-3">No tasks linked to this goal yet.</p>
                    <a href="{{ route('goals-todo.tasks.create', ['goal_id' => $goal->id]) }}" class="btn btn-sm btn-outline">Add First Task</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($goal->tasks->whereIn('status', ['open', 'in_progress', 'snoozed']) as $task)
                        <div class="flex items-center gap-3 p-3 border border-base-200 rounded-lg hover:border-primary/30 transition-colors">
                            <button onclick="toggleTask({{ $task->id }})" class="w-5 h-5 rounded border-2 {{ $task->status === 'completed' ? 'bg-primary border-primary' : 'border-slate-300 hover:border-primary' }} flex items-center justify-center transition-colors shrink-0">
                                @if($task->status === 'completed')
                                    <span class="icon-[tabler--check] size-3 text-white"></span>
                                @endif
                            </button>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('goals-todo.tasks.edit', $task) }}" class="font-medium text-slate-900 hover:text-primary">{{ $task->title }}</a>
                                    @if($task->is_recurring)
                                        <span class="badge badge-xs badge-info">{{ $task->recurrence_summary }}</span>
                                    @endif
                                    @if($task->count_toward_goal)
                                        <span class="icon-[tabler--target-arrow] size-3.5 text-primary" title="Counts toward goal"></span>
                                    @endif
                                </div>
                                @if($task->due_date)
                                    <span class="text-xs text-slate-500 {{ $task->is_overdue ? 'text-error' : '' }}">
                                        Due: {{ $task->due_date->format('M j') }}
                                    </span>
                                @endif
                            </div>
                            <div class="relative" id="taskDropdown{{ $task->id }}">
                                <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="toggleDropdown('taskDropdown{{ $task->id }}', event)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="5" cy="12" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="19" cy="12" r="2"/>
                                    </svg>
                                </button>
                                <ul class="dropdown-content hidden absolute right-0 top-full mt-1 z-50 p-2 shadow-xl bg-base-100 rounded-xl w-44 border border-base-200">
                                    <li>
                                        <a href="{{ route('goals-todo.tasks.edit', $task) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                            <span class="icon-[tabler--edit] shrink-0 size-4 text-slate-400"></span>
                                            Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" onclick="confirmDelete('{{ route('goals-todo.tasks.destroy', $task) }}')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error/10">
                                            <span class="icon-[tabler--trash] shrink-0 size-4"></span>
                                            Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endforeach

                    <!-- Completed Tasks -->
                    @php $completedTasks = $goal->tasks->where('status', 'completed'); @endphp
                    @if($completedTasks->count() > 0)
                        <div class="pt-3 border-t">
                            <button onclick="document.getElementById('completedTasksList').classList.toggle('hidden')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                                Completed ({{ $completedTasks->count() }})
                            </button>
                            <div id="completedTasksList" class="hidden mt-3 space-y-2">
                                @foreach($completedTasks as $task)
                                    <div class="flex items-center gap-3 p-2 opacity-60">
                                        <button onclick="toggleTask({{ $task->id }})" class="w-5 h-5 rounded bg-primary border-primary flex items-center justify-center shrink-0">
                                            <span class="icon-[tabler--check] size-3 text-white"></span>
                                        </button>
                                        <span class="line-through text-slate-400 flex-1">{{ $task->title }}</span>
                                        <span class="text-xs text-slate-400">{{ $task->completed_at?->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal Component -->
<x-delete-confirm-modal />

<script>
let currentStars = 0;
let activeDropdown = null;
let dropdownJustOpened = false;

function toggleDropdown(dropdownId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;

    const menu = dropdown.querySelector('.dropdown-content');
    if (!menu) return;

    // Close any other open dropdown
    if (activeDropdown && activeDropdown !== dropdownId) {
        const prevDropdown = document.getElementById(activeDropdown);
        if (prevDropdown) {
            const prevMenu = prevDropdown.querySelector('.dropdown-content');
            if (prevMenu) prevMenu.classList.add('hidden');
        }
    }

    // Toggle current dropdown
    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        activeDropdown = dropdownId;
        // Set flag to prevent immediate close
        dropdownJustOpened = true;
        setTimeout(() => { dropdownJustOpened = false; }, 100);
    } else {
        menu.classList.add('hidden');
        activeDropdown = null;
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    // Skip if dropdown was just opened
    if (dropdownJustOpened) return;
    if (!activeDropdown) return;

    const dropdown = document.getElementById(activeDropdown);
    if (!dropdown) {
        activeDropdown = null;
        return;
    }

    // Check if click is inside the dropdown
    if (!dropdown.contains(event.target)) {
        dropdown.querySelector('.dropdown-content')?.classList.add('hidden');
        activeDropdown = null;
    }
});

// Check-in Modal functions
function openCheckInModal() {
    document.getElementById('checkInModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCheckInModal() {
    document.getElementById('checkInModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function setStars(rating) {
    currentStars = rating;
    document.getElementById('starRatingInput').value = rating;
    document.querySelectorAll('#starRating button').forEach((btn, index) => {
        btn.classList.toggle('opacity-30', index >= rating);
        btn.classList.toggle('opacity-100', index < rating);
    });
}

function submitCheckIn(event) {
    event.preventDefault();
    const form = document.getElementById('checkInForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    const status = formData.get('check_in_status');
    if (!status) {
        alert('Please select a status');
        return;
    }

    // Disable button while submitting
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    fetch('{{ route('goals-todo.goals.check-in', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status,
            note: formData.get('check_in_note'),
            progress_added: formData.get('check_in_progress') || null,
            star_rating: formData.get('star_rating') || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            closeCheckInModal();

            // Show success message
            showSuccessMessage('Check-in recorded successfully!');

            // Reload page after short delay to show message
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Failed to save check-in. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Check-in';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Check-in';
    });
}

function showSuccessMessage(message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 z-[100] bg-success text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2';
    toast.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>' + message;
    document.body.appendChild(toast);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function addProgress() {
    const progressInput = document.getElementById('progressToAdd');
    const progressToAdd = parseInt(progressInput?.value || 1);

    fetch('{{ route('goals-todo.goals.progress', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            progress: {{ $goal->milestone_current ?? 0 }} + progressToAdd
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function claimReward() {
    fetch('{{ route('goals-todo.goals.claim-reward', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function toggleTask(taskId) {
    fetch(`{{ url('/goals-todo/tasks') }}/${taskId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markGoalDone() {
    fetch('{{ route('goals-todo.goals.mark-done', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function skipGoal() {
    fetch('{{ route('goals-todo.goals.skip', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function updateGoalStatus(status) {
    fetch('{{ route('goals-todo.goals.status', $goal) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endsection
