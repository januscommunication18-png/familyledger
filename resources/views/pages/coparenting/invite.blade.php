@extends('layouts.dashboard')

@section('page-name', 'Invite Co-parent')

@section('content')
<div class="p-4 lg:p-6 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('coparenting.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to Dashboard
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Invite Co-parent</h1>
        <p class="text-slate-500">Send an invitation to your co-parent to share child information.</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('coparenting.invite.send') }}" method="POST" class="space-y-6" id="inviteForm">
        @csrf

        {{-- Step 1: Select Children --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">1</div>
                    <h3 class="font-semibold text-slate-800">Select Children <span class="text-error">*</span></h3>
                </div>
                <p class="text-sm text-slate-500 mb-4">Choose which children to share with your co-parent.</p>

                @if($minors->count() > 0)
                <div class="space-y-3">
                    @foreach($minors as $minor)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors child-checkbox-label">
                        <input type="checkbox" name="children[]" value="{{ $minor->id }}" class="checkbox checkbox-primary child-checkbox" {{ in_array($minor->id, old('children', [])) ? 'checked' : '' }}>
                        @if($minor->profile_image_url)
                            <img src="{{ $minor->profile_image_url }}" alt="{{ $minor->full_name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center">
                                <span class="text-sm font-bold text-white">{{ strtoupper(substr($minor->first_name ?? 'C', 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="font-medium text-slate-800">{{ $minor->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $minor->age }} years old</p>
                        </div>
                        @if($minor->co_parenting_enabled)
                            <span class="badge badge-success badge-xs">Co-parenting active</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                @error('children')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                @enderror
                @else
                <div class="text-center py-6">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                    </div>
                    <p class="text-slate-500 text-sm mb-3">No children found in your family circle.</p>
                    <a href="{{ route('family-circle.index') }}" class="btn btn-sm btn-outline">Add Children</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Step 2: Select Parent Role & Co-parent --}}
        <div class="card bg-base-100 shadow-sm" id="parentSelectionCard" style="{{ old('children') ? '' : 'display: none;' }}">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">2</div>
                    <h3 class="font-semibold text-slate-800">Select Co-parent <span class="text-error">*</span></h3>
                </div>

                {{-- Parent Role Selection --}}
                <div class="form-control mb-6">
                    <label class="label">
                        <span class="label-text font-medium">Relationship to Children <span class="text-error">*</span></span>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all parent-role-label" data-role="mother">
                            <input type="radio" name="parent_role" value="mother" class="radio radio-primary parent-role-radio" {{ old('parent_role') === 'mother' ? 'checked' : '' }} required>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </div>
                                <span class="text-slate-700 font-medium">Mother</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all parent-role-label" data-role="father">
                            <input type="radio" name="parent_role" value="father" class="radio radio-primary parent-role-radio" {{ old('parent_role') === 'father' ? 'checked' : '' }}>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </div>
                                <span class="text-slate-700 font-medium">Father</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all parent-role-label" data-role="parent">
                            <input type="radio" name="parent_role" value="parent" class="radio radio-primary parent-role-radio" {{ old('parent_role') === 'parent' ? 'checked' : '' }}>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <span class="text-slate-700 font-medium">Parent (Other)</span>
                            </div>
                        </label>
                    </div>
                    @error('parent_role')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Potential Co-parents from Family Circle --}}
                @if($potentialCoparents->count() > 0)
                <div class="mb-6" id="potentialCoparentsSection">
                    <label class="label">
                        <span class="label-text font-medium">Suggested from your Family Circle</span>
                    </label>
                    <p class="text-sm text-slate-500 mb-3">We found these family members who might be the co-parent:</p>

                    <div class="space-y-2">
                        @foreach($potentialCoparents as $potential)
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-primary cursor-pointer transition-all coparent-option" data-email="{{ $potential->email }}">
                            <input type="radio" name="coparent_source" value="existing_{{ $potential->id }}" class="radio radio-primary coparent-source-radio">
                            @if($potential->profile_image_url)
                                <img src="{{ $potential->profile_image_url }}" alt="{{ $potential->full_name }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-200">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center ring-2 ring-slate-200">
                                    <span class="text-lg font-bold text-white">{{ strtoupper(substr($potential->first_name ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">{{ $potential->full_name }}</p>
                                <p class="text-sm text-slate-500">{{ ucfirst($potential->relationship) }} &bull; {{ $potential->email ?: 'No email on file' }}</p>
                            </div>
                            <div class="badge badge-outline badge-sm">{{ ucfirst($potential->relationship) }}</div>
                        </label>
                        @endforeach

                        {{-- Option to invite someone else --}}
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-dashed border-slate-300 hover:border-primary cursor-pointer transition-all coparent-option" data-email="">
                            <input type="radio" name="coparent_source" value="new_invite" class="radio radio-primary coparent-source-radio" {{ old('coparent_source') === 'new_invite' ? 'checked' : '' }}>
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">Invite Someone Else</p>
                                <p class="text-sm text-slate-500">Send an invitation to a different email address</p>
                            </div>
                        </label>
                    </div>
                </div>
                @endif

                {{-- New Invite Fields (shown when "Invite Someone Else" is selected or no potential coparents) --}}
                <div id="newInviteFields" style="{{ ($potentialCoparents->count() === 0 || old('coparent_source') === 'new_invite') ? '' : 'display: none;' }}">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <h4 class="font-medium text-slate-700 mb-4">Co-parent Contact Information</h4>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Email Address <span class="text-error">*</span></span>
                            </label>
                            <input type="email" name="email" id="emailInput" value="{{ old('email') }}" placeholder="coparent@example.com" class="input input-bordered @error('email') input-error @enderror">
                            @error('email')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">First Name</span>
                                </label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Jane" class="input input-bordered">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Last Name</span>
                                </label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" class="input input-bordered">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Personal Message --}}
        <div class="card bg-base-100 shadow-sm" id="messageCard" style="{{ old('children') ? '' : 'display: none;' }}">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">3</div>
                    <h3 class="font-semibold text-slate-800">Personal Message</h3>
                    <span class="badge badge-ghost badge-sm">Optional</span>
                </div>
                <p class="text-sm text-slate-500 mb-4">Add an optional message to your invitation.</p>

                <textarea name="message" rows="3" placeholder="Hi! I'd like to invite you to co-parent with me on Family Ledger so we can stay coordinated on our children's information..." class="textarea textarea-bordered w-full">{{ old('message') }}</textarea>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4" id="infoBox" style="{{ old('children') ? '' : 'display: none;' }}">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">What happens next?</p>
                    <ul class="list-disc list-inside text-blue-700 space-y-1">
                        <li>Your co-parent will receive an email invitation</li>
                        <li>They'll need to create a Family Ledger account if they don't have one</li>
                        <li>Once accepted, they'll have view access to shared children's information</li>
                        <li>You can manage their permissions at any time</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center justify-end gap-3" id="submitSection" style="{{ old('children') ? '' : 'display: none;' }}">
            <a href="{{ route('coparenting.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary gap-2" id="submitBtn" {{ $minors->count() === 0 ? 'disabled' : '' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Send Invitation
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const childCheckboxes = document.querySelectorAll('.child-checkbox');
    const parentSelectionCard = document.getElementById('parentSelectionCard');
    const messageCard = document.getElementById('messageCard');
    const infoBox = document.getElementById('infoBox');
    const submitSection = document.getElementById('submitSection');
    const newInviteFields = document.getElementById('newInviteFields');
    const coparentSourceRadios = document.querySelectorAll('.coparent-source-radio');
    const coparentOptions = document.querySelectorAll('.coparent-option');
    const emailInput = document.getElementById('emailInput');
    const parentRoleLabels = document.querySelectorAll('.parent-role-label');
    const parentRoleRadios = document.querySelectorAll('.parent-role-radio');
    const hasPotentialCoparents = {{ $potentialCoparents->count() > 0 ? 'true' : 'false' }};

    // Function to check if any children are selected
    function updateVisibility() {
        const anyChecked = Array.from(childCheckboxes).some(cb => cb.checked);

        if (anyChecked) {
            parentSelectionCard.style.display = 'block';
            messageCard.style.display = 'block';
            infoBox.style.display = 'block';
            submitSection.style.display = 'flex';
        } else {
            parentSelectionCard.style.display = 'none';
            messageCard.style.display = 'none';
            infoBox.style.display = 'none';
            submitSection.style.display = 'none';
        }
    }

    // Add event listeners to child checkboxes
    childCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateVisibility);
    });

    // Handle coparent source selection
    coparentSourceRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update visual selection
            coparentOptions.forEach(opt => {
                opt.classList.remove('border-primary', 'bg-primary/5');
                opt.classList.add('border-slate-200');
            });

            const parentLabel = this.closest('.coparent-option');
            if (parentLabel) {
                parentLabel.classList.remove('border-slate-200');
                parentLabel.classList.add('border-primary', 'bg-primary/5');
            }

            // Show/hide new invite fields
            if (this.value === 'new_invite') {
                newInviteFields.style.display = 'block';
                emailInput.required = true;
            } else {
                newInviteFields.style.display = 'none';
                emailInput.required = false;

                // Auto-fill email from selected existing coparent
                const selectedOption = document.querySelector('input[name="coparent_source"]:checked');
                if (selectedOption) {
                    const parentEl = selectedOption.closest('.coparent-option');
                    if (parentEl && parentEl.dataset.email) {
                        emailInput.value = parentEl.dataset.email;
                    }
                }
            }
        });
    });

    // Handle parent role selection visual feedback
    parentRoleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            parentRoleLabels.forEach(label => {
                label.classList.remove('border-primary', 'bg-primary/5');
                label.classList.add('border-slate-200');
            });

            const parentLabel = this.closest('.parent-role-label');
            if (parentLabel) {
                parentLabel.classList.remove('border-slate-200');
                parentLabel.classList.add('border-primary', 'bg-primary/5');
            }
        });
    });

    // If no potential coparents, always show new invite fields
    if (!hasPotentialCoparents) {
        if (newInviteFields) {
            newInviteFields.style.display = 'block';
            if (emailInput) emailInput.required = true;
        }
    }

    // Initialize visual state for pre-selected options
    const checkedRole = document.querySelector('.parent-role-radio:checked');
    if (checkedRole) {
        const parentLabel = checkedRole.closest('.parent-role-label');
        if (parentLabel) {
            parentLabel.classList.remove('border-slate-200');
            parentLabel.classList.add('border-primary', 'bg-primary/5');
        }
    }

    const checkedSource = document.querySelector('.coparent-source-radio:checked');
    if (checkedSource) {
        const parentLabel = checkedSource.closest('.coparent-option');
        if (parentLabel) {
            parentLabel.classList.remove('border-slate-200');
            parentLabel.classList.add('border-primary', 'bg-primary/5');
        }
    }

    // Initial check
    updateVisibility();
});
</script>

<style>
.parent-role-label {
    border-color: rgb(226 232 240);
}
.parent-role-label:hover {
    border-color: rgb(203 213 225);
}
.coparent-option {
    transition: all 0.2s ease;
}
</style>
@endsection
