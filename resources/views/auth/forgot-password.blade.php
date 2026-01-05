@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div id="forgot-password-app">
    <h2 class="text-2xl font-semibold text-center mb-2">Reset Password</h2>
    <p class="text-base-content/60 text-center text-sm mb-6">Enter your email to receive a reset code</p>

    <!-- Request Code Form -->
    <form id="request-code-form" class="space-y-4">
        <div class="form-control">
            <label class="label">
                <span class="label-text">Email Address</span>
            </label>
            <input type="email" name="email" placeholder="you@example.com" class="input input-bordered w-full" value="{{ $prefillEmail ?? '' }}" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Send Reset Code
        </button>
    </form>

    <!-- Verify Code & Reset Form -->
    <form id="reset-form" class="space-y-4 hidden">
        <p class="text-sm text-center text-base-content/70 mb-4">
            We sent a code to <span id="reset-email" class="font-medium"></span>
        </p>

        <input type="hidden" name="email" id="hidden-email">

        <div class="form-control">
            <label class="label">
                <span class="label-text">Verification Code</span>
            </label>
            <input type="text" name="code" placeholder="000000" class="input input-bordered w-full text-center text-2xl tracking-widest" maxlength="6" pattern="[0-9]{6}" required>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">New Password</span>
            </label>
            <input type="password" name="password" placeholder="Enter new password" class="input input-bordered w-full" minlength="8" required>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Confirm New Password</span>
            </label>
            <input type="password" name="password_confirmation" placeholder="Confirm new password" class="input input-bordered w-full" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Reset Password
        </button>

        <button type="button" id="resend-code" class="btn btn-ghost btn-sm btn-block">
            Resend Code
        </button>
    </form>

    <!-- Success Message -->
    <div id="success-message" class="hidden text-center">
        <div class="text-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold mb-2">Password Reset Successful</h3>
        <p class="text-base-content/60 mb-4">You can now log in with your new password.</p>
        <a href="/login" class="btn btn-primary btn-block">Go to Login</a>
    </div>

    <!-- Error Alert -->
    <div id="error-alert" class="alert alert-error mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="error-message"></span>
    </div>

    <!-- Success Alert -->
    <div id="success-alert" class="alert alert-success mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="success-text"></span>
    </div>

    <p class="text-center text-sm mt-6">
        Remember your password?
        <a href="/login" class="text-primary hover:underline">Sign in</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let userEmail = '';

    // Request Code Form
    document.getElementById('request-code-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = this.querySelector('input[name="email"]').value;

        try {
            const response = await fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (response.ok) {
                userEmail = email;
                document.getElementById('request-code-form').classList.add('hidden');
                document.getElementById('reset-form').classList.remove('hidden');
                document.getElementById('reset-email').textContent = email;
                document.getElementById('hidden-email').value = email;
                document.getElementById('reset-form').querySelector('input[name="code"]').focus();
                showSuccess('Reset code sent to your email.');
            } else {
                showError(data.error || 'Failed to send reset code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    // Reset Password Form
    document.getElementById('reset-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('hidden-email').value;
        const code = this.querySelector('input[name="code"]').value;
        const password = this.querySelector('input[name="password"]').value;
        const password_confirmation = this.querySelector('input[name="password_confirmation"]').value;

        if (password !== password_confirmation) {
            showError('Passwords do not match.');
            return;
        }

        if (password.length < 8) {
            showError('Password must be at least 8 characters.');
            return;
        }

        try {
            const response = await fetch('/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email, code, password, password_confirmation })
            });

            const data = await response.json();

            if (response.ok) {
                document.getElementById('reset-form').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');
            } else {
                showError(data.error || 'Failed to reset password');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    // Resend Code
    document.getElementById('resend-code').addEventListener('click', async function() {
        const email = document.getElementById('hidden-email').value;

        try {
            const response = await fetch('/forgot-password/resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('A new code has been sent to your email.');
            } else {
                showError(data.error || 'Failed to resend code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    function showError(message) {
        const alert = document.getElementById('error-alert');
        const successAlert = document.getElementById('success-alert');
        successAlert.classList.add('hidden');
        document.getElementById('error-message').textContent = message;
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }

    function showSuccess(message) {
        const alert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        errorAlert.classList.add('hidden');
        document.getElementById('success-text').textContent = message;
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }
});
</script>
@endpush
