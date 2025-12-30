@extends('layouts.dashboard')

@section('title', 'Expenses Tracker')
@section('page-name', 'Expenses')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Expenses Tracker</li>
@endsection

@section('page-title', 'Expenses Tracker')
@section('page-description', 'Track shared expenses, budgets, and reimbursements.')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <p class="text-sm text-base-content/60">This Month</p>
            <p class="text-2xl font-bold mt-1">$0.00</p>
        </div>
    </div>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <p class="text-sm text-base-content/60">Last Month</p>
            <p class="text-2xl font-bold mt-1">$0.00</p>
        </div>
    </div>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <p class="text-sm text-base-content/60">Pending</p>
            <p class="text-2xl font-bold mt-1 text-warning">$0.00</p>
        </div>
    </div>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <p class="text-sm text-base-content/60">Owed to You</p>
            <p class="text-2xl font-bold mt-1 text-success">$0.00</p>
        </div>
    </div>
</div>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="tabs tabs-boxed">
                <a class="tab tab-active">All</a>
                <a class="tab">Pending</a>
                <a class="tab">Settled</a>
            </div>
            <button class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Expense
            </button>
        </div>

        <div class="text-center py-12 text-base-content/60">
            <span class="icon-[tabler--receipt] size-16 opacity-30"></span>
            <p class="mt-4 text-lg font-medium">No expenses recorded</p>
            <p class="text-sm">Start tracking shared family expenses</p>
            <button class="btn btn-primary mt-4">
                <span class="icon-[tabler--plus] size-4"></span>
                Add Your First Expense
            </button>
        </div>
    </div>
</div>
@endsection
