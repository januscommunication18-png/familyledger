@extends('layouts.dashboard')

@section('page-name', 'Edit History')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('coparenting.messages.show', $message->conversation_id) }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Conversation
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit History</h1>
            <p class="text-slate-500">Complete edit history for this message</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        {{-- Info Banner --}}
        <div class="alert bg-blue-50 border border-blue-200 text-blue-800 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            <div>
                <p class="font-semibold">Audit Trail</p>
                <p class="text-sm opacity-80">All edits are permanently logged with timestamps and IP addresses for court compliance.</p>
            </div>
        </div>

        {{-- Current Message --}}
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-3">
                    <span class="badge badge-primary">Current Version</span>
                    <span class="text-sm text-slate-500">{{ $message->updated_at->format('M j, Y \a\t g:i A') }}</span>
                </div>
                <div class="bg-base-200 rounded-lg p-4">
                    <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                </div>
                <div class="flex items-center gap-3 mt-3 text-sm text-slate-500">
                    <span>Sent by: <strong>{{ $message->sender->name }}</strong></span>
                    <span>Original: {{ $message->created_at->format('M j, Y \a\t g:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Edit History --}}
        @if($edits->count() > 0)
        <h3 class="font-semibold text-slate-800 mb-4">Edit History ({{ $edits->count() }} edit{{ $edits->count() > 1 ? 's' : '' }})</h3>

        <div class="space-y-4">
            @foreach($edits as $index => $edit)
            <div class="card bg-base-100 shadow-sm border-l-4 border-amber-400">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-outline badge-warning">Edit #{{ $edits->count() - $index }}</span>
                            <span class="text-sm text-slate-500">{{ $edit->created_at->format('M j, Y \a\t g:i A') }}</span>
                        </div>
                        @if($edit->ip_address)
                        <span class="text-xs text-slate-400" title="IP Address">{{ $edit->ip_address }}</span>
                        @endif
                    </div>

                    {{-- Previous Content --}}
                    <div class="mb-3">
                        <span class="text-xs font-medium text-red-600 uppercase tracking-wider">Before</span>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mt-1">
                            <p class="whitespace-pre-wrap text-red-800">{{ $edit->previous_content }}</p>
                        </div>
                    </div>

                    {{-- New Content --}}
                    <div>
                        <span class="text-xs font-medium text-emerald-600 uppercase tracking-wider">After</span>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mt-1">
                            <p class="whitespace-pre-wrap text-emerald-800">{{ $edit->new_content }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            </div>
            <p class="text-slate-500">This message has not been edited.</p>
        </div>
        @endif
    </div>
</div>
@endsection
