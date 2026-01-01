{{-- Image Verification Modal --}}
<div id="imageVerifyModal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeVerifyModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-3xl shadow-2xl max-w-md w-full relative overflow-hidden">
            {{-- Decorative gradient top --}}
            <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-br from-primary/20 via-secondary/10 to-accent/10"></div>

            <button type="button" onclick="closeVerifyModal()" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3 z-10 bg-base-100/80 hover:bg-base-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>

            <div class="relative p-8">
                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="relative inline-block">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center mx-auto mb-5 shadow-lg shadow-primary/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><rect width="8" height="5" x="8" y="11" rx="1"/><path d="M12 8v3"/></svg>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-warning flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-warning-content"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>
                    <h3 class="font-bold text-2xl text-base-content">Verify Your Identity</h3>
                    <p class="text-base-content/60 mt-2 text-sm max-w-xs mx-auto">To view sensitive documents, please verify your identity with a one-time code.</p>
                </div>

                {{-- Step 1: Choose verification method --}}
                <div id="verifyStep1" class="space-y-5">
                    <p class="text-sm text-base-content/70 text-center font-medium">Choose verification method:</p>

                    <div class="space-y-3">
                        {{-- Email Option --}}
                        <button type="button" onclick="sendVerificationCode('email')" class="group w-full p-4 rounded-2xl border-2 border-base-300 hover:border-primary hover:bg-primary/5 transition-all duration-200 flex items-center gap-4" id="emailVerifyBtn">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <div class="text-left flex-1">
                                <div class="font-semibold text-base-content">Email</div>
                                <div class="text-xs text-base-content/60">Send code to your email address</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-base-content/40 group-hover:text-primary group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                        </button>

                        {{-- SMS Option (Coming Soon) --}}
                        <div class="relative w-full p-4 rounded-2xl border-2 border-base-200 bg-base-200/30 flex items-center gap-4 cursor-not-allowed opacity-70">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                            </div>
                            <div class="text-left flex-1">
                                <div class="font-semibold text-base-content flex items-center gap-2">
                                    Phone (SMS)
                                    <span class="badge badge-sm badge-warning gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Coming Soon
                                    </span>
                                </div>
                                <div class="text-xs text-base-content/60">Send code to your phone number</div>
                            </div>
                        </div>
                    </div>

                    <div id="verifyMethodError" class="alert alert-error hidden rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span id="verifyMethodErrorText"></span>
                    </div>
                </div>

                {{-- Step 2: Enter verification code --}}
                <div id="verifyStep2" class="space-y-5 hidden">
                    <div id="verifyCodeSentMessage" class="alert alert-success rounded-xl shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span id="verifyCodeSentText">Verification code sent!</span>
                    </div>

                    <div class="form-control">
                        <label class="label justify-center">
                            <span class="label-text font-medium">Enter 6-digit verification code</span>
                        </label>
                        <div class="flex gap-2 justify-center mt-2">
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="0" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="1" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="2" inputmode="numeric" pattern="[0-9]*">
                            <span class="flex items-center text-base-content/30 font-bold">-</span>
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="3" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="4" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" maxlength="1" class="verify-code-input input input-bordered w-12 h-14 text-center text-2xl font-bold rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20" data-index="5" inputmode="numeric" pattern="[0-9]*">
                        </div>
                    </div>

                    <div id="verifyCodeError" class="alert alert-error hidden rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span id="verifyCodeErrorText"></span>
                    </div>

                    <button type="button" id="verifyCodeBtn" onclick="verifyCode()" class="btn btn-primary w-full rounded-xl h-12 text-base font-semibold shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 transition-all">
                        <span class="loading loading-spinner loading-sm hidden" id="verifyCodeSpinner"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        Verify Code
                    </button>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" onclick="goBackToStep1()" class="btn btn-ghost btn-sm gap-1 text-base-content/60 hover:text-base-content">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Back
                        </button>

                        <div class="text-sm text-base-content/60">
                            <button type="button" onclick="resendCode()" class="link link-primary font-medium" id="resendBtn">Resend code</button>
                            <span id="resendTimer" class="hidden font-medium"></span>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Success --}}
                <div id="verifyStep3" class="space-y-6 hidden text-center py-4">
                    <div class="relative inline-block">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-success to-emerald-500 flex items-center justify-center mx-auto shadow-lg shadow-success/30 animate-bounce-slow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-base-100 border-4 border-success flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-success"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-2xl text-success">Verified!</h4>
                        <p class="text-base-content/60 mt-2 text-sm">You can now view and download all sensitive documents for this session.</p>
                    </div>
                    <button type="button" onclick="closeVerifyModal()" class="btn btn-success w-full rounded-xl h-12 text-base font-semibold shadow-lg shadow-success/30">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}
</style>

