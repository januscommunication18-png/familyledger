@extends('layouts.dashboard')

@section('page-name', 'Shared Expenses')

@section('content')
<div class="p-4 lg:p-6 flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto rounded-2xl bg-emerald-100 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Expense Tracking</h2>
        <p class="text-slate-500 mb-6">
            Track child-related expenses, split costs fairly, and maintain transparency. Upload receipts and request reimbursements.
        </p>
        <span class="badge badge-lg badge-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            Coming Soon
        </span>
    </div>
</div>
@endsection
