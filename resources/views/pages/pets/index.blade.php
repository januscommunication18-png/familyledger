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
    @if($totalPets > 0)
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
    @php
        $baseParams = request('species') ? ['species' => request('species')] : [];
        $toggleOnUrl = route('pets.index', array_merge($baseParams, ['include_passed_away' => 1]));
        $toggleOffUrl = route('pets.index', $baseParams);
    @endphp
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <!-- Filter Label -->
                <div class="flex items-center gap-2 text-slate-500">
                    <span class="icon-[tabler--filter] size-5"></span>
                    <span class="text-sm font-medium">Filter by:</span>
                </div>

                <!-- Species Filter Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('pets.index', request('include_passed_away') ? ['include_passed_away' => 1] : []) }}"
                       class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 {{ !request('species') ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-base-200 text-slate-600 hover:bg-base-300' }}">
                        All
                    </a>
                    @foreach($species as $key => $info)
                        <a href="{{ route('pets.index', array_merge(['species' => $key], request('include_passed_away') ? ['include_passed_away' => 1] : [])) }}"
                           class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 flex items-center gap-1.5 {{ request('species') === $key ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-base-200 text-slate-600 hover:bg-base-300' }}">
                            <span>{{ $info['emoji'] }}</span>
                            <span>{{ $info['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-8 bg-base-300"></div>

                <!-- Include Passed Away Toggle -->
                <label class="inline-flex items-center gap-3 cursor-pointer group flex-shrink-0 whitespace-nowrap">
                    <input type="checkbox" class="toggle toggle-sm toggle-primary"
                           {{ request('include_passed_away') ? 'checked' : '' }}
                           onchange="window.location.href = this.checked ? '{{ $toggleOnUrl }}' : '{{ $toggleOffUrl }}'">
                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors inline-flex items-center gap-1.5">
                        <span>🌈</span>
                        <span>Include passed away</span>
                    </span>
                </label>

                <!-- Clear Filters -->
                @if(request()->hasAny(['species', 'include_passed_away']))
                    <a href="{{ route('pets.index') }}" class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-slate-500 hover:text-error hover:bg-error/10 rounded-lg transition-all duration-200">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

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
                            <div class="relative" id="petDropdown{{ $pet->id }}">
                                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="toggleDropdown('petDropdown{{ $pet->id }}', event)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                                <ul class="dropdown-content hidden absolute right-0 top-full mt-1 z-50 p-2 shadow-xl bg-base-100 rounded-xl w-48 border border-base-200">
                                    <li>
                                        <a href="{{ route('pets.edit', $pet) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                            <span class="icon-[tabler--edit] size-4 text-slate-400"></span>
                                            Edit Profile
                                        </a>
                                    </li>
                                    <li class="my-1 border-t border-base-200"></li>
                                    <li>
                                        <a href="javascript:void(0)" onclick="confirmDeletePet('{{ route('pets.destroy', $pet) }}', '{{ addslashes($pet->name) }}')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error/10">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                            Remove Pet
                                        </a>
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

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Remove Pet?</h3>
            </div>
            <p id="deleteConfirmMessage" class="text-base-content/70 mb-6">Are you sure you want to remove this pet?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideDeleteModal()" class="btn btn-ghost">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

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

function confirmDeletePet(url, petName) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteConfirmMessage').textContent = 'Are you sure you want to remove ' + petName + '? This action cannot be undone.';
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>
@endsection
