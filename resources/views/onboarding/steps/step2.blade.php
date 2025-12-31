<!-- Step 2: Household Setup -->
<h2 class="card-title text-2xl mb-2">Set up your household</h2>
<p class="text-base-content/60 mb-6">Define your family unit and preferences</p>

<form action="/onboarding/step2" method="POST">
    @csrf

    <div class="space-y-4">
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
            <select name="country" class="select select-bordered w-full @error('country') select-error @enderror" required>
                <option value="">Select country</option>
                @foreach($countries as $code => $name)
                    <option value="{{ $code }}" {{ old('country', $tenant['country'] ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
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
