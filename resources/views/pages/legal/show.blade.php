@extends('layouts.dashboard')

@section('title', $document->name)
@section('page-name', 'Legal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li>
        <a href="{{ route('legal.index') }}" class="text-slate-400 hover:text-slate-600">Legal</a>
    </li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page" class="truncate max-w-[200px]">{{ $document->name }}</li>
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
                    <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                        <span class="{{ $document->document_type_icon }} size-7 text-primary"></span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $document->name }}</h1>
                        <div class="flex items-center gap-3 mt-1 text-base-content/60">
                            <span class="badge badge-{{ $document->status_color }}">{{ $document->status_name }}</span>
                            <span>{{ $document->document_type_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('legal.edit', $document) }}" class="btn btn-primary btn-sm gap-2">
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
            <!-- Document Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Document Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($document->execution_date)
                        <div>
                            <p class="text-sm text-base-content/60 mb-1">Execution Date</p>
                            <p class="font-medium">{{ $document->execution_date->format('F j, Y') }}</p>
                        </div>
                        @endif

                        @if($document->expiration_date)
                        <div>
                            <p class="text-sm text-base-content/60 mb-1">Expiration Date</p>
                            <p class="font-medium {{ $document->isExpired() ? 'text-error' : ($document->isExpiringSoon() ? 'text-warning' : '') }}">
                                {{ $document->expiration_date->format('F j, Y') }}
                                @if($document->isExpired())
                                    <span class="badge badge-error badge-sm ml-2">Expired</span>
                                @elseif($document->isExpiringSoon())
                                    <span class="badge badge-warning badge-sm ml-2">Expiring Soon</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        @if($document->digital_copy_date)
                        <div>
                            <p class="text-sm text-base-content/60 mb-1">Digital Copy Date</p>
                            <p class="font-medium">{{ $document->digital_copy_date->format('F j, Y') }}</p>
                        </div>
                        @endif

                        @if($document->original_location)
                        <div class="md:col-span-2">
                            <p class="text-sm text-base-content/60 mb-1">Location of Original</p>
                            <p class="font-medium">{{ $document->original_location }}</p>
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
                        <a href="{{ route('legal.edit', $document) }}#files" class="btn btn-ghost btn-sm gap-1">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Files
                        </a>
                    </div>

                    @if($document->files->count() > 0)
                        <div class="space-y-2">
                            @foreach($document->files as $file)
                            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-base-100 flex items-center justify-center">
                                    @if($file->isImage())
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-base-content/60"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    @elseif($file->isPdf())
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 12h4"/><path d="M10 16h4"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-base-content/60"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $file->original_name }}</p>
                                    <p class="text-xs text-base-content/60">{{ $file->formatted_file_size }}</p>
                                </div>
                                <div class="flex gap-1">
                                    @if($file->isImage() || $file->isPdf())
                                    <a href="{{ route('legal.files.view', [$document, $file]) }}" target="_blank"
                                       class="btn btn-ghost btn-sm btn-square" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @endif
                                    <a href="{{ route('legal.files.download', [$document, $file]) }}"
                                       class="btn btn-ghost btn-sm btn-square" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    </a>
                                    <button type="button" onclick="showFileDeleteModal('{{ route('legal.files.destroy', [$document, $file]) }}')"
                                       class="btn btn-ghost btn-sm btn-square text-error" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--files-off] size-12 opacity-30"></span>
                            <p class="mt-2">No files uploaded</p>
                            <a href="{{ route('legal.edit', $document) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                                <span class="icon-[tabler--upload] size-4"></span>
                                Upload Files
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($document->notes)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Notes</h2>
                    <div class="prose prose-sm max-w-none">
                        {!! nl2br(e($document->notes)) !!}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Attorney Information -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Attorney</h2>

                    @if($document->attorney || $document->attorney_name)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--gavel] size-5 text-amber-600"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($document->attorney)
                                    <a href="{{ route('people.show', $document->attorney) }}" class="font-medium hover:text-primary">
                                        {{ $document->attorney->full_name }}
                                    </a>
                                    @if($document->attorney->company)
                                        <p class="text-sm text-base-content/60">{{ $document->attorney->company }}</p>
                                    @endif
                                @else
                                    <p class="font-medium">{{ $document->attorney_name }}</p>
                                    @if($document->attorney_firm)
                                        <p class="text-sm text-base-content/60">{{ $document->attorney_firm }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @if($document->attorney)
                                @if($document->attorney->primary_phone)
                                <a href="tel:{{ $document->attorney->primary_phone->phone }}" class="flex items-center gap-2 text-sm hover:text-primary">
                                    <span class="icon-[tabler--phone] size-4 text-base-content/60"></span>
                                    {{ $document->attorney->primary_phone->phone }}
                                </a>
                                @endif
                                @if($document->attorney->primary_email)
                                <a href="mailto:{{ $document->attorney->primary_email->email }}" class="flex items-center gap-2 text-sm hover:text-primary">
                                    <span class="icon-[tabler--mail] size-4 text-base-content/60"></span>
                                    {{ $document->attorney->primary_email->email }}
                                </a>
                                @endif
                            @else
                                @if($document->attorney_phone)
                                <a href="tel:{{ $document->attorney_phone }}" class="flex items-center gap-2 text-sm hover:text-primary">
                                    <span class="icon-[tabler--phone] size-4 text-base-content/60"></span>
                                    {{ $document->attorney_phone }}
                                </a>
                                @endif
                                @if($document->attorney_email)
                                <a href="mailto:{{ $document->attorney_email }}" class="flex items-center gap-2 text-sm hover:text-primary">
                                    <span class="icon-[tabler--mail] size-4 text-base-content/60"></span>
                                    {{ $document->attorney_email }}
                                </a>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 text-base-content/60">
                            <span class="icon-[tabler--user-off] size-8 opacity-30"></span>
                            <p class="mt-2 text-sm">No attorney information</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Information</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span>{{ $document->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Updated</span>
                            <span>{{ $document->updated_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Added by</span>
                            <span>{{ $document->creator->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Files</span>
                            <span>{{ $document->files->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Document Modal -->
<div id="deleteDocumentModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteDocumentModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete Document?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete "<strong>{{ $document->name }}</strong>"? This action cannot be undone and all associated files will be permanently deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideDeleteDocumentModal()" class="btn btn-ghost">Cancel</button>
                <form action="{{ route('legal.destroy', $document) }}" method="POST">
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
    document.getElementById('deleteDocumentModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteDocumentModal() {
    document.getElementById('deleteDocumentModal').classList.add('hidden');
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
        hideDeleteDocumentModal();
        hideFileDeleteModal();
    }
});
</script>
@endsection
