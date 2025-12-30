@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div id="verify-app">
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2 class="text-2xl font-semibold">Verify Your Email</h2>
        <p class="text-base-content/60 mt-2">
            We sent a verification code to<br>
            <span class="font-medium text-base-content">{{ auth()->user()->email }}</span>
        </p>
    </div>

    <form id="verify-form" class="space-y-4">
        <div class="form-control">
            <label class="label">
                <span class="label-text">Verification Code</span>
            </label>
            <input type="text" name="code" placeholder="000000" class="input input-bordered w-full text-center text-2xl tracking-widest" maxlength="6" pattern="[0-9]{6}" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Verify Email
        </button>
    </form>

    <div class="text-center mt-6">
        <p class="text-sm text-base-content/60 mb-2">Didn't receive the code?</p>
        <button id="resend-btn" class="btn btn-ghost btn-sm">
            Resend Code
        </button>
    </div>

    <!-- Alerts -->
    <div id="success-alert" class="alert alert-success mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="success-message"></span>
    </div>

    <div id="error-alert" class="alert alert-error mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="error-message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('verify-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const code = this.querySelector('input[name="code"]').value;

        try {
            const response = await fetch('/verify-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ code })
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('Email verified successfully!');
                setTimeout(() => {
                    window.location.href = data.redirect || '/onboarding';
                }, 1000);
            } else {
                showError(data.error || 'Invalid code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    document.getElementById('resend-btn').addEventListener('click', async function() {
        this.disabled = true;

        try {
            const response = await fetch('/verify-email/resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('New code sent!');
            } else {
                showError(data.error || 'Failed to resend code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }

        setTimeout(() => { this.disabled = false; }, 60000);
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
