<div class="space-y-4">
    <!-- Header with List Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold">To-Do List</h2>
            @if($todoLists->count() > 1)
                <select id="todoListSelector" onchange="window.location.href='{{ route('lists.index', ['tab' => 'todos']) }}&todo_list=' + this.value" class="select select-bordered select-sm">
                    @foreach($todoLists as $list)
                        <option value="{{ $list->id }}" {{ $activeTodoListId == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                    @endforeach
                </select>
            @elseif($todoLists->count() === 1)
                <span class="badge badge-lg badge-ghost">{{ $todoLists->first()->name }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('lists.todos.create') }}" class="btn btn-ghost btn-sm gap-1">
                <span class="icon-[tabler--plus] size-4"></span>
                New List
            </a>
            @if($todoLists->count() > 0)
                <a href="{{ route('lists.todos.items.create', ['todo_list' => $activeTodoListId]) }}" class="btn btn-primary btn-sm gap-1">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Task
                </a>
            @endif
        </div>
    </div>

    @if($todoLists->count() === 0)
        <!-- Empty state - no lists -->
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--checkbox] size-8 text-violet-600"></span>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No lists yet</h3>
            <p class="text-slate-500 mb-4">Create your first to-do list to start organizing family tasks.</p>
            <a href="{{ route('lists.todos.create') }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Create First List
            </a>
        </div>
    @else
        @php
            $activeList = $todoLists->firstWhere('id', $activeTodoListId) ?? $todoLists->first();
            $items = $activeList ? $activeList->items : collect();
            $pendingItems = $items->where('status', 'pending');
            $inProgressItems = $items->where('status', 'in_progress');
            $completedItems = $items->where('status', 'completed');
        @endphp

        @if($items->count() === 0)
            <!-- Empty state - no items -->
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--list-check] size-8 text-violet-600"></span>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No tasks in this list</h3>
                <p class="text-slate-500 mb-4">Add tasks to keep your family organized.</p>
                <a href="{{ route('lists.todos.items.create', ['todo_list' => $activeList->id]) }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add First Task
                </a>
            </div>
        @else
            <!-- Task Stats -->
            <div class="flex gap-4 text-sm">
                <span class="text-slate-500">
                    <span class="font-medium text-slate-700">{{ $pendingItems->count() }}</span> to do
                </span>
                @if($inProgressItems->count() > 0)
                    <span class="text-slate-500">
                        <span class="font-medium text-blue-600">{{ $inProgressItems->count() }}</span> in progress
                    </span>
                @endif
                <span class="text-slate-500">
                    <span class="font-medium text-emerald-600">{{ $completedItems->count() }}</span> completed
                </span>
            </div>

            <!-- Tasks List -->
            <div class="space-y-3">
                @foreach($pendingItems->merge($inProgressItems) as $item)
                    <div class="border border-base-200 rounded-xl p-4 hover:border-primary/30 transition-colors {{ $item->is_overdue ? 'border-l-4 border-l-error' : '' }}">
                        <div class="flex items-start gap-3">
                            <!-- Checkbox -->
                            <button onclick="toggleTodoItem({{ $item->id }})" class="mt-1 w-5 h-5 rounded border-2 {{ $item->status === 'completed' ? 'bg-primary border-primary' : 'border-slate-300 hover:border-primary' }} flex items-center justify-center transition-colors">
                                @if($item->status === 'completed')
                                    <span class="icon-[tabler--check] size-3 text-white"></span>
                                @endif
                            </button>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-medium text-slate-900 {{ $item->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $item->title }}</h4>
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $item->category_color }}">{{ $item->category_name }}</span>
                                    @if($item->priority === 'urgent')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-700">Urgent</span>
                                    @elseif($item->priority === 'high')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">High</span>
                                    @endif
                                    @if($item->is_recurring)
                                        <span class="icon-[tabler--repeat] size-4 text-slate-400" title="Recurring"></span>
                                    @endif
                                </div>

                                @if($item->description)
                                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $item->description }}</p>
                                @endif

                                <div class="flex items-center gap-3 mt-2 text-xs text-slate-500 flex-wrap">
                                    @if($item->assignees->count() > 0)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--users] size-3.5"></span>
                                            @foreach($item->assignees as $assignee)
                                                <span class="bg-base-200 px-1.5 py-0.5 rounded">{{ $assignee->first_name }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                    @if($item->due_date)
                                        <span class="flex items-center gap-1 {{ $item->is_overdue ? 'text-error font-medium' : ($item->is_due_today ? 'text-warning font-medium' : '') }}">
                                            <span class="icon-[tabler--calendar] size-3.5"></span>
                                            @if($item->is_due_today)
                                                Today
                                            @elseif($item->is_overdue)
                                                Overdue
                                            @else
                                                {{ $item->due_date->format('M j') }}
                                            @endif
                                        </span>
                                    @endif
                                    @if($item->comments->count() > 0)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--message] size-3.5"></span>
                                            {{ $item->comments->count() }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="dropdown dropdown-end">
                                <button tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </button>
                                <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 w-40">
                                    <li><a href="{{ route('lists.todos.items.edit', $item) }}"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
                                    <li><a href="javascript:void(0)" onclick="confirmDelete('{{ route('lists.todos.items.destroy', $item) }}', 'Are you sure you want to delete this task?')"><span class="icon-[tabler--trash] size-4 text-error"></span> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Completed Section (Collapsed by default) -->
                @if($completedItems->count() > 0)
                    <div class="pt-4">
                        <button onclick="document.getElementById('completedTasks').classList.toggle('hidden')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
                            <span class="icon-[tabler--chevron-down] size-4" id="completedToggleIcon"></span>
                            Completed ({{ $completedItems->count() }})
                        </button>
                        <div id="completedTasks" class="hidden mt-3 space-y-2">
                            @foreach($completedItems as $item)
                                <div class="border border-base-200 rounded-xl p-3 opacity-60">
                                    <div class="flex items-center gap-3">
                                        <button onclick="toggleTodoItem({{ $item->id }})" class="w-5 h-5 rounded bg-primary border-primary flex items-center justify-center">
                                            <span class="icon-[tabler--check] size-3 text-white"></span>
                                        </button>
                                        <span class="line-through text-slate-400 flex-1">{{ $item->title }}</span>
                                        <button onclick="confirmDelete('{{ route('lists.todos.items.destroy', $item) }}')" class="btn btn-ghost btn-xs btn-square text-error">
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
    @endif
</div>
