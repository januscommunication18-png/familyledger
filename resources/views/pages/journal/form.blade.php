@extends('layouts.dashboard')

@section('title', $entry ? 'Edit Entry' : 'New Entry')
@section('page-name', 'Journal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('journal.index') }}">Journal</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">{{ $entry ? 'Edit' : 'New Entry' }}</li>
@endsection

@section('page-title', $entry ? 'Edit Entry' : 'New Journal Entry')
@section('page-description', $entry ? 'Update your entry' : 'Capture a thought, memory, or milestone')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ $entry ? route('journal.update', $entry) : route('journal.store') }}"
          enctype="multipart/form-data" x-data="journalForm()">
        @csrf
        @if($entry)
            @method('PUT')
        @endif

        @if($errors->any())
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <!-- Entry Type Tabs -->
                <div class="flex flex-wrap gap-2 mb-4" x-data="{ selectedType: '{{ old('type', $entry?->type ?? $defaultType) }}' }">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="journal" class="hidden" x-model="selectedType">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border-2 transition-all hover:bg-base-200"
                              :class="selectedType === 'journal' ? 'border-primary bg-primary/10 text-primary' : 'border-transparent'">
                            <span class="icon-[tabler--notebook] size-4"></span>
                            Journal Entry
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="memory" class="hidden" x-model="selectedType">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border-2 transition-all hover:bg-base-200"
                              :class="selectedType === 'memory' ? 'border-pink-500 bg-pink-500/10 text-pink-500' : 'border-transparent'">
                            <span class="icon-[tabler--heart] size-4"></span>
                            Memory
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="note" class="hidden" x-model="selectedType">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border-2 transition-all hover:bg-base-200"
                              :class="selectedType === 'note' ? 'border-amber-500 bg-amber-500/10 text-amber-500' : 'border-transparent'">
                            <span class="icon-[tabler--note] size-4"></span>
                            Quick Note
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="milestone" class="hidden" x-model="selectedType">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border-2 transition-all hover:bg-base-200"
                              :class="selectedType === 'milestone' ? 'border-success bg-success/10 text-success' : 'border-transparent'">
                            <span class="icon-[tabler--trophy] size-4"></span>
                            Milestone
                        </span>
                    </label>
                </div>

                <!-- Title (Optional) -->
                <div class="form-control mb-4">
                    <input type="text" name="title" value="{{ old('title', $entry?->title) }}"
                           class="input input-bordered input-lg font-semibold"
                           placeholder="Title (optional)">
                </div>

                <!-- Body -->
                <div class="form-control mb-4">
                    <x-quill-editor
                        name="body"
                        :value="old('body', $entry?->body ?? '')"
                        placeholder="What's on your mind?"
                        height="300px"
                        toolbar="standard"
                    />
                </div>

                <!-- Date & Time -->
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <div class="form-control">
                        <label class="label label-text text-xs">Date & Time</label>
                        <input type="datetime-local" name="entry_datetime"
                               value="{{ old('entry_datetime', ($entry?->entry_datetime ?? now())->format('Y-m-d\TH:i')) }}"
                               class="input input-bordered input-sm">
                    </div>

                    <!-- Mood Selector -->
                    <div class="form-control">
                        <label class="label label-text text-xs">Mood</label>
                        <div class="flex gap-1">
                            @foreach($moods as $key => $mood)
                                <label class="cursor-pointer">
                                    <input type="radio" name="mood" value="{{ $key }}" class="hidden peer"
                                           {{ old('mood', $entry?->mood) === $key ? 'checked' : '' }}>
                                    <span class="flex items-center justify-center w-10 h-10 text-2xl rounded-xl
                                                 border-2 border-transparent transition-all hover:bg-base-200
                                                 peer-checked:border-primary peer-checked:bg-primary/10"
                                          title="{{ $mood['label'] }}">
                                        {{ $mood['emoji'] }}
                                    </span>
                                </label>
                            @endforeach
                            <label class="cursor-pointer">
                                <input type="radio" name="mood" value="" class="hidden peer"
                                       {{ old('mood', $entry?->mood) === null ? 'checked' : '' }}>
                                <span class="flex items-center justify-center w-10 h-10 text-xs text-slate-400 rounded-xl
                                             border-2 border-transparent transition-all hover:bg-base-200
                                             peer-checked:border-primary peer-checked:bg-primary/10">
                                    None
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="form-control mb-4" x-data="tagSelector()">
                    <label class="label label-text text-xs">Tags</label>

                    <!-- Selected Tags -->
                    <div class="flex flex-wrap gap-2 mb-2" x-show="selectedTags.length > 0">
                        <template x-for="tag in selectedTags" :key="tag.id">
                            <span class="badge badge-lg gap-1" :style="{ backgroundColor: tag.color + '20', color: tag.color }">
                                <span x-text="tag.name"></span>
                                <button type="button" @click="removeTag(tag.id)" class="hover:opacity-70">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>

                    <!-- Tag Input -->
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" x-model="newTagInput" @keydown.enter.prevent="addNewTag"
                                   @input="searchTags" @focus="showSuggestions = true" @blur="hideSuggestions"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="Add tags (press Enter to create)">

                            <!-- Suggestions Dropdown -->
                            <div x-show="showSuggestions && suggestions.length > 0" x-cloak
                                 class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                                <template x-for="tag in suggestions" :key="tag.id">
                                    <div @mousedown="selectTag(tag)"
                                         class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-base-200">
                                        <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                                        <span x-text="tag.name"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs for form submission (only existing tags with positive IDs) -->
                    <template x-for="tag in selectedTags.filter(t => t.id > 0)" :key="'input-' + tag.id">
                        <input type="hidden" name="tags[]" :value="tag.id">
                    </template>
                    <input type="hidden" name="new_tags" :value="newTags.join(',')">
                </div>

                <!-- Photo Upload -->
                <div class="form-control mb-4" x-data="{ photoNames: [] }">
                    <label class="label label-text text-xs flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        Photos (up to 5)
                    </label>

                    <!-- Existing Attachments -->
                    @if($entry && $entry->attachments->where('type', 'photo')->count())
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($entry->attachments->where('type', 'photo') as $attachment)
                                <div class="relative group">
                                    <img src="{{ $attachment->thumbnail_url }}" alt=""
                                         class="w-20 h-20 rounded-xl object-cover">
                                    <label class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                        <input type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}"
                                               class="checkbox checkbox-sm checkbox-error">
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mb-2">Check photos to remove them</p>
                    @endif

                    <!-- Selected Photos Preview -->
                    <div x-show="photoNames.length > 0" x-cloak class="flex flex-wrap gap-2 mb-3">
                        <template x-for="(name, index) in photoNames" :key="index">
                            <div class="flex items-center gap-2 px-3 py-2 bg-primary/10 text-primary rounded-lg text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                <span x-text="name" class="max-w-[200px] truncate"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Upload Area -->
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-base-300 rounded-xl cursor-pointer bg-base-200/50 hover:bg-base-200 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            <p class="mb-1 text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-slate-400">JPG, PNG, GIF, WebP (Max 10MB each)</p>
                        </div>
                        <input type="file" name="photos[]" accept="image/*" multiple class="hidden"
                               @change="photoNames = Array.from($event.target.files).map(f => f.name)">
                    </label>
                </div>

                <!-- File Upload -->
                <div class="form-control mb-4" x-data="{ fileName: '' }">
                    <label class="label label-text text-xs flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        File Attachment (1 max)
                    </label>

                    @if($entry && $entry->attachments->where('type', 'file')->count())
                        <div class="flex items-center gap-2 mb-2 p-2 bg-base-200 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                            <span class="text-sm flex-1">{{ $entry->attachments->where('type', 'file')->first()->file_name }}</span>
                            <label class="cursor-pointer text-error text-xs flex items-center gap-1">
                                <input type="checkbox" name="remove_attachments[]"
                                       value="{{ $entry->attachments->where('type', 'file')->first()->id }}"
                                       class="checkbox checkbox-xs checkbox-error">
                                Remove
                            </label>
                        </div>
                    @endif

                    <!-- Selected File Preview -->
                    <div x-show="fileName" x-cloak class="flex items-center gap-2 mb-3 px-3 py-2 bg-primary/10 text-primary rounded-lg text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        <span x-text="fileName" class="flex-1 truncate"></span>
                    </div>

                    <!-- Upload Area -->
                    <label class="flex items-center gap-3 w-full p-3 border-2 border-dashed border-base-300 rounded-xl cursor-pointer bg-base-200/50 hover:bg-base-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <div>
                            <p class="text-sm text-slate-500"><span class="font-semibold">Click to upload</span> a file</p>
                            <p class="text-xs text-slate-400">PDF, Word, Excel, TXT (Max 25MB)</p>
                        </div>
                        <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" class="hidden"
                               @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                </div>

                <!-- Privacy Settings -->
                <div class="border-t pt-4 mt-4">
                    <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Privacy
                    </h4>

                    <div class="flex flex-wrap gap-3 mb-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="private" class="hidden" x-model="selectedVisibility">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm border transition-all hover:bg-base-200"
                                  :class="selectedVisibility === 'private' ? 'border-primary bg-primary/10 text-primary' : 'border-base-300'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Only Me
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="family" class="hidden" x-model="selectedVisibility">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm border transition-all hover:bg-base-200"
                                  :class="selectedVisibility === 'family' ? 'border-primary bg-primary/10 text-primary' : 'border-base-300'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                Family Circle
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="specific" class="hidden" x-model="selectedVisibility">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm border transition-all hover:bg-base-200"
                                  :class="selectedVisibility === 'specific' ? 'border-primary bg-primary/10 text-primary' : 'border-base-300'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                                Specific People
                            </span>
                        </label>
                    </div>

                    <!-- Specific People Selector -->
                    <div x-show="selectedVisibility === 'specific'" x-cloak class="form-control">
                        <label class="label label-text text-xs">Share with</label>
                        <select name="shared_with[]" multiple class="select select-bordered select-sm h-24">
                            @foreach($familyMembers as $member)
                                <option value="{{ $member->linked_user_id ?? '' }}"
                                        {{ in_array($member->linked_user_id, $entry?->shared_with_user_ids ?? []) ? 'selected' : '' }}
                                        {{ !$member->linked_user_id ? 'disabled' : '' }}>
                                    {{ $member->first_name }} {{ $member->last_name }}
                                    {{ !$member->linked_user_id ? '(no account)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt">Hold Ctrl/Cmd to select multiple</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Hidden status input -->
            <input type="hidden" name="status" id="journal_status" value="published">

            <!-- Form Actions -->
            <div class="card-body pt-0">
                <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-4">
                    <a href="{{ $entry ? route('journal.show', $entry) : route('journal.index') }}"
                       class="btn btn-ghost">Cancel</a>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-outline gap-2" onclick="document.getElementById('journal_status').value='draft'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                            Save as Draft
                        </button>
                        <button type="submit" class="btn btn-primary gap-2" onclick="document.getElementById('journal_status').value='published'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            {{ $entry ? 'Update' : 'Publish' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function journalForm() {
    return {
        selectedVisibility: '{{ old('visibility', $entry?->visibility ?? 'private') }}'
    }
}

function tagSelector() {
    return {
        selectedTags: [
            @if($entry)
                @foreach($entry->tags as $tag)
                {
                    id: {{ $tag->id }},
                    name: '{{ addslashes($tag->name) }}',
                    color: '{{ $tag->color_hex }}'
                },
                @endforeach
            @endif
        ],
        newTags: [],
        newTagInput: '',
        suggestions: [],
        showSuggestions: false,
        availableTags: [
            @foreach($tags as $tag)
            {
                id: {{ $tag->id }},
                name: '{{ addslashes($tag->name) }}',
                color: '{{ $tag->color_hex }}'
            },
            @endforeach
        ],

        searchTags() {
            if (!this.newTagInput) {
                this.suggestions = this.availableTags.filter(t =>
                    !this.selectedTags.some(s => s.id === t.id)
                ).slice(0, 5);
                return;
            }

            const search = this.newTagInput.toLowerCase();
            this.suggestions = this.availableTags.filter(t =>
                t.name.toLowerCase().includes(search) &&
                !this.selectedTags.some(s => s.id === t.id)
            ).slice(0, 5);
        },

        selectTag(tag) {
            if (!this.selectedTags.some(t => t.id === tag.id)) {
                this.selectedTags.push(tag);
            }
            this.newTagInput = '';
            this.suggestions = [];
        },

        removeTag(tagId) {
            // Find the tag being removed
            const tagToRemove = this.selectedTags.find(t => t.id === tagId);

            // If it's a new tag (negative ID), remove from newTags array
            if (tagToRemove && tagId < 0) {
                this.newTags = this.newTags.filter(name => name !== tagToRemove.name);
            }

            // Remove from selectedTags
            this.selectedTags = this.selectedTags.filter(t => t.id !== tagId);
        },

        addNewTag() {
            const name = this.newTagInput.trim();
            if (!name) return;

            // Check if tag exists in suggestions
            const existing = this.availableTags.find(t => t.name.toLowerCase() === name.toLowerCase());
            if (existing) {
                this.selectTag(existing);
                return;
            }

            // Check if already added
            if (this.selectedTags.some(t => t.name.toLowerCase() === name.toLowerCase())) {
                this.newTagInput = '';
                return;
            }

            // Add as new tag
            const colors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            this.selectedTags.push({
                id: -Date.now(), // Negative ID for new tags
                name: name,
                color: randomColor
            });
            this.newTags.push(name);
            this.newTagInput = '';
        },

        hideSuggestions() {
            setTimeout(() => this.showSuggestions = false, 200);
        }
    }
}
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
