@extends('layouts.auth')

@section('title', 'Access Code')

@section('content')
<div class="text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>
    <h2 class="text-2xl font-semibold mb-2">Private Access</h2>
    <p class="text-base-content/60 mb-6">Enter your security code to continue</p>
</div>

<form action="{{ route('security.verify') }}" method="POST" class="space-y-4">
    @csrf

    <div class="form-control">
        <input type="text"
               name="security_code"
               placeholder="Enter access code"
               class="input input-bordered w-full text-center text-2xl tracking-widest @error('security_code') input-error @enderror"
               maxlength="4"
               pattern="[0-9]{4}"
               inputmode="numeric"
               autocomplete="off"
               autofocus
               required>
        @error('security_code')
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" x2="3" y1="12" y2="12"/>
        </svg>
        Continue
    </button>
</form>

<p class="text-center text-sm text-base-content/50 mt-6">
    This site is currently in private beta.
</p>
@endsection
