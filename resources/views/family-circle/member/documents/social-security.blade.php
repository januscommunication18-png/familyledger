@extends('layouts.dashboard')

@php
    $isOwnerSelf = $member->relationship === 'self' && $member->linked_user_id == auth()->id();
    $backUrl = $isOwnerSelf ? route('family-circle.owner.show', $circle) : route('family-circle.member.show', [$circle, $member]);
    $canEdit = $access->canEdit('ssn');
    $isViewOnly = !$canEdit;
@endphp

@section('title', ($document ? ($canEdit ? 'Edit' : 'View') : 'Add') . ' Social Security Card')
@section('page-name', ($document ? ($canEdit ? 'Edit' : 'View') : 'Add') . ' Social Security Card')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ $backUrl }}" class="hover:text-violet-600">{{ $member->full_name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Social Security</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ $backUrl }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
            @if($isViewOnly)
            <span class="badge badge-ghost">View Only</span>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 12h.01"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $document ? ($canEdit ? 'Edit' : 'View') : 'Add' }} Social Security Card</h1>
                <p class="text-slate-500">{{ $member->full_name }}</p>
            </div>
        </div>
    </div>

    @if($isViewOnly && $document)
    {{-- READ-ONLY VIEW --}}
    <!-- Security Notice -->
    <div class="alert bg-amber-50 border border-amber-200 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.87L13.637 3.59a1.914 1.914 0 0 0-3.274 0z"/><path d="M12 17h.01"/></svg>
        <div>
            <h4 class="font-semibold text-amber-800">Sensitive Information</h4>
            <p class="text-sm text-amber-700">Social Security numbers are encrypted. Only the last 4 digits are displayed for security.</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <!-- SSN Details Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-1">Social Security Number</h2>
                <p class="text-sm text-slate-500 mb-4">SSN information on file</p>

                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">SSN (Last 4 digits)</p>
                    <p class="font-medium text-slate-900 font-mono tracking-wider">XXX-XX-{{ substr($document->document_number ?? '0000', -4) }}</p>
                </div>
            </div>

            <!-- Document Images Section -->
            @if($document->front_image || $document->back_image)
            <div class="pt-6 border-t border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-1">Card Images</h2>
                <p class="text-sm text-slate-500 mb-4">Front and back images of the Social Security card</p>

                <div class="grid grid-cols-2 gap-4">
                    @if($document->front_image)
                    <div>
                        <p class="text-sm font-medium text-slate-700 mb-2">Front of Card</p>
                        <div class="rounded-lg overflow-hidden border border-slate-200">
                            <x-protected-image
                                :src="route('member.documents.image', [$member, $document, 'front'])"
                                alt="Front of Card"
                                class="w-full h-40 object-contain"
                                container-class="w-full h-40"
                            />
                        </div>
                    </div>
                    @endif

                    @if($document->back_image)
                    <div>
                        <p class="text-sm font-medium text-slate-700 mb-2">Back of Card</p>
                        <div class="rounded-lg overflow-hidden border border-slate-200">
                            <x-protected-image
                                :src="route('member.documents.image', [$member, $document, 'back'])"
                                alt="Back of Card"
                                class="w-full h-40 object-contain"
                                container-class="w-full h-40"
                            />
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="pt-6 border-t border-slate-200">
                <a href="{{ $backUrl }}" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    @elseif($isViewOnly && !$document)
    {{-- NO DATA AND VIEW ONLY --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 12h.01"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-1">No Social Security Card on File</h3>
                <p class="text-sm text-slate-500 mb-4">No Social Security information has been added yet.</p>
                <a href="{{ $backUrl }}" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    @else
    {{-- EDITABLE FORM --}}
    <!-- Security Notice -->
    <div class="alert bg-amber-50 border border-amber-200 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.87L13.637 3.59a1.914 1.914 0 0 0-3.274 0z"/><path d="M12 17h.01"/></svg>
        <div>
            <h4 class="font-semibold text-amber-800">Sensitive Information</h4>
            <p class="text-sm text-amber-700">Social Security numbers are stored securely and encrypted. Only the last 4 digits will be displayed after saving.</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <form action="{{ $document ? route('member.documents.update', [$member, $document]) : route('member.documents.store', $member) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($document)
                @method('PUT')
            @endif
            <input type="hidden" name="document_type" value="social_security">

            <div class="card-body">
                <!-- SSN Details Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Social Security Number</h2>
                    <p class="text-sm text-slate-500 mb-4">Enter the SSN in format XXX-XX-XXXX</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                SSN <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="document_number" value="{{ old('document_number', $document?->document_number) }}" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 font-mono tracking-wider"
                                placeholder="123-45-6789" maxlength="11"
                                oninput="formatSSN(this)">
                            @error('document_number')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Document Images Section -->
                <div class="pt-6 border-t border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Card Images</h2>
                    <p class="text-sm text-slate-500 mb-4">Upload images of the Social Security card (optional)</p>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Front Image -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Front of Card</label>
                            <div class="relative">
                                <input type="file" name="front_image" id="front_image" accept="image/*" class="hidden" onchange="previewFile(this, 'frontPreview')">
                                <label for="front_image" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all group">
                                    <div id="frontPreview" class="hidden w-full h-full p-2">
                                        <img src="" alt="Front preview" class="w-full h-full object-contain rounded-lg">
                                    </div>
                                    <div id="frontPlaceholder" class="flex flex-col items-center justify-center py-4">
                                        @if($document?->front_image)
                                            <x-protected-image
                                                :src="route('member.documents.image', [$member, $document, 'front'])"
                                                alt="Current front"
                                                class="w-full h-32 object-contain rounded-lg"
                                                container-class="w-full h-32 rounded-lg mb-2"
                                            />
                                            <span class="text-xs text-slate-500">Click to replace</span>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mb-2 group-hover:bg-emerald-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600">Upload Front</span>
                                            <span class="text-xs text-slate-400 mt-1">PNG, JPG up to 2MB</span>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Back Image -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Back of Card</label>
                            <div class="relative">
                                <input type="file" name="back_image" id="back_image" accept="image/*" class="hidden" onchange="previewFile(this, 'backPreview')">
                                <label for="back_image" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all group">
                                    <div id="backPreview" class="hidden w-full h-full p-2">
                                        <img src="" alt="Back preview" class="w-full h-full object-contain rounded-lg">
                                    </div>
                                    <div id="backPlaceholder" class="flex flex-col items-center justify-center py-4">
                                        @if($document?->back_image)
                                            <x-protected-image
                                                :src="route('member.documents.image', [$member, $document, 'back'])"
                                                alt="Current back"
                                                class="w-full h-32 object-contain rounded-lg"
                                                container-class="w-full h-32 rounded-lg mb-2"
                                            />
                                            <span class="text-xs text-slate-500">Click to replace</span>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mb-2 group-hover:bg-emerald-200 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600">Upload Back</span>
                                            <span class="text-xs text-slate-400 mt-1">PNG, JPG up to 2MB</span>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="card-body pt-0">
                <div class="flex items-center justify-start gap-3 pt-6 border-t border-slate-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $document ? 'Update' : 'Save' }} SSN
                    </button>
                    <a href="{{ $backUrl }}" class="btn btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(previewId.replace('Preview', 'Placeholder'));

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function formatSSN(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 9) value = value.substring(0, 9);

    if (value.length > 5) {
        input.value = value.substring(0, 3) + '-' + value.substring(3, 5) + '-' + value.substring(5);
    } else if (value.length > 3) {
        input.value = value.substring(0, 3) + '-' + value.substring(3);
    } else {
        input.value = value;
    }
}
</script>
@endpush
