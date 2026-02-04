@extends('layouts.dashboard')

@section('page-name', 'Pending Edit Requests')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pending Edit Requests</h1>
            @if($canTakeAction ?? false)
                <p class="text-slate-500">Review and approve/reject edit requests from co-parents.</p>
            @else
                <p class="text-slate-500">View your submitted edit requests awaiting owner approval.</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')

            <span class="badge badge-warning badge-lg gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $counts['pending'] }} Pending
            </span>
            <a href="{{ route('coparenting.pending-edits.history') }}" class="btn btn-ghost btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                View History
            </a>
        </div>
    </div>

    @if($pendingEdits->isEmpty())
        {{-- Empty State --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">All Caught Up!</h3>
                @if($canTakeAction ?? false)
                    <p class="text-slate-500 max-w-md mx-auto">There are no pending edit requests from co-parents. All requests have been reviewed.</p>
                @else
                    <p class="text-slate-500 max-w-md mx-auto">You have no pending edit requests. Your submitted edits will appear here while awaiting approval.</p>
                @endif
            </div>
        </div>
    @else
        {{-- Pending Edits by Child --}}
        @foreach($pendingEdits as $childId => $edits)
            @php
                $child = $edits->first()->familyMember;
                $isOwnerOfChild = $edits->first()->tenant_id === auth()->user()->tenant_id;
            @endphp
            <div class="card bg-base-100 shadow-sm mb-6">
                <div class="card-body">
                    {{-- Child Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-4">
                            @if($child->profile_image_url)
                                <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                    <span class="text-lg font-bold text-white">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-lg text-slate-800">{{ $child->full_name }}</h3>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-warning badge-sm">{{ $edits->count() }} pending request(s)</span>
                                    @if(!$isOwnerOfChild)
                                        <span class="badge badge-info badge-sm">Your submissions</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($isOwnerOfChild)
                        <div class="sm:ml-auto flex gap-2">
                            <button onclick="bulkApproveForChild({{ $childId }})" class="btn btn-success btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Approve All
                            </button>
                            <button onclick="bulkRejectForChild({{ $childId }})" class="btn btn-outline btn-error btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                Reject All
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Edits Table --}}
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    @if($isOwnerOfChild)
                                    <th class="w-10">
                                        <input type="checkbox" class="checkbox checkbox-sm" onchange="toggleAllForChild(this, {{ $childId }})">
                                    </th>
                                    @endif
                                    <th>Field</th>
                                    <th>Current Value</th>
                                    <th>Requested Value</th>
                                    <th>{{ $isOwnerOfChild ? 'Requested By' : 'Submitted' }}</th>
                                    <th>When</th>
                                    @if($isOwnerOfChild)
                                    <th class="w-24">Actions</th>
                                    @else
                                    <th>Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($edits as $edit)
                                <tr data-child-id="{{ $childId }}" data-edit-id="{{ $edit->id }}">
                                    @if($isOwnerOfChild)
                                    <td>
                                        <input type="checkbox" class="checkbox checkbox-sm edit-checkbox" value="{{ $edit->id }}">
                                    </td>
                                    @endif
                                    <td>
                                        <div class="font-medium text-slate-800">{{ $edit->field_label }}</div>
                                        <div class="text-xs text-slate-500">{{ $edit->editable_type_label }}</div>
                                    </td>
                                    <td>
                                        @if($edit->is_create)
                                            <span class="badge badge-ghost badge-sm">New Record</span>
                                        @elseif($edit->is_delete)
                                            <span class="text-error text-sm">Record to be deleted</span>
                                        @else
                                            <span class="text-slate-600">{{ $edit->formatted_old_value }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($edit->is_create)
                                            <span class="text-emerald-600 text-sm">New record</span>
                                        @elseif($edit->is_delete)
                                            <span class="badge badge-error badge-sm">Delete</span>
                                        @else
                                            <span class="font-medium text-emerald-600">{{ $edit->formatted_new_value }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isOwnerOfChild)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                                                <span class="text-[10px] font-bold text-white">{{ strtoupper(substr($edit->requester->name ?? 'U', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm">{{ $edit->requester->name ?? 'Unknown' }}</span>
                                        </div>
                                        @else
                                        <span class="text-sm text-slate-500">By you</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-slate-500">{{ $edit->created_at->diffForHumans() }}</td>
                                    @if($isOwnerOfChild)
                                    <td>
                                        <div class="flex gap-1">
                                            <button onclick="showEditDetails({{ json_encode([
                                                'type' => $edit->is_create ? 'create' : ($edit->is_delete ? 'delete' : 'update'),
                                                'field_label' => $edit->field_label,
                                                'field_name' => $edit->field_name,
                                                'editable_type_label' => $edit->editable_type_label,
                                                'old_value' => $edit->old_value,
                                                'new_value' => $edit->new_value,
                                                'create_data' => $edit->create_data,
                                                'editable' => $edit->editable?->toArray(),
                                                'requester' => $edit->requester?->name,
                                                'created_at' => $edit->created_at->format('M j, Y g:i A'),
                                            ]) }})" class="btn btn-ghost btn-xs" title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                            <button onclick="approveEdit({{ $edit->id }})" class="btn btn-success btn-xs" title="Approve">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                            <button onclick="rejectEdit({{ $edit->id }})" class="btn btn-outline btn-error btn-xs" title="Reject">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    @else
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <button onclick="showEditDetails({{ json_encode([
                                                'type' => $edit->is_create ? 'create' : ($edit->is_delete ? 'delete' : 'update'),
                                                'field_label' => $edit->field_label,
                                                'field_name' => $edit->field_name,
                                                'editable_type_label' => $edit->editable_type_label,
                                                'old_value' => $edit->old_value,
                                                'new_value' => $edit->new_value,
                                                'create_data' => $edit->create_data,
                                                'editable' => $edit->editable?->toArray(),
                                                'requester' => $edit->requester?->name,
                                                'created_at' => $edit->created_at->format('M j, Y g:i A'),
                                            ]) }})" class="btn btn-ghost btn-xs" title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                            <span class="badge badge-warning badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                Awaiting
                                            </span>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

