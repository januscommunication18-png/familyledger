@extends('layouts.dashboard')

@php
    $isOwnerSelf = $member->relationship === 'self' && $member->linked_user_id == auth()->id();
    $backUrl = $isOwnerSelf ? route('family-circle.owner.show', $circle) : route('family-circle.member.show', [$circle, $member]);
    $canEdit = $access->canEdit('birth_certificate');
    $isViewOnly = !$canEdit;
@endphp

@section('title', $isViewOnly ? 'View Birth Certificate' : (($document ? 'Edit' : 'Add') . ' Birth Certificate'))
@section('page-name', $isViewOnly ? 'View Birth Certificate' : (($document ? 'Edit' : 'Add') . ' Birth Certificate'))

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
    <li aria-current="page">Birth Certificate</li>
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
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">
                        @if($isViewOnly)
                            Birth Certificate
                        @else
                            {{ $document ? 'Edit' : 'Add' }} Birth Certificate
                        @endif
                    </h1>
                    @if($isViewOnly)
                        <span class="badge badge-soft badge-secondary text-xs">View Only</span>
                    @endif
                </div>
                <p class="text-slate-500">{{ $member->full_name }}</p>
            </div>
        </div>
    </div>

    @if($isViewOnly && $document)
        {{-- READ-ONLY VIEW --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <!-- Certificate Details Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Certificate Details</h2>
                    <p class="text-sm text-slate-500 mb-4">Birth certificate information</p>

                    <div class="space-y-4">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Certificate Number</label>
                            <p class="text-slate-900 font-medium">{{ $document->document_number ?? 'Not provided' }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">State/Country of Issue</label>
                            <p class="text-slate-900 font-medium">{{ $document->state_of_issue ?? 'Not provided' }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Issue Date</label>
                            <p class="text-slate-900 font-medium">{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('F d, Y') : 'Not provided' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Document Images Section (Read-Only) -->
                @if($document->front_image || $document->back_image)
                    <div class="pt-6 border-t border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900 mb-1">Certificate Images</h2>
                        <p class="text-sm text-slate-500 mb-4">Uploaded certificate images</p>

                        <div class="grid grid-cols-2 gap-4">
                            @if($document->front_image)
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Front of Certificate</label>
                                    <div class="border border-slate-200 rounded-xl p-2 bg-slate-50">
                                        <x-protected-image
                                            :src="route('member.documents.image', [$member, $document, 'front'])"
                                            alt="Front of certificate"
                                            class="w-full h-40 object-contain rounded-lg"
                                            container-class="w-full h-40 rounded-lg"
                                        />
                                    </div>
                                </div>
                            @endif

                            @if($document->back_image)
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Back of Certificate</label>
                                    <div class="border border-slate-200 rounded-xl p-2 bg-slate-50">
                                        <x-protected-image
                                            :src="route('member.documents.image', [$member, $document, 'back'])"
                                            alt="Back of certificate"
                                            class="w-full h-40 object-contain rounded-lg"
                                            container-class="w-full h-40 rounded-lg"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="card-body pt-0">
                <div class="flex items-center justify-start gap-3 pt-6 border-t border-slate-200">
                    <a href="{{ $backUrl }}" class="btn btn-ghost gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back to Member
                    </a>
                </div>
            </div>
        </div>
    @elseif($isViewOnly && !$document)
        {{-- NO DATA MESSAGE --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Birth Certificate Added</h3>
                    <p class="text-slate-500 mb-6">Birth certificate information has not been added for this member yet.</p>
                    <a href="{{ $backUrl }}" class="btn btn-ghost gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back to Member
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- EDITABLE FORM --}}
        <div class="card bg-base-100 shadow-sm">
            <form action="{{ $document ? route('member.documents.update', [$member, $document]) : route('member.documents.store', $member) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($document)
                    @method('PUT')
                @endif
                <input type="hidden" name="document_type" value="birth_certificate">

                <div class="card-body">
                    <!-- Certificate Details Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-1">Certificate Details</h2>
                        <p class="text-sm text-slate-500 mb-4">Enter the birth certificate information</p>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Certificate Number <span class="text-rose-500">*</span></label>
                                <input type="text" name="document_number" value="{{ old('document_number', $document?->document_number) }}"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                    placeholder="e.g., BC-123456"
                                    required>
                                @error('document_number')
                                    <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">State/Country of Issue</label>
                                <input type="text" name="state_of_issue" value="{{ old('state_of_issue', $document?->state_of_issue) }}"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                    placeholder="e.g., California or United States">
                            </div>

                            <x-date-select
                                name="issue_date"
                                label="Issue Date"
                                :value="$document?->issue_date"
                                :required="true"
                            />
                        </div>
                    </div>

                    <!-- Document Images Section -->
                    <div class="pt-6 border-t border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900 mb-1">Certificate Images</h2>
                        <p class="text-sm text-slate-500 mb-4">Upload front and back images of the certificate</p>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Front Image -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Front of Certificate</label>
                                <div class="relative">
                                    <input type="file" name="front_image" id="front_image" accept="image/*" class="hidden" onchange="previewFile(this, 'frontPreview')">
                                    <label for="front_image" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50/50 transition-all group">
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
                                                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-2 group-hover:bg-amber-200 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
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
                                <label class="block text-sm font-medium text-slate-700 mb-2">Back of Certificate</label>
                                <div class="relative">
                                    <input type="file" name="back_image" id="back_image" accept="image/*" class="hidden" onchange="previewFile(this, 'backPreview')">
                                    <label for="back_image" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50/50 transition-all group">
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
                                                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-2 group-hover:bg-amber-200 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
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
                            {{ $document ? 'Update' : 'Save' }} Certificate
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
</script>
@endpush
