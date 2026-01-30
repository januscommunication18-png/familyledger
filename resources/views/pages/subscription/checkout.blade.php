@extends('layouts.dashboard')

@section('title', 'Checkout')
@section('page-name', 'Subscription')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li><a href="{{ route('subscription.pricing') }}">Pricing</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Checkout</li>
@endsection

@section('page-title', 'Complete Your Purchase')
@section('page-description', 'Review your order and complete payment.')

@push('styles')
<style>
    /* Ensure Paddle checkout iframe takes full width */
    #paddle-checkout-frame {
        width: 100% !important;
        min-width: 100% !important;
    }
    #paddle-checkout-frame iframe {
        width: 100% !important;
        min-width: 100% !important;
    }
    .paddle-frame-overlay,
    .paddle-frame-inline {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto" x-data="checkoutForm()">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Order Summary -->
        <div class="lg:col-span-2 order-2 lg:order-1">
            <div class="card bg-base-100 shadow-sm sticky top-6">
                <div class="card-body">
                    <h2 class="card-title mb-4">Order Summary</h2>

                    <div class="space-y-4">
                        <!-- Plan Details -->
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold">{{ $plan->name }}</p>
                                <p class="text-sm text-base-content/60">{{ ucfirst($billingCycle) }} billing</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">${{ number_format($price, 2) }}</p>
                                @if($billingCycle === 'yearly')
                                    <p class="text-xs text-base-content/60">(${{ number_format($price / 12, 2) }}/mo)</p>
                                @endif
                            </div>
                        </div>

                        <!-- Trial Info -->
                        @if($plan->trial_period_days > 0)
                            <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                                <p class="text-sm text-success font-medium">{{ $plan->trial_period_days }}-day free trial included</p>
                                <p class="text-xs text-base-content/60 mt-1">You won't be charged until the trial ends</p>
                            </div>
                        @endif

                        <div class="divider my-2"></div>

                        <!-- Discount Code -->
                        <div>
                            <label class="text-sm font-medium">Discount Code</label>
                            <div class="flex gap-2 mt-1">
                                <input
                                    type="text"
                                    x-model="discountCode"
                                    placeholder="Enter code"
                                    class="input input-bordered input-sm flex-1 uppercase"
                                    :disabled="discountApplied"
                                >
                                <button
                                    type="button"
                                    @click="applyDiscount"
                                    :disabled="discountApplied || !discountCode || applyingDiscount"
                                    class="btn btn-sm btn-outline"
                                >
                                    <span x-show="!applyingDiscount">Apply</span>
                                    <span x-show="applyingDiscount" class="loading loading-spinner loading-xs"></span>
                                </button>
                            </div>
                            <p x-show="discountError" x-text="discountError" class="text-xs text-error mt-1"></p>
                            <div x-show="discountApplied" class="text-xs text-success mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span x-text="discountName + ' applied!'"></span>
                                <button type="button" @click="removeDiscount" class="text-error ml-2">&times;</button>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Totals -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">Subtotal</span>
                                <span>${{ number_format($price, 2) }}</span>
                            </div>
                            <div x-show="discountApplied" class="flex justify-between text-sm text-success">
                                <span>Discount (<span x-text="discountPercentage"></span>%)</span>
                                <span>-$<span x-text="savings"></span></span>
                            </div>
                            <div class="flex justify-between font-bold text-lg pt-2 border-t border-base-200">
                                <span>Total</span>
                                <span x-text="'$' + finalPrice"></span>
                            </div>
                        </div>

                        @if($billingCycle === 'yearly')
                            <p class="text-xs text-base-content/60 text-center">
                                You save ${{ number_format(($plan->cost_per_month * 12) - $plan->cost_per_year, 2) }} compared to monthly billing
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="lg:col-span-3 order-1 lg:order-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">Payment Details</h2>

                    <!-- Paddle Checkout Container -->
                    <div id="paddle-checkout-container" class="w-full">
                        <!-- Loading State -->
                        <div id="paddle-loading" class="text-center py-8">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="text-sm text-base-content/60 mt-2">Loading secure checkout...</p>
                        </div>

                        <!-- Pay Now Button (shows after Paddle loads) -->
                        <div id="paddle-button-container" class="hidden">
                            <div class="bg-base-200/50 rounded-xl p-6 text-center">
                                <div class="mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold mb-2">Secure Payment</h3>
                                <p class="text-base-content/70 mb-6">Complete your purchase securely via Paddle. Your payment information is encrypted and protected.</p>

                                <button
                                    type="button"
                                    id="paddle-pay-button"
                                    class="btn btn-primary btn-lg gap-2"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    @if($plan->trial_period_days > 0)
                                        Start {{ $plan->trial_period_days }}-Day Free Trial
                                    @else
                                        Pay $<span id="button-price">{{ number_format($discountedPrice, 2) }}</span>
                                    @endif
                                </button>

                                <p class="text-xs text-base-content/50 mt-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Payments processed by Paddle.com
                                </p>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div id="paddle-error" class="hidden">
                            <div class="alert alert-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Payment system failed to load. Please refresh the page or try again later.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback form for testing without Paddle -->
                    <form action="{{ route('subscription.subscribe') }}" method="POST" id="checkout-form" class="hidden">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="billing_cycle" value="{{ $billingCycle }}">
                        <input type="hidden" name="discount_code" x-model="discountCode">

                        <div class="space-y-4">
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Paddle payment integration is being configured. For testing, click the button below.</span>
                            </div>

                            <button type="submit" class="btn btn-primary w-full">
                                @if($plan->trial_period_days > 0)
                                    Start {{ $plan->trial_period_days }}-Day Free Trial
                                @else
                                    Complete Purchase
                                @endif
                            </button>
                        </div>
                    </form>

                    <!-- Security Badges -->
                    <div class="flex items-center justify-center gap-4 mt-6 pt-4 border-t border-base-200">
                        <div class="flex items-center gap-1 text-xs text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Secure Checkout
                        </div>
                        <div class="flex items-center gap-1 text-xs text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Money-back Guarantee
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-4 text-center">
                <a href="{{ route('subscription.pricing') }}" class="text-sm text-base-content/60 hover:text-primary">
                    &larr; Back to pricing
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkoutForm() {
    return {
        discountCode: '{{ $discount?->code ?? '' }}',
        discountApplied: {{ $discount ? 'true' : 'false' }},
        discountName: '{{ $discount?->name ?? '' }}',
        discountPercentage: '{{ $discount?->discount_percentage ?? 0 }}',
        discountError: '',
        applyingDiscount: false,
        originalPrice: {{ $price }},
        finalPrice: '{{ number_format($discountedPrice, 2) }}',
        savings: '{{ number_format($price - $discountedPrice, 2) }}',

        applyDiscount() {
            if (!this.discountCode) return;

            this.applyingDiscount = true;
            this.discountError = '';

            fetch('{{ route('subscription.apply-discount') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: this.discountCode,
                    plan_id: {{ $plan->id }},
                    billing_cycle: '{{ $billingCycle }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.discountApplied = true;
                    this.discountName = data.discount.name;
                    this.discountPercentage = data.discount.percentage;
                    this.finalPrice = data.discounted_price;
                    this.savings = data.savings;
                } else {
                    this.discountError = data.message || 'Invalid discount code';
                }
            })
            .catch(error => {
                this.discountError = 'An error occurred. Please try again.';
            })
            .finally(() => {
                this.applyingDiscount = false;
            });
        },

        removeDiscount() {
            this.discountApplied = false;
            this.discountCode = '';
            this.discountName = '';
            this.discountPercentage = 0;
            this.finalPrice = this.originalPrice.toFixed(2);
            this.savings = '0.00';
        }
    }
}

