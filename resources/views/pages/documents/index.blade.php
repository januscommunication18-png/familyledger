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
<dialog id="deleteConfirmModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Confirm Delete</h3>
        <p class="py-4">Are you sure you want to delete this item? This action cannot be undone.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function confirmDelete(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteConfirmModal').showModal();
}
</script>
@endsection
