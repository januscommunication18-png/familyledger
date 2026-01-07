@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-name', 'Home')

@section('page-title', 'Welcome, ' . (auth()->user()->name ?? 'User') . '!')
@section('page-description', 'Your family\'s important information, all in one place.')

@section('content')
@php
    // Check for pending co-parent invites for the current user
    $pendingCoparentInvites = \App\Models\CollaboratorInvite::where('email', auth()->user()->email)
        ->coparentInvites()
        ->pending()
        ->with(['inviter', 'familyMembers'])
        ->get();
@endphp

{{-- Pending Co-parent Invitations --}}
@if($pendingCoparentInvites->count() > 0)
<div class="mb-6">
    <div class="card bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 rounded-full bg-pink-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-pink-800">Co-parenting Invitations</h3>
                    <p class="text-sm text-pink-600">You have {{ $pendingCoparentInvites->count() }} pending co-parent {{ Str::plural('invitation', $pendingCoparentInvites->count()) }}</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($pendingCoparentInvites as $invite)
                <div class="flex items-center justify-between p-3 rounded-lg bg-white/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($invite->inviter->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ $invite->inviter->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $invite->familyMembers->count() }} {{ Str::plural('child', $invite->familyMembers->count()) }}:
                                {{ $invite->familyMembers->pluck('first_name')->take(2)->join(', ') }}{{ $invite->familyMembers->count() > 2 ? '...' : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('collaborator.accept', $invite->token) }}" class="btn btn-sm btn-primary">View Invite</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60">Documents</p>
                    <p class="text-2xl font-bold mt-1">0</p>
                </div>
                <div class="p-3 rounded-full bg-primary/10">
                    <span class="icon-[tabler--file-text] size-6 text-primary"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60">Family Members</p>
                    <p class="text-2xl font-bold mt-1">1</p>
                </div>
                <div class="p-3 rounded-full bg-secondary/10">
                    <span class="icon-[tabler--users] size-6 text-secondary"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60">Pending Tasks</p>
                    <p class="text-2xl font-bold mt-1">0</p>
                </div>
                <div class="p-3 rounded-full bg-accent/10">
                    <span class="icon-[tabler--checklist] size-6 text-accent"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60">Reminders</p>
                    <p class="text-2xl font-bold mt-1">0</p>
                </div>
                <div class="p-3 rounded-full bg-warning/10">
                    <span class="icon-[tabler--bell] size-6 text-warning"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Recent Activity -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Recent Activity</h2>
                    <a href="#" class="btn btn-ghost btn-sm">View All</a>
                </div>
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                        <div class="p-2 rounded-full bg-primary/10">
                            <span class="icon-[tabler--user-plus] size-4 text-primary"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium">Account created</p>
                            <p class="text-xs text-base-content/60">Welcome to Family Ledger! Start by adding your family members.</p>
                            <p class="text-xs text-base-content/40 mt-1">Just now</p>
                        </div>
                    </div>

                    <div class="text-center py-8 text-base-content/60">
                        <span class="icon-[tabler--clock] size-12 opacity-30"></span>
                        <p class="mt-2">No recent activity yet</p>
                        <p class="text-sm">Your activity history will appear here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="#" class="flex flex-col items-center p-4 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors text-center group">
                        <div class="p-3 rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors mb-2">
                            <span class="icon-[tabler--file-plus] size-6 text-primary"></span>
                        </div>
                        <span class="text-sm font-medium">Add Document</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors text-center group">
                        <div class="p-3 rounded-full bg-secondary/10 group-hover:bg-secondary/20 transition-colors mb-2">
                            <span class="icon-[tabler--user-plus] size-6 text-secondary"></span>
                        </div>
                        <span class="text-sm font-medium">Invite Member</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors text-center group">
                        <div class="p-3 rounded-full bg-accent/10 group-hover:bg-accent/20 transition-colors mb-2">
                            <span class="icon-[tabler--calendar-plus] size-6 text-accent"></span>
                        </div>
                        <span class="text-sm font-medium">Add Reminder</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors text-center group">
                        <div class="p-3 rounded-full bg-warning/10 group-hover:bg-warning/20 transition-colors mb-2">
                            <span class="icon-[tabler--receipt] size-6 text-warning"></span>
                        </div>
                        <span class="text-sm font-medium">Track Expense</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="space-y-6">
        <!-- Family Overview -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Family Circle</h2>
                <div class="flex flex-wrap gap-2 mb-4">
                    <div class="avatar placeholder">
                        <div class="w-10 rounded-full bg-primary text-primary-content">
                            <span class="text-sm">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                    </div>
                    <button class="avatar placeholder">
                        <div class="w-10 rounded-full border-2 border-dashed border-base-content/20 hover:border-primary transition-colors">
                            <span class="icon-[tabler--plus] size-4 text-base-content/40"></span>
                        </div>
                    </button>
                </div>
                <a href="#" class="btn btn-outline btn-sm btn-block">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Invite Family Member
                </a>
            </div>
        </div>

        <!-- Upcoming Reminders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Upcoming</h2>
                    <a href="#" class="btn btn-ghost btn-xs">View All</a>
                </div>
                <div class="text-center py-6 text-base-content/60">
                    <span class="icon-[tabler--calendar] size-10 opacity-30"></span>
                    <p class="mt-2 text-sm">No upcoming reminders</p>
                    <a href="#" class="btn btn-primary btn-sm mt-3">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Reminder
                    </a>
                </div>
            </div>
        </div>

        <!-- Storage Usage -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Storage</h2>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Used</span>
                        <span class="font-medium">0 MB / 5 GB</span>
                    </div>
                    <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                    <p class="text-xs text-base-content/60">5 GB free storage available</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
