@extends('layouts.auth')

@section('title', 'Invitation Declined')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Invitation Declined</h1>
        <p class="text-slate-600 mb-6">
            You've declined the invitation from {{ $invite->inviter->name ?? 'the sender' }}.
        </p>

        <div class="bg-base-200/50 rounded-xl p-4 mb-8">
            <p class="text-sm text-slate-500">
                The sender will be notified that you've declined their invitation.
                If you change your mind, you'll need to ask them to send a new invitation.
            </p>
        </div>

        <a href="/" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Return to Homepage
        </a>
    </div>
</div>
@endsection
