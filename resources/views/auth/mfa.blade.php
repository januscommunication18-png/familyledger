@extends('layouts.auth')

@section('title', 'Two-Factor Authentication')

@section('content')
<div id="mfa-app">
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-warning/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-2xl font-semibold">Two-Factor Authentication</h2>
        <p class="text-base-content/60 mt-2" id="method-description">
            @if($method === 'sms')
                Enter the code sent to your phone ending in {{ $phone_last_four }}
            @elseif($method === 'email')
                Enter the code sent to {{ $email }}
            @else
                Enter the code from your authenticator app
            @endif
        </p>
    </div>

    <!-- Method Selection -->
    <div class="mb-6">
        <label class="label">
            <span class="label-text text-base-content/70">Verification Method</span>
        </label>
        <div class="grid grid-cols-3 gap-2">
            <!-- Authenticator Option -->
            @if(in_array('authenticator', $availableMethods))
            <button type="button" id="btn-authenticator" onclick="selectMethod('authenticator')"
                    class="btn btn-sm {{ $method === 'authenticator' ? 'btn-primary' : 'btn-outline' }} flex-col h-auto py-3 gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-xs">Authenticator</span>
            </button>
            @else
            <button type="button" disabled class="btn btn-sm btn-outline btn-disabled flex-col h-auto py-3 gap-1 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-xs">Authenticator</span>
                <span class="badge badge-xs badge-ghost">Not Set</span>
            </button>
            @endif

            <!-- Email Option -->
            @if(in_array('email', $availableMethods))
            <button type="button" id="btn-email" onclick="selectMethod('email')"
                    class="btn btn-sm {{ $method === 'email' ? 'btn-primary' : 'btn-outline' }} flex-col h-auto py-3 gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-xs">Email</span>
            </button>
            @else
            <button type="button" disabled class="btn btn-sm btn-outline btn-disabled flex-col h-auto py-3 gap-1 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-xs">Email</span>
                <span class="badge badge-xs badge-ghost">Not Set</span>
            </button>
            @endif

            <!-- SMS Option -->
            @if(in_array('sms', $availableMethods))
            <button type="button" id="btn-sms" onclick="selectMethod('sms')"
                    class="btn btn-sm {{ $method === 'sms' ? 'btn-primary' : 'btn-outline' }} flex-col h-auto py-3 gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="text-xs">SMS</span>
            </button>
            @else
            <button type="button" disabled class="btn btn-sm btn-outline btn-disabled flex-col h-auto py-3 gap-1 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="text-xs">SMS</span>
                <span class="badge badge-xs badge-warning">Soon</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Authenticator Instructions -->
    <div id="authenticator-section" class="{{ $method === 'authenticator' ? '' : 'hidden' }}">
        <div class="bg-base-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-base-content/70">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Open your authenticator app (Google Authenticator, Authy, etc.) and enter the 6-digit code shown for Family Ledger.
            </p>
        </div>
    </div>

    <!-- Email Send Button -->
    <div id="email-section" class="{{ $method === 'email' ? '' : 'hidden' }}">
        <button id="send-email-btn" class="btn btn-outline btn-block mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Send Email Code
        </button>
    </div>

    <!-- SMS Send Button -->
    <div id="sms-section" class="{{ $method === 'sms' ? '' : 'hidden' }}">
        <button id="send-sms-btn" class="btn btn-outline btn-block mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Send SMS Code
        </button>
    </div>

    <form id="mfa-form" class="space-y-4">
        <input type="hidden" name="method" id="selected-method" value="{{ $method }}">

        <div class="form-control">
            <label class="label">
                <span class="label-text">Verification Code</span>
            </label>
            <input type="text" name="code" id="code-input" placeholder="000000"
                   class="input input-bordered w-full text-center text-2xl tracking-widest"
                   maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Verify
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="/login" class="text-sm text-base-content/60 hover:underline">
            Cancel and return to login
        </a>
    </div>

    <!-- Alerts -->
    <div id="success-alert" class="alert alert-success mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="success-message"></span>
    </div>

    <div id="error-alert" class="alert alert-error mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="error-message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const maskedEmail = '{{ $email ?? "" }}';
    const phoneLastFour = '{{ $phone_last_four ?? "" }}';
    let currentMethod = '{{ $method }}';

    // Focus on code input
    document.getElementById('code-input').focus();

    // Method Selection
    window.selectMethod = function(method) {
        currentMethod = method;
        document.getElementById('selected-method').value = method;

        // Update button styles
        ['authenticator', 'email', 'sms'].forEach(m => {
            const btn = document.getElementById('btn-' + m);
            if (btn) {
                btn.classList.toggle('btn-primary', method === m);
                btn.classList.toggle('btn-outline', method !== m);
            }
        });

        // Show/hide sections
        document.getElementById('authenticator-section').classList.toggle('hidden', method !== 'authenticator');
        document.getElementById('email-section').classList.toggle('hidden', method !== 'email');
        document.getElementById('sms-section').classList.toggle('hidden', method !== 'sms');

        // Update description
        const descEl = document.getElementById('method-description');
        if (method === 'sms') {
            descEl.textContent = 'Enter the code sent to your phone ending in ' + phoneLastFour;
        } else if (method === 'email') {
            descEl.textContent = 'Enter the code sent to ' + maskedEmail;
        } else {
            descEl.textContent = 'Enter the code from your authenticator app';
        }

        // Clear and focus input
        document.getElementById('code-input').value = '';
        document.getElementById('code-input').focus();

        // Hide alerts
        hideAlerts();
    };

    // Send Email Code
    const sendEmailBtn = document.getElementById('send-email-btn');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', async function() {
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Sending...';

            try {
                const response = await fetch('/auth/mfa/email/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showSuccess('Code sent to your email');
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>Resend Code';
                    document.getElementById('code-input').focus();
                } else {
                    showError(data.error || 'Failed to send code');
                    this.innerHTML = originalHtml;
                }
            } catch (err) {
                showError('Network error. Please try again.');
                this.innerHTML = originalHtml;
            }

            setTimeout(() => { this.disabled = false; }, 30000);
        });
    }

    // Send SMS Code
    const sendSmsBtn = document.getElementById('send-sms-btn');
    if (sendSmsBtn) {
        sendSmsBtn.addEventListener('click', async function() {
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Sending...';

            try {
                const response = await fetch('/auth/mfa/sms/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showSuccess('Code sent to your phone');
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>Resend Code';
                    document.getElementById('code-input').focus();
                } else {
                    showError(data.error || 'Failed to send code');
                    this.innerHTML = originalHtml;
                }
            } catch (err) {
                showError('Network error. Please try again.');
                this.innerHTML = originalHtml;
            }

            setTimeout(() => { this.disabled = false; }, 30000);
        });
    }

    // Verify MFA
    document.getElementById('mfa-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const code = document.getElementById('code-input').value;
        const method = document.getElementById('selected-method').value;
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Verifying...';

        try {
            const response = await fetch('/auth/mfa/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ code, method })
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('Verified! Redirecting...');
                submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Success!';
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                showError(data.error || 'Invalid code');
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
                document.getElementById('code-input').select();
            }
        } catch (err) {
            showError('Network error. Please try again.');
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
        }
    });

    // Only allow numeric input
    document.getElementById('code-input').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    function showSuccess(message) {
        hideAlerts();
        const alert = document.getElementById('success-alert');
        document.getElementById('success-message').textContent = message;
        alert.classList.remove('hidden');
    }

    function showError(message) {
        hideAlerts();
        const alert = document.getElementById('error-alert');
        document.getElementById('error-message').textContent = message;
        alert.classList.remove('hidden');
    }

    function hideAlerts() {
        document.getElementById('success-alert').classList.add('hidden');
        document.getElementById('error-alert').classList.add('hidden');
    }
});
</script>
@endpush
