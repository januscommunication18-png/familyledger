@extends('layouts.dashboard')

@section('title', 'Pricing Plans')
@section('page-name', 'Subscription')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Pricing Plans</li>
@endsection

@section('page-title', 'Choose Your Plan')
@section('page-description', 'Select the plan that best fits your family\'s needs.')

@section('content')
<div class="space-y-8" x-data="{ billingCycle: 'monthly' }">
    <!-- Billing Cycle Toggle -->
    <div class="flex justify-center">
        <div class="bg-base-100 p-1 rounded-xl shadow-sm inline-flex">
            <button
                @click="billingCycle = 'monthly'"
                :class="billingCycle === 'monthly' ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content'"
                class="px-6 py-2 rounded-lg text-sm font-medium transition-colors"
            >
                Monthly
            </button>
            <button
                @click="billingCycle = 'yearly'"
                :class="billingCycle === 'yearly' ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content'"
                class="px-6 py-2 rounded-lg text-sm font-medium transition-colors"
            >
                Yearly
                <span class="ml-1 text-xs bg-success/20 text-success px-2 py-0.5 rounded-full">Save 20%</span>
            </button>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($plans), 3) }} gap-6 max-w-5xl mx-auto">
        @foreach($plans as $plan)
            <div class="card bg-base-100 shadow-sm border {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-primary ring-2 ring-primary/20' : 'border-base-200' }} relative">
                @if($currentPlan && $currentPlan->id === $plan->id)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="badge badge-primary">Current Plan</span>
                    </div>
                @endif

                @if($plan->type === 'paid')
                    <div class="absolute -top-3 right-4">
                        <span class="badge badge-secondary">Popular</span>
                    </div>
                @endif

                <div class="card-body">
                    <!-- Plan Header -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold">{{ $plan->name }}</h3>
                        @if($plan->description)
                            <p class="text-sm text-base-content/60 mt-1">{{ $plan->description }}</p>
                        @endif

                        <div class="mt-4">
                            <template x-if="billingCycle === 'monthly'">
                                <div>
                                    <span class="text-4xl font-bold">${{ number_format($plan->cost_per_month, 2) }}</span>
                                    <span class="text-base-content/60">/month</span>
                                </div>
                            </template>
                            <template x-if="billingCycle === 'yearly'">
                                <div>
                                    <span class="text-4xl font-bold">${{ number_format($plan->cost_per_year / 12, 2) }}</span>
                                    <span class="text-base-content/60">/month</span>
                                    <p class="text-sm text-base-content/60 mt-1">
                                        ${{ number_format($plan->cost_per_year, 2) }} billed yearly
                                    </p>
                                </div>
                            </template>
                        </div>

                        @if($plan->trial_period_days > 0 && $plan->type === 'paid')
                            <p class="text-sm text-success mt-2">{{ $plan->trial_period_days }}-day free trial</p>
                        @endif
                    </div>

                    <!-- Features List -->
                    <ul class="space-y-3 mb-6 flex-1">
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>
                                <strong>{{ $plan->getFormattedLimit('family_circles_limit') }}</strong> Family Circle{{ $plan->family_circles_limit !== 1 ? 's' : '' }}
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>
                                <strong>{{ $plan->getFormattedLimit('family_members_limit') }}</strong> Family Member{{ $plan->family_members_limit !== 1 ? 's' : '' }}
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>
                                <strong>{{ $plan->getFormattedLimit('document_storage_limit') }}</strong> Document Upload{{ $plan->document_storage_limit !== 1 ? 's' : '' }}
                            </span>
                        </li>

                        @php
                            $reminderFeatures = $plan->reminder_features ?? [];
                        @endphp

                        @if(in_array('email_reminder', $reminderFeatures))
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Email Reminders</span>
                            </li>
                        @endif

                        @if(in_array('push_notification', $reminderFeatures))
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Push Notifications</span>
                            </li>
                        @endif

                        @if(in_array('sms_reminder', $reminderFeatures))
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>SMS Reminders</span>
                            </li>
                        @else
                            <li class="flex items-start gap-3 text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>SMS Reminders</span>
                            </li>
                        @endif
                    </ul>

                    <!-- Action Button -->
                    <div class="card-actions">
                        @if($currentPlan && $currentPlan->id === $plan->id)
                            <button class="btn btn-outline btn-primary w-full" disabled>
                                Current Plan
                            </button>
                        @elseif($plan->isFree())
                            <form action="{{ route('subscription.subscribe') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_cycle" x-bind:value="billingCycle">
                                <button type="submit" class="btn btn-outline w-full">
                                    Switch to Free
                                </button>
                            </form>
                        @else
                            <a
                                :href="'{{ route('subscription.checkout', $plan) }}?cycle=' + billingCycle"
                                class="btn btn-primary w-full"
                            >
                                @if($plan->trial_period_days > 0)
                                    Start Free Trial
                                @else
                                    Get Started
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Features Comparison (Optional) -->
    <div class="bg-base-100 rounded-xl shadow-sm p-6 max-w-4xl mx-auto">
        <h3 class="text-lg font-semibold text-center mb-6">All Plans Include</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Secure Data Storage
            </div>
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Mobile Access
            </div>
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Data Export
            </div>
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                24/7 Support
            </div>
        </div>
    </div>

    <!-- FAQ or Money-back Guarantee -->
    <div class="text-center text-sm text-base-content/60">
        <p>Questions? <a href="#" class="text-primary hover:underline">Contact our support team</a></p>
        <p class="mt-1">30-day money-back guarantee on all paid plans</p>
    </div>
</div>
@endsection
