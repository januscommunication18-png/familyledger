@extends('layouts.dashboard')

@section('page-name', 'Expenses & Budgeting')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Welcome Header --}}
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="1" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 mb-3">Take Control of Your Finances</h1>
        <p class="text-slate-500 max-w-lg mx-auto">
            Track expenses, set budgets, and achieve your financial goals with our simple and powerful budgeting tools.
        </p>
    </div>

    {{-- Video Placeholder --}}
    <div class="card bg-slate-900 mb-8">
        <div class="card-body aspect-video flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="white" stroke="none"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
                <p class="text-white/60 text-sm">Watch how budgeting works</p>
            </div>
        </div>
    </div>

    {{-- Budgeting Methods --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="card bg-base-100 shadow-sm border-2 border-emerald-200">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <span class="badge badge-success">Recommended</span>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Envelope Budgeting</h3>
                <p class="text-sm text-slate-500">Allocate money to virtual "envelopes" for each spending category. When an envelope is empty, you stop spending in that category.</p>
                <ul class="text-xs text-slate-600 mt-3 space-y-1">
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Visual spending limits</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Great for controlling overspending</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Zero-based budgeting</li>
                </ul>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Traditional Budgeting</h3>
                <p class="text-sm text-slate-500">Set spending goals by category and track actual spending vs budget. See variances and adjust spending as needed.</p>
                <ul class="text-xs text-slate-600 mt-3 space-y-1">
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Budget vs actual comparison</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Flexible spending</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Track variance over time</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Features Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(139 92 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">CSV Import</h3>
                <p class="text-sm text-slate-500">Import transactions from your bank statements with our easy CSV importer.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(245 158 11)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Smart Alerts</h3>
                <p class="text-sm text-slate-500">Get notified when you're approaching budget limits or unusual spending patterns.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(236 72 153)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Share with Family</h3>
                <p class="text-sm text-slate-500">Share budgets with family members for collaborative expense tracking.</p>
            </div>
        </div>
    </div>

    {{-- Current Status --}}
    @if($hasBudget)
    <div class="card bg-base-100 shadow-sm mb-8">
        <div class="card-body">
            <div class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>You have an active budget set up.</span>
                <a href="{{ route('expenses.dashboard') }}" class="ml-auto btn btn-sm btn-success">Go to Dashboard</a>
            </div>
        </div>
    </div>
    @endif

    {{-- CTA Button --}}
    <div class="text-center">
        @if($hasBudget)
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('expenses.dashboard') }}" class="btn btn-primary btn-lg gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    View Dashboard
                </a>
                <a href="{{ route('expenses.budget.create') }}" class="btn btn-outline btn-lg gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Create New Budget
                </a>
            </div>
        @else
            <a href="{{ route('expenses.budget.create') }}" class="btn btn-primary btn-lg gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Create Your Budget
            </a>
        @endif
        <p class="text-sm text-slate-500 mt-3">Get started in less than 5 minutes.</p>
    </div>

    {{-- Bank Sync Coming Soon --}}
    <div class="card bg-gradient-to-r from-slate-100 to-slate-50 border border-slate-200 mt-8">
        <div class="card-body flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-slate-700">Bank Sync</h3>
                    <span class="badge badge-outline badge-sm">Coming Soon</span>
                </div>
                <p class="text-sm text-slate-500">Automatically import transactions from your bank accounts.</p>
            </div>
        </div>
    </div>
</div>
@endsection
