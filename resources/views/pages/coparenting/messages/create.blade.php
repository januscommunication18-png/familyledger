@extends('layouts.dashboard')

@section('page-name', 'New Message')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('coparenting.messages.index') }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">New Conversation</h1>
            <p class="text-slate-500">Start a secure conversation about your child</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <form action="{{ route('coparenting.messages.store') }}" method="POST" class="card bg-base-100 shadow-sm">
            @csrf
            <div class="card-body">
                {{-- Child Selection --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Select Child</span>
                    </label>
                    <select name="child_id" class="select select-bordered" required>
                        <option value="">Choose a child...</option>
                        @foreach($coparentChildren as $coparentChild)
                            <option value="{{ $coparentChild->id }}" {{ old('child_id') == $coparentChild->id ? 'selected' : '' }}>
                                {{ $coparentChild->familyMember->full_name ?? 'Child' }}
                                @if($coparentChild->other_parent_name ?? null)
                                    (with {{ $coparentChild->other_parent_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('child_id')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Subject (Optional) --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Subject (Optional)</span>
                    </label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="input input-bordered" placeholder="e.g., School pickup schedule">
                </div>

                {{-- Message Category --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Category</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $key => $cat)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $key }}" class="hidden peer" {{ old('category', 'General') == $key ? 'checked' : '' }}>
                            <span class="badge badge-lg peer-checked:ring-2 peer-checked:ring-offset-2 transition-all" style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}; border-color: {{ $cat['color'] }}">
                                {{ $cat['icon'] }} {{ $cat['label'] }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('category')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Message Templates --}}
                @if($templates->count() > 0)
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Use Template (Optional)</span>
                    </label>
                    <select id="template-select" class="select select-bordered select-sm">
                        <option value="">Choose a template...</option>
                        @foreach($templates as $category => $categoryTemplates)
                        <optgroup label="{{ $category }}">
                            @foreach($categoryTemplates as $template)
                            <option value="{{ $template->content }}" data-category="{{ $template->category }}">{{ $template->title }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Message Content --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Message</span>
                    </label>
                    <textarea name="content" id="message-content" class="textarea textarea-bordered h-40" placeholder="Write your message here..." required>{{ old('content') }}</textarea>
                    @error('content')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Reminder --}}
                <div class="alert bg-amber-50 border border-amber-200 text-amber-800 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <span class="text-sm">Remember: All messages are permanently logged with timestamps for court compliance. Please communicate respectfully.</span>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('coparenting.messages.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Send Message
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template-select');
    const messageContent = document.getElementById('message-content');

    if (templateSelect && messageContent) {
        templateSelect.addEventListener('change', function() {
            if (this.value) {
                messageContent.value = this.value;
                // Update category if template has one
                const selectedOption = this.options[this.selectedIndex];
                const templateCategory = selectedOption.dataset.category;
                if (templateCategory) {
                    const categoryRadio = document.querySelector(`input[name="category"][value="${templateCategory}"]`);
                    if (categoryRadio) {
                        categoryRadio.checked = true;
                    }
                }
            }
        });
    }
});
</script>
@endsection
