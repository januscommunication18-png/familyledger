@extends('layouts.dashboard')

@push('styles')
<style>
/* Select2 Custom Styles */
.select2-container--default .select2-selection--multiple {
    min-height: 42px;
    padding: 4px 8px;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    background-color: white;
}

.select2-container--default.select2-container--focus .select2-selection--multiple,
.select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
    outline: none;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #ede9fe;
    border: none;
    border-radius: 0.375rem;
    padding: 2px 8px;
    margin: 2px;
    color: #5b21b6;
    font-size: 0.875rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #7c3aed;
    margin-right: 4px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #5b21b6;
}

.select2-dropdown {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #f5f3ff;
    color: #5b21b6;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #ede9fe;
}

.select2-results__option {
    padding: 8px 12px;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    padding: 6px 10px;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #8b5cf6;
    outline: none;
}
</style>
@endpush

@section('title', $insurance ? 'Edit Insurance Policy' : 'Add Insurance Policy')
@section('page-name', 'Documents')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="hover:text-primary">Documents</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $insurance ? 'Edit Insurance' : 'Add Insurance' }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $insurance ? 'Edit Insurance Policy' : 'Add Insurance Policy' }}</h1>
                <p class="text-slate-500">Store your insurance policy information securely</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ $insurance ? route('documents.insurance.update', $insurance) : route('documents.insurance.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($insurance)
            @method('PUT')
        @endif

        <!-- Policy Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Policy Information</h2>
                        <p class="text-xs text-slate-400">Basic insurance policy details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Insurance Type <span class="text-rose-500">*</span></label>
                        <select name="insurance_type" id="insurance_type_select" required data-select='{
                            "placeholder": "Select type...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "hasSearch": true,
                            "searchPlaceholder": "Search...",
                            "searchClasses": "input input-sm",
                            "searchWrapperClasses": "bg-base-100 p-2 sticky top-0",
                            "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                            <option value="">Choose type</option>
                            @foreach($insuranceTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('insurance_type', $insurance?->insurance_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" id="status_select" data-select='{
                            "placeholder": "Select status...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $insurance?->status ?? 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Provider Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="provider_name" value="{{ old('provider_name', $insurance?->provider_name) }}" required
                               class="input w-full"
                               placeholder="e.g., Blue Cross Blue Shield" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Policyholder</label>
                        @php
                            $policyholderIds = $insurance ? $insurance->policyholders->pluck('id')->toArray() : [];
                            $ownerHasNoRecord = $familyMembers->first()?->is_owner && empty($familyMembers->first()?->id);
                        @endphp
                        @if($ownerHasNoRecord)
                            <div class="text-xs text-amber-600 mb-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                To add yourself, first add yourself to a Family Circle.
                            </div>
                        @endif
                        <select name="policyholders[]" id="policyholder_select" multiple class="select2-multi w-full">
                            @foreach($familyMembers as $member)
                                @if(!empty($member->id))
                                    <option value="{{ $member->id }}" {{ in_array($member->id, old('policyholders', $policyholderIds)) ? 'selected' : '' }}>
                                        {{ $member->first_name }} {{ $member->last_name ?? '' }}{{ !empty($member->is_owner) ? ' (You)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Covered Members</label>
                        @php
                            $coveredIds = $insurance ? $insurance->coveredMembers->pluck('id')->toArray() : [];
                        @endphp
                        <select name="covered_members[]" id="covered_members_select" multiple class="select2-multi w-full">
                            @foreach($familyMembers as $member)
                                @if(!empty($member->id))
                                    <option value="{{ $member->id }}" {{ in_array($member->id, old('covered_members', $coveredIds)) ? 'selected' : '' }}>
                                        {{ $member->first_name }} {{ $member->last_name ?? '' }}{{ !empty($member->is_owner) ? ' (You)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Policy Number</label>
                        <input type="text" name="policy_number" value="{{ old('policy_number', $insurance?->policy_number) }}"
                               class="input w-full"
                               placeholder="Policy number" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Group Number</label>
                        <input type="text" name="group_number" value="{{ old('group_number', $insurance?->group_number) }}"
                               class="input w-full"
                               placeholder="Group number" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Plan Name</label>
                        <input type="text" name="plan_name" value="{{ old('plan_name', $insurance?->plan_name) }}"
                               class="input w-full"
                               placeholder="e.g., Gold PPO Plan" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates & Payments -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Dates & Payments</h2>
                        <p class="text-xs text-slate-400">Coverage period and premium information</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <x-date-select
                        name="effective_date"
                        label="Effective Date"
                        :value="$insurance?->effective_date"
                    />

                    <x-date-select
                        name="expiration_date"
                        label="Expiration Date"
                        :value="$insurance?->expiration_date"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Premium Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">$</span>
                            <input type="number" name="premium_amount" value="{{ old('premium_amount', $insurance?->premium_amount) }}"
                                   step="0.01" min="0" placeholder="0.00"
                                   class="input w-full pl-7" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Frequency</label>
                        <select name="payment_frequency" id="frequency_select" data-select='{
                            "placeholder": "Select frequency...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                            <option value="">Select</option>
                            @foreach($paymentFrequencies as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_frequency', $insurance?->payment_frequency) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent / Contact Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Contact Information</h2>
                        <p class="text-xs text-slate-400">Agent and claims contact details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Agent Name</label>
                        <input type="text" name="agent_name" value="{{ old('agent_name', $insurance?->agent_name) }}"
                               class="input w-full" placeholder="Agent name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Agent Phone</label>
                        <input type="tel" name="agent_phone" value="{{ old('agent_phone', $insurance?->agent_phone) }}"
                               class="input w-full" placeholder="(555) 123-4567" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Agent Email</label>
                        <input type="email" name="agent_email" value="{{ old('agent_email', $insurance?->agent_email) }}"
                               class="input w-full" placeholder="agent@example.com" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Claims Phone</label>
                        <input type="tel" name="claims_phone" value="{{ old('claims_phone', $insurance?->claims_phone) }}"
                               class="input w-full" placeholder="(800) 123-4567" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Insurance Card Images -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Insurance Card</h2>
                        <p class="text-xs text-slate-400">Upload images of your insurance card</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Front of Card -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Front of Card</label>
                        <div id="front-preview" class="mb-3 p-2 bg-slate-50 rounded-lg border border-slate-200 {{ $insurance?->card_front_image ? '' : 'hidden' }}">
                            @if($insurance?->card_front_image)
                                <x-protected-image
                                    :src="route('documents.insurance.card', [$insurance, 'front'])"
                                    alt="Card Front"
                                    class="max-h-32 rounded mx-auto"
                                    container-class="flex justify-center"
                                />
                            @else
                                <img src="" alt="Card Front" class="max-h-32 rounded mx-auto" id="front-preview-img" />
                            @endif
                        </div>
                        <label id="front-upload-label" class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-blue-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-blue-600">Click to upload</span></p>
                                <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                            </div>
                            <input type="file" name="card_front_image" id="card_front_input" accept="image/jpeg,image/png,image/jpg" class="hidden" />
                        </label>
                    </div>

                    <!-- Back of Card -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Back of Card</label>
                        <div id="back-preview" class="mb-3 p-2 bg-slate-50 rounded-lg border border-slate-200 {{ $insurance?->card_back_image ? '' : 'hidden' }}">
                            @if($insurance?->card_back_image)
                                <x-protected-image
                                    :src="route('documents.insurance.card', [$insurance, 'back'])"
                                    alt="Card Back"
                                    class="max-h-32 rounded mx-auto"
                                    container-class="flex justify-center"
                                />
                            @else
                                <img src="" alt="Card Back" class="max-h-32 rounded mx-auto" id="back-preview-img" />
                            @endif
                        </div>
                        <label id="back-upload-label" class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-blue-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-blue-600">Click to upload</span></p>
                                <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                            </div>
                            <input type="file" name="card_back_image" id="card_back_input" accept="image/jpeg,image/png,image/jpg" class="hidden" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Additional Information</h2>
                        <p class="text-xs text-slate-400">Coverage details and notes</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Coverage Details</label>
                        <textarea name="coverage_details" rows="3" class="textarea textarea-bordered w-full"
                                  placeholder="Describe what this policy covers...">{{ old('coverage_details', $insurance?->coverage_details) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="textarea textarea-bordered w-full"
                                  placeholder="Any additional notes...">{{ old('notes', $insurance?->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-start gap-3">
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $insurance ? 'Update Policy' : 'Save Policy' }}
            </button>
            <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@vite('resources/js/vendor/select2.js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview functionality
        function setupImagePreview(inputId, previewContainerId, previewImgId) {
            const input = document.getElementById(inputId);
            const previewContainer = document.getElementById(previewContainerId);
            const previewImg = document.getElementById(previewImgId);

            if (input) {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewContainer.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        setupImagePreview('card_front_input', 'front-preview', 'front-preview-img');
        setupImagePreview('card_back_input', 'back-preview', 'back-preview-img');
    });

    // Initialize Select2 for multi-select dropdowns
    $(document).ready(function() {
        $('#policyholder_select').select2({
            placeholder: 'Select policyholders...',
            allowClear: true,
            width: '100%'
        });

        $('#covered_members_select').select2({
            placeholder: 'Select covered members...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
