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
        <p class="text-base-content/60 mt-2">
            @if($method === 'sms')
                Enter the code sent to your phone ending in {{ $phone_last_four }}
            @else
                Enter your verification code
            @endif
        </p>
    </div>

    @if($method === 'sms')
    <button id="send-sms-btn" class="btn btn-outline btn-block mb-4">
        Send SMS Code
    </button>
    @endif

    <form id="mfa-form" class="space-y-4">
        <div class="form-control">
            <label class="label">
                <span class="label-text">Verification Code</span>
            </label>
            <input type="text" name="code" placeholder="000000" class="input input-bordered w-full text-center text-2xl tracking-widest" maxlength="6" pattern="[0-9]{6}" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
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
        <span id="success-message"></span>
    </div>

    <div id="error-alert" class="alert alert-error mt-4 hidden">
        <span id="error-message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Send SMS Code
    const sendSmsBtn = document.getElementById('send-sms-btn');
    if (sendSmsBtn) {
        sendSmsBtn.addEventListener('click', async function() {
            this.disabled = true;
            this.textContent = 'Sending...';

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
                    this.textContent = 'Resend Code';
                    document.querySelector('input[name="code"]').focus();
                } else {
                    showError(data.error || 'Failed to send code');
                    this.textContent = 'Send SMS Code';
                }
            } catch (err) {
                showError('Network error. Please try again.');
                this.textContent = 'Send SMS Code';
            }

            setTimeout(() => { this.disabled = false; }, 30000);
        });
    }

    // Verify MFA
    document.getElementById('mfa-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const code = this.querySelector('input[name="code"]').value;

        try {
            const response = await fetch('/auth/mfa/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ code })
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('Verified!');
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                showError(data.error || 'Invalid code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    function showSuccess(message) {
        document.getElementById('error-alert').classList.add('hidden');
        const alert = document.getElementById('success-alert');
        document.getElementById('success-message').textContent = message;
        alert.classList.remove('hidden');
    }

    function showError(message) {
        document.getElementById('success-alert').classList.add('hidden');
        const alert = document.getElementById('error-alert');
        document.getElementById('error-message').textContent = message;
        alert.classList.remove('hidden');
    }
});
</script>
@endpush
