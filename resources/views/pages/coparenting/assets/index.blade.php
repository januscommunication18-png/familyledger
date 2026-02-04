@extends('layouts.dashboard')

@section('page-name', 'Shared Assets')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Shared Assets</h1>
            <p class="text-slate-500">View assets belonging to your shared children.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')

            @if($canRequestAssets)
            <a href="{{ route('coparenting.assets.create') }}" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Request New Asset
            </a>
            @endif
        </div>
    </div>

    {{-- Pending Requests Alert --}}
    @if($pendingRequests->isNotEmpty())
    <div class="alert alert-warning mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div>
            <h3 class="font-semibold">You have {{ $pendingRequests->count() }} pending asset request(s)</h3>
            <p class="text-sm">Waiting for the account owner to approve your requests.</p>
        </div>
        <a href="{{ route('coparenting.pending-edits.index') }}" class="btn btn-warning btn-sm">View Requests</a>
    </div>
    @endif

    @if($assets->isEmpty() && $pendingRequests->isEmpty())
        {{-- Empty State --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">No Shared Assets Yet</h3>
                <p class="text-slate-500 max-w-md mx-auto mb-6">
                    @if($canRequestAssets)
                        There are no assets associated with your shared children yet. You can request to add one.
                    @else
                        There are no assets associated with your shared children yet, or you don't have permission to view them.
                    @endif
                </p>
                @if($canRequestAssets)
                <a href="{{ route('coparenting.assets.create') }}" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Request New Asset
                </a>
                @endif
            </div>
        </div>
    @else
        {{-- Assets Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($assets as $asset)
            <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                {{-- Asset Image/Icon --}}
                <figure class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-200">
                    @if($asset->image_url)
                        <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full">
                            <span class="{{ $asset->getCategoryIcon() }} text-6xl text-slate-300"></span>
                        </div>
                    @endif
                    {{-- Category Badge --}}
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-{{ $asset->getStatusColor() }}">{{ $asset->category_name }}</span>
                    </div>
                </figure>

                <div class="card-body p-4">
                    <h3 class="card-title text-lg">{{ $asset->name }}</h3>

                    @if($asset->asset_category === 'vehicle' && $asset->vehicle_description)
                        <p class="text-sm text-slate-500">{{ $asset->vehicle_description }}</p>
                    @endif

                    {{-- Value --}}
                    @if($asset->current_value)
                    <div class="flex items-center gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span class="font-semibold text-emerald-600">{{ $asset->formatted_current_value }}</span>
                    </div>
                    @endif

                    {{-- Owners --}}
                    <div class="mt-3">
                        <p class="text-xs text-slate-500 mb-1">Owned by:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($asset->owners as $owner)
                                @if($owner->familyMember)
                                    <span class="badge badge-ghost badge-sm">{{ $owner->familyMember->full_name }}</span>
                                @elseif($owner->external_owner_name)
                                    <span class="badge badge-ghost badge-sm">{{ $owner->external_owner_name }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('coparenting.assets.show', $asset) }}" class="btn btn-ghost btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Children with Asset Access Info --}}
    @if($childrenWithAssetAccess->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-slate-700 mb-4">Children You Can View Assets For</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($childrenWithAssetAccess as $child)
            <div class="card bg-base-100 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    @if($child->profile_image_url)
                        <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="font-medium text-slate-800">{{ $child->full_name }}</p>
                        <p class="text-xs text-slate-500">
                            @if($child->can_edit_assets ?? false)
                                <span class="text-emerald-600">Can request assets</span>
                            @else
                                <span class="text-slate-400">View only</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
