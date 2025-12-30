@extends('layouts.dashboard')

@section('title', $member->full_name)
@section('page-name', $member->full_name)

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.show', $circle) }}" class="hover:text-violet-600">{{ $circle->name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $member->full_name }}</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Personal Information Card -->
    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Personal Information</h2>
                        <p class="text-xs text-slate-400">Member details and status</p>
                    </div>
                </div>
                <a href="{{ route('family-circle.member.edit', [$circle, $member]) }}" class="btn btn-sm btn-ghost gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    Edit
                </a>
            </div>

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Profile Photo -->
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-500 shadow-lg overflow-hidden">
                        @if($member->profile_image_url)
                            <img src="{{ $member->profile_image_url }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1 mt-3 justify-center">
                        @if($member->is_minor)
                            <span class="badge badge-info badge-sm">Minor</span>
                        @endif
                        @if($member->co_parenting_enabled)
                            <span class="badge badge-warning badge-sm">Co-Parent</span>
                        @endif
                    </div>
                </div>

                <!-- Profile Info Grid -->
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Full Name -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Full Name</p>
                        <p class="font-semibold text-slate-800 flex items-center gap-2">
                            {{ $member->full_name }}
                            @if($member->linked_user_id === auth()->id())
                                <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-500">{{ $member->relationship_name }}</p>
                    </div>

                    <!-- Date of Birth -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Date of Birth</p>
                        <p class="font-semibold text-slate-800">{{ $member->date_of_birth->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $member->age }} years old</p>
                    </div>

                    <!-- Blood Group - Inline Editable -->
                    <div class="p-3 rounded-lg bg-slate-50 group relative">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Blood Group</p>
                        <div id="bloodGroupDisplay" class="flex items-center gap-2">
                            @if($member->medicalInfo && $member->medicalInfo->blood_type)
                                <p class="font-semibold text-slate-800">{{ \App\Models\MemberMedicalInfo::BLOOD_TYPES[$member->medicalInfo->blood_type] ?? $member->medicalInfo->blood_type }}</p>
                            @else
                                <p class="text-sm text-slate-400 italic">Not specified</p>
                            @endif
                            <button onclick="toggleBloodGroupEdit()" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-slate-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                        </div>
                        <form id="bloodGroupEdit" class="hidden" action="{{ route('member.medical.update-field', $member) }}" method="POST">
                            @csrf
                            <input type="hidden" name="field" value="blood_type">
                            <div class="flex gap-1">
                                <select name="value" class="select select-xs select-bordered flex-1" style="min-width: 0;">
                                    <option value="">Select</option>
                                    @foreach(\App\Models\MemberMedicalInfo::BLOOD_TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ ($member->medicalInfo?->blood_type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-xs btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                                <button type="button" onclick="toggleBloodGroupEdit()" class="btn btn-xs btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Immigration Status - Inline Editable -->
                    <div class="p-3 rounded-lg bg-slate-50 group relative">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Immigration Status</p>
                        <div id="immigrationStatusDisplay" class="flex items-center gap-2">
                            @if($member->immigration_status_name)
                                <p class="font-semibold text-slate-800">{{ $member->immigration_status_name }}</p>
                            @else
                                <p class="text-sm text-slate-400 italic">Not specified</p>
                            @endif
                            <button onclick="toggleImmigrationEdit()" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-slate-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                        </div>
                        <form id="immigrationStatusEdit" class="hidden" action="{{ route('family-circle.member.update-field', [$circle, $member]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="field" value="immigration_status">
                            <div class="flex gap-1">
                                <select name="value" class="select select-xs select-bordered flex-1" style="min-width: 0;">
                                    <option value="">Select</option>
                                    @foreach(\App\Models\FamilyMember::IMMIGRATION_STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ ($member->immigration_status ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-xs btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                                <button type="button" onclick="toggleImmigrationEdit()" class="btn btn-xs btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Cards - 4 in a row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Driver's License Card -->
        <a href="{{ route('family-circle.member.drivers-license', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M6 9h4"/><path d="M14 9h4"/></svg>
                    </div>
                    <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Driver's License</h3>

                @if($member->drivers_license)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $member->drivers_license->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Expires</span>
                            @if($member->drivers_license->expiry_date)
                                @if($member->drivers_license->isExpired())
                                    <span class="text-rose-500 font-medium">Expired</span>
                                @else
                                    <span class="font-medium text-slate-700">{{ $member->drivers_license->expiry_date->format('m/d/Y') }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">---</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <!-- Passport Card -->
        <a href="{{ route('family-circle.member.passport', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/></svg>
                    </div>
                    <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Passport</h3>

                @if($member->passport)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $member->passport->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Expires</span>
                            @if($member->passport->expiry_date)
                                @if($member->passport->isExpired())
                                    <span class="text-rose-500 font-medium">Expired</span>
                                @else
                                    <span class="font-medium text-slate-700">{{ $member->passport->expiry_date->format('m/d/Y') }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">---</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <!-- Social Security Card -->
        <a href="{{ route('family-circle.member.social-security', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 12h.01"/></svg>
                    </div>
                    <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Social Security</h3>

                @if($member->social_security)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">SSN</span>
                            <span class="font-mono font-medium text-slate-700">{{ $member->social_security->masked_number ?: 'XXX-XX-' . substr($member->social_security->document_number ?? '0000', -4) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status</span>
                            <span class="text-emerald-600 font-medium">On File</span>
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <!-- Birth Certificate Card -->
        <a href="{{ route('family-circle.member.birth-certificate', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Birth Certificate</h3>

                @if($member->birth_certificate)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $member->birth_certificate->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status</span>
                            <span class="text-emerald-600 font-medium">On File</span>
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>
    </div>

    <!-- Additional Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Health & Medical -->
        <a href="{{ route('family-circle.member.medical-info', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Health & Medical</h3>
                    </div>
                    <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                </div>

                @php
                    $hasData = $member->medications->count() > 0 || $member->medicalConditions->count() > 0 || $member->allergies->count() > 0 || $member->healthcareProviders->count() > 0 || $member->medicalInfo?->blood_type || $member->medicalInfo?->insurance_provider;
                @endphp

                @if($hasData)
                    <div class="space-y-2 text-xs">
                        @if($member->medications->count() > 0)
                            <div>
                                <span class="text-slate-400 block mb-1">Medications</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->medications->take(3) as $med)
                                        <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">{{ $med->name }}</span>
                                    @endforeach
                                    @if($member->medications->count() > 3)
                                        <span class="badge badge-sm bg-slate-100 text-slate-500 border-0">+{{ $member->medications->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($member->medicalConditions->count() > 0)
                            <div>
                                <span class="text-slate-400 block mb-1">Conditions</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->medicalConditions->take(3) as $condition)
                                        <span class="badge badge-sm bg-{{ $condition->status_color }}-100 text-{{ $condition->status_color }}-700 border-0">{{ $condition->name }}</span>
                                    @endforeach
                                    @if($member->medicalConditions->count() > 3)
                                        <span class="badge badge-sm bg-slate-100 text-slate-500 border-0">+{{ $member->medicalConditions->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($member->allergies->count() > 0)
                            <div>
                                <span class="text-slate-400 block mb-1">Allergies</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->allergies->take(3) as $allergy)
                                        <span class="badge badge-sm bg-{{ $allergy->severity_color }}-100 text-{{ $allergy->severity_color }}-700 border-0">{{ $allergy->allergen_name }}</span>
                                    @endforeach
                                    @if($member->allergies->count() > 3)
                                        <span class="badge badge-sm bg-slate-100 text-slate-500 border-0">+{{ $member->allergies->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($member->healthcareProviders->count() > 0)
                            <div>
                                <span class="text-slate-400 block mb-1">Providers</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->healthcareProviders->take(2) as $provider)
                                        <span class="badge badge-sm bg-emerald-100 text-emerald-700 border-0">{{ $provider->name }}</span>
                                    @endforeach
                                    @if($member->healthcareProviders->count() > 2)
                                        <span class="badge badge-sm bg-slate-100 text-slate-500 border-0">+{{ $member->healthcareProviders->count() - 2 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($member->medicalInfo?->blood_type || $member->medicalInfo?->insurance_provider)
                            <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-100">
                                @if($member->medicalInfo?->blood_type)
                                    <span class="text-slate-500"><span class="text-slate-400">Blood:</span> {{ \App\Models\MemberMedicalInfo::BLOOD_TYPES[$member->medicalInfo->blood_type] ?? $member->medicalInfo->blood_type }}</span>
                                @endif
                                @if($member->medicalInfo?->insurance_provider)
                                    <span class="text-slate-500"><span class="text-slate-400">Insurance:</span> {{ $member->medicalInfo->insurance_provider }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-1">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <!-- Emergency Contacts -->
        <a href="{{ route('family-circle.member.emergency-contacts', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Emergency Contacts</h3>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="m9 18 6-6-6-6"/></svg>
                </div>

                @if($member->contacts->where('is_emergency_contact', true)->count() > 0)
                    <div class="space-y-1.5">
                        @foreach($member->contacts->where('is_emergency_contact', true)->take(3) as $contact)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span class="font-medium text-slate-700">{{ $contact->name }}</span>
                                @if($contact->phone)
                                    <span class="text-slate-400 text-[10px]">{{ $contact->phone }}</span>
                                @endif
                            </div>
                        @endforeach
                        @if($member->contacts->where('is_emergency_contact', true)->count() > 3)
                            <span class="text-xs text-slate-400">+{{ $member->contacts->where('is_emergency_contact', true)->count() - 3 }} more</span>
                        @endif
                    </div>
                @else
                    <div class="mt-1">
                        <span class="btn btn-xs btn-primary w-full gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <!-- Activity -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Recent Activity</h3>
                    </div>
                </div>

                @if($member->auditLogs->count() > 0)
                    <div class="space-y-2 max-h-28 overflow-y-auto">
                        @foreach($member->auditLogs->take(4) as $log)
                            <div class="flex items-start gap-2 text-xs">
                                <div class="w-1.5 h-1.5 rounded-full mt-1 {{ $log->action === 'created' ? 'bg-emerald-500' : 'bg-violet-500' }}"></div>
                                <div>
                                    <p class="text-slate-700">{{ $log->action_description }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 text-center py-2">No activity yet</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Inline editing functions
function toggleBloodGroupEdit() {
    const display = document.getElementById('bloodGroupDisplay');
    const edit = document.getElementById('bloodGroupEdit');
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

function toggleImmigrationEdit() {
    const display = document.getElementById('immigrationStatusDisplay');
    const edit = document.getElementById('immigrationStatusEdit');
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}
</script>
@endpush
