@extends('layouts.dashboard')

@section('title', 'Journal')
@section('page-name', 'Journal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Journal</li>
@endsection

@section('page-title', 'My Journal')
@section('page-description', 'Capture memories, thoughts, and milestones')

@section('content')
<div class="space-y-6">
    <!-- Header with New Entry Button -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">My Journal</h2>
            <p class="text-sm text-slate-500">Capture memories, thoughts, and milestones</p>
        </div>
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                New Entry
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': open }" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-lg shadow-lg z-50 py-1">
                <a href="{{ route('journal.create', ['type' => 'journal']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Journal Entry
                </a>
                <a href="{{ route('journal.create', ['type' => 'memory']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-pink-500"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    Memory
                </a>
                <a href="{{ route('journal.create', ['type' => 'note']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                    Quick Note
                </a>
                <a href="{{ route('journal.create', ['type' => 'milestone']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    Milestone
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                        <div class="text-xs text-slate-500">Total Entries</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['drafts'] }}</div>
                        <div class="text-xs text-slate-500">Drafts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['this_month'] }}</div>
                        <div class="text-xs text-slate-500">This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm" x-data="{ showFilters: false }">
        <div class="card-body p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('journal.index') }}" class="flex-1 min-w-[200px] max-w-md">
                    <div class="join w-full">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               class="input input-bordered join-item flex-1" placeholder="Search entries...">
                        <button type="submit" class="btn btn-primary join-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showFilters = !showFilters" class="btn btn-ghost btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filters
                        @if(count(array_filter($filters)))
                            <span class="badge badge-primary badge-sm">{{ count(array_filter($filters)) }}</span>
                        @endif
                    </button>

                    @if($filters['status'] ?? false)
                        <a href="{{ route('journal.index') }}" class="btn btn-ghost btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            Clear
                        </a>
                    @endif
                </div>
            </div>

            <!-- Expanded Filters -->
            <div x-show="showFilters" x-cloak class="mt-4 pt-4 border-t border-base-200">
                <form method="GET" action="{{ route('journal.index') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">

                    <div class="form-control">
                        <label class="label label-text text-xs">Type</label>
                        <select name="type" class="select select-bordered select-sm">
                            <option value="">All Types</option>
                            @foreach($types as $key => $type)
                                <option value="{{ $key }}" {{ ($filters['type'] ?? '') === $key ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label label-text text-xs">Mood</label>
                        <select name="mood" class="select select-bordered select-sm">
                            <option value="">All Moods</option>
                            @foreach($moods as $key => $mood)
                                <option value="{{ $key }}" {{ ($filters['mood'] ?? '') === $key ? 'selected' : '' }}>
                                    {{ $mood['emoji'] }} {{ $mood['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label label-text text-xs">Status</label>
                        <select name="status" class="select select-bordered select-sm">
                            <option value="">Published</option>
                            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Drafts</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label label-text text-xs">Tag</label>
                        <select name="tag" class="select select-bordered select-sm">
                            <option value="">All Tags</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ ($filters['tag'] ?? '') == $tag->id ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-4 flex justify-end gap-2">
                        <a href="{{ route('journal.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pinned Entries -->
    @if($pinnedEntries->count())
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-slate-600 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" class="text-amber-500"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2z"/></svg>
                Pinned Entries
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($pinnedEntries as $entry)
                    @include('pages.journal.partials.entry-card', ['entry' => $entry, 'isPinned' => true])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Journal Entries -->
    <div class="space-y-4">
        @forelse($entries as $entry)
            @include('pages.journal.partials.entry-card', ['entry' => $entry, 'isPinned' => false])
        @empty
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-12">
                    <div class="text-6xl mb-4">📔</div>
                    <h3 class="text-lg font-semibold text-slate-700">No entries yet</h3>
                    <p class="text-slate-500 mb-4">Start capturing your thoughts and memories</p>
                    <a href="{{ route('journal.create') }}" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Write Your First Entry
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($entries->hasPages())
        <div class="flex justify-center">
            {{ $entries->links() }}
        </div>
    @endif
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