// Paddle configuration - All values from .env via config/paddle.php
const PADDLE_CONFIG = {
    clientToken: '{{ config('paddle.client_token') }}',
    priceId: '{{ $billingCycle === 'yearly' ? $plan->paddle_yearly_price_id : $plan->paddle_monthly_price_id }}',
    env: '{{ config('paddle.sandbox') ? 'sandbox' : 'production' }}',
    jsUrl: '{{ config('paddle.sandbox') ? config('paddle.js_urls.sandbox') : config('paddle.js_urls.production') }}',
    customerEmail: '{{ auth()->user()->email }}',
    successUrl: '{{ route('subscription.index') }}?success=1'
};

function showFallbackForm() {
    document.getElementById('paddle-checkout-container').classList.add('hidden');
    document.getElementById('checkout-form').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('paddle-loading').classList.add('hidden');
}

// Load Paddle.js and setup
document.addEventListener('DOMContentLoaded', function() {
    if (!PADDLE_CONFIG.clientToken || !PADDLE_CONFIG.priceId) {
        console.log('Paddle not configured');
        showFallbackForm();
        return;
    }

    // Load Paddle.js from config URL (sandbox or production)
    const script = document.createElement('script');
    script.src = PADDLE_CONFIG.jsUrl;
    script.async = true;

    script.onload = function() {
        console.log('Paddle.js loaded from:', PADDLE_CONFIG.jsUrl);

        // Set environment (sandbox or production)
        Paddle.Environment.set(PADDLE_CONFIG.env);

        // Setup Paddle with event callback
        Paddle.Setup({
            token: PADDLE_CONFIG.clientToken,
            eventCallback: function(event) {
                console.log('Paddle event:', event);
                if (event.name === 'checkout.completed') {
                    console.log('Checkout completed:', event.data);
                    window.location.href = PADDLE_CONFIG.successUrl;
                }
                if (event.name === 'checkout.error') {
                    console.error('Checkout error:', event.data);
                }
            }
        });

        console.log('Paddle setup complete - Environment:', PADDLE_CONFIG.env);

        // Hide loading, show button
        document.getElementById('paddle-loading').classList.add('hidden');
        document.getElementById('paddle-button-container').classList.remove('hidden');

        // Use actual price ID from plan
        const priceId = PADDLE_CONFIG.priceId || 'pri_01kg53j00zek0t6y5ga73dwnwf';
        console.log('Using price ID:', priceId);
        console.log('Customer email:', PADDLE_CONFIG.customerEmail);

        // Add click handler for Pay button (opens Paddle overlay checkout)
        document.getElementById('paddle-pay-button').addEventListener('click', function() {
            console.log('Opening Paddle checkout overlay...');

            Paddle.Checkout.open({
                items: [
                    {
                        priceId: priceId,
                        quantity: 1
                    }
                ],
                customer: {
                    email: PADDLE_CONFIG.customerEmail
                }
            });
        });
    };

    script.onerror = function() {
        console.error('Failed to load Paddle.js');
        document.getElementById('paddle-loading').classList.add('hidden');
        document.getElementById('paddle-error').classList.remove('hidden');
    };

    document.head.appendChild(script);
});
</script>
@endpush
@endsection
