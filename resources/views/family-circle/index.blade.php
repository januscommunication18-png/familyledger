@extends('layouts.dashboard')

@section('title', 'Family Circle')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Family Circle</li>
@endsection

@section('page-title', 'Family Circle')
@section('page-description', 'Create and manage family circles to organize your family members.')

@section('content')
<div id="family-circle-app">
    @if(isset($pendingInvites) && $pendingInvites->count() > 0)
        <!-- Pending Invitations Alert -->
        <div class="mb-6">
            @foreach($pendingInvites as $invite)
                <div class="alert bg-amber-50 border border-amber-200 shadow-sm mb-3">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-800">You have a pending invitation!</h3>
                            <p class="text-sm text-slate-600">
                                <strong>{{ $invite->inviter->name ?? 'Someone' }}</strong> has invited you to access their family circle as <strong>{{ $invite->relationship_info['label'] ?? 'Collaborator' }}</strong>.
                                @if($invite->familyMembers->count() > 0)
                                    You'll have access to {{ $invite->familyMembers->count() }} family member{{ $invite->familyMembers->count() > 1 ? 's' : '' }}.
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <a href="{{ route('collaborator.accept', $invite->token) }}" class="btn btn-primary btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Accept
                            </a>
                            <a href="{{ route('collaborator.accept', $invite->token) }}" class="btn btn-ghost btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($circles->isEmpty() && $collaborations->count() == 0 && (!isset($pendingInvites) || $pendingInvites->count() == 0))
        <!-- Empty State - No circles and no collaborations -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-16">
                <div class="text-center max-w-lg mx-auto">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-3">Welcome to Family Circle</h2>
                    <p class="text-slate-500 mb-8">
                        Family Circles help you organize and manage your family members. Create a circle for your immediate family, extended family, or any group you'd like to track.
                    </p>
                    <button type="button" onclick="openCreateModal()" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                        Create Your First Family Circle
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Card Layout -->
        <div class="grid grid-cols-1 {{ $collaborations->count() > 0 ? 'lg:grid-cols-2' : '' }} gap-6">
            <!-- Card 1: My Family Circle -->
            <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">My Family Circle</h2>
                                <p class="text-sm text-slate-500">Your own family circles</p>
                            </div>
                        </div>
                        <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Create
                        </button>
                    </div>

                    @if($circles->count() > 0)
                        <div class="space-y-3">
                            @foreach($circles as $circle)
                                <a href="{{ route('family-circle.show', $circle) }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/50 transition-all group">
                                    <div class="w-12 h-12 shrink-0 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center overflow-hidden">
                                        @if($circle->cover_image)
                                            <img src="{{ Storage::disk('do_spaces')->url($circle->cover_image) }}" alt="{{ $circle->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-slate-900 group-hover:text-violet-600 transition-colors truncate">{{ $circle->name }}</h3>
                                        <div class="flex items-center gap-3 text-sm text-slate-500 mt-1">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                {{ $circle->members_count }} member{{ $circle->members_count != 1 ? 's' : '' }}
                                            </span>
                                            <span class="text-slate-300">•</span>
                                            <span>{{ $circle->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover:text-violet-500 transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 px-4 rounded-xl bg-slate-50 border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <h3 class="font-semibold text-slate-700 mb-2">No family circles yet</h3>
                            <p class="text-sm text-slate-500 mb-4">Create your first family circle to start organizing your family members.</p>
                            <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Create Family Circle
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 2: Shared With Me (only shown if there are collaborations) -->
            @if($collaborations->count() > 0)
            <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" x2="12" y1="2" y2="15"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">Shared With Me</h2>
                                <p class="text-sm text-slate-500">Family circles others have shared</p>
                            </div>
                        </div>
                        <span class="badge badge-success">{{ $collaborations->count() }} circle{{ $collaborations->count() != 1 ? 's' : '' }}</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($collaborations as $collab)
                            <a href="{{ route('family-circle.show', $collab['circle_id']) }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all group">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center overflow-hidden">
                                    @if(isset($collab['cover_image']) && $collab['cover_image'])
                                        <img src="{{ Storage::disk('do_spaces')->url($collab['cover_image']) }}" alt="{{ $collab['owner_name'] }}'s Family" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xl font-bold text-white">{{ strtoupper(substr($collab['owner_name'], 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600 transition-colors truncate">{{ $collab['owner_name'] }}'s Family</h3>
                                    <div class="flex items-center gap-3 text-sm text-slate-500 mt-1">
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            {{ $collab['family_members']->count() }} member{{ $collab['family_members']->count() != 1 ? 's' : '' }}
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        @php
                                            $roleColor = $collab['role_info']['color'] ?? 'ghost';
                                            $badgeClass = match($roleColor) {
                                                'error' => 'badge-error',
                                                'warning' => 'badge-warning',
                                                'info' => 'badge-info',
                                                'success' => 'badge-success',
                                                'secondary' => 'badge-secondary',
                                                default => 'badge-ghost',
                                            };
                                        @endphp
                                        <span class="badge badge-xs {{ $badgeClass }}">{{ $collab['role_info']['label'] ?? 'Viewer' }}</span>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover:text-emerald-500 transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif
</div>

<!-- Create Family Circle Modal -->
<div id="createCircleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <!-- Backdrop -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"></div>
    <!-- Modal -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; overflow-y: auto;">
        <div style="display: flex; min-height: 100%; align-items: center; justify-content: center; padding: 1rem;">
            <div style="position: relative; width: 100%; max-width: 28rem; background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Create Family Circle</h3>
                    <button type="button" onclick="closeCreateModal()" style="padding: 0.25rem; border-radius: 0.5rem; color: #94a3b8; background: transparent; border: none; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <!-- Body -->
                <form id="createCircleForm" action="{{ route('family-circle.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="px-6 py-5 space-y-5">
                        <!-- Cover Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Circle Photo <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <div id="createCircleImagePreview" class="w-20 h-20 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center overflow-hidden border-2 border-white shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <img id="createCircleImageImg" src="" alt="Preview" class="w-full h-full object-cover hidden">
                                </div>
                                <div class="flex-1">
                                    <label for="create_cover_image" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        Choose Photo
                                    </label>
                                    <input type="file" name="cover_image" id="create_cover_image" accept="image/*" class="hidden" onchange="previewCreateCircleImage(this)">
                                    <p class="text-xs text-slate-500 mt-1">JPG, PNG or GIF. Max 2MB.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Circle Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" placeholder="e.g., Johnson Family" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" required maxlength="255">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Description <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <textarea name="description" placeholder="A brief description of this family circle..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 h-24 resize-none" maxlength="1000"></textarea>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-violet-50 rounded-lg border border-violet-100">
                            <input type="checkbox" name="include_me" id="include_me" value="1" checked class="mt-0.5 h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                            <label for="include_me" class="flex-1 cursor-pointer">
                                <span class="block text-sm font-medium text-slate-700">{{ explode(' ', Auth::user()->name)[0] }}, would you like to include yourself in this circle?</span>
                            </label>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div style="display: flex; justify-content: flex-start; gap: 0.75rem; border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; background: #f8fafc; border-radius: 0 0 1rem 1rem;">
                        <button type="submit" style="padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 500; color: white; background: #7c3aed; border: none; border-radius: 0.5rem; cursor: pointer;">Create Family Circle</button>
                        <button type="button" onclick="closeCreateModal()" style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #334155; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewCreateCircleImage(input) {
    const preview = document.getElementById('createCircleImageImg');
    const container = document.getElementById('createCircleImagePreview');
    const defaultIcon = container.querySelector('svg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (defaultIcon) {
                defaultIcon.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openCreateModal() {
    const modal = document.getElementById('createCircleModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeCreateModal() {
    const modal = document.getElementById('createCircleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('createCircleModal');
    const backdrop = modal ? modal.querySelector('div:first-child') : null;

    if (backdrop) {
        // Close on backdrop click
        backdrop.addEventListener('click', function(e) {
            closeCreateModal();
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
        }
    });
});
</script>
@endsection
