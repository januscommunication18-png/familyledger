<div class="space-y-4">
    <!-- Header with Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold">To-Do List</h2>
            <p class="text-sm text-slate-500">All family tasks in one place</p>
        </div>
        <a href="{{ route('goals-todo.tasks.create') }}" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] shrink-0 size-5"></span>
            Add Task
        </a>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2">
        <select onchange="applyFilter('category', this.value)" class="select select-bordered select-sm w-auto">
            <option value="all" {{ request('category') === 'all' || !request('category') ? 'selected' : '' }}>All Categories</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select onchange="applyFilter('priority', this.value)" class="select select-bordered select-sm w-auto">
            <option value="all" {{ request('priority') === 'all' || !request('priority') ? 'selected' : '' }}>All Priorities</option>
            @foreach($priorities as $key => $label)
                <option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select onchange="applyFilter('status', this.value)" class="select select-bordered select-sm w-auto">
            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            <option value="due_today" {{ request('status') === 'due_today' ? 'selected' : '' }}>Due Today</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
        @if($goals->count() > 0)
            <select onchange="applyFilter('goal_id', this.value)" class="select select-bordered select-sm w-auto">
                <option value="all" {{ request('goal_id') === 'all' || !request('goal_id') ? 'selected' : '' }}>All Goals</option>
                @foreach($goals->where('status', 'active') as $goal)
                    <option value="{{ $goal->id }}" {{ request('goal_id') == $goal->id ? 'selected' : '' }}>{{ $goal->title }}</option>
                @endforeach
            </select>
        @endif
        <div class="flex items-center gap-3 ml-2">
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-sm checkbox-primary" onchange="applyFilter('recurring_only', this.checked ? '1' : '')" {{ request('recurring_only') ? 'checked' : '' }}>
                <span class="text-sm">Recurring</span>
            </label>
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-sm checkbox-error" onchange="applyFilter('missed_recurring', this.checked ? '1' : '')" {{ request('missed_recurring') ? 'checked' : '' }}>
                <span class="text-sm text-error">Missed</span>
            </label>
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-sm checkbox-info" onchange="applyFilter('upcoming_this_week', this.checked ? '1' : '')" {{ request('upcoming_this_week') ? 'checked' : '' }}>
                <span class="text-sm text-info">This Week</span>
            </label>
        </div>
    </div>

    @if($tasks->count() === 0)
        <!-- Empty state -->
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--checkbox] size-8 text-violet-600"></span>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No tasks yet</h3>
            <p class="text-slate-500 mb-4">Create tasks to keep your family organized and on track.</p>
            <a href="{{ route('goals-todo.tasks.create') }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Add First Task
            </a>
        </div>
    @else
        <!-- Task Stats -->
        <div class="flex gap-4 text-sm">
            <span class="text-slate-500">
                <span class="font-medium text-slate-700">{{ $openTasks->where('status', 'open')->count() }}</span> to do
            </span>
            @php
                $overdueCount = $openTasks->filter(fn($t) => $t->is_overdue)->count();
            @endphp
            @if($overdueCount > 0)
                <span class="text-slate-500">
                    <span class="font-medium text-error">{{ $overdueCount }}</span> overdue
                </span>
            @endif
            @php
                $recurringCount = $tasks->where('is_recurring', true)->count();
            @endphp
            @if($recurringCount > 0)
                <span class="text-slate-500">
                    <span class="font-medium text-blue-600">{{ $recurringCount }}</span> recurring
                </span>
            @endif
            <span class="text-slate-500">
                <span class="font-medium text-emerald-600">{{ $completedTasks->count() }}</span> completed
            </span>
        </div>

        <!-- Today's Occurrences (if any) -->
        @if($todayOccurrences->count() > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <h3 class="font-medium text-amber-800 mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--calendar-event] size-5"></span>
                    Today's Scheduled Tasks
                </h3>
                <div class="space-y-2">
                    @foreach($todayOccurrences as $occ)
                        <div class="flex items-center justify-between bg-white rounded-lg p-3">
                            <div class="flex items-center gap-3">
                                <button onclick="completeOccurrence({{ $occ->id }})" class="w-5 h-5 rounded border-2 {{ $occ->status === 'completed' ? 'bg-primary border-primary' : 'border-slate-300 hover:border-primary' }} flex items-center justify-center transition-colors">
                                    @if($occ->status === 'completed')
                                        <span class="icon-[tabler--check] size-3 text-white"></span>
                                    @endif
                                </button>
                                <div>
                                    <span class="font-medium text-slate-900 {{ $occ->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $occ->task->title }}</span>
                                    @if($occ->scheduled_time)
                                        <span class="text-sm text-slate-500 ml-2">{{ \Carbon\Carbon::parse($occ->scheduled_time)->format('g:i A') }}</span>
                                    @endif
                                    @if($occ->assignee)
                                        <span class="ml-2 text-xs bg-base-200 px-1.5 py-0.5 rounded">{{ $occ->assignee->first_name }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($occ->status === 'open')
                                <div class="flex items-center gap-1">
                                    <button onclick="skipOccurrence({{ $occ->id }})" class="btn btn-ghost btn-xs" title="Skip">
                                        <span class="icon-[tabler--player-skip-forward] size-4"></span>
                                    </button>
                                    <button onclick="openSnoozeModal({{ $occ->id }})" class="btn btn-ghost btn-xs" title="Snooze">
                                        <span class="icon-[tabler--clock-pause] size-4"></span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tasks List -->
        <div class="space-y-3">
            @foreach($openTasks as $task)
                @include('pages.goals-todo.partials.task-card', ['task' => $task])
            @endforeach

            <!-- Completed Section (Collapsed by default) -->
            @if($completedTasks->count() > 0)
                <div class="pt-4 border-t border-base-200">
                    <button onclick="document.getElementById('completedTasks').classList.toggle('hidden')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
                        <span class="icon-[tabler--chevron-down] size-4"></span>
                        Completed ({{ $completedTasks->count() }})
                    </button>
                    <div id="completedTasks" class="hidden mt-3 space-y-2">
                        @foreach($completedTasks->take(10) as $task)
                            <div class="border border-base-200 rounded-xl p-3 opacity-60">
                                <div class="flex items-center gap-3">
                                    <button onclick="toggleTask({{ $task->id }})" class="w-5 h-5 rounded bg-primary border-primary flex items-center justify-center">
                                        <span class="icon-[tabler--check] size-3 text-white"></span>
                                    </button>
                                    <span class="line-through text-slate-400 flex-1">{{ $task->title }}</span>
                                    <span class="text-xs text-slate-400">{{ $task->completed_at?->diffForHumans() }}</span>
                                    <button onclick="confirmDelete('{{ route('goals-todo.tasks.destroy', $task) }}')" class="btn btn-ghost btn-xs btn-square text-error">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
function applyFilter(key, value) {
    const url = new URL(window.location.href);
    if (value === 'all' || value === '') {
        url.searchParams.delete(key);
    } else {
        url.searchParams.set(key, value);
    }
    url.searchParams.set('tab', 'todos');
    window.location.href = url.toString();
}
</script>
