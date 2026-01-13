@extends('layouts.dashboard')

@section('title', $taxReturn ? 'Edit Tax Return' : 'Add Tax Return')
@section('page-name', 'Documents')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="hover:text-primary">Documents</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $taxReturn ? 'Edit Tax Return' : 'Add Tax Return' }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $taxReturn ? 'Edit Tax Return' : 'Add Tax Return' }}</h1>
                <p class="text-slate-500">Track your tax return filings and documents</p>
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

    <!-- Overview (Edit mode only) -->
    @if($taxReturn)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Overview</h2>
                        <p class="text-xs text-slate-400">Tax return summary</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ $taxReturn->getFilesCount() }}</p>
                        <p class="text-sm text-slate-500">Files</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <p class="text-2xl font-bold text-slate-800">{{ $taxReturn->tax_year }}</p>
                        <p class="text-sm text-slate-500">Tax Year</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <span class="badge badge-{{ $taxReturn->getStatusColor() }}">{{ $statuses[$taxReturn->status] ?? $taxReturn->status }}</span>
                        <p class="text-sm text-slate-500 mt-1">Status</p>
                    </div>
                    @if($taxReturn->refund_amount)
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                            <p class="text-2xl font-bold text-emerald-600">${{ number_format($taxReturn->refund_amount, 2) }}</p>
                            <p class="text-sm text-slate-500">Refund</p>
                        </div>
                    @elseif($taxReturn->amount_owed)
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-center">
                            <p class="text-2xl font-bold text-rose-600">${{ number_format($taxReturn->amount_owed, 2) }}</p>
                            <p class="text-sm text-slate-500">Owed</p>
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <p class="text-2xl font-bold text-slate-400">-</p>
                            <p class="text-sm text-slate-500">Amount</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form action="{{ $taxReturn ? route('documents.tax-returns.update', $taxReturn) : route('documents.tax-returns.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($taxReturn)
            @method('PUT')
        @endif

        <!-- Basic Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Tax Return Information</h2>
                        <p class="text-xs text-slate-400">Basic filing details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Tax Year -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax Year <span class="text-rose-500">*</span></label>
                        <select name="tax_year" id="tax_year_select" required class="select select-bordered w-full">
                            @for($year = date('Y') + 1; $year >= 2010; $year--)
                                <option value="{{ $year }}" {{ old('tax_year', $taxReturn?->tax_year ?? date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Taxpayers -->
                    @php
                        $taxpayerIds = $taxReturn ? $taxReturn->taxpayers->pluck('id')->map(fn($id) => (string)$id)->toArray() : [];
                    @endphp
                    <div x-data="taxpayerSelect()">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Taxpayer</label>

                        <!-- Dropdown Input -->
                        <div class="relative">
                            <div class="relative">
                                <input type="text" x-model="search" @focus="openDropdown()" @click="openDropdown()"
                                       class="input input-bordered w-full pr-8"
                                       placeholder="Select taxpayers...">
                                <button type="button" @click="open ? closeDropdown() : openDropdown()" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-cloak @click.outside="closeDropdown()"
                                 class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="member in filteredMembers" :key="member.id">
                                    <div @click="toggleMember(member.id)"
                                         class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-base-200"
                                         :class="{ 'bg-primary/5': isSelected(member.id) }">
                                        <input type="checkbox" :checked="isSelected(member.id)" class="checkbox checkbox-sm checkbox-primary" @click.stop>
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-xs font-bold text-emerald-700"
                                             x-text="member.initial"></div>
                                        <div class="flex-1">
                                            <span class="font-medium text-sm" x-text="member.name"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredMembers.length === 0" class="px-3 py-2 text-sm text-slate-500">
                                    No members found
                                </div>
                            </div>
                        </div>

                        <!-- Selected Tags Below Input -->
                        <div class="flex flex-wrap gap-2 mt-2" x-show="selected.length > 0">
                            <template x-for="id in selected" :key="id">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm">
                                    <span x-text="getMemberName(id)"></span>
                                    <button type="button" @click="toggleMember(id)" class="hover:text-error">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <template x-for="id in selected" :key="'input-' + id">
                            <input type="hidden" name="taxpayers[]" :value="id">
                        </template>
                    </div>

                    <!-- Filing Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Filing Status</label>
                        <select name="filing_status" id="filing_status_select" class="select select-bordered w-full">
                            <option value="">Select filing status...</option>
                            @foreach($filingStatuses as $key => $label)
                                <option value="{{ $key }}" {{ old('filing_status', $taxReturn?->filing_status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" id="status_select" class="select select-bordered w-full">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $taxReturn?->status ?? 'not_started') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tax Jurisdiction -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax Jurisdiction</label>
                        <select name="tax_jurisdiction" id="jurisdiction_select" class="select select-bordered w-full">
                            @foreach($jurisdictions as $key => $label)
                                <option value="{{ $key }}" {{ old('tax_jurisdiction', $taxReturn?->tax_jurisdiction ?? 'federal') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- State -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">State</label>
                        <select name="state_jurisdiction" id="state_select" class="select select-bordered w-full">
                            <option value="">Select state...</option>
                            @foreach($usStates as $code => $name)
                                <option value="{{ $code }}" {{ old('state_jurisdiction', $taxReturn?->state_jurisdiction) === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filing Date -->
                    <x-date-select
                        name="filing_date"
                        label="Filing Date"
                        :value="$taxReturn?->filing_date"
                    />

                    <!-- Due Date -->
                    <x-date-select
                        name="due_date"
                        label="Due Date"
                        :value="$taxReturn?->due_date"
                    />

                    <!-- Refund Amount -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Refund Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">$</span>
                            <input type="number" name="refund_amount" value="{{ old('refund_amount', $taxReturn?->refund_amount) }}"
                                   step="0.01" min="0" placeholder="0.00"
                                   class="input input-bordered w-full pl-7" />
                        </div>
                    </div>

                    <!-- Amount Owed -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Amount Owed</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">$</span>
                            <input type="number" name="amount_owed" value="{{ old('amount_owed', $taxReturn?->amount_owed) }}"
                                   step="0.01" min="0" placeholder="0.00"
                                   class="input input-bordered w-full pl-7" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CPA Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">CPA / Tax Preparer</h2>
                        <p class="text-xs text-slate-400">Your accountant's contact information</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">CPA Name</label>
                        <input type="text" name="cpa_name" value="{{ old('cpa_name', $taxReturn?->cpa_name) }}"
                               class="input input-bordered w-full" placeholder="Accountant name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Firm Name</label>
                        <input type="text" name="cpa_firm" value="{{ old('cpa_firm', $taxReturn?->cpa_firm) }}"
                               class="input input-bordered w-full" placeholder="Accounting firm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="tel" name="cpa_phone" value="{{ old('cpa_phone', $taxReturn?->cpa_phone) }}"
                               class="input input-bordered w-full" placeholder="(555) 123-4567" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="cpa_email" value="{{ old('cpa_email', $taxReturn?->cpa_email) }}"
                               class="input input-bordered w-full" placeholder="cpa@example.com" />
                    </div>
                </div>
            </div>
        </div>

        <!-- File Uploads -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M15.5 2H8.6c-.4 0-.8.2-1.1.5-.3.3-.5.7-.5 1.1v12.8c0 .4.2.8.5 1.1.3.3.7.5 1.1.5h9.8c.4 0 .8-.2 1.1-.5.3-.3.5-.7.5-1.1V6.5L15.5 2z"/><path d="M3 7.6v12.8c0 .4.2.8.5 1.1.3.3.7.5 1.1.5h9.8"/><path d="M15 2v5h5"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Tax Documents</h2>
                        <p class="text-xs text-slate-400">Upload your tax return files</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Federal Returns -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Federal Returns</label>
                        @if($taxReturn?->federal_returns && count($taxReturn->federal_returns) > 0)
                            <div class="mb-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <p class="text-xs text-slate-500 mb-2">Existing files:</p>
                                <ul class="space-y-1">
                                    @foreach($taxReturn->federal_returns as $path)
                                        <li class="flex items-center gap-2 text-sm text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            {{ basename($path) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div id="federal_preview" class="hidden mb-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                            <p class="text-xs text-slate-500 mb-2">New files to upload:</p>
                            <ul id="federal_list" class="space-y-1"></ul>
                        </div>
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-amber-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-amber-600">Click to upload</span> or drag files</p>
                                <p class="text-xs text-slate-400">PDF, JPG, PNG (max 10MB each)</p>
                            </div>
                            <input type="file" id="federal_input" name="federal_returns[]" accept=".pdf,.jpg,.jpeg,.png" multiple class="hidden" />
                        </label>
                    </div>

                    <!-- State Returns -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">State Returns</label>
                        @if($taxReturn?->state_returns && count($taxReturn->state_returns) > 0)
                            <div class="mb-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <p class="text-xs text-slate-500 mb-2">Existing files:</p>
                                <ul class="space-y-1">
                                    @foreach($taxReturn->state_returns as $path)
                                        <li class="flex items-center gap-2 text-sm text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            {{ basename($path) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div id="state_preview" class="hidden mb-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                            <p class="text-xs text-slate-500 mb-2">New files to upload:</p>
                            <ul id="state_list" class="space-y-1"></ul>
                        </div>
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-amber-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-amber-600">Click to upload</span> or drag files</p>
                                <p class="text-xs text-slate-400">PDF, JPG, PNG (max 10MB each)</p>
                            </div>
                            <input type="file" id="state_input" name="state_returns[]" accept=".pdf,.jpg,.jpeg,.png" multiple class="hidden" />
                        </label>
                    </div>

                    <!-- Supporting Documents -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Supporting Documents (W2s, 1099s, etc.)</label>
                        @if($taxReturn?->supporting_documents && count($taxReturn->supporting_documents) > 0)
                            <div class="mb-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <p class="text-xs text-slate-500 mb-2">Existing files:</p>
                                <ul class="space-y-1">
                                    @foreach($taxReturn->supporting_documents as $path)
                                        <li class="flex items-center gap-2 text-sm text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            {{ basename($path) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div id="supporting_preview" class="hidden mb-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                            <p class="text-xs text-slate-500 mb-2">New files to upload:</p>
                            <ul id="supporting_list" class="space-y-1"></ul>
                        </div>
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-amber-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-amber-600">Click to upload</span> or drag files</p>
                                <p class="text-xs text-slate-400">PDF, JPG, PNG (max 10MB each)</p>
                            </div>
                            <input type="file" id="supporting_input" name="supporting_documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple class="hidden" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Notes</h2>
                        <p class="text-xs text-slate-400">Additional notes about this tax return</p>
                    </div>
                </div>

                <div>
                    <textarea name="notes" rows="4"
                              class="textarea textarea-bordered w-full"
                              placeholder="Any important notes about this tax return...">{{ old('notes', $taxReturn?->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-start gap-3">
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $taxReturn ? 'Update Tax Return' : 'Save Tax Return' }}
            </button>
            <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<script>
function taxpayerSelect() {
    return {
        open: false,
        justOpened: false,
        search: '',
        selected: {!! json_encode($taxpayerIds) !!},
        members: [
            @foreach($familyMembers as $member)
            {
                id: '{{ $member->id }}',
                name: '{{ addslashes($member->first_name) }} {{ addslashes($member->last_name ?? '') }}',
                initial: '{{ strtoupper(substr($member->first_name, 0, 1)) }}'
            },
            @endforeach
        ],

        get filteredMembers() {
            if (!this.search) return this.members;
            const searchLower = this.search.toLowerCase();
            return this.members.filter(m => m.name.toLowerCase().includes(searchLower));
        },

        openDropdown() {
            this.open = true;
            this.justOpened = true;
            setTimeout(() => { this.justOpened = false; }, 150);
        },

        closeDropdown() {
            if (!this.justOpened) {
                this.open = false;
            }
        },

        toggleMember(id) {
            const strId = String(id);
            const index = this.selected.findIndex(s => String(s) === strId);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(strId);
            }
        },

        isSelected(id) {
            return this.selected.some(s => String(s) === String(id));
        },

        getMemberName(id) {
            const member = this.members.find(m => String(m.id) === String(id));
            return member ? member.name : '';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    function setupFilePreview(inputId, previewId, listId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const list = document.getElementById(listId);

        if (input) {
            input.addEventListener('change', function() {
                list.innerHTML = '';
                if (this.files.length > 0) {
                    preview.classList.remove('hidden');
                    Array.from(this.files).forEach(file => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-2 text-sm text-slate-700';
                        li.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            ${file.name}
                        `;
                        list.appendChild(li);
                    });
                } else {
                    preview.classList.add('hidden');
                }
            });
        }
    }

    setupFilePreview('federal_input', 'federal_preview', 'federal_list');
    setupFilePreview('state_input', 'state_preview', 'state_list');
    setupFilePreview('supporting_input', 'supporting_preview', 'supporting_list');
});
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
