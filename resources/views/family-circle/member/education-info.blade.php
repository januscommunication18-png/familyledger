@extends('layouts.dashboard')

@section('title', 'Education')
@section('page-name', 'Education')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="hover:text-violet-600">{{ $member->full_name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Education</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Education</h1>
                    <p class="text-slate-500">{{ $member->full_name }}</p>
                </div>
            </div>
            @if($access->canEdit('school'))
                <a href="{{ route('family-circle.member.education.school.create', [$circle, $member]) }}" class="btn btn-primary btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add School Record
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Info Section -->
    @if($member->schoolRecords && $member->schoolRecords->count() > 1)
    <div class="alert bg-blue-50 border-blue-200 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span class="text-sm text-blue-700">Records are sorted by most recent school year first. Current school appears at the top.</span>
    </div>
    @endif

    <!-- School Records List -->
    @if($member->schoolRecords && $member->schoolRecords->count() > 0)
        <div class="space-y-4">
            @foreach($member->schoolRecords as $record)
                <a href="{{ route('family-circle.member.education.school.show', [$circle, $member, $record]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer block">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg {{ $record->is_current ? 'bg-blue-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $record->is_current ? 'text-blue-600' : 'text-slate-500' }}"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 truncate">{{ $record->school_name }}</h3>
                                    @if($record->is_current)
                                        <span class="badge badge-primary badge-sm flex-shrink-0">Current</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-500 mt-1">
                                    @if($record->grade_level_name)
                                        <span>{{ $record->grade_level_name }}</span>
                                    @endif
                                    @if($record->school_year)
                                        @if($record->grade_level_name)<span class="text-slate-300">|</span>@endif
                                        <span>{{ $record->school_year }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-1">No School Records</h3>
                <p class="text-slate-500 text-sm mb-4">Start tracking education history by adding a school record.</p>
                @if($access->canEdit('school'))
                    <a href="{{ route('family-circle.member.education.school.create', [$circle, $member]) }}" class="btn btn-primary gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add School Record
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
