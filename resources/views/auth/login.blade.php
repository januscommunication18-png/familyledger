@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div id="login-app">
    <h2 class="text-2xl font-semibold text-center mb-6">Welcome Back</h2>

    <!-- Social Login Buttons -->
    <div class="space-y-3 mb-6">
        <a href="/auth/google" class="btn btn-outline btn-block gap-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <a href="/auth/apple" class="btn btn-outline btn-block gap-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
            </svg>
            Continue with Apple
        </a>

        <a href="/auth/facebook" class="btn btn-outline btn-block gap-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Continue with Facebook
        </a>
    </div>

    <div class="divider">or</div>

    <!-- Tab Navigation -->
    <div class="tabs tabs-boxed mb-6">
        <button class="tab tab-active" data-tab="otp">Email Code</button>
        <button class="tab" data-tab="password">Password</button>
    </div>

    <!-- OTP Login Form -->
    <div id="otp-form">
        <form id="otp-request-form" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <input type="email" name="email" placeholder="you@example.com" class="input input-bordered w-full" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Send Login Code
            </button>
        </form>

        <form id="otp-verify-form" class="space-y-4 hidden">
            <p class="text-sm text-center text-base-content/70 mb-4">
                We sent a code to <span id="otp-email" class="font-medium"></span>
            </p>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Verification Code</span>
                </label>
                <input type="text" name="code" placeholder="000000" class="input input-bordered w-full text-center text-2xl tracking-widest" maxlength="6" pattern="[0-9]{6}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Verify & Sign In
            </button>

            <button type="button" id="resend-otp" class="btn btn-ghost btn-sm btn-block">
                Resend Code
            </button>
        </form>
    </div>

    <!-- Password Login Form -->
    <div id="password-form" class="hidden">
        <form class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <input type="email" name="email" placeholder="you@example.com" class="input input-bordered w-full" required>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" name="password" placeholder="Enter your password" class="input input-bordered w-full" required>
            </div>

            <div class="flex items-center justify-between">
                <label class="label cursor-pointer gap-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm">
                    <span class="label-text">Remember me</span>
                </label>
                <a href="/forgot-password" class="text-sm text-primary hover:underline">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Sign In
            </button>
        </form>
    </div>

    <!-- Honeypot (hidden) -->
    <input type="text" name="website_url_hp" class="hidden" tabindex="-1" autocomplete="off">
    <input type="hidden" name="form_time_hp" value="{{ time() }}">

    <!-- Error Alert -->
    <div id="error-alert" class="alert alert-error mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="error-message"></span>
    </div>

    <p class="text-center text-sm mt-6">
        Don't have an account?
        <a href="/register" class="text-primary hover:underline">Create one</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            const tabName = this.dataset.tab;
            document.getElementById('otp-form').classList.toggle('hidden', tabName !== 'otp');
            document.getElementById('password-form').classList.toggle('hidden', tabName !== 'password');
        });
    });

    // OTP Request
    document.getElementById('otp-request-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = this.querySelector('input[name="email"]').value;

        try {
            const response = await fetch('/auth/otp/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (response.ok) {
                document.getElementById('otp-request-form').classList.add('hidden');
                document.getElementById('otp-verify-form').classList.remove('hidden');
                document.getElementById('otp-email').textContent = email;
                document.getElementById('otp-verify-form').querySelector('input[name="code"]').focus();
            } else {
                showError(data.error || 'Failed to send code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    // OTP Verify
    document.getElementById('otp-verify-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('otp-email').textContent;
        const code = this.querySelector('input[name="code"]').value;

        try {
            const response = await fetch('/auth/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email, code })
            });

            const data = await response.json();

            if (response.ok) {
                window.location.href = data.redirect || '/dashboard';
            } else {
                showError(data.error || 'Invalid code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    // Password Login
    document.getElementById('password-form').querySelector('form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    email: formData.get('email'),
                    password: formData.get('password'),
                    remember: formData.has('remember')
                })
            });

            const data = await response.json();

            if (response.ok) {
                window.location.href = data.redirect || '/dashboard';
            } else {
                showError(data.error || 'Invalid credentials');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }
    });

    function showError(message) {
        const alert = document.getElementById('error-alert');
        document.getElementById('error-message').textContent = message;
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }
});
</script>
@endpush
