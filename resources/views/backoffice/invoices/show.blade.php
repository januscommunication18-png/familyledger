@extends('backoffice.layouts.app')

@php
    $header = 'Invoice #' . $invoice->invoice_number;
@endphp

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('backoffice.invoices.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Invoices
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Main Invoice Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created {{ $invoice->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'refunded' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$invoice->status] ?? $statusColors['pending'] }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>

                <div class="p-6">
                    <!-- Customer Info -->
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Bill To</h3>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $invoice->customer_name ?? $invoice->tenant?->name ?? 'N/A' }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $invoice->customer_email }}</p>
                        @if ($invoice->billing_address)
                            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $invoice->billing_address }}</p>
                        @endif
                    </div>

                    <!-- Line Items -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Items</h3>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-gray-500 dark:text-gray-400">
                                    <th class="pb-2">Description</th>
                                    <th class="pb-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900 dark:text-white">
                                <tr>
                                    <td class="py-2">
                                        <p class="font-medium">{{ $invoice->packagePlan?->name ?? 'Subscription' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($invoice->billing_cycle) }} billing</p>
                                        @if ($invoice->period_start && $invoice->period_end)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $invoice->period_start->format('M j, Y') }} - {{ $invoice->period_end->format('M j, Y') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right">{{ $invoice->formatted_subtotal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                            <span class="text-gray-900 dark:text-white">{{ $invoice->formatted_subtotal }}</span>
                        </div>
                        @if ($invoice->discount_amount > 0)
                            <div class="flex justify-between py-2 text-green-600 dark:text-green-400">
                                <span>
                                    Discount
                                    @if ($invoice->discount_code)
                                        ({{ $invoice->discount_code }} - {{ $invoice->discount_percentage }}%)
                                    @endif
                                </span>
                                <span>{{ $invoice->formatted_discount }}</span>
                            </div>
                        @endif
                        @if ($invoice->tax_amount > 0)
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600 dark:text-gray-400">Tax</span>
                                <span class="text-gray-900 dark:text-white">{{ $invoice->formatted_tax }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-3 border-t border-gray-200 dark:border-gray-700 text-lg font-bold">
                            <span class="text-gray-900 dark:text-white">Total</span>
                            <span class="text-gray-900 dark:text-white">{{ $invoice->formatted_total }} {{ $invoice->currency }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
                <form action="{{ route('backoffice.invoices.add-note', $invoice) }}" method="POST">
                    @csrf
                    <textarea
                        name="notes"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 mb-3"
                        placeholder="Add internal notes..."
                    >{{ $invoice->notes }}</textarea>
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        Save Notes
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Actions</h3>

                <!-- Resend to original email -->
                <form action="{{ route('backoffice.invoices.resend', $invoice) }}" method="POST" class="mb-4">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Resend Invoice
                    </button>
                </form>

                <!-- Resend to custom email -->
                <form action="{{ route('backoffice.invoices.resend-to-email', $invoice) }}" method="POST">
                    @csrf
                    <label for="custom_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Send to different email</label>
                    <div class="flex gap-2">
                        <input
                            type="email"
                            name="email"
                            id="custom_email"
                            placeholder="email@example.com"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                            Send
                        </button>
                    </div>
                </form>

                {{-- TESTING ONLY - Delete test invoice --}}
                @if (str_contains($invoice->notes ?? '', '[TEST INVOICE]'))
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-3">
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 font-medium">TESTING ONLY - This is a test invoice</p>
                        </div>
                        <form action="{{ route('backoffice.invoices.destroy-test', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this test invoice? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Test Invoice
                            </button>
                        </form>
                    </div>
                @endif
                {{-- END TESTING ONLY --}}
            </div>

            <!-- Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Information</h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Tenant</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">{{ $invoice->tenant?->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">{{ $invoice->packagePlan?->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Billing Cycle</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">{{ ucfirst($invoice->billing_cycle) }}</dd>
                    </div>
                    @if ($invoice->paid_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Paid At</dt>
                            <dd class="text-gray-900 dark:text-white font-medium">{{ $invoice->paid_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Emailed</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">
                            @if ($invoice->emailed_at)
                                Yes ({{ $invoice->email_count }}x)
                            @else
                                No
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Paddle Info -->
            @if ($invoice->paddle_transaction_id || $invoice->paddle_subscription_id)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Paddle Details</h3>

                    <dl class="space-y-3 text-sm">
                        @if ($invoice->paddle_transaction_id)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Transaction ID</dt>
                                <dd class="text-gray-900 dark:text-white font-mono text-xs mt-1 break-all">{{ $invoice->paddle_transaction_id }}</dd>
                            </div>
                        @endif
                        @if ($invoice->paddle_subscription_id)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Subscription ID</dt>
                                <dd class="text-gray-900 dark:text-white font-mono text-xs mt-1 break-all">{{ $invoice->paddle_subscription_id }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endsection
