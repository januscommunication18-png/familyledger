@extends('layouts.dashboard')

@section('title', 'Settings')
@section('page-name', 'Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Settings</li>
@endsection

@section('page-title', 'Settings')
@section('page-description', 'Manage your account and application preferences.')

@section('content')
<div class="space-y-6">
    <!-- Settings Navigation Tabs -->
    <div class="bg-base-100 rounded-xl shadow-sm">
        <div class="border-b border-base-200">
            <ul class="flex flex-wrap gap-1 px-4 -mb-px overflow-x-auto">
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'profile']) }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 {{ $tab === 'profile' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'security']) }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 {{ $tab === 'security' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Security
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'notifications']) }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 {{ $tab === 'notifications' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        Notifications
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'appearance']) }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 {{ $tab === 'appearance' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                        Appearance
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'privacy']) }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 {{ $tab === 'privacy' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Privacy
                    </a>
                </li>
                <li>
                    <a href="{{ route('subscription.index') }}" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        Billing
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div>
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Profile Tab --}}
        @if($tab === 'profile')
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-6">Profile Settings</h2>

                <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="flex items-center gap-6">
                        <div class="avatar {{ $user->avatar ? '' : 'placeholder' }}">
                            @if($user->avatar)
                                <div class="w-20 rounded-full">
                                    <img src="{{ Storage::disk('do_spaces')->url($user->avatar) }}" alt="Avatar" />
                                </div>
                            @else
                                <div class="w-20 rounded-full bg-primary text-primary-content">
                                    <span class="text-2xl">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="btn btn-outline btn-sm cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                Upload Photo
                                <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden" onchange="previewAvatar(this)" />
                            </label>
                            <p class="text-sm text-base-content/60 mt-1">JPG, PNG, or GIF. Max 2MB.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Full Name</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="input input-bordered" required />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" value="{{ $user->email ?? '' }}" class="input input-bordered bg-base-200" disabled />
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Contact support to change email</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Phone Number</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+1 (555) 000-0000" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Account Created</span>
                            </label>
                            <input type="text" value="{{ $user->created_at?->format('M d, Y') }}" class="input input-bordered bg-base-200" disabled />
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        @php
            $onboardingSkipped = $tenant->onboarding_skipped ?? false;
        @endphp

        @if($isOwner && $onboardingSkipped)
        <div class="card bg-base-100 shadow-sm mt-6">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Complete Setup
                </h2>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="font-medium text-amber-800">Setup incomplete</p>
                            <p class="text-sm text-amber-700 mt-1">You skipped the initial setup process. Complete it to get the most out of FamilyLedger.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('onboarding.restart') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Complete Setup Now
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endif

        {{-- Security Tab --}}
        @if($tab === 'security')
        <div class="space-y-6">
            {{-- Change Password --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                        Change Password
                    </h2>

                    <form action="{{ route('settings.password.update') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Current Password</span>
                            </label>
                            <input type="password" name="current_password" class="input input-bordered" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">New Password</span>
                                </label>
                                <input type="password" name="password" class="input input-bordered" required />
                                <label class="label">
                                    <span class="label-text-alt text-base-content/60">Min 8 chars, mixed case, numbers</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Confirm New Password</span>
                                </label>
                                <input type="password" name="password_confirmation" class="input input-bordered" required />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Two-Factor Authentication --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Two-Factor Authentication
                    </h2>

                    @if($user->mfa_enabled)
                        <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-lg border border-emerald-200 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <div>
                                <p class="font-medium text-emerald-800">Two-factor authentication is enabled</p>
                                <p class="text-sm text-emerald-700">Method: {{ $user->mfa_method === 'sms' ? 'SMS' : 'Authenticator App' }}</p>
                            </div>
                        </div>

                        <form action="/settings/mfa/disable" method="POST" onsubmit="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-error btn-sm">Disable 2FA</button>
                        </form>
                    @else
                        <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <div>
                                <p class="font-medium text-amber-800">Two-factor authentication is not enabled</p>
                                <p class="text-sm text-amber-700">Add an extra layer of security to your account</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm text-base-content/70 font-medium">Choose a 2FA method:</p>
                            <div class="flex flex-wrap gap-2">
                                {{-- Authenticator App Option --}}
                                <button type="button" onclick="initAuthenticatorSetup()" class="btn btn-primary btn-sm gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    Authenticator App
                                </button>

                                {{-- SMS Option --}}
                                @if($user->phone)
                                <form action="/settings/mfa/sms/enable" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        SMS Code
                                    </button>
                                </form>
                                @else
                                <span class="text-sm text-base-content/50 self-center">Add a phone number in Profile to enable SMS 2FA</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Authenticator Setup Modal --}}
            <div id="authenticatorSetupModal" class="fixed inset-0 z-50 hidden">
                <div class="fixed inset-0 bg-black/50" onclick="closeAuthenticatorModal()"></div>
                <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                    <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 pointer-events-auto">
                        <h3 class="font-bold text-lg mb-4">Set Up Authenticator App</h3>

                        {{-- Step 1: QR Code --}}
                        <div id="authenticatorStep1">
                            <p class="text-sm text-base-content/70 mb-4">
                                Scan this QR code with your authenticator app (Google Authenticator, Authy, 1Password, etc.)
                            </p>

                            <div id="qrCodeContainer" class="flex justify-center items-center mb-4 min-h-[200px] bg-base-200 rounded-lg">
                                <span class="loading loading-spinner loading-lg"></span>
                            </div>

                            <div class="bg-base-200 rounded-lg p-3 mb-4">
                                <p class="text-xs text-base-content/60 mb-1">Can't scan? Enter this code manually:</p>
                                <code id="secretKey" class="text-sm font-mono select-all break-all">Loading...</code>
                            </div>

                            <button type="button" onclick="showAuthenticatorStep2()" class="btn btn-primary w-full">
                                I've Scanned the Code
                            </button>
                        </div>

                        {{-- Step 2: Verify Code --}}
                        <div id="authenticatorStep2" class="hidden">
                            <p class="text-sm text-base-content/70 mb-4">
                                Enter the 6-digit code from your authenticator app to verify the setup.
                            </p>

                            <div class="form-control mb-4">
                                <input type="text" id="authenticatorCode" maxlength="6" placeholder="000000" class="input input-bordered text-center text-2xl tracking-widest font-mono" autocomplete="off" />
                                <label class="label">
                                    <span id="authenticatorError" class="label-text-alt text-error hidden"></span>
                                </label>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" onclick="showAuthenticatorStep1()" class="btn btn-ghost flex-1">Back</button>
                                <button type="button" onclick="verifyAuthenticatorCode()" id="verifyAuthBtn" class="btn btn-primary flex-1">
                                    Verify & Enable
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t flex justify-end">
                            <button type="button" onclick="closeAuthenticatorModal()" class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Connected Accounts --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        Connected Accounts
                    </h2>

                    <div class="space-y-3">
                        @php
                            $providers = ['google' => 'Google', 'apple' => 'Apple', 'facebook' => 'Facebook'];
                        @endphp

                        @foreach($providers as $provider => $name)
                            @php
                                $connected = $socialAccounts->firstWhere('provider', $provider);
                            @endphp
                            <div class="flex items-center justify-between p-3 border border-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    @if($provider === 'google')
                                        <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    @elseif($provider === 'apple')
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $name }}</p>
                                        @if($connected)
                                            <p class="text-xs text-emerald-600">Connected</p>
                                        @else
                                            <p class="text-xs text-base-content/60">Not connected</p>
                                        @endif
                                    </div>
                                </div>
                                @if($connected)
                                    <form action="/settings/social/{{ $provider }}" method="POST" onsubmit="return confirm('Disconnect {{ $name }} account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-error">Disconnect</button>
                                    </form>
                                @else
                                    <a href="{{ url('/auth/' . $provider) }}" class="btn btn-outline btn-sm">Connect</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Active Sessions --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Active Sessions
                        </h2>
                        @if($sessions->count() > 1)
                        <form action="{{ route('settings.sessions.revoke-all') }}" method="POST" onsubmit="return confirm('Log out of all other devices?')">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm text-error">Log Out Other Devices</button>
                        </form>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse($sessions as $session)
                            <div class="flex items-center justify-between p-3 border border-base-200 rounded-lg {{ $session->is_current ? 'bg-primary/5 border-primary/30' : '' }}">
                                <div class="flex items-center gap-3">
                                    @if($session->device['icon'] === 'device-mobile')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    @elseif($session->device['icon'] === 'device-laptop')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @endif
                                    <div>
                                        <p class="font-medium">
                                            {{ $session->device['browser'] }} on {{ $session->device['platform'] }}
                                            @if($session->is_current)
                                                <span class="badge badge-primary badge-xs ml-2">Current</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-base-content/60">
                                            {{ $session->ip_address }} &bull; Last active {{ $session->last_activity_human }}
                                        </p>
                                    </div>
                                </div>
                                @if(!$session->is_current)
                                    <form action="{{ route('settings.sessions.revoke', $session->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-error">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-base-content/60 text-sm text-center py-4">No active sessions</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent Login Activity --}}
            @if($loginAttempts->count() > 0)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Recent Login Activity
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loginAttempts as $attempt)
                                    <tr>
                                        <td class="text-sm">{{ \Carbon\Carbon::parse($attempt->created_at)->format('M d, Y H:i') }}</td>
                                        <td class="text-sm capitalize">{{ str_replace('_', ' ', $attempt->auth_method ?? 'password') }}</td>
                                        <td class="text-sm font-mono text-xs">{{ $attempt->ip_address }}</td>
                                        <td>
                                            @if($attempt->successful)
                                                <span class="badge badge-success badge-xs">Success</span>
                                            @else
                                                <span class="badge badge-error badge-xs">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Notifications Tab --}}
        @if($tab === 'notifications')
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-6">Notification Preferences</h2>

                <form action="{{ route('settings.notifications.update') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Notification Channels --}}
                    <div>
                        <h3 class="text-sm font-semibold text-base-content/80 mb-3">Notification Channels</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <div>
                                        <p class="font-medium">Email Notifications</p>
                                        <p class="text-xs text-base-content/60">Receive notifications via email</p>
                                    </div>
                                </div>
                                <input type="checkbox" name="email_notifications" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.email_notifications', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    <div>
                                        <p class="font-medium">SMS Notifications</p>
                                        <p class="text-xs text-base-content/60">Receive important alerts via SMS</p>
                                    </div>
                                </div>
                                <input type="checkbox" name="sms_notifications" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.sms_notifications', false) ? 'checked' : '' }} />
                            </label>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Notification Types --}}
                    <div>
                        <h3 class="text-sm font-semibold text-base-content/80 mb-3">Notification Types</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Budget & Expense Alerts</p>
                                    <p class="text-xs text-base-content/60">Budget limits, spending alerts</p>
                                </div>
                                <input type="checkbox" name="notify_expense_alerts" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.notify_expense_alerts', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Task Reminders</p>
                                    <p class="text-xs text-base-content/60">Upcoming tasks and to-dos</p>
                                </div>
                                <input type="checkbox" name="notify_task_reminders" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.notify_task_reminders', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Co-Parent Messages</p>
                                    <p class="text-xs text-base-content/60">New messages from co-parents</p>
                                </div>
                                <input type="checkbox" name="notify_coparent_messages" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.notify_coparent_messages', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Calendar Events</p>
                                    <p class="text-xs text-base-content/60">Upcoming events and schedule changes</p>
                                </div>
                                <input type="checkbox" name="notify_calendar_events" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.notify_calendar_events', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Document Expiry</p>
                                    <p class="text-xs text-base-content/60">Documents expiring soon</p>
                                </div>
                                <input type="checkbox" name="notify_document_expiry" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.notify_document_expiry', true) ? 'checked' : '' }} />
                            </label>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Digest & Marketing --}}
                    <div>
                        <h3 class="text-sm font-semibold text-base-content/80 mb-3">Summary & Updates</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Weekly Digest</p>
                                    <p class="text-xs text-base-content/60">Weekly summary of activity</p>
                                </div>
                                <input type="checkbox" name="weekly_digest" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.weekly_digest', false) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Product Updates</p>
                                    <p class="text-xs text-base-content/60">New features and announcements</p>
                                </div>
                                <input type="checkbox" name="marketing_emails" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('notifications.marketing_emails', false) ? 'checked' : '' }} />
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Appearance Tab --}}
        @if($tab === 'appearance')
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-6">Appearance Settings</h2>

                <form action="{{ route('settings.appearance.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Theme</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="theme" value="{{ $value }}" class="peer hidden" {{ $tenant->getSetting('appearance.theme', 'light') === $value ? 'checked' : '' }} />
                                    <div class="flex flex-col items-center gap-2 p-4 border-2 border-base-200 rounded-lg peer-checked:border-primary peer-checked:bg-primary/5">
                                        @if($value === 'light')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        @elseif($value === 'dark')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        @endif
                                        <span class="text-sm font-medium">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Timezone</span>
                            </label>
                            <select name="timezone" class="select select-bordered">
                                @foreach(['America/New_York' => 'Eastern Time (ET)', 'America/Chicago' => 'Central Time (CT)', 'America/Denver' => 'Mountain Time (MT)', 'America/Los_Angeles' => 'Pacific Time (PT)', 'America/Anchorage' => 'Alaska Time (AKT)', 'Pacific/Honolulu' => 'Hawaii Time (HT)', 'Europe/London' => 'London (GMT)', 'Europe/Paris' => 'Paris (CET)', 'Asia/Tokyo' => 'Tokyo (JST)', 'Australia/Sydney' => 'Sydney (AEST)'] as $tz => $label)
                                    <option value="{{ $tz }}" {{ $tenant->getSetting('appearance.timezone', 'America/New_York') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Date Format</span>
                            </label>
                            <select name="date_format" class="select select-bordered">
                                @foreach(['M d, Y' => 'Jan 15, 2024', 'm/d/Y' => '01/15/2024', 'd/m/Y' => '15/01/2024', 'Y-m-d' => '2024-01-15'] as $format => $example)
                                    <option value="{{ $format }}" {{ $tenant->getSetting('appearance.date_format', 'M d, Y') === $format ? 'selected' : '' }}>{{ $example }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Currency</span>
                        </label>
                        <select name="currency" class="select select-bordered w-full md:w-1/2">
                            @foreach(['USD' => 'US Dollar ($)', 'EUR' => 'Euro (&euro;)', 'GBP' => 'British Pound (&pound;)', 'CAD' => 'Canadian Dollar (C$)', 'AUD' => 'Australian Dollar (A$)'] as $code => $label)
                                <option value="{{ $code }}" {{ $tenant->getSetting('appearance.currency', 'USD') === $code ? 'selected' : '' }}>{!! $label !!}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Privacy Tab --}}
        @if($tab === 'privacy')
        <div class="space-y-6">
            {{-- Privacy Settings --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-6">Privacy Settings</h2>

                    <form action="{{ route('settings.privacy.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Profile Visibility</span>
                            </label>
                            <select name="profile_visibility" class="select select-bordered w-full md:w-1/2">
                                <option value="family" {{ $tenant->getSetting('privacy.profile_visibility', 'family') === 'family' ? 'selected' : '' }}>Family Members Only</option>
                                <option value="collaborators" {{ $tenant->getSetting('privacy.profile_visibility') === 'collaborators' ? 'selected' : '' }}>Family & Collaborators</option>
                                <option value="private" {{ $tenant->getSetting('privacy.profile_visibility') === 'private' ? 'selected' : '' }}>Private (Only Me)</option>
                            </select>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Activity Tracking</p>
                                    <p class="text-xs text-base-content/60">Track login activity and session history</p>
                                </div>
                                <input type="checkbox" name="activity_tracking" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('privacy.activity_tracking', true) ? 'checked' : '' }} />
                            </label>

                            <label class="flex items-center justify-between p-3 border border-base-200 rounded-lg cursor-pointer hover:bg-base-50">
                                <div>
                                    <p class="font-medium">Anonymous Analytics</p>
                                    <p class="text-xs text-base-content/60">Help improve FamilyLedger with usage data</p>
                                </div>
                                <input type="checkbox" name="share_analytics" value="1" class="toggle toggle-primary" {{ $tenant->getSetting('privacy.share_analytics', false) ? 'checked' : '' }} />
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Export Data --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Export Your Data
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Download a copy of all your data stored in FamilyLedger, including family members, transactions, journal entries, and more.</p>
                    <a href="{{ route('settings.export-data') }}" class="btn btn-outline btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download My Data
                    </a>
                </div>
            </div>

            {{-- Delete Account --}}
            <div class="card bg-base-100 shadow-sm border border-error/30">
                <div class="card-body">
                    <h2 class="card-title text-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Account
                    </h2>

                    <div class="bg-error/10 border border-error/30 rounded-lg p-4 mb-4">
                        <p class="text-sm text-error">
                            <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. All your data, including family members, documents, transactions, and history will be permanently deleted.
                        </p>
                    </div>

                    <button type="button" onclick="document.getElementById('deleteAccountModal').showModal()" class="btn btn-error btn-outline btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete Account Modal --}}
        <dialog id="deleteAccountModal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error">Delete Account</h3>
                <p class="py-4 text-sm text-base-content/70">This action is permanent and cannot be undone. To confirm, enter your password and type <strong>DELETE</strong> below.</p>

                <form action="{{ route('settings.delete-account') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Your Password</span>
                        </label>
                        <input type="password" name="password" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Type DELETE to confirm</span>
                        </label>
                        <input type="text" name="confirmation" class="input input-bordered" placeholder="DELETE" required />
                    </div>
                    <div class="modal-action">
                        <button type="button" onclick="document.getElementById('deleteAccountModal').close()" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-error">Permanently Delete</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
        @endif
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = input.closest('form').querySelector('.avatar');
            avatarContainer.classList.remove('placeholder');
            avatarContainer.innerHTML = `
                <div class="w-20 rounded-full">
                    <img src="${e.target.result}" alt="Avatar Preview" />
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Authenticator Setup
let authenticatorSecret = '';

function initAuthenticatorSetup() {
    const modal = document.getElementById('authenticatorSetupModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Reset to step 1
    document.getElementById('authenticatorStep1').classList.remove('hidden');
    document.getElementById('authenticatorStep2').classList.add('hidden');
    document.getElementById('authenticatorCode').value = '';
    document.getElementById('authenticatorError').classList.add('hidden');

    // Show loading state
    document.getElementById('qrCodeContainer').innerHTML = '<span class="loading loading-spinner loading-lg"></span>';
    document.getElementById('secretKey').textContent = 'Loading...';

    // Fetch QR code and secret from server
    fetch('/settings/mfa/authenticator/setup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            authenticatorSecret = data.secret;
            document.getElementById('qrCodeContainer').innerHTML = data.qr_code;
            document.getElementById('secretKey').textContent = data.secret;
        } else {
            document.getElementById('qrCodeContainer').innerHTML = '<p class="text-error text-sm">Failed to generate QR code. Please try again.</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('qrCodeContainer').innerHTML = '<p class="text-error text-sm">Failed to generate QR code. Please try again.</p>';
    });
}

function showAuthenticatorStep1() {
    document.getElementById('authenticatorStep1').classList.remove('hidden');
    document.getElementById('authenticatorStep2').classList.add('hidden');
}

function showAuthenticatorStep2() {
    document.getElementById('authenticatorStep1').classList.add('hidden');
    document.getElementById('authenticatorStep2').classList.remove('hidden');
    document.getElementById('authenticatorCode').focus();
}

function verifyAuthenticatorCode() {
    const code = document.getElementById('authenticatorCode').value.trim();
    const errorEl = document.getElementById('authenticatorError');
    const verifyBtn = document.getElementById('verifyAuthBtn');

    if (code.length !== 6 || !/^\d+$/.test(code)) {
        errorEl.textContent = 'Please enter a valid 6-digit code';
        errorEl.classList.remove('hidden');
        return;
    }

    // Disable button and show loading
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Verifying...';
    errorEl.classList.add('hidden');

    fetch('/settings/mfa/authenticator/confirm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            code: code,
            secret: authenticatorSecret
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page to show success
            closeAuthenticatorModal();
            window.location.reload();
        } else {
            errorEl.textContent = data.message || 'Invalid code. Please try again.';
            errorEl.classList.remove('hidden');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = 'Verify & Enable';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorEl.textContent = 'An error occurred. Please try again.';
        errorEl.classList.remove('hidden');
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = 'Verify & Enable';
    });
}

function closeAuthenticatorModal() {
    const modal = document.getElementById('authenticatorSetupModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    authenticatorSecret = '';
}

// Allow Enter key to submit code and Escape to close modal
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('authenticatorCode');
    if (codeInput) {
        codeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyAuthenticatorCode();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('authenticatorSetupModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAuthenticatorModal();
            }
        }
    });
});
</script>
@endsection
