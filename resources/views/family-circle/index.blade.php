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
        <!-- Empty State -->
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
    @endif

    @if($circles->count() > 0)
        <!-- Family Circles Grid -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-slate-500">You have {{ $circles->count() }} family circle{{ $circles->count() > 1 ? 's' : '' }}</p>
            </div>
            <button type="button" onclick="openCreateModal()" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                Create Family Circle
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($circles as $circle)
                <a href="{{ route('family-circle.show', $circle) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 shrink-0 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900 group-hover:text-violet-600 transition-colors truncate">{{ $circle->name }}</h3>
                                @if($circle->description)
                                    <p class="text-sm text-slate-500 line-clamp-2 mt-1">{{ $circle->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <span>{{ $circle->members_count }} member{{ $circle->members_count != 1 ? 's' : '' }}</span>
                            </div>
                            <div class="text-sm text-slate-400">
                                {{ $circle->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @if($collaborations->count() > 0)
        <!-- Shared With Me Section -->
        <div class="mt-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" x2="12" y1="2" y2="15"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Shared With Me</h2>
                    <p class="text-sm text-slate-500">Family members others have shared with you</p>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($collaborations as $collab)
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-emerald-100 text-emerald-700 rounded-full w-10">
                                            <span class="text-sm font-semibold">{{ strtoupper(substr($collab['owner_name'], 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">{{ $collab['owner_name'] }}'s Family</h3>
                                        <p class="text-xs text-slate-500">You're their {{ $collab['relationship'] }}</p>
                                    </div>
                                </div>
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
                                <span class="badge {{ $badgeClass }}">{{ $collab['role_info']['label'] ?? 'Viewer' }}</span>
                            </div>

                            @if($collab['family_members']->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($collab['family_members'] as $member)
                                        <a href="{{ route('family-circle.member.show', ['familyCircle' => $member->family_circle_id, 'member' => $member->id]) }}"
                                           class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all group">
                                            <div class="avatar placeholder">
                                                <div class="bg-slate-100 text-slate-600 rounded-full w-10 group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-colors">
                                                    <span>{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-slate-800 truncate group-hover:text-emerald-700 transition-colors">{{ $member->first_name }} {{ $member->last_name }}</div>
                                                <div class="text-xs text-slate-500">{{ $member->relationship ?? 'Family Member' }}</div>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover:text-emerald-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500 text-center py-4">No family members shared yet</p>
                            @endif

                            <div class="text-xs text-slate-400 mt-3 pt-3 border-t">
                                Joined {{ $collab['joined_at']->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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
                <form id="createCircleForm" action="{{ route('family-circle.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-5">
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
