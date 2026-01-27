@extends('layouts.dashboard')

@section('page-name', $asset->name)

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('coparenting.assets.index') }}" class="btn btn-ghost btn-sm gap-1 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to Assets
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $asset->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <span class="badge badge-{{ $asset->getStatusColor() }}">{{ $asset->status_name }}</span>
                    <span class="badge badge-ghost">{{ $asset->category_name }}</span>
                    @if($asset->type_name)
                        <span class="badge badge-ghost">{{ $asset->type_name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Asset Image --}}
            @if($asset->image_url)
            <div class="card bg-base-100 shadow-sm">
                <figure>
                    <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="w-full h-64 object-cover">
                </figure>
            </div>
            @endif

            {{-- Description --}}
            @if($asset->description)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Description</h2>
                    <p class="text-slate-600">{{ $asset->description }}</p>
                </div>
            </div>
            @endif

            {{-- Vehicle Details --}}
            @if($asset->asset_category === 'vehicle')
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Vehicle Details</h2>
                    <div class="grid grid-cols-2 gap-4 mt-2">
                        @if($asset->vehicle_description)
                        <div>
                            <p class="text-sm text-slate-500">Vehicle</p>
                            <p class="font-medium">{{ $asset->vehicle_description }}</p>
                        </div>
                        @endif
                        @if($asset->vin_registration)
                        <div>
                            <p class="text-sm text-slate-500">VIN/Registration</p>
                            <p class="font-medium">{{ $asset->vin_registration }}</p>
                        </div>
                        @endif
                        @if($asset->license_plate)
                        <div>
                            <p class="text-sm text-slate-500">License Plate</p>
                            <p class="font-medium">{{ $asset->license_plate }}</p>
                        </div>
                        @endif
                        @if($asset->mileage)
                        <div>
                            <p class="text-sm text-slate-500">Mileage</p>
                            <p class="font-medium">{{ number_format($asset->mileage) }} miles</p>
                        </div>
                        @endif
                        @if($asset->vehicle_ownership_name)
                        <div>
                            <p class="text-sm text-slate-500">Ownership Type</p>
                            <p class="font-medium">{{ $asset->vehicle_ownership_name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Location --}}
            @if($asset->full_location || $asset->storage_location)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Location</h2>
                    @if($asset->full_location)
                    <div class="flex items-start gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mt-0.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <p class="text-slate-600">{{ $asset->full_location }}</p>
                    </div>
                    @endif
                    @if($asset->storage_location)
                    <div class="flex items-start gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mt-0.5"><path d="m21 11-7-7-7 7"/><path d="M21 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/></svg>
                        <p class="text-slate-600">{{ $asset->storage_location }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Documents --}}
            @if($asset->documents->isNotEmpty())
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Documents</h2>
                    <div class="divide-y divide-slate-100 mt-2">
                        @foreach($asset->documents as $document)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $document->original_filename ?? 'Document' }}</p>
                                    <p class="text-sm text-slate-500">{{ $document->document_type_label ?? $document->document_type }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($asset->notes)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Notes</h2>
                    <p class="text-slate-600 whitespace-pre-wrap">{{ $asset->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Value Card --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Value</h2>
                    <div class="space-y-3 mt-2">
                        @if($asset->current_value)
                        <div>
                            <p class="text-sm text-slate-500">Current Value</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $asset->formatted_current_value }}</p>
                        </div>
                        @endif
                        @if($asset->purchase_value)
                        <div>
                            <p class="text-sm text-slate-500">Purchase Value</p>
                            <p class="font-medium">{{ $asset->formatted_purchase_value }}</p>
                        </div>
                        @endif
                        @if($asset->acquisition_date)
                        <div>
                            <p class="text-sm text-slate-500">Acquired</p>
                            <p class="font-medium">{{ $asset->acquisition_date->format('M j, Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Owners Card --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Owners</h2>
                    <div class="divide-y divide-slate-100 mt-2">
                        @foreach($asset->owners as $owner)
                        <div class="py-3 flex items-center gap-3">
                            @if($owner->familyMember)
                                @if($owner->familyMember->profile_image_url)
                                    <img src="{{ $owner->familyMember->profile_image_url }}" alt="{{ $owner->familyMember->full_name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-white">{{ strtoupper(substr($owner->familyMember->first_name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-slate-800">{{ $owner->familyMember->full_name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $owner->ownership_percentage }}% ownership
                                        @if($owner->is_primary_owner)
                                            <span class="badge badge-success badge-xs ml-1">Primary</span>
                                        @endif
                                    </p>
                                </div>
                            @elseif($owner->external_owner_name)
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center">
                                    <span class="text-xs font-bold text-slate-500">{{ strtoupper(substr($owner->external_owner_name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $owner->external_owner_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $owner->ownership_percentage }}% ownership (External)</p>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Insurance Card --}}
            @if($asset->is_insured)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Insurance</h2>
                    <div class="space-y-3 mt-2">
                        @if($asset->insurance_provider)
                        <div>
                            <p class="text-sm text-slate-500">Provider</p>
                            <p class="font-medium">{{ $asset->insurance_provider }}</p>
                        </div>
                        @endif
                        @if($asset->insurance_policy_number)
                        <div>
                            <p class="text-sm text-slate-500">Policy Number</p>
                            <p class="font-medium">{{ $asset->insurance_policy_number }}</p>
                        </div>
                        @endif
                        @if($asset->insurance_renewal_date)
                        <div>
                            <p class="text-sm text-slate-500">Renewal Date</p>
                            <p class="font-medium {{ $asset->isInsuranceExpiringSoon() ? 'text-warning' : '' }}">
                                {{ $asset->insurance_renewal_date->format('M j, Y') }}
                                @if($asset->isInsuranceExpiringSoon())
                                    <span class="badge badge-warning badge-sm ml-1">Expiring Soon</span>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
