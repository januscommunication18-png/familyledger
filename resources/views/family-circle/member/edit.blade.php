@extends('layouts.dashboard')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('title', 'Edit ' . $member->full_name)
@section('page-name', 'Edit Member')

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
    <li><a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="hover:text-violet-600">{{ $member->full_name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Edit</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Edit Family Member</h1>
        <p class="text-slate-500 mt-1">Update information for {{ $member->full_name }}</p>
    </div>

    <!-- Form Card -->
    <div class="card bg-base-100 shadow-sm">
        <form action="{{ route('family-circle.member.update', [$circle, $member]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <!-- Profile Photo & Basic Information Section -->
                <div class="mb-8">
                    <div class="flex items-start gap-6 mb-6">
                        <!-- Profile Photo Circle -->
                        <div class="shrink-0">
                            <div class="relative">
                                <div id="profilePreviewContainer" class="w-24 h-24 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center overflow-hidden border-4 border-white shadow-lg">
                                    @if($member->profile_image_url)
                                        <img id="profilePreview" src="{{ $member->profile_image_url }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="hidden"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <img id="profilePreview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                                    @endif
                                </div>
                                <label for="profile_image" class="absolute -bottom-1 -right-1 w-8 h-8 bg-violet-600 hover:bg-violet-700 rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                </label>
                                <input type="file" name="profile_image" id="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp" class="hidden" onchange="previewImage(this)">
                            </div>
                            <p class="text-xs text-slate-500 text-center mt-2">Profile Photo</p>
                            <p id="profileImageError" class="text-xs text-red-500 text-center mt-1 hidden">Invalid format. Use JPG, PNG, GIF or WebP.</p>
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
                            <input type="text" name="first_name" value="{{ old('first_name', $member->first_name) }}" placeholder="John" required maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('first_name') border-rose-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Last Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name', $member->last_name) }}" placeholder="Doe" required maxlength="255"
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
                                    <select name="dob_month" id="dob_month" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                                        <option value="">Month</option>
                                        <option value="01" {{ old('dob_month', $member->date_of_birth?->format('m')) == '01' ? 'selected' : '' }}>January</option>
                                        <option value="02" {{ old('dob_month', $member->date_of_birth?->format('m')) == '02' ? 'selected' : '' }}>February</option>
                                        <option value="03" {{ old('dob_month', $member->date_of_birth?->format('m')) == '03' ? 'selected' : '' }}>March</option>
                                        <option value="04" {{ old('dob_month', $member->date_of_birth?->format('m')) == '04' ? 'selected' : '' }}>April</option>
                                        <option value="05" {{ old('dob_month', $member->date_of_birth?->format('m')) == '05' ? 'selected' : '' }}>May</option>
                                        <option value="06" {{ old('dob_month', $member->date_of_birth?->format('m')) == '06' ? 'selected' : '' }}>June</option>
                                        <option value="07" {{ old('dob_month', $member->date_of_birth?->format('m')) == '07' ? 'selected' : '' }}>July</option>
                                        <option value="08" {{ old('dob_month', $member->date_of_birth?->format('m')) == '08' ? 'selected' : '' }}>August</option>
                                        <option value="09" {{ old('dob_month', $member->date_of_birth?->format('m')) == '09' ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ old('dob_month', $member->date_of_birth?->format('m')) == '10' ? 'selected' : '' }}>October</option>
                                        <option value="11" {{ old('dob_month', $member->date_of_birth?->format('m')) == '11' ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ old('dob_month', $member->date_of_birth?->format('m')) == '12' ? 'selected' : '' }}>December</option>
                                    </select>
                                </div>
                                <!-- Day -->
                                <div class="w-20">
                                    <input type="number" name="dob_day" id="dob_day" value="{{ old('dob_day', $member->date_of_birth?->format('d')) }}" placeholder="Day" min="1" max="31"
                                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                </div>
                                <!-- Year -->
                                <div class="w-24">
                                    <input type="number" name="dob_year" id="dob_year" value="{{ old('dob_year', $member->date_of_birth?->format('Y')) }}" placeholder="Year" min="1900" max="{{ date('Y') }}"
                                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                </div>
                            </div>
                            <input type="hidden" name="date_of_birth" id="date_of_birth" value="{{ $member->date_of_birth?->format('m/d/Y') }}" required>
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Relationship <span class="text-rose-500">*</span>
                            </label>
                            <select name="relationship" id="relationship-select" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                                <option value="">Choose</option>
                                @foreach($relationships as $key => $label)
                                    <option value="{{ $key }}" {{ old('relationship', $member->relationship) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('relationship')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Family Circle
                            </label>
                            <select name="family_circle_id" id="family_circle_select" class="w-full">
                                @foreach($allCircles as $circleOption)
                                    <option value="{{ $circleOption->id }}"
                                        data-image="{{ $circleOption->cover_image ? Storage::disk('do_spaces')->url($circleOption->cover_image) : '' }}"
                                        data-members="{{ $circleOption->members_count }}"
                                        {{ old('family_circle_id', $member->family_circle_id) == $circleOption->id ? 'selected' : '' }}>
                                        {{ $circleOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Select a different circle to move this member</p>
                            @error('family_circle_id')
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
                            <input type="email" name="email" value="{{ old('email', $member->email) }}" placeholder="john@example.com"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                            <div class="flex gap-2">
                                <select name="phone_country_code" class="w-24 px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                                    <option value="+1" {{ old('phone_country_code', $member->phone_country_code) === '+1' ? 'selected' : '' }}>+1</option>
                                    <option value="+44" {{ old('phone_country_code', $member->phone_country_code) === '+44' ? 'selected' : '' }}>+44</option>
                                    <option value="+91" {{ old('phone_country_code', $member->phone_country_code) === '+91' ? 'selected' : '' }}>+91</option>
                                    <option value="+61" {{ old('phone_country_code', $member->phone_country_code) === '+61' ? 'selected' : '' }}>+61</option>
                                </select>
                                <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" placeholder="555-123-4567"
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
                            <input type="text" name="father_name" value="{{ old('father_name', $member->father_name) }}" placeholder="Full name" maxlength="255"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Mother's Name</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}" placeholder="Full name" maxlength="255"
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
                            <select name="immigration_status" id="immigration-status-select" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                                <option value="">Choose</option>
                                @foreach($immigrationStatuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('immigration_status', $member->immigration_status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_minor" value="1" {{ old('is_minor', $member->is_minor) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <span class="text-sm font-medium text-slate-700">This person is a minor (under 18)</span>
                                    <p class="text-xs text-slate-500">Enable additional protections and features for minors</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="co_parenting_enabled" value="1" {{ old('co_parenting_enabled', $member->co_parenting_enabled) ? 'checked' : '' }}
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
                    <button type="submit" id="memberSubmitBtn" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Changes
                    </button>
                    <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Profile image preview with validation
const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

function previewImage(input) {
    const preview = document.getElementById('profilePreview');
    const container = document.getElementById('profilePreviewContainer');
    const defaultIcon = container.querySelector('svg');
    const errorEl = document.getElementById('profileImageError');
    const submitBtn = document.getElementById('memberSubmitBtn');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();

        // Validate file type
        if (!allowedImageTypes.includes(file.type) && !allowedExtensions.includes(extension)) {
            // Show error
            errorEl.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-disabled');
            // Reset file input
            input.value = '';
            // Reset preview
            preview.classList.add('hidden');
            if (defaultIcon) {
                defaultIcon.classList.remove('hidden');
            }
            return;
        }

        // Hide error if valid
        errorEl.classList.add('hidden');
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-disabled');

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (defaultIcon) {
                defaultIcon.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
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
        const day = dobDay.value ? String(dobDay.value).padStart(2, '0') : '';
        const year = dobYear.value;

        if (month && day && year) {
            dobHidden.value = `${month}/${day}/${year}`;
        } else {
            dobHidden.value = '';
        }
    }

    // Update on change
    dobMonth.addEventListener('change', updateDateOfBirth);
    dobDay.addEventListener('input', updateDateOfBirth);
    dobYear.addEventListener('input', updateDateOfBirth);

    // Initial update
    updateDateOfBirth();
});

// Family Circle Select2 initialization - separate jQuery ready block
$(document).ready(function() {
    function formatCircleOption(option) {
        if (!option.id) {
            return option.text;
        }

        var $option = $(option.element);
        var imageUrl = $option.data('image');
        var membersCount = $option.data('members') || 0;

        var imageHtml;
        if (imageUrl) {
            imageHtml = '<img src="' + imageUrl + '" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" alt="' + option.text + '">';
        } else {
            imageHtml = '<div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center;">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>' +
            '</div>';
        }

        var $result = $(
            '<div style="display: flex; align-items: center; gap: 12px; padding: 4px 0;">' +
                imageHtml +
                '<div style="flex: 1;">' +
                    '<div style="font-weight: 500; color: #1e293b;">' + option.text + '</div>' +
                    '<div style="font-size: 12px; color: #64748b;">' + membersCount + ' member' + (membersCount !== 1 ? 's' : '') + '</div>' +
                '</div>' +
            '</div>'
        );

        return $result;
    }

    function formatCircleSelection(option) {
        if (!option.id) {
            return option.text;
        }

        var $option = $(option.element);
        var imageUrl = $option.data('image');

        var imageHtml;
        if (imageUrl) {
            imageHtml = '<img src="' + imageUrl + '" style="width: 24px; height: 24px; border-radius: 4px; object-fit: cover;" alt="' + option.text + '">';
        } else {
            imageHtml = '<div style="width: 24px; height: 24px; border-radius: 4px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center;">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>' +
            '</div>';
        }

        var $result = $(
            '<div style="display: flex; align-items: center; gap: 8px;">' +
                imageHtml +
                '<span>' + option.text + '</span>' +
            '</div>'
        );

        return $result;
    }

    $('#family_circle_select').select2({
        templateResult: formatCircleOption,
        templateSelection: formatCircleSelection,
        width: '100%',
        dropdownAutoWidth: false
    });
});
</script>

<style>
/* Select2 Custom Styles */
.select2-container--default .select2-selection--single {
    height: 42px;
    padding: 6px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    background-color: white;
}

.select2-container--default .select2-selection--single:focus,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
    outline: none;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    padding-left: 0;
    color: #0f172a;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
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
</style>
@endpush
