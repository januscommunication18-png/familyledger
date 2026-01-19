<!-- Step 2: Household Setup -->
<h2 class="card-title text-2xl mb-2">Set up your household</h2>
<p class="text-base-content/60 mb-6">Define your family unit and preferences</p>

<form action="/onboarding/step2" method="POST">
    @csrf

    <div class="space-y-4">
        <!-- Personal Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">First Name <span class="text-error">*</span></span>
                </label>
                <input type="text" name="first_name" value="{{ old('first_name', $user['first_name'] ?? '') }}"
                       placeholder="Enter your first name"
                       class="input input-bordered w-full @error('first_name') input-error @enderror" required>
                @error('first_name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Last Name <span class="text-error">*</span></span>
                </label>
                <input type="text" name="last_name" value="{{ old('last_name', $user['last_name'] ?? '') }}"
                       placeholder="Enter your last name"
                       class="input input-bordered w-full @error('last_name') input-error @enderror" required>
                @error('last_name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Email Address</span>
            </label>
            <input type="email" value="{{ $user['email'] ?? '' }}"
                   class="input input-bordered w-full bg-base-200" readonly disabled>
            <label class="label">
                <span class="label-text-alt text-base-content/50">Email cannot be changed</span>
            </label>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Backup Email</span>
            </label>
            <input type="email" name="backup_email" value="{{ old('backup_email', $user['backup_email'] ?? '') }}"
                   placeholder="Enter a backup email address"
                   class="input input-bordered w-full @error('backup_email') input-error @enderror">
            <label class="label">
                <span class="label-text-alt text-base-content/50">Used for account recovery if you lose access</span>
            </label>
            @error('backup_email')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Phone Number</span>
            </label>
            <div class="flex gap-2">
                <select name="country_code" class="select select-bordered w-32">
                    <option value="">Code</option>
                    @foreach($countryCodes as $code => $label)
                        <option value="{{ $code }}" {{ old('country_code', $user['country_code'] ?? '+1') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="tel" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}"
                       placeholder="Phone number"
                       class="input input-bordered flex-1 @error('phone') input-error @enderror">
            </div>
            @error('phone')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="divider"></div>

        <!-- Household Information -->
        <div class="form-control">
            <label class="label">
                <span class="label-text">Household name <span class="text-error">*</span></span>
            </label>
            <input type="text" name="name" value="{{ old('name', $tenant['name'] ?? '') }}"
                   placeholder="e.g., Smith Family, Alex and Jamie"
                   class="input input-bordered w-full @error('name') input-error @enderror" required>
            <label class="label">
                <span class="label-text-alt text-base-content/50">This is your first family circle</span>
            </label>
            @error('name')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Country / Region <span class="text-error">*</span></span>
            </label>
            <input type="hidden" name="country" value="US">
            <input type="text" value="United States" class="input input-bordered w-full bg-base-200" readonly disabled>
            @error('country')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Timezone <span class="text-error">*</span></span>
            </label>
            <select name="timezone" class="select select-bordered w-full @error('timezone') select-error @enderror" required>
                <option value="">Select timezone</option>
                @foreach($timezones as $region => $zones)
                    <optgroup label="{{ $region }}">
                        @foreach($zones as $zone)
                            <option value="{{ $zone }}" {{ old('timezone', $tenant['timezone'] ?? '') === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('timezone')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Family type (optional)</span>
            </label>
            <select name="family_type" class="select select-bordered w-full">
                <option value="">Select family type</option>
                @foreach($familyTypes as $key => $name)
                    <option value="{{ $key }}" {{ old('family_type', $tenant['family_type'] ?? '') === $key ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-actions justify-between mt-8">
        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
        <button type="submit" class="btn btn-primary">Continue</button>
    </div>
</form>

<form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>
