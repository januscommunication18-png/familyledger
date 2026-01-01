@extends('layouts.dashboard')

@section('title', 'Add Family Member')
@section('page-name', 'Add Family Member')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.show', $circle) }}" class="hover:text-violet-600">{{ $circle->name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Add Member</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.show', $circle) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Add Family Member</h1>
        <p class="text-slate-500 mt-1">Add a new member to {{ $circle->name }}</p>
    </div>

    <!-- Form Card -->
    <div class="card bg-base-100 shadow-sm">
        <form action="{{ route('family-circle.member.store', $circle) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <!-- Profile Photo & Basic Information Section -->
                <div class="mb-8">
                    <div class="flex items-start gap-6 mb-6">
                        <!-- Profile Photo Circle -->
                        <div class="shrink-0">
                            <div class="relative">
                                <div id="profilePreviewContainer" class="w-24 h-24 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center overflow-hidden border-4 border-white shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <img id="profilePreview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                                </div>
                                <label for="profile_image" class="absolute -bottom-1 -right-1 w-8 h-8 bg-violet-600 hover:bg-violet-700 rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                </label>
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(this)">
                            </div>
                            <p class="text-xs text-slate-500 text-center mt-2">Profile Photo</p>
                        </div>
                        <!-- Basic Info Header -->
                        <div class="flex-1 pt-2">
                            <h2 class="text-lg font-semibold text-slate-900">Basic Information</h2>
                            <p class="text-sm text-slate-500">Enter the member's personal details</p>
                        </div>
                    </div>

                    <!-- Form Fields - Single Column -->
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="John" required maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('first_name') border-rose-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Last Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('last_name') border-rose-500 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Date of Birth <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <!-- Month -->
                                <div class="flex-1">
                                    <select name="dob_month" id="dob_month" data-select='{
                                        "placeholder": "Month",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "hasSearch": true,
                                        "searchPlaceholder": "Search...",
                                        "dropdownClasses": "advance-select-menu",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden">
                                        <option value="">Month</option>
                                        <option value="01" {{ old('dob_month') == '01' ? 'selected' : '' }}>January</option>
                                        <option value="02" {{ old('dob_month') == '02' ? 'selected' : '' }}>February</option>
                                        <option value="03" {{ old('dob_month') == '03' ? 'selected' : '' }}>March</option>
                                        <option value="04" {{ old('dob_month') == '04' ? 'selected' : '' }}>April</option>
                                        <option value="05" {{ old('dob_month') == '05' ? 'selected' : '' }}>May</option>
                                        <option value="06" {{ old('dob_month') == '06' ? 'selected' : '' }}>June</option>
                                        <option value="07" {{ old('dob_month') == '07' ? 'selected' : '' }}>July</option>
                                        <option value="08" {{ old('dob_month') == '08' ? 'selected' : '' }}>August</option>
                                        <option value="09" {{ old('dob_month') == '09' ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ old('dob_month') == '10' ? 'selected' : '' }}>October</option>
                                        <option value="11" {{ old('dob_month') == '11' ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ old('dob_month') == '12' ? 'selected' : '' }}>December</option>
                                    </select>
                                </div>
                                <!-- Day -->
                                <div class="flex-1">
                                    <select name="dob_day" id="dob_day" data-select='{
                                        "placeholder": "Day",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "hasSearch": true,
                                        "searchPlaceholder": "Search...",
                                        "dropdownClasses": "advance-select-menu",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden">
                                        <option value="">Day</option>
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ old('dob_day') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <!-- Year -->
                                <div class="flex-1">
                                    <select name="dob_year" id="dob_year" data-select='{
                                        "placeholder": "Year",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "hasSearch": true,
                                        "searchPlaceholder": "Search...",
                                        "dropdownClasses": "advance-select-menu",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden">
                                        <option value="">Year</option>
                                        @for($year = date('Y'); $year >= 1900; $year--)
                                            <option value="{{ $year }}" {{ old('dob_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="date_of_birth" id="date_of_birth" required>
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Relationship <span class="text-rose-500">*</span>
                            </label>
                            <select name="relationship" id="relationship-select" required data-select='{
                                "placeholder": "Select relationship",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "hasSearch": true,
                                "searchPlaceholder": "Search...",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">Choose</option>
                                @foreach($relationships as $key => $label)
                                    <option value="{{ $key }}" {{ old('relationship') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('relationship')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="mb-8 pt-6 border-t border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Contact Information</h2>
                    <p class="text-sm text-slate-500 mb-4">Optional contact details for this member</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                            <div class="flex gap-2">
                                <select name="phone_country_code" class="w-24 px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                                    <option value="+1" {{ old('phone_country_code', '+1') === '+1' ? 'selected' : '' }}>+1</option>
                                    <option value="+44" {{ old('phone_country_code') === '+44' ? 'selected' : '' }}>+44</option>
                                    <option value="+91" {{ old('phone_country_code') === '+91' ? 'selected' : '' }}>+91</option>
                                    <option value="+61" {{ old('phone_country_code') === '+61' ? 'selected' : '' }}>+61</option>
                                </select>
                                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="555-123-4567"
                                    class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('phone') border-rose-500 @enderror">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Parent Information Section -->
                <div class="mb-8 pt-6 border-t border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Parent Information</h2>
                    <p class="text-sm text-slate-500 mb-4">Optional parent details for this member</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Father's Name</label>
                            <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="Full name" maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Mother's Name</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="Full name" maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="mb-8 pt-6 border-t border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Status</h2>
                    <p class="text-sm text-slate-500 mb-4">Additional member details</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Immigration Status</label>
                            <select name="immigration_status" id="immigration-status-select" data-select='{
                                "placeholder": "Select status",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "hasSearch": true,
                                "searchPlaceholder": "Search...",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">Choose</option>
                                @foreach($immigrationStatuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('immigration_status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_minor" value="1" {{ old('is_minor') ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <span class="text-sm font-medium text-slate-700">This person is a minor (under 18)</span>
                                    <p class="text-xs text-slate-500">Enable additional protections and features for minors</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="co_parenting_enabled" value="1" {{ old('co_parenting_enabled') ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <span class="text-sm font-medium text-slate-700">Enable co-parenting features</span>
                                    <p class="text-xs text-slate-500">Allow sharing information with co-parents</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="card-body pt-0">
                <div class="flex items-center justify-start gap-3 pt-6 border-t border-slate-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        Add Family Member
                    </button>
                    <a href="{{ route('family-circle.show', $circle) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Profile image preview
function previewImage(input) {
    const preview = document.getElementById('profilePreview');
    const container = document.getElementById('profilePreviewContainer');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            // Hide the default icon
            container.querySelector('svg').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const dobMonth = document.getElementById('dob_month');
    const dobDay = document.getElementById('dob_day');
    const dobYear = document.getElementById('dob_year');
    const dobHidden = document.getElementById('date_of_birth');

    // Combine date parts into hidden field
    function updateDateOfBirth() {
        const month = dobMonth.value;
        const day = dobDay.value;
        const year = dobYear.value;

        if (month && day && year) {
            dobHidden.value = `${month}/${day}/${year}`;
        } else {
            dobHidden.value = '';
        }
    }

    // Update on change
    dobMonth.addEventListener('change', updateDateOfBirth);
    dobDay.addEventListener('change', updateDateOfBirth);
    dobYear.addEventListener('change', updateDateOfBirth);

    // Initial update
    updateDateOfBirth();
});
</script>
@endpush
