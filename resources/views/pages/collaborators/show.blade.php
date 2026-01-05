@extends('layouts.dashboard')

@section('title', 'Collaborator Details')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('collaborators.index') }}">Collaborators</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $collaborator->display_name }}</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header Card -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-16">
                            <span class="text-2xl">{{ strtoupper(substr($collaborator->display_name, 0, 1)) }}</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">{{ $collaborator->display_name }}</h2>
                        <p class="text-slate-500">{{ $collaborator->email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-{{ $collaborator->role_info['color'] }}">{{ $collaborator->role_info['label'] }}</span>
                            <span class="text-sm text-slate-400">{{ $collaborator->relationship_info['label'] }}</span>
                            @if(!$collaborator->is_active)
                                <span class="badge badge-error badge-outline">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('collaborators.edit', $collaborator) }}" class="btn btn-outline btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        Edit
                    </a>

                    @if($collaborator->is_active)
                        <form action="{{ route('collaborators.deactivate', $collaborator) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-warning btn-sm gap-2" onclick="return confirm('Are you sure you want to deactivate this collaborator?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                Deactivate
                            </button>
                        </form>
                    @else
                        <form action="{{ route('collaborators.activate', $collaborator) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-success btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Reactivate
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Access Summary -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Family Member Access
            </h3>

            @if(count($accessSummary) > 0)
                <div class="space-y-4">
                    @foreach($accessSummary as $item)
                        <div class="border rounded-xl p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="avatar placeholder">
                                    <div class="bg-secondary/10 text-secondary rounded-full w-10">
                                        <span>{{ strtoupper(substr($item['member']->first_name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $item['member']->first_name }} {{ $item['member']->last_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $item['member']->relationship ?? 'Family Member' }}</div>
                                </div>
                            </div>

                            @if(count($item['access']) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($item['access'] as $access)
                                        <div class="flex items-center gap-2 text-sm">
                                            @if($access['level'] === 'edit')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            @endif
                                            <span class="text-slate-600">{{ $access['label'] }}</span>
                                            <span class="badge badge-xs {{ $access['level'] === 'edit' ? 'badge-success' : 'badge-info' }}">{{ $access['level_label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No specific permissions set</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500">No family members assigned to this collaborator.</p>
            @endif
        </div>
    </div>

    <!-- Activity & Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Timeline -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-800 mb-4">Activity</h3>

                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-700">Joined</div>
                            <div class="text-xs text-slate-500">{{ $collaborator->created_at->format('M j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="m22 2-7 20-4-9-9-4Z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-700">Invited by {{ $collaborator->inviter->name ?? 'Unknown' }}</div>
                            @if($collaborator->invite)
                                <div class="text-xs text-slate-500">{{ $collaborator->invite->created_at->format('M j, Y') }}</div>
                            @endif
                        </div>
                    </div>

                    @if($collaborator->deactivated_at)
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-error/10 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-700">Deactivated</div>
                                <div class="text-xs text-slate-500">{{ $collaborator->deactivated_at->format('M j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="font-semibold text-slate-800 mb-4">Notes</h3>

                @if($collaborator->notes)
                    <p class="text-sm text-slate-600">{{ $collaborator->notes }}</p>
                @else
                    <p class="text-sm text-slate-500 italic">No notes added</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card bg-base-100 shadow-sm border border-error/20">
        <div class="card-body">
            <h3 class="font-semibold text-error mb-4">Danger Zone</h3>

            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-slate-700">Remove Collaborator</div>
                    <p class="text-sm text-slate-500">Permanently remove this person's access. This action cannot be undone.</p>
                </div>

                <form action="{{ route('collaborators.destroy', $collaborator) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently remove this collaborator? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-outline btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('collaborators.index') }}" class="btn btn-ghost gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Collaborators
        </a>
    </div>
</div>
@endsection
