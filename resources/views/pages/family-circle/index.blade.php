@extends('layouts.dashboard')

@section('title', 'Family Circle')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Family Circle</li>
@endsection

@section('page-title', 'Family Circle')
@section('page-description', 'Manage your family members and their access permissions.')

@section('content')
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="card-title">Family Members</h2>
                <p class="text-sm text-base-content/60">View and manage who has access to your family ledger</p>
            </div>
            <button class="btn btn-primary">
                <span class="icon-[tabler--user-plus] size-5"></span>
                Invite Member
            </button>
        </div>

        <!-- Family Members List -->
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="w-10 rounded-full bg-primary text-primary-content">
                                        <span>{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ auth()->user()->name }}</div>
                                    <div class="text-sm text-base-content/60">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ auth()->user()->role_name ?? 'Owner' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-success gap-1">
                                <span class="icon-[tabler--check] size-3"></span>
                                Active
                            </span>
                        </td>
                        <td>{{ auth()->user()->created_at?->format('M d, Y') ?? 'Today' }}</td>
                        <td class="text-right">
                            <span class="text-base-content/40 text-sm">You</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
