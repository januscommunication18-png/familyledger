@extends('layouts.dashboard')

@section('title', 'Invite Collaborator')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('collaborators.index') }}">Collaborators</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Invite</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="inviteForm()">
    <!-- Progress Steps -->
    <div class="flex items-center justify-center gap-4 mb-8">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-success text-success-content flex items-center justify-center text-sm font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="text-sm text-slate-500">Select Circles</span>
        </div>
        <div class="w-12 h-0.5 bg-primary"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">2</span>
            <span class="text-sm font-medium text-primary">Invite Details</span>
        </div>
    </div>

    <form method="POST" action="{{ route('collaborators.store') }}">
        @csrf

        <!-- Hidden field to track selected circles -->
        <input type="hidden" name="selected_circles" value="{{ $selectedCircles->pluck('id')->implode(',') }}">

        @if($errors->any())
            <div class="alert alert-error mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Step 1: Basic Info -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">1</span>
                    Invitee Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-medium">Email Address <span class="text-error">*</span></span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="input input-bordered" placeholder="email@example.com">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">First Name</span>
                        </label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                               class="input input-bordered" placeholder="Optional">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Last Name</span>
                        </label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               class="input input-bordered" placeholder="Optional">
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text">Personal Message</span>
                        </label>
                        <textarea name="message" rows="3" class="textarea textarea-bordered"
                                  placeholder="Add a personal note to your invitation...">{{ old('message') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Relationship -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">2</span>
                    Relationship
                </h3>

                <!-- Relationship Type -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">How is this person related to your family? <span class="text-error">*</span></span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @foreach($relationshipTypes as $key => $type)
                            @if(!($type['disabled'] ?? false))
                                <label class="cursor-pointer">
                                    <input type="radio" name="relationship_type" value="{{ $key }}"
                                           class="hidden peer" x-model="relationshipType"
                                           {{ old('relationship_type', 'other') === $key ? 'checked' : '' }}>
                                    <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-base-300
                                                peer-checked:border-primary peer-checked:bg-primary/5 transition-all hover:bg-base-200">
                                        <span class="text-sm">{{ $type['label'] }}</span>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Family Members by Circle -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">3</span>
                    Family Member Access
                </h3>

                <p class="text-sm text-slate-500 mb-4">Select which family members this person can view in each circle</p>

                @foreach($selectedCircles as $circle)
                    <div class="mb-6 last:mb-0">
                        <!-- Circle Header -->
                        <div class="flex items-center gap-3 mb-3 pb-2 border-b">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-slate-800">{{ $circle->name }}</h4>
                                <p class="text-xs text-slate-500">{{ $circle->members->count() }} member{{ $circle->members->count() != 1 ? 's' : '' }}</p>
                            </div>
                            <button type="button" class="btn btn-ghost btn-xs" @click="toggleCircle({{ $circle->id }})">
                                <span x-text="isCircleFullySelected({{ $circle->id }}) ? 'Deselect All' : 'Select All'"></span>
                            </button>
                        </div>

                        @if($circle->members->count() > 0)
                            <div class="space-y-3">
                                @foreach($circle->members as $member)
                                    <div class="border-2 rounded-xl transition-all"
                                         :class="selectedMembers.includes('{{ $member->id }}') ? 'border-primary bg-primary/5' : 'border-base-300'">
                                        <!-- Member Header (Clickable) -->
                                        <label class="cursor-pointer block">
                                            <input type="checkbox" name="family_members[]" value="{{ $member->id }}"
                                                   class="hidden" x-model="selectedMembers"
                                                   data-circle="{{ $circle->id }}">
                                            <div class="flex items-center gap-3 p-4">
                                                <div class="avatar placeholder">
                                                    <div class="rounded-full w-10 transition-colors"
                                                         :class="selectedMembers.includes('{{ $member->id }}') ? 'bg-primary/20 text-primary' : 'bg-slate-100 text-slate-600'">
                                                        <span>{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-sm truncate">{{ $member->first_name }} {{ $member->last_name }}</div>
                                                    <div class="text-xs text-slate-500">{{ $member->relationship ?? 'Family Member' }}</div>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                                     :class="selectedMembers.includes('{{ $member->id }}') ? 'border-primary bg-primary' : 'border-base-300'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                         x-show="selectedMembers.includes('{{ $member->id }}')" x-cloak><path d="M20 6 9 17l-5-5"/></svg>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Permission Controls (Shown when selected) -->
                                        <div x-show="selectedMembers.includes('{{ $member->id }}')" x-cloak
                                             class="px-4 pb-4 pt-2 border-t border-base-200">

                                            <!-- Access Level Selector (Top) -->
                                            <div class="mb-4">
                                                <div class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">
                                                    Access Level for {{ $member->first_name }}
                                                </div>
                                                <div class="grid grid-cols-4 gap-2">
                                                    <!-- Hidden -->
                                                    <button type="button"
                                                            class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                                                            :class="getMemberAccessLevel('{{ $member->id }}') === 'none' ? 'border-slate-400 bg-slate-100' : 'border-base-200 hover:border-slate-300 hover:bg-slate-50'"
                                                            @click="setAllPermissions('{{ $member->id }}', 'none')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                                                        <span class="text-xs font-medium text-slate-600">Hidden</span>
                                                    </button>

                                                    <!-- View Only -->
                                                    <button type="button"
                                                            class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                                                            :class="getMemberAccessLevel('{{ $member->id }}') === 'view' ? 'border-info bg-info/10' : 'border-base-200 hover:border-info/50 hover:bg-info/5'"
                                                            @click="setAllPermissions('{{ $member->id }}', 'view')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                        <span class="text-xs font-medium text-slate-600">View Only</span>
                                                    </button>

                                                    <!-- Can Edit -->
                                                    <button type="button"
                                                            class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                                                            :class="getMemberAccessLevel('{{ $member->id }}') === 'edit' ? 'border-success bg-success/10' : 'border-base-200 hover:border-success/50 hover:bg-success/5'"
                                                            @click="setAllPermissions('{{ $member->id }}', 'edit')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                        <span class="text-xs font-medium text-slate-600">Can Edit</span>
                                                    </button>

                                                    <!-- Full Access -->
                                                    <button type="button"
                                                            class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                                                            :class="getMemberAccessLevel('{{ $member->id }}') === 'full' ? 'border-warning bg-warning/10' : 'border-base-200 hover:border-warning/50 hover:bg-warning/5'"
                                                            @click="setAllPermissions('{{ $member->id }}', 'full')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                                        <span class="text-xs font-medium text-slate-600">Full Access</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Customize Individual Permissions (Collapsible) -->
                                            <div x-data="{ showDetails: false }">
                                                <button type="button" @click="showDetails = !showDetails"
                                                        class="flex items-center gap-2 text-xs text-slate-500 hover:text-slate-700 mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                         :class="showDetails ? 'rotate-90' : ''" class="transition-transform"><path d="m9 18 6-6-6-6"/></svg>
                                                    <span x-text="showDetails ? 'Hide individual settings' : 'Customize individual permissions'"></span>
                                                </button>

                                                <div x-show="showDetails" x-collapse>
                                                    @php
                                                        // Group permissions while preserving keys
                                                        $groupedPermissions = [];
                                                        foreach ($permissionCategories as $key => $category) {
                                                            $group = $category['group'];
                                                            if (!isset($groupedPermissions[$group])) {
                                                                $groupedPermissions[$group] = [];
                                                            }
                                                            $groupedPermissions[$group][$key] = $category;
                                                        }
                                                    @endphp

                                                    <div class="space-y-4">
                                                        @foreach($groupedPermissions as $groupName => $permissions)
                                                            <div>
                                                                <div class="text-xs font-semibold text-slate-600 mb-2 flex items-center gap-2">
                                                                    @if($groupName === 'Basic Info')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                                                    @elseif($groupName === 'Documents')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-400"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                    @elseif($groupName === 'Health')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                                                    @elseif($groupName === 'Education')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                                                    @elseif($groupName === 'Financial')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                                                    @elseif($groupName === 'Legal')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400"><path d="m14 14-8.5 8.5a2.12 2.12 0 1 1-3-3L11 11"/><path d="m16 16 6-6"/><path d="m8 8 6-6"/><path d="m9 7 8 8"/><path d="m21 11-8-8"/></svg>
                                                                    @elseif($groupName === 'Resources')
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                                                                    @endif
                                                                    {{ $groupName }}
                                                                </div>
                                                                <div class="space-y-2">
                                                                    @foreach($permissions as $catKey => $category)
                                                                        <div class="flex items-center justify-between p-2 rounded-lg border border-base-200 hover:bg-base-50"
                                                                             :class="{
                                                                                 'bg-slate-50 border-slate-200': memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'none',
                                                                                 'bg-info/5 border-info/30': memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'view',
                                                                                 'bg-success/5 border-success/30': memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'edit',
                                                                                 'bg-warning/5 border-warning/30': memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'full'
                                                                             }">
                                                                            <span class="text-sm text-slate-700">{{ $category['label'] }}</span>
                                                                            <div class="flex items-center gap-1">
                                                                                <input type="hidden" name="permissions[{{ $member->id }}][{{ $catKey }}]"
                                                                                       :value="memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] || 'view'">

                                                                                <!-- Hidden button -->
                                                                                <button type="button"
                                                                                        class="btn btn-xs transition-all"
                                                                                        :class="memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'none' ? 'btn-neutral' : 'btn-ghost text-slate-400'"
                                                                                        @click="setPermission('{{ $member->id }}', '{{ $catKey }}', 'none')"
                                                                                        title="Hidden">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                                                                                </button>

                                                                                <!-- View button -->
                                                                                <button type="button"
                                                                                        class="btn btn-xs transition-all"
                                                                                        :class="memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'view' ? 'btn-info' : 'btn-ghost text-slate-400'"
                                                                                        @click="setPermission('{{ $member->id }}', '{{ $catKey }}', 'view')"
                                                                                        title="View Only">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                                </button>

                                                                                <!-- Edit button -->
                                                                                <button type="button"
                                                                                        class="btn btn-xs transition-all"
                                                                                        :class="memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'edit' ? 'btn-success' : 'btn-ghost text-slate-400'"
                                                                                        @click="setPermission('{{ $member->id }}', '{{ $catKey }}', 'edit')"
                                                                                        title="Can Edit">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                                                </button>

                                                                                <!-- Full Access button -->
                                                                                <button type="button"
                                                                                        class="btn btn-xs transition-all"
                                                                                        :class="memberPermissions['{{ $member->id }}']?.['{{ $catKey }}'] === 'full' ? 'btn-warning' : 'btn-ghost text-slate-400'"
                                                                                        @click="setPermission('{{ $member->id }}', '{{ $catKey }}', 'full')"
                                                                                        title="Full Access (Edit & Delete)">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-slate-500 bg-base-200/50 rounded-xl">
                                <p class="text-sm">No family members in this circle yet</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Step 4: Preview -->
        <div class="card bg-base-100 shadow-sm mb-6" x-show="selectedMembers.length > 0" x-cloak>
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-success text-success-content flex items-center justify-center text-sm font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    Access Preview
                </h3>

                <div class="bg-success/5 border border-success/20 rounded-xl p-4">
                    <p class="font-medium text-slate-700 mb-2">This person will be able to view:</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <template x-for="member in selectedMembers" :key="member">
                            <li class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M20 6 9 17l-5-5"/></svg>
                                <span><span class="font-medium" x-text="getMemberName(member)"></span>'s information</span>
                            </li>
                        </template>
                    </ul>
                    <p class="text-xs text-slate-500 mt-3 pt-3 border-t border-success/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        Collaborators receive view-only access to the selected family members.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('collaborators.create') }}" class="btn btn-ghost gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
            <button type="submit" class="btn btn-primary gap-2" :disabled="selectedMembers.length === 0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                Send Invitation
            </button>
        </div>
    </form>
</div>

<script>
function inviteForm() {
    // Define permission categories
    const permissionCategories = [
        @foreach($permissionCategories as $key => $cat)
            '{{ $key }}',
        @endforeach
    ];

    return {
        relationshipType: '{{ old('relationship_type', 'other') }}',
        selectedMembers: [],
        memberPermissions: {},
        memberNames: {
            @foreach($selectedCircles as $circle)
                @foreach($circle->members as $member)
                    '{{ $member->id }}': '{{ $member->first_name }}',
                @endforeach
            @endforeach
        },
        circleMembers: {
            @foreach($selectedCircles as $circle)
                {{ $circle->id }}: [@foreach($circle->members as $member)'{{ $member->id }}',@endforeach],
            @endforeach
        },

        init() {
            // Initialize permissions for all members with 'view' as default
            @foreach($selectedCircles as $circle)
                @foreach($circle->members as $member)
                    this.memberPermissions['{{ $member->id }}'] = {};
                    permissionCategories.forEach(cat => {
                        this.memberPermissions['{{ $member->id }}'][cat] = 'view';
                    });
                @endforeach
            @endforeach
        },

        getMemberName(id) {
            return this.memberNames[id] || 'Unknown';
        },

        isCircleFullySelected(circleId) {
            const members = this.circleMembers[circleId] || [];
            if (members.length === 0) return false;
            return members.every(id => this.selectedMembers.includes(id));
        },

        toggleCircle(circleId) {
            const members = this.circleMembers[circleId] || [];
            if (this.isCircleFullySelected(circleId)) {
                // Deselect all members in this circle
                this.selectedMembers = this.selectedMembers.filter(id => !members.includes(id));
            } else {
                // Select all members in this circle
                members.forEach(id => {
                    if (!this.selectedMembers.includes(id)) {
                        this.selectedMembers.push(id);
                    }
                });
            }
        },

        setPermission(memberId, category, level) {
            if (!this.memberPermissions[memberId]) {
                this.memberPermissions[memberId] = {};
            }
            this.memberPermissions[memberId][category] = level;
        },

        setAllPermissions(memberId, level) {
            if (!this.memberPermissions[memberId]) {
                this.memberPermissions[memberId] = {};
            }
            permissionCategories.forEach(cat => {
                this.memberPermissions[memberId][cat] = level;
            });
        },

        getMemberAccessLevel(memberId) {
            const perms = this.memberPermissions[memberId];
            if (!perms) return 'view';

            const levels = Object.values(perms);
            if (levels.length === 0) return 'view';

            // Check if all permissions are the same
            const firstLevel = levels[0];
            const allSame = levels.every(l => l === firstLevel);

            return allSame ? firstLevel : 'mixed';
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
