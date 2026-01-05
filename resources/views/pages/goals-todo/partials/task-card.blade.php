<div class="border border-base-200 rounded-xl p-4 hover:border-primary/30 transition-colors {{ $task->is_overdue ? 'border-l-4 border-l-error' : '' }} {{ $task->is_series_paused ? 'bg-amber-50/50' : '' }}">
    <div class="flex items-start gap-3">
        <!-- Checkbox -->
        <button onclick="toggleTask({{ $task->id }})" class="mt-1 w-5 h-5 rounded border-2 {{ $task->status === 'completed' ? 'bg-primary border-primary' : 'border-slate-300 hover:border-primary' }} flex items-center justify-center transition-colors">
            @if($task->status === 'completed')
                <span class="icon-[tabler--check] size-3 text-white"></span>
            @endif
        </button>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h4 class="font-medium text-slate-900 {{ $task->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</h4>

                <!-- Category Badge -->
                <span class="px-2 py-0.5 text-xs rounded-full {{ $task->category_color }}">
                    <span class="{{ $task->category_icon }} size-3 inline-block align-middle mr-0.5"></span>
                    {{ $task->category_name }}
                </span>

                <!-- Priority Badge -->
                @if($task->priority === 'urgent')
                    <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-700">Urgent</span>
                @elseif($task->priority === 'high')
                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">High</span>
                @endif

                <!-- Recurring Badge -->
                @if($task->is_recurring)
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700" title="{{ $task->recurrence_summary }}">
                        <span class="icon-[tabler--repeat] size-3"></span>
                        {{ $task->recurrence_summary }}
                    </span>
                    @if($task->is_series_paused)
                        <span class="badge badge-xs badge-warning">Paused</span>
                    @endif
                @endif

                <!-- Goal Link -->
                @if($task->goal)
                    <a href="{{ route('goals-todo.goals.show', $task->goal) }}" class="flex items-center gap-1 px-2 py-0.5 text-xs rounded-full {{ $task->goal->color_light_class }}">
                        <span class="{{ $task->goal->icon_class }} size-3"></span>
                        {{ Str::limit($task->goal->title, 15) }}
                    </a>
                @endif
            </div>

            @if($task->description)
                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $task->description }}</p>
            @endif

            <div class="flex items-center gap-3 mt-2 text-xs text-slate-500 flex-wrap">
                <!-- Assignees -->
                @if($task->assignees->count() > 0)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--users] size-3.5"></span>
                        @foreach($task->assignees as $assignee)
                            <span class="bg-base-200 px-1.5 py-0.5 rounded">{{ $assignee->first_name }}</span>
                        @endforeach
                        @if($task->rotation_type !== 'none')
                            <span class="icon-[tabler--arrows-exchange] size-3.5 text-blue-500" title="Rotating assignment"></span>
                        @endif
                    </span>
                @endif

                <!-- Due Date -->
                @if($task->due_date)
                    <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-error font-medium' : ($task->is_due_today ? 'text-warning font-medium' : '') }}">
                        <span class="icon-[tabler--calendar] size-3.5"></span>
                        @if($task->is_due_today)
                            Today
                        @elseif($task->is_overdue)
                            Overdue ({{ $task->due_date->diffForHumans() }})
                        @else
                            {{ $task->due_date->format('M j') }}
                        @endif
                        @if($task->due_time)
                            <span class="text-slate-400">{{ \Carbon\Carbon::parse($task->due_time)->format('g:i A') }}</span>
                        @endif
                    </span>
                @endif

                <!-- Next Occurrence (for recurring) -->
                @if($task->is_recurring && $task->next_occurrence_date && $task->status !== 'completed')
                    <span class="flex items-center gap-1 text-blue-600">
                        <span class="icon-[tabler--calendar-repeat] size-3.5"></span>
                        Next: {{ $task->next_occurrence_date->format('M j') }}
                    </span>
                @endif

                <!-- Occurrence counts -->
                @if($task->is_recurring)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--list-check] size-3.5"></span>
                        {{ $task->completed_occurrences_count }} done
                        @if($task->upcoming_occurrences_count > 0)
                            / {{ $task->upcoming_occurrences_count }} upcoming
                        @endif
                    </span>
                @endif

                <!-- Comments -->
                @if($task->comments->count() > 0)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--message] size-3.5"></span>
                        {{ $task->comments->count() }}
                    </span>
                @endif

                <!-- Reminder indicator -->
                @if($task->send_reminder)
                    <span class="icon-[tabler--bell] size-3.5 text-amber-500" title="Reminder enabled"></span>
                @endif

                <!-- Proof required indicator -->
                @if($task->proof_required)
                    <span class="flex items-center gap-1 text-violet-600" title="Proof required: {{ $task->proof_type ?? 'photo' }}">
                        <span class="icon-[tabler--camera] size-3.5"></span>
                        <span class="sr-only">Proof required</span>
                    </span>
                @endif

                <!-- Missed occurrences warning -->
                @if($task->is_recurring)
                    @php
                        $missedCount = $task->occurrences()->where('status', 'open')->where('scheduled_date', '<', now()->startOfDay())->count();
                    @endphp
                    @if($missedCount > 0)
                        <span class="flex items-center gap-1 px-1.5 py-0.5 bg-error/10 text-error rounded">
                            <span class="icon-[tabler--alert-triangle] size-3.5"></span>
                            {{ $missedCount }} missed
                        </span>
                    @endif
                @endif

                <!-- Escalation enabled indicator -->
                @if($task->escalation_settings && ($task->escalation_settings['enabled'] ?? false))
                    <span class="icon-[tabler--arrow-up] size-3.5 text-warning" title="Escalation enabled"></span>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="dropdown dropdown-end">
            <button tabindex="0" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="5" r="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="12" cy="19" r="2"/>
                </svg>
            </button>
            <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden z-50 p-2 shadow-xl bg-base-100 rounded-xl w-52 border border-base-200">
                @if($task->is_recurring)
                    <li>
                        <a href="javascript:void(0)" onclick="openEditScopeModal({{ $task->id }}, '{{ route('goals-todo.tasks.edit', $task) }}')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                            <span class="icon-[tabler--edit] shrink-0 size-4 text-slate-400"></span>
                            Edit Task
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('goals-todo.tasks.edit', $task) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                            <span class="icon-[tabler--edit] shrink-0 size-4 text-slate-400"></span>
                            Edit Task
                        </a>
                    </li>
                @endif
                @if($task->is_recurring)
                    <li>
                        <a href="javascript:void(0)" onclick="toggleSeries({{ $task->id }})" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                            @if($task->is_series_paused)
                                <span class="icon-[tabler--player-play] shrink-0 size-4 text-emerald-600"></span>
                                <span>Resume Series</span>
                            @else
                                <span class="icon-[tabler--player-pause] shrink-0 size-4 text-amber-600"></span>
                                <span>Pause Series</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="showOccurrences({{ $task->id }})" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                            <span class="icon-[tabler--calendar-stats] shrink-0 size-4 text-slate-400"></span>
                            View Occurrences
                        </a>
                    </li>
                @endif
                <li class="my-1 border-t border-base-200"></li>
                <li>
                    <a href="javascript:void(0)" onclick="confirmDelete('{{ route('goals-todo.tasks.destroy', $task) }}', 'Are you sure you want to delete this task{{ $task->is_recurring ? ' and all its occurrences' : '' }}?')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error/10">
                        <span class="icon-[tabler--trash] shrink-0 size-4"></span>
                        Delete
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
