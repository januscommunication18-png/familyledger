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
<div id="family-circle-space">
    <!-- Circle Header -->
    <div class="card bg-gradient-to-r from-violet-600 to-purple-600 text-white mb-6">
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
            </div>
        </div>
    </div>

    <!-- Family Members Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            // Check if the owner included themselves in this circle
            $selfMember = $circle->members->where('relationship', 'self')->where('linked_user_id', auth()->id())->first();
            $owner = auth()->user();
            $ownerNameParts = explode(' ', $owner->name, 2);
            $ownerFirstName = $ownerNameParts[0];
            $ownerLastName = $ownerNameParts[1] ?? '';
            $ownerAge = $owner->date_of_birth ? \Carbon\Carbon::parse($owner->date_of_birth)->age : null;
        @endphp

        <!-- Owner Self Card (only shown if owner included themselves) -->
        @if($selfMember)
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow border-2 border-violet-200">
            <div class="card-body">
                <!-- Owner Header -->
                <div class="flex items-start gap-4">
                    <div class="avatar">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600">
                            @if($owner->profile_image)
                                <img src="{{ Storage::disk('do_spaces')->url($owner->profile_image) }}" alt="{{ $owner->name }}" class="object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-xl font-bold text-white">{{ strtoupper(substr($ownerFirstName, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 truncate flex items-center gap-2">
                            {{ $owner->name }}
                            <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                        </h3>
                        <p class="text-sm text-slate-500">Self</p>
                        @if($ownerAge)
                            <p class="text-sm text-slate-400">{{ $ownerAge }} years old</p>
                        @endif
                    </div>
                </div>

                <!-- Owner Info -->
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                    @if($owner->email)
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span class="truncate">{{ $owner->email }}</span>
                        </div>
                    @endif
                    @if($owner->phone)
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span>{{ $owner->phone }}</span>
                        </div>
                    @endif
                    @if($owner->date_of_birth)
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                            <span>{{ \Carbon\Carbon::parse($owner->date_of_birth)->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Owner Badge -->
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="badge badge-primary badge-sm">Account Owner</span>
                </div>

                <!-- Quick Actions -->
                <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                    <a href="{{ route('family-circle.owner.show', $circle) }}" class="btn btn-sm btn-ghost flex-1">View</a>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline btn-primary flex-1">Documents</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Other Family Members (excluding self to avoid duplicate) -->
        @foreach($circle->members->where('relationship', '!=', 'self') as $member)
                <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="card-body">
                        <!-- Member Header -->
                        <div class="flex items-start gap-4">
                            <div class="avatar">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-500">
                                    @if($member->profile_image_url)
                                        <img src="{{ $member->profile_image_url }}" alt="{{ $member->full_name }}" class="object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <span class="text-xl font-bold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900 truncate flex items-center gap-2">
                                    {{ $member->full_name }}
                                    @if($member->linked_user_id === auth()->id())
                                        <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-slate-500">{{ $member->relationship_name }}</p>
                                <p class="text-sm text-slate-400">{{ $member->age }} years old</p>
                            </div>
                            <div class="dropdown dropdown-end">
                                <button tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden w-48 p-2 bg-white shadow-xl border border-slate-200 rounded-xl">
                                    <li>
                                        <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('family-circle.member.edit', [$circle, $member]) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                            Edit Member
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('member.documents.index', $member) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                            Document Vault
                                        </a>
                                    </li>
                                    @if($member->is_minor && $member->co_parenting_enabled)
                                        <li>
                                            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" x2="12" y1="2" y2="15"/></svg>
                                                Co-Parenting
                                            </a>
                                        </li>
                                    @endif
                                    <li class="border-t border-slate-100 mt-1 pt-1">
                                        <form action="{{ route('family-circle.member.destroy', [$circle, $member]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this family member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                Remove
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Member Info -->
                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                            @if($member->email)
                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <span class="truncate">{{ $member->email }}</span>
                                </div>
                            @endif
                            @if($member->phone)
                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>{{ $member->phone_country_code ?? '' }}{{ $member->phone }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                <span>{{ $member->date_of_birth->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="mt-4 flex gap-2 flex-wrap">
                            @if($member->is_minor)
                                <span class="badge badge-info badge-sm">Minor</span>
                            @endif
                            @if($member->co_parenting_enabled)
                                <span class="badge badge-warning badge-sm">Co-Parenting</span>
                            @endif
                            @if($member->documents->count() > 0)
                                <span class="badge badge-ghost badge-sm">{{ $member->documents->count() }} docs</span>
                            @endif
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-sm btn-ghost flex-1">View</a>
                            <a href="{{ route('member.documents.index', $member) }}" class="btn btn-sm btn-outline btn-primary flex-1">Documents</a>
                        </div>
                    </div>
                </div>
        @endforeach
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
});
</script>
@endpush
