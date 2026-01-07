@extends('layouts.dashboard')

@section('page-name', $child->full_name)

@php
    // Helper to check if co-parent can view a category
    $canView = function($category) use ($isCoparent, $collaborator) {
        if (!($isCoparent ?? false)) return true; // Owner can see everything
        if (!$collaborator) return true;

        // Get permissions from the pivot
        $coparentChild = \App\Models\CoparentChild::where('collaborator_id', $collaborator->id)
            ->where('family_member_id', request()->route('child')->id)
            ->first();

        if (!$coparentChild) return false;
        return $coparentChild->canView($category);
    };
@endphp

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('coparenting.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to Co-parenting
        </a>

        @if($isCoparent ?? false)
        <div class="alert bg-pink-50 border border-pink-200 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-pink-600"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            <span class="text-sm text-pink-700">You're viewing as a co-parent. Some information may be hidden based on your permissions.</span>
        </div>
        @endif

        <div class="flex items-center gap-4">
            @if($child->profile_image_url)
                <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-pink-100">
            @else
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center ring-4 ring-pink-100">
                    <span class="text-3xl font-bold text-white">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $child->full_name }}</h1>
                <p class="text-slate-500">{{ $child->age }} years old</p>
                @if($child->date_of_birth)
                <p class="text-sm text-slate-400">Born {{ $child->date_of_birth->format('F j, Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Personal Information Card (basic_info permission) --}}
    @if($canView('basic_info'))
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Personal Information</h2>
                    <p class="text-xs text-slate-400">Member details and status</p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6">
                {{-- Profile Photo --}}
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-pink-400 to-rose-500 shadow-lg overflow-hidden">
                        @if($child->profile_image_url)
                            <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ strtoupper(substr($child->first_name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1 mt-3 justify-center">
                        @if($child->is_minor)
                            <span class="badge badge-info badge-sm">Minor</span>
                        @endif
                        @if($child->co_parenting_enabled)
                            <span class="badge badge-warning badge-sm">Co-Parent</span>
                        @endif
                    </div>
                </div>

                {{-- Profile Info Grid --}}
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Full Name</p>
                        <p class="font-semibold text-slate-800">{{ $child->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ $child->relationship_name ?? 'Child' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Date of Birth</p>
                        <p class="font-semibold text-slate-800">{{ $child->date_of_birth?->format('M d, Y') ?? 'Not set' }}</p>
                        <p class="text-xs text-slate-500">{{ $child->age }} years old</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Blood Group</p>
                        <p class="font-semibold text-slate-800">{{ $child->medicalInfo?->blood_type ?? 'Not specified' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Immigration Status</p>
                        <p class="font-semibold text-slate-800">{{ $child->immigration_status_name ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Document Cards - 4 in a row (documents permission) --}}
    @if($canView('documents'))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Driver's License Card --}}
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M6 9h4"/><path d="M14 9h4"/></svg>
                    </div>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Driver's License</h3>
                @if($child->drivers_license)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $child->drivers_license->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Expires</span>
                            @if($child->drivers_license->expiry_date)
                                <span class="font-medium text-slate-700">{{ $child->drivers_license->expiry_date->format('m/d/Y') }}</span>
                            @else
                                <span class="text-slate-400">---</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 mt-2">No data</p>
                @endif
            </div>
        </div>

        {{-- Passport Card --}}
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/></svg>
                    </div>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Passport</h3>
                @if($child->passport)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $child->passport->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Expires</span>
                            @if($child->passport->expiry_date)
                                <span class="font-medium text-slate-700">{{ $child->passport->expiry_date->format('m/d/Y') }}</span>
                            @else
                                <span class="text-slate-400">---</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 mt-2">No data</p>
                @endif
            </div>
        </div>

        {{-- Social Security Card --}}
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 12h.01"/></svg>
                    </div>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Social Security</h3>
                @if($child->social_security)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">SSN</span>
                            <span class="font-mono font-medium text-slate-700">XXX-XX-{{ substr($child->social_security->document_number ?? '0000', -4) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status</span>
                            <span class="text-emerald-600 font-medium">On File</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 mt-2">No data</p>
                @endif
            </div>
        </div>

        {{-- Birth Certificate Card --}}
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Birth Certificate</h3>
                @if($child->birth_certificate)
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Number</span>
                            <span class="font-mono font-medium text-slate-700">{{ $child->birth_certificate->document_number ?: '---' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status</span>
                            <span class="text-emerald-600 font-medium">On File</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 mt-2">No data</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- School Info (school_info permission) --}}
            @if($canView('school_info') && $child->schoolInfo)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800">School Information</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @if($child->schoolInfo->school_name)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">School</p>
                            <p class="font-semibold text-slate-800">{{ $child->schoolInfo->school_name }}</p>
                        </div>
                        @endif
                        @if($child->schoolInfo->grade)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Grade</p>
                            <p class="font-semibold text-slate-800">{{ $child->schoolInfo->grade }}</p>
                        </div>
                        @endif
                        @if($child->schoolInfo->teacher_name)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Teacher</p>
                            <p class="font-semibold text-slate-800">{{ $child->schoolInfo->teacher_name }}</p>
                        </div>
                        @endif
                        @if($child->schoolInfo->school_phone)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">School Phone</p>
                            <p class="font-semibold text-slate-800">{{ $child->schoolInfo->school_phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Medical Info (medical_records permission) --}}
            @if($canView('medical_records'))
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800">Health & Medical</h3>
                    </div>

                    @if($child->medicalInfo)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @if($child->medicalInfo->blood_type)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Blood Type</p>
                            <p class="font-semibold text-slate-800">{{ $child->medicalInfo->blood_type }}</p>
                        </div>
                        @endif
                        @if($child->medicalInfo->primary_doctor)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Primary Doctor</p>
                            <p class="font-semibold text-slate-800">{{ $child->medicalInfo->primary_doctor }}</p>
                        </div>
                        @endif
                        @if($child->medicalInfo->doctor_phone)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Doctor Phone</p>
                            <p class="font-semibold text-slate-800">{{ $child->medicalInfo->doctor_phone }}</p>
                        </div>
                        @endif
                        @if($child->medicalInfo->insurance_provider)
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Insurance Provider</p>
                            <p class="font-semibold text-slate-800">{{ $child->medicalInfo->insurance_provider }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Allergies --}}
                    @if($child->allergies && $child->allergies->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Allergies</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($child->allergies as $allergy)
                            <span class="badge badge-error badge-sm">{{ $allergy->allergen_name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Medical Conditions --}}
                    @if($child->medicalConditions && $child->medicalConditions->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Medical Conditions</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($child->medicalConditions as $condition)
                            <span class="badge badge-warning badge-sm">{{ $condition->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Medications --}}
                    @if($child->medications && $child->medications->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Medications</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($child->medications as $medication)
                            <span class="badge badge-info badge-sm">{{ $medication->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Healthcare Providers --}}
                    @if($canView('healthcare_providers') && $child->healthcareProviders && $child->healthcareProviders->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Healthcare Providers</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($child->healthcareProviders as $provider)
                            <span class="badge badge-success badge-sm">{{ $provider->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!$child->medicalInfo && (!$child->allergies || $child->allergies->count() === 0))
                    <p class="text-sm text-slate-400">No medical information available</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Emergency Contacts (emergency_contacts permission) --}}
            @if($canView('emergency_contacts') && $child->emergencyContacts && $child->emergencyContacts->count() > 0)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800">Emergency Contacts</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($child->emergencyContacts as $contact)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(239 68 68)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-slate-800">{{ $contact->name }}</p>
                                <p class="text-sm text-slate-500">{{ $contact->relationship ?? 'Contact' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-slate-700">{{ $contact->phone }}</p>
                                @if($contact->email)
                                <p class="text-xs text-slate-500">{{ $contact->email }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Insurance, Tax & Assets Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Insurance Policies (insurance permission) --}}
                @if($canView('insurance'))
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm">Insurance Policies</h3>
                        </div>
                        @if($child->insurancePolicies && $child->insurancePolicies->count() > 0)
                            <div class="space-y-2">
                                @foreach($child->insurancePolicies->take(3) as $policy)
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <p class="font-medium text-slate-800 text-sm">{{ $policy->provider_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $policy->insurance_type }}</p>
                                </div>
                                @endforeach
                                @if($child->insurancePolicies->count() > 3)
                                    <p class="text-xs text-slate-400">+{{ $child->insurancePolicies->count() - 3 }} more</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-400">No insurance policies</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Tax Returns (tax_returns permission) --}}
                @if($canView('tax_returns'))
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm">Tax Returns</h3>
                        </div>
                        @if($child->taxReturns && $child->taxReturns->count() > 0)
                            <div class="space-y-2">
                                @foreach($child->taxReturns->sortByDesc('tax_year')->take(3) as $taxReturn)
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <p class="font-medium text-slate-800 text-sm">{{ $taxReturn->tax_year }}</p>
                                    <p class="text-xs text-slate-400">{{ $taxReturn->status ?? 'Filed' }}</p>
                                </div>
                                @endforeach
                                @if($child->taxReturns->count() > 3)
                                    <p class="text-xs text-slate-400">+{{ $child->taxReturns->count() - 3 }} more</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-400">No tax returns</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Assets (assets permission) --}}
                @if($canView('assets'))
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm">Assets</h3>
                        </div>
                        @if($child->assets && $child->assets->count() > 0)
                            <div class="space-y-2">
                                @foreach($child->assets->take(3) as $asset)
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <p class="font-medium text-slate-800 text-sm">{{ Str::limit($asset->name, 20) }}</p>
                                    @if($asset->current_value)
                                    <p class="text-xs text-emerald-600">${{ number_format($asset->current_value, 2) }}</p>
                                    @endif
                                </div>
                                @endforeach
                                @if($child->assets->count() > 3)
                                    <p class="text-xs text-slate-400">+{{ $child->assets->count() - 3 }} more</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-400">No assets</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Co-parents --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Co-parents</h3>
                        @if($isOwner ?? true)
                        <a href="{{ route('coparenting.children.access', $child) }}" class="text-sm text-primary hover:underline">Manage</a>
                        @endif
                    </div>

                    @forelse($child->coparents as $coparent)
                    @php
                        $roleColor = match($coparent->parent_role) {
                            'mother' => 'from-pink-400 to-rose-500',
                            'father' => 'from-blue-400 to-indigo-500',
                            default => 'from-emerald-400 to-cyan-500',
                        };
                        $roleLabel = \App\Models\Collaborator::PARENT_ROLES[$coparent->parent_role]['label'] ?? 'Parent';
                    @endphp
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $roleColor }} flex items-center justify-center">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($coparent->user->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ $coparent->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-slate-500">{{ $roleLabel }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500">No co-parents connected</p>
                    @endforelse
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-slate-800 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        @if($isOwner ?? true)
                        <a href="{{ route('coparenting.children.access', $child) }}" class="btn btn-block btn-outline gap-2 justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Manage Access
                        </a>
                        <a href="{{ route('family-circle.member.show', [$child->family_circle_id, $child->id]) }}" class="btn btn-block btn-outline gap-2 justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            Full Profile
                        </a>
                        @endif
                        <a href="{{ route('coparenting.calendar') }}" class="btn btn-block btn-outline gap-2 justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            View Calendar
                        </a>
                        <a href="{{ route('coparenting.activities') }}" class="btn btn-block btn-outline gap-2 justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            Activities
                        </a>
                    </div>
                </div>
            </div>

            {{-- Child Summary Stats --}}
            <div class="card bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Quick Stats</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-pink-100">Co-parents</span>
                            <span class="font-bold">{{ $child->coparents->count() }}</span>
                        </div>
                        @if($canView('emergency_contacts'))
                        <div class="flex items-center justify-between">
                            <span class="text-pink-100">Emergency Contacts</span>
                            <span class="font-bold">{{ $child->emergencyContacts?->count() ?? 0 }}</span>
                        </div>
                        @endif
                        @if($canView('documents'))
                        <div class="flex items-center justify-between">
                            <span class="text-pink-100">Documents</span>
                            <span class="font-bold">{{ ($child->drivers_license ? 1 : 0) + ($child->passport ? 1 : 0) + ($child->social_security ? 1 : 0) + ($child->birth_certificate ? 1 : 0) }}</span>
                        </div>
                        @endif
                        @if($canView('basic_info') && $child->medicalInfo?->blood_type)
                        <div class="flex items-center justify-between">
                            <span class="text-pink-100">Blood Type</span>
                            <span class="font-bold">{{ $child->medicalInfo->blood_type }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
