@extends('layouts.dashboard')

@section('page-name', 'Activities')

@section('content')
<div class="p-4 lg:p-6 flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto rounded-2xl bg-green-100 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(34 197 94)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Activities Log</h2>
        <p class="text-slate-500 mb-6">
            Track extracurricular activities, sports practices, lessons, and events. Keep both parents informed about what's happening.
        </p>
        <span class="badge badge-lg badge-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            Coming Soon
        </span>
    </div>
</div>
@endsection
