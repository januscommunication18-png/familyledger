@extends('layouts.dashboard')

@section('title', $resource->name)
@section('page-name', 'Family Resources')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li>
        <a href="{{ route('family-resources.index') }}" class="text-slate-400 hover:text-slate-600">Family Resources</a>
    </li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page" class="truncate max-w-[200px]">{{ $resource->name }}</li>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header Card -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-{{ $resource->document_type_color }}-100 flex items-center justify-center shrink-0">
                        @switch($resource->document_type)
                            @case('emergency')
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                @break
                            @case('evacuation_plan')
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><path d="M13 4h3a2 2 0 0 1 2 2v14"/><path d="M2 20h3"/><path d="M13 20h9"/><path d="M10 12v.01"/><path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.561Z"/></svg>
                                @break
                            @case('fire_extinguisher')
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600"><path d="M15 6.5V3a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5"/><path d="M9 18h6"/><path d="M18 3h-3"/><path d="M11 3a6 6 0 0 0-6 6v11"/><path d="M19 9a6 6 0 0 0-6-6"/><path d="M19 9v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1"/></svg>
                                @break
                            @case('rental_agreement')
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                @break
                            @case('home_warranty')
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                                @break
                            @default
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-600"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                        @endswitch
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $resource->name }}</h1>
                        <div class="flex items-center gap-3 mt-1 text-base-content/60">
                            <span class="badge badge-{{ $resource->status_color }}">{{ $resource->status_name }}</span>
                            <span>{{ $resource->document_type_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('family-resources.edit', $resource) }}" class="btn btn-primary btn-sm gap-2">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                    <button onclick="confirmDelete()" class="btn btn-outline btn-error btn-sm gap-2">
                        <span class="icon-[tabler--trash] size-4"></span>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Resource Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Resource Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($resource->digital_copy_date)
                        <div>
                            <p class="text-sm text-base-content/60 mb-1">Digital Copy Date</p>
                            <p class="font-medium">{{ $resource->digital_copy_date->format('F j, Y') }}</p>
                        </div>
                        @endif

                        @if($resource->document_type === 'other' && $resource->custom_document_type)
                        <div>
                            <p class="text-sm text-base-content/60 mb-1">Custom Type</p>
                            <p class="font-medium">{{ $resource->custom_document_type }}</p>
                        </div>
                        @endif

                        @if($resource->original_location)
                        <div class="md:col-span-2">
                            <p class="text-sm text-base-content/60 mb-1">Location of Original</p>
                            <p class="font-medium">{{ $resource->original_location }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Files -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Files</h2>
                        <a href="{{ route('family-resources.edit', $resource) }}#files" class="btn btn-ghost btn-sm gap-1">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Files
                        </a>
                    </div>

                    @if($resource->files->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($resource->files as $file)
                            <div class="card bg-base-200/50 hover:bg-base-200 transition-colors">
                                <div class="card-body p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-base-100 flex items-center justify-center shrink-0">
                                            @if($file->isImage())
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-teal-500"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                            @elseif($file->isPdf())
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 12h4"/><path d="M10 16h4"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-base-content/60"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate">{{ $file->original_name }}</p>
                                            <p class="text-xs text-base-content/60">{{ $file->formatted_file_size }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-1 mt-3 pt-3 border-t border-base-300">
                                        @if($file->isImage() || $file->isPdf())
                                        <a href="{{ route('family-resources.files.view', [$resource, $file]) }}" target="_blank"
                                           class="btn btn-ghost btn-sm flex-1 gap-1" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View
                                        </a>
                                        @endif
                                        <a href="{{ route('family-resources.files.download', [$resource, $file]) }}"
                                           class="btn btn-ghost btn-sm flex-1 gap-1" title="Download">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                            Download
                                        </a>
                                        <button type="button" onclick="showFileDeleteModal('{{ route('family-resources.files.destroy', [$resource, $file]) }}')"
                                           class="btn btn-ghost btn-sm text-error gap-1" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--files-off] size-12 opacity-30"></span>
                            <p class="mt-2">No files uploaded</p>
                            <a href="{{ route('family-resources.edit', $resource) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                                <span class="icon-[tabler--upload] size-4"></span>
                                Upload Files
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($resource->notes)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Notes</h2>
                    <div class="prose prose-sm max-w-none">
                        {!! nl2br(e($resource->notes)) !!}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <a href="{{ route('family-resources.edit', $resource) }}" class="btn btn-ghost btn-block justify-start gap-2">
                            <span class="icon-[tabler--edit] size-5 text-primary"></span>
                            Edit Resource
                        </a>
                        <a href="{{ route('family-resources.create', ['type' => $resource->document_type]) }}" class="btn btn-ghost btn-block justify-start gap-2">
                            <span class="icon-[tabler--plus] size-5 text-success"></span>
                            Add Similar Resource
                        </a>
                        <button onclick="confirmDelete()" class="btn btn-ghost btn-block justify-start gap-2 text-error">
                            <span class="icon-[tabler--trash] size-5"></span>
                            Delete Resource
                        </button>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Information</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Type</span>
                            <span>{{ $resource->document_type_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Status</span>
                            <span class="badge badge-{{ $resource->status_color }} badge-sm">{{ $resource->status_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span>{{ $resource->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Updated</span>
                            <span>{{ $resource->updated_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Added by</span>
                            <span>{{ $resource->creator->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Files</span>
                            <span>{{ $resource->files->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Resource Modal -->
<div id="deleteResourceModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteResourceModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete Resource?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete "<strong>{{ $resource->name }}</strong>"? This action cannot be undone and all associated files will be permanently deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideDeleteResourceModal()" class="btn btn-ghost">Cancel</button>
                <form action="{{ route('family-resources.destroy', $resource) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete File Modal -->
<div id="deleteFileModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideFileDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete File?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete this file? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideFileDeleteModal()" class="btn btn-ghost">Cancel</button>
                <form id="deleteFileForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    document.getElementById('deleteResourceModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteResourceModal() {
    document.getElementById('deleteResourceModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function showFileDeleteModal(url) {
    document.getElementById('deleteFileForm').action = url;
    document.getElementById('deleteFileModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideFileDeleteModal() {
    document.getElementById('deleteFileModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteResourceModal();
        hideFileDeleteModal();
    }
});
</script>
@endsection
