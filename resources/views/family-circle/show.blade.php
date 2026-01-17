@extends('layouts.dashboard')

@section('title', $circle->name)
@section('page-name', $circle->name)

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $circle->name }}</li>
@endsection

@section('content')

@php
    $isCollaborator = $isCollaborator ?? false;
@endphp

<div id="family-circle-space">
    @if($isCollaborator)
        <!-- Collaborator Notice -->
        <div class="alert bg-emerald-50 border border-emerald-200 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="text-sm text-emerald-700">You're viewing this circle as a collaborator. You can only see members that have been shared with you.</span>
        </div>
    @endif

    <!-- Circle Header -->
    <div class="card bg-gradient-to-r {{ $isCollaborator ? 'from-emerald-600 to-teal-600' : 'from-violet-600 to-purple-600' }} text-white mb-6">
        <div class="card-body">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center overflow-hidden">
                        @if($circle->cover_image)
                            <img src="{{ Storage::disk('do_spaces')->url($circle->cover_image) }}" alt="{{ $circle->name }}" class="w-full h-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $circle->name }}</h1>
                        @if($circle->description)
                            <p class="text-white/80 mt-1">{{ $circle->description }}</p>
                        @endif
                        <p class="text-white/60 text-sm mt-2">{{ $circle->members->count() }} member{{ $circle->members->count() != 1 ? 's' : '' }}</p>
                    </div>
                </div>
                @if(!$isCollaborator)
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openEditCircleModal()" class="btn btn-ghost btn-sm text-white/80 hover:text-white hover:bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        Edit
                    </button>
                    <a href="{{ route('family-circle.member.create', $circle) }}" class="btn btn-sm bg-white text-violet-600 hover:bg-white/90 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        Add Member
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs tabs-bordered mb-6" role="tablist">
        <button type="button" class="tab tab-active text-base gap-2" data-tab="members" role="tab" aria-selected="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Members
            <span class="badge badge-sm">{{ $circle->members->count() }}</span>
        </button>
        <button type="button" class="tab text-base gap-2" data-tab="resources" role="tab" aria-selected="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
            Family Resources
            @if(isset($familyResources) && $familyResources->count() > 0)
            <span class="badge badge-sm badge-success">{{ $familyResources->count() }}</span>
            @endif
        </button>
        <button type="button" class="tab text-base gap-2" data-tab="legal" role="tab" aria-selected="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14 14 6 6"/><path d="M4 6h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/><path d="M2 10h20"/></svg>
            Legal Documents
            @if(isset($legalDocuments) && $legalDocuments->count() > 0)
            <span class="badge badge-sm badge-primary">{{ $legalDocuments->count() }}</span>
            @endif
        </button>
    </div>

    <!-- Tab: Members -->
    <div id="tab-members" class="tab-content">
        <div class="space-y-4">
            @php
                // Check if the owner included themselves in this circle (only for non-collaborators)
                $selfMember = !$isCollaborator ? $circle->members->where('relationship', 'self')->where('linked_user_id', auth()->id())->first() : null;
                $owner = auth()->user();
                $ownerNameParts = explode(' ', $owner->name, 2);
                $ownerFirstName = $ownerNameParts[0];
                $ownerLastName = $ownerNameParts[1] ?? '';
                $ownerAge = $owner->date_of_birth ? \Carbon\Carbon::parse($owner->date_of_birth)->age : null;
            @endphp

            <!-- Owner Self Card (only shown if owner included themselves and not a collaborator) -->
            @if($selfMember && !$isCollaborator)
            <div class="card bg-base-100 shadow-sm hover:shadow-lg transition-all duration-200 border-2 border-violet-200 hover:border-violet-400">
                <div class="card-body p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Avatar -->
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-md overflow-hidden">
                            @if($owner->avatar)
                                <img src="{{ Storage::disk('do_spaces')->url($owner->avatar) }}" alt="{{ $owner->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-white">{{ strtoupper(substr($ownerFirstName, 0, 1)) }}</span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $owner->name }}</h3>
                                <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                                <span class="badge badge-primary badge-sm">Account Owner</span>
                            </div>
                            <p class="text-sm text-slate-500 mb-2">Self @if($ownerAge)• {{ $ownerAge }} years old @endif</p>
                            <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                                @if($owner->email)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <span class="truncate">{{ $owner->email }}</span>
                                </div>
                                @endif
                                @if($owner->phone)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>{{ $owner->phone }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-row md:flex-col gap-2">
                            <a href="{{ route('family-circle.owner.show', $circle) }}" class="btn btn-sm btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                            <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Other Family Members (excluding self to avoid duplicate for owners, show all for collaborators) -->
            @foreach($isCollaborator ? $circle->members : $circle->members->where('relationship', '!=', 'self') as $member)
            @php
                // Get permissions for this specific member if collaborator
                $memberAccess = $isCollaborator && $collaboration
                    ? \App\Services\CollaboratorPermissionService::forMember($member)->forView()
                    : null;
                $canViewDob = !$isCollaborator || ($memberAccess && $memberAccess->canView('date_of_birth'));
                $canViewContact = !$isCollaborator || ($memberAccess && $memberAccess->canView('emergency_contacts'));
            @endphp
            <div class="card bg-base-100 shadow-sm hover:shadow-lg transition-all duration-200 border border-slate-200 hover:border-cyan-300">
                <div class="card-body p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Avatar -->
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center flex-shrink-0 shadow-md overflow-hidden">
                            @if($member->profile_image_url)
                                <img src="{{ $member->profile_image_url }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $member->full_name }}</h3>
                                @if($member->linked_user_id === auth()->id())
                                    <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                                @endif
                                @if(!$isCollaborator)
                                    @if($member->is_minor)
                                        <span class="badge badge-info badge-sm">Minor</span>
                                    @endif
                                    @if($member->co_parenting_enabled)
                                        <span class="badge badge-warning badge-sm">Co-Parenting</span>
                                    @endif
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mb-2">{{ $member->relationship_name }} @if($canViewDob)• {{ $member->age }} years old @endif</p>
                            <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                                @if($canViewContact && $member->email)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <span class="truncate">{{ $member->email }}</span>
                                </div>
                                @endif
                                @if($canViewContact && $member->phone)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>{{ $member->phone_country_code ?? '' }}{{ $member->phone }}</span>
                                </div>
                                @endif
                                @if(!$isCollaborator && $member->documents->count() > 0)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                    <span>{{ $member->documents->count() }} docs</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-row md:flex-col gap-2">
                            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-sm btn-info gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                            @if(!$isCollaborator)
                            <a href="{{ route('member.documents.index', $member) }}" class="btn btn-sm btn-outline btn-info gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                Documents
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab: Family Resources -->
    <div id="tab-resources" class="tab-content hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m5 12-3 3 3 3"/><path d="m9 18 3-3-3-3"/></svg>
                Family Resources
            </h2>
            @if(!$isCollaborator)
            <a href="{{ route('family-resources.create') }}?family_circle_id={{ $circle->id }}" class="btn btn-sm btn-outline btn-success gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Resource
            </a>
            @endif
        </div>

        @if(isset($familyResources) && $familyResources->count() > 0)
        <div class="space-y-4">
            @foreach($familyResources as $resource)
            <div class="card bg-base-100 shadow-sm hover:shadow-lg transition-all duration-200 border border-emerald-100 hover:border-emerald-300">
                <div class="card-body p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Icon -->
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center flex-shrink-0 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $resource->name }}</h3>
                                <span class="badge badge-sm {{ $resource->status === 'active' ? 'badge-success' : 'badge-neutral' }}">
                                    {{ ucfirst($resource->status) }}
                                </span>
                                @if(!$resource->family_circle_id)
                                <span class="badge badge-xs badge-ghost" title="Shared with all circles">All Circles</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mb-2">{{ $resource->document_type_name }}</p>
                            @if($resource->notes)
                            <p class="text-sm text-slate-600 line-clamp-1">{{ $resource->notes }}</p>
                            @endif
                        </div>

                        <!-- Meta & Actions -->
                        <div class="flex flex-row md:flex-col items-center md:items-end gap-3 md:gap-2">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                <span>{{ $resource->files_count ?? 0 }} file{{ ($resource->files_count ?? 0) != 1 ? 's' : '' }}</span>
                            </div>
                            <a href="{{ route('family-resources.show', $resource) }}" class="btn btn-sm btn-success gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No Family Resources Yet</h3>
            <p class="text-slate-500 mb-4">Add resources like emergency plans, warranties, and more.</p>
            @if(!$isCollaborator)
            <a href="{{ route('family-resources.create') }}?family_circle_id={{ $circle->id }}" class="btn btn-success gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add First Resource
            </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Tab: Legal Documents -->
    <div id="tab-legal" class="tab-content hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="m14 14 6 6"/><path d="M4 6h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/><path d="M2 10h20"/></svg>
                Legal Documents
            </h2>
            @if(!$isCollaborator)
            <a href="{{ route('legal.create') }}?family_circle_id={{ $circle->id }}" class="btn btn-sm btn-outline btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Document
            </a>
            @endif
        </div>

        @if(isset($legalDocuments) && $legalDocuments->count() > 0)
        <div class="space-y-4">
            @foreach($legalDocuments as $document)
            <div class="card bg-base-100 shadow-sm hover:shadow-lg transition-all duration-200 border border-violet-100 hover:border-violet-300">
                <div class="card-body p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Icon -->
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center flex-shrink-0 shadow-md">
                            <span class="{{ $document->document_type_icon }} text-white text-2xl"></span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $document->name }}</h3>
                                <span class="badge badge-sm badge-{{ $document->status_color }}">
                                    {{ $document->status_name }}
                                </span>
                                @if(!$document->family_circle_id)
                                <span class="badge badge-xs badge-ghost" title="Shared with all circles">All Circles</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mb-2">{{ $document->document_type_name }}</p>
                            <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                                @if($document->attorney_display_name)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    <span>{{ $document->attorney_display_name }}</span>
                                </div>
                                @endif
                                @if($document->execution_date)
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                    <span>{{ $document->execution_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Meta & Actions -->
                        <div class="flex flex-row md:flex-col items-center md:items-end gap-3 md:gap-2">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                <span>{{ $document->files_count ?? 0 }} file{{ ($document->files_count ?? 0) != 1 ? 's' : '' }}</span>
                            </div>
                            <a href="{{ route('legal.show', $document) }}" class="btn btn-sm btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="m14 14 6 6"/><path d="M4 6h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/><path d="M2 10h20"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">No Legal Documents Yet</h3>
            <p class="text-slate-500 mb-4">Add wills, trusts, power of attorney, and other legal documents.</p>
            @if(!$isCollaborator)
            <a href="{{ route('legal.create') }}?family_circle_id={{ $circle->id }}" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add First Document
            </a>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Edit Circle Modal -->
<div id="editCircleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <!-- Backdrop -->
    <div id="editCircleBackdrop" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"></div>
    <!-- Modal -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; overflow-y: auto;">
        <div style="display: flex; min-height: 100%; align-items: center; justify-content: center; padding: 1rem;">
            <div style="position: relative; width: 100%; max-width: 28rem; background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Edit Family Circle</h3>
                    <button type="button" onclick="closeEditCircleModal()" style="padding: 0.25rem; border-radius: 0.5rem; color: #94a3b8; background: transparent; border: none; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <form action="{{ route('family-circle.update', $circle) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div style="padding: 1.5rem;">
                        <!-- Cover Image Upload -->
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">
                                Circle Photo <span style="color: #94a3b8; font-weight: 400;">(Optional)</span>
                            </label>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div id="editCircleImagePreview" style="width: 80px; height: 80px; border-radius: 12px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                    @if($circle->cover_image)
                                        <img id="editCircleImageImg" src="{{ Storage::disk('do_spaces')->url($circle->cover_image) }}" alt="{{ $circle->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        <svg id="editCircleDefaultIcon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    @else
                                        <img id="editCircleImageImg" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <svg id="editCircleDefaultIcon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    @endif
                                </div>
                                <div style="flex: 1;">
                                    <label for="edit_cover_image" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; color: #334155; cursor: pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        {{ $circle->cover_image ? 'Change Photo' : 'Choose Photo' }}
                                    </label>
                                    <input type="file" name="cover_image" id="edit_cover_image" accept="image/*" style="display: none;" onchange="previewEditCircleImage(this)">
                                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">JPG, PNG or GIF. Max 2MB.</p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">
                                Circle Name <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="name" value="{{ $circle->name }}" required maxlength="255" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.875rem; outline: none;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Description</label>
                            <textarea name="description" maxlength="1000" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.875rem; outline: none; height: 6rem; resize: none;">{{ $circle->description }}</textarea>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-violet-50 rounded-lg border border-violet-100 mt-4">
                            <input type="checkbox" name="include_me" id="edit_include_me" value="1" {{ $selfMember ? 'checked' : '' }} class="mt-0.5 h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                            <label for="edit_include_me" class="flex-1 cursor-pointer">
                                <span class="block text-sm font-medium text-slate-700">{{ explode(' ', auth()->user()->name)[0] }}, would you like to include yourself in this circle?</span>
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; background: #f8fafc; border-radius: 0 0 1rem 1rem;">
                        <button type="button" onclick="closeEditCircleModal()" style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #334155; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer;">Cancel</button>
                        <button type="submit" style="padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 500; color: white; background: #7c3aed; border: none; border-radius: 0.5rem; cursor: pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Edit Circle Image Preview
function previewEditCircleImage(input) {
    const preview = document.getElementById('editCircleImageImg');
    const defaultIcon = document.getElementById('editCircleDefaultIcon');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (defaultIcon) {
                defaultIcon.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Edit Circle Modal functions
function openEditCircleModal() {
    const modal = document.getElementById('editCircleModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditCircleModal() {
    const modal = document.getElementById('editCircleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Edit Circle Modal backdrop click
    const editBackdrop = document.getElementById('editCircleBackdrop');
    if (editBackdrop) {
        editBackdrop.addEventListener('click', function() {
            closeEditCircleModal();
        });
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditCircleModal();
        }
    });

    // Tab switching functionality
    const tabs = document.querySelectorAll('[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Update tab button states
            tabs.forEach(t => {
                t.classList.remove('tab-active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('tab-active');
            this.setAttribute('aria-selected', 'true');

            // Update tab content visibility
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            const targetContent = document.getElementById('tab-' + targetTab);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }

            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + targetTab);
        });
    });

    // Handle initial hash in URL
    const hash = window.location.hash.replace('#', '');
    if (hash && ['members', 'resources', 'legal'].includes(hash)) {
        const targetTab = document.querySelector('[data-tab="' + hash + '"]');
        if (targetTab) {
            targetTab.click();
        }
    }
});
</script>
@endpush
