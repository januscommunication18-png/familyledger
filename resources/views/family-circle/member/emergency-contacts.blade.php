@extends('layouts.dashboard')

@section('title', 'Emergency Contacts')
@section('page-name', 'Emergency Contacts')

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
    <li aria-current="page">Emergency Contacts</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('family-circle.member.show', [$circle, $member]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Emergency Contacts</h1>
                <p class="text-slate-500">{{ $member->full_name }}</p>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts Section -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Emergency Contacts</h2>
                        <p class="text-xs text-slate-400">People to contact in case of emergency</p>
                    </div>
                </div>
                <button type="button" onclick="toggleContactForm()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add
                </button>
            </div>

            <!-- Add Contact Form (Hidden by default) -->
            <div id="contactForm" class="hidden mb-4 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <form action="{{ route('member.emergency-contact.store', $member) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="Contact name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
                            <select name="relationship" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="">Select relationship</option>
                                @foreach($relationshipTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                                <input type="tel" name="phone" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="(555) 123-4567">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" name="email" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="email@example.com">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                            <input type="text" name="address" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="Contact address">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                            <select name="priority" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="">Auto-assign</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" onclick="toggleContactForm()" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Contacts List -->
            @if($member->contacts->count() > 0)
                <div class="space-y-2">
                    @foreach($member->contacts as $contact)
                        <div class="rounded-lg border border-slate-200 hover:border-amber-300 transition-colors">
                            <!-- Display Mode -->
                            <div id="contactDisplay{{ $contact->id }}" class="flex items-start justify-between p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-slate-800">{{ $contact->name }}</span>
                                            @if($contact->priority)
                                                <span class="badge badge-sm bg-amber-100 text-amber-700 border-0">#{{ $contact->priority }}</span>
                                            @endif
                                            @if($contact->relationship_name)
                                                <span class="text-xs text-slate-400">({{ $contact->relationship_name }})</span>
                                            @endif
                                        </div>
                                        @if($contact->phone)
                                            <p class="text-sm text-slate-600 mt-1 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                                {{ $contact->phone }}
                                            </p>
                                        @endif
                                        @if($contact->email)
                                            <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                                {{ $contact->email }}
                                            </p>
                                        @endif
                                        @if($contact->address)
                                            <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                                {{ $contact->address }}
                                            </p>
                                        @endif
                                        @if($contact->notes)
                                            <p class="text-xs text-slate-400 mt-1">{{ $contact->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleContactEdit({{ $contact->id }})" class="btn btn-ghost btn-xs text-slate-500 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('member.emergency-contact.destroy', [$member, $contact]) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Remove Contact?', 'Are you sure you want to remove {{ $contact->name }}? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Mode -->
                            <div id="contactEdit{{ $contact->id }}" class="hidden p-3 bg-amber-50 border-t border-amber-200">
                                <form action="{{ route('member.emergency-contact.update', [$member, $contact]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                                                <input type="text" name="name" value="{{ $contact->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
                                                <select name="relationship" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                                    <option value="">Select</option>
                                                    @foreach($relationshipTypes as $key => $label)
                                                        <option value="{{ $key }}" {{ $contact->relationship == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                                                <input type="tel" name="phone" value="{{ $contact->phone }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                                <input type="email" name="email" value="{{ $contact->email }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                                <input type="text" name="address" value="{{ $contact->address }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                                                <select name="priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                                    <option value="">Auto</option>
                                                    @for($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" {{ $contact->priority == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-900 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">{{ $contact->notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" onclick="toggleContactEdit({{ $contact->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <p class="text-sm">No emergency contacts recorded</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm transform transition-all">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 text-center mb-2" id="confirmModalTitle">Remove Item?</h3>
                <p class="text-sm text-slate-500 text-center mb-6" id="confirmModalMessage">Are you sure you want to remove this item? This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="button" onclick="executeConfirmedAction()" class="flex-1 btn btn-error text-white">Remove</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleContactForm() {
    const form = document.getElementById('contactForm');
    form.classList.toggle('hidden');
}

function toggleContactEdit(id) {
    const display = document.getElementById('contactDisplay' + id);
    const edit = document.getElementById('contactEdit' + id);
    display.classList.toggle('hidden');
    edit.classList.toggle('hidden');
}

// Confirmation Modal Functions
let pendingDeleteForm = null;

function confirmDelete(form, title, message) {
    pendingDeleteForm = form;
    document.getElementById('confirmModalTitle').textContent = title || 'Remove Item?';
    document.getElementById('confirmModalMessage').textContent = message || 'Are you sure you want to remove this item? This action cannot be undone.';
    document.getElementById('confirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingDeleteForm = null;
}

function executeConfirmedAction() {
    if (pendingDeleteForm) {
        pendingDeleteForm.submit();
    }
    closeConfirmModal();
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('confirmModal').classList.contains('hidden')) {
        closeConfirmModal();
    }
});
</script>
@endpush
