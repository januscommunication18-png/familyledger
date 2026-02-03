@extends('backoffice.layouts.app')

@php
    $header = 'Account Recovery';
@endphp

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('backoffice.account-recovery.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Search
        </a>
    </div>

    @if (session('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-6">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Client Info & Verification -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Client Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Client Information</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Family Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $client->name ?? 'Unnamed' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Owner</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $owner->name ?? 'Unknown' }}</p>
                    </div>

                    @if($isVerified)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $owner->email ?? 'No email' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $owner->phone ?? 'Not set' }}</p>
                    </div>
                    @else
                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                        Verify recovery code to see full details
                    </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Account Status</p>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $client->is_active ?? true ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                            {{ $client->is_active ?? true ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">2FA Status</p>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $owner && $owner->mfa_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $owner && $owner->mfa_enabled ? ($owner->mfa_method === 'sms' ? 'SMS' : 'Authenticator') . ' Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Recovery Code Status</p>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $owner && $owner->hasAccountRecoveryCode() ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                            {{ $owner && $owner->hasAccountRecoveryCode() ? 'Set' : 'Not Set' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $client->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Verification Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Verification Status</h3>

                @if($isVerified)
                    <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-green-800 dark:text-green-300">Verified</p>
                            <p class="text-sm text-green-600 dark:text-green-400">Recovery actions enabled</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Session expires after 30 minutes of inactivity
                    </p>
                @else
                    <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-amber-800 dark:text-amber-300">Not Verified</p>
                            <p class="text-sm text-amber-600 dark:text-amber-400">Enter recovery code to proceed</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Verification & Actions -->
        <div class="lg:col-span-2 space-y-6">
            @if(!$isVerified)
                <!-- Recovery Code Verification -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Verify Recovery Code</h3>

                    @if(!$owner || !$owner->hasAccountRecoveryCode())
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <p class="text-red-700 dark:text-red-400">
                                <strong>Cannot proceed:</strong> This account does not have a recovery code set.
                                The client must set up a recovery code in their settings before account recovery is possible.
                            </p>
                        </div>
                    @else
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Ask the client to provide their 16-digit recovery code. Enter it below to verify their identity.
                        </p>

                        <div id="verifyCodeForm" class="space-y-4">
                            <div>
                                <label for="recovery_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    16-Digit Recovery Code
                                </label>
                                <input
                                    type="text"
                                    id="recovery_code"
                                    maxlength="16"
                                    placeholder="0000000000000000"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-xl tracking-wider font-mono focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 16)"
                                >
                            </div>

                            <div id="verifyError" class="hidden p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm"></div>

                            <button
                                type="button"
                                onclick="verifyRecoveryCode()"
                                id="verifyBtn"
                                class="w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Verify Code
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <!-- Recovery Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Recovery Actions</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Select an action to help the client recover their account. All actions require a reason for audit purposes.
                    </p>

                    <div class="space-y-4">
                        <!-- Change Email -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Change Email Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Update the client's email address</p>
                                </div>
                                <button type="button" onclick="toggleAction('email')" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm font-medium">
                                    Expand
                                </button>
                            </div>
                            <form id="emailAction" action="{{ route('backoffice.account-recovery.changeEmail', $client) }}" method="POST" class="hidden space-y-3">
                                @csrf
                                <div>
                                    <label for="current_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Email</label>
                                    <input type="email" id="current_email" value="{{ $owner->email ?? '' }}" disabled class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                                </div>
                                <div>
                                    <label for="new_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Email</label>
                                    <input type="email" id="new_email" name="new_email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label for="email_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                    <textarea name="reason" id="email_reason" required rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Why is this change needed?"></textarea>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Change Email
                                </button>
                            </form>
                        </div>

                        <!-- Reset Password -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Reset Password</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set a new password for the client</p>
                                </div>
                                <button type="button" onclick="toggleAction('password')" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm font-medium">
                                    Expand
                                </button>
                            </div>
                            <form id="passwordAction" action="{{ route('backoffice.account-recovery.resetPassword', $client) }}" method="POST" class="hidden space-y-3">
                                @csrf
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                                    <input type="text" id="new_password" name="new_password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Enter new password (min 8 characters)">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Share this password with the client securely</p>
                                </div>
                                <div>
                                    <label for="password_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                    <textarea name="reason" id="password_reason" required rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Why is this change needed?"></textarea>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Reset Password
                                </button>
                            </form>
                        </div>

                        <!-- Disable 2FA -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Disable Two-Factor Authentication</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Remove 2FA requirement from the account</p>
                                </div>
                                @if($owner && $owner->mfa_enabled)
                                <button type="button" onclick="toggleAction('2fa')" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm font-medium">
                                    Expand
                                </button>
                                @endif
                            </div>
                            @if($owner && $owner->mfa_enabled)
                            <form id="2faAction" action="{{ route('backoffice.account-recovery.disable2fa', $client) }}" method="POST" class="hidden space-y-3">
                                @csrf
                                <div>
                                    <label for="current_2fa_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current 2FA Method</label>
                                    <input type="text" id="current_2fa_method" value="{{ $owner->mfa_method === 'sms' ? 'SMS' : 'Authenticator App' }}" disabled class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                                </div>
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-400">
                                    This will disable all two-factor authentication methods for this account.
                                </div>
                                <div>
                                    <label for="twofa_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                    <textarea name="reason" id="twofa_reason" required rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Why is this change needed?"></textarea>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Disable 2FA
                                </button>
                            </form>
                            @else
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    2FA is not enabled for this account
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Reset Phone -->
                        @if($owner && $owner->phone)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Reset Phone Number</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Remove the phone number from the account</p>
                                </div>
                                <button type="button" onclick="toggleAction('phone')" class="text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm font-medium">
                                    Expand
                                </button>
                            </div>
                            <form id="phoneAction" action="{{ route('backoffice.account-recovery.resetPhone', $client) }}" method="POST" class="hidden space-y-3">
                                @csrf
                                <div>
                                    <label for="current_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Phone Number</label>
                                    <input type="text" id="current_phone" value="{{ $owner->phone }}" disabled class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                                </div>
                                @if($owner->mfa_method === 'sms')
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-400">
                                    This will also disable SMS-based 2FA since it requires a phone number.
                                </div>
                                @endif
                                <div>
                                    <label for="phone_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                    <textarea name="reason" id="phone_reason" required rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Why is this change needed?"></textarea>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Reset Phone Number
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-medium text-gray-700 dark:text-gray-300">Important Notes</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>All actions are logged with your admin ID and the reason provided</li>
                                <li>The client will NOT be notified of these changes automatically</li>
                                <li>Data deletion is not available through this interface for security reasons</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function verifyRecoveryCode() {
            const code = document.getElementById('recovery_code').value.trim();
            const errorEl = document.getElementById('verifyError');
            const btn = document.getElementById('verifyBtn');

            if (code.length !== 16 || !/^\d+$/.test(code)) {
                errorEl.textContent = 'Please enter a valid 16-digit code';
                errorEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Verifying...';
            errorEl.classList.add('hidden');

            fetch('{{ route("backoffice.account-recovery.verifyCode", $client) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ recovery_code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    errorEl.textContent = data.message || 'Verification failed';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Verify Code';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorEl.textContent = 'An error occurred. Please try again.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Verify Code';
            });
        }

        function toggleAction(action) {
            const form = document.getElementById(action + 'Action');
            const isHidden = form.classList.contains('hidden');

            // Hide all forms first
            document.querySelectorAll('[id$="Action"]').forEach(el => {
                el.classList.add('hidden');
            });

            // Toggle the clicked one
            if (isHidden) {
                form.classList.remove('hidden');
            }
        }

        // Allow Enter key to verify
        document.getElementById('recovery_code')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyRecoveryCode();
            }
        });
    </script>
@endsection
