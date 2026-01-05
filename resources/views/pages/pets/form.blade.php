@extends('layouts.dashboard')

@section('title', $pet ? 'Edit ' . $pet->name : 'Add Pet')
@section('page-name', 'Pets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('pets.index') }}">Pets</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $pet ? 'Edit' : 'Add Pet' }}</li>
@endsection

@section('page-title', $pet ? 'Edit ' . $pet->name : 'Add New Pet')
@section('page-description', $pet ? 'Update your pet\'s information' : 'Add a new family member')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ $pet ? route('pets.update', $pet) : route('pets.store') }}" enctype="multipart/form-data" x-data="petForm()">
                @csrf
                @if($pet)
                    @method('PUT')
                @endif

                @if($errors->any())
                    <div class="alert alert-error mb-6">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <div>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                        <span class="text-xl">🐾</span> Pet Identity
                    </h3>

                    <!-- Photo Upload -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Photo</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                @if($pet && $pet->photo)
                                    <img src="{{ $pet->photo_url }}" alt="{{ $pet->name }}" class="w-24 h-24 rounded-xl object-cover" id="photo-preview">
                                @else
                                    <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-primary/10 to-primary/20 flex items-center justify-center text-4xl" id="photo-placeholder">
                                        <span x-text="speciesEmoji">🐾</span>
                                    </div>
                                    <img src="" alt="Preview" class="w-24 h-24 rounded-xl object-cover hidden" id="photo-preview">
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="photo" accept="image/*" class="file-input file-input-bordered w-full max-w-xs"
                                       onchange="previewPhoto(this)">
                                <p class="text-xs text-slate-500 mt-1">Max 5MB. JPG, PNG or GIF.</p>
                                @if($pet && $pet->photo)
                                    <label class="label cursor-pointer justify-start gap-2 mt-2">
                                        <input type="checkbox" name="remove_photo" value="1" class="checkbox checkbox-sm checkbox-error">
                                        <span class="label-text text-error text-sm">Remove current photo</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Pet Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $pet?->name) }}"
                                   class="input input-bordered" placeholder="e.g., Max, Bella, Whiskers" required>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Species <span class="text-error">*</span></span>
                            </label>
                            <select name="species" class="select select-bordered" required x-model="selectedSpecies">
                                <option value="">Select species...</option>
                                @foreach($species as $key => $info)
                                    <option value="{{ $key }}" {{ old('species', $pet?->species) === $key ? 'selected' : '' }}>
                                        {{ $info['emoji'] }} {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Breed</span>
                            </label>
                            <input type="text" name="breed" value="{{ old('breed', $pet?->breed) }}"
                                   class="input input-bordered" placeholder="e.g., Golden Retriever, Persian">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Gender</span>
                            </label>
                            <select name="gender" class="select select-bordered">
                                <option value="">Select gender...</option>
                                @foreach($genders as $key => $label)
                                    <option value="{{ $key }}" {{ old('gender', $pet?->gender) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Date of Birth</span>
                            </label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $pet?->date_of_birth?->format('Y-m-d')) }}"
                                   class="input input-bordered" max="{{ date('Y-m-d') }}">
                            <label class="label">
                                <span class="label-text-alt text-slate-500">Leave blank if unknown</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Approximate Age</span>
                            </label>
                            <input type="text" name="approx_age" value="{{ old('approx_age', $pet?->approx_age) }}"
                                   class="input input-bordered" placeholder="e.g., ~3 years, Adult">
                            <label class="label">
                                <span class="label-text-alt text-slate-500">If exact DOB is unknown</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Microchip ID</span>
                            </label>
                            <input type="text" name="microchip_id" value="{{ old('microchip_id', $pet?->microchip_id) }}"
                                   class="input input-bordered" placeholder="15-digit ID">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Status <span class="text-error">*</span></span>
                            </label>
                            <select name="status" class="select select-bordered" x-model="petStatus" required>
                                @foreach($statuses as $key => $info)
                                    <option value="{{ $key }}" {{ old('status', $pet?->status ?? 'active') === $key ? 'selected' : '' }}>
                                        {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div x-show="petStatus === 'passed_away'" x-cloak class="form-control">
                        <label class="label">
                            <span class="label-text">Passed Away Date</span>
                        </label>
                        <input type="date" name="passed_away_date" value="{{ old('passed_away_date', $pet?->passed_away_date?->format('Y-m-d')) }}"
                               class="input input-bordered w-auto" max="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Caregivers -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                        <span class="icon-[tabler--users-group] size-5"></span> Caregivers
                    </h3>

                    @php
                        // Check if current user has a linked family member (by linked_user_id or email match)
                        $userFamilyMember = $familyMembers->firstWhere('linked_user_id', $currentUser->id)
                            ?? $familyMembers->firstWhere('email', $currentUser->email);

                        // Get the primary caregiver ID, convert to 'me' if it matches current user's family member
                        $existingPrimaryId = $pet?->primaryCaregiver->first()?->id;
                        if ($existingPrimaryId && $userFamilyMember && $existingPrimaryId == $userFamilyMember->id) {
                            $selectedPrimaryId = 'me';
                        } else {
                            $selectedPrimaryId = old('primary_caregiver', $existingPrimaryId);
                        }

                        // Get secondary caregiver IDs, convert user's family member ID to 'me'
                        $existingSecondaryIds = $pet?->secondaryCaregivers->pluck('id')->toArray() ?? [];
                        $selectedSecondaryIds = [];
                        foreach ($existingSecondaryIds as $secId) {
                            if ($userFamilyMember && $secId == $userFamilyMember->id) {
                                $selectedSecondaryIds[] = 'me';
                            } else {
                                $selectedSecondaryIds[] = (string)$secId;
                            }
                        }
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control" x-data="primaryCaregiverSelect()">
                            <label class="label">
                                <span class="label-text">Primary Caregiver</span>
                            </label>

                            <!-- Selected Display -->
                            <div x-show="selectedId" class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                    <span class="icon-[tabler--star-filled] size-3 text-amber-500"></span>
                                    <span x-text="getSelectedName()"></span>
                                    <button type="button" @click="clearSelection()" class="hover:text-error">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </div>

                            <!-- Dropdown -->
                            <div class="relative">
                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                           class="input input-bordered w-full pr-8"
                                           :placeholder="selectedId ? 'Change caregiver...' : 'Search caregivers...'">
                                    <button type="button" @click="open = !open" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Dropdown Menu -->
                                <div x-show="open" x-cloak @click.away="open = false"
                                     class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="member in filteredMembers" :key="member.id">
                                        <div @click="selectMember(member.id)"
                                             class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-base-200"
                                             :class="{ 'bg-primary/10': selectedId === member.id }">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/20 to-primary/30 flex items-center justify-center text-xs font-bold text-primary"
                                                 x-text="member.initial"></div>
                                            <div class="flex-1">
                                                <span class="font-medium text-sm" x-text="member.name"></span>
                                                <span x-show="member.isMe" class="text-xs text-primary ml-1">(Me)</span>
                                            </div>
                                            <span x-show="selectedId === member.id" class="icon-[tabler--check] size-4 text-primary"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredMembers.length === 0" class="px-3 py-2 text-sm text-slate-500">
                                        No members found
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="primary_caregiver" :value="selectedId || ''">

                            @if(!$userFamilyMember && !$familyMembers->count())
                                <label class="label">
                                    <span class="label-text-alt text-amber-600">
                                        <span class="icon-[tabler--info-circle] size-3 inline-block align-text-bottom"></span>
                                        Add yourself to Family Circle to appear here
                                    </span>
                                </label>
                            @endif
                        </div>

                        <div class="form-control" x-data="caregiverSelect()">
                            <label class="label">
                                <span class="label-text">Secondary Caregivers</span>
                            </label>

                            <!-- Selected Tags -->
                            <div class="flex flex-wrap gap-2 mb-2" x-show="selected.length > 0">
                                <template x-for="id in selected" :key="id">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary rounded-full text-sm">
                                        <span x-text="getMemberName(id)"></span>
                                        <button type="button" @click="toggleMember(id)" class="hover:text-error">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>

                            <!-- Dropdown -->
                            <div class="relative">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                               class="input input-bordered w-full pr-8"
                                               placeholder="Search caregivers...">
                                        <button type="button" @click="open = !open" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="button" @click="selectAll()" class="btn btn-outline btn-sm" title="Select All">All</button>
                                    <button type="button" @click="clearAll()" class="btn btn-ghost btn-sm" x-show="selected.length > 0" title="Clear All">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Dropdown Menu -->
                                <div x-show="open" x-cloak @click.away="open = false"
                                     class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="member in filteredMembers" :key="member.id">
                                        <div @click="toggleMember(member.id)"
                                             class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-base-200"
                                             :class="{ 'bg-primary/5': isSelected(member.id) }">
                                            <input type="checkbox" :checked="isSelected(member.id)" class="checkbox checkbox-sm checkbox-primary" @click.stop>
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/20 to-primary/30 flex items-center justify-center text-xs font-bold text-primary"
                                                 x-text="member.initial"></div>
                                            <div class="flex-1">
                                                <span class="font-medium text-sm" x-text="member.name"></span>
                                                <span x-show="member.isMe" class="text-xs text-primary ml-1">(Me)</span>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filteredMembers.length === 0" class="px-3 py-2 text-sm text-slate-500">
                                        No members found
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <template x-for="id in selected" :key="'input-' + id">
                                <input type="hidden" name="secondary_caregivers[]" :value="id">
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Health Snapshot -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                        <span class="icon-[tabler--heart-rate-monitor] size-5"></span> Health Snapshot
                    </h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Allergies</span>
                        </label>
                        <textarea name="allergies" rows="2" class="textarea textarea-bordered"
                                  placeholder="e.g., Chicken allergy, sensitive skin">{{ old('allergies', $pet?->allergies) }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Health Conditions</span>
                        </label>
                        <textarea name="conditions" rows="2" class="textarea textarea-bordered"
                                  placeholder="e.g., Diabetes, arthritis, heart murmur">{{ old('conditions', $pet?->conditions) }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Last Vet Visit</span>
                        </label>
                        <input type="date" name="last_vet_visit" value="{{ old('last_vet_visit', $pet?->last_vet_visit?->format('Y-m-d')) }}"
                               class="input input-bordered w-auto" max="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Vet Information -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                        <span class="icon-[tabler--stethoscope] size-5"></span> Veterinarian
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Vet Name</span>
                            </label>
                            <input type="text" name="vet_name" value="{{ old('vet_name', $pet?->vet_name) }}"
                                   class="input input-bordered" placeholder="Dr. Smith">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Vet Phone</span>
                            </label>
                            <input type="tel" name="vet_phone" value="{{ old('vet_phone', $pet?->vet_phone) }}"
                                   class="input input-bordered" placeholder="(555) 123-4567">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Clinic Name</span>
                        </label>
                        <input type="text" name="vet_clinic" value="{{ old('vet_clinic', $pet?->vet_clinic) }}"
                               class="input input-bordered" placeholder="Happy Paws Animal Hospital">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Clinic Address</span>
                        </label>
                        <input type="text" name="vet_address" value="{{ old('vet_address', $pet?->vet_address) }}"
                               class="input input-bordered" placeholder="123 Main St, City, State">
                    </div>
                </div>

                <!-- Privacy & Notes -->
                <div class="space-y-4 mt-8">
                    <h3 class="font-semibold text-slate-900 border-b pb-2 flex items-center gap-2">
                        <span class="icon-[tabler--lock] size-5"></span> Privacy & Notes
                    </h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Visibility <span class="text-error">*</span></span>
                        </label>
                        <select name="visibility" class="select select-bordered" required>
                            @foreach($visibility as $key => $label)
                                <option value="{{ $key }}" {{ old('visibility', $pet?->visibility ?? 'family') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Notes</span>
                        </label>
                        <textarea name="notes" rows="3" class="textarea textarea-bordered"
                                  placeholder="Any additional notes about your pet...">{{ old('notes', $pet?->notes) }}</textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
                    <a href="{{ $pet ? route('pets.show', $pet) : route('pets.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--check] size-5"></span>
                        {{ $pet ? 'Update Pet' : 'Add Pet' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function petForm() {
    return {
        selectedSpecies: '{{ old('species', $pet?->species ?? '') }}',
        petStatus: '{{ old('status', $pet?->status ?? 'active') }}',
        speciesEmojis: {
            @foreach($species as $key => $info)
            '{{ $key }}': '{{ $info['emoji'] }}',
            @endforeach
        },
        get speciesEmoji() {
            return this.speciesEmojis[this.selectedSpecies] || '🐾';
        }
    }
}

function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    const placeholder = document.getElementById('photo-placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function primaryCaregiverSelect() {
    return {
        open: false,
        search: '',
        selectedId: '{{ $selectedPrimaryId ?? '' }}',
        members: [
            // Always show current user at top
            {
                id: 'me',
                name: '{{ addslashes($currentUser->name) }}',
                initial: '{{ strtoupper(substr($currentUser->name, 0, 1)) }}',
                isMe: true
            },
            @foreach($familyMembers as $member)
                @if(!$userFamilyMember || $member->id != $userFamilyMember->id)
            {
                id: '{{ $member->id }}',
                name: '{{ addslashes($member->first_name) }} {{ addslashes($member->last_name ?? '') }}',
                initial: '{{ strtoupper(substr($member->first_name, 0, 1)) }}',
                isMe: false
            },
                @endif
            @endforeach
        ],

        get filteredMembers() {
            if (!this.search) return this.members;
            const searchLower = this.search.toLowerCase();
            return this.members.filter(m => m.name.toLowerCase().includes(searchLower));
        },

        selectMember(id) {
            this.selectedId = id;
            this.search = '';
            this.open = false;
        },

        clearSelection() {
            this.selectedId = null;
        },

        getSelectedName() {
            const member = this.members.find(m => String(m.id) === String(this.selectedId));
            return member ? (member.isMe ? member.name + ' (Me)' : member.name) : '';
        }
    }
}

function caregiverSelect() {
    return {
        open: false,
        search: '',
        selected: {!! json_encode($selectedSecondaryIds) !!},
        members: [
            // Always show current user at top
            {
                id: 'me',
                name: '{{ addslashes($currentUser->name) }}',
                initial: '{{ strtoupper(substr($currentUser->name, 0, 1)) }}',
                isMe: true
            },
            @foreach($familyMembers as $member)
                @if(!$userFamilyMember || $member->id != $userFamilyMember->id)
            {
                id: '{{ $member->id }}',
                name: '{{ addslashes($member->first_name) }} {{ addslashes($member->last_name ?? '') }}',
                initial: '{{ strtoupper(substr($member->first_name, 0, 1)) }}',
                isMe: false
            },
                @endif
            @endforeach
        ],

        get filteredMembers() {
            if (!this.search) return this.members;
            const searchLower = this.search.toLowerCase();
            return this.members.filter(m => m.name.toLowerCase().includes(searchLower));
        },

        toggleMember(id) {
            const strId = String(id);
            const index = this.selected.findIndex(s => String(s) === strId);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(strId);
            }
        },

        isSelected(id) {
            return this.selected.some(s => String(s) === String(id));
        },

        selectAll() {
            this.selected = this.members.map(m => String(m.id));
        },

        clearAll() {
            this.selected = [];
        },

        getMemberName(id) {
            const member = this.members.find(m => String(m.id) === String(id));
            return member ? (member.isMe ? member.name + ' (Me)' : member.name) : '';
        }
    }
}
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
