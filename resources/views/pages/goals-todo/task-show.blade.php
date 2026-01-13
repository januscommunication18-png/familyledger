@extends('layouts.dashboard')

@section('title', $task->title)
@section('page-name', 'Goals & To-Do')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('goals-todo.index', ['tab' => 'todos']) }}">Goals & To-Do</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ Str::limit($task->title, 30) }}</li>
@endsection

@section('page-title', $task->title)
@section('page-description', $task->is_recurring ? 'Recurring task details and schedule' : 'Task details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Task Header -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold text-slate-900 {{ $task->status === 'completed' ? 'line-through text-slate-400' : '' }}">
                            {{ $task->title }}
                        </h1>

                        <!-- Status Badge -->
                        <span class="badge {{ $task->status === 'completed' ? 'badge-success' : ($task->is_overdue ? 'badge-error' : 'badge-primary') }}">
                            {{ ucfirst($task->status) }}
                        </span>

                        <!-- Recurring Badge -->
                        @if($task->is_recurring)
                            <span class="badge badge-info gap-1">
                                <span class="icon-[tabler--repeat] size-3"></span>
                                {{ $task->recurrence_summary }}
                            </span>
                            @if($task->is_series_paused)
                                <span class="badge badge-warning">Paused</span>
                            @endif
                        @endif
                    </div>

                    @if($task->description)
                        <p class="text-slate-600 mt-2">{{ $task->description }}</p>
                    @endif

                    <div class="flex items-center gap-4 mt-4 text-sm text-slate-500 flex-wrap">
                        <!-- Category -->
                        <span class="flex items-center gap-1">
                            <span class="{{ $task->category_icon }} size-4"></span>
                            {{ $task->category_name }}
                        </span>

                        <!-- Priority -->
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--flag] size-4 {{ $task->priority === 'urgent' ? 'text-error' : ($task->priority === 'high' ? 'text-warning' : '') }}"></span>
                            {{ ucfirst($task->priority) }}
                        </span>

                        <!-- Due Date -->
                        @if($task->due_date)
                            <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-error font-medium' : '' }}">
                                <span class="icon-[tabler--calendar] size-4"></span>
                                {{ $task->due_date->format('M j, Y') }}
                                @if($task->due_time)
                                    at {{ \Carbon\Carbon::parse($task->due_time)->format('g:i A') }}
                                @endif
                            </span>
                        @endif

                        <!-- Assignees -->
                        @if($task->assignees->count() > 0)
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--users] size-4"></span>
                                @foreach($task->assignees as $assignee)
                                    <span class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ $assignee->first_name }}</span>
                                @endforeach
                            </span>
                        @endif

                        <!-- Proof Required -->
                        @if($task->proof_required)
                            <span class="flex items-center gap-1 text-violet-600">
                                <span class="icon-[tabler--camera] size-4"></span>
                                Proof required
                            </span>
                        @endif
                    </div>

                    <!-- Goal Link -->
                    @if($task->goal)
                        <div class="mt-4 p-3 bg-base-200 rounded-lg inline-flex items-center gap-2">
                            <span class="icon-[tabler--target-arrow] size-5"></span>
                            <span class="text-sm">Linked to:</span>
                            <a href="{{ route('goals-todo.goals.show', $task->goal) }}" class="font-medium text-primary hover:underline">
                                {{ $task->goal->title }}
                            </a>
                            @if($task->count_toward_goal)
                                <span class="badge badge-xs badge-success">Counts toward goal</span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    @if($task->is_recurring)
                        <button onclick="confirmToggleSeries({{ $task->is_series_paused ? 'true' : 'false' }})" class="btn btn-ghost btn-sm gap-1">
                            @if($task->is_series_paused)
                                <span class="icon-[tabler--player-play] size-4"></span>
                                Resume
                            @else
                                <span class="icon-[tabler--player-pause] size-4"></span>
                                Pause
                            @endif
                        </button>
                    @endif
                    <a href="{{ route('goals-todo.tasks.edit', $task) }}{{ $task->is_recurring ? '?edit_scope=all' : '' }}" class="btn btn-primary btn-sm gap-1">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($task->is_recurring)
    <!-- Series Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Occurrences -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base">
                    <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                    Upcoming (Next 5)
                </h2>
                @if($upcomingOccurrences->count() > 0)
                    <div class="divide-y">
                        @foreach($upcomingOccurrences as $occ)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button onclick="completeOccurrence({{ $occ->id }})" class="w-5 h-5 rounded border-2 {{ $occ->status === 'snoozed' ? 'border-warning bg-warning/20' : 'border-slate-300 hover:border-primary' }} flex items-center justify-center transition-colors">
                                        @if($occ->status === 'snoozed')
                                            <span class="icon-[tabler--clock] size-3 text-warning"></span>
                                        @endif
                                    </button>
                                    <div>
                                        <div class="font-medium">{{ $occ->scheduled_date->format('D, M j') }}</div>
                                        @if($occ->scheduled_time)
                                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($occ->scheduled_time)->format('g:i A') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($occ->assignee)
                                        <span class="text-xs bg-base-200 px-2 py-1 rounded">{{ $occ->assignee->first_name }}</span>
                                    @endif
                                    @if($occ->status === 'snoozed')
                                        <span class="badge badge-xs badge-warning">Snoozed</span>
                                    @endif
                                    <div class="dropdown dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square">
                                            <span class="icon-[tabler--dots] size-4"></span>
                                        </button>
                                        <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 w-40">
                                            <li><a href="javascript:void(0)" onclick="skipOccurrence({{ $occ->id }})"><span class="icon-[tabler--player-skip-forward] size-4"></span> Skip</a></li>
                                            <li><a href="javascript:void(0)" onclick="openSnoozeModal({{ $occ->id }})"><span class="icon-[tabler--clock-pause] size-4"></span> Snooze</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm py-4">No upcoming occurrences scheduled.</p>
                @endif
            </div>
        </div>

        <!-- Completed History -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base">
                    <span class="icon-[tabler--check] size-5 text-success"></span>
                    Completed History
                </h2>
                @if($completedHistory->count() > 0)
                    <div class="divide-y max-h-80 overflow-y-auto">
                        @foreach($completedHistory as $occ)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded bg-success flex items-center justify-center">
                                        <span class="icon-[tabler--check] size-3 text-white"></span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-600">{{ $occ->scheduled_date->format('D, M j') }}</div>
                                        <div class="text-xs text-slate-400">
                                            Completed {{ $occ->completed_at->diffForHumans() }}
                                            @if($occ->completedByUser)
                                                by {{ $occ->completedByUser->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-sm text-slate-500 mt-2">
                        Total completed: <span class="font-medium">{{ $task->completed_occurrences_count }}</span>
                    </div>
                @else
                    <p class="text-slate-500 text-sm py-4">No completed occurrences yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Missed Occurrences Warning -->
    @if($missedOccurrences->count() > 0)
        <div class="card bg-error/5 border border-error/20 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base text-error">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    Missed Occurrences ({{ $missedOccurrences->count() }})
                </h2>
                <div class="divide-y divide-error/10">
                    @foreach($missedOccurrences->take(5) as $occ)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-error">{{ $occ->scheduled_date->format('D, M j, Y') }}</div>
                                <div class="text-xs text-slate-500">{{ $occ->scheduled_date->diffForHumans() }}</div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="completeOccurrence({{ $occ->id }})" class="btn btn-success btn-xs">
                                    <span class="icon-[tabler--check] size-3"></span>
                                    Done
                                </button>
                                <button onclick="skipOccurrence({{ $occ->id }})" class="btn btn-ghost btn-xs">
                                    Skip
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($missedOccurrences->count() > 5)
                    <p class="text-xs text-slate-500 mt-2">And {{ $missedOccurrences->count() - 5 }} more...</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Series Settings Summary -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">
                <span class="icon-[tabler--settings] size-5 text-slate-500"></span>
                Series Settings
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-slate-500">Frequency</div>
                    <div class="font-medium">{{ $task->recurrence_summary }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Generate Mode</div>
                    <div class="font-medium">{{ \App\Models\TodoItem::GENERATE_MODES[$task->generate_mode] ?? $task->generate_mode }}</div>
                </div>
                <div>
                    <div class="text-slate-500">If Missed</div>
                    <div class="font-medium">{{ \App\Models\TodoItem::MISSED_POLICIES[$task->missed_policy] ?? $task->missed_policy }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Ends</div>
                    <div class="font-medium">
                        @if($task->recurrence_end_type === 'never')
                            Never
                        @elseif($task->recurrence_end_type === 'on_date')
                            {{ $task->recurrence_end_date?->format('M j, Y') }}
                        @elseif($task->recurrence_end_type === 'after_occurrences')
                            After {{ $task->recurrence_max_occurrences }} times
                        @endif
                    </div>
                </div>
                @if($task->rotation_type !== 'none')
                    <div>
                        <div class="text-slate-500">Rotation</div>
                        <div class="font-medium">{{ \App\Models\TodoItem::ROTATION_TYPES[$task->rotation_type] ?? $task->rotation_type }}</div>
                    </div>
                @endif
                @if($task->skip_weekends)
                    <div>
                        <div class="text-slate-500">Skip Weekends</div>
                        <div class="font-medium text-primary">Yes</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <!-- Non-Recurring Task: Simple Actions -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($task->status !== 'completed')
                        <button onclick="toggleTask({{ $task->id }})" class="btn btn-success gap-2">
                            <span class="icon-[tabler--check] size-5"></span>
                            Mark Complete
                        </button>
                    @else
                        <button onclick="toggleTask({{ $task->id }})" class="btn btn-outline gap-2">
                            <span class="icon-[tabler--rotate] size-5"></span>
                            Reopen Task
                        </button>
                    @endif
                </div>

                @if($task->status === 'completed' && $task->completed_at)
                    <div class="text-sm text-slate-500">
                        Completed {{ $task->completed_at->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Comments Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">
                <span class="icon-[tabler--message] size-5 text-slate-500"></span>
                Comments ({{ $task->comments->count() }})
            </h2>

            @if($task->comments->count() > 0)
                <div class="space-y-4 mt-4">
                    @foreach($task->comments as $comment)
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-sm font-medium">
                                {{ substr($comment->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-medium text-sm">{{ $comment->user->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">{{ $comment->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Add Comment Form -->
            <form action="{{ route('goals-todo.tasks.comments.store', $task) }}" method="POST" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="content" placeholder="Add a comment..." class="input input-bordered flex-1" required>
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--send] size-4"></span>
                        <span class="hidden sm:inline">Comment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Back Link -->
    <div class="text-center">
        <a href="{{ route('goals-todo.index', ['tab' => 'todos']) }}" class="btn btn-ghost gap-2">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to Tasks
        </a>
    </div>
</div>

<!-- Confirmation Modal Component -->
<x-confirm-modal />

<script>
// Toggle task completion
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
    });
}

// Confirm before toggling series pause/resume
function confirmToggleSeries(isPaused) {
    const taskTitle = @json($task->title);
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
            toggleSeries({{ $task->id }});
        }
    });
}

// Toggle series pause/resume
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

// Skip occurrence
function skipOccurrence(occurrenceId) {
    fetch(`{{ url('/goals-todo/occurrences') }}/${occurrenceId}/skip`, {
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

// Open snooze modal - would need to add the modal or redirect
function openSnoozeModal(occurrenceId) {
    // For now, just snooze until tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(9, 0, 0, 0);

    fetch(`{{ url('/goals-todo/occurrences') }}/${occurrenceId}/snooze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ until: tomorrow.toISOString() })
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
