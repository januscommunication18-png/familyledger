@extends('layouts.dashboard')

@section('page-name', 'Co-parenting')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Welcome Header --}}
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/><path d="m18 15-2-2"/><path d="m15 18-2-2"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 mb-3">Co-parenting Made Simple</h1>
        <p class="text-slate-500 max-w-lg mx-auto">
            Keep your co-parent informed and involved in your child's life. Share important information, coordinate schedules, and track expenses together.
        </p>
    </div>

    {{-- Video Placeholder --}}
    <div class="card bg-slate-900 mb-8">
        <div class="card-body aspect-video flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="white" stroke="none"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
                <p class="text-white/60 text-sm">Watch how co-parenting works</p>
            </div>
        </div>
    </div>

    {{-- Features Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Shared Calendar</h3>
                <p class="text-sm text-slate-500">Coordinate custody schedules, events, and appointments in one place.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(34 197 94)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Expense Tracking</h3>
                <p class="text-sm text-slate-500">Track and split child-related expenses fairly and transparently.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(168 85 247)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Secure Messaging</h3>
                <p class="text-sm text-slate-500">Communicate about your children without personal drama.</p>
            </div>
        </div>
    </div>

    {{-- Current Status --}}
    @if($hasCoparents || $hasPendingInvites)
    <div class="card bg-base-100 shadow-sm mb-8">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Co-parenting Status</h3>

            @if($hasCoparents)
            <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 text-green-700 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>You have active co-parenting arrangements.</span>
                <a href="{{ route('coparenting.index') }}" class="ml-auto btn btn-sm btn-ghost">View Dashboard</a>
            </div>
            @endif

            @if($hasPendingInvites)
            <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>You have pending co-parent invitations.</span>
                <a href="{{ route('coparenting.index') }}" class="ml-auto btn btn-sm btn-ghost">View Invites</a>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Minors Available for Co-parenting --}}
    @if($minors->count() > 0)
    <div class="card bg-base-100 shadow-sm mb-8">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Children Available for Co-parenting</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($minors as $minor)
                <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200">
                    @if($minor->profile_image_url)
                        <img src="{{ $minor->profile_image_url }}" alt="{{ $minor->full_name }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($minor->first_name ?? 'C', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800 truncate">{{ $minor->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ $minor->age }} years old</p>
                    </div>
                    @if($minor->co_parenting_enabled)
                        <span class="badge badge-success badge-sm">Active</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="card bg-amber-50 border border-amber-200 mb-8">
        <div class="card-body">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(217 119 6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div>
                    <h4 class="font-semibold text-amber-800">No Children Added</h4>
                    <p class="text-sm text-amber-700">You need to add children to your family circle before setting up co-parenting.</p>
                    <a href="{{ route('family-circle.index') }}" class="btn btn-sm btn-outline btn-warning mt-3">Go to Family Circle</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- CTA Button --}}
    <div class="text-center">
        <a href="{{ route('coparenting.invite') }}" class="btn btn-primary btn-lg gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Invite Co-parent
        </a>
        <p class="text-sm text-slate-500 mt-3">Start sharing important information with your co-parent today.</p>
    </div>
</div>
@endsection
