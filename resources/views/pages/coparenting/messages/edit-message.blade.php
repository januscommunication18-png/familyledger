@extends('layouts.dashboard')

@section('page-name', 'Edit Message')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('coparenting.messages.show', $message->conversation_id) }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Conversation
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Edit Message</h2>

                {{-- Warning --}}
                <div class="alert bg-amber-50 border border-amber-200 text-amber-800 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <div>
                        <p class="font-semibold">Edit history is permanently recorded</p>
                        <p class="text-sm opacity-80">The original message and all edits are logged for transparency. Both parties can view the edit history.</p>
                    </div>
                </div>

                {{-- Original Message Info --}}
                <div class="bg-base-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm text-slate-500">Original message sent:</span>
                        <span class="text-sm font-medium">{{ $message->created_at->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                    @if($message->wasEdited())
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-500">Times edited:</span>
                        <span class="text-sm font-medium">{{ $message->edit_count }}</span>
                        <a href="{{ route('coparenting.messages.editHistory', $message) }}" class="text-sm link link-primary">View History</a>
                    </div>
                    @endif
                </div>

                <form action="{{ route('coparenting.messages.updateMessage', $message) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Message Content</span>
                        </label>
                        <textarea name="content" class="textarea textarea-bordered h-40" required>{{ old('content', $message->content) }}</textarea>
                        @error('content')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('coparenting.messages.show', $message->conversation_id) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
