@extends('layouts.dashboard')

@section('page-name', 'Co-parenting Calendar')

@section('content')
<div class="p-4 lg:p-6 flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto rounded-2xl bg-blue-100 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Shared Calendar</h2>
        <p class="text-slate-500 mb-6">
            Coordinate custody schedules, appointments, school events, and activities with your co-parent. Everyone stays in sync.
        </p>
        <span class="badge badge-lg badge-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            Coming Soon
        </span>
    </div>
</div>
@endsection
