@extends('layouts.dashboard')

@section('title', $asset ? 'Edit Asset' : 'Add Asset')
@section('page-name', $asset ? 'Edit Asset' : 'Add Asset')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('assets.index') }}">Assets</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $asset ? 'Edit' : 'Add' }} Asset</li>
@endsection

@section('page-title', $asset ? 'Edit Asset' : 'Add Asset')
@section('page-description', $asset ? 'Update your asset details.' : 'Add a new asset to your portfolio.')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('assets.index') }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $asset ? 'Edit Asset' : 'Add New Asset' }}</h1>
                <p class="text-slate-500">{{ $asset ? 'Update your asset details' : 'Track your valuable possessions' }}</p>
            </div>
        </div>
    </div>

    <form action="{{ $asset ? route('assets.update', $asset) : route('assets.store') }}" method="POST" enctype="multipart/form-data" id="asset-form" onsubmit="return validateAssetForm()">
        @csrf
        @if($asset)
            @method('PUT')
        @endif

        <!-- Asset Category -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Asset Category</h2>
                        <p class="text-xs text-slate-400">Select the type of asset</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Category <span class="text-rose-500">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($categories as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="asset_category" value="{{ $key }}"
                                           class="peer hidden"
                                           {{ (old('asset_category', $asset?->asset_category ?? $category) === $key) ? 'checked' : '' }}
                                           onchange="updateAssetType(this.value)">
                                    <span class="btn btn-sm peer-checked:btn-primary peer-checked:text-primary-content">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('asset_category')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Asset Type <span class="text-rose-500">*</span></label>
                        <select name="asset_type" id="asset_type" class="select select-bordered w-full" required>
                            <option value="">Select type</option>
                        </select>
                        @error('asset_type')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
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
                        <p class="text-xs text-slate-400">Asset name and details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Asset Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="e.g., 123 Main Street or 2020 Honda Accord" value="{{ old('name', $asset?->name ?? '') }}" required>
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $asset?->status ?? 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Ownership Type</label>
                        <select name="ownership_type" id="ownership_type" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" onchange="toggleOwnershipSection(this.value)">
                            @foreach($ownershipTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('ownership_type', $asset?->ownership_type ?? 'individual') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Joint Ownership Section -->
                    <div id="joint-ownership-section" class="hidden">
                        <div class="p-4 bg-violet-50 rounded-xl border border-violet-200 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">Joint Owners</h3>
                                    <p class="text-xs text-slate-500">Add co-owners for this asset</p>
                                </div>
                            </div>

                            <!-- Family Circle Selection -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Family Circle</label>
                                <select id="family-circle-select" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" onchange="loadCircleMembers(this.value)">
                                    <option value="">Choose a family circle...</option>
                                    @foreach($familyCircles as $circle)
                                        @php
                                            $membersData = $circle->members->map(function($m) {
                                                return [
                                                    'id' => $m->id,
                                                    'first_name' => $m->first_name,
                                                    'last_name' => $m->last_name,
                                                    'email' => $m->email
                                                ];
                                            })->values();
                                        @endphp
                                        <option value="{{ $circle->id }}" data-members="{{ $membersData->toJson() }}">
                                            {{ $circle->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Family Members Selection (shown after circle is selected) -->
                            <div id="family-members-section" class="hidden">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Family Members</label>
                                <div id="family-member-owners" class="space-y-2">
                                    <!-- Members will be populated dynamically -->
                                </div>
                                <p id="family-owners-error" class="text-rose-500 text-sm mt-2 hidden"></p>
                                @error('family_owners')
                                    <p class="text-rose-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Divider -->
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-px bg-slate-300"></div>
                                <span class="text-xs text-slate-500 font-medium">OR ADD EXTERNAL OWNER</span>
                                <div class="flex-1 h-px bg-slate-300"></div>
                            </div>

                            <!-- External Owners -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-slate-700">External Owners</label>
                                    <button type="button" onclick="addExternalOwner()" class="btn btn-xs btn-ghost text-violet-600 gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                        Add Owner
                                    </button>
                                </div>
                                <div id="external-owners-container" class="space-y-3">
                                    @if($asset)
                                        @foreach($asset->owners->whereNull('family_member_id') as $index => $owner)
                                            <div class="external-owner-row p-3 bg-white rounded-lg border border-slate-200 space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-slate-500">External Owner</span>
                                                    <button type="button" onclick="removeExternalOwner(this)" class="btn btn-ghost btn-xs text-rose-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <input type="text" name="external_owners[{{ $index }}][first_name]"
                                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                                                            placeholder="First Name" value="{{ $owner->external_owner_name ? explode(' ', $owner->external_owner_name)[0] ?? '' : '' }}">
                                                    </div>
                                                    <div>
                                                        <input type="text" name="external_owners[{{ $index }}][last_name]"
                                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                                                            placeholder="Last Name" value="{{ $owner->external_owner_name ? (explode(' ', $owner->external_owner_name)[1] ?? '') : '' }}">
                                                    </div>
                                                </div>
                                                <div>
                                                    <input type="email" name="external_owners[{{ $index }}][email]"
                                                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                                                        placeholder="Email Address" value="{{ $owner->external_owner_email ?? '' }}">
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <input type="tel" name="external_owners[{{ $index }}][phone]"
                                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                                                            placeholder="Phone Number" value="{{ $owner->external_owner_phone ?? '' }}">
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="number" name="external_owners[{{ $index }}][percentage]"
                                                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg text-center placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                                                            placeholder="%" min="0" max="100" step="0.01" value="{{ $owner->ownership_percentage ?? '' }}">
                                                        <span class="text-xs text-slate-500">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <p id="external-owners-error" class="text-rose-500 text-sm mt-2 hidden"></p>
                                @error('external_owners')
                                    <p class="text-rose-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Percentage Indicator -->
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200">
                                <span class="text-sm font-medium text-slate-700">Total Ownership</span>
                                <span id="total-percentage" class="text-sm font-bold text-violet-600">0%</span>
                            </div>

                            <!-- General ownership error -->
                            <p id="ownership-error" class="text-rose-500 text-sm hidden"></p>
                            @error('ownership_type')
                                <p class="text-rose-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" rows="2" placeholder="Brief description of the asset">{{ old('description', $asset?->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Value & Dates -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Value & Dates</h2>
                        <p class="text-xs text-slate-400">Financial information</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <x-date-select
                        name="acquisition_date"
                        label="Acquisition Date"
                        :value="$asset?->acquisition_date"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Purchase Value</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                            <input type="number" name="purchase_value" class="w-full pl-8 pr-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" step="0.01" min="0" placeholder="0.00" value="{{ old('purchase_value', $asset?->purchase_value ?? '') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Current Value</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                            <input type="number" name="current_value" class="w-full pl-8 pr-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" step="0.01" min="0" placeholder="0.00" value="{{ old('current_value', $asset?->current_value ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location (for Property) -->
        <div id="location-section" class="card bg-base-100 shadow-sm hidden">
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

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                        <input type="text" name="location_address" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Street address" value="{{ old('location_address', $asset?->location_address ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">City</label>
                        <input type="text" name="location_city" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="City" value="{{ old('location_city', $asset?->location_city ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">State</label>
                        <input type="text" name="location_state" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="State" value="{{ old('location_state', $asset?->location_state ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">ZIP Code</label>
                        <input type="text" name="location_zip" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="ZIP" value="{{ old('location_zip', $asset?->location_zip ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Country</label>
                        <input type="text" name="location_country" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Country" value="{{ old('location_country', $asset?->location_country ?? 'USA') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div id="vehicle-section" class="card bg-base-100 shadow-sm hidden">
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

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Make</label>
                        <input type="text" name="vehicle_make" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="e.g., Honda" value="{{ old('vehicle_make', $asset?->vehicle_make ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Model</label>
                        <input type="text" name="vehicle_model" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="e.g., Accord" value="{{ old('vehicle_model', $asset?->vehicle_model ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Year</label>
                        <input type="number" name="vehicle_year" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" min="1900" max="{{ date('Y') + 2 }}" placeholder="{{ date('Y') }}" value="{{ old('vehicle_year', $asset?->vehicle_year ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">VIN / Registration</label>
                        <input type="text" name="vin_registration" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="Vehicle identification" value="{{ old('vin_registration', $asset?->vin_registration ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">License Plate</label>
                        <input type="text" name="license_plate" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="License plate" value="{{ old('license_plate', $asset?->license_plate ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Mileage</label>
                        <input type="number" name="mileage" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" min="0" placeholder="Current mileage" value="{{ old('mileage', $asset?->mileage ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Ownership Status</label>
                        <select name="vehicle_ownership" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                            <option value="">Select status</option>
                            @foreach($vehicleOwnership as $key => $label)
                                <option value="{{ $key }}" {{ old('vehicle_ownership', $asset?->vehicle_ownership ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collectable Details -->
        <div id="collectable-section" class="card bg-base-100 shadow-sm hidden">
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

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Category</label>
                        <select name="collectable_category" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="">Select category</option>
                            @foreach($collectableCategories as $key => $label)
                                <option value="{{ $key }}" {{ old('collectable_category', $asset?->collectable_category ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Condition</label>
                        <select name="condition" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="">Select condition</option>
                            @foreach($conditions as $key => $label)
                                <option value="{{ $key }}" {{ old('condition', $asset?->condition ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Appraised By</label>
                        <input type="text" name="appraised_by" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" placeholder="Appraiser name" value="{{ old('appraised_by', $asset?->appraised_by ?? '') }}">
                    </div>

                    <x-date-select
                        name="appraisal_date"
                        label="Appraisal Date"
                        :value="$asset?->appraisal_date"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Appraisal Value</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                            <input type="number" name="appraisal_value" class="w-full pl-8 pr-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" step="0.01" min="0" placeholder="0.00" value="{{ old('appraisal_value', $asset?->appraisal_value ?? '') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Storage Location</label>
                        <input type="text" name="storage_location" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" placeholder="Where is it stored?" value="{{ old('storage_location', $asset?->storage_location ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Provenance</label>
                        <textarea name="provenance" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" rows="2" placeholder="History and origin of the item">{{ old('provenance', $asset?->provenance ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Details -->
        <div id="inventory-section" class="card bg-base-100 shadow-sm hidden">
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

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Room / Location</label>
                        <select name="room_location" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                            <option value="">Select room</option>
                            @foreach($roomLocations as $key => $label)
                                <option value="{{ $key }}" {{ old('room_location', $asset?->room_location ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Serial Number</label>
                        <input type="text" name="serial_number" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20" placeholder="Serial number" value="{{ old('serial_number', $asset?->serial_number ?? '') }}">
                    </div>

                    <x-date-select
                        name="warranty_expiry"
                        label="Warranty Expiry"
                        :value="$asset?->warranty_expiry"
                    />
                </div>
            </div>
        </div>

        <!-- Insurance Information -->
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

                <div class="space-y-4">
                    <div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_insured" value="1" class="checkbox checkbox-sm checkbox-primary" {{ old('is_insured', $asset?->is_insured ?? false) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-slate-700">This asset is insured</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Insurance Provider</label>
                        <input type="text" name="insurance_provider" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Provider name" value="{{ old('insurance_provider', $asset?->insurance_provider ?? '') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Policy Number</label>
                        <input type="text" name="insurance_policy_number" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Policy number" value="{{ old('insurance_policy_number', $asset?->insurance_policy_number ?? '') }}">
                    </div>

                    <x-date-select
                        name="insurance_renewal_date"
                        label="Renewal Date"
                        :value="$asset?->insurance_renewal_date"
                    />
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Documents & Photos</h2>
                        <p class="text-xs text-slate-400">Upload related files</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @if($asset && $asset->documents->count() > 0)
                        <div>
                            <p class="text-sm font-medium text-slate-700 mb-3">Existing Documents</p>
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
                                                <p class="text-xs text-slate-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('assets.documents.download', [$asset, $doc]) }}" class="btn btn-ghost btn-sm gap-2 text-orange-600 hover:bg-orange-50" title="Download">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                            Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Upload Documents</label>
                        <label for="document-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-orange-400 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-orange-600">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-400 mt-1">PDF, JPG, PNG (max 10MB each)</p>
                            </div>
                            <input id="document-upload" type="file" name="documents[]" class="hidden" multiple accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                        <div id="file-list" class="mt-2 space-y-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
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

                <div>
                    <textarea name="notes" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/20" rows="3" placeholder="Additional notes about this asset">{{ old('notes', $asset?->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-start gap-3 pt-4">
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $asset ? 'Update Asset' : 'Save Asset' }}
            </button>
            <a href="{{ route('assets.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
const assetTypes = {
    property: @json($propertyTypes),
    vehicle: @json($vehicleTypes),
    valuable: @json($valuableTypes),
    inventory: @json($valuableTypes)
};

const currentAssetType = @json(old('asset_type', $asset?->asset_type ?? ''));
let externalOwnerIndex = {{ $asset ? $asset->owners->whereNull('family_member_id')->count() : 0 }};

function updateAssetType(category) {
    const select = document.getElementById('asset_type');

    // Clear and rebuild options
    select.innerHTML = '<option value="">Select type</option>';

    const types = assetTypes[category] || {};
    for (const [key, label] of Object.entries(types)) {
        const option = document.createElement('option');
        option.value = key;
        option.textContent = label;
        if (key === currentAssetType) {
            option.selected = true;
        }
        select.appendChild(option);
    }

    // Show/hide sections based on category
    document.getElementById('location-section').classList.toggle('hidden', category !== 'property');
    document.getElementById('vehicle-section').classList.toggle('hidden', category !== 'vehicle');
    document.getElementById('collectable-section').classList.toggle('hidden', category !== 'valuable');
    document.getElementById('inventory-section').classList.toggle('hidden', category !== 'inventory');
}

// Toggle joint ownership section based on ownership type
function toggleOwnershipSection(value) {
    const section = document.getElementById('joint-ownership-section');
    if (value === 'joint') {
        section.classList.remove('hidden');
        calculateTotalPercentage();
    } else {
        section.classList.add('hidden');
    }
}

// Load members based on selected family circle
function loadCircleMembers(circleId) {
    const membersSection = document.getElementById('family-members-section');
    const membersContainer = document.getElementById('family-member-owners');

    if (!circleId) {
        membersSection.classList.add('hidden');
        membersContainer.innerHTML = '';
        return;
    }

    // Get members from the selected option's data attribute
    const select = document.getElementById('family-circle-select');
    const selectedOption = select.options[select.selectedIndex];
    const members = JSON.parse(selectedOption.dataset.members || '[]');

    // Clear and populate members
    membersContainer.innerHTML = '';

    if (members.length === 0) {
        membersContainer.innerHTML = '<p class="text-sm text-slate-500 p-3 bg-white rounded-lg border border-slate-200">No members found in this circle.</p>';
    } else {
        members.forEach(member => {
            const memberHtml = `
                <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-200">
                    <input type="checkbox" name="family_owners[${member.id}][selected]" value="1"
                        class="checkbox checkbox-sm checkbox-primary family-owner-checkbox"
                        data-member-id="${member.id}">
                    <div class="flex-1">
                        <span class="font-medium text-slate-800">${member.first_name} ${member.last_name || ''}</span>
                        ${member.email ? `<span class="text-xs text-slate-400 ml-2">${member.email}</span>` : ''}
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="family_owners[${member.id}][percentage]"
                            class="w-20 px-2 py-1.5 text-sm border border-slate-300 rounded-lg text-center focus:border-violet-500 focus:outline-none"
                            placeholder="%" min="0" max="100" step="0.01"
                            oninput="calculateTotalPercentage()">
                        <span class="text-xs text-slate-500">%</span>
                    </div>
                </div>
            `;
            membersContainer.insertAdjacentHTML('beforeend', memberHtml);
        });

        // Re-attach event listeners for checkboxes
        document.querySelectorAll('.family-owner-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', calculateTotalPercentage);
        });
    }

    membersSection.classList.remove('hidden');
    calculateTotalPercentage();
}

// Add external owner row
function addExternalOwner() {
    const container = document.getElementById('external-owners-container');
    const html = `
        <div class="external-owner-row p-3 bg-white rounded-lg border border-slate-200 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">External Owner</span>
                <button type="button" onclick="removeExternalOwner(this)" class="btn btn-ghost btn-xs text-rose-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="text" name="external_owners[${externalOwnerIndex}][first_name]"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                        placeholder="First Name">
                </div>
                <div>
                    <input type="text" name="external_owners[${externalOwnerIndex}][last_name]"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                        placeholder="Last Name">
                </div>
            </div>
            <div>
                <input type="email" name="external_owners[${externalOwnerIndex}][email]"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                    placeholder="Email Address">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="tel" name="external_owners[${externalOwnerIndex}][phone]"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg placeholder:text-slate-400 focus:border-violet-500 focus:outline-none"
                        placeholder="Phone Number">
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="external_owners[${externalOwnerIndex}][percentage]"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg text-center placeholder:text-slate-400 focus:border-violet-500 focus:outline-none percentage-input"
                        placeholder="%" min="0" max="100" step="0.01" oninput="calculateTotalPercentage()">
                    <span class="text-xs text-slate-500">%</span>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    externalOwnerIndex++;
}

// Remove external owner row
function removeExternalOwner(btn) {
    const row = btn.closest('.external-owner-row');
    row.remove();
    calculateTotalPercentage();
}

// Calculate total ownership percentage
function calculateTotalPercentage() {
    let total = 0;

    // Sum family member percentages (only checked ones)
    document.querySelectorAll('.family-owner-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('.flex');
        const percentageInput = row.querySelector('input[type="number"]');
        if (percentageInput && percentageInput.value) {
            total += parseFloat(percentageInput.value) || 0;
        }
    });

    // Sum external owner percentages
    document.querySelectorAll('#external-owners-container input[name$="[percentage]"]').forEach(input => {
        if (input.value) {
            total += parseFloat(input.value) || 0;
        }
    });

    const totalElement = document.getElementById('total-percentage');
    totalElement.textContent = total.toFixed(2) + '%';

    // Color code based on total
    if (total === 100) {
        totalElement.classList.remove('text-violet-600', 'text-rose-600');
        totalElement.classList.add('text-emerald-600');
    } else if (total > 100) {
        totalElement.classList.remove('text-violet-600', 'text-emerald-600');
        totalElement.classList.add('text-rose-600');
    } else {
        totalElement.classList.remove('text-emerald-600', 'text-rose-600');
        totalElement.classList.add('text-violet-600');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const selectedCategory = document.querySelector('input[name="asset_category"]:checked');
    if (selectedCategory) {
        updateAssetType(selectedCategory.value);
    }

    // Initialize ownership section
    const ownershipType = document.getElementById('ownership_type');
    if (ownershipType) {
        toggleOwnershipSection(ownershipType.value);
    }

    // Add event listeners for percentage calculations
    document.querySelectorAll('.family-owner-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotalPercentage);
    });

    document.querySelectorAll('#family-member-owners input[type="number"]').forEach(input => {
        input.addEventListener('input', calculateTotalPercentage);
    });

    // Initial calculation
    calculateTotalPercentage();

    // File upload preview
    const fileInput = document.getElementById('document-upload');
    const fileList = document.getElementById('file-list');

    if (fileInput && fileList) {
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            if (this.files.length > 0) {
                Array.from(this.files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center gap-2 p-2 bg-orange-50 rounded-lg text-sm';
                    fileItem.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        <span class="flex-1 truncate text-slate-700">${file.name}</span>
                        <span class="text-xs text-slate-400">${(file.size / 1024).toFixed(1)} KB</span>
                    `;
                    fileList.appendChild(fileItem);
                });
            }
        });
    }
});

// Clear all validation errors
function clearValidationErrors() {
    const errorElements = ['family-owners-error', 'external-owners-error', 'ownership-error'];
    errorElements.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = '';
            el.classList.add('hidden');
        }
    });

    // Remove error styling from inputs
    document.querySelectorAll('.percentage-error').forEach(el => {
        el.classList.remove('percentage-error', 'border-rose-500');
    });
}

// Show inline error
function showInlineError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.classList.remove('hidden');
    }
}

// Validate form before submission
function validateAssetForm() {
    const ownershipType = document.getElementById('ownership_type').value;

    // Clear previous errors
    clearValidationErrors();

    // Only validate if joint ownership is selected
    if (ownershipType !== 'joint') {
        return true;
    }

    // Check if at least one owner is selected
    const checkedFamilyOwners = document.querySelectorAll('.family-owner-checkbox:checked');
    const externalOwnerRows = document.querySelectorAll('.external-owner-row');
    let hasExternalOwner = false;

    externalOwnerRows.forEach(row => {
        const firstName = row.querySelector('input[name$="[first_name]"]')?.value?.trim();
        const lastName = row.querySelector('input[name$="[last_name]"]')?.value?.trim();
        if (firstName || lastName) {
            hasExternalOwner = true;
        }
    });

    if (checkedFamilyOwners.length === 0 && !hasExternalOwner) {
        showInlineError('ownership-error', 'Please select at least one joint owner or add an external owner.');
        return false;
    }

    // Validate that selected family members have percentages
    let hasError = false;
    let familyErrors = [];

    checkedFamilyOwners.forEach(checkbox => {
        const row = checkbox.closest('.flex');
        const percentageInput = row.querySelector('input[type="number"]');
        const memberName = row.querySelector('.font-medium')?.textContent?.trim() || 'A member';

        if (!percentageInput || !percentageInput.value || percentageInput.value === '') {
            hasError = true;
            familyErrors.push(memberName);
            if (percentageInput) {
                percentageInput.classList.add('percentage-error', 'border-rose-500');
            }
        }
    });

    if (familyErrors.length > 0) {
        showInlineError('family-owners-error', `Please enter ownership percentage for: ${familyErrors.join(', ')}`);
    }

    // Check external owners have percentages
    let externalErrors = [];
    externalOwnerRows.forEach((row, index) => {
        const firstName = row.querySelector('input[name$="[first_name]"]')?.value?.trim();
        const lastName = row.querySelector('input[name$="[last_name]"]')?.value?.trim();
        const percentageInput = row.querySelector('input[name$="[percentage]"]');
        const percentage = percentageInput?.value;

        if ((firstName || lastName) && (!percentage || percentage === '')) {
            hasError = true;
            externalErrors.push(`${firstName || ''} ${lastName || ''}`.trim());
            if (percentageInput) {
                percentageInput.classList.add('percentage-error', 'border-rose-500');
            }
        }
    });

    if (externalErrors.length > 0) {
        showInlineError('external-owners-error', `Please enter ownership percentage for: ${externalErrors.join(', ')}`);
    }

    if (hasError) {
        // Scroll to the joint ownership section
        document.getElementById('joint-ownership-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}
</script>
@endpush
