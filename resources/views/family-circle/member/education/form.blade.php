@extends('layouts.dashboard')

@section('title', $schoolRecord ? 'Edit School Record' : 'Add School Record')
@section('page-name', $schoolRecord ? 'Edit School Record' : 'Add School Record')

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
    <li aria-current="page">{{ $schoolRecord ? 'Edit' : 'Add' }}</li>
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
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $schoolRecord ? 'Edit School Record' : 'Add School Record' }}</h1>
                <p class="text-slate-500">{{ $member->full_name }}</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <div>
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ $schoolRecord ? route('family-circle.member.education.school.update', [$circle, $member, $schoolRecord]) : route('family-circle.member.education.school.store', [$circle, $member]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($schoolRecord)
            @method('PUT')
        @endif

        <!-- School Information Card -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">School Information</h2>
                        <p class="text-xs text-slate-400">Basic school details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-medium">School Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="school_name" class="input input-bordered" placeholder="e.g., Lincoln Elementary School" value="{{ old('school_name', $schoolRecord?->school_name) }}" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">School Year</span>
                        </label>
                        <input type="text" name="school_year" class="input input-bordered" placeholder="e.g., 2024-2025" value="{{ old('school_year', $schoolRecord?->school_year) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Grade Level</span>
                        </label>
                        <select name="grade_level" class="select select-bordered">
                            <option value="">Select grade</option>
                            @foreach($gradeLevels as $key => $label)
                                <option value="{{ $key }}" {{ old('grade_level', $schoolRecord?->grade_level) == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Student ID</span>
                        </label>
                        <input type="text" name="student_id" class="input input-bordered" placeholder="Student ID number" value="{{ old('student_id', $schoolRecord?->student_id) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Current School</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="is_current" value="1" class="checkbox checkbox-primary" {{ old('is_current', $schoolRecord?->is_current ?? true) ? 'checked' : '' }}>
                            <span class="label-text">This is the current school</span>
                        </label>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-medium">School Address</span>
                        </label>
                        <input type="text" name="school_address" class="input input-bordered" placeholder="Full school address" value="{{ old('school_address', $schoolRecord?->school_address) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">School Phone</span>
                        </label>
                        <input type="tel" name="school_phone" class="input input-bordered" placeholder="(555) 123-4567" value="{{ old('school_phone', $schoolRecord?->school_phone) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">School Email</span>
                        </label>
                        <input type="email" name="school_email" class="input input-bordered" placeholder="office@school.edu" value="{{ old('school_email', $schoolRecord?->school_email) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher & Staff Card (Only for minors) -->
        @if($member->is_minor || ($member->date_of_birth && $member->date_of_birth->age < 18))
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Teacher & Counselor</h2>
                        <p class="text-xs text-slate-400">Staff contact information</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Teacher Name</span>
                        </label>
                        <input type="text" name="teacher_name" class="input input-bordered" placeholder="Teacher's full name" value="{{ old('teacher_name', $schoolRecord?->teacher_name) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Teacher Email</span>
                        </label>
                        <input type="email" name="teacher_email" class="input input-bordered" placeholder="teacher@school.edu" value="{{ old('teacher_email', $schoolRecord?->teacher_email) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Counselor Name</span>
                        </label>
                        <input type="text" name="counselor_name" class="input input-bordered" placeholder="Counselor's full name" value="{{ old('counselor_name', $schoolRecord?->counselor_name) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Counselor Email</span>
                        </label>
                        <input type="email" name="counselor_email" class="input input-bordered" placeholder="counselor@school.edu" value="{{ old('counselor_email', $schoolRecord?->counselor_email) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bus Information Card -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Bus Information</h2>
                        <p class="text-xs text-slate-400">Transportation details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Bus Number</span>
                        </label>
                        <input type="text" name="bus_number" class="input input-bordered" placeholder="e.g., 42" value="{{ old('bus_number', $schoolRecord?->bus_number) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Pickup Time</span>
                        </label>
                        <input type="time" name="bus_pickup_time" class="input input-bordered" value="{{ old('bus_pickup_time', $schoolRecord?->bus_pickup_time) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Dropoff Time</span>
                        </label>
                        <input type="time" name="bus_dropoff_time" class="input input-bordered" value="{{ old('bus_dropoff_time', $schoolRecord?->bus_dropoff_time) }}">
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes Card -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Notes</h2>
                        <p class="text-xs text-slate-400">Additional information</p>
                    </div>
                </div>

                <div class="form-control">
                    <textarea name="notes" class="textarea textarea-bordered" rows="3" placeholder="Additional notes about this school record">{{ old('notes', $schoolRecord?->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Documents</h2>
                        <p class="text-xs text-slate-400">Attach documents to this record</p>
                    </div>
                </div>

                <!-- Existing Documents (when editing) -->
                @if($schoolRecord && $schoolRecord->documents && $schoolRecord->documents->count() > 0)
                    <div class="mb-4">
                        <p class="text-sm font-medium text-slate-700 mb-3">Existing Documents</p>
                        <div class="space-y-2">
                            @foreach($schoolRecord->documents as $document)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                                            @if(str_contains($document->mime_type ?? '', 'image'))
                                                <img src="{{ $document->file_url }}" alt="{{ $document->title }}" class="w-10 h-10 object-cover">
                                            @else
                                                <span class="{{ $document->file_icon }} text-slate-600 text-lg"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800 text-sm">{{ $document->title }}</p>
                                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                                <span>{{ $document->document_type_name }}</span>
                                                <span>&bull;</span>
                                                <span>{{ $document->formatted_file_size }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('family-circle.member.education.document.download', [$circle, $member, $document]) }}" class="btn btn-ghost btn-xs" title="Download">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                        </a>
                                        <button type="button" onclick="confirmDelete('{{ route('family-circle.member.education.document.destroy', [$circle, $member, $document]) }}', 'Are you sure you want to delete this document?', 'Delete Document?')" class="btn btn-ghost btn-xs text-error" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="divider my-4"></div>
                @endif

                <!-- Upload New Document -->
                <p class="text-sm font-medium text-slate-700 mb-3">{{ $schoolRecord ? 'Add New Document' : 'Upload Document (Optional)' }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Document Type</span>
                        </label>
                        <select name="document_type" class="select select-bordered">
                            <option value="">Select type</option>
                            @foreach($documentTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Document Title</span>
                        </label>
                        <input type="text" name="document_title" class="input input-bordered" placeholder="e.g., Fall 2024 Report Card">
                    </div>

                    <div class="md:col-span-2">
                        <label for="education-document-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-blue-400 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-blue-600">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-400 mt-1">PDF, JPG, PNG, DOC, DOCX (max 10MB)</p>
                            </div>
                            <input id="education-document-upload" type="file" name="document_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </label>
                        <div id="education-file-list" class="mt-2 space-y-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3 justify-end">
            <a href="{{ route('family-circle.member.education-info', [$circle, $member]) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ $schoolRecord ? 'Update School Record' : 'Add School Record' }}
            </button>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<x-delete-confirm-modal id="deleteConfirmModal" />
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('education-document-upload');
    const fileList = document.getElementById('education-file-list');

    if (fileInput && fileList) {
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center gap-2 p-2 bg-blue-50 rounded-lg text-sm';
                fileItem.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span class="flex-1 truncate text-slate-700">${file.name}</span>
                    <span class="text-xs text-slate-400">${(file.size / 1024).toFixed(1)} KB</span>
                `;
                fileList.appendChild(fileItem);
            }
        });
    }
});
</script>
@endpush
