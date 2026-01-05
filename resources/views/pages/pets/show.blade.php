@extends('layouts.dashboard')

@section('title', $pet->name)
@section('page-name', 'Pets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('pets.index') }}">Pets</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
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
<div class="space-y-6" x-data="petProfile()">
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
                            <a href="{{ route('pets.edit', $pet) }}" class="btn btn-outline btn-sm gap-1">
                                <span class="icon-[tabler--edit] size-4"></span>
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
                                            <span class="icon-[tabler--star-filled] size-3 text-amber-500"></span>
                                        @else
                                            <span class="icon-[tabler--user] size-3 text-slate-400"></span>
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
                    <span class="icon-[tabler--heart-rate-monitor] size-5 text-rose-500"></span>
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
                    <span class="icon-[tabler--stethoscope] size-5 text-teal-500"></span>
                    Veterinarian
                </h3>

                @if($pet->vet_name || $pet->vet_clinic)
                    <div class="space-y-3">
                        @if($pet->vet_name)
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--user] size-4 text-slate-400"></span>
                                <span class="font-medium">{{ $pet->vet_name }}</span>
                            </div>
                        @endif
                        @if($pet->vet_clinic)
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--building] size-4 text-slate-400"></span>
                                <span>{{ $pet->vet_clinic }}</span>
                            </div>
                        @endif
                        @if($pet->vet_phone)
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--phone] size-4 text-slate-400"></span>
                                <a href="tel:{{ $pet->vet_phone }}" class="text-primary hover:underline">{{ $pet->vet_phone }}</a>
                            </div>
                        @endif
                        @if($pet->vet_address)
                            <div class="flex items-start gap-2">
                                <span class="icon-[tabler--map-pin] size-4 text-slate-400 mt-0.5"></span>
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
                    <span class="icon-[tabler--vaccine] size-5 text-blue-500"></span>
                    Vaccinations
                    @if($overdueVaccinations->count() > 0)
                        <span class="badge badge-error badge-sm">{{ $overdueVaccinations->count() }} overdue</span>
                    @endif
                </h3>
                <button type="button" class="btn btn-sm btn-primary gap-1" @click="showVaccinationModal = true">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Vaccination
                </button>
            </div>

            @if($overdueVaccinations->count() > 0)
                <div class="alert alert-error mb-4">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
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
                    <span class="icon-[tabler--clock] size-5"></span>
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
                                        <form method="POST" action="{{ route('pets.vaccinations.destroy', [$pet, $vaccination]) }}"
                                              onsubmit="return confirm('Delete this vaccination record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
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
                    <span class="icon-[tabler--pill] size-5 text-violet-500"></span>
                    Medications
                    @if($pet->active_medications->count() > 0)
                        <span class="badge badge-info badge-sm">{{ $pet->active_medications->count() }} active</span>
                    @endif
                </h3>
                <button type="button" class="btn btn-sm btn-primary gap-1" @click="showMedicationModal = true">
                    <span class="icon-[tabler--plus] size-4"></span>
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
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('pets.medications.toggle', [$pet, $medication]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-xs" title="{{ $medication->is_active ? 'Mark Inactive' : 'Mark Active' }}">
                                        <span class="icon-[tabler--{{ $medication->is_active ? 'player-pause' : 'player-play' }}] size-4"></span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('pets.medications.destroy', [$pet, $medication]) }}"
                                      onsubmit="return confirm('Delete this medication?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </form>
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
                    <span class="icon-[tabler--notes] size-5 text-slate-500"></span>
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
                    <div class="form-control">
                        <label class="label"><span class="label-text">Date Given *</span></label>
                        <input type="date" name="date_administered" class="input input-bordered" required max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Next Due Date</span></label>
                        <input type="date" name="next_due_date" class="input input-bordered">
                    </div>
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
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Start Date</span></label>
                            <input type="date" name="start_date" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">End Date</span></label>
                            <input type="date" name="end_date" class="input input-bordered">
                        </div>
                    </div>
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
</div>

<script>
function petProfile() {
    return {
        showVaccinationModal: false,
        showMedicationModal: false
    }
}
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
