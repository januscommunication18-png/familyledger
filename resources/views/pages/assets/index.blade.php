@extends('layouts.dashboard')

@section('title', 'Assets')
@section('page-name', 'Assets')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Assets</li>
@endsection

@section('page-title', 'Assets')
@section('page-description', 'Track and manage your property, vehicles, valuables, and home inventory.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--home] size-5 text-primary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $counts['property'] }}</div>
                        <div class="text-xs text-base-content/60">Properties</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--car] size-5 text-secondary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $counts['vehicle'] }}</div>
                        <div class="text-xs text-base-content/60">Vehicles</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                        <span class="icon-[tabler--diamond] size-5 text-accent"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $counts['valuable'] }}</div>
                        <div class="text-xs text-base-content/60">Valuables</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--box] size-5 text-info"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $counts['inventory'] }}</div>
                        <div class="text-xs text-base-content/60">Inventory</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm col-span-2 md:col-span-4 lg:col-span-1">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--currency-dollar] size-5 text-success"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($totals['overall'], 0) }}</div>
                        <div class="text-xs text-base-content/60">Total Value</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="border-b border-base-200 mb-6">
                <nav class="-mb-px flex gap-4 overflow-x-auto">
                    <a href="{{ route('assets.index', ['tab' => 'property']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ $tab === 'property' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--home] size-5 inline-block align-middle mr-2"></span>
                        Property
                        @if($counts['property'] > 0)
                            <span class="badge badge-sm {{ $tab === 'property' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $counts['property'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('assets.index', ['tab' => 'vehicle']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ $tab === 'vehicle' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--car] size-5 inline-block align-middle mr-2"></span>
                        Vehicles
                        @if($counts['vehicle'] > 0)
                            <span class="badge badge-sm {{ $tab === 'vehicle' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $counts['vehicle'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('assets.index', ['tab' => 'valuable']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ $tab === 'valuable' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--diamond] size-5 inline-block align-middle mr-2"></span>
                        Valuables
                        @if($counts['valuable'] > 0)
                            <span class="badge badge-sm {{ $tab === 'valuable' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $counts['valuable'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('assets.index', ['tab' => 'inventory']) }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ $tab === 'inventory' ? 'border-primary text-primary' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300' }}">
                        <span class="icon-[tabler--box] size-5 inline-block align-middle mr-2"></span>
                        Home Inventory
                        @if($counts['inventory'] > 0)
                            <span class="badge badge-sm {{ $tab === 'inventory' ? 'badge-primary' : 'badge-neutral' }} ml-2">{{ $counts['inventory'] }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            @if($tab === 'property')
                @include('pages.assets.partials.property-tab')
            @elseif($tab === 'vehicle')
                @include('pages.assets.partials.vehicles-tab')
            @elseif($tab === 'valuable')
                @include('pages.assets.partials.valuables-tab')
            @else
                @include('pages.assets.partials.inventory-tab')
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete Asset?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete this asset? This action cannot be undone.</p>
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
function confirmDelete(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>
@endsection
