@extends('layouts.dashboard')

@section('page-name', 'Actual Time')

@section('content')
<div class="p-4 lg:p-6 flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto rounded-2xl bg-cyan-100 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(6 182 212)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Actual Time Tracking</h2>
        <p class="text-slate-500 mb-6">
            Log actual custody time versus scheduled time. Helpful for understanding parenting time patterns and for legal documentation.
        </p>
        <span class="badge badge-lg badge-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            Coming Soon
        </span>
    </div>
</div>
@endsection
