<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold">Your Goals</h2>
            <p class="text-sm text-slate-500">Track progress towards your family objectives</p>
        </div>
        <a href="{{ route('goals-todo.goals.create') }}" class="btn btn-primary btn-sm gap-1">
            <span class="icon-[tabler--plus] size-4"></span>
            New Goal
        </a>
    </div>

    @if($goals->count() === 0)
        <!-- Empty state -->
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--target-arrow] size-8 text-violet-600"></span>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No goals yet</h3>
            <p class="text-slate-500 mb-4">Create goals to track your family's progress towards important objectives.</p>
            <a href="{{ route('goals-todo.goals.create') }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Create First Goal
            </a>
        </div>
    @else
        @php
            $activeGoals = $goals->where('status', 'active');
            $pausedGoals = $goals->where('status', 'paused');
            $completedGoals = $goals->where('status', 'done');
            $archivedGoals = $goals->where('status', 'archived');
        @endphp

        <!-- Goal Stats -->
        <div class="flex gap-4 text-sm">
            <span class="text-slate-500">
                <span class="font-medium text-slate-700">{{ $activeGoals->count() }}</span> active
            </span>
            @if($pausedGoals->count() > 0)
                <span class="text-slate-500">
                    <span class="font-medium text-amber-600">{{ $pausedGoals->count() }}</span> paused
                </span>
            @endif
            <span class="text-slate-500">
                <span class="font-medium text-emerald-600">{{ $completedGoals->count() }}</span> completed
            </span>
        </div>

        <!-- Goals List -->
        <div class="space-y-3">
            @foreach($activeGoals->merge($pausedGoals) as $goal)
                <a href="{{ route('goals-todo.goals.show', $goal) }}" class="block border border-base-200 rounded-xl p-4 hover:border-primary/30 hover:shadow-md transition-all {{ $goal->status === 'paused' ? 'opacity-70 bg-amber-50/30' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Icon & Title -->
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-lg {{ $goal->color_class }} flex items-center justify-center text-white flex-shrink-0">
                                <span class="{{ $goal->icon_class }} size-6"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-slate-900 truncate">{{ $goal->title }}</h3>
                                    @if($goal->status === 'paused')
                                        <span class="badge badge-xs badge-warning">Paused</span>
                                    @endif
                                    @if($goal->is_kid_goal)
                                        <span class="badge badge-xs badge-info">Kid Goal</span>
                                    @endif
                                </div>
                                @if($goal->description)
                                    <p class="text-sm text-slate-500 truncate mt-0.5">{{ $goal->description }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Progress -->
                        @if($goal->target_type !== 'none')
                            <div class="sm:w-48 flex-shrink-0">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-500">{{ $goal->progress_display }}</span>
                                    <span class="font-medium text-slate-700">{{ number_format($goal->progress_percentage, 0) }}%</span>
                                </div>
                                <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                                    <div class="h-full {{ $goal->color_class }} transition-all duration-300" style="width: {{ $goal->progress_percentage }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Meta Info -->
                        <div class="flex items-center gap-4 text-xs text-slate-500 flex-shrink-0">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--checkbox] size-4"></span>
                                {{ $goal->active_tasks_count ?? 0 }}
                            </span>
                            @if($goal->target_date)
                                <span class="flex items-center gap-1 {{ $goal->target_date->isPast() ? 'text-error' : '' }}">
                                    <span class="icon-[tabler--calendar] size-4"></span>
                                    {{ $goal->target_date->format('M j') }}
                                </span>
                            @endif
                            <span class="icon-[tabler--chevron-right] size-5 text-slate-300"></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Completed Goals (Collapsed) -->
        @if($completedGoals->count() > 0)
            <div class="pt-4 border-t border-base-200">
                <button onclick="document.getElementById('completedGoals').classList.toggle('hidden')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                    Completed Goals ({{ $completedGoals->count() }})
                </button>
                <div id="completedGoals" class="hidden mt-4 space-y-2">
                    @foreach($completedGoals as $goal)
                        <a href="{{ route('goals-todo.goals.show', $goal) }}" class="block border border-base-200 rounded-xl p-3 opacity-60 hover:opacity-80 transition-opacity">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $goal->color_class }} flex items-center justify-center text-white flex-shrink-0">
                                    <span class="icon-[tabler--check] size-5"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900 truncate">{{ $goal->title }}</h3>
                                    <span class="text-xs text-emerald-600">Completed {{ $goal->completed_at?->diffForHumans() }}</span>
                                </div>
                                <span class="icon-[tabler--chevron-right] size-5 text-slate-300 flex-shrink-0"></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
