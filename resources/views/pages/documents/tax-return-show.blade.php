@extends('layouts.dashboard')

@section('title', 'Tax Return Details')
@section('page-name', 'Documents')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="hover:text-primary">Documents</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $taxReturn->tax_year }} Tax Return</li>
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
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $taxReturn->tax_year }} Tax Return</h1>
                    <p class="text-slate-500">{{ $jurisdictions[$taxReturn->tax_jurisdiction] ?? $taxReturn->tax_jurisdiction }}@if($taxReturn->state_jurisdiction) ({{ $usStates[$taxReturn->state_jurisdiction] ?? $taxReturn->state_jurisdiction }})@endif</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('documents.tax-returns.edit', $taxReturn) }}" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $taxReturn->getFilesCount() }}</p>
            <p class="text-sm text-slate-500">Files</p>
        </div>
        <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $taxReturn->tax_year }}</p>
            <p class="text-sm text-slate-500">Tax Year</p>
        </div>
        <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
            <span class="badge badge-{{ $taxReturn->getStatusColor() }}">{{ $statuses[$taxReturn->status] ?? $taxReturn->status }}</span>
            <p class="text-sm text-slate-500 mt-1">Status</p>
        </div>
        @if($taxReturn->refund_amount)
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 shadow-sm text-center">
                <p class="text-2xl font-bold text-emerald-600">${{ number_format($taxReturn->refund_amount, 2) }}</p>
                <p class="text-sm text-slate-500">Refund</p>
            </div>
        @elseif($taxReturn->amount_owed)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 shadow-sm text-center">
                <p class="text-2xl font-bold text-rose-600">${{ number_format($taxReturn->amount_owed, 2) }}</p>
                <p class="text-sm text-slate-500">Owed</p>
            </div>
        @else
            <div class="p-4 rounded-xl bg-base-100 border border-base-200 shadow-sm text-center">
                <p class="text-2xl font-bold text-slate-400">-</p>
                <p class="text-sm text-slate-500">Amount</p>
            </div>
        @endif
    </div>

    <!-- Tax Return Information -->
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Tax Year</label>
                    <p class="text-slate-900 text-lg font-semibold">{{ $taxReturn->tax_year }}</p>
                </div>

                @if($taxReturn->taxpayers->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Taxpayer(s)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($taxReturn->taxpayers as $taxpayer)
                            <span class="badge badge-outline">{{ $taxpayer->first_name }} {{ $taxpayer->last_name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($taxReturn->filing_status)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Filing Status</label>
                    <p class="text-slate-900">{{ $filingStatuses[$taxReturn->filing_status] ?? $taxReturn->filing_status }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Status</label>
                    <span class="badge badge-{{ $taxReturn->getStatusColor() }}">{{ $statuses[$taxReturn->status] ?? $taxReturn->status }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Tax Jurisdiction</label>
                    <p class="text-slate-900">{{ $jurisdictions[$taxReturn->tax_jurisdiction] ?? $taxReturn->tax_jurisdiction }}</p>
                </div>

                @if($taxReturn->state_jurisdiction)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">State</label>
                    <p class="text-slate-900">{{ $usStates[$taxReturn->state_jurisdiction] ?? $taxReturn->state_jurisdiction }}</p>
                </div>
                @endif

                @if($taxReturn->filing_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Filing Date</label>
                    <p class="text-slate-900">{{ $taxReturn->filing_date->format('F j, Y') }}</p>
                </div>
                @endif

                @if($taxReturn->due_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Due Date</label>
                    <p class="text-slate-900">{{ $taxReturn->due_date->format('F j, Y') }}</p>
                </div>
                @endif

                @if($taxReturn->refund_amount)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Refund Amount</label>
                    <p class="text-emerald-600 text-lg font-semibold">${{ number_format($taxReturn->refund_amount, 2) }}</p>
                </div>
                @endif

                @if($taxReturn->amount_owed)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Amount Owed</label>
                    <p class="text-rose-600 text-lg font-semibold">${{ number_format($taxReturn->amount_owed, 2) }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- CPA / Tax Preparer Information -->
    @if($taxReturn->cpa_name || $taxReturn->cpa_firm || $taxReturn->cpa_phone || $taxReturn->cpa_email)
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($taxReturn->cpa_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">CPA Name</label>
                    <p class="text-slate-900">{{ $taxReturn->cpa_name }}</p>
                </div>
                @endif

                @if($taxReturn->cpa_firm)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Firm Name</label>
                    <p class="text-slate-900">{{ $taxReturn->cpa_firm }}</p>
                </div>
                @endif

                @if($taxReturn->cpa_phone)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Phone</label>
                    <a href="tel:{{ $taxReturn->cpa_phone }}" class="text-primary hover:underline">{{ $taxReturn->cpa_phone }}</a>
                </div>
                @endif

                @if($taxReturn->cpa_email)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                    <a href="mailto:{{ $taxReturn->cpa_email }}" class="text-primary hover:underline">{{ $taxReturn->cpa_email }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Tax Documents -->
    @if(($taxReturn->federal_returns && count($taxReturn->federal_returns) > 0) || ($taxReturn->state_returns && count($taxReturn->state_returns) > 0) || ($taxReturn->supporting_documents && count($taxReturn->supporting_documents) > 0))
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M15.5 2H8.6c-.4 0-.8.2-1.1.5-.3.3-.5.7-.5 1.1v12.8c0 .4.2.8.5 1.1.3.3.7.5 1.1.5h9.8c.4 0 .8-.2 1.1-.5.3-.3.5-.7.5-1.1V6.5L15.5 2z"/><path d="M3 7.6v12.8c0 .4.2.8.5 1.1.3.3.7.5 1.1.5h9.8"/><path d="M15 2v5h5"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Tax Documents</h2>
                    <p class="text-xs text-slate-400">{{ $taxReturn->getFilesCount() }} file(s) attached</p>
                </div>
            </div>

            <div class="space-y-4">
                @if($taxReturn->federal_returns && count($taxReturn->federal_returns) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Federal Returns</label>
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <ul class="space-y-2">
                            @foreach($taxReturn->federal_returns as $index => $path)
                                <li class="flex items-center justify-between text-sm text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600 flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>{{ basename($path) }}</span>
                                    </div>
                                    <x-protected-download :href="route('documents.tax-returns.download', [$taxReturn, 'federal', $index])" class="btn btn-ghost btn-xs gap-1 text-blue-600 hover:bg-blue-100" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    </x-protected-download>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @if($taxReturn->state_returns && count($taxReturn->state_returns) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">State Returns</label>
                    <div class="p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <ul class="space-y-2">
                            @foreach($taxReturn->state_returns as $index => $path)
                                <li class="flex items-center justify-between text-sm text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-600 flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>{{ basename($path) }}</span>
                                    </div>
                                    <x-protected-download :href="route('documents.tax-returns.download', [$taxReturn, 'state', $index])" class="btn btn-ghost btn-xs gap-1 text-purple-600 hover:bg-purple-100" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    </x-protected-download>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @if($taxReturn->supporting_documents && count($taxReturn->supporting_documents) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Supporting Documents (W2s, 1099s, etc.)</label>
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <ul class="space-y-2">
                            @foreach($taxReturn->supporting_documents as $index => $path)
                                <li class="flex items-center justify-between text-sm text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600 flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>{{ basename($path) }}</span>
                                    </div>
                                    <x-protected-download :href="route('documents.tax-returns.download', [$taxReturn, 'supporting', $index])" class="btn btn-ghost btn-xs gap-1 text-amber-600 hover:bg-amber-100" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                        Download
                                    </x-protected-download>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($taxReturn->notes)
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

            <div class="p-3 bg-slate-50 rounded-lg text-slate-700 whitespace-pre-wrap">{{ $taxReturn->notes }}</div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex justify-start gap-3">
        <a href="{{ route('documents.tax-returns.edit', $taxReturn) }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            Edit Tax Return
        </a>
        <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="btn btn-ghost">Back to Documents</a>
    </div>
</div>
@endsection
