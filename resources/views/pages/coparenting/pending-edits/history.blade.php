@extends('layouts.dashboard')

@section('page-name', 'Edit History')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('coparenting.pending-edits.index') }}" class="text-slate-500 hover:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800">Edit Request History</h1>
            </div>
            <p class="text-slate-500">View previously reviewed edit requests.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')

            <a href="{{ route('coparenting.pending-edits.index') }}" class="btn btn-primary btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                View Pending
            </a>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="tabs tabs-boxed bg-base-100 p-1 mb-6 w-fit">
        <a href="{{ route('coparenting.pending-edits.history', ['status' => 'all']) }}"
           class="tab {{ $status === 'all' ? 'tab-active' : '' }}">All</a>
        <a href="{{ route('coparenting.pending-edits.history', ['status' => 'approved']) }}"
           class="tab {{ $status === 'approved' ? 'tab-active' : '' }}">
           <span class="w-2 h-2 rounded-full bg-success mr-1.5"></span>
           Approved
        </a>
        <a href="{{ route('coparenting.pending-edits.history', ['status' => 'rejected']) }}"
           class="tab {{ $status === 'rejected' ? 'tab-active' : '' }}">
           <span class="w-2 h-2 rounded-full bg-error mr-1.5"></span>
           Rejected
        </a>
    </div>

    @if($edits->isEmpty())
        {{-- Empty State --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">No History Yet</h3>
                <p class="text-slate-500 max-w-md mx-auto">No edit requests have been reviewed yet. They will appear here once edit requests are approved or rejected.</p>
            </div>
        </div>
    @else
        {{-- History Table --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Child</th>
                                <th>Field</th>
                                <th>Change</th>
                                <th>Requested By</th>
                                <th>Reviewed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($edits as $edit)
                            <tr>
                                <td>
                                    @if($edit->status === 'approved')
                                        <span class="badge badge-success badge-sm gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Approved
                                        </span>
                                    @else
                                        <span class="badge badge-error badge-sm gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($edit->familyMember->profile_image_url)
                                            <img src="{{ $edit->familyMember->profile_image_url }}" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($edit->familyMember->first_name ?? 'C', 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span class="font-medium">{{ $edit->familyMember->full_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-800">{{ $edit->field_label }}</div>
                                    <div class="text-xs text-slate-500">{{ $edit->editable_type_label }}</div>
                                </td>
                                <td>
                                    @if($edit->is_create)
                                        <span class="text-emerald-600">New record created</span>
                                    @elseif($edit->is_delete)
                                        <span class="text-error">Record deleted</span>
                                    @else
                                        <div class="text-sm">
                                            <span class="text-slate-500 line-through">{{ $edit->formatted_old_value }}</span>
                                            <span class="mx-1">→</span>
                                            <span class="text-emerald-600">{{ $edit->formatted_new_value }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                                            <span class="text-[10px] font-bold text-white">{{ strtoupper(substr($edit->requester->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm">{{ $edit->requester->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-slate-400">{{ $edit->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        <div>by {{ $edit->reviewer->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-slate-400">{{ $edit->reviewed_at?->diffForHumans() }}</div>
                                    </div>
                                    @if($edit->review_notes)
                                        @if($edit->status === 'rejected')
                                            <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                                                <div class="flex items-start gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                                    <div>
                                                        <div class="text-xs font-medium text-red-700">Rejection Reason:</div>
                                                        <div class="text-xs text-red-600">{{ $edit->review_notes }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-xs text-slate-500 mt-1 italic">"{{ $edit->review_notes }}"</div>
                                        @endif
                                    @elseif($edit->status === 'rejected')
                                        <div class="text-xs text-slate-400 mt-1 italic">No reason provided</div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($edits->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $edits->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
