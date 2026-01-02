@extends('layouts.dashboard')

@section('title', 'Legal Documents')
@section('page-name', 'Legal')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Legal</li>
@endsection

@section('page-title', 'Legal Documents')
@section('page-description', 'Manage your family\'s important legal documents including wills, trusts, and powers of attorney.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Landing Section with Video Placeholder -->
    @if($counts['total'] === 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <div class="max-w-2xl mx-auto">
                <!-- Video Placeholder -->
                <div class="aspect-video bg-gradient-to-br from-violet-100 to-purple-100 rounded-xl mb-8 flex items-center justify-center">
                    <div class="text-center">
                        <span class="icon-[tabler--player-play-filled] size-16 text-primary/40"></span>
                        <p class="text-sm text-base-content/60 mt-2">Watch: How to organize your legal documents</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold mb-4">Secure Your Family's Legal Documents</h2>
                <p class="text-base-content/70 mb-8">
                    Keep your most important legal documents organized and accessible. Store wills, trusts,
                    powers of attorney, and medical directives in one secure location that your family can
                    access when needed.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('legal.create') }}" class="btn btn-primary btn-lg gap-2">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Legal Document
                    </a>
                    <button class="btn btn-outline btn-lg gap-2" disabled>
                        <span class="icon-[tabler--file-certificate] size-5"></span>
                        Create Digital Will
                        <span class="badge badge-sm badge-warning">Coming Soon</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Document Types Overview -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('legal.create', ['type' => 'will']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center mb-2 group-hover:bg-violet-200 transition-colors">
                    <span class="icon-[tabler--file-certificate] size-6 text-violet-600"></span>
                </div>
                <h3 class="font-semibold text-sm">Wills</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['wills'] }}</span>
            </div>
        </a>

        <a href="{{ route('legal.create', ['type' => 'trust']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-2 group-hover:bg-blue-200 transition-colors">
                    <span class="icon-[tabler--building-bank] size-6 text-blue-600"></span>
                </div>
                <h3 class="font-semibold text-sm">Trusts</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['trusts'] }}</span>
            </div>
        </a>

        <a href="{{ route('legal.create', ['type' => 'power_of_attorney']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-2 group-hover:bg-amber-200 transition-colors">
                    <span class="icon-[tabler--gavel] size-6 text-amber-600"></span>
                </div>
                <h3 class="font-semibold text-sm">Power of Attorney</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['poa'] }}</span>
            </div>
        </a>

        <a href="{{ route('legal.create', ['type' => 'medical_directive']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mb-2 group-hover:bg-emerald-200 transition-colors">
                    <span class="icon-[tabler--stethoscope] size-6 text-emerald-600"></span>
                </div>
                <h3 class="font-semibold text-sm">Medical Directives</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['medical'] }}</span>
            </div>
        </a>

        <a href="{{ route('legal.create', ['type' => 'other']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-2 group-hover:bg-gray-200 transition-colors">
                    <span class="icon-[tabler--file-text] size-6 text-gray-600"></span>
                </div>
                <h3 class="font-semibold text-sm">Other</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['other'] }}</span>
            </div>
        </a>
    </div>

    <!-- Documents List -->
    @if($counts['total'] > 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title">All Legal Documents</h2>
                <a href="{{ route('legal.create') }}" class="btn btn-primary btn-sm gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Document
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $document)
                <div class="card bg-base-200/50 hover:bg-base-200 transition-colors">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <span class="{{ $document->document_type_icon }} size-5 text-primary"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-sm truncate">{{ $document->name }}</h3>
                                <p class="text-xs text-base-content/60">{{ $document->document_type_name }}</p>
                            </div>
                            <span class="badge badge-{{ $document->status_color }} badge-sm">{{ $document->status_name }}</span>
                        </div>

                        <div class="mt-3 text-xs text-base-content/60 space-y-1">
                            @if($document->execution_date)
                            <div class="flex items-center gap-1">
                                <span class="icon-[tabler--calendar] size-3.5"></span>
                                <span>Executed: {{ $document->execution_date->format('M j, Y') }}</span>
                            </div>
                            @endif

                            @if($document->attorney_display_name)
                            <div class="flex items-center gap-1">
                                <span class="icon-[tabler--user] size-3.5"></span>
                                <span>{{ $document->attorney_display_name }}</span>
                            </div>
                            @endif

                            <div class="flex items-center gap-1">
                                <span class="icon-[tabler--files] size-3.5"></span>
                                <span>{{ $document->files->count() }} file(s)</span>
                            </div>
                        </div>

                        <div class="card-actions justify-end mt-3 pt-3 border-t border-base-300">
                            <a href="{{ route('legal.show', $document) }}" class="btn btn-ghost btn-xs gap-1">
                                <span class="icon-[tabler--eye] size-4"></span>
                                View
                            </a>
                            <a href="{{ route('legal.edit', $document) }}" class="btn btn-ghost btn-xs gap-1">
                                <span class="icon-[tabler--edit] size-4"></span>
                                Edit
                            </a>
                            <button type="button" onclick="showDeleteModal('{{ route('legal.destroy', $document) }}')" class="btn btn-ghost btn-xs text-error gap-1">
                                <span class="icon-[tabler--trash] size-4"></span>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteModal()"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete Document?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete this legal document? This action cannot be undone and all associated files will be permanently deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideDeleteModal()" class="btn btn-ghost">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>
@endsection
