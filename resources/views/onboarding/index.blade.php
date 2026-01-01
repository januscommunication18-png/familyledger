@extends('layouts.app')

@section('title', 'Setup Your Account')

@section('content')
<div class="min-h-screen bg-base-200 py-8">
    <div class="container mx-auto max-w-2xl px-4">
        <!-- Progress indicator -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-base-content/60">Step {{ $step }} of {{ $totalSteps }}</span>
                <span class="text-sm font-medium">{{ round(($step / $totalSteps) * 100) }}% complete</span>
            </div>
            <div class="w-full bg-base-300 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full transition-all duration-300" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>

        <!-- Step Cards -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- DEBUG: Step value is: {{ $step }} -->

                @if($step == 1)
                <!-- Step 1: Goals -->
                <h2 class="card-title text-2xl mb-2">Welcome! Let's get started</h2>
                <p class="text-base-content/60 mb-6">What's your primary goal for using this app?</p>

                <form action="/onboarding/step1" method="POST">
                    @csrf
                    @error('goals')
                        <div class="alert alert-error mb-4">{{ $message }}</div>
                    @enderror

                    <div class="space-y-3">
                        @foreach($goals as $key => $goal)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="goals[]" value="{{ $key }}"
                                       class="checkbox checkbox-primary mt-1"
                                       {{ in_array($key, $tenant['goals'] ?? []) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-medium">{{ $goal['title'] }}</div>
                                    <div class="text-sm text-base-content/60">{{ $goal['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="divider"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                Backup Email
                                <span class="badge badge-success badge-sm">Recommended</span>
                            </span>
                        </label>
                        <input type="email" name="backup_email" value="{{ old('backup_email', $user['backup_email'] ?? '') }}"
                               placeholder="Enter a backup email address"
                               class="input input-bordered w-full @error('backup_email') input-error @enderror">
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">Use a different email for account recovery</span>
                        </label>
                        @error('backup_email')
                            <label class="label pt-0"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="/dashboard" class="btn btn-ghost">Skip for now</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>

                @elseif($step == 2)
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
                                <span class="label-text">Phone Number</span>
                            </label>
                            <div class="flex gap-2">
                                <select name="country_code" class="select select-bordered w-32">
                                    <option value="">Code</option>
                                    @foreach($countryCodes as $code => $label)
                                        <option value="{{ $code }}" {{ old('country_code', $user['country_code'] ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
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
                            <label class="label"><span class="label-text">Household name <span class="text-error">*</span></span></label>
                            <input type="text" name="name" value="{{ old('name', $tenant['name'] ?? '') }}"
                                   placeholder="e.g., Smith Family" class="input input-bordered w-full" required>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Country <span class="text-error">*</span></span></label>
                            <select name="country" id="country-select" class="select select-bordered w-full" required>
                                <option value="">Select country</option>
                                @foreach($countries as $code => $name)
                                    <option value="{{ $code }}" {{ old('country', $tenant['country'] ?? '') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Timezone <span class="text-error">*</span></span></label>
                            <select name="timezone" id="timezone-select" class="select select-bordered w-full" required disabled>
                                <option value="">Select country first</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Family type (optional)</span></label>
                            <select name="family_type" id="family-type-select" class="select select-bordered w-full">
                                <option value="">Select family type</option>
                                @foreach($familyTypes as $key => $name)
                                    <option value="{{ $key }}" {{ old('family_type', $tenant['family_type'] ?? '') == $key ? 'selected' : '' }}>{{ $name }}</option>
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

                <script>
                    const timezonesByCountry = {
                        'US': [
                            { value: 'America/New_York', label: 'Eastern (New York)' },
                            { value: 'America/Chicago', label: 'Central (Chicago)' },
                            { value: 'America/Denver', label: 'Mountain (Denver)' },
                            { value: 'America/Phoenix', label: 'Arizona (Phoenix)' },
                            { value: 'America/Los_Angeles', label: 'Pacific (Los Angeles)' },
                            { value: 'America/Anchorage', label: 'Alaska (Anchorage)' },
                            { value: 'Pacific/Honolulu', label: 'Hawaii (Honolulu)' }
                        ],
                        'GB': [
                            { value: 'Europe/London', label: 'London' }
                        ],
                        'CA': [
                            { value: 'America/Toronto', label: 'Eastern (Toronto)' },
                            { value: 'America/Vancouver', label: 'Pacific (Vancouver)' },
                            { value: 'America/Edmonton', label: 'Mountain (Edmonton)' },
                            { value: 'America/Winnipeg', label: 'Central (Winnipeg)' },
                            { value: 'America/Halifax', label: 'Atlantic (Halifax)' },
                            { value: 'America/St_Johns', label: 'Newfoundland (St. John\'s)' }
                        ],
                        'AU': [
                            { value: 'Australia/Sydney', label: 'Sydney' },
                            { value: 'Australia/Melbourne', label: 'Melbourne' },
                            { value: 'Australia/Brisbane', label: 'Brisbane' },
                            { value: 'Australia/Perth', label: 'Perth' },
                            { value: 'Australia/Adelaide', label: 'Adelaide' },
                            { value: 'Australia/Darwin', label: 'Darwin' },
                            { value: 'Australia/Hobart', label: 'Hobart' }
                        ],
                        'DE': [
                            { value: 'Europe/Berlin', label: 'Berlin' }
                        ],
                        'FR': [
                            { value: 'Europe/Paris', label: 'Paris' }
                        ],
                        'OTHER': [
                            { value: 'UTC', label: 'UTC (Coordinated Universal Time)' }
                        ]
                    };

                    const countrySelect = document.getElementById('country-select');
                    const timezoneSelect = document.getElementById('timezone-select');
                    const savedTimezone = "{{ old('timezone', $tenant['timezone'] ?? '') }}";

                    function updateTimezones() {
                        const country = countrySelect.value;
                        timezoneSelect.innerHTML = '';

                        if (!country) {
                            timezoneSelect.innerHTML = '<option value="">Select country first</option>';
                            timezoneSelect.disabled = true;
                            return;
                        }

                        const timezones = timezonesByCountry[country] || [];
                        timezoneSelect.disabled = false;

                        if (timezones.length === 0) {
                            timezoneSelect.innerHTML = '<option value="">No timezones available</option>';
                            return;
                        }

                        timezoneSelect.innerHTML = '<option value="">Select timezone</option>';
                        timezones.forEach(tz => {
                            const option = document.createElement('option');
                            option.value = tz.value;
                            option.textContent = tz.label;
                            if (tz.value === savedTimezone) {
                                option.selected = true;
                            }
                            timezoneSelect.appendChild(option);
                        });
                    }

                    countrySelect.addEventListener('change', updateTimezones);

                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        if (countrySelect.value) {
                            updateTimezones();
                        }
                    });
                </script>

                @elseif($step == 3)
                <!-- Step 3: Role Selection -->
                <h2 class="card-title text-2xl mb-2">What's your role?</h2>
                <p class="text-base-content/60 mb-6">This helps us set appropriate permissions</p>

                <form action="/onboarding/step3" method="POST">
                    @csrf
                    <div class="space-y-3">
                        @foreach($roles as $key => $role)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="role" value="{{ $key }}" class="radio radio-primary mt-1"
                                       {{ old('role', $user['role'] ?? 'parent') == $key ? 'checked' : '' }} required>
                                <div class="ml-3">
                                    <div class="font-medium">{{ $role['title'] }}</div>
                                    <div class="text-sm text-base-content/60">{{ $role['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 4)
                <!-- Step 4: Quick Setup -->
                <h2 class="card-title text-2xl mb-2">What do you want to set up first?</h2>
                <p class="text-base-content/60 mb-6">Select one or more to get started</p>

                <form action="/onboarding/step4" method="POST">
                    @csrf
                    <div class="space-y-3">
                        @foreach($quickSetup as $key => $item)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="quick_setup[]" value="{{ $key }}"
                                       class="checkbox checkbox-primary mt-1"
                                       {{ in_array($key, $tenant['quick_setup'] ?? []) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <div class="font-medium text-sm">{{ $item['title'] }}</div>
                                    <div class="text-xs text-base-content/60">{{ $item['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 5)
                <!-- Step 5: Security & Finish -->
                <h2 class="card-title text-2xl mb-2">Secure your account</h2>
                <p class="text-base-content/60 mb-6">Set up security options and notifications</p>

                <form action="/onboarding/step5" method="POST" id="step5-form">
                    @csrf
                    <div class="space-y-4">
                        <!-- Notifications Section -->
                        <div class="form-control">
                            <label class="label pb-0">
                                <span class="label-text flex items-center gap-2">
                                    <input type="checkbox" name="email_notifications" value="1" class="checkbox checkbox-primary checkbox-sm" checked>
                                    Email notifications
                                </span>
                            </label>
                            <label class="label pt-0">
                                <span class="label-text-alt text-base-content/60">Receive important updates via email</span>
                            </label>
                        </div>

                        <div class="divider">Two-Factor Authentication</div>

                        <!-- Authenticator App -->
                        <div class="form-control">
                            <label class="label pb-0">
                                <span class="label-text flex items-center gap-2">
                                    <input type="checkbox" name="enable_2fa" value="1" id="authenticator-checkbox" class="checkbox checkbox-primary checkbox-sm">
                                    Authenticator app
                                    <span class="badge badge-success badge-sm">Recommended</span>
                                </span>
                            </label>
                            <label class="label pt-0">
                                <span class="label-text-alt text-base-content/60">Use Google Authenticator or similar app</span>
                            </label>

                            <!-- 2FA Setup Section (hidden by default) -->
                            <div id="authenticator-setup-section" class="hidden mt-3 ml-6">
                                <!-- Step 1: Show QR Code -->
                                <div id="qr-code-section">
                                    <div class="bg-base-200 rounded-lg p-4">
                                        <p class="text-sm font-medium mb-3">Scan this QR code with your authenticator app:</p>
                                        <div id="qr-code-container" class="flex justify-center mb-3">
                                            <div class="skeleton w-48 h-48"></div>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs text-base-content/60 mb-2">Or enter this code manually:</p>
                                            <span id="secret-key" class="text-xs bg-base-100 border border-base-300 px-3 py-1.5 rounded font-mono select-all inline-block">Loading...</span>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <p class="text-sm text-base-content/70 mb-2">Enter the 6-digit code from your app to verify:</p>
                                        <div class="flex gap-2">
                                            <input type="text" id="totp-code" maxlength="6" placeholder="000000"
                                                   class="input input-bordered input-sm w-28 text-center tracking-widest font-mono">
                                            <button type="button" id="verify-totp-btn" class="btn btn-sm btn-primary">Verify</button>
                                        </div>
                                        <p id="totp-error" class="text-xs text-error mt-1 hidden"></p>
                                    </div>
                                </div>

                                <!-- Step 2: Verified state -->
                                <div id="authenticator-verified-section" class="hidden">
                                    <div class="flex items-center gap-2 text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-medium">Authenticator app configured successfully</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="two_factor_confirmed" id="two-factor-confirmed" value="0">
                        </div>

                        <!-- Phone Verification (Coming Soon) -->
                        <div class="form-control opacity-50">
                            <label class="label pb-0">
                                <span class="label-text flex items-center gap-2">
                                    <input type="checkbox" name="enable_phone_2fa" value="1" id="phone-2fa-checkbox" class="checkbox checkbox-primary checkbox-sm" disabled>
                                    Phone verification (SMS)
                                    <span class="badge badge-neutral badge-sm">Coming Soon</span>
                                </span>
                            </label>
                            <label class="label pt-0">
                                <span class="label-text-alt text-base-content/60">Receive verification codes via SMS</span>
                            </label>
                        </div>

                        <div class="divider">Recovery Options</div>

                        <!-- Recovery Codes -->
                        <div class="bg-base-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="font-medium">Recovery backup codes</h4>
                                    <p class="text-sm text-base-content/60 mb-3">Download backup codes to recover your account if you lose access to your phone or authenticator app.</p>

                                    <!-- Generate Button -->
                                    <div id="generate-section">
                                        <button type="button" id="generate-codes-btn" class="btn btn-sm btn-outline btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Generate & Download Codes
                                        </button>
                                    </div>

                                    <!-- Codes Display (hidden initially) -->
                                    <div id="codes-section" class="hidden">
                                        <div class="alert alert-warning mb-3 py-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <span class="text-xs">Save these codes! Each can only be used once.</span>
                                        </div>
                                        <div id="recovery-codes-list" class="grid grid-cols-2 gap-2 p-3 bg-base-100 rounded-lg font-mono text-xs mb-3">
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" id="copy-codes-btn" class="btn btn-xs btn-outline">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Copy
                                            </button>
                                            <button type="button" id="download-codes-btn" class="btn btn-xs btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Download
                                            </button>
                                            <button type="button" id="regenerate-codes-btn" class="btn btn-xs btn-ghost">
                                                Regenerate
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="recovery_codes_generated" id="recovery-codes-generated" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Complete Setup</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                <script>
                    let recoveryCodes = [];

                    async function generateCodes() {
                        const generateBtn = document.getElementById('generate-codes-btn');
                        const regenerateBtn = document.getElementById('regenerate-codes-btn');
                        const activeBtn = generateBtn.classList.contains('hidden') ? regenerateBtn : generateBtn;

                        activeBtn.classList.add('loading');
                        activeBtn.disabled = true;

                        try {
                            const response = await fetch('/onboarding/generate-recovery-codes', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const data = await response.json();

                            if (data.codes) {
                                recoveryCodes = data.codes;

                                // Display codes
                                const codesList = document.getElementById('recovery-codes-list');
                                codesList.innerHTML = data.codes.map(code =>
                                    '<div class="p-1.5 bg-base-200 rounded text-center">' + code + '</div>'
                                ).join('');

                                // Show codes section, hide generate button
                                document.getElementById('generate-section').classList.add('hidden');
                                document.getElementById('codes-section').classList.remove('hidden');
                                document.getElementById('recovery-codes-generated').value = '1';

                                // Auto-download
                                downloadCodes();
                            }
                        } catch (error) {
                            console.error('Error generating codes:', error);
                            alert('Failed to generate recovery codes. Please try again.');
                        } finally {
                            activeBtn.classList.remove('loading');
                            activeBtn.disabled = false;
                        }
                    }

                    function downloadCodes() {
                        if (recoveryCodes.length === 0) return;

                        const text = "Family Ledger Recovery Codes\n" +
                                    "Generated: " + new Date().toLocaleDateString() + "\n" +
                                    "================================\n\n" +
                                    recoveryCodes.join('\n') + "\n\n" +
                                    "================================\n" +
                                    "Keep these codes safe. Each code can only be used once.";

                        const blob = new Blob([text], { type: 'text/plain' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'familyledger-recovery-codes.txt';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    }

                    document.getElementById('generate-codes-btn').addEventListener('click', generateCodes);
                    document.getElementById('regenerate-codes-btn').addEventListener('click', generateCodes);
                    document.getElementById('download-codes-btn').addEventListener('click', downloadCodes);

                    document.getElementById('copy-codes-btn').addEventListener('click', function() {
                        if (recoveryCodes.length > 0) {
                            const text = recoveryCodes.join('\n');
                            navigator.clipboard.writeText(text).then(() => {
                                const btn = this;
                                const originalHTML = btn.innerHTML;
                                btn.innerHTML = 'Copied!';
                                setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
                            });
                        }
                    });

                    // Authenticator App Logic
                    const authCheckbox = document.getElementById('authenticator-checkbox');
                    const authSetupSection = document.getElementById('authenticator-setup-section');
                    const qrCodeSection = document.getElementById('qr-code-section');
                    const authVerifiedSection = document.getElementById('authenticator-verified-section');
                    const qrCodeContainer = document.getElementById('qr-code-container');
                    const secretKeyEl = document.getElementById('secret-key');
                    const totpCodeInput = document.getElementById('totp-code');
                    const verifyTotpBtn = document.getElementById('verify-totp-btn');
                    const totpError = document.getElementById('totp-error');
                    const twoFactorConfirmed = document.getElementById('two-factor-confirmed');

                    let currentSecret = null;

                    if (authCheckbox) {
                        authCheckbox.addEventListener('change', async function() {
                            if (this.checked) {
                                authSetupSection.classList.remove('hidden');

                                // Check if already verified
                                if (twoFactorConfirmed.value === '1') {
                                    qrCodeSection.classList.add('hidden');
                                    authVerifiedSection.classList.remove('hidden');
                                    return;
                                }

                                // Generate 2FA secret and QR code
                                try {
                                    const response = await fetch('/onboarding/generate-2fa-secret', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        currentSecret = data.secret;
                                        secretKeyEl.textContent = data.secret;
                                        qrCodeContainer.innerHTML = data.qr_code;
                                    } else {
                                        alert('Failed to generate 2FA secret. Please try again.');
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                    alert('Failed to generate 2FA secret. Please try again.');
                                }
                            } else {
                                authSetupSection.classList.add('hidden');
                                twoFactorConfirmed.value = '0';
                            }
                        });

                        // Verify TOTP code
                        if (verifyTotpBtn) {
                            verifyTotpBtn.addEventListener('click', async function() {
                                const code = totpCodeInput.value.trim();
                                if (code.length !== 6) {
                                    totpError.textContent = 'Please enter a 6-digit code';
                                    totpError.classList.remove('hidden');
                                    return;
                                }

                                this.classList.add('loading');
                                this.disabled = true;
                                totpError.classList.add('hidden');

                                try {
                                    const response = await fetch('/onboarding/verify-2fa-code', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ code: code, secret: currentSecret })
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        qrCodeSection.classList.add('hidden');
                                        authVerifiedSection.classList.remove('hidden');
                                        twoFactorConfirmed.value = '1';
                                    } else {
                                        totpError.textContent = data.message || 'Invalid code. Please try again.';
                                        totpError.classList.remove('hidden');
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                    totpError.textContent = 'Verification failed. Please try again.';
                                    totpError.classList.remove('hidden');
                                } finally {
                                    this.classList.remove('loading');
                                    this.disabled = false;
                                }
                            });
                        }
                    }

                    // Phone Verification Logic
                    const phoneCheckbox = document.getElementById('phone-2fa-checkbox');
                    const phoneVerifySection = document.getElementById('phone-verify-section');
                    const verifyBtnSection = document.getElementById('verify-btn-section');
                    const codeInputSection = document.getElementById('code-input-section');
                    const verifiedSection = document.getElementById('verified-section');
                    const sendCodeBtn = document.getElementById('send-code-btn');
                    const verifyCodeBtn = document.getElementById('verify-code-btn');
                    const resendCodeBtn = document.getElementById('resend-code-btn');
                    const verificationCodeInput = document.getElementById('verification-code');
                    const codeError = document.getElementById('code-error');
                    const phoneVerifiedInput = document.getElementById('phone-verified-input');

                    if (phoneCheckbox && phoneVerifySection) {
                        // Show/hide verify section based on checkbox
                        phoneCheckbox.addEventListener('change', function() {
                            if (this.checked) {
                                phoneVerifySection.classList.remove('hidden');
                                // Check if already verified
                                if (phoneVerifiedInput && phoneVerifiedInput.value === '1') {
                                    verifyBtnSection.classList.add('hidden');
                                    codeInputSection.classList.add('hidden');
                                    verifiedSection.classList.remove('hidden');
                                }
                            } else {
                                phoneVerifySection.classList.add('hidden');
                            }
                        });

                        // Send verification code
                        if (sendCodeBtn) {
                            sendCodeBtn.addEventListener('click', async function() {
                                this.classList.add('loading');
                                this.disabled = true;

                                try {
                                    const response = await fetch('/onboarding/send-phone-code', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        verifyBtnSection.classList.add('hidden');
                                        codeInputSection.classList.remove('hidden');
                                        verificationCodeInput.focus();
                                    } else {
                                        alert(data.message || 'Failed to send code. Please try again.');
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                    alert('Failed to send verification code. Please try again.');
                                } finally {
                                    this.classList.remove('loading');
                                    this.disabled = false;
                                }
                            });
                        }

                        // Verify code
                        if (verifyCodeBtn) {
                            verifyCodeBtn.addEventListener('click', async function() {
                                const code = verificationCodeInput.value.trim();
                                if (code.length !== 6) {
                                    codeError.textContent = 'Please enter a 6-digit code';
                                    codeError.classList.remove('hidden');
                                    return;
                                }

                                this.classList.add('loading');
                                this.disabled = true;
                                codeError.classList.add('hidden');

                                try {
                                    const response = await fetch('/onboarding/verify-phone-code', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ code: code })
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        codeInputSection.classList.add('hidden');
                                        verifiedSection.classList.remove('hidden');
                                        phoneVerifiedInput.value = '1';
                                    } else {
                                        codeError.textContent = data.message || 'Invalid code. Please try again.';
                                        codeError.classList.remove('hidden');
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                    codeError.textContent = 'Verification failed. Please try again.';
                                    codeError.classList.remove('hidden');
                                } finally {
                                    this.classList.remove('loading');
                                    this.disabled = false;
                                }
                            });
                        }

                        // Resend code
                        if (resendCodeBtn) {
                            resendCodeBtn.addEventListener('click', async function() {
                                this.classList.add('loading');
                                this.disabled = true;

                                try {
                                    const response = await fetch('/onboarding/send-phone-code', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.textContent = 'Sent!';
                                        setTimeout(() => { this.textContent = 'Resend'; }, 2000);
                                    } else {
                                        alert(data.message || 'Failed to resend code.');
                                    }
                                } catch (error) {
                                    alert('Failed to resend code. Please try again.');
                                } finally {
                                    this.classList.remove('loading');
                                    this.disabled = false;
                                }
                            });
                        }
                    }
                </script>

                @else
                <!-- Fallback -->
                <p>Loading step {{ $step }}...</p>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
