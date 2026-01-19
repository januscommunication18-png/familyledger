@extends('layouts.dashboard')

@section('title', $entry->title ?: 'Journal Entry')
@section('page-name', 'Journal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('journal.index') }}">Journal</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ Str::limit($entry->title ?: 'Entry', 20) }}</li>
@endsection

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('journal.index') }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back
        </a>
        <form method="POST" action="{{ route('journal.toggle-pin', $entry) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-ghost btn-sm gap-2" title="{{ $entry->is_pinned ? 'Unpin' : 'Pin' }}">
                @if($entry->is_pinned)
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" class="text-amber-500"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2z"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2z"/></svg>
                @endif
            </button>
        </form>
        <a href="{{ route('journal.edit', $entry) }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            Edit
        </a>
        <form method="POST" action="{{ route('journal.destroy', $entry) }}" class="inline"
              onsubmit="return confirm('Are you sure you want to delete this entry?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-sm text-error gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Entry Card -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    @php
                        $typeColors = [
                            'journal' => 'primary',
                            'memory' => 'pink-500',
                            'note' => 'amber-500',
                            'milestone' => 'success'
                        ];
                        $color = $typeColors[$entry->type] ?? 'primary';
                    @endphp
                    <div class="w-12 h-12 rounded-xl bg-{{ $color }}/10 flex items-center justify-center">
                        @if($entry->type === 'journal')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        @elseif($entry->type === 'memory')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        @elseif($entry->type === 'note')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                        @elseif($entry->type === 'milestone')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-{{ $color }}"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-{{ $color }}">{{ $entry->type_info['label'] }}</span>
                            @if($entry->is_draft)
                                <span class="badge badge-warning badge-sm">Draft</span>
                            @endif
                            @if($entry->is_pinned)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" class="text-amber-500"><path d="M9 4v6l-2 4v2h6v6l1 1 1-1v-6h6v-2l-2-4V4a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <span>{{ $entry->entry_datetime->format('l, F j, Y') }}</span>
                            <span>at</span>
                            <span>{{ $entry->entry_datetime->format('g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($entry->mood)
                        <span class="text-3xl" title="{{ $entry->mood_info['label'] }}">{{ $entry->mood_emoji }}</span>
                    @endif
                    @if($entry->visibility === 'private')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400" title="{{ $entry->visibility_info['label'] }}"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    @elseif($entry->visibility === 'family')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400" title="{{ $entry->visibility_info['label'] }}"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400" title="{{ $entry->visibility_info['label'] }}"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                    @endif
                </div>
            </div>

            <!-- Title -->
            @if($entry->title)
                <h1 class="text-2xl font-bold text-slate-800 mb-4">{{ $entry->title }}</h1>
            @endif

            <!-- Body -->
            <div class="prose prose-slate max-w-none mb-6">
                {!! $entry->body !!}
            </div>

            <!-- Photos Gallery -->
            @if($entry->attachments->where('type', 'photo')->count())
                <div class="mb-6" x-data="imageGallery()">
                    <h3 class="text-sm font-semibold text-slate-600 mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        Photos
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($entry->attachments->where('type', 'photo') as $index => $photo)
                            <button type="button" @click="openModal('{{ $photo->url }}', {{ $index }})"
                               class="aspect-square rounded-xl overflow-hidden bg-slate-100 hover:opacity-90 transition-opacity cursor-pointer">
                                <img src="{{ $photo->thumbnail_url }}" alt=""
                                     class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    <!-- Image Modal -->
                    <div x-show="isOpen" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center"
                         @keydown.escape.window="closeModal()"
                         @keydown.arrow-left.window="prevImage()"
                         @keydown.arrow-right.window="nextImage()">
                        <!-- Backdrop -->
                        <div class="absolute inset-0 bg-black/90" @click="closeModal()"></div>

                        <!-- Modal Content -->
                        <div class="relative z-10 max-w-5xl max-h-[90vh] w-full mx-4">
                            <!-- Close Button -->
                            <button @click="closeModal()" class="absolute -top-12 right-0 text-white hover:text-slate-300 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>

                            <!-- Image -->
                            <img :src="currentImage" alt="" class="max-w-full max-h-[85vh] mx-auto rounded-lg shadow-2xl object-contain">

                            <!-- Navigation Arrows -->
                            @if($entry->attachments->where('type', 'photo')->count() > 1)
                                <button @click="prevImage()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-16 p-2 text-white hover:text-slate-300 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <button @click="nextImage()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-16 p-2 text-white hover:text-slate-300 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            @endif

                            <!-- Image Counter -->
                            <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 text-white text-sm">
                                <span x-text="currentIndex + 1"></span> / <span>{{ $entry->attachments->where('type', 'photo')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                function imageGallery() {
                    return {
                        isOpen: false,
                        currentImage: '',
                        currentIndex: 0,
                        images: [
                            @foreach($entry->attachments->where('type', 'photo') as $photo)
                                '{{ $photo->url }}',
                            @endforeach
                        ],
                        openModal(url, index) {
                            this.currentImage = url;
                            this.currentIndex = index;
                            this.isOpen = true;
                            document.body.style.overflow = 'hidden';
                        },
                        closeModal() {
                            this.isOpen = false;
                            document.body.style.overflow = '';
                        },
                        nextImage() {
                            if (!this.isOpen) return;
                            this.currentIndex = (this.currentIndex + 1) % this.images.length;
                            this.currentImage = this.images[this.currentIndex];
                        },
                        prevImage() {
                            if (!this.isOpen) return;
                            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                            this.currentImage = this.images[this.currentIndex];
                        }
                    }
                }
                </script>
                @endpush
            @endif

            <!-- File Attachments -->
            @if($entry->attachments->where('type', 'file')->count())
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-600 mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        Attachments
                    </h3>
                    @foreach($entry->attachments->where('type', 'file') as $file)
                        <a href="{{ $file->url }}" download="{{ $file->file_name }}"
                           class="flex items-center gap-3 p-3 bg-base-200 rounded-xl hover:bg-base-300 transition-colors mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $file->file_name }}</div>
                                <div class="text-xs text-slate-500">{{ $file->file_size_formatted }}</div>
                            </div>
                            <span class="text-xs text-primary font-medium">Download</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Tags -->
            @if($entry->tags->count())
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($entry->tags as $tag)
                        <a href="{{ route('journal.index', ['tag' => $tag->id]) }}"
                           class="badge badge-lg gap-1 hover:opacity-80 transition-opacity"
                           style="background-color: {{ $tag->color_hex }}20; color: {{ $tag->color_hex }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Meta Footer -->
            <div class="border-t pt-4 text-xs text-slate-400">
                <div class="flex items-center justify-between">
                    <div>
                        Created {{ $entry->created_at->diffForHumans() }}
                        @if($entry->updated_at->gt($entry->created_at))
                            &middot; Updated {{ $entry->updated_at->diffForHumans() }}
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        @if($entry->visibility === 'private')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @elseif($entry->visibility === 'family')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                        @endif
                        {{ $entry->visibility_info['label'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body p-4">
            <div class="flex items-center justify-center gap-3">
                @if($previousEntry)
                    <a href="{{ route('journal.show', $previousEntry) }}" class="btn btn-outline btn-sm gap-2 flex-1 max-w-[200px] justify-start">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        <span class="truncate">Previous</span>
                    </a>
                @endif

                <a href="{{ route('journal.index') }}" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    All Entries
                </a>

                @if($nextEntry)
                    <a href="{{ route('journal.show', $nextEntry) }}" class="btn btn-outline btn-sm gap-2 flex-1 max-w-[200px] justify-end">
                        <span class="truncate">Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
