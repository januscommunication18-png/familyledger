@extends('layouts.dashboard')

@section('title', $pet->name)
@section('page-name', 'Pets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('pets.index') }}">Pets</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $pet->name }}</li>
@endsection

@section('page-title')
    <div class="flex items-center gap-3">
        {{ $pet->species_emoji }} {{ $pet->name }}
        @if($pet->is_passed_away)
            <span class="text-sm" title="Passed Away {{ $pet->passed_away_date?->format('M j, Y') }}">🌈</span>
        @endif
    </div>
@endsection
@section('page-description', $pet->species_label . ($pet->breed ? ' - ' . $pet->breed : ''))

@section('content')
<div class="space-y-6" x-data="petProfile()" x-cloak>
    <!-- Pet Profile Card -->
    <div class="card bg-base-100 shadow-sm {{ $pet->is_passed_away ? 'border-l-4 border-l-slate-400' : '' }}">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Photo -->
                <div class="flex-shrink-0">
                    @if($pet->photo)
                        <img src="{{ $pet->photo_url }}" alt="{{ $pet->name }}"
                             class="w-40 h-40 rounded-xl object-cover {{ $pet->is_passed_away ? 'grayscale' : '' }}">
                    @else
                        <div class="w-40 h-40 rounded-xl bg-gradient-to-br from-primary/10 to-primary/20 flex items-center justify-center text-7xl">
                            {{ $pet->species_emoji }}
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">{{ $pet->name }}</h2>
                            <p class="text-slate-500">
                                {{ $pet->species_emoji }} {{ $pet->species_label }}
                                @if($pet->breed) &bull; {{ $pet->breed }} @endif
                                @if($pet->gender) &bull; {{ ucfirst($pet->gender) }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('pets.index') }}" class="btn btn-ghost btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                Back
                            </a>
                            <a href="{{ route('pets.edit', $pet) }}" class="btn btn-outline btn-primary btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                Edit
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        @if($pet->age)
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Age</p>
                                <p class="font-medium">{{ $pet->age }}</p>
                            </div>
                        @endif
                        @if($pet->microchip_id)
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Microchip</p>
                                <p class="font-medium font-mono text-sm">{{ $pet->microchip_id }}</p>
                            </div>
                        @endif
                        @if($pet->last_vet_visit)
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Last Vet Visit</p>
                                <p class="font-medium">{{ $pet->last_vet_visit->format('M j, Y') }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide">Status</p>
                            <span class="badge badge-{{ $pet->status_color }}">{{ $pet->status_label }}</span>
                        </div>
                    </div>

                    <!-- Caregivers -->
                    @if($pet->caregivers->count() > 0)
                        <div class="mt-4 pt-4 border-t border-base-200">
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">Caregivers</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($pet->caregivers as $caregiver)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-base-200 rounded-full text-sm">
                                        @if($caregiver->pivot->role === 'primary')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-amber-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        @endif
                                        {{ $caregiver->first_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Health Info -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-500"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    Health Snapshot
                </h3>

                <div class="space-y-4">
                    @if($pet->allergies)
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Allergies</p>
                            <p class="text-sm bg-rose-50 text-rose-700 px-3 py-2 rounded-lg">{{ $pet->allergies }}</p>
                        </div>
                    @endif

                    @if($pet->conditions)
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Health Conditions</p>
                            <p class="text-sm bg-amber-50 text-amber-700 px-3 py-2 rounded-lg">{{ $pet->conditions }}</p>
                        </div>
                    @endif

                    @if(!$pet->allergies && !$pet->conditions)
                        <p class="text-sm text-slate-500 italic">No health conditions recorded</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vet Info -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-teal-500"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/></svg>
                    Veterinarian
                </h3>

                @if($pet->vet_name || $pet->vet_clinic)
                    <div class="space-y-3">
                        @if($pet->vet_name)
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span class="font-medium">{{ $pet->vet_name }}</span>
                            </div>
                        @endif
                        @if($pet->vet_clinic)
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                                <span>{{ $pet->vet_clinic }}</span>
                            </div>
                        @endif
                        @if($pet->vet_phone)
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <a href="tel:{{ $pet->vet_phone }}" class="text-primary hover:underline">{{ $pet->vet_phone }}</a>
                            </div>
                        @endif
                        @if($pet->vet_address)
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mt-0.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span class="text-sm text-slate-600">{{ $pet->vet_address }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500 italic">No veterinarian information recorded</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Vaccinations -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="m9 3 6 6"/><path d="m15 3-6 6"/><path d="m21 12-6 6"/><path d="m21 18-6-6"/><path d="M3 12h18"/></svg>
                    Vaccinations
                    @if($overdueVaccinations->count() > 0)
                        <span class="badge badge-error badge-sm">{{ $overdueVaccinations->count() }} overdue</span>
                    @endif
                </h3>
                <button type="button" class="btn btn-sm btn-primary gap-2" @click="showVaccinationModal = true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Vaccination
                </button>
            </div>

            @if($overdueVaccinations->count() > 0)
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                    <div>
                        <p class="font-medium">Overdue Vaccinations</p>
                        <ul class="text-sm mt-1">
                            @foreach($overdueVaccinations as $vax)
                                <li>{{ $vax->name }} was due {{ $vax->next_due_date->diffForHumans() }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($upcomingVaccinations->count() > 0)
                <div class="alert alert-warning mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div>
                        <p class="font-medium">Due Soon</p>
                        <ul class="text-sm mt-1">
                            @foreach($upcomingVaccinations as $vax)
                                <li>{{ $vax->name }} due {{ $vax->next_due_date->format('M j, Y') }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($pet->vaccinations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Vaccination</th>
                                <th>Date Given</th>
                                <th>Next Due</th>
                                <th>By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pet->vaccinations as $vaccination)
                                <tr class="{{ $vaccination->is_overdue ? 'bg-error/5' : ($vaccination->is_due_soon ? 'bg-warning/5' : '') }}">
                                    <td class="font-medium">{{ $vaccination->name }}</td>
                                    <td>{{ $vaccination->date_administered->format('M j, Y') }}</td>
                                    <td>
                                        @if($vaccination->next_due_date)
                                            <span class="{{ $vaccination->is_overdue ? 'text-error font-medium' : ($vaccination->is_due_soon ? 'text-warning font-medium' : '') }}">
                                                {{ $vaccination->next_due_date->format('M j, Y') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500">{{ $vaccination->administered_by ?? '-' }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50"
                                            @click="openDeleteModal('{{ route('pets.vaccinations.destroy', [$pet, $vaccination]) }}', 'vaccination', '{{ $vaccination->name }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-500 italic text-center py-4">No vaccination records yet</p>
            @endif
        </div>
    </div>

    <!-- Medications -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                    Medications
                    @if($pet->active_medications->count() > 0)
                        <span class="badge badge-info badge-sm">{{ $pet->active_medications->count() }} active</span>
                    @endif
                </h3>
                <button type="button" class="btn btn-sm btn-primary gap-2" @click="showMedicationModal = true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Medication
                </button>
            </div>

            @if($pet->medications->count() > 0)
                <div class="space-y-3">
                    @foreach($pet->medications as $medication)
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $medication->is_active ? 'bg-violet-50 border border-violet-200' : 'bg-base-200 opacity-60' }}">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium {{ $medication->is_active ? 'text-violet-900' : 'text-slate-600' }}">{{ $medication->name }}</span>
                                    @if(!$medication->is_active)
                                        <span class="badge badge-xs">Inactive</span>
                                    @endif
                                </div>
                                <div class="text-sm text-slate-500 mt-1">
                                    @if($medication->dosage)
                                        {{ $medication->dosage }}
                                    @endif
                                    @if($medication->frequency)
                                        &bull; {{ $medication->frequency_label }}
                                    @endif
                                </div>
                                @if($medication->instructions)
                                    <p class="text-xs text-slate-500 mt-1">{{ $medication->instructions }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <form method="POST" action="{{ route('pets.medications.toggle', [$pet, $medication]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-xs text-slate-600 hover:bg-slate-100" title="{{ $medication->is_active ? 'Mark Inactive' : 'Mark Active' }}">
                                        @if($medication->is_active)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <button type="button"
                                    class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50"
                                    @click="openDeleteModal('{{ route('pets.medications.destroy', [$pet, $medication]) }}', 'medication', '{{ $medication->name }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 italic text-center py-4">No medications recorded</p>
            @endif
        </div>
    </div>

    <!-- Notes -->
    @if($pet->notes)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="M8 2v4"/><path d="M12 2v4"/><path d="M16 2v4"/><rect width="16" height="18" x="4" y="4" rx="2"/><path d="M8 10h6"/><path d="M8 14h8"/><path d="M8 18h5"/></svg>
                    Notes
                </h3>
                <p class="text-slate-600 whitespace-pre-line">{{ $pet->notes }}</p>
            </div>
        </div>
    @endif

    <!-- Add Vaccination Modal -->
    <div x-show="showVaccinationModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showVaccinationModal = false">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
            <h3 class="text-lg font-semibold mb-4">Add Vaccination Record</h3>
            <form method="POST" action="{{ route('pets.vaccinations.store', $pet) }}">
                @csrf
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Vaccination Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" required placeholder="e.g., Rabies, DHPP">
                    </div>
                    <x-date-select
                        name="date_administered"
                        label="Date Given"
                        :required="true"
                    />
                    <x-date-select
                        name="next_due_date"
                        label="Next Due Date"
                    />
                    <div class="form-control">
                        <label class="label"><span class="label-text">Administered By</span></label>
                        <input type="text" name="administered_by" class="input input-bordered" placeholder="Vet name or clinic">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Notes</span></label>
                        <textarea name="notes" class="textarea textarea-bordered" rows="2"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="btn btn-ghost" @click="showVaccinationModal = false">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Medication Modal -->
    <div x-show="showMedicationModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showMedicationModal = false">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
            <h3 class="text-lg font-semibold mb-4">Add Medication</h3>
            <form method="POST" action="{{ route('pets.medications.store', $pet) }}">
                @csrf
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Medication Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" required placeholder="e.g., Heartgard, Insulin">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Dosage</span></label>
                        <input type="text" name="dosage" class="input input-bordered" placeholder="e.g., 10mg, 1 tablet">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Frequency</span></label>
                        <select name="frequency" class="select select-bordered">
                            <option value="">Select frequency...</option>
                            @foreach($medicationFrequencies as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-date-select
                        name="start_date"
                        label="Start Date"
                    />
                    <x-date-select
                        name="end_date"
                        label="End Date"
                    />
                    <div class="form-control">
                        <label class="label"><span class="label-text">Instructions</span></label>
                        <textarea name="instructions" class="textarea textarea-bordered" rows="2" placeholder="e.g., Give with food"></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" checked>
                            <span class="label-text">Currently taking this medication</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="btn btn-ghost" @click="showMedicationModal = false">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showDeleteModal = false">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full p-6" @click.stop>
            <div class="text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Delete <span x-text="deleteType"></span>?</h3>
                <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete "<span x-text="deleteName" class="font-medium"></span>"? This action cannot be undone.</p>
            </div>
            <form :action="deleteUrl" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" class="flex-1 btn btn-ghost" @click="showDeleteModal = false">Cancel</button>
                    <button type="submit" class="flex-1 btn btn-error text-white">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function petProfile() {
    return {
        showVaccinationModal: false,
        showMedicationModal: false,
        showDeleteModal: false,
        deleteUrl: '',
        deleteType: '',
        deleteName: '',
        openDeleteModal(url, type, name) {
            this.deleteUrl = url;
            this.deleteType = type;
            this.deleteName = name;
            this.showDeleteModal = true;
        }
    }
}
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