<script>
// Image Verification System
window.ImageVerification = {
    verified: {{ session('image_verified') ? 'true' : 'false' }},
    currentMethod: null,
    pendingCallback: null,
    resendCooldown: 0,

    // Check if verified
    isVerified() {
        return this.verified;
    },

    // Request verification (call this before showing sensitive images)
    async requireVerification(callback) {
        if (this.verified) {
            if (callback) callback();
            return true;
        }

        this.pendingCallback = callback;
        openVerifyModal();
        return false;
    },

    // Mark as verified
    setVerified() {
        this.verified = true;
        // Remove blur from all protected images
        document.querySelectorAll('.protected-image').forEach(img => {
            img.classList.remove('blur-lg');
            img.classList.add('blur-none');
        });
        // Mark all containers as verified
        document.querySelectorAll('.protected-image-container').forEach(el => {
            el.classList.add('verified');
        });
    }
};

// Open/close modal functions
function openVerifyModal() {
    const modal = document.getElementById('imageVerifyModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeVerifyModal() {
    const modal = document.getElementById('imageVerifyModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    resetVerifyModal();
}

// Send verification code
async function sendVerificationCode(method) {
    const errorDiv = document.getElementById('verifyMethodError');
    const errorText = document.getElementById('verifyMethodErrorText');
    errorDiv.classList.add('hidden');

    const btn = document.getElementById('emailVerifyBtn');
    btn.classList.add('opacity-50', 'pointer-events-none');

    try {
        const response = await fetch('/image-verify/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ method }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorText.textContent = data.error || 'Failed to send code';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Move to step 2
        window.ImageVerification.currentMethod = method;
        document.getElementById('verifyCodeSentText').textContent = data.message;
        document.getElementById('verifyStep1').classList.add('hidden');
        document.getElementById('verifyStep2').classList.remove('hidden');

        // Focus first input
        setTimeout(() => {
            document.querySelector('.verify-code-input[data-index="0"]').focus();
        }, 100);

        // Start resend cooldown
        startResendCooldown();
    } catch (error) {
        errorText.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.classList.remove('opacity-50', 'pointer-events-none');
    }
}

// Verify code
async function verifyCode() {
    const inputs = document.querySelectorAll('.verify-code-input');
    let code = '';
    inputs.forEach(input => code += input.value);

    if (code.length !== 6) {
        showCodeError('Please enter all 6 digits');
        return;
    }

    const btn = document.getElementById('verifyCodeBtn');
    const spinner = document.getElementById('verifyCodeSpinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const response = await fetch('/image-verify/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                code,
                method: window.ImageVerification.currentMethod,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            showCodeError(data.error || 'Invalid code');
            // Clear inputs on error
            inputs.forEach(input => input.value = '');
            inputs[0].focus();
            return;
        }

        // Success!
        window.ImageVerification.setVerified();
        document.getElementById('verifyStep2').classList.add('hidden');
        document.getElementById('verifyStep3').classList.remove('hidden');

        // Execute pending callback after a short delay
        if (window.ImageVerification.pendingCallback) {
            setTimeout(() => {
                window.ImageVerification.pendingCallback();
                window.ImageVerification.pendingCallback = null;
            }, 500);
        }
    } catch (error) {
        showCodeError('An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

function showCodeError(message) {
    const errorDiv = document.getElementById('verifyCodeError');
    const errorText = document.getElementById('verifyCodeErrorText');
    errorText.textContent = message;
    errorDiv.classList.remove('hidden');
}

function goBackToStep1() {
    document.getElementById('verifyStep2').classList.add('hidden');
    document.getElementById('verifyStep1').classList.remove('hidden');
    document.getElementById('verifyCodeError').classList.add('hidden');
    // Clear inputs
    document.querySelectorAll('.verify-code-input').forEach(input => input.value = '');
}

function resetVerifyModal() {
    document.getElementById('verifyStep1').classList.remove('hidden');
    document.getElementById('verifyStep2').classList.add('hidden');
    document.getElementById('verifyStep3').classList.add('hidden');
    document.getElementById('verifyMethodError').classList.add('hidden');
    document.getElementById('verifyCodeError').classList.add('hidden');
    document.querySelectorAll('.verify-code-input').forEach(input => input.value = '');
}

function startResendCooldown() {
    window.ImageVerification.resendCooldown = 60;
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    resendBtn.classList.add('hidden');
    resendTimer.classList.remove('hidden');

    const interval = setInterval(() => {
        window.ImageVerification.resendCooldown--;
        resendTimer.textContent = `Resend in ${window.ImageVerification.resendCooldown}s`;

        if (window.ImageVerification.resendCooldown <= 0) {
            clearInterval(interval);
            resendBtn.classList.remove('hidden');
            resendTimer.classList.add('hidden');
        }
    }, 1000);
}

function resendCode() {
    if (window.ImageVerification.resendCooldown > 0) return;
    sendVerificationCode(window.ImageVerification.currentMethod);
}

// Code input handling
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.verify-code-input');

    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length === 1 && index < 5) {
                inputs[index + 1].focus();
            }

            // Auto-submit when all filled
            let code = '';
            inputs.forEach(i => code += i.value);
            if (code.length === 6) {
                verifyCode();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            pastedData.split('').forEach((char, i) => {
                if (inputs[i]) inputs[i].value = char;
            });
            if (pastedData.length === 6) {
                verifyCode();
            }
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('imageVerifyModal');
            if (!modal.classList.contains('hidden')) {
                closeVerifyModal();
            }
        }
    });
});
</script>
