@extends('layouts.dashboard')

@section('title', 'Pets')
@section('page-name', 'Pets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Pets</li>
@endsection

@section('page-title', 'Family Pets')
@section('page-description', 'Manage your beloved family pets')

@section('content')
<div class="space-y-6">
    <!-- Header with Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-2xl">
                        🐾
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary">{{ $totalPets }}</p>
                        <p class="text-sm text-slate-500">Total Pets</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-2xl">
                        💉
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600">{{ $upcomingVaccinations }}</p>
                        <p class="text-sm text-slate-500">Vaccinations Due Soon</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-error/10 flex items-center justify-center text-2xl">
                        ⚠️
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-error">{{ $overdueVaccinations }}</p>
                        <p class="text-sm text-slate-500">Overdue Vaccinations</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4 flex items-center justify-center">
                <a href="{{ route('pets.create') }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add Pet
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <div class="form-control">
                    <select name="species" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">All Species</option>
                        @foreach($species as $key => $info)
                            <option value="{{ $key }}" {{ request('species') === $key ? 'selected' : '' }}>
                                {{ $info['emoji'] }} {{ $info['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label class="label cursor-pointer gap-2">
                    <input type="checkbox" name="include_passed_away" value="1" class="checkbox checkbox-sm"
                           {{ request('include_passed_away') ? 'checked' : '' }} onchange="this.form.submit()">
                    <span class="label-text text-sm">Include pets who passed away</span>
                </label>

                @if(request()->hasAny(['species', 'include_passed_away']))
                    <a href="{{ route('pets.index') }}" class="btn btn-ghost btn-sm">Clear Filters</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Pets Grid -->
    @if($pets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pets as $pet)
                <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow {{ $pet->is_passed_away ? 'opacity-75' : '' }}">
                    <div class="card-body p-5">
                        <div class="flex items-start gap-4">
                            <!-- Pet Photo -->
                            <div class="flex-shrink-0">
                                @if($pet->photo)
                                    <img src="{{ $pet->photo_url }}" alt="{{ $pet->name }}"
                                         class="w-20 h-20 rounded-xl object-cover {{ $pet->is_passed_away ? 'grayscale' : '' }}">
                                @else
                                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-primary/10 to-primary/20 flex items-center justify-center text-4xl">
                                        {{ $pet->species_emoji }}
                                    </div>
                                @endif
                            </div>

                            <!-- Pet Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-slate-900 truncate">{{ $pet->name }}</h3>
                                    @if($pet->is_passed_away)
                                        <span class="text-sm" title="Passed Away">🌈</span>
                                    @endif
                                </div>

                                <p class="text-sm text-slate-500">
                                    {{ $pet->species_emoji }} {{ $pet->species_label }}
                                    @if($pet->breed)
                                        &bull; {{ $pet->breed }}
                                    @endif
                                </p>

                                @if($pet->age)
                                    <p class="text-sm text-slate-500 mt-1">
                                        <span class="icon-[tabler--calendar] size-4 inline-block align-text-bottom"></span>
                                        {{ $pet->age }} old
                                    </p>
                                @endif

                                <!-- Caregivers -->
                                @if($pet->caregivers->count() > 0)
                                    <div class="flex items-center gap-1 mt-2">
                                        <span class="icon-[tabler--user-heart] size-4 text-slate-400"></span>
                                        <span class="text-xs text-slate-500">
                                            {{ $pet->caregivers->pluck('first_name')->implode(', ') }}
                                        </span>
                                    </div>
                                @endif

                                <!-- Alerts -->
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @if($pet->overdue_vaccinations->count() > 0)
                                        <span class="badge badge-sm badge-error gap-1">
                                            <span class="icon-[tabler--alert-triangle] size-3"></span>
                                            {{ $pet->overdue_vaccinations->count() }} overdue
                                        </span>
                                    @endif
                                    @if($pet->upcoming_vaccinations->count() > 0)
                                        <span class="badge badge-sm badge-warning gap-1">
                                            <span class="icon-[tabler--vaccine] size-3"></span>
                                            {{ $pet->upcoming_vaccinations->count() }} due soon
                                        </span>
                                    @endif
                                    @if($pet->active_medications->count() > 0)
                                        <span class="badge badge-sm badge-info gap-1">
                                            <span class="icon-[tabler--pill] size-3"></span>
                                            {{ $pet->active_medications->count() }} meds
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-200">
                            <a href="{{ route('pets.show', $pet) }}" class="btn btn-sm btn-primary">
                                View Profile
                            </a>
                            <div class="dropdown dropdown-end">
                                <button tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                                <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden z-50 p-2 shadow-xl bg-base-100 rounded-xl w-48 border border-base-200">
                                    <li>
                                        <a href="{{ route('pets.edit', $pet) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                            <span class="icon-[tabler--edit] size-4 text-slate-400"></span>
                                            Edit Profile
                                        </a>
                                    </li>
                                    <li class="my-1 border-t border-base-200"></li>
                                    <li>
                                        <form method="POST" action="{{ route('pets.destroy', $pet) }}"
                                              onsubmit="return confirm('Are you sure you want to remove {{ $pet->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error/10">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                                Remove Pet
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="text-6xl mb-4">🐾</div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">No pets yet</h3>
                <p class="text-slate-500 mb-6 max-w-md mx-auto">
                    Add your furry, feathered, or scaly family members to keep track of their care and health.
                </p>
                <a href="{{ route('pets.create') }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add Your First Pet
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
