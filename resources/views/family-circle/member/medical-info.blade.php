@extends('layouts.dashboard')

@section('title', 'Health & Medical')
@section('page-name', 'Health & Medical')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="hover:text-violet-600">{{ $member->full_name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Health & Medical</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Health & Medical</h1>
                <p class="text-slate-500">{{ $member->full_name }}</p>
            </div>
        </div>
    </div>

    <!-- General Medical Information -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">General Information</h2>
                        <p class="text-xs text-slate-400">Basic medical details</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 hover:border-rose-300 transition-colors">
                <!-- Display Mode -->
                <div id="bloodTypeDisplay" class="flex items-center justify-between p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                        <div>
                            <span class="text-xs text-slate-400">Blood Type</span>
                            @if($member->medicalInfo?->blood_type)
                                <p class="font-medium text-slate-800">{{ $bloodTypes[$member->medicalInfo->blood_type] ?? $member->medicalInfo->blood_type }}</p>
                            @else
                                <p class="text-slate-400 text-sm">Not recorded</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" onclick="toggleBloodTypeForm()" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                </div>
                <!-- Edit Mode -->
                <div id="bloodTypeForm" class="hidden p-3 bg-rose-50 border-t border-rose-200">
                    <form action="{{ route('member.medical-info.update', $member) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Blood Type</label>
                            <select name="blood_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                <option value="">Select blood type</option>
                                @foreach($bloodTypes as $key => $label)
                                    <option value="{{ $key }}" {{ ($member->medicalInfo?->blood_type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" onclick="toggleBloodTypeForm()" class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Medications Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Current Medications</h2>
                        <p class="text-xs text-slate-400">Track medications and dosages</p>
                    </div>
                </div>
                <button type="button" onclick="toggleMedicationForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Medication Form (Hidden by default) -->
            <div id="medicationForm" class="hidden mb-4 p-4 bg-violet-50 rounded-xl border border-violet-200">
                <form action="{{ route('member.medication.store', $member) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Medication Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="e.g., Lisinopril">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Dosage</label>
                                <input type="text" name="dosage" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="e.g., 10mg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Frequency</label>
                                <select name="frequency" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">Select</option>
                                    @foreach($medicationFrequencies as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <input type="text" name="notes" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="Additional notes...">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleMedicationForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Medications List -->
            @if($member->medications->count() > 0)
                <div class="space-y-2">
                    @foreach($member->medications as $medication)
                        <div class="rounded-lg border border-slate-200 hover:border-violet-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="medicationDisplay{{ $medication->id }}" class="flex items-center justify-between p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                                    <div>
                                        <span class="font-medium text-slate-800">{{ $medication->name }}</span>
                                        @if($medication->dosage)
                                            <span class="text-slate-500"> - {{ $medication->dosage }}</span>
                                        @endif
                                        @if($medication->frequency_name)
                                            <span class="text-xs text-slate-400 ml-2">({{ $medication->frequency_name }})</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleMedicationEdit({{ $medication->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.medication.destroy', [$member, $medication]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Medication?', 'Are you sure you want to remove {{ $medication->name }}? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="medicationEdit{{ $medication->id }}" class="hidden p-3 bg-violet-50 border-t border-violet-200">
                                <form action="{{ route('member.medication.update', [$member, $medication]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Medication Name <span class="text-rose-500">*</span></label>
                                            <input type="text" name="name" value="{{ $medication->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Dosage</label>
                                                <input type="text" name="dosage" value="{{ $medication->dosage }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Frequency</label>
                                                <select name="frequency" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                                    <option value="">Select</option>
                                                    @foreach($medicationFrequencies as $key => $label)
                                                        <option value="{{ $key }}" {{ $medication->frequency == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleMedicationEdit({{ $medication->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No medications recorded</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Medical Conditions Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-sky-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-600"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Medical Conditions</h2>
                        <p class="text-xs text-slate-400">Ongoing health conditions</p>
                    </div>
                </div>
                <button type="button" onclick="toggleConditionForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Condition Form (Hidden by default) -->
            <div id="conditionForm" class="hidden mb-4 p-4 bg-sky-50 rounded-xl border border-sky-200">
                <form action="{{ route('member.condition.store', $member) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Condition Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="e.g., Diabetes, Asthma">
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <select name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                                    <option value="">Select</option>
                                    @foreach($conditionStatuses as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-date-select
                                name="diagnosed_date"
                                label="Diagnosed Date"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <input type="text" name="notes" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="Additional notes...">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleConditionForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Conditions List -->
            @if($member->medicalConditions->count() > 0)
                <div class="space-y-2">
                    @foreach($member->medicalConditions as $condition)
                        <div class="rounded-lg border border-slate-200 hover:border-sky-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="conditionDisplay{{ $condition->id }}" class="flex items-center justify-between p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-{{ $condition->status_color }}-500"></div>
                                    <div>
                                        <span class="font-medium text-slate-800">{{ $condition->name }}</span>
                                        @if($condition->status_name)
                                            <span class="badge badge-sm bg-{{ $condition->status_color }}-100 text-{{ $condition->status_color }}-700 border-0 ml-2">{{ $condition->status_name }}</span>
                                        @endif
                                        @if($condition->diagnosed_date)
                                            <span class="text-xs text-slate-400 ml-2">Diagnosed: {{ $condition->diagnosed_date->format('M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleConditionEdit({{ $condition->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.condition.destroy', [$member, $condition]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Condition?', 'Are you sure you want to remove {{ $condition->name }}? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="conditionEdit{{ $condition->id }}" class="hidden p-3 bg-sky-50 border-t border-sky-200">
                                <form action="{{ route('member.condition.update', [$member, $condition]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Condition Name <span class="text-rose-500">*</span></label>
                                            <input type="text" name="name" value="{{ $condition->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                                                    <option value="">Select</option>
                                                    @foreach($conditionStatuses as $key => $label)
                                                        <option value="{{ $key }}" {{ $condition->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <x-date-select
                                                name="diagnosed_date"
                                                label="Diagnosed Date"
                                                :value="$condition->diagnosed_date"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleConditionEdit({{ $condition->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No conditions recorded</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Vaccinations Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-teal-600"><path d="m18 2 4 4"/><path d="m17 7 3-3"/><path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/><path d="m9 11 4 4"/><path d="m5 19-3 3"/><path d="m14 4 6 6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Vaccinations</h2>
                        <p class="text-xs text-slate-400">Track immunization records</p>
                    </div>
                </div>
                <button type="button" onclick="toggleVaccinationForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Vaccination Form (Hidden by default) -->
            <div id="vaccinationForm" class="hidden mb-4 p-4 bg-teal-50 rounded-xl border border-teal-200">
                <form action="{{ route('member.vaccination.store', $member) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vaccine <span class="text-rose-500">*</span></label>
                            <select name="vaccine_type" id="vaccine_type_select" required onchange="toggleCustomVaccine(this)" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                <option value="">Select vaccine</option>
                                @foreach($vaccineTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="customVaccineField" class="hidden">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Custom Vaccine Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="custom_vaccine_name" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20" placeholder="Enter vaccine name">
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <x-date-select
                                name="vaccination_date"
                                label="Date of Vaccination"
                            />
                            <x-date-select
                                name="next_vaccination_date"
                                label="Next Vaccination"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Administered By</label>
                            <input type="text" name="administered_by" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20" placeholder="Doctor or clinic name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20" placeholder="Any additional notes..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload Document</label>
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-teal-400 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mb-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                    <p class="text-xs text-slate-500"><span class="font-semibold text-teal-600">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-slate-400">PDF, JPG, or PNG (max 10MB)</p>
                                </div>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleVaccinationForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Vaccinations List -->
            @if($member->vaccinations && $member->vaccinations->count() > 0)
                <div class="space-y-2">
                    @foreach($member->vaccinations as $vaccination)
                        <div class="rounded-lg border border-slate-200 hover:border-teal-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="vaccinationDisplay{{ $vaccination->id }}" class="flex items-start justify-between p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full mt-1.5 {{ $vaccination->is_due ? 'bg-rose-500' : ($vaccination->is_coming_soon ? 'bg-amber-500' : 'bg-teal-500') }}"></div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-slate-800">{{ $vaccination->vaccine_name }}</span>
                                            @if($vaccination->is_due)
                                                <span class="badge badge-sm bg-rose-100 text-rose-700 border-0">Due</span>
                                            @elseif($vaccination->is_coming_soon)
                                                <span class="badge badge-sm bg-amber-100 text-amber-700 border-0">Due Soon</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-slate-500 mt-1 space-y-0.5">
                                            @if($vaccination->vaccination_date)
                                                <p>Received: {{ $vaccination->vaccination_date->format('M d, Y') }}</p>
                                            @endif
                                            @if($vaccination->next_vaccination_date)
                                                <p>Next: {{ $vaccination->next_vaccination_date->format('M d, Y') }}</p>
                                            @endif
                                            @if($vaccination->administered_by)
                                                <p>By: {{ $vaccination->administered_by }}</p>
                                            @endif
                                        </div>
                                        @if($vaccination->document_path)
                                            <a href="{{ route('member.vaccination.download', [$member, $vaccination]) }}" class="inline-flex items-center gap-1 text-sm text-teal-600 hover:text-teal-700 mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                                {{ $vaccination->document_name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleVaccinationEdit({{ $vaccination->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.vaccination.destroy', [$member, $vaccination]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Vaccination?', 'Are you sure you want to remove this vaccination record? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="vaccinationEdit{{ $vaccination->id }}" class="hidden p-3 bg-teal-50 border-t border-teal-200">
                                <form action="{{ route('member.vaccination.update', [$member, $vaccination]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Vaccine <span class="text-rose-500">*</span></label>
                                            <select name="vaccine_type" required onchange="toggleCustomVaccineEdit(this, {{ $vaccination->id }})" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                                @foreach($vaccineTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ $vaccination->vaccine_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div id="customVaccineFieldEdit{{ $vaccination->id }}" class="{{ $vaccination->vaccine_type === 'other' ? '' : 'hidden' }}">
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Custom Vaccine Name</label>
                                            <input type="text" name="custom_vaccine_name" value="{{ $vaccination->custom_vaccine_name }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <x-date-select
                                                name="vaccination_date"
                                                label="Date of Vaccination"
                                                :value="$vaccination->vaccination_date"
                                            />
                                            <x-date-select
                                                name="next_vaccination_date"
                                                label="Next Vaccination"
                                                :value="$vaccination->next_vaccination_date"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Administered By</label>
                                            <input type="text" name="administered_by" value="{{ $vaccination->administered_by }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">{{ $vaccination->notes }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload New Document</label>
                                            <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-teal-400 transition-colors">
                                                <div class="flex flex-col items-center justify-center py-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mb-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                                    <p class="text-xs text-slate-500"><span class="font-semibold text-teal-600">Click to upload</span></p>
                                                </div>
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                            </label>
                                            @if($vaccination->document_path)
                                                <p class="text-xs text-slate-500 mt-1">Current: {{ $vaccination->document_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleVaccinationEdit({{ $vaccination->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No vaccinations recorded</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Insurance Information - Commented out for now
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Insurance Information</h2>
                        <p class="text-xs text-slate-400">Health insurance details</p>
                    </div>
                </div>
                <button type="button" onclick="toggleInsuranceForm()" id="insuranceEditBtn" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    Edit
                </button>
            </div>

            <div id="insuranceDisplay">
                @if($member->medicalInfo?->insurance_provider || $member->medicalInfo?->insurance_policy_number || $member->medicalInfo?->insurance_group_number)
                    <div class="space-y-3">
                        @if($member->medicalInfo?->insurance_provider)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200">
                                <div>
                                    <span class="text-xs text-slate-400">Provider</span>
                                    <p class="font-medium text-slate-800">{{ $member->medicalInfo->insurance_provider }}</p>
                                </div>
                            </div>
                        @endif
                        @if($member->medicalInfo?->insurance_policy_number)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200">
                                <div>
                                    <span class="text-xs text-slate-400">Policy Number</span>
                                    <p class="font-medium text-slate-800">{{ $member->medicalInfo->insurance_policy_number }}</p>
                                </div>
                            </div>
                        @endif
                        @if($member->medicalInfo?->insurance_group_number)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200">
                                <div>
                                    <span class="text-xs text-slate-400">Group Number</span>
                                    <p class="font-medium text-slate-800">{{ $member->medicalInfo->insurance_group_number }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6 text-slate-400">
                        <p class="text-sm">No insurance information recorded</p>
                    </div>
                @endif
            </div>

            <div id="insuranceForm" class="hidden">
                <form action="{{ route('member.medical-info.update', $member) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Insurance Provider</label>
                            <input type="text" name="insurance_provider" value="{{ old('insurance_provider', $member->medicalInfo?->insurance_provider) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="e.g., Blue Cross">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Policy Number</label>
                            <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number', $member->medicalInfo?->insurance_policy_number) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Policy #">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Group Number</label>
                            <input type="text" name="insurance_group_number" value="{{ old('insurance_group_number', $member->medicalInfo?->insurance_group_number) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Group #">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" onclick="toggleInsuranceForm()" class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    --}}

    <!-- Allergies Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.87L13.637 3.59a1.914 1.914 0 0 0-3.274 0z"/><path d="M12 17h.01"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Medical Allergies</h2>
                        <p class="text-xs text-slate-400">Track allergies and reactions</p>
                    </div>
                </div>
                <button type="button" onclick="toggleAllergyForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Allergy Form (Hidden by default) -->
            <div id="allergyForm" class="hidden mb-4 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <form action="{{ route('member.allergy.store', $member) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Allergy Type <span class="text-rose-500">*</span></label>
                            <select name="allergy_type" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="">Select type</option>
                                @foreach($allergyTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Allergen Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="allergen_name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="e.g., Penicillin, Peanuts">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Severity <span class="text-rose-500">*</span></label>
                            <select name="severity" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="">Select severity</option>
                                @foreach($severities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($symptoms as $key => $label)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="symptoms[]" value="{{ $key }}" class="checkbox checkbox-sm checkbox-warning">
                                        <span class="ml-1 text-xs text-slate-600">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Instructions</label>
                            <textarea name="emergency_instructions" rows="2" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="e.g., EpiPen required"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleAllergyForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Allergies List -->
            @if($member->allergies->count() > 0)
                <div class="space-y-2">
                    @foreach($member->allergies as $allergy)
                        <div class="rounded-lg border border-slate-200 hover:border-amber-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="allergyDisplay{{ $allergy->id }}" class="flex items-start justify-between p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full mt-1.5 bg-{{ $allergy->severity_color }}-500"></div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-slate-800">{{ $allergy->allergen_name }}</span>
                                            <span class="badge badge-sm bg-{{ $allergy->severity_color }}-100 text-{{ $allergy->severity_color }}-700 border-0">{{ $allergy->severity_name }}</span>
                                            <span class="text-xs text-slate-400">({{ $allergy->allergy_type_name }})</span>
                                        </div>
                                        @if($allergy->symptoms && count($allergy->symptoms) > 0)
                                            <p class="text-sm text-slate-500 mt-1">{{ implode(', ', $allergy->symptom_names) }}</p>
                                        @endif
                                        @if($allergy->emergency_instructions)
                                            <p class="text-sm text-rose-600 mt-1">{{ $allergy->emergency_instructions }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleAllergyEdit({{ $allergy->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.allergy.destroy', [$member, $allergy]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Allergy?', 'Are you sure you want to remove {{ $allergy->allergen_name }}? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="allergyEdit{{ $allergy->id }}" class="hidden p-3 bg-amber-50 border-t border-amber-200">
                                <form action="{{ route('member.allergy.update', [$member, $allergy]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Allergy Type <span class="text-rose-500">*</span></label>
                                                <select name="allergy_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                                    @foreach($allergyTypes as $key => $label)
                                                        <option value="{{ $key }}" {{ $allergy->allergy_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Allergen Name <span class="text-rose-500">*</span></label>
                                                <input type="text" name="allergen_name" value="{{ $allergy->allergen_name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Severity <span class="text-rose-500">*</span></label>
                                            <select name="severity" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                                @foreach($severities as $key => $label)
                                                    <option value="{{ $key }}" {{ $allergy->severity == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms</label>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($symptoms as $key => $label)
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" name="symptoms[]" value="{{ $key }}" {{ in_array($key, $allergy->symptoms ?? []) ? 'checked' : '' }} class="checkbox checkbox-sm checkbox-warning">
                                                        <span class="ml-1 text-xs text-slate-600">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Instructions</label>
                                            <textarea name="emergency_instructions" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">{{ $allergy->emergency_instructions }}</textarea>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleAllergyEdit({{ $allergy->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No allergies recorded</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Healthcare Providers Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Healthcare Providers</h2>
                        <p class="text-xs text-slate-400">Doctors and care providers</p>
                    </div>
                </div>
                <button type="button" onclick="toggleProviderForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Provider Form (Hidden by default) -->
            <div id="providerForm" class="hidden mb-4 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                <form action="{{ route('member.provider.store', $member) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Provider Type <span class="text-rose-500">*</span></label>
                            <select name="provider_type" id="provider_type_select" required data-select='{
                                "placeholder": "Select type...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "hasSearch": true,
                                "searchPlaceholder": "Search...",
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">Select type</option>
                                @foreach($providerTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Doctor Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="Dr. John Smith">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Specialty</label>
                            <select name="specialty" id="specialty_select" data-select='{
                                "placeholder": "Select specialty...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "hasSearch": true,
                                "searchPlaceholder": "Search specialties...",
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">Select specialty</option>
                                @foreach($specialties as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Clinic / Hospital</label>
                            <input type="text" name="clinic_name" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="ABC Medical Center">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="(555) 123-4567">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="doctor@clinic.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                            <textarea name="address" rows="2" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="123 Medical Drive, Suite 100"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Preferred Contact</label>
                            <select name="preferred_contact" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Select method</option>
                                @foreach($contactMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_primary" value="1" class="checkbox checkbox-sm checkbox-success">
                                <span class="ml-2 text-sm text-slate-700">Mark as primary provider</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleProviderForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Providers List -->
            @if($member->healthcareProviders->count() > 0)
                <div class="space-y-2">
                    @foreach($member->healthcareProviders as $provider)
                        <div class="rounded-lg border border-slate-200 hover:border-emerald-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="providerDisplay{{ $provider->id }}" class="flex items-start justify-between p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-slate-800">{{ $provider->name }}</span>
                                            <span class="badge badge-sm bg-emerald-100 text-emerald-700 border-0">{{ $provider->provider_type_name }}</span>
                                            @if($provider->is_primary)
                                                <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">Primary</span>
                                            @endif
                                        </div>
                                        @if($provider->specialty_name || $provider->clinic_name)
                                            <p class="text-sm text-slate-500 mt-1">
                                                @if($provider->specialty_name){{ $provider->specialty_name }}@endif
                                                @if($provider->specialty_name && $provider->clinic_name) - @endif
                                                @if($provider->clinic_name){{ $provider->clinic_name }}@endif
                                            </p>
                                        @endif
                                        @if($provider->phone)
                                            <p class="text-sm text-slate-600 mt-1">{{ $provider->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleProviderEdit({{ $provider->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.provider.destroy', [$member, $provider]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Provider?', 'Are you sure you want to remove {{ $provider->name }}? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="providerEdit{{ $provider->id }}" class="hidden p-3 bg-emerald-50 border-t border-emerald-200">
                                <form action="{{ route('member.provider.update', [$member, $provider]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Provider Type <span class="text-rose-500">*</span></label>
                                                <select name="provider_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                    @foreach($providerTypes as $key => $label)
                                                        <option value="{{ $key }}" {{ $provider->provider_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Doctor Name <span class="text-rose-500">*</span></label>
                                                <input type="text" name="name" value="{{ $provider->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Specialty</label>
                                                <select name="specialty" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                    <option value="">Select</option>
                                                    @foreach($specialties as $key => $label)
                                                        <option value="{{ $key }}" {{ $provider->specialty == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Clinic / Hospital</label>
                                                <input type="text" name="clinic_name" value="{{ $provider->clinic_name }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                                                <input type="tel" name="phone" value="{{ $provider->phone }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                                <input type="email" name="email" value="{{ $provider->email }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_primary" value="1" {{ $provider->is_primary ? 'checked' : '' }} class="checkbox checkbox-sm checkbox-success">
                                                <span class="ml-2 text-sm text-slate-700">Mark as primary provider</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleProviderEdit({{ $provider->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No healthcare providers recorded</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm transform transition-all">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 text-center mb-2" id="confirmModalTitle">Remove Item?</h3>
                <p class="text-sm text-slate-500 text-center mb-6" id="confirmModalMessage">Are you sure you want to remove this item? This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="button" onclick="executeConfirmedAction()" class="flex-1 btn btn-error text-white">Remove</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleMedicationForm() {
    const form = document.getElementById('medicationForm');
    form.classList.toggle('hidden');
}

function toggleMedicationEdit(id) {
    const display = document.getElementById('medicationDisplay' + id);
    const edit = document.getElementById('medicationEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function toggleConditionForm() {
    const form = document.getElementById('conditionForm');
    form.classList.toggle('hidden');
}

function toggleConditionEdit(id) {
    const display = document.getElementById('conditionDisplay' + id);
    const edit = document.getElementById('conditionEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function toggleAllergyForm() {
    const form = document.getElementById('allergyForm');
    form.classList.toggle('hidden');
}

function toggleAllergyEdit(id) {
    const display = document.getElementById('allergyDisplay' + id);
    const edit = document.getElementById('allergyEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function toggleProviderForm() {
    const form = document.getElementById('providerForm');
    form.classList.toggle('hidden');

    // Initialize advanced selects when form is shown
    if (!form.classList.contains('hidden')) {
        initAdvancedSelects();
    }
}

function toggleProviderEdit(id) {
    const display = document.getElementById('providerDisplay' + id);
    const edit = document.getElementById('providerEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function initAdvancedSelects() {
    // Reinitialize HSSelect for the advanced selects
    if (typeof HSSelect !== 'undefined') {
        setTimeout(() => {
            HSSelect.autoInit();
        }, 100);
    }
}

// toggleInsuranceForm - Commented out for now
// function toggleInsuranceForm() {
//     const form = document.getElementById('insuranceForm');
//     const display = document.getElementById('insuranceDisplay');
//     const btn = document.getElementById('insuranceEditBtn');
//     form.classList.toggle('hidden');
//     display.classList.toggle('hidden');
//     if (form.classList.contains('hidden')) {
//         btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg> Edit';
//     } else {
//         btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> Cancel';
//     }
// }

function toggleBloodTypeForm() {
    const form = document.getElementById('bloodTypeForm');
    const display = document.getElementById('bloodTypeDisplay');
    display.classList.toggle('hidden');
    form.classList.toggle('hidden');
}

function toggleVaccinationForm() {
    const form = document.getElementById('vaccinationForm');
    form.classList.toggle('hidden');
}

function toggleVaccinationEdit(id) {
    const display = document.getElementById('vaccinationDisplay' + id);
    const edit = document.getElementById('vaccinationEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function toggleCustomVaccine(select) {
    const customField = document.getElementById('customVaccineField');
    if (select.value === 'other') {
        customField.classList.remove('hidden');
    } else {
        customField.classList.add('hidden');
    }
}

function toggleCustomVaccineEdit(select, id) {
    const customField = document.getElementById('customVaccineFieldEdit' + id);
    if (select.value === 'other') {
        customField.classList.remove('hidden');
    } else {
        customField.classList.add('hidden');
    }
}

// Confirmation Modal Functions
let pendingDeleteForm = null;

function confirmDelete(form, title, message) {
    pendingDeleteForm = form;
    document.getElementById('confirmModalTitle').textContent = title || 'Remove Item?';
    document.getElementById('confirmModalMessage').textContent = message || 'Are you sure you want to remove this item? This action cannot be undone.';
    document.getElementById('confirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingDeleteForm = null;
}

function executeConfirmedAction() {
    if (pendingDeleteForm) {
        pendingDeleteForm.submit();
    }
    closeConfirmModal();
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('confirmModal').classList.contains('hidden')) {
        closeConfirmModal();
    }
});

</script>
@endpush
