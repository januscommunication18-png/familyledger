@extends('backoffice.layouts.app')

@php
    $header = 'Create Test Invoice';
@endphp

@section('content')
    <!-- TESTING ONLY Warning Banner -->
    <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900/30 border-2 border-yellow-400 dark:border-yellow-600 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 class="font-bold text-yellow-800 dark:text-yellow-200">TESTING ONLY</h3>
                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    This feature is for testing the invoice and email system. Test invoices are marked with "[TEST INVOICE]" in notes and can be deleted.
                    <strong>Remove this feature after testing is complete.</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('backoffice.invoices.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Invoices
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/20">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Create Test Invoice
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Create a test invoice to verify the invoice system and email templates are working correctly.
                </p>
            </div>

            <form action="{{ route('backoffice.invoices.store-test') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Tenant Selection -->
                <div>
                    <label for="tenant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Tenant (Customer) <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="tenant_id"
                        name="tenant_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Select a tenant...</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }} ({{ $tenant->users()->first()?->email ?? 'No email' }})
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plan Selection -->
                <div>
                    <label for="package_plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Package Plan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="package_plan_id"
                        name="package_plan_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Select a plan...</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('package_plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (${{ number_format($plan->cost_per_month, 2) }}/mo or ${{ number_format($plan->cost_per_year, 2) }}/yr)
                            </option>
                        @endforeach
                    </select>
                    @error('package_plan_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Cycle -->
                <div>
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Billing Cycle <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="billing_cycle"
                        name="billing_cycle"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    @error('billing_cycle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Total Amount ($) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="total_amount"
                            name="total_amount"
                            step="0.01"
                            min="0"
                            value="{{ old('total_amount', '9.99') }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('total_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="discount_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Discount Amount ($)
                        </label>
                        <input
                            type="number"
                            id="discount_amount"
                            name="discount_amount"
                            step="0.01"
                            min="0"
                            value="{{ old('discount_amount', '0') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('discount_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Send Email Option -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="send_email"
                            value="1"
                            checked
                            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Send invoice email</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Send the payment confirmation email to the tenant's email address</p>
                        </div>
                    </label>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Create Test Invoice
                    </button>
                    <a href="{{ route('backoffice.invoices.index') }}" class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Instructions -->
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">How to test:</h4>
            <ol class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-decimal list-inside">
                <li>Select a tenant (customer) from the dropdown</li>
                <li>Choose a package plan and billing cycle</li>
                <li>Set the total amount (or use default)</li>
                <li>Keep "Send invoice email" checked to test the email</li>
                <li>Click "Create Test Invoice"</li>
                <li>Check the tenant's email inbox for the invoice email</li>
                <li>You can delete test invoices from the invoice detail page</li>
            </ol>
        </div>
    </div>
@endsection
