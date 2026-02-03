<div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow" x-data="{ menuOpen: false, deleteModalOpen: false }">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <!-- Type Icon -->
                @php
                    $typeColors = [
                        'journal' => 'primary',
                        'memory' => 'pink-500',
                        'note' => 'amber-500',
                        'milestone' => 'success'
                    ];
                    $color = $typeColors[$entry->type] ?? 'primary';
                @endphp
                <div class="w-10 h-10 rounded-xl bg-{{ $color }}/10 flex items-center justify-center shrink-0">
                    @if($entry->type === 'journal')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    @elseif($entry->type === 'memory')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    @elseif($entry->type === 'note')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                    @elseif($entry->type === 'milestone')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <!-- Title or Excerpt -->
                    <a href="{{ route('journal.show', $entry) }}" class="font-semibold text-slate-800 hover:text-primary line-clamp-1">
                        {{ $entry->title ?: Str::limit(strip_tags($entry->body), 50) }}
                    </a>

                    <!-- Date & Mood -->
                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                        <span>{{ $entry->entry_datetime->format('M j, Y') }}</span>
                        @if($entry->mood)
                            <span class="text-base" title="{{ $entry->mood_info['label'] }}">{{ $entry->mood_emoji }}</span>
                        @endif
                        @if($entry->is_draft)
                            <span class="badge badge-warning badge-xs">Draft</span>
                        @endif
                        @if($entry->is_private)
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400" title="Private"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1 shrink-0">
                @if($isPinned)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" class="text-amber-500"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2z"/></svg>
                @endif
                <div class="relative">
                    <button @click="menuOpen = !menuOpen" class="btn btn-ghost btn-xs btn-square">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak
                         class="absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded-lg shadow-lg z-50 py-1">
                        <a href="{{ route('journal.show', $entry) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </a>
                        <a href="{{ route('journal.edit', $entry) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('journal.toggle-pin', $entry) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 w-full text-left">
                                @if($entry->is_pinned)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 2 20 20"/><path d="M9 9v1.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V17h12"/><path d="M12 17v5"/><path d="M15 9.34V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1"/><path d="M19 15.24A2 2 0 0 0 17.89 13.45l-1.78-.9A2 2 0 0 1 15 10.76V8"/></svg>
                                    Unpin
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2z"/></svg>
                                    Pin
                                @endif
                            </button>
                        </form>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button type="button" @click="deleteModalOpen = true; menuOpen = false" class="flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 w-full text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="deleteModalOpen = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Delete Entry</h3>
                                <p class="text-sm text-slate-500">This action cannot be undone</p>
                            </div>
                        </div>
                        <p class="text-slate-600 mb-6">Are you sure you want to delete "<span class="font-medium">{{ $entry->title ?: Str::limit(strip_tags($entry->body), 30) }}</span>"?</p>
                        <div class="flex gap-3">
                            <button type="button" @click="deleteModalOpen = false" class="flex-1 btn btn-ghost">Cancel</button>
                            <form method="POST" action="{{ route('journal.destroy', $entry) }}" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full btn btn-error gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body Preview -->
        @if($entry->title)
            <p class="text-sm text-slate-600 mt-2 line-clamp-2">{{ $entry->excerpt }}</p>
        @endif

        <!-- Attachments Preview -->
        @if($entry->attachments->count())
            <div class="flex gap-2 mt-3">
                @foreach($entry->attachments->take(4) as $attachment)
                    @if($attachment->isPhoto())
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-100">
                            <img src="{{ $attachment->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        </div>
                    @endif
                @endforeach
                @if($entry->attachments->count() > 4)
                    <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-xs text-slate-500 font-medium">
                        +{{ $entry->attachments->count() - 4 }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tags -->
        @if($entry->tags->count())
            <div class="flex flex-wrap gap-1 mt-3">
                @foreach($entry->tags->take(5) as $tag)
                    <a href="{{ route('journal.index', ['tag' => $tag->id]) }}"
                       class="badge badge-sm gap-1 hover:badge-primary"
                       style="background-color: {{ $tag->color_hex }}20; color: {{ $tag->color_hex }}">
                        {{ $tag->name }}
                    </a>
                @endforeach
                @if($entry->tags->count() > 5)
                    <span class="badge badge-sm badge-ghost">+{{ $entry->tags->count() - 5 }}</span>
                @endif
            </div>
        @endif
    </div>
</div>
