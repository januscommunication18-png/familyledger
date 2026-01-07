@extends('layouts.dashboard')

@section('page-name', 'Share Budget')

@section('content')
<div class="p-4 lg:p-6 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Share Budget</h1>
        <p class="text-sm text-slate-500">Share "{{ $budget->name }}" with family members</p>
    </div>

    {{-- Share by Email (Most Reliable) --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Share by Email</h3>
            <p class="text-sm text-slate-500 mb-4">Enter the email address of a registered user to share this budget with them.</p>

            <form action="{{ route('expenses.share.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email Address</span></label>
                        <input type="email" name="email" class="input input-bordered" placeholder="user@example.com" required>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Permission Level</span></label>
                        <select name="permission" class="select select-bordered" required>
                            @foreach($permissions as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }} - {{ $info['description'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Share Budget</button>
            </form>
        </div>
    </div>

    {{-- Share with Existing Members --}}
    @php
        $hasFamilyMembers = $familyCircles->sum(fn($c) => $c->members->count()) > 0;
    @endphp
    @if($collaborators->count() > 0 || $hasFamilyMembers)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Or Select from Family Members</h3>

            <form action="{{ route('expenses.share.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Family Member</span></label>
                        <select name="share_with" class="select select-bordered" required>
                            <option value="">Select a member...</option>
                            @if($collaborators->count() > 0)
                            <optgroup label="Collaborators">
                                @foreach($collaborators as $collaborator)
                                <option value="collaborator:{{ $collaborator->id }}">
                                    {{ $collaborator->user?->name ?? $collaborator->email }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endif
                            @foreach($familyCircles as $circle)
                                @if($circle->members->count() > 0)
                                <optgroup label="{{ $circle->name }}">
                                    @foreach($circle->members as $member)
                                    <option value="family_member:{{ $member->id }}">
                                        {{ $member->full_name }} ({{ $member->relationship_name }})
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Permission Level</span></label>
                        <select name="permission" class="select select-bordered" required>
                            @foreach($permissions as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }} - {{ $info['description'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Share Budget</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Current Shares --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">People with Access</h3>

            @if($shares->count() > 0)
            <div class="space-y-3">
                @foreach($shares as $share)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-white">
                            {{ strtoupper(substr($share->getSharedUserName(), 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800 truncate">{{ $share->getSharedUserName() }}</p>
                        <p class="text-xs text-slate-500">{{ $share->permission_label }} - {{ $share->permission_description }}</p>
                    </div>
                    <form action="{{ route('expenses.share.delete', $share) }}" method="POST" onsubmit="return confirm('Remove access for this person?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="text-slate-600">This budget is not shared with anyone yet.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Permission Levels Guide --}}
    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Permission Levels</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th class="text-center">View</th>
                            <th class="text-center">Add Transactions</th>
                            <th class="text-center">Edit Categories</th>
                            <th class="text-center">Delete Budget</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $key => $info)
                        <tr>
                            <td class="font-medium">{{ $info['label'] }}</td>
                            <td class="text-center">
                                @if($info['can_view'])
                                <span class="text-emerald-600">Yes</span>
                                @else
                                <span class="text-slate-400">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($info['can_add_transactions'])
                                <span class="text-emerald-600">Yes</span>
                                @else
                                <span class="text-slate-400">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($info['can_edit_categories'])
                                <span class="text-emerald-600">Yes</span>
                                @else
                                <span class="text-slate-400">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($info['can_delete_budget'])
                                <span class="text-emerald-600">Yes</span>
                                @else
                                <span class="text-slate-400">No</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
