@extends('layouts.dashboard')

@section('title', 'Document Vault - ' . $member->full_name)
@section('page-name', 'Document Vault')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.show', $member->familyCircle) }}" class="hover:text-violet-600">{{ $member->familyCircle->name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.member.show', [$member->familyCircle, $member]) }}" class="hover:text-violet-600">{{ $member->full_name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Documents</li>
@endsection

@section('page-title', 'Document Vault')
@section('page-description', 'Securely store and manage documents for ' . $member->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="avatar">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-500">
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="text-lg font-bold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                    </div>
                </div>
            </div>
            <div>
                <p class="font-medium text-slate-700">{{ $member->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $documents->count() }} document{{ $documents->count() != 1 ? 's' : '' }}</p>
            </div>
        </div>
        <button type="button" onclick="addDocumentModal.showModal()" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/></svg>
            Add Document
        </button>
    </div>

    <!-- Document Categories -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach($documentTypes as $typeKey => $typeName)
            @php
                $typeDoc = $documents->where('document_type', $typeKey)->first();
                $hasDoc = $typeDoc !== null;
            @endphp
            <div class="card {{ $hasDoc ? 'bg-base-100' : 'bg-slate-50 border border-dashed border-slate-200' }} shadow-sm hover:shadow-md transition-all cursor-pointer"
                 onclick="{{ $hasDoc ? "viewDocumentModal('{$typeDoc->id}')" : "addDocumentOfType('{$typeKey}')" }}">
                <div class="card-body p-4 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl {{ $hasDoc ? 'bg-violet-100' : 'bg-slate-100' }} flex items-center justify-center mb-2">
                        @if($typeKey === 'drivers_license')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $hasDoc ? 'text-violet-600' : 'text-slate-400' }}"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        @elseif($typeKey === 'passport')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $hasDoc ? 'text-violet-600' : 'text-slate-400' }}"><rect width="16" height="20" x="4" y="2" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 17h10"/></svg>
                        @elseif($typeKey === 'social_security')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $hasDoc ? 'text-violet-600' : 'text-slate-400' }}"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @elseif($typeKey === 'birth_certificate')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $hasDoc ? 'text-violet-600' : 'text-slate-400' }}"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 15h6"/><path d="M12 18v-6"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $hasDoc ? 'text-violet-600' : 'text-slate-400' }}"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        @endif
                    </div>
                    <p class="font-medium text-sm {{ $hasDoc ? 'text-slate-700' : 'text-slate-400' }}">{{ $typeName }}</p>
                    @if($hasDoc)
                        @if($typeDoc->isExpired())
                            <span class="badge badge-error badge-xs">Expired</span>
                        @elseif($typeDoc->expiresWithin(30))
                            <span class="badge badge-warning badge-xs">Expiring Soon</span>
                        @else
                            <span class="badge badge-success badge-xs">Active</span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Click to add</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- All Documents List -->
    @if($documents->isNotEmpty())
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">All Documents</h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Number</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $doc->document_type_name }}</p>
                                                @if($doc->state_of_issue || $doc->country_of_issue)
                                                    <p class="text-xs text-slate-400">{{ $doc->state_of_issue ?? '' }}{{ $doc->state_of_issue && $doc->country_of_issue ? ', ' : '' }}{{ $doc->country_of_issue ?? '' }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($doc->document_type === 'social_security')
                                            <span class="font-mono">{{ $doc->masked_number ?? 'XXX-XX-XXXX' }}</span>
                                        @else
                                            <span>{{ $doc->document_number ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->issue_date ? $doc->issue_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $doc->expiry_date ? $doc->expiry_date->format('M d, Y') : 'No expiry' }}</td>
                                    <td>
                                        @if($doc->isExpired())
                                            <span class="badge badge-error gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                                Expired
                                            </span>
                                        @elseif($doc->expiresWithin(30))
                                            <span class="badge badge-warning gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                Expiring Soon
                                            </span>
                                        @else
                                            <span class="badge badge-success gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex justify-end gap-1">
                                            @if($doc->front_image || $doc->back_image)
                                                <button class="btn btn-ghost btn-xs" onclick="viewImages({{ $doc->id }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </button>
                                            @endif
                                            <button class="btn btn-ghost btn-xs" onclick="editDocument({{ $doc->id }})">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                            </button>
                                            <form action="{{ route('member.documents.destroy', [$member, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-rose-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Add Document Modal -->
<dialog id="addDocumentModal" class="modal">
    <div class="modal-box max-w-2xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </form>
        <h3 class="text-xl font-bold mb-6">Add Document</h3>

        <form id="addDocForm" action="{{ route('member.documents.store', $member) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Document Type <span class="text-rose-500">*</span></span></label>
                    <select name="document_type" id="docTypeSelect" class="select select-bordered w-full" required onchange="toggleSSNField()">
                        <option value="">Select document type</option>
                        @foreach($documentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="ssnField" class="form-control hidden">
                    <label class="label"><span class="label-text font-medium">Social Security Number</span></label>
                    <input type="text" name="ssn_number" placeholder="XXX-XX-XXXX" class="input input-bordered w-full" pattern="\d{3}-?\d{2}-?\d{4}">
                    <label class="label"><span class="label-text-alt text-slate-400">This will be encrypted and stored securely</span></label>
                </div>

                <div id="regularFields">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Document Number</span></label>
                            <input type="text" name="document_number" class="input input-bordered w-full">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">State of Issue</span></label>
                            <input type="text" name="state_of_issue" class="input input-bordered w-full">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Country of Issue</span></label>
                            <input type="text" name="country_of_issue" value="United States" class="input input-bordered w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Issue Date</span></label>
                            <input type="date" name="issue_date" class="input input-bordered w-full">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Expiry Date</span></label>
                            <input type="date" name="expiry_date" class="input input-bordered w-full">
                        </div>
                    </div>
                </div>

                <div class="divider">Document Images</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Front Image</span></label>
                        <input type="file" name="front_image" accept="image/*" class="file-input file-input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Back Image</span></label>
                        <input type="file" name="back_image" accept="image/*" class="file-input file-input-bordered w-full">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Notes</span></label>
                    <textarea name="details" class="textarea textarea-bordered w-full" rows="2" placeholder="Any additional details..."></textarea>
                </div>
            </div>

            <div class="modal-action">
                <button type="button" onclick="addDocumentModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Document</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection

@push('scripts')
<script>
function toggleSSNField() {
    const docType = document.getElementById('docTypeSelect').value;
    const ssnField = document.getElementById('ssnField');
    const regularFields = document.getElementById('regularFields');

    if (docType === 'social_security') {
        ssnField.classList.remove('hidden');
        regularFields.classList.add('hidden');
    } else {
        ssnField.classList.add('hidden');
        regularFields.classList.remove('hidden');
    }
}

function addDocumentOfType(type) {
    document.getElementById('docTypeSelect').value = type;
    toggleSSNField();
    addDocumentModal.showModal();
}

function viewDocumentModal(id) {
    // Could open a view modal - for now just navigate to detail
    console.log('View document', id);
}

function viewImages(id) {
    // Could open an image viewer modal
    console.log('View images for document', id);
}

function editDocument(id) {
    // Could open edit modal
    console.log('Edit document', id);
}
</script>
@endpush
