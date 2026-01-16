@extends('layouts.dashboard')

@section('title', $document ? 'Edit Legal Document' : 'Add Legal Document')
@section('page-name', 'Legal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('legal.index') }}" class="hover:text-primary">Legal</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $document ? 'Edit Document' : 'Add Document' }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('legal.index') }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14 14 2 2"/><path d="M16 4a2 2 0 1 1 4 0v2a2 2 0 0 1-2 2h-2V4Z"/><path d="M11 6 7 10"/><path d="m7 6 4 4"/><rect x="2" y="2" width="8" height="8" rx="2"/><path d="M11 18H5a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h6"/><path d="m14 14 6 6"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $document ? 'Edit Legal Document' : 'Add Legal Document' }}</h1>
                <p class="text-slate-500">Store your important legal documents securely</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ $document ? route('legal.update', $document) : route('legal.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($document)
            @method('PUT')
        @endif

        <!-- Document Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Document Information</h2>
                        <p class="text-xs text-slate-400">Basic legal document details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Type <span class="text-rose-500">*</span></label>
                        <select name="document_type" id="document_type" required class="select select-bordered w-full">
                            <option value="">Choose type</option>
                            @foreach($documentTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('document_type', $selectedType ?? $document?->document_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="custom_type_container" class="{{ old('document_type', $selectedType ?? $document?->document_type) === 'other' ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Custom Document Type</label>
                        <input type="text" name="custom_document_type" value="{{ old('custom_document_type', $document?->custom_document_type) }}"
                               class="input w-full"
                               placeholder="e.g., Prenuptial Agreement" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $document?->name) }}" required
                               class="input w-full"
                               placeholder="e.g., John Smith Last Will and Testament" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $document?->status ?? 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Family Circle</label>
                        <select name="family_circle_id" class="select select-bordered w-full">
                            <option value="">All Circles (Visible to everyone)</option>
                            @foreach($familyCircles as $circle)
                                <option value="{{ $circle->id }}" {{ old('family_circle_id', $document?->family_circle_id ?? ($selectedFamilyCircleId ?? null)) == $circle->id ? 'selected' : '' }}>{{ $circle->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Select a circle to restrict visibility, or leave empty for all circles</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Location of Original Document</label>
                        <input type="text" name="original_location" value="{{ old('original_location', $document?->original_location) }}"
                               class="input w-full"
                               placeholder="e.g., Home safe, safety deposit box at First National Bank" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Important Dates</h2>
                        <p class="text-xs text-slate-400">Execution and expiration information</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Execution Date -->
                    <x-date-select
                        name="execution_date"
                        label="Execution Date"
                        :value="$document?->execution_date"
                    />
                    <p class="text-xs text-slate-400 -mt-2">Date the document was signed</p>

                    <!-- Expiration Date -->
                    <x-date-select
                        name="expiration_date"
                        label="Expiration Date"
                        :value="$document?->expiration_date"
                    />
                    <p class="text-xs text-slate-400 -mt-2">If applicable</p>

                    <!-- Digital Copy Date -->
                    <x-date-select
                        name="digital_copy_date"
                        label="Digital Copy Date"
                        :value="$document?->digital_copy_date"
                    />
                    <p class="text-xs text-slate-400 -mt-2">When the digital copy was created</p>
                </div>
            </div>
        </div>

        <!-- Attorney Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="m14 14 2 2"/><path d="M16 4a2 2 0 1 1 4 0v2a2 2 0 0 1-2 2h-2V4Z"/><rect x="2" y="12" width="8" height="8" rx="2"/><path d="m9 12 2-2 5 5"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Attorney Information</h2>
                        <p class="text-xs text-slate-400">Legal professional handling this document</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select from People Directory</label>
                        <select name="attorney_person_id" id="attorney_person_select" class="select select-bordered w-full">
                            <option value="">None selected</option>
                            @foreach($attorneys as $attorney)
                                <option value="{{ $attorney->id }}" {{ old('attorney_person_id', $document?->attorney_person_id) == $attorney->id ? 'selected' : '' }}>
                                    {{ $attorney->full_name }} ({{ $attorney->relationship_name }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">
                            Select from your <a href="{{ route('people.index') }}" class="text-primary hover:underline" target="_blank">People Directory</a> or enter manually below
                        </p>
                    </div>

                    <div class="divider text-xs text-slate-400">OR ENTER MANUALLY</div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Attorney Name</label>
                        <input type="text" name="attorney_name" id="attorney_name" value="{{ old('attorney_name', $document?->attorney_name) }}"
                               class="input w-full"
                               placeholder="Full name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Law Firm</label>
                        <input type="text" name="attorney_firm" value="{{ old('attorney_firm', $document?->attorney_firm) }}"
                               class="input w-full"
                               placeholder="Firm name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="tel" name="attorney_phone" value="{{ old('attorney_phone', $document?->attorney_phone) }}"
                               class="input w-full"
                               placeholder="(555) 123-4567" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="attorney_email" value="{{ old('attorney_email', $document?->attorney_email) }}"
                               class="input w-full"
                               placeholder="attorney@lawfirm.com" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Files -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Upload Files</h2>
                        <p class="text-xs text-slate-400">Upload document scans and related files</p>
                    </div>
                </div>

                @if($document && $document->files->count() > 0)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Existing Files</label>
                    <div class="space-y-2">
                        @foreach($document->files as $file)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="w-8 h-8 rounded bg-slate-200 flex items-center justify-center">
                                <span class="{{ $file->file_icon }} size-4 text-slate-600"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $file->original_name }}</p>
                                <p class="text-xs text-slate-400">{{ $file->formatted_file_size }}@if($file->folder) &middot; {{ $file->folder }}@endif</p>
                            </div>
                            <div class="flex gap-1">
                                <a href="{{ route('legal.files.download', [$document, $file]) }}" class="btn btn-ghost btn-xs btn-square" title="Download">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                </a>
                                <button type="button" onclick="confirmFileDelete('{{ route('legal.files.destroy', [$document, $file]) }}')" class="btn btn-ghost btn-xs btn-square text-rose-500" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Upload Files</label>
                    <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-blue-400 transition-colors" onclick="document.getElementById('file-input').click()">
                        <div class="flex flex-col items-center justify-center py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            <p class="text-sm text-slate-600"><span class="font-medium text-blue-600">Click to upload</span></p>
                            <p class="text-xs text-slate-400 mt-1">Select multiple files - PDF, DOC, JPG, PNG, WebP, HEIC up to 20MB each</p>
                        </div>
                    </div>
                    <input type="file" name="files[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.heic,.heif" class="hidden" id="file-input" multiple />

                    <!-- Selected files preview -->
                    <div id="selected-files" class="mt-3 space-y-2"></div>
                    <p id="file-count" class="text-xs text-slate-500 mt-2 hidden"></p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Notes</h2>
                        <p class="text-xs text-slate-400">Additional information about this document</p>
                    </div>
                </div>

                <div>
                    <textarea name="notes" rows="4" class="textarea textarea-bordered w-full"
                              placeholder="Any additional notes about this document...">{{ old('notes', $document?->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-start gap-3">
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $document ? 'Update Document' : 'Save Document' }}
            </button>
            <a href="{{ route('legal.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<!-- Delete File Confirmation Modal -->
<dialog id="deleteFileModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Delete File?</h3>
        <p class="py-4">Are you sure you want to delete this file? This action cannot be undone.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <form id="deleteFileForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide custom document type field
    const typeSelect = document.getElementById('document_type');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const customContainer = document.getElementById('custom_type_container');
            if (this.value === 'other') {
                customContainer.classList.remove('hidden');
            } else {
                customContainer.classList.add('hidden');
            }
        });
    }

    // Clear manual entry when selecting from contacts
    const attorneySelect = document.getElementById('attorney_person_select');
    if (attorneySelect) {
        attorneySelect.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('attorney_name').value = '';
            }
        });
    }

    // File upload preview
    const fileInput = document.getElementById('file-input');
    const selectedFilesContainer = document.getElementById('selected-files');
    const fileCountEl = document.getElementById('file-count');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            selectedFilesContainer.innerHTML = '';

            if (this.files.length > 0) {
                // Show file count
                fileCountEl.textContent = this.files.length + ' file(s) selected';
                fileCountEl.classList.remove('hidden');

                Array.from(this.files).forEach(function(file, index) {
                    const fileSize = formatFileSize(file.size);
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'flex items-center gap-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200';
                    fileDiv.innerHTML = `
                        <div class="w-8 h-8 rounded bg-emerald-200 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">${file.name}</p>
                            <p class="text-xs text-slate-400">${fileSize}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500"><path d="M20 6 9 17l-5-5"/></svg>
                    `;
                    selectedFilesContainer.appendChild(fileDiv);
                });
            } else {
                fileCountEl.classList.add('hidden');
            }
        });
    }

    function formatFileSize(bytes) {
        if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' bytes';
    }
});

function confirmFileDelete(url) {
    document.getElementById('deleteFileForm').action = url;
    document.getElementById('deleteFileModal').showModal();
}
</script>
@endsection
