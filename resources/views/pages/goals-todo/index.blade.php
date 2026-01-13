@extends('layouts.dashboard')

@section('title', 'Goals & To-Do')
@section('page-name', 'Goals & To-Do')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Goals & To-Do</li>
@endsection

@section('page-title', 'Goals & To-Do')
@section('page-description', 'Track your goals and manage family tasks - everyone stays aligned.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="border-b border-base-200 mb-6">
                <nav class="-mb-px flex gap-6">
                    <a href="{{ route('goals-todo.index', ['tab' => 'goals']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm {{ $tab === 'goals' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <span class="icon-[tabler--target-arrow] size-5 inline-block align-middle mr-2"></span>
                        Goals
                        @if($goals->where('status', 'active')->count() > 0)
                            <span class="badge badge-sm {{ $tab === 'goals' ? 'badge-primary' : 'badge-ghost' }} ml-2">{{ $goals->where('status', 'active')->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('goals-todo.index', ['tab' => 'todos']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm {{ $tab === 'todos' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <span class="icon-[tabler--checkbox] size-5 inline-block align-middle mr-2"></span>
                        To-Do List
                        @if($openTasks->count() > 0)
                            <span class="badge badge-sm {{ $tab === 'todos' ? 'badge-primary' : 'badge-ghost' }} ml-2">{{ $openTasks->count() }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            @if($tab === 'goals')
                @include('pages.goals-todo.partials.goals-tab')
            @else
                @include('pages.goals-todo.partials.todos-tab')
            @endif
        </div>
    </div>
</div>

<!-- Edit Scope Modal (for recurring tasks) -->
<dialog id="editScopeModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg mb-2">Edit Recurring Task</h3>
        <p class="text-slate-500 text-sm mb-4">Choose what you want to edit:</p>
        <div class="space-y-2">
            <a href="#" id="editThisOnly" class="btn btn-outline btn-block justify-start gap-3">
                <span class="icon-[tabler--circle-dot] size-5"></span>
                <div class="text-left">
                    <div class="font-medium">This occurrence only</div>
                    <div class="text-xs text-slate-500">Change only this instance</div>
                </div>
            </a>
            <a href="#" id="editFuture" class="btn btn-outline btn-block justify-start gap-3">
                <span class="icon-[tabler--arrow-right] size-5"></span>
                <div class="text-left">
                    <div class="font-medium">This and future</div>
                    <div class="text-xs text-slate-500">Change this and all future occurrences</div>
                </div>
            </a>
            <a href="#" id="editAll" class="btn btn-outline btn-block justify-start gap-3">
                <span class="icon-[tabler--arrows-exchange] size-5"></span>
                <div class="text-left">
                    <div class="font-medium">Entire series</div>
                    <div class="text-xs text-slate-500">Change all occurrences (past and future)</div>
                </div>
            </a>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Delete Confirmation Modal Component -->
<x-delete-confirm-modal />

<!-- Generic Confirmation Modal Component -->
<x-confirm-modal />

<!-- Snooze Modal -->
<dialog id="snoozeModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg">Snooze Task</h3>
        <form id="snoozeForm" method="POST" class="space-y-4 pt-4">
            @csrf
            <div>
                <label class="label">
                    <span class="label-text">Snooze until</span>
                </label>
                <input type="datetime-local" name="until" id="snoozeUntil" class="input input-bordered w-full" required>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="setSnooze('tomorrow')" class="btn btn-sm btn-outline">Tomorrow</button>
                <button type="button" onclick="setSnooze('weekend')" class="btn btn-sm btn-outline">This Weekend</button>
                <button type="button" onclick="setSnooze('week')" class="btn btn-sm btn-outline">Next Week</button>
            </div>
            <div class="modal-action">
                <button type="button" onclick="document.getElementById('snoozeModal').close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Snooze</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
// Dropdown functionality
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
        dropdownJustOpened = true;
        setTimeout(() => { dropdownJustOpened = false; }, 100);
    } else {
        menu.classList.add('hidden');
        activeDropdown = null;
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (dropdownJustOpened) return;
    if (!activeDropdown) return;

    const dropdown = document.getElementById(activeDropdown);
    if (!dropdown) {
        activeDropdown = null;
        return;
    }

    if (!dropdown.contains(event.target)) {
        dropdown.querySelector('.dropdown-content')?.classList.add('hidden');
        activeDropdown = null;
    }
});

// Open edit scope modal for recurring tasks
function openEditScopeModal(taskId, editUrl) {
    document.getElementById('editThisOnly').href = editUrl + '?edit_scope=this';
    document.getElementById('editFuture').href = editUrl + '?edit_scope=future';
    document.getElementById('editAll').href = editUrl + '?edit_scope=all';
    document.getElementById('editScopeModal').showModal();
}

// Show occurrences in a modal
function showOccurrences(taskId) {
    // For now, navigate to task detail view - TODO: implement modal
    window.location.href = `{{ url('/goals-todo/tasks') }}/${taskId}`;
}

// Toggle todo item completion
function toggleTask(taskId) {
    fetch(`{{ url('/goals-todo/tasks') }}/${taskId}/toggle`, {
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
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Confirm and toggle recurring series pause/resume
function confirmToggleSeries(taskId, isPaused, taskTitle) {
    const action = isPaused ? 'resume' : 'pause';
    const type = isPaused ? 'play' : 'pause';

    showConfirmModal({
        title: isPaused ? 'Resume Series?' : 'Pause Series?',
        message: isPaused
            ? `Resume generating new occurrences for "${taskTitle}"?`
            : `Pause all future occurrences for "${taskTitle}"? Existing occurrences will remain.`,
        type: type,
        btnText: isPaused ? 'Resume' : 'Pause',
        btnIcon: isPaused ? 'icon-[tabler--player-play]' : 'icon-[tabler--player-pause]',
        onConfirm: function() {
            toggleSeries(taskId);
        }
    });
}

function toggleSeries(taskId) {
    fetch(`{{ url('/goals-todo/tasks') }}/${taskId}/toggle-series`, {
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
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        location.reload();
    });
}

// Complete occurrence
function completeOccurrence(occurrenceId) {
    fetch(`{{ url('/goals-todo/occurrences') }}/${occurrenceId}/complete`, {
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

// Skip occurrence
function skipOccurrence(occurrenceId) {
    fetch(`{{ url('/goals-todo/occurrences') }}/${occurrenceId}/skip`, {
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

// Open snooze modal
function openSnoozeModal(occurrenceId) {
    document.getElementById('snoozeForm').action = `{{ url('/goals-todo/occurrences') }}/${occurrenceId}/snooze`;
    document.getElementById('snoozeModal').showModal();
}

// Set snooze preset
function setSnooze(preset) {
    const now = new Date();
    let snoozeDate;

    switch(preset) {
        case 'tomorrow':
            snoozeDate = new Date(now.getTime() + 24 * 60 * 60 * 1000);
            snoozeDate.setHours(9, 0, 0, 0);
            break;
        case 'weekend':
            // Find next Saturday
            snoozeDate = new Date(now);
            snoozeDate.setDate(now.getDate() + (6 - now.getDay() + 7) % 7);
            snoozeDate.setHours(10, 0, 0, 0);
            break;
        case 'week':
            snoozeDate = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
            snoozeDate.setHours(9, 0, 0, 0);
            break;
    }

    // Format for datetime-local input
    const formatted = snoozeDate.toISOString().slice(0, 16);
    document.getElementById('snoozeUntil').value = formatted;
}

// Update goal progress
function updateGoalProgress(goalId) {
    const input = document.getElementById(`progress_${goalId}`);
    const progress = input.value;

    fetch(`{{ url('/goals-todo/goals') }}/${goalId}/progress`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ progress: progress })
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
