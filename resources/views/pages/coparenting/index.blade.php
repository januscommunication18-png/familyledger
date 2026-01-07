@extends('layouts.dashboard')

@section('page-name', 'Co-parenting Dashboard')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Co-parenting Dashboard</h1>
            <p class="text-slate-500">Manage your co-parenting arrangements and shared children.</p>
        </div>
        <a href="{{ route('coparenting.invite') }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Invite Co-parent
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $children->count() }}</p>
                        <p class="text-sm text-slate-500">Children</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $coparents->count() }}</p>
                        <p class="text-sm text-slate-500">Co-parents</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(245 158 11)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $pendingInvites->count() }}</p>
                        <p class="text-sm text-slate-500">Pending Invites</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Children with Co-parents Section --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 text-lg">Children & Co-parents</h3>
                <a href="{{ route('coparenting.children') }}" class="text-sm text-primary hover:underline">Manage All</a>
            </div>

            @forelse($children as $child)
            <div class="border border-slate-200 rounded-xl p-4 mb-4 last:mb-0 hover:border-slate-300 transition-colors">
                {{-- Child Info --}}
                <div class="flex items-center gap-3 mb-3">
                    @if($child->profile_image_url)
                        <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-pink-200">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center ring-2 ring-pink-200">
                            <span class="text-xl font-bold text-white">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 text-lg truncate">{{ $child->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $child->age }} years old</p>
                    </div>
                    <a href="{{ route('coparenting.children.show', $child) }}" class="btn btn-sm btn-ghost text-primary">
                        View Details
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>

                {{-- Co-parents for this child --}}
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Co-parents</p>
                    <div class="flex flex-wrap gap-2">
                        {{-- Current User as Owner/Parent --}}
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-100 border border-violet-200">
                            <div class="w-6 h-6 rounded-full bg-violet-500 flex items-center justify-center">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}</span>
                            </div>
                            <span class="text-sm font-medium text-violet-700">{{ $currentUser->name }}</span>
                            <span class="text-xs text-violet-500">(You)</span>
                        </div>

                        {{-- Other Co-parents --}}
                        @forelse($child->coparents as $coparent)
                        @php
                            $roleInfo = $coparent->parent_role_info;
                            $roleLabel = $coparent->parent_role_label;
                            $bgColor = match($coparent->parent_role) {
                                'mother' => 'bg-pink-100 border-pink-200',
                                'father' => 'bg-blue-100 border-blue-200',
                                default => 'bg-emerald-100 border-emerald-200',
                            };
                            $avatarColor = match($coparent->parent_role) {
                                'mother' => 'bg-pink-500',
                                'father' => 'bg-blue-500',
                                default => 'bg-emerald-500',
                            };
                            $textColor = match($coparent->parent_role) {
                                'mother' => 'text-pink-700',
                                'father' => 'text-blue-700',
                                default => 'text-emerald-700',
                            };
                            $badgeColor = match($coparent->parent_role) {
                                'mother' => 'text-pink-500',
                                'father' => 'text-blue-500',
                                default => 'text-emerald-500',
                            };
                        @endphp
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full {{ $bgColor }} border">
                            @if($coparent->avatar_url)
                                <img src="{{ $coparent->avatar_url }}" alt="{{ $coparent->display_name }}" class="w-6 h-6 rounded-full object-cover">
                            @else
                                <div class="w-6 h-6 rounded-full {{ $avatarColor }} flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">{{ strtoupper(substr($coparent->display_name ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <span class="text-sm font-medium {{ $textColor }}">{{ $coparent->display_name }}</span>
                            <span class="text-xs {{ $badgeColor }}">({{ $roleLabel }})</span>
                        </div>
                        @empty
                        <span class="text-sm text-slate-400 italic">No other co-parents yet</span>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                </div>
                <p class="text-slate-500 mb-3">No children in co-parenting yet</p>
                <a href="{{ route('coparenting.invite') }}" class="btn btn-sm btn-primary">Add Children</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pending Invites Section --}}
    @if($pendingInvites->count() > 0)
    <div class="card bg-amber-50 border border-amber-200 shadow-sm">
        <div class="card-body">
            <h3 class="font-semibold text-amber-800 mb-4">Pending Invitations</h3>

            @foreach($pendingInvites as $invite)
            @php
                $roleLabel = \App\Models\Collaborator::PARENT_ROLES[$invite->parent_role]['label'] ?? 'Co-Parent';
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg bg-white/80 mb-2 last:mb-0">
                <div class="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(217 119 6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-800 truncate">
                        {{ $invite->full_name ?: $invite->email }}
                        <span class="text-sm text-amber-600">({{ $roleLabel }})</span>
                    </p>
                    <p class="text-sm text-slate-500">
                        {{ $invite->familyMembers->pluck('first_name')->join(', ') }}
                        &bull; Expires {{ $invite->expires_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <form action="{{ route('coparenting.invite.resend', $invite) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ghost text-amber-700 hover:bg-amber-100 gap-1" title="Resend invitation email">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/></svg>
                            Resend
                        </button>
                    </form>
                    <span class="badge badge-warning badge-sm">Pending</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
