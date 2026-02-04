@extends('layouts.dashboard')

@section('page-name', 'Daily Check-ins')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6" x-data>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Daily Check-ins</h1>
            <p class="text-slate-500">Track your child's daily mood and wellbeing.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')

            {{-- Daily Check-in Button - Only show if user can check in today --}}
            @if($canCheckin)
                <button @click="$dispatch('open-daily-checkin')" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    New Check-in
                </button>
            @else
                @if($custodyParent)
                    <div class="badge badge-lg gap-2 py-3 px-4" style="background-color: {{ $custodyParent === 'mother' ? '#fce7f3' : '#dbeafe' }}; color: {{ $custodyParent === 'mother' ? '#be185d' : '#1d4ed8' }};">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        Today is {{ ucfirst($custodyParent) }}'s day
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Selected Child Info --}}
    @if($selectedChild)
    <div class="alert bg-purple-50 border border-purple-200 text-purple-800 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                @if($selectedChild->profile_image_url)
                    <img src="{{ $selectedChild->profile_image_url }}" alt="{{ $selectedChild->first_name }}" class="w-full h-full rounded-full object-cover">
                @else
                    <span class="text-sm font-bold text-white">{{ strtoupper(substr($selectedChild->first_name ?? 'C', 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <p class="font-semibold">Showing check-ins for {{ $selectedChild->full_name }}</p>
                <p class="text-sm opacity-80">{{ $checkins->total() }} check-in{{ $checkins->total() !== 1 ? 's' : '' }} recorded</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Check-ins List --}}
    @if($checkins->count() > 0)
    <div class="space-y-4">
        @foreach($checkins as $checkin)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-start gap-4">
                    {{-- Mood Emoji --}}
                    <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center text-3xl">
                        {{ $checkin->mood_emoji }}
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-slate-800">{{ $checkin->mood_label }}</span>
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-sm text-slate-500">{{ $checkin->checkin_date->format('l, M j, Y') }}</span>
                        </div>

                        @if($checkin->notes)
                        <p class="text-slate-600 mb-2">{{ $checkin->notes }}</p>
                        @endif

                        <div class="flex items-center gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $checkin->created_at->format('g:i A') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $checkin->checkedBy->name ?? 'Unknown' }}
                            </span>
                            @if($checkin->parent_role)
                            <span class="badge badge-sm" style="background-color: {{ $checkin->parent_role === 'mother' ? '#fce7f3' : '#dbeafe' }}; color: {{ $checkin->parent_role === 'mother' ? '#be185d' : '#1d4ed8' }};">
                                {{ ucfirst($checkin->parent_role) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Child Avatar (if showing all children) --}}
                    @if(!$selectedChild && $checkin->child)
                    <div class="text-right">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center mx-auto mb-1">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($checkin->child->first_name ?? 'C', 0, 1)) }}</span>
                        </div>
                        <span class="text-xs text-slate-500">{{ $checkin->child->first_name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $checkins->links() }}
    </div>
    @else
    {{-- Empty State --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-16">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgb(168 85 247)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No check-ins yet</h3>
            <p class="text-slate-500 mb-6">Start tracking your child's daily mood and wellbeing.</p>
            @if($canCheckin)
            <button @click="$dispatch('open-daily-checkin')" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Create First Check-in
            </button>
            @else
                @if($custodyParent)
                <p class="text-sm text-slate-400">Today is {{ ucfirst($custodyParent) }}'s custody day</p>
                @endif
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Daily Check-in Modal --}}
@include('partials.modals.daily-checkin-modal')
@endsection
