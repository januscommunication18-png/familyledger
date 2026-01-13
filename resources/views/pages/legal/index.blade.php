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
        <div class="alert alert-success shadow-lg">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Hero Section -->
    @if($counts['total'] === 0)
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.07)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>
        <div class="relative px-6 py-12 sm:px-12 sm:py-16">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-white/90 text-sm mb-6">
                    <span class="icon-[tabler--shield-check] size-4"></span>
                    Secure & Private
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                    Protect What Matters Most
                </h1>
                <p class="text-lg text-white/80 mb-8 max-w-xl mx-auto">
                    Keep your family's most important legal documents organized, secure, and accessible when you need them.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('legal.create') }}" class="btn btn-lg bg-white text-violet-700 hover:bg-white/90 border-0 gap-2 shadow-lg">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Your First Document
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Header with Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Your Legal Documents</h2>
            <p class="text-slate-500 mt-1">{{ $counts['total'] }} document{{ $counts['total'] !== 1 ? 's' : '' }} stored securely</p>
        </div>
        <a href="{{ route('legal.create') }}" class="btn btn-primary gap-2 shadow-md">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Document
        </a>
    </div>
    @endif

    <!-- Document Types Grid -->
    @php
        // Use allDocuments (unfiltered) to ensure we always have access to documents for navigation
        $willDocs = $allDocuments->where('document_type', 'will');
        $trustDocs = $allDocuments->where('document_type', 'trust');
        $poaDocs = $allDocuments->whereIn('document_type', ['power_of_attorney', 'financial_poa', 'healthcare_poa']);
        $medicalDocs = $allDocuments->whereIn('document_type', ['medical_directive', 'living_will', 'healthcare_proxy', 'dnr']);
        $otherDocs = $allDocuments->whereNotIn('document_type', ['will', 'trust', 'power_of_attorney', 'financial_poa', 'healthcare_poa', 'medical_directive', 'living_will', 'healthcare_proxy', 'dnr']);
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- All -->
        <a href="{{ route('legal.index') }}"
           class="group relative overflow-hidden rounded-xl {{ !$filterType ? 'bg-primary/10 border-primary' : 'bg-slate-50 border-slate-200' }} border hover:border-primary hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ !$filterType ? 'bg-primary/20' : 'bg-slate-100 group-hover:bg-primary/10' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--files] size-5 {{ !$filterType ? 'text-primary' : 'text-slate-600 group-hover:text-primary' }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">All</h3>
                    <p class="text-xs text-slate-500">{{ $counts['total'] }} doc{{ $counts['total'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 text-slate-400 group-hover:text-primary transition-colors"></span>
            </div>
        </a>

        <!-- Wills -->
        <a href="{{ $counts['wills'] === 0 ? route('legal.create', ['type' => 'will']) : ($counts['wills'] === 1 ? route('legal.show', $willDocs->first()) : route('legal.index', ['type' => 'will'])) }}"
           class="group relative overflow-hidden rounded-xl {{ $filterType === 'will' ? 'bg-violet-100 border-violet-400 ring-2 ring-violet-200' : 'bg-violet-50 border-violet-100' }} border hover:border-violet-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ $filterType === 'will' ? 'bg-violet-200' : 'bg-violet-100 group-hover:bg-violet-200' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--file-certificate] size-5 text-violet-600"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">Wills</h3>
                    <p class="text-xs text-slate-500">{{ $counts['wills'] }} doc{{ $counts['wills'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 {{ $filterType === 'will' ? 'text-violet-600' : 'text-slate-400 group-hover:text-violet-600' }} transition-colors"></span>
            </div>
        </a>

        <!-- Trusts -->
        <a href="{{ $counts['trusts'] === 0 ? route('legal.create', ['type' => 'trust']) : ($counts['trusts'] === 1 ? route('legal.show', $trustDocs->first()) : route('legal.index', ['type' => 'trust'])) }}"
           class="group relative overflow-hidden rounded-xl {{ $filterType === 'trust' ? 'bg-blue-100 border-blue-400 ring-2 ring-blue-200' : 'bg-blue-50 border-blue-100' }} border hover:border-blue-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ $filterType === 'trust' ? 'bg-blue-200' : 'bg-blue-100 group-hover:bg-blue-200' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--building-bank] size-5 text-blue-600"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">Trusts</h3>
                    <p class="text-xs text-slate-500">{{ $counts['trusts'] }} doc{{ $counts['trusts'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 {{ $filterType === 'trust' ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' }} transition-colors"></span>
            </div>
        </a>

        <!-- Power of Attorney -->
        <a href="{{ $counts['poa'] === 0 ? route('legal.create', ['type' => 'power_of_attorney']) : ($counts['poa'] === 1 ? route('legal.show', $poaDocs->first()) : route('legal.index', ['type' => 'power_of_attorney'])) }}"
           class="group relative overflow-hidden rounded-xl {{ $filterType === 'power_of_attorney' ? 'bg-amber-100 border-amber-400 ring-2 ring-amber-200' : 'bg-amber-50 border-amber-100' }} border hover:border-amber-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ $filterType === 'power_of_attorney' ? 'bg-amber-200' : 'bg-amber-100 group-hover:bg-amber-200' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--gavel] size-5 text-amber-600"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">POA</h3>
                    <p class="text-xs text-slate-500">{{ $counts['poa'] }} doc{{ $counts['poa'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 {{ $filterType === 'power_of_attorney' ? 'text-amber-600' : 'text-slate-400 group-hover:text-amber-600' }} transition-colors"></span>
            </div>
        </a>

        <!-- Medical Directives -->
        <a href="{{ $counts['medical'] === 0 ? route('legal.create', ['type' => 'medical_directive']) : ($counts['medical'] === 1 ? route('legal.show', $medicalDocs->first()) : route('legal.index', ['type' => 'medical_directive'])) }}"
           class="group relative overflow-hidden rounded-xl {{ $filterType === 'medical_directive' ? 'bg-emerald-100 border-emerald-400 ring-2 ring-emerald-200' : 'bg-emerald-50 border-emerald-100' }} border hover:border-emerald-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ $filterType === 'medical_directive' ? 'bg-emerald-200' : 'bg-emerald-100 group-hover:bg-emerald-200' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--heart-rate-monitor] size-5 text-emerald-600"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">Medical</h3>
                    <p class="text-xs text-slate-500">{{ $counts['medical'] }} doc{{ $counts['medical'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 {{ $filterType === 'medical_directive' ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-600' }} transition-colors"></span>
            </div>
        </a>

        <!-- Other -->
        <a href="{{ $counts['other'] === 0 ? route('legal.create', ['type' => 'other']) : ($counts['other'] === 1 ? route('legal.show', $otherDocs->first()) : route('legal.index', ['type' => 'other'])) }}"
           class="group relative overflow-hidden rounded-xl {{ $filterType === 'other' ? 'bg-slate-200 border-slate-400 ring-2 ring-slate-300' : 'bg-slate-50 border-slate-200' }} border hover:border-slate-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3 p-3">
                <div class="w-10 h-10 rounded-lg {{ $filterType === 'other' ? 'bg-slate-300' : 'bg-slate-100 group-hover:bg-slate-200' }} flex items-center justify-center transition-colors shrink-0">
                    <span class="icon-[tabler--folders] size-5 text-slate-600"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">Other</h3>
                    <p class="text-xs text-slate-500">{{ $counts['other'] }} doc{{ $counts['other'] !== 1 ? 's' : '' }}</p>
                </div>
                <span class="icon-[tabler--chevron-right] size-4 {{ $filterType === 'other' ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors"></span>
            </div>
        </a>
    </div>

    <!-- Documents List -->
    @if($documents->count() > 0)
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">
                @if($filterType)
                    {{ $filterType === 'will' ? 'Wills' : ($filterType === 'trust' ? 'Trusts' : ($filterType === 'power_of_attorney' ? 'Powers of Attorney' : ($filterType === 'medical_directive' ? 'Medical Directives' : 'Other Documents'))) }}
                @else
                    All Documents
                @endif
            </h3>
            <span class="text-sm text-slate-500">{{ $documents->count() }} document{{ $documents->count() !== 1 ? 's' : '' }}</span>
        </div>

        <div class="space-y-3">
            @foreach($documents as $document)
            <div class="group bg-white rounded-xl border border-slate-200 hover:border-primary/30 hover:shadow-lg shadow-sm transition-all duration-300 overflow-hidden">
                <div class="flex items-center gap-4 p-4">
                    <!-- Icon -->
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $document->document_type === 'will' ? 'bg-violet-100' : ($document->document_type === 'trust' ? 'bg-blue-100' : (in_array($document->document_type, ['power_of_attorney', 'financial_poa', 'healthcare_poa']) ? 'bg-amber-100' : (in_array($document->document_type, ['medical_directive', 'living_will', 'healthcare_proxy', 'dnr']) ? 'bg-emerald-100' : 'bg-slate-100'))) }}">
                        <span class="{{ $document->document_type_icon }} size-6 {{ $document->document_type === 'will' ? 'text-violet-600' : ($document->document_type === 'trust' ? 'text-blue-600' : (in_array($document->document_type, ['power_of_attorney', 'financial_poa', 'healthcare_poa']) ? 'text-amber-600' : (in_array($document->document_type, ['medical_directive', 'living_will', 'healthcare_proxy', 'dnr']) ? 'text-emerald-600' : 'text-slate-600'))) }}"></span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-slate-900 truncate group-hover:text-primary transition-colors">{{ $document->name }}</h4>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $document->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($document->status === 'expired' ? 'bg-rose-100 text-rose-700' : ($document->status === 'superseded' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600')) }}">
                                {{ $document->status_name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--tag] size-3.5"></span>
                                {{ $document->document_type_name }}
                            </span>
                            @if($document->execution_date)
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--calendar] size-3.5"></span>
                                {{ $document->execution_date->format('M j, Y') }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--paperclip] size-3.5"></span>
                                {{ $document->files->count() }} file{{ $document->files->count() !== 1 ? 's' : '' }}
                            </span>
                            @if($document->attorney_display_name)
                            <span class="hidden sm:flex items-center gap-1">
                                <span class="icon-[tabler--user] size-3.5"></span>
                                {{ $document->attorney_display_name }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('legal.show', $document) }}" class="btn btn-sm btn-primary gap-1.5">
                            <span class="icon-[tabler--eye] size-4"></span>
                            <span class="hidden sm:inline">View</span>
                        </a>
                        <a href="{{ route('legal.edit', $document) }}" class="btn btn-sm btn-ghost gap-1.5 hover:bg-slate-100">
                            <span class="icon-[tabler--edit] size-4 text-slate-500"></span>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                        <button type="button" onclick="showDeleteModal('{{ route('legal.destroy', $document) }}')" class="btn btn-sm btn-ghost gap-1.5 hover:bg-rose-50">
                            <span class="icon-[tabler--trash] size-4 text-rose-500"></span>
                            <span class="hidden sm:inline text-rose-500">Delete</span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @elseif($filterType && $counts['total'] > 0)
    <!-- Empty Filter State -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">
                {{ $filterType === 'will' ? 'Wills' : ($filterType === 'trust' ? 'Trusts' : ($filterType === 'power_of_attorney' ? 'Powers of Attorney' : ($filterType === 'medical_directive' ? 'Medical Directives' : 'Other Documents'))) }}
            </h3>
        </div>
        <div class="bg-slate-50 rounded-xl border border-slate-200 p-8 text-center">
            <span class="icon-[tabler--file-off] size-12 text-slate-300"></span>
            <p class="mt-3 text-slate-600">No {{ $filterType === 'will' ? 'wills' : ($filterType === 'trust' ? 'trusts' : ($filterType === 'power_of_attorney' ? 'powers of attorney' : ($filterType === 'medical_directive' ? 'medical directives' : 'other documents'))) }} found.</p>
            <a href="{{ route('legal.create', ['type' => $filterType]) }}" class="btn btn-primary btn-sm mt-4 gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Add {{ $filterType === 'will' ? 'Will' : ($filterType === 'trust' ? 'Trust' : ($filterType === 'power_of_attorney' ? 'Power of Attorney' : ($filterType === 'medical_directive' ? 'Medical Directive' : 'Document'))) }}
            </a>
        </div>
    </div>
    @endif

    <!-- Empty State Tips -->
    @if($counts['total'] === 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
                <span class="icon-[tabler--shield-lock] size-6 text-violet-600"></span>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Secure Storage</h3>
            <p class="text-sm text-slate-500">Your documents are encrypted and stored securely. Only you and authorized family members can access them.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
                <span class="icon-[tabler--users] size-6 text-blue-600"></span>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Family Access</h3>
            <p class="text-sm text-slate-500">Share access with trusted family members so important documents are accessible when needed most.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                <span class="icon-[tabler--bell-ringing] size-6 text-emerald-600"></span>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Renewal Reminders</h3>
            <p class="text-sm text-slate-500">Get notified when documents are expiring or need to be reviewed and updated.</p>
        </div>
    </div>
    @endif
</div>

<!-- Delete Document Modal -->
<div id="deleteDocumentModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteModal()"></div>
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
                <form id="deleteDocumentForm" method="POST">
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
    document.getElementById('deleteDocumentForm').action = url;
    document.getElementById('deleteDocumentModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteDocumentModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>
@endsection