{{-- Edit Details Modal --}}
<div id="editDetailsModal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeEditDetailsModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full transform transition-all">
            {{-- Header --}}
            <div class="p-6 pb-0">
                <div class="flex items-center gap-3">
                    <div id="editDetailsIcon" class="w-12 h-12 rounded-full flex items-center justify-center">
                        {{-- Icon will be set by JS --}}
                    </div>
                    <div>
                        <h3 id="editDetailsTitle" class="text-xl font-bold text-slate-800"></h3>
                        <p id="editDetailsSubtitle" class="text-sm text-slate-500"></p>
                    </div>
                </div>
            </div>

            {{-- Meta Info --}}
            <div class="px-6 pt-4">
                <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Requested by: <strong id="editDetailsRequester"></strong></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span id="editDetailsDate"></span>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <div id="editDetailsContent" class="space-y-2 max-h-80 overflow-y-auto">
                    {{-- Data rows will be inserted here by JS --}}
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 p-6 pt-0">
                <button type="button" onclick="closeEditDetailsModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium rounded-lg hover:bg-slate-100 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include confirmation modal partial --}}
@include('partials.modals.confirm-modal')

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function approveEdit(editId) {
    showConfirmModal({
        type: 'success',
        title: 'Approve Edit Request',
        message: 'Are you sure you want to approve this edit? The change will be applied immediately.',
        confirmText: 'Approve',
        onConfirm: async () => {
            try {
                const response = await fetch(`/coparenting/pending-edits/${editId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showConfirmModal({
                        type: 'danger',
                        title: 'Error',
                        message: data.message || 'Failed to approve edit',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                }
            } catch (error) {
                showConfirmModal({
                    type: 'danger',
                    title: 'Error',
                    message: 'An error occurred. Please try again.',
                    confirmText: 'OK',
                    onConfirm: () => {}
                });
            }
        }
    });
}

function rejectEdit(editId) {
    showConfirmModal({
        type: 'danger',
        title: 'Reject Edit Request',
        message: 'Are you sure you want to reject this edit request?',
        confirmText: 'Reject',
        showNotes: true,
        notesLabel: 'Reason for rejection (optional)',
        notesPlaceholder: 'Enter reason for rejection...',
        onConfirm: async (notes) => {
            try {
                const response = await fetch(`/coparenting/pending-edits/${editId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ notes: notes || '' })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showConfirmModal({
                        type: 'danger',
                        title: 'Error',
                        message: data.message || 'Failed to reject edit',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                }
            } catch (error) {
                showConfirmModal({
                    type: 'danger',
                    title: 'Error',
                    message: 'An error occurred. Please try again.',
                    confirmText: 'OK',
                    onConfirm: () => {}
                });
            }
        }
    });
}

function getSelectedIdsForChild(childId) {
    return Array.from(document.querySelectorAll(`tr[data-child-id="${childId}"] .edit-checkbox:checked`))
        .map(cb => parseInt(cb.value));
}

function bulkApproveForChild(childId) {
    let ids = getSelectedIdsForChild(childId);

    // If none selected, approve all for this child
    if (ids.length === 0) {
        ids = Array.from(document.querySelectorAll(`tr[data-child-id="${childId}"] .edit-checkbox`))
            .map(cb => parseInt(cb.value));
    }

    if (ids.length === 0) return;

    showConfirmModal({
        type: 'success',
        title: 'Approve Multiple Edits',
        message: `Are you sure you want to approve ${ids.length} edit(s)? All changes will be applied immediately.`,
        confirmText: 'Approve All',
        onConfirm: async () => {
            try {
                const response = await fetch('/coparenting/pending-edits/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ ids })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showConfirmModal({
                        type: 'danger',
                        title: 'Error',
                        message: data.message || 'Failed to approve edits',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                }
            } catch (error) {
                showConfirmModal({
                    type: 'danger',
                    title: 'Error',
                    message: 'An error occurred. Please try again.',
                    confirmText: 'OK',
                    onConfirm: () => {}
                });
            }
        }
    });
}

function bulkRejectForChild(childId) {
    let ids = getSelectedIdsForChild(childId);

    // If none selected, reject all for this child
    if (ids.length === 0) {
        ids = Array.from(document.querySelectorAll(`tr[data-child-id="${childId}"] .edit-checkbox`))
            .map(cb => parseInt(cb.value));
    }

    if (ids.length === 0) return;

    showConfirmModal({
        type: 'danger',
        title: 'Reject Multiple Edits',
        message: `Are you sure you want to reject ${ids.length} edit request(s)?`,
        confirmText: 'Reject All',
        showNotes: true,
        notesLabel: 'Reason for rejection (optional)',
        notesPlaceholder: 'Enter reason for rejection...',
        onConfirm: async (notes) => {
            try {
                const response = await fetch('/coparenting/pending-edits/bulk-reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ ids, notes: notes || '' })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showConfirmModal({
                        type: 'danger',
                        title: 'Error',
                        message: data.message || 'Failed to reject edits',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                }
            } catch (error) {
                showConfirmModal({
                    type: 'danger',
                    title: 'Error',
                    message: 'An error occurred. Please try again.',
                    confirmText: 'OK',
                    onConfirm: () => {}
                });
            }
        }
    });
}

function toggleAllForChild(checkbox, childId) {
    document.querySelectorAll(`tr[data-child-id="${childId}"] .edit-checkbox`)
        .forEach(cb => cb.checked = checkbox.checked);
}

// Field label mapping for better display
const fieldLabels = {
    'tenant_id': 'Tenant',
    'family_member_id': 'Family Member',
    'uploaded_by': 'Uploaded By',
    'document_type': 'Document Type',
    'document_number': 'Document Number',
    'state_of_issue': 'State of Issue',
    'country_of_issue': 'Country of Issue',
    'issue_date': 'Issue Date',
    'expiry_date': 'Expiry Date',
    'details': 'Details',
    'encrypted_number': 'SSN Number',
    'front_image': 'Front Image',
    'back_image': 'Back Image',
    'school_name': 'School Name',
    'school_type': 'School Type',
    'grade_level': 'Grade Level',
    'start_date': 'Start Date',
    'end_date': 'End Date',
    'address': 'Address',
    'city': 'City',
    'state': 'State',
    'zip_code': 'Zip Code',
    'phone': 'Phone',
    'email': 'Email',
    'website': 'Website',
    'notes': 'Notes',
    'name': 'Name',
    'relationship': 'Relationship',
    'is_primary': 'Primary Contact',
    'is_emergency': 'Emergency Contact',
    'allergy_name': 'Allergy Name',
    'severity': 'Severity',
    'reaction': 'Reaction',
    'treatment': 'Treatment',
    'provider_name': 'Provider Name',
    'provider_type': 'Provider Type',
    'specialty': 'Specialty',
    'medication_name': 'Medication Name',
    'dosage': 'Dosage',
    'frequency': 'Frequency',
    'prescribed_by': 'Prescribed By',
    'condition_name': 'Condition Name',
    'diagnosed_date': 'Diagnosed Date',
    'vaccine_name': 'Vaccine Name',
    'date_administered': 'Date Administered',
    'administered_by': 'Administered By',
    'blood_type': 'Blood Type',
    'first_name': 'First Name',
    'last_name': 'Last Name',
    'middle_name': 'Middle Name',
    'nickname': 'Nickname',
    'date_of_birth': 'Date of Birth',
    'gender': 'Gender',
    'title': 'Title',
    'description': 'Description',
    'status': 'Status',
    'priority': 'Priority',
    'type': 'Type',
    'category': 'Category',
    'amount': 'Amount',
    'date': 'Date',
    'time': 'Time',
    'location': 'Location',
    'contact_name': 'Contact Name',
    'contact_phone': 'Contact Phone',
    'contact_email': 'Contact Email',
    'emergency_contact': 'Emergency Contact',
    'insurance_provider': 'Insurance Provider',
    'policy_number': 'Policy Number',
    'group_number': 'Group Number',
    'lot_number': 'Lot Number',
    'next_dose_date': 'Next Dose Date',
    'is_current': 'Current',
    'reason': 'Reason',
    'purpose': 'Purpose',
};

// Fields to hide from display (internal IDs, etc.)
const hiddenFields = ['tenant_id', 'family_member_id', 'uploaded_by', 'id', 'created_at', 'updated_at', 'deleted_at'];

function formatFieldLabel(field) {
    return fieldLabels[field] || field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatValue(value, isOld = false) {
    if (value === null || value === undefined || value === '') {
        return '<span class="text-slate-400 italic">Not set</span>';
    }
    if (typeof value === 'boolean' || value === '1' || value === '0' || value === 1 || value === 0) {
        const boolVal = value === true || value === '1' || value === 1;
        return boolVal
            ? `<span class="${isOld ? 'text-slate-600' : 'text-emerald-600'}">Yes</span>`
            : `<span class="text-slate-500">No</span>`;
    }
    if (typeof value === 'object') {
        return '<span class="text-slate-500 text-xs">' + JSON.stringify(value) + '</span>';
    }
    // Check if it's a file path
    if (typeof value === 'string' && (value.includes('documents/') || value.includes('/'))) {
        return `<span class="${isOld ? 'text-slate-600' : 'text-emerald-600'}">File uploaded</span>`;
    }
    return `<span class="${isOld ? 'text-slate-600' : 'text-emerald-600 font-medium'}">${value}</span>`;
}

function showEditDetails(data) {
    const iconEl = document.getElementById('editDetailsIcon');
    const titleEl = document.getElementById('editDetailsTitle');
    const subtitleEl = document.getElementById('editDetailsSubtitle');
    const requesterEl = document.getElementById('editDetailsRequester');
    const dateEl = document.getElementById('editDetailsDate');
    const contentEl = document.getElementById('editDetailsContent');

    // Set requester and date
    requesterEl.textContent = data.requester || 'Unknown';
    dateEl.textContent = data.created_at || '';

    // Clear content
    contentEl.innerHTML = '';

    // Set icon and title based on type
    if (data.type === 'create') {
        iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-emerald-100';
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/></svg>';
        titleEl.textContent = 'New ' + data.editable_type_label;
        subtitleEl.textContent = 'Creating a new record';

        // Show create data with Previous (N/A) and New columns
        if (data.create_data) {
            // Add table header
            contentEl.innerHTML = `
                <div class="grid grid-cols-12 gap-2 py-2 border-b-2 border-slate-200 font-medium text-sm text-slate-700">
                    <div class="col-span-4">Field</div>
                    <div class="col-span-4">Previous</div>
                    <div class="col-span-4">New</div>
                </div>
            `;

            for (const [key, value] of Object.entries(data.create_data)) {
                if (hiddenFields.includes(key)) continue;

                const row = document.createElement('div');
                row.className = 'grid grid-cols-12 gap-2 py-2 border-b border-slate-100 text-sm';
                row.innerHTML = `
                    <div class="col-span-4 text-slate-600 font-medium">${formatFieldLabel(key)}</div>
                    <div class="col-span-4 text-slate-400 italic">N/A</div>
                    <div class="col-span-4">${formatValue(value)}</div>
                `;
                contentEl.appendChild(row);
            }
        }
    } else if (data.type === 'delete') {
        iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-red-100';
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>';
        titleEl.textContent = 'Delete ' + data.editable_type_label;
        subtitleEl.textContent = 'Record will be permanently deleted';

        // Show current data with Previous and New (Deleted) columns
        if (data.editable) {
            contentEl.innerHTML = `
                <div class="grid grid-cols-12 gap-2 py-2 border-b-2 border-slate-200 font-medium text-sm text-slate-700">
                    <div class="col-span-4">Field</div>
                    <div class="col-span-4">Previous</div>
                    <div class="col-span-4">New</div>
                </div>
            `;

            for (const [key, value] of Object.entries(data.editable)) {
                if (hiddenFields.includes(key)) continue;

                const row = document.createElement('div');
                row.className = 'grid grid-cols-12 gap-2 py-2 border-b border-slate-100 text-sm';
                row.innerHTML = `
                    <div class="col-span-4 text-slate-600 font-medium">${formatFieldLabel(key)}</div>
                    <div class="col-span-4">${formatValue(value, true)}</div>
                    <div class="col-span-4 text-red-500 italic">Deleted</div>
                `;
                contentEl.appendChild(row);
            }
        } else {
            contentEl.innerHTML = '<p class="text-slate-500 text-center py-4">Record data not available</p>';
        }
    } else {
        // Update type
        iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-violet-100';
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
        titleEl.textContent = 'Update ' + data.editable_type_label;
        subtitleEl.textContent = 'Changing: ' + data.field_label;

        // Show single field update with Previous and New
        contentEl.innerHTML = `
            <div class="grid grid-cols-12 gap-2 py-2 border-b-2 border-slate-200 font-medium text-sm text-slate-700">
                <div class="col-span-4">Field</div>
                <div class="col-span-4">Previous</div>
                <div class="col-span-4">New</div>
            </div>
        `;

        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 py-3 border-b border-slate-100 text-sm';
        row.innerHTML = `
            <div class="col-span-4 text-slate-600 font-medium">${data.field_label || formatFieldLabel(data.field_name)}</div>
            <div class="col-span-4">${formatValue(data.old_value, true)}</div>
            <div class="col-span-4">${formatValue(data.new_value)}</div>
        `;
        contentEl.appendChild(row);
    }

    // If no visible fields for create/delete
    if (contentEl.children.length <= 1 && (data.type === 'create' || data.type === 'delete')) {
        contentEl.innerHTML += '<p class="text-slate-500 text-center py-4">No details available</p>';
    }

    document.getElementById('editDetailsModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditDetailsModal() {
    document.getElementById('editDetailsModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// For backward compatibility with showCreateData
function showCreateData(data) {
    showEditDetails({
        type: 'create',
        editable_type_label: 'Record',
        create_data: data
    });
}

// Close edit details modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('editDetailsModal').classList.contains('hidden')) {
        closeEditDetailsModal();
    }
});
</script>
@endpush
@endsection
