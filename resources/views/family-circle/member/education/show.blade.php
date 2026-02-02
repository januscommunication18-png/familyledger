@extends('layouts.dashboard')

@php
    $canEdit = $access->canEdit('school');
    $isViewOnly = !$canEdit;
@endphp

@section('title', 'School Record')
@section('page-name', 'School Record')

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
    <li><a href="{{ route('family-circle.member.education-info', [$circle, $member]) }}" class="hover:text-violet-600">Education</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $schoolRecord->school_name }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.member.education-info', [$circle, $member]) }}" class="btn btn-ghost btn-sm gap-2">
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
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-slate-900">{{ $schoolRecord->school_name }}</h1>
                        @if($schoolRecord->is_current)
                            <span class="badge badge-primary">Current</span>
                        @endif
                        @if($isViewOnly)
                            <span class="badge badge-soft badge-secondary text-xs">View Only</span>
                        @endif
                    </div>
                    <p class="text-slate-500">
                        @if($schoolRecord->grade_level_name){{ $schoolRecord->grade_level_name }}@endif
                        @if($schoolRecord->school_year) &bull; {{ $schoolRecord->school_year }}@endif
                    </p>
                </div>
            </div>
            @if($access->canEdit('school'))
                <div class="flex gap-2">
                    <a href="{{ route('family-circle.member.education.school.edit', [$circle, $member, $schoolRecord]) }}" class="btn btn-outline btn-sm gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        Edit
                    </a>
                    <button type="button" onclick="confirmDelete('{{ route('family-circle.member.education.school.destroy', [$circle, $member, $schoolRecord]) }}', 'Are you sure you want to delete this school record? This action cannot be undone.', 'Delete School Record?')" class="btn btn-outline btn-error btn-sm gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Delete
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- School Information Card -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">School Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($schoolRecord->student_id)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Student ID</p>
                    <p class="font-medium text-slate-700 font-mono">{{ $schoolRecord->student_id }}</p>
                </div>
                @endif

                @if($schoolRecord->school_phone)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Phone</p>
                    <p class="font-medium text-slate-700">{{ $schoolRecord->school_phone }}</p>
                </div>
                @endif

                @if($schoolRecord->school_email)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Email</p>
                    <a href="mailto:{{ $schoolRecord->school_email }}" class="font-medium text-blue-600 hover:underline">{{ $schoolRecord->school_email }}</a>
                </div>
                @endif

                @if($schoolRecord->school_address)
                <div class="md:col-span-2">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Address</p>
                    <p class="font-medium text-slate-700">{{ $schoolRecord->school_address }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Teacher & Counselor Card -->
    @if($schoolRecord->teacher_name || $schoolRecord->counselor_name)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Teacher & Counselor</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($schoolRecord->teacher_name)
                <div class="p-4 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Teacher</p>
                    <p class="font-bold text-slate-800">{{ $schoolRecord->teacher_name }}</p>
                    @if($schoolRecord->teacher_email)
                        <a href="mailto:{{ $schoolRecord->teacher_email }}" class="text-sm text-blue-600 hover:underline">{{ $schoolRecord->teacher_email }}</a>
                    @endif
                </div>
                @endif

                @if($schoolRecord->counselor_name)
                <div class="p-4 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Counselor</p>
                    <p class="font-bold text-slate-800">{{ $schoolRecord->counselor_name }}</p>
                    @if($schoolRecord->counselor_email)
                        <a href="mailto:{{ $schoolRecord->counselor_email }}" class="text-sm text-blue-600 hover:underline">{{ $schoolRecord->counselor_email }}</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Bus Information Card -->
    @if($schoolRecord->bus_number)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Bus Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-yellow-50 rounded-lg text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Bus Number</p>
                    <p class="text-2xl font-bold text-yellow-700">#{{ $schoolRecord->bus_number }}</p>
                </div>

                @if($schoolRecord->bus_pickup_time)
                <div class="p-4 bg-slate-50 rounded-lg text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Pickup Time</p>
                    <p class="text-lg font-bold text-slate-700">{{ $schoolRecord->bus_pickup_time }}</p>
                </div>
                @endif

                @if($schoolRecord->bus_dropoff_time)
                <div class="p-4 bg-slate-50 rounded-lg text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Dropoff Time</p>
                    <p class="text-lg font-bold text-slate-700">{{ $schoolRecord->bus_dropoff_time }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Notes Card -->
    @if($schoolRecord->notes)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Notes</h2>
            </div>

            <p class="text-slate-600 whitespace-pre-wrap">{{ $schoolRecord->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Documents Card -->
    @if($schoolRecord->documents && $schoolRecord->documents->count() > 0)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Documents</h2>
            </div>

            <div class="space-y-3">
                @foreach($schoolRecord->documents as $document)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                                @if(str_contains($document->mime_type ?? '', 'image'))
                                    <img src="{{ $document->file_url }}" alt="{{ $document->title }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <span class="{{ $document->file_icon }} text-slate-600 text-lg"></span>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ $document->title }}</p>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <span class="badge badge-sm badge-ghost">{{ $document->document_type_name }}</span>
                                    <span>{{ $document->formatted_file_size }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('family-circle.member.education.document.download', [$circle, $member, $document]) }}" class="btn btn-ghost btn-sm" title="Download">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            </a>
                            @if($access->canEdit('school'))
                                <button type="button" onclick="confirmDelete('{{ route('family-circle.member.education.document.destroy', [$circle, $member, $document]) }}', 'Are you sure you want to delete this document? This action cannot be undone.', 'Delete Document?')" class="btn btn-ghost btn-sm text-error" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<x-delete-confirm-modal id="deleteConfirmModal" />
@endsection
