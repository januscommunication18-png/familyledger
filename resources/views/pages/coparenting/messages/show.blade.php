@extends('layouts.dashboard')

@section('page-name', 'Conversation')

@push('styles')
<style>
    /* Hide footer on this page */
    footer { display: none !important; }
    main { padding-bottom: 0 !important; }
</style>
@endpush

@section('content')
<div class="flex flex-col h-[calc(100vh-64px)] bg-gradient-to-br from-slate-50 to-purple-50/30 overflow-hidden -m-6">
    {{-- Header - Fixed/Sticky --}}
    <div class="bg-white/90 backdrop-blur-lg border-b border-slate-200/60 px-4 lg:px-6 py-3 flex-shrink-0 sticky top-0 z-20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('coparenting.messages.index') }}" class="btn btn-ghost btn-sm btn-circle hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <div class="flex items-center gap-3">
                    @if($child && $child->familyMember)
                        <div class="relative">
                            @if($child->familyMember->profile_image_url)
                                <img src="{{ $child->familyMember->profile_image_url }}" alt="{{ $child->familyMember->full_name }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-purple-100 shadow-sm">
                            @else
                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-lg shadow-purple-200/50">
                                    <span class="font-bold text-white text-lg">{{ strtoupper(substr($child->familyMember->first_name ?? 'C', 0, 1)) }}</span>
                                </div>
                            @endif
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>
                        </div>
                    @endif
                    <div>
                        <h1 class="font-semibold text-slate-800 text-lg leading-tight">
                            {{ $child->familyMember->full_name ?? 'Conversation' }}
                        </h1>
                        <p class="text-xs text-slate-500 flex items-center gap-1.5">
                            @foreach($participants as $participant)
                                <span>{{ $participant->name }}</span>@if(!$loop->last)<span class="text-slate-300">•</span>@endif
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div id="connection-status" class="px-2.5 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 bg-slate-100 text-slate-500">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-slate-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
                    </span>
                    Connecting...
                </div>
                <a href="{{ route('coparenting.messages.exportPdf', $conversation) }}" class="btn btn-ghost btn-sm btn-circle hover:bg-slate-100" title="Export to PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                </a>
            </div>
        </div>
        @if($conversation->subject)
        <div class="mt-2 ml-14">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                {{ $conversation->subject }}
            </span>
        </div>
        @endif
    </div>

    {{-- Messages Container --}}
    <div class="flex-1 overflow-y-auto px-4 lg:px-6 py-4" id="messages-container">
        <div class="max-w-3xl mx-auto space-y-3">
            @php $lastDate = null; @endphp
            @foreach($messages as $message)
            @php
                $isOwn = $message->sender_id === auth()->id();
                $catInfo = $categories[$message->category] ?? $categories['General'];
                $messageDate = $message->created_at->format('Y-m-d');
            @endphp

            @if($lastDate !== $messageDate)
            <div class="flex items-center justify-center my-6">
                <div class="px-3 py-1 rounded-full bg-white/80 backdrop-blur-sm shadow-sm text-xs text-slate-500 font-medium">
                    {{ $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' : $message->created_at->format('M j, Y')) }}
                </div>
            </div>
            @php $lastDate = $messageDate; @endphp
            @endif

            <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} message-bubble group/msg" data-message-id="{{ $message->id }}">
                <div class="max-w-[85%] lg:max-w-[70%]">
                    {{-- Sender & Category --}}
                    <div class="flex items-center gap-2 mb-1 {{ $isOwn ? 'justify-end' : '' }}">
                        @if(!$isOwn)
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-xs font-medium text-slate-600">
                            {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-slate-600">{{ $message->sender->name }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $catInfo['color'] }}15; color: {{ $catInfo['color'] }}">
                            {{ $catInfo['icon'] }} {{ $catInfo['label'] }}
                        </span>
                    </div>

                    {{-- Message Bubble with Emoji Reactions --}}
                    <div class="relative">
                        {{-- Emoji Picker (appears on hover) --}}
                        <div class="absolute {{ $isOwn ? 'left-0 -translate-x-full pr-2' : 'right-0 translate-x-full pl-2' }} top-1/2 -translate-y-1/2 opacity-0 group-hover/msg:opacity-100 transition-all z-10">
                            <div class="flex items-center gap-0.5 bg-white rounded-full shadow-lg border border-slate-100 p-1">
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="👍">👍</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="❤️">❤️</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="😊">😊</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="🙏">🙏</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="✅">✅</button>
                            </div>
                        </div>

                        <div class="rounded-2xl px-4 py-2.5 shadow-sm {{ $isOwn ? 'bg-gradient-to-br from-purple-600 to-violet-600 text-white rounded-br-md' : 'bg-white text-slate-800 rounded-bl-md border border-slate-100' }}">
                            <p class="whitespace-pre-wrap break-words text-[15px] leading-relaxed">{{ $message->content }}</p>

                            {{-- Attachments --}}
                            @if($message->attachments->count() > 0)
                            <div class="mt-3 pt-3 border-t {{ $isOwn ? 'border-white/20' : 'border-slate-100' }} space-y-2">
                                @foreach($message->attachments as $attachment)
                                <a href="{{ route('coparenting.messages.downloadAttachment', $attachment) }}" class="flex items-center gap-3 p-2.5 rounded-xl {{ $isOwn ? 'bg-white/10 hover:bg-white/20' : 'bg-slate-50 hover:bg-slate-100' }} transition-all">
                                    <div class="w-10 h-10 rounded-lg {{ $isOwn ? 'bg-white/20' : 'bg-white shadow-sm' }} flex items-center justify-center">
                                        <span class="text-xl">{{ $attachment->icon }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ $attachment->original_filename }}</p>
                                        <p class="text-xs {{ $isOwn ? 'text-white/70' : 'text-slate-400' }}">{{ $attachment->formatted_size }}</p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $isOwn ? 'text-white/60' : 'text-slate-400' }}"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Timestamp & Actions --}}
                        <div class="flex items-center gap-2 mt-1 px-1 {{ $isOwn ? 'justify-end' : '' }}">
                            <span class="text-xs text-slate-400">{{ $message->created_at->format('g:i A') }}</span>
                            @if($message->wasEdited())
                            <a href="{{ route('coparenting.messages.editHistory', $message) }}" class="text-xs text-slate-400 hover:text-purple-600 transition-colors">(edited)</a>
                            @endif
                            @if($isOwn && $message->canBeEditedBy(auth()->id()))
                            <a href="{{ route('coparenting.messages.editMessage', $message) }}" class="text-xs text-slate-400 hover:text-purple-600 transition-colors opacity-0 group-hover/msg:opacity-100">Edit</a>
                            @endif
                        </div>

                        {{-- Emoji Reactions Display --}}
                        <div class="reactions-container flex items-center gap-1 mt-1 {{ $isOwn ? 'justify-end' : '' }} px-1">
                            @php
                                $groupedReactions = $message->reactions->groupBy('emoji');
                            @endphp
                            @foreach($groupedReactions as $emoji => $emojiReactions)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-sm cursor-pointer hover:bg-slate-200 transition-colors reaction-badge" data-emoji="{{ $emoji }}" title="{{ $emojiReactions->pluck('user.name')->join(', ') }}">
                                {{ $emoji }} <span class="text-xs text-slate-500">{{ $emojiReactions->count() }}</span>
                            </span>
                            @endforeach
                        </div>

                        {{-- Read Receipts --}}
                        @if($isOwn && $message->reads->where('user_id', '!=', auth()->id())->count() > 0)
                        <div class="flex items-center gap-1 mt-0.5 {{ $isOwn ? 'justify-end' : '' }} px-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/></svg>
                            <span class="text-xs text-emerald-500">Read</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Attachment Preview --}}
    <div id="attachment-preview" class="hidden flex-shrink-0 bg-white border-t border-slate-200 px-4 py-3">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <div class="flex-1 flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span id="attachment-icon" class="text-2xl">📎</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="attachment-name" class="text-sm font-medium text-slate-800 truncate">filename.pdf</p>
                    <p id="attachment-size" class="text-xs text-slate-500">0 KB</p>
                </div>
            </div>
            <button type="button" id="remove-attachment" class="btn btn-ghost btn-circle btn-sm text-slate-400 hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Input Area --}}
    <div class="flex-shrink-0 bg-white/90 backdrop-blur-lg border-t border-slate-200/60 px-4 lg:px-6 py-3">
        <div class="max-w-3xl mx-auto">
            <form id="message-form" enctype="multipart/form-data">
                @csrf
                {{-- Category Pills --}}
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach($categories as $key => $cat)
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="{{ $key }}" class="hidden peer" {{ $loop->first ? 'checked' : '' }}>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium border-2 border-transparent peer-checked:border-current transition-all hover:opacity-80" style="background-color: {{ $cat['color'] }}12; color: {{ $cat['color'] }}">
                            {{ $cat['icon'] }} {{ $cat['label'] }}
                        </span>
                    </label>
                    @endforeach
                </div>

                {{-- Input Row --}}
                <div class="flex items-end gap-2">
                    {{-- Attachment Button --}}
                    <label class="btn btn-ghost btn-circle btn-sm hover:bg-slate-100 cursor-pointer flex-shrink-0">
                        <input type="file" id="file-input" name="file" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    </label>

                    {{-- Text Input --}}
                    <div class="flex-1 relative">
                        <textarea name="content" id="message-content" rows="1" class="w-full px-4 py-2.5 bg-slate-100 rounded-2xl border-0 focus:ring-2 focus:ring-purple-500/20 focus:bg-white resize-none text-[15px] placeholder-slate-400 transition-all" placeholder="Type a message..." style="min-height: 44px; max-height: 120px;"></textarea>
                    </div>

                    {{-- Send Button --}}
                    <button type="submit" id="send-btn" class="btn btn-circle bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white border-0 shadow-lg shadow-purple-200/50 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>

                {{-- Hint --}}
                <div class="flex items-center justify-between mt-2 px-1">
                    <span class="text-xs text-slate-400">Press Enter to send</span>
                    <span id="char-count" class="text-xs text-slate-400">0 / 5000</span>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
