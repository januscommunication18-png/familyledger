@extends('layouts.dashboard')

@section('title', 'Collaborators')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Collaborators</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Collaborators</h2>
            <p class="text-sm text-slate-500">Manage who can access your family's information</p>
        </div>
        <a href="{{ route('collaborators.create') }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Invite Collaborator
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['total_collaborators'] }}</div>
                        <div class="text-xs text-slate-500">Active Collaborators</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['pending_invites'] }}</div>
                        <div class="text-xs text-slate-500">Pending Invites</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['total_invited'] }}</div>
                        <div class="text-xs text-slate-500">Total Invited</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Invites -->
    @if($pendingInvites->count())
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Pending Invitations
                </h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invitee</th>
                                <th>Relationship</th>
                                <th>Role</th>
                                <th>Access To</th>
                                <th>Expires</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingInvites as $invite)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary/10 text-primary rounded-full w-10">
                                                    <span class="text-sm">{{ strtoupper(substr($invite->first_name ?: $invite->email, 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $invite->full_name }}</div>
                                                <div class="text-xs text-slate-500">{{ $invite->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm">{{ $invite->relationship_info['label'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $invite->role_info['color'] }} badge-sm">
                                            {{ $invite->role_info['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($invite->familyMembers->take(3) as $member)
                                                <span class="badge badge-ghost badge-sm">{{ $member->first_name }}</span>
                                            @endforeach
                                            @if($invite->familyMembers->count() > 3)
                                                <span class="badge badge-ghost badge-sm">+{{ $invite->familyMembers->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm {{ $invite->expires_at->diffInDays() < 2 ? 'text-error' : 'text-slate-500' }}">
                                            {{ $invite->expires_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <form method="POST" action="{{ route('collaborators.invites.resend', $invite) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-xs" title="Resend">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('collaborators.invites.revoke', $invite) }}" class="inline"
                                                  onsubmit="return confirm('Revoke this invitation?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error" title="Revoke">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Active Collaborators -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Active Collaborators
            </h3>

            @if($collaborators->where('is_active', true)->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($collaborators->where('is_active', true) as $collaborator)
                        <div class="card bg-base-200/50 hover:shadow-md transition-shadow">
                            <div class="card-body p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-12">
                                                <span>{{ strtoupper(substr($collaborator->user->name ?? 'U', 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ $collaborator->display_name }}</div>
                                            <div class="text-xs text-slate-500">{{ $collaborator->email }}</div>
                                        </div>
                                    </div>
                                    <div class="dropdown dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                        </button>
                                        <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden w-40">
                                            <li><a href="{{ route('collaborators.show', $collaborator) }}" class="dropdown-item">View Details</a></li>
                                            <li><a href="{{ route('collaborators.edit', $collaborator) }}" class="dropdown-item">Edit Permissions</a></li>
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('collaborators.deactivate', $collaborator) }}"
                                                      onsubmit="return confirm('Deactivate this collaborator?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-error w-full text-left">Deactivate</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mt-3">
                                    <span class="badge badge-sm">{{ $collaborator->relationship_info['label'] }}</span>
                                    <span class="badge badge-{{ $collaborator->role_info['color'] }} badge-sm">
                                        {{ $collaborator->role_info['label'] }}
                                    </span>
                                </div>

                                <div class="mt-3 pt-3 border-t border-base-300">
                                    <div class="text-xs text-slate-500 mb-1">Can access:</div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($collaborator->familyMembers->take(4) as $member)
                                            <span class="badge badge-ghost badge-sm">{{ $member->first_name }}</span>
                                        @endforeach
                                        @if($collaborator->familyMembers->count() > 4)
                                            <span class="badge badge-ghost badge-sm">+{{ $collaborator->familyMembers->count() - 4 }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-xs text-slate-400 mt-2">
                                    Joined {{ $collaborator->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto rounded-full bg-base-200 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-700">No collaborators yet</h3>
                    <p class="text-slate-500 mb-4">Invite family members or trusted people to help manage your family information</p>
                    <a href="{{ route('collaborators.create') }}" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        Invite Your First Collaborator
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Inactive Collaborators -->
    @if($collaborators->where('is_active', false)->count())
        <div class="card bg-base-100 shadow-sm opacity-75">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-slate-600 flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                    Inactive Collaborators
                </h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Deactivated</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collaborators->where('is_active', false) as $collaborator)
                                <tr class="opacity-60">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-slate-200 text-slate-500 rounded-full w-10">
                                                    <span>{{ strtoupper(substr($collaborator->user->name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $collaborator->display_name }}</div>
                                                <div class="text-xs text-slate-500">{{ $collaborator->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $collaborator->role_info['label'] }}</td>
                                    <td>{{ $collaborator->deactivated_at?->diffForHumans() ?? 'Unknown' }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('collaborators.activate', $collaborator) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-ghost btn-xs">Reactivate</button>
                                        </form>
                                        <form method="POST" action="{{ route('collaborators.destroy', $collaborator) }}" class="inline"
                                              onsubmit="return confirm('Remove this collaborator permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
