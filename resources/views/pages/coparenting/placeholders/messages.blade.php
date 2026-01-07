@extends('layouts.dashboard')

@section('page-name', 'Messages')

@section('content')
<div class="p-4 lg:p-6 flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(168 85 247)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Secure Messages</h2>
        <p class="text-slate-500 mb-6">
            Communicate about your children in a focused, drama-free environment. All conversations are logged for record-keeping.
        </p>
        <span class="badge badge-lg badge-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            Coming Soon
        </span>
    </div>
</div>
@endsection
