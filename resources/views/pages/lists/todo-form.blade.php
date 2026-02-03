@extends('layouts.dashboard')

@section('title', $item ? 'Edit Task' : 'Add Task')
@section('page-name', 'Lists')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('lists.index', ['tab' => 'todos']) }}" class="hover:text-primary">Lists</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $item ? 'Edit Task' : 'Add Task' }}</li>
@endsection

@push('styles')
<style>
    .ql-container {
        min-height: 120px;
        font-size: 14px;
    }
    .ql-editor {
        min-height: 100px;
    }
    .ql-toolbar.ql-snow {
        border-radius: 0.5rem 0.5rem 0 0;
        border-color: oklch(var(--bc) / 0.2);
    }
    .ql-container.ql-snow {
        border-radius: 0 0 0.5rem 0.5rem;
        border-color: oklch(var(--bc) / 0.2);
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('lists.index', ['tab' => 'todos', 'todo_list' => $todoList->id]) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $item ? 'Edit Task' : 'Add Task' }}</h1>
                <p class="text-slate-500">{{ $todoList->name }}</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ $item ? route('lists.todos.items.update', $item) : route('lists.todos.items.store') }}"
          method="POST"
          class="space-y-6">
        @csrf
        @if($item)
            @method('PUT')
        @endif
        <input type="hidden" name="todo_list_id" value="{{ $todoList->id }}">
        <input type="hidden" name="description" id="descriptionInput" value="{{ old('description', $item?->description) }}">

        <!-- Task Details -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Task Details</h2>
                        <p class="text-xs text-slate-400">What needs to be done?</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Task Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $item?->title) }}" required
                            class="input input-bordered w-full"
                            placeholder="e.g., Schedule doctor appointment, Pay electricity bill">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <div id="descriptionEditor"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select name="category" required class="select select-bordered w-full">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" {{ old('category', $item?->category ?? 'personal') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                            <select name="priority" class="select select-bordered w-full">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', $item?->priority ?? 'medium') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment & Schedule -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Assignment & Schedule</h2>
                        <p class="text-xs text-slate-400">Who and when</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Assign To</label>
                        @if($familyMembers->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @php
                                    $selectedAssignees = old('assignees', $item?->assignees?->pluck('id')->toArray() ?? []);
                                @endphp
                                @foreach($familyMembers as $member)
                                    <label class="flex items-center gap-3 p-2 rounded-lg border border-base-300 hover:bg-base-200 cursor-pointer transition-colors {{ in_array($member->id, $selectedAssignees) ? 'bg-primary/10 border-primary' : '' }}">
                                        <input type="checkbox" name="assignees[]" value="{{ $member->id }}"
                                            class="checkbox checkbox-sm checkbox-primary"
                                            {{ in_array($member->id, $selectedAssignees) ? 'checked' : '' }}>
                                        <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-violet-400 to-purple-500">
                                            @if($member->profile_image_url)
                                                <img src="{{ $member->profile_image_url }}" alt="{{ $member->first_name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium">{{ $member->first_name }} {{ $member->last_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-500">No family members added yet.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                            <div class="relative">
                                <input type="text" name="due_date" id="dueDatePicker"
                                    value="{{ old('due_date', $item?->due_date?->format('Y-m-d')) }}"
                                    class="input input-bordered w-full pl-10"
                                    placeholder="Select date">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                </span>
                            </div>
                        </div>

                        @if($item)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <select name="status" class="select select-bordered w-full">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $item->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('lists.index', ['tab' => 'todos', 'todo_list' => $todoList->id]) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $item ? 'Update Task' : 'Add Task' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
@vite('resources/js/vendor/quill.js')
<script>
    // Initialize Quill editor
    const quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        placeholder: 'Additional details or instructions...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    // Set initial content
    const initialContent = document.getElementById('descriptionInput').value;
    if (initialContent) {
        quill.root.innerHTML = initialContent;
    }

    // Update hidden input on form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('descriptionInput').value = quill.root.innerHTML;
    });

    // Initialize Flatpickr datepicker
    flatpickr('#dueDatePicker', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        disableMobile: true,
        monthSelectorType: 'static'
    });
</script>
@endpush
@endsection
