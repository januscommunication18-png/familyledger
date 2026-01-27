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
    @if($access->isCollaborator && !$access->hasFullAccess)
        <!-- Collaborator Notice (limited access) -->
        <div class="alert bg-amber-50 border border-amber-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="text-sm text-amber-700">You're viewing as a co-parent. Some sections may be hidden or read-only based on your permissions.</span>
        </div>
    @elseif($access->isCollaborator && $access->hasFullAccess)
        <!-- Co-parent Notice (full edit access) -->
        <div class="alert bg-emerald-50 border border-emerald-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="text-sm text-emerald-700">You're viewing as a co-parent with full edit access.</span>
        </div>
    @endif

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
                @if($access->hasFullAccess)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('family-circle.member.edit', [$circle, $member]) }}" class="btn btn-sm btn-ghost gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            Edit
                        </a>
                        @if($access->isOwner)
                        <button type="button" onclick="showDeleteMemberModal()" class="btn btn-sm btn-ghost text-error gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            Delete
                        </button>
                        @endif
                    </div>
                @endif
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
                    @if($access->canView('date_of_birth'))
                        <div class="p-3 rounded-lg bg-slate-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Date of Birth</p>
                            <p class="font-semibold text-slate-800">{{ $member->date_of_birth->format('M d, Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $member->age }} years old</p>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-slate-50 opacity-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Date of Birth</p>
                            <p class="text-sm text-slate-400 italic">No access</p>
                        </div>
                    @endif

                    <!-- Blood Group - Inline Editable -->
                    @if($access->canView('medical'))
                    <div class="p-3 rounded-lg bg-slate-50 group relative">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Blood Group</p>
                        <div id="bloodGroupDisplay" class="flex items-center gap-2">
                            @if($member->medicalInfo && $member->medicalInfo->blood_type)
                                <p class="font-semibold text-slate-800">{{ \App\Models\MemberMedicalInfo::BLOOD_TYPES[$member->medicalInfo->blood_type] ?? $member->medicalInfo->blood_type }}</p>
                            @else
                                <p class="text-sm text-slate-400 italic">Not specified</p>
                            @endif
                            @if($access->canEdit('medical'))
                            <button onclick="toggleBloodGroupEdit()" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-slate-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            @endif
                        </div>
                        @if($access->canEdit('medical'))
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
                        @endif
                    </div>
                    @else
                    <div class="p-3 rounded-lg bg-slate-50 opacity-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Blood Group</p>
                        <p class="text-sm text-slate-400 italic">No access</p>
                    </div>
                    @endif

                    <!-- Immigration Status - Inline Editable -->
                    @if($access->canView('immigration_status'))
                        <div class="p-3 rounded-lg bg-slate-50 group relative">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Immigration Status</p>
                            <div id="immigrationStatusDisplay" class="flex items-center gap-2">
                                @if($member->immigration_status_name)
                                    <p class="font-semibold text-slate-800">{{ $member->immigration_status_name }}</p>
                                @else
                                    <p class="text-sm text-slate-400 italic">Not specified</p>
                                @endif
                                @if($access->canEdit('immigration_status'))
                                    <button onclick="toggleImmigrationEdit()" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-slate-200 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </button>
                                @endif
                            </div>
                            @if($access->canEdit('immigration_status'))
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
                            @endif
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-slate-50 opacity-50">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Immigration Status</p>
                            <p class="text-sm text-slate-400 italic">No access</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Cards - 4 in a row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Driver's License Card -->
        @if($access->canView('drivers_license'))
            <a href="{{ route('family-circle.member.drivers-license', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M6 9h4"/><path d="M14 9h4"/></svg>
                        </div>
                        @if($access->canEdit('drivers_license'))
                            <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </span>
                        @elseif($access->canEdit('drivers_license'))
                            <span class="badge badge-success badge-xs">Can Edit</span>
                        @elseif($access->isCollaborator)
                            <span class="badge badge-ghost badge-xs">View Only</span>
                        @endif
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
                            @if($access->canEdit('drivers_license'))
                                <span class="btn btn-xs btn-primary w-full gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    Add
                                </span>
                            @else
                                <span class="text-xs text-slate-400">No data</span>
                            @endif
                        </div>
                    @endif
                </div>
            </a>
        @endif

        <!-- Passport Card -->
        @if($access->canView('passport'))
            <a href="{{ route('family-circle.member.passport', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/></svg>
                        </div>
                        @if($access->canEdit('passport'))
                            <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </span>
                        @elseif($access->canEdit('passport'))
                            <span class="badge badge-success badge-xs">Can Edit</span>
                        @elseif($access->isCollaborator)
                            <span class="badge badge-ghost badge-xs">View Only</span>
                        @endif
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
                            @if($access->canEdit('passport'))
                                <span class="btn btn-xs btn-primary w-full gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    Add
                                </span>
                            @else
                                <span class="text-xs text-slate-400">No data</span>
                            @endif
                        </div>
                    @endif
                </div>
            </a>
        @endif

        <!-- Social Security Card -->
        @if($access->canView('ssn'))
            <a href="{{ route('family-circle.member.social-security', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 12h.01"/></svg>
                        </div>
                        @if($access->canEdit('ssn'))
                            <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </span>
                        @elseif($access->canEdit('ssn'))
                            <span class="badge badge-success badge-xs">Can Edit</span>
                        @elseif($access->isCollaborator)
                            <span class="badge badge-ghost badge-xs">View Only</span>
                        @endif
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
                            @if($access->canEdit('ssn'))
                                <span class="btn btn-xs btn-primary w-full gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    Add
                                </span>
                            @else
                                <span class="text-xs text-slate-400">No data</span>
                            @endif
                        </div>
                    @endif
                </div>
            </a>
        @endif

        <!-- Birth Certificate Card -->
        @if($access->canView('birth_certificate'))
            <a href="{{ route('family-circle.member.birth-certificate', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        </div>
                        @if($access->canEdit('birth_certificate'))
                            <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </span>
                        @elseif($access->canEdit('birth_certificate'))
                            <span class="badge badge-success badge-xs">Can Edit</span>
                        @elseif($access->isCollaborator)
                            <span class="badge badge-ghost badge-xs">View Only</span>
                        @endif
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
                            @if($access->canEdit('birth_certificate'))
                                <span class="btn btn-xs btn-primary w-full gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    Add
                                </span>
                            @else
                                <span class="text-xs text-slate-400">No data</span>
                            @endif
                        </div>
                    @endif
                </div>
            </a>
        @endif
    </div>

    <!-- Quick Links - 4 in a row (Only for owners, not collaborators) -->
    @if(!$access->isCollaborator)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Documents -->
        <a href="{{ route('documents.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Documents</h3>
                <p class="text-xs text-slate-500 mt-1">View all your identity documents</p>
            </div>
        </a>

        <!-- Legal Documents -->
        <a href="{{ route('legal.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Legal Documents</h3>
                <p class="text-xs text-slate-500 mt-1">Wills, trusts & legal papers</p>
            </div>
        </a>

        <!-- Family Resources -->
        <a href="{{ route('family-resources.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Family Resources</h3>
                <p class="text-xs text-slate-500 mt-1">Recipes, traditions & more</p>
            </div>
        </a>

    </div>
    @endif

    <!-- Additional Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Education -->
        @if($access->canView('school'))
        <a href="{{ route('family-circle.member.education-info', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Education</h3>
                    </div>
                    @if($access->canEdit('school'))
                        <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </span>
                    @elseif($access->canEdit('school'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
                </div>

                @if($member->schoolRecords && $member->schoolRecords->count() > 0)
                    <div class="space-y-2 text-xs">
                        @foreach($member->schoolRecords->take(3) as $record)
                            <div class="flex items-center justify-between gap-2 {{ !$loop->first ? 'pt-2 border-t border-slate-100' : '' }}">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-slate-700 truncate">{{ $record->school_name }}</p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        @if($record->grade_level_name)
                                            <span class="text-slate-400">{{ $record->grade_level_name }}</span>
                                        @endif
                                        @if($record->school_year)
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="text-slate-400">{{ $record->school_year }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($record->is_current)
                                    <span class="badge badge-primary badge-xs flex-shrink-0">Current</span>
                                @endif
                            </div>
                        @endforeach
                        @if($member->schoolRecords->count() > 3)
                            <div class="text-center pt-1">
                                <span class="text-slate-400">+{{ $member->schoolRecords->count() - 3 }} more</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-1">
                        @if($access->canEdit('school'))
                            <span class="btn btn-xs btn-primary w-full gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add
                            </span>
                        @else
                            <span class="text-xs text-slate-400">No data</span>
                        @endif
                    </div>
                @endif
            </div>
        </a>
        @endif

        <!-- Health & Medical -->
        @if($access->canView('medical'))
        <a href="{{ route('family-circle.member.medical-info', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Health & Medical</h3>
                    </div>
                    @if($access->canEdit('medical'))
                        <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </span>
                    @elseif($access->canEdit('medical'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
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
                        @if($access->canEdit('medical'))
                            <span class="btn btn-xs btn-primary w-full gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add
                            </span>
                        @else
                            <span class="text-xs text-slate-400">No data</span>
                        @endif
                    </div>
                @endif
            </div>
        </a>
        @endif

        <!-- Emergency Contacts -->
        @if($access->canView('emergency_contacts'))
        <a href="{{ route('family-circle.member.emergency-contacts', [$circle, $member]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer group">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Emergency Contacts</h3>
                    </div>
                    @if($access->canEdit('emergency_contacts'))
                        <span class="btn btn-ghost btn-xs opacity-0 group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </span>
                    @elseif($access->canEdit('emergency_contacts'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
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
                        @if($access->canEdit('emergency_contacts'))
                            <span class="btn btn-xs btn-primary w-full gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add
                            </span>
                        @else
                            <span class="text-xs text-slate-400">No data</span>
                        @endif
                    </div>
                @endif
            </div>
        </a>
        @endif

        <!-- Activity - Only show to owners -->
        @if(!$access->isCollaborator)
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
        @endif
    </div>

    <!-- Insurance, Tax Returns & Assets Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Insurance Policies Card -->
        @if($access->canView('insurance'))
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Insurance Policies</h3>
                    </div>
                    @if($access->canEdit('insurance'))
                        <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost btn-xs text-blue-600">
                            View All
                        </a>
                    @elseif($access->canEdit('insurance'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
                </div>

                @if($member->insurancePolicies && $member->insurancePolicies->count() > 0)
                    <div class="space-y-2">
                        @foreach($member->insurancePolicies->take(3) as $policy)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <span class="{{ $policy->getTypeIcon() }} size-4 text-blue-600"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $policy->provider_name }}</p>
                                        <p class="text-xs text-slate-400">{{ \App\Models\InsurancePolicy::INSURANCE_TYPES[$policy->insurance_type] ?? $policy->insurance_type }}</p>
                                    </div>
                                </div>
                                <span class="badge badge-sm badge-{{ $policy->getStatusColor() }}">{{ \App\Models\InsurancePolicy::STATUSES[$policy->status] ?? $policy->status }}</span>
                            </div>
                        @endforeach
                        @if($member->insurancePolicies->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $member->insurancePolicies->count() - 3 }} more policies</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No insurance policies linked</p>
                        @if($access->canEdit('insurance'))
                            <a href="{{ route('documents.insurance.create') }}" class="btn btn-xs btn-primary gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add Insurance
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Tax Returns Card -->
        @if($access->canView('tax_returns'))
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Tax Returns</h3>
                    </div>
                    @if($access->canEdit('tax_returns'))
                        <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="btn btn-ghost btn-xs text-emerald-600">
                            View All
                        </a>
                    @elseif($access->canEdit('tax_returns'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
                </div>

                @if($member->taxReturns && $member->taxReturns->count() > 0)
                    <div class="space-y-2">
                        @foreach($member->taxReturns->sortByDesc('tax_year')->take(3) as $taxReturn)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <span class="text-emerald-700 font-bold text-xs">{{ substr($taxReturn->tax_year, -2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $taxReturn->tax_year }} Tax Return</p>
                                        <p class="text-xs text-slate-400">{{ \App\Models\TaxReturn::JURISDICTIONS[$taxReturn->tax_jurisdiction] ?? $taxReturn->tax_jurisdiction }}</p>
                                    </div>
                                </div>
                                <span class="badge badge-sm badge-{{ $taxReturn->getStatusColor() }}">{{ \App\Models\TaxReturn::STATUSES[$taxReturn->status] ?? $taxReturn->status }}</span>
                            </div>
                        @endforeach
                        @if($member->taxReturns->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $member->taxReturns->count() - 3 }} more returns</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No tax returns linked</p>
                        @if($access->canEdit('tax_returns'))
                            <a href="{{ route('documents.tax-returns.create') }}" class="btn btn-xs btn-primary gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add Tax Return
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Assets Card -->
        @if($access->canView('assets'))
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Assets</h3>
                    </div>
                    @if($access->canEdit('assets'))
                        <a href="{{ route('assets.index') }}" class="btn btn-ghost btn-xs text-amber-600">
                            View All
                        </a>
                    @elseif($access->canEdit('assets'))
                        <span class="badge badge-success badge-xs">Can Edit</span>
                    @elseif($access->isCollaborator)
                        <span class="badge badge-ghost badge-xs">View Only</span>
                    @endif
                </div>

                @if($member->assets && $member->assets->count() > 0)
                    <div class="space-y-2">
                        @foreach($member->assets->take(3) as $asset)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <span class="{{ $asset->getCategoryIcon() }} size-4 text-amber-600"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ Str::limit($asset->name, 20) }}</p>
                                        <p class="text-xs text-slate-400">{{ $asset->category_name }}</p>
                                    </div>
                                </div>
                                @if($asset->current_value)
                                    <span class="text-xs font-medium text-emerald-600">{{ $asset->formatted_current_value }}</span>
                                @endif
                            </div>
                        @endforeach
                        @if($member->assets->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $member->assets->count() - 3 }} more assets</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No assets linked</p>
                        @if($access->canEdit('assets'))
                            <a href="{{ route('assets.create') }}" class="btn btn-xs btn-primary gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add Asset
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Member Confirmation Modal -->
<div id="deleteMemberModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeDeleteMemberModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 pointer-events-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                </div>
                <h3 class="font-bold text-lg text-error">Delete Member</h3>
            </div>
            <p class="text-sm text-base-content/70 mb-2">Are you sure you want to delete <strong>{{ $member->full_name }}</strong>?</p>
            <p class="text-sm text-base-content/60 mb-6">This will also remove all associated documents, medical records, and other related data. This action cannot be undone.</p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDeleteMemberModal()" class="btn btn-ghost">Cancel</button>
                <form action="{{ route('family-circle.member.destroy', [$circle, $member]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete Member</button>
                </form>
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

// Delete member modal functions
function showDeleteMemberModal() {
    document.getElementById('deleteMemberModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteMemberModal() {
    document.getElementById('deleteMemberModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('deleteMemberModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeDeleteMemberModal();
        }
    }
});
</script>
@endpush
