@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div id="login-app">
    <h2 class="text-2xl font-semibold text-center mb-6">Welcome Back</h2>

    <!-- Step 1: Social Login & Email Button -->
    <div id="step-social" class="space-y-3">
        <!-- Social Login Buttons -->
        <button type="button" disabled class="btn btn-outline btn-block gap-2 btn-disabled opacity-60 cursor-not-allowed">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
            <span class="badge badge-sm badge-neutral">Coming Soon</span>
        </button>

        <button type="button" disabled class="btn btn-outline btn-block gap-2 btn-disabled opacity-60 cursor-not-allowed">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
            </svg>
            Continue with Apple
            <span class="badge badge-sm badge-neutral">Coming Soon</span>
        </button>

        <button type="button" disabled class="btn btn-outline btn-block gap-2 btn-disabled opacity-60 cursor-not-allowed">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Continue with Facebook
            <span class="badge badge-sm badge-neutral">Coming Soon</span>
        </button>

        <div class="divider text-base-content/50">or</div>

        <!-- Continue with Email Button -->
        <button type="button" id="btn-continue-email" class="btn btn-outline btn-block gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Continue with Email
        </button>
    </div>

    <!-- Step 2: Email Input -->
    <div id="step-email" class="hidden">
        <div class="flex items-center gap-2 mb-6">
            <button type="button" id="back-to-social" class="btn btn-ghost btn-sm btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-sm text-base-content/70">Sign in with email</span>
        </div>

        <form id="email-form" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <input type="email" name="email" id="email-input" placeholder="you@example.com" class="input input-bordered w-full" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Continue
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Step 3: Login Method Selection (Password or OTP) -->
    <div id="step-login-method" class="hidden">
        <div class="flex items-center gap-2 mb-4">
            <button type="button" id="back-to-email" class="btn btn-ghost btn-sm btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-sm text-base-content/70">Signing in as <strong id="display-email" class="text-base-content"></strong></span>
        </div>

        <!-- Login Method Selection -->
        <div class="space-y-3 mb-6">
            <p class="text-sm text-base-content/70 mb-3">Choose how you'd like to sign in:</p>

            <!-- Password Option -->
            <label class="login-method-option flex items-start gap-3 p-4 border border-base-300 rounded-xl cursor-pointer hover:border-primary hover:bg-base-50 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="radio" name="login_method" value="password" class="radio radio-primary mt-0.5" checked>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="font-medium text-base-content">Use my password</span>
                    </div>
                    <p class="text-sm text-base-content/60 mt-1">Sign in with your account password. Best for quick access.</p>
                </div>
            </label>

            <!-- Email Code Option -->
            <label class="login-method-option flex items-start gap-3 p-4 border border-base-300 rounded-xl cursor-pointer hover:border-primary hover:bg-base-50 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="radio" name="login_method" value="otp" class="radio radio-primary mt-0.5">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium text-base-content">Email me a code</span>
                    </div>
                    <p class="text-sm text-base-content/60 mt-1">We'll send a one-time 6-digit code to your email. No password needed.</p>
                </div>
            </label>
        </div>

        <!-- Password Login Form -->
        <div id="password-form">
            <form id="password-login-form" class="space-y-4">
                <input type="hidden" name="email" id="password-email">

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password-input" placeholder="Enter your password" class="input input-bordered w-full pr-10" required>
                        <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary">
                        <span class="label-text">Remember me</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-primary hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Sign In
                </button>
            </form>
        </div>

        <!-- OTP Login Form -->
        <div id="otp-form" class="hidden">
            <form id="otp-request-form" class="space-y-4">
                <input type="hidden" name="email" id="otp-email-hidden">

                <button type="submit" class="btn btn-primary btn-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Send Code
                </button>
            </form>

            <form id="otp-verify-form" class="space-y-4 hidden">
                <div class="bg-base-200 rounded-lg p-4 text-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-base-content/70">
                        We sent a 6-digit code to<br>
                        <span id="otp-email-confirm" class="font-medium text-base-content"></span>
                    </p>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Verification Code</span>
                    </label>
                    <input type="text" name="code" placeholder="000000" class="input input-bordered w-full text-center text-2xl tracking-widest" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verify & Sign In
                </button>

                <button type="button" id="resend-otp" class="btn btn-ghost btn-sm btn-block">
                    Didn't receive it? Resend Code
                </button>
            </form>
        </div>
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

    <!-- Success Alert -->
    <div id="success-alert" class="alert alert-success mt-4 hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="success-message"></span>
    </div>

    <p class="text-center text-sm mt-6">
        Don't have an account?
        <a href="/register" class="text-primary hover:underline font-medium">Create one</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let userEmail = '';

    // Step navigation
    const stepSocial = document.getElementById('step-social');
    const stepEmail = document.getElementById('step-email');
    const stepLoginMethod = document.getElementById('step-login-method');

    function showStep(step) {
        stepSocial.classList.add('hidden');
        stepEmail.classList.add('hidden');
        stepLoginMethod.classList.add('hidden');

        if (step === 'social') {
            stepSocial.classList.remove('hidden');
        } else if (step === 'email') {
            stepEmail.classList.remove('hidden');
            document.getElementById('email-input').focus();
        } else if (step === 'login-method') {
            stepLoginMethod.classList.remove('hidden');
            document.getElementById('password-input').focus();
        }
    }

    // Continue with Email button
    document.getElementById('btn-continue-email').addEventListener('click', function() {
        showStep('email');
    });

    // Back to social
    document.getElementById('back-to-social').addEventListener('click', function() {
        showStep('social');
    });

    // Back to email
    document.getElementById('back-to-email').addEventListener('click', function() {
        showStep('email');
    });

    // Email form submission
    document.getElementById('email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        userEmail = document.getElementById('email-input').value;

        // Update email displays
        document.getElementById('display-email').textContent = userEmail;
        document.getElementById('password-email').value = userEmail;
        document.getElementById('otp-email-hidden').value = userEmail;
        document.getElementById('otp-email-confirm').textContent = userEmail;

        // Show login method step
        showStep('login-method');
    });

    // Login method radio button switching
    document.querySelectorAll('input[name="login_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const method = this.value;
            document.getElementById('otp-form').classList.toggle('hidden', method !== 'otp');
            document.getElementById('password-form').classList.toggle('hidden', method !== 'password');

            if (method === 'password') {
                document.getElementById('password-input').focus();
            }
        });
    });

    // Password visibility toggle
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password-input');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.querySelector('.eye-open').classList.toggle('hidden');
            this.querySelector('.eye-closed').classList.toggle('hidden');
        });
    }

    // Password Login
    document.getElementById('password-login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Signing in...';

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    email: userEmail,
                    password: formData.get('password'),
                    remember: formData.has('remember')
                })
            });

            const data = await response.json();

            if (response.ok) {
                showSuccess('Success! Redirecting...');
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                showError(data.error || 'Invalid credentials');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        } catch (err) {
            showError('Network error. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });

    // OTP Request
    document.getElementById('otp-request-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Sending...';

        try {
            const response = await fetch('/auth/otp/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: userEmail })
            });

            const data = await response.json();

            if (response.ok) {
                document.getElementById('otp-request-form').classList.add('hidden');
                document.getElementById('otp-verify-form').classList.remove('hidden');
                document.getElementById('otp-verify-form').querySelector('input[name="code"]').focus();
                showSuccess('Code sent! Check your email.');
            } else {
                showError(data.error || 'Failed to send code');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    });

    // OTP Verify
    document.getElementById('otp-verify-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const code = this.querySelector('input[name="code"]').value;
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span>Verifying...';

        try {
            const response = await fetch('/auth/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: userEmail, code })
            });

            const data = await response.json();

            if (response.ok) {
                if (data.mfa_required) {
                    showSuccess('Code verified! Redirecting to 2FA...');
                } else {
                    showSuccess('Success! Redirecting...');
                }
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                showError(data.error || 'Invalid code');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        } catch (err) {
            showError('Network error. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });

    // Resend OTP
    document.getElementById('resend-otp').addEventListener('click', async function() {
        this.disabled = true;
        const originalText = this.textContent;
        this.textContent = 'Sending...';

        try {
            const response = await fetch('/auth/otp/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: userEmail })
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

        setTimeout(() => {
            this.disabled = false;
            this.textContent = originalText;
        }, 30000);
    });

    // Only allow numeric input for OTP
    const otpInput = document.querySelector('#otp-verify-form input[name="code"]');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    function showError(message) {
        hideAlerts();
        const alert = document.getElementById('error-alert');
        document.getElementById('error-message').textContent = message;
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }

    function showSuccess(message) {
        hideAlerts();
        const alert = document.getElementById('success-alert');
        document.getElementById('success-message').textContent = message;
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }

    function hideAlerts() {
        document.getElementById('error-alert').classList.add('hidden');
        document.getElementById('success-alert').classList.add('hidden');
    }
});
</script>
@endpush
