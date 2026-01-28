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

                    <!-- Paddle Checkout will be loaded here -->
                    <div id="paddle-checkout-container" class="min-h-[400px] flex items-center justify-center">
                        <div class="text-center">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="text-sm text-base-content/60 mt-2">Loading secure checkout...</p>
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

// Initialize Paddle checkout when available
document.addEventListener('DOMContentLoaded', function() {
    // Check if Paddle is configured
    const paddlePriceId = '{{ $billingCycle === 'yearly' ? $plan->paddle_yearly_price_id : $plan->paddle_monthly_price_id }}';

    if (!paddlePriceId) {
        // Show fallback form if Paddle is not configured
        document.getElementById('paddle-checkout-container').classList.add('hidden');
        document.getElementById('checkout-form').classList.remove('hidden');
    } else {
        // Initialize Paddle checkout here when configured
        // Paddle.Checkout.open({
        //     items: [{ priceId: paddlePriceId, quantity: 1 }],
        //     customer: {
        //         email: '{{ auth()->user()->email }}'
        //     },
        //     customData: {
        //         tenant_id: '{{ auth()->user()->tenant_id }}',
        //         plan_id: '{{ $plan->id }}'
        //     }
        // });

        // For now, show the fallback form
        document.getElementById('paddle-checkout-container').classList.add('hidden');
        document.getElementById('checkout-form').classList.remove('hidden');
    }
});
</script>
@endpush
@endsection
