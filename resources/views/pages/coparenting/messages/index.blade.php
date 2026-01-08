@extends('layouts.dashboard')

@section('page-name', 'Messages')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Secure Messages</h1>
            <p class="text-slate-500">Child-focused, court-friendly communication</p>
        </div>
        <a href="{{ route('coparenting.messages.create') }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            New Conversation
        </a>
    </div>

    {{-- Info Banner --}}
    <div class="alert bg-blue-50 border border-blue-200 text-blue-800 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
        <div>
            <p class="font-semibold">Messages are permanently logged</p>
            <p class="text-sm opacity-80">All messages are securely stored with timestamps and read receipts. Messages cannot be deleted for court compliance.</p>
        </div>
    </div>

    {{-- Category Legend --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($categories as $key => $cat)
        <span class="badge badge-outline gap-1" style="border-color: {{ $cat['color'] }}; color: {{ $cat['color'] }}">
            {{ $cat['icon'] }} {{ $cat['label'] }}
        </span>
        @endforeach
    </div>

    {{-- Conversations List --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            @forelse($conversations as $conversation)
            <a href="{{ route('coparenting.messages.show', $conversation) }}" class="flex items-start gap-4 p-4 border-b border-base-200 hover:bg-base-50 transition-colors last:border-b-0">
                {{-- Child Avatar --}}
                <div class="relative">
                    @if($conversation->child && $conversation->child->familyMember)
                        @if($conversation->child->familyMember->profile_image_url)
                            <img src="{{ $conversation->child->familyMember->profile_image_url }}" alt="{{ $conversation->child->familyMember->full_name }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-purple-200">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-violet-500 flex items-center justify-center ring-2 ring-purple-200">
                                <span class="text-lg font-bold text-white">{{ strtoupper(substr($conversation->child->familyMember->first_name ?? 'C', 0, 1)) }}</span>
                            </div>
                        @endif
                    @else
                        <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                    @endif
                    @if($conversation->unread_count > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold">{{ $conversation->unread_count }}</span>
                    @endif
                </div>

                {{-- Conversation Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-slate-800">
                            {{ $conversation->child->familyMember->full_name ?? 'Child' }}
                        </span>
                        @if($conversation->subject)
                        <span class="text-slate-400">-</span>
                        <span class="text-slate-600 truncate">{{ $conversation->subject }}</span>
                        @endif
                    </div>
                    @if($conversation->lastMessage)
                    <p class="text-sm text-slate-500 truncate">
                        <span class="font-medium">{{ $conversation->lastMessage->sender->name }}:</span>
                        {{ Str::limit($conversation->lastMessage->content, 60) }}
                    </p>
                    @endif
                </div>

                {{-- Timestamp & Category --}}
                <div class="text-right flex flex-col items-end gap-1">
                    <span class="text-xs text-slate-400">{{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}</span>
                    @if($conversation->lastMessage)
                    @php $catInfo = $categories[$conversation->lastMessage->category] ?? $categories['General']; @endphp
                    <span class="badge badge-sm" style="background-color: {{ $catInfo['color'] }}20; color: {{ $catInfo['color'] }}">
                        {{ $catInfo['icon'] }} {{ $catInfo['label'] }}
                    </span>
                    @endif
                </div>
            </a>
            @empty
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgb(168 85 247)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No conversations yet</h3>
                <p class="text-slate-500 mb-6">Start a conversation with your co-parent about your children.</p>
                <a href="{{ route('coparenting.messages.create') }}" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Start Conversation
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
