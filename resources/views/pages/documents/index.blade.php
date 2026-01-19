@extends('layouts.dashboard')

@section('title', 'Documents')
@section('page-name', 'Documents')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Documents</li>
@endsection

@section('page-title', 'Documents')
@section('page-description', 'Manage your family insurance policies and tax returns.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="border-b border-base-200 mb-6">
                <nav class="-mb-px flex gap-6">
                    <a href="{{ route('documents.index', ['tab' => 'insurance']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm {{ $tab === 'insurance' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--shield-check] size-5 inline-block align-middle mr-2"></span>
                        Insurance
                        @if($insurancePolicies->count() > 0)
                            <span class="badge badge-sm {{ $tab === 'insurance' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $insurancePolicies->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm {{ $tab === 'tax-returns' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--file-invoice] size-5 inline-block align-middle mr-2"></span>
                        Tax Returns
                        @if($taxReturns->count() > 0)
                            <span class="badge badge-sm {{ $tab === 'tax-returns' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $taxReturns->count() }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            @if($tab === 'insurance')
                @include('pages.documents.partials.insurance-tab')
            @else
                @include('pages.documents.partials.tax-returns-tab')
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 pointer-events-auto">
            <h3 class="font-bold text-lg text-error">Confirm Delete</h3>
            <p class="py-4">Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-ghost">Cancel</button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('deleteConfirmModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    }
});
</script>
@endsection
