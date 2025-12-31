@extends('layouts.dashboard')

@section('title', 'People Directory')
@section('page-name', 'People Directory')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">People Directory</li>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">People Directory</h1>
            <p class="text-slate-500">Manage your personal contacts and connections</p>
        </div>
        <a href="{{ route('people.create') }}" class="btn btn-primary gap-2">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Person
        </a>
    </div>

    <!-- Coming Soon Banner -->
    <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <span class="icon-[tabler--cloud-upload] size-5 text-blue-600"></span>
            </div>
            <div>
                <h3 class="font-semibold text-blue-900">Sync Your Contacts</h3>
                <p class="text-sm text-blue-700 mt-1">
                    <span class="font-medium">Coming Soon:</span> Connect your Google Contacts and iPhone Contacts for automatic sync.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body py-4">
            <form method="GET" action="{{ route('people.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search by name, company..."
                            class="input input-bordered w-full pl-10">
                    </div>
                </div>

                <div class="w-48">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
                    <select name="relationship" class="select select-bordered w-full">
                        <option value="">All Relationships</option>
                        @foreach($relationships as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['relationship'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($allTags->count() > 0)
                <div class="w-48">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tag</label>
                    <select name="tag" class="select select-bordered w-full">
                        <option value="">All Tags</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag }}" {{ ($filters['tag'] ?? '') === $tag ? 'selected' : '' }}>{{ $tag }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if(!empty($filters['search']) || !empty($filters['relationship']) || !empty($filters['tag']))
                        <a href="{{ route('people.index') }}" class="btn btn-ghost">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- People Grid -->
    @if($people->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($people as $person)
                <a href="{{ route('people.show', $person) }}"
                   class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow border border-base-200 hover:border-primary/30">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                @if($person->profile_image_url)
                                    <img src="{{ $person->profile_image_url }}" alt="{{ $person->full_name }}"
                                         class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                        <span class="text-white font-semibold text-lg">{{ $person->initials }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-900 truncate">{{ $person->full_name }}</h3>
                                @if($person->nickname)
                                    <p class="text-sm text-slate-500 truncate">"{{ $person->nickname }}"</p>
                                @endif
                                <span class="badge badge-sm badge-{{ $person->relationship_color }} mt-1">
                                    {{ $person->relationship_name }}
                                </span>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="mt-3 space-y-1 text-sm text-slate-600">
                            @if($person->company)
                                <div class="flex items-center gap-2 truncate">
                                    <span class="icon-[tabler--building] size-4 text-slate-400"></span>
                                    <span class="truncate">{{ $person->company }}</span>
                                </div>
                            @endif

                            @if($person->primary_email)
                                <div class="flex items-center gap-2 truncate">
                                    <span class="icon-[tabler--mail] size-4 text-slate-400"></span>
                                    <span class="truncate">{{ $person->primary_email->email }}</span>
                                </div>
                            @endif

                            @if($person->primary_phone)
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--phone] size-4 text-slate-400"></span>
                                    <span>{{ $person->primary_phone->formatted_phone }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Tags -->
                        @if($person->tags && count($person->tags) > 0)
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach(array_slice($person->tags, 0, 3) as $tag)
                                    <span class="badge badge-xs badge-outline">{{ $tag }}</span>
                                @endforeach
                                @if(count($person->tags) > 3)
                                    <span class="badge badge-xs badge-ghost">+{{ count($person->tags) - 3 }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $people->withQueryString()->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--users] size-10 text-slate-400"></span>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">No contacts yet</h3>
                    <p class="text-slate-500 mb-6">Start building your personal directory by adding your first contact.</p>
                    <a href="{{ route('people.create') }}" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Your First Contact
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
