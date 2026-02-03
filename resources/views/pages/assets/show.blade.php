@extends('layouts.dashboard')

@section('title', $asset->name)
@section('page-name', 'Assets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('assets.index', ['tab' => $asset->asset_category]) }}">Assets</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $asset->name }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('assets.index', ['tab' => $asset->asset_category]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($asset->image)
                    <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="w-20 h-20 rounded-xl object-cover shadow-lg border-2 border-white">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center shadow-lg">
                        <span class="{{ $asset->getCategoryIcon() }} size-7 text-white"></span>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $asset->name }}</h1>
                    <p class="text-slate-500">{{ $asset->category_name }} - {{ $asset->type_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Status & Value Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
            <span class="badge badge-{{ $asset->getStatusColor() }}">{{ $asset->status_name }}</span>
            <p class="text-sm text-slate-500 mt-1">Status</p>
        </div>
        <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
            <p class="text-sm font-medium text-slate-800">{{ $asset->ownership_type_name }}</p>
            <p class="text-sm text-slate-500">Ownership</p>
        </div>
        @if($asset->current_value)
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 shadow-sm text-center">
                <p class="text-xl font-bold text-emerald-600">{{ $asset->formatted_current_value }}</p>
                <p class="text-sm text-slate-500">Current Value</p>
            </div>
        @endif
        @if($asset->purchase_value)
            <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
                <p class="text-lg font-semibold text-slate-700">{{ $asset->formatted_purchase_value }}</p>
                <p class="text-sm text-slate-500">Purchase Value</p>
            </div>
        @endif
    </div>

    <!-- Basic Information -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Basic Information</h2>
                    <p class="text-xs text-slate-400">Asset details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Asset Name</label>
                    <p class="text-slate-900 font-semibold">{{ $asset->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Category</label>
                    <p class="text-slate-900">{{ $asset->category_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Type</label>
                    <p class="text-slate-900">{{ $asset->type_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Status</label>
                    <span class="badge badge-{{ $asset->getStatusColor() }}">{{ $asset->status_name }}</span>
                </div>

                @if($asset->acquisition_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Acquisition Date</label>
                    <p class="text-slate-900">{{ $asset->acquisition_date->format('F j, Y') }}</p>
                </div>
                @endif

                @if($asset->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Description</label>
                    <p class="text-slate-900">{{ $asset->description }}</p>
                </div>
                @endif

                @if($asset->ownership_type === 'joint' && $asset->owners->count() > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-2">Owners</label>
                    <div class="space-y-2">
                        @foreach($asset->owners as $owner)
                            <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
                                <span class="text-slate-800">{{ $owner->owner_name }}</span>
                                @if($owner->ownership_percentage)
                                    <span class="badge badge-sm badge-outline">{{ $owner->ownership_percentage }}%</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Location (for Property) -->
    @if($asset->asset_category === 'property' && $asset->full_location)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Location</h2>
                    <p class="text-xs text-slate-400">Property address</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($asset->location_address)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Address</label>
                    <p class="text-slate-900">{{ $asset->location_address }}</p>
                </div>
                @endif

                @if($asset->location_city)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">City</label>
                    <p class="text-slate-900">{{ $asset->location_city }}</p>
                </div>
                @endif

                @if($asset->location_state)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">State</label>
                    <p class="text-slate-900">{{ $asset->location_state }}</p>
                </div>
                @endif

                @if($asset->location_zip)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">ZIP Code</label>
                    <p class="text-slate-900">{{ $asset->location_zip }}</p>
                </div>
                @endif

                @if($asset->location_country)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Country</label>
                    <p class="text-slate-900">{{ $asset->location_country }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Vehicle Details -->
    @if($asset->asset_category === 'vehicle' && ($asset->vehicle_make || $asset->vehicle_model || $asset->vehicle_year))
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-sky-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-600"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Vehicle Details</h2>
                    <p class="text-xs text-slate-400">Vehicle-specific information</p>
                </div>
            </div>

            @if($asset->vehicle_description)
            <div class="p-4 bg-sky-50 rounded-lg border border-sky-200 mb-4">
                <p class="text-lg font-semibold text-sky-800">{{ $asset->vehicle_description }}</p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($asset->vehicle_make)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Make</label>
                    <p class="text-slate-900">{{ $asset->vehicle_make }}</p>
                </div>
                @endif

                @if($asset->vehicle_model)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Model</label>
                    <p class="text-slate-900">{{ $asset->vehicle_model }}</p>
                </div>
                @endif

                @if($asset->vehicle_year)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Year</label>
                    <p class="text-slate-900">{{ $asset->vehicle_year }}</p>
                </div>
                @endif

                @if($asset->vin_registration)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">VIN / Registration</label>
                    <p class="text-slate-900 font-mono">{{ $asset->vin_registration }}</p>
                </div>
                @endif

                @if($asset->license_plate)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">License Plate</label>
                    <p class="text-slate-900 font-mono">{{ $asset->license_plate }}</p>
                </div>
                @endif

                @if($asset->mileage)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Mileage</label>
                    <p class="text-slate-900">{{ number_format($asset->mileage) }} miles</p>
                </div>
                @endif

                @if($asset->vehicle_ownership_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Ownership Status</label>
                    <p class="text-slate-900">{{ $asset->vehicle_ownership_name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Collectable Details -->
    @if($asset->asset_category === 'valuable' && ($asset->collectable_category || $asset->condition || $asset->appraised_by))
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Collectable Details</h2>
                    <p class="text-xs text-slate-400">Valuable item information</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($asset->collectable_category_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Category</label>
                    <p class="text-slate-900">{{ $asset->collectable_category_name }}</p>
                </div>
                @endif

                @if($asset->condition_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Condition</label>
                    <span class="badge badge-outline">{{ $asset->condition_name }}</span>
                </div>
                @endif

                @if($asset->appraised_by)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Appraised By</label>
                    <p class="text-slate-900">{{ $asset->appraised_by }}</p>
                </div>
                @endif

                @if($asset->appraisal_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Appraisal Date</label>
                    <p class="text-slate-900">{{ $asset->appraisal_date->format('F j, Y') }}</p>
                </div>
                @endif

                @if($asset->appraisal_value)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Appraisal Value</label>
                    <p class="text-rose-600 font-semibold">${{ number_format($asset->appraisal_value, 2) }}</p>
                </div>
                @endif

                @if($asset->storage_location)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Storage Location</label>
                    <p class="text-slate-900">{{ $asset->storage_location }}</p>
                </div>
                @endif

                @if($asset->provenance)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Provenance</label>
                    <p class="text-slate-900 whitespace-pre-wrap">{{ $asset->provenance }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Details -->
    @if($asset->asset_category === 'inventory' && ($asset->room_location || $asset->serial_number || $asset->warranty_expiry))
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-teal-600"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Inventory Details</h2>
                    <p class="text-xs text-slate-400">Home inventory information</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($asset->room_location_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Room / Location</label>
                    <p class="text-slate-900">{{ $asset->room_location_name }}</p>
                </div>
                @endif

                @if($asset->serial_number)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Serial Number</label>
                    <p class="text-slate-900 font-mono">{{ $asset->serial_number }}</p>
                </div>
                @endif

                @if($asset->warranty_expiry)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Warranty Expiry</label>
                    <p class="text-slate-900 {{ $asset->isWarrantyExpiringSoon() ? 'text-warning font-semibold' : '' }}">
                        {{ $asset->warranty_expiry->format('F j, Y') }}
                        @if($asset->isWarrantyExpiringSoon())
                            <span class="text-warning text-sm">(Expiring soon)</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Insurance Information -->
    @if($asset->is_insured || $asset->insurance_provider || $asset->insurance_policy_number)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Insurance Information</h2>
                    <p class="text-xs text-slate-400">Insurance coverage details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Insured</label>
                    @if($asset->is_insured)
                        <span class="badge badge-success gap-1">
                            <span class="icon-[tabler--shield-check] size-4"></span>
                            Yes
                        </span>
                    @else
                        <span class="badge badge-neutral">No</span>
                    @endif
                </div>

                @if($asset->insurance_provider)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Provider</label>
                    <p class="text-slate-900">{{ $asset->insurance_provider }}</p>
                </div>
                @endif

                @if($asset->insurance_policy_number)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Policy Number</label>
                    <p class="text-slate-900 font-mono">{{ $asset->insurance_policy_number }}</p>
                </div>
                @endif

                @if($asset->insurance_renewal_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Renewal Date</label>
                    <p class="text-slate-900 {{ $asset->isInsuranceExpiringSoon() ? 'text-warning font-semibold' : '' }}">
                        {{ $asset->insurance_renewal_date->format('F j, Y') }}
                        @if($asset->isInsuranceExpiringSoon())
                            <span class="text-warning text-sm">(Expiring soon)</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Documents -->
    @if($asset->documents->count() > 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Documents & Photos</h2>
                    <p class="text-xs text-slate-400">{{ $asset->documents->count() }} file(s) attached</p>
                </div>
            </div>

            <div class="space-y-2">
                @foreach($asset->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                @if(str_contains($doc->mime_type, 'pdf'))
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                @elseif(str_contains($doc->mime_type, 'image'))
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-slate-800 text-sm">{{ $doc->original_filename }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $documentTypes[$doc->document_type] ?? $doc->document_type }} |
                                    {{ number_format($doc->file_size / 1024, 1) }} KB
                                </p>
                            </div>
                        </div>
                        <x-protected-download :href="route('assets.documents.download', [$asset, $doc])" class="btn btn-ghost btn-sm gap-1 text-orange-600 hover:bg-orange-50" title="Download">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Download
                        </x-protected-download>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($asset->notes)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Notes</h2>
                    <p class="text-xs text-slate-400">Additional information</p>
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-lg text-slate-700 whitespace-pre-wrap">{{ $asset->notes }}</div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex justify-start gap-3">
        <a href="{{ route('assets.edit', $asset) }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            Edit Asset
        </a>
        <a href="{{ route('assets.index', ['tab' => $asset->asset_category]) }}" class="btn btn-ghost">Back to Assets</a>
    </div>
</div>
@endsection
