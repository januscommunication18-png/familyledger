@extends('layouts.dashboard')

@section('title', 'Invite Details')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('collaborators.index') }}">Collaborators</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Pending Invite</li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Status Banner -->
    <div class="alert {{ $invite->status === 'pending' ? 'alert-info' : 'alert-warning' }} mb-6">
        @if($invite->status === 'pending')
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <div>
                <div class="font-medium">Invitation Pending</div>
                <p class="text-sm">Sent {{ $invite->created_at->diffForHumans() }} &bull; Expires {{ $invite->expires_at->diffForHumans() }}</p>
            </div>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/></svg>
            <div>
                <div class="font-medium">Invitation {{ ucfirst($invite->status) }}</div>
            </div>
        @endif
    </div>

    <!-- Invite Card -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-warning/20 text-warning rounded-full w-14">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/></svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $invite->full_name }}</h2>
                        <p class="text-slate-500">{{ $invite->email }}</p>
                    </div>
                </div>

                @if($invite->status === 'pending')
                    <div class="flex items-center gap-2">
                        <form action="{{ route('collaborators.invites.resend', $invite) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                                Resend
                            </button>
                        </form>

                        <form action="{{ route('collaborators.invites.revoke', $invite) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke this invitation?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-error btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                Revoke
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Invite Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Relationship</div>
                    <div class="font-medium text-slate-700">{{ $invite->relationship_info['label'] }}</div>
                </div>

                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Role</div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-{{ $roles[$invite->role]['color'] ?? 'ghost' }}">{{ $roles[$invite->role]['label'] ?? $invite->role }}</span>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Invited By</div>
                    <div class="font-medium text-slate-700">{{ $invite->inviter->name ?? 'Unknown' }}</div>
                </div>

                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Sent On</div>
                    <div class="font-medium text-slate-700">{{ $invite->created_at->format('M j, Y \a\t g:i A') }}</div>
                </div>

                @if($invite->resent_count > 0)
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Times Resent</div>
                        <div class="font-medium text-slate-700">{{ $invite->resent_count }}</div>
                    </div>
                @endif
            </div>

            @if($invite->message)
                <div class="mt-6 pt-6 border-t">
                    <div class="text-xs text-slate-400 uppercase tracking-wide mb-2">Personal Message</div>
                    <div class="bg-base-200/50 p-4 rounded-xl">
                        <p class="text-sm text-slate-600 italic">"{{ $invite->message }}"</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Family Members Access -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Family Members Will Have Access To
            </h3>

            <div class="space-y-3">
                @foreach($invite->familyMembers as $member)
                    @php
                        $permissions = json_decode($member->pivot->permissions ?? '{}', true) ?: [];
                    @endphp
                    <div class="border rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="avatar placeholder">
                                <div class="bg-secondary/10 text-secondary rounded-full w-10">
                                    <span>{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $member->first_name }} {{ $member->last_name }}</div>
                                <div class="text-xs text-slate-500">{{ $member->relationship ?? 'Family Member' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($permissions as $category => $level)
                                @if($level !== 'none')
                                    <div class="flex items-center gap-2 text-sm">
                                        @if($level === 'edit')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @endif
                                        <span class="text-slate-600">{{ $permissionCategories[$category]['label'] ?? $category }}</span>
                                        <span class="badge badge-xs {{ $level === 'edit' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($level) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Invite Link -->
    @if($invite->status === 'pending')
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Invitation Link
                </h3>

                <div class="flex gap-2">
                    <input type="text" class="input input-bordered flex-1 font-mono text-sm"
                           value="{{ route('collaborator.accept', $invite->token) }}" readonly id="invite-link">
                    <button type="button" class="btn btn-outline gap-2" onclick="copyInviteLink()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                        Copy
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-2">Share this link with {{ $invite->first_name ?? 'the invitee' }} to allow them to accept the invitation.</p>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div>
        <a href="{{ route('collaborators.index') }}" class="btn btn-ghost gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Collaborators
        </a>
    </div>
</div>

<script>
function copyInviteLink() {
    const input = document.getElementById('invite-link');
    input.select();
    document.execCommand('copy');

    // Show feedback
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Copied!';
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
    }, 2000);
}
</script>
@endsection