#messages-container {
    overscroll-behavior: contain;
    scroll-behavior: smooth;
}
.emoji-btn:active {
    transform: scale(1.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    const form = document.getElementById('message-form');
    const contentInput = document.getElementById('message-content');
    const sendBtn = document.getElementById('send-btn');
    const connectionStatus = document.getElementById('connection-status');
    const fileInput = document.getElementById('file-input');
    const attachmentPreview = document.getElementById('attachment-preview');
    const attachmentName = document.getElementById('attachment-name');
    const attachmentSize = document.getElementById('attachment-size');
    const attachmentIcon = document.getElementById('attachment-icon');
    const removeAttachment = document.getElementById('remove-attachment');
    const charCount = document.getElementById('char-count');
    const conversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};

    // Auto-resize textarea
    contentInput.addEventListener('input', function() {
        this.style.height = '44px';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        charCount.textContent = `${this.value.length} / 5000`;
    });

    // Scroll to bottom
    function scrollToBottom() {
        container.scrollTop = container.scrollHeight;
    }
    scrollToBottom();

    // Category info
    const categories = @json($categories);

    // File handling
    const fileIcons = { 'image': '🖼️', 'pdf': '📄', 'document': '📝', 'default': '📎' };

    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return fileIcons.image;
        if (ext === 'pdf') return fileIcons.pdf;
        if (['doc', 'docx', 'txt'].includes(ext)) return fileIcons.document;
        return fileIcons.default;
    }

    function formatFileSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' bytes';
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            attachmentName.textContent = file.name;
            attachmentSize.textContent = formatFileSize(file.size);
            attachmentIcon.textContent = getFileIcon(file.name);
            attachmentPreview.classList.remove('hidden');
        }
    });

    removeAttachment.addEventListener('click', function() {
        fileInput.value = '';
        attachmentPreview.classList.add('hidden');
    });

    // Emoji reactions - using database API
    async function toggleReaction(messageId, emoji) {
        try {
            const response = await fetch(`/coparenting/messages/message/${messageId}/reaction`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ emoji }),
            });

            const data = await response.json();

            if (data.success) {
                updateReactionsDisplay(messageId, data.reactions);
            }
        } catch (error) {
            console.error('Error toggling reaction:', error);
        }
    }

    function updateReactionsDisplay(messageId, reactions) {
        const messageEl = document.querySelector(`[data-message-id="${messageId}"]`);
        if (!messageEl) return;

        const container = messageEl.querySelector('.reactions-container');
        if (!container) return;

        container.innerHTML = '';

        Object.entries(reactions).forEach(([emoji, count]) => {
            if (count > 0) {
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-sm cursor-pointer hover:bg-slate-200 transition-colors reaction-badge';
                badge.dataset.emoji = emoji;
                badge.innerHTML = `${emoji} <span class="text-xs text-slate-500">${count}</span>`;
                badge.onclick = function() {
                    toggleReaction(messageId, emoji);
                };
                container.appendChild(badge);
            }
        });
    }

    // Attach click handlers to existing reaction badges
    document.querySelectorAll('.reaction-badge').forEach(badge => {
        const messageEl = badge.closest('.message-bubble');
        const messageId = messageEl.dataset.messageId;
        const emoji = badge.dataset.emoji;
        badge.onclick = function() {
            toggleReaction(messageId, emoji);
        };
    });

    // Emoji button click handlers
    document.querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const emoji = this.dataset.emoji;
            const messageEl = this.closest('.message-bubble');
            const messageId = messageEl.dataset.messageId;

            // Animate the button
            this.style.transform = 'scale(1.5)';
            setTimeout(() => this.style.transform = '', 200);

            // Toggle the reaction via API
            toggleReaction(messageId, emoji);
        });
    });

    // Create message HTML
    function createMessageHtml(message, isOwn) {
        const catInfo = categories[message.category] || categories['General'];
        const senderInitial = message.sender_name ? message.sender_name.charAt(0).toUpperCase() : '?';

        let attachmentsHtml = '';
        if (message.attachments && message.attachments.length > 0) {
            attachmentsHtml = `<div class="mt-3 pt-3 border-t ${isOwn ? 'border-white/20' : 'border-slate-100'} space-y-2">`;
            message.attachments.forEach(att => {
                attachmentsHtml += `
                    <a href="${att.download_url}" class="flex items-center gap-3 p-2.5 rounded-xl ${isOwn ? 'bg-white/10 hover:bg-white/20' : 'bg-slate-50 hover:bg-slate-100'} transition-all">
                        <div class="w-10 h-10 rounded-lg ${isOwn ? 'bg-white/20' : 'bg-white shadow-sm'} flex items-center justify-center">
                            <span class="text-xl">${att.icon}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">${escapeHtml(att.original_filename)}</p>
                            <p class="text-xs ${isOwn ? 'text-white/70' : 'text-slate-400'}">${att.formatted_size}</p>
                        </div>
                    </a>`;
            });
            attachmentsHtml += '</div>';
        }

        return `
            <div class="flex ${isOwn ? 'justify-end' : 'justify-start'} message-bubble group/msg animate-fadeIn" data-message-id="${message.id}">
                <div class="max-w-[85%] lg:max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1 ${isOwn ? 'justify-end' : ''}">
                        ${!isOwn ? `
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-xs font-medium text-slate-600">${senderInitial}</div>
                            <span class="text-sm font-medium text-slate-600">${escapeHtml(message.sender_name)}</span>
                        ` : ''}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium" style="background-color: ${catInfo.color}15; color: ${catInfo.color}">
                            ${catInfo.icon} ${catInfo.label}
                        </span>
                    </div>
                    <div class="relative">
                        <div class="absolute ${isOwn ? 'left-0 -translate-x-full pr-2' : 'right-0 translate-x-full pl-2'} top-1/2 -translate-y-1/2 opacity-0 group-hover/msg:opacity-100 transition-all z-10">
                            <div class="flex items-center gap-0.5 bg-white rounded-full shadow-lg border border-slate-100 p-1">
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="👍">👍</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="❤️">❤️</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="😊">😊</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="🙏">🙏</button>
                                <button type="button" class="emoji-btn w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-full transition-all hover:scale-110" data-emoji="✅">✅</button>
                            </div>
                        </div>
                        <div class="rounded-2xl px-4 py-2.5 shadow-sm ${isOwn ? 'bg-gradient-to-br from-purple-600 to-violet-600 text-white rounded-br-md' : 'bg-white text-slate-800 rounded-bl-md border border-slate-100'}">
                            <p class="whitespace-pre-wrap break-words text-[15px] leading-relaxed">${escapeHtml(message.content)}</p>
                            ${attachmentsHtml}
                        </div>
                        <div class="flex items-center gap-2 mt-1 px-1 ${isOwn ? 'justify-end' : ''}">
                            <span class="text-xs text-slate-400">${message.created_at}</span>
                        </div>
                        <div class="reactions-container flex items-center gap-1 mt-1 ${isOwn ? 'justify-end' : ''} px-1"></div>
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addMessage(message, isOwn) {
        if (container.querySelector(`[data-message-id="${message.id}"]`)) return;

        const messagesWrapper = container.querySelector('.max-w-3xl');
        messagesWrapper.insertAdjacentHTML('beforeend', createMessageHtml(message, isOwn));
        scrollToBottom();

        // Re-attach emoji handlers for new message
        const newMsg = messagesWrapper.querySelector(`[data-message-id="${message.id}"]`);
        if (newMsg) {
            newMsg.querySelectorAll('.emoji-btn').forEach(btn => {
                btn.onclick = function() {
                    const emoji = this.dataset.emoji;
                    this.style.transform = 'scale(1.5)';
                    setTimeout(() => this.style.transform = '', 200);
                    toggleReaction(message.id, emoji);
                };
            });
            // Also attach handlers to any reaction badges
            newMsg.querySelectorAll('.reaction-badge').forEach(badge => {
                const emoji = badge.dataset.emoji;
                badge.onclick = function() {
                    toggleReaction(message.id, emoji);
                };
            });
        }

        if (!isOwn) {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Onraxu8DGysnJxse+sJ2QhHdqY2Zve46epbK9xtPY3N7a1MrBs6SRfm9jXV5iaXeJm6m2wcvT2tzc2tXNwrSjkH5vYl1dYGd0hZiktL/K1Nvc3drVzcKzo5B+b2JdXWBnd4WZpLS/ytTb3N3a1c3CtKOQfm9iXV1gZ3eFmKS0v8rU29zd2tXNwrSjkH5vYl1dYGd3hZiktL/K1Nvc3drVzcK0o5B+b2JdXWBnd4WYpLS/ytTb3N3a1c3CtKOQfm9iXV1gZ3eFmKS0v8rU29zd2tXNwrSjkH5vYl1dYGd3');
                audio.volume = 0.3;
                audio.play().catch(() => {});
            } catch(e) {}
        }
    }

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const content = contentInput.value.trim();
        const hasFile = fileInput.files.length > 0;

        if (!content && !hasFile) return;

        const category = form.querySelector('input[name="category"]:checked').value;
        const originalBtnHtml = sendBtn.innerHTML;

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';

        try {
            if (hasFile) {
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('category', category);
                formData.append('content', content || 'Shared a file');
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch('{{ route("coparenting.messages.uploadAttachment", $conversation) }}', {
                    method: 'POST',
                    body: formData,
                });

                if (response.ok) {
                    window.location.reload();
                    return;
                }
            } else {
                const response = await fetch('{{ route("coparenting.messages.storeMessage", $conversation) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ content, category }),
                });

                const data = await response.json();

                if (data.success) {
                    addMessage({
                        id: data.message.id,
                        sender_id: currentUserId,
                        sender_name: '{{ auth()->user()->name }}',
                        category: category,
                        content: content,
                        created_at: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
                        attachments: [],
                    }, true);

                    contentInput.value = '';
                    contentInput.style.height = '44px';
                    charCount.textContent = '0 / 5000';
                }
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnHtml;
            fileInput.value = '';
            attachmentPreview.classList.add('hidden');
        }
    });

    // Real-time messaging
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private(`coparent.conversation.${conversationId}`)
            .listen('.message.sent', (data) => {
                addMessage(data, data.sender_id === currentUserId);
            })
            .subscribed(() => {
                connectionStatus.innerHTML = '<span class="relative flex h-2 w-2"><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>Live';
                connectionStatus.className = 'px-2.5 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 bg-emerald-50 text-emerald-600';
            })
            .error((error) => {
                connectionStatus.innerHTML = '<span class="relative flex h-2 w-2"><span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span></span>Offline';
                connectionStatus.className = 'px-2.5 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 bg-red-50 text-red-600';
            });
    } else {
        connectionStatus.innerHTML = '<span class="relative flex h-2 w-2"><span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span></span>Polling';
        connectionStatus.className = 'px-2.5 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 bg-amber-50 text-amber-600';
    }

    // Enter to send
    contentInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
});
</script>
@endsection
