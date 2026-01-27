@extends('layouts.dashboard')

@section('page-name', 'Request New Asset')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('coparenting.assets.index') }}" class="btn btn-ghost btn-sm gap-1 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to Assets
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Request New Asset</h1>
        <p class="text-slate-500">Submit a request to add a new asset for your shared child. The account owner will need to approve your request.</p>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-info mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span>Your request will be sent to the account owner for approval. They will be notified and can approve or reject your request.</span>
    </div>

    <form action="{{ route('coparenting.assets.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Child Selection --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Select Child</h2>
                <p class="text-sm text-slate-500 mb-4">This asset will be registered as belonging to the selected child.</p>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Child Owner <span class="text-error">*</span></span>
                    </label>
                    <select name="family_member_id" class="select select-bordered w-full" required>
                        <option value="">Select a child...</option>
                        @foreach($children as $child)
                            <option value="{{ $child->id }}" {{ (old('family_member_id') == $child->id || request('family_member_id') == $child->id) ? 'selected' : '' }}>
                                {{ $child->full_name }}
                                @if(isset($child->other_parent_name))
                                    (shared with {{ $child->other_parent_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('family_member_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Basic Asset Info --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Asset Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-medium">Asset Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., College Savings Account, Car for College" class="input input-bordered" required>
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Category <span class="text-error">*</span></span>
                        </label>
                        <select name="asset_category" id="asset_category" class="select select-bordered" required onchange="updateAssetTypeOptions()">
                            <option value="">Select category...</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('asset_category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('asset_category')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Type</span>
                        </label>
                        <select name="asset_type" id="asset_type" class="select select-bordered">
                            <option value="">Select type...</option>
                        </select>
                        @error('asset_type')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Ownership Type --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Ownership Type</span>
                        </label>
                        <select name="ownership_type" class="select select-bordered">
                            <option value="">Select...</option>
                            @foreach($ownershipTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('ownership_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Currency --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Currency</span>
                        </label>
                        <select name="currency" class="select select-bordered">
                            <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <textarea name="description" class="textarea textarea-bordered" rows="3" placeholder="Brief description of the asset...">{{ old('description') }}</textarea>
                    @error('description')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Values --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Value Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Acquisition Date --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Acquisition Date</span>
                        </label>
                        <input type="date" name="acquisition_date" value="{{ old('acquisition_date') }}" class="input input-bordered">
                    </div>

                    {{-- Purchase Value --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Purchase Value</span>
                        </label>
                        <input type="number" name="purchase_value" value="{{ old('purchase_value') }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered">
                    </div>

                    {{-- Current Value --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Current Value</span>
                        </label>
                        <input type="number" name="current_value" value="{{ old('current_value') }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered">
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicle Fields (conditional) --}}
        <div id="vehicle_fields" class="card bg-base-100 shadow-sm hidden">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Vehicle Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Make</span></label>
                        <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}" placeholder="e.g., Toyota" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Model</span></label>
                        <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" placeholder="e.g., Camry" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Year</span></label>
                        <input type="number" name="vehicle_year" value="{{ old('vehicle_year') }}" min="1900" max="2100" placeholder="2024" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">VIN/Registration</span></label>
                        <input type="text" name="vin_registration" value="{{ old('vin_registration') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">License Plate</span></label>
                        <input type="text" name="license_plate" value="{{ old('license_plate') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mileage</span></label>
                        <input type="number" name="mileage" value="{{ old('mileage') }}" min="0" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Vehicle Ownership</span></label>
                        <select name="vehicle_ownership" class="select select-bordered">
                            <option value="">Select...</option>
                            @foreach($vehicleOwnership as $key => $label)
                                <option value="{{ $key }}" {{ old('vehicle_ownership') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Location</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-medium">Address</span></label>
                        <input type="text" name="location_address" value="{{ old('location_address') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">City</span></label>
                        <input type="text" name="location_city" value="{{ old('location_city') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">State</span></label>
                        <input type="text" name="location_state" value="{{ old('location_state') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">ZIP Code</span></label>
                        <input type="text" name="location_zip" value="{{ old('location_zip') }}" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Country</span></label>
                        <input type="text" name="location_country" value="{{ old('location_country', 'USA') }}" class="input input-bordered">
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes for Request --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Request Notes</h2>
                <p class="text-sm text-slate-500 mb-4">Add any notes or explanation for the account owner about why you're requesting to add this asset.</p>

                <div class="form-control">
                    <textarea name="request_notes" class="textarea textarea-bordered" rows="3" placeholder="e.g., This is the car we purchased for Emma's 16th birthday...">{{ old('request_notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('coparenting.assets.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7z"/></svg>
                Submit Request
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const assetTypes = {
    property: @json($propertyTypes),
    vehicle: @json($vehicleTypes),
    valuable: @json($valuableTypes),
    inventory: @json($valuableTypes)
};

function updateAssetTypeOptions() {
    const category = document.getElementById('asset_category').value;
    const typeSelect = document.getElementById('asset_type');
    const vehicleFields = document.getElementById('vehicle_fields');

    // Clear current options
    typeSelect.innerHTML = '<option value="">Select type...</option>';

    // Add new options based on category
    if (category && assetTypes[category]) {
        Object.entries(assetTypes[category]).forEach(([key, label]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = label;
            typeSelect.appendChild(option);
        });
    }

    // Show/hide vehicle fields
    if (category === 'vehicle') {
        vehicleFields.classList.remove('hidden');
    } else {
        vehicleFields.classList.add('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAssetTypeOptions();

    // Restore old asset_type value if exists
    const oldAssetType = '{{ old('asset_type') }}';
    if (oldAssetType) {
        document.getElementById('asset_type').value = oldAssetType;
    }
});
</script>
@endpush
@endsection
