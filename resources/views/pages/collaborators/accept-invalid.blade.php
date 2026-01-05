@extends('layouts.auth')

@section('title', 'Invitation Invalid')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        @if($reason === 'not_found')
            <div class="w-20 h-20 bg-error/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Invitation Not Found</h1>
            <p class="text-slate-600 mb-8">This invitation link is invalid or has been removed. Please check the link and try again, or contact the person who invited you.</p>

        @elseif($reason === 'already_accepted')
            <div class="w-20 h-20 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Already Accepted!</h1>
            <p class="text-slate-600 mb-8">This invitation has already been accepted. You should have access to the shared family information.</p>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                    Log In
                </a>
            @endauth

        @elseif($reason === 'expired')
            <div class="w-20 h-20 bg-warning/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Invitation Expired</h1>
            <p class="text-slate-600 mb-8">This invitation has expired. Please ask the person who invited you to send a new invitation.</p>

        @elseif($reason === 'revoked')
            <div class="w-20 h-20 bg-error/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-error"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Invitation Revoked</h1>
            <p class="text-slate-600 mb-8">This invitation has been revoked by the sender. If you believe this is a mistake, please contact them directly.</p>
        @endif

        <!-- Back to Home -->
        <div class="mt-8">
            <a href="/" class="text-primary hover:underline">
                Return to homepage
            </a>
        </div>
    </div>
</div>
@endsection
