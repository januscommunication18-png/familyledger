@extends('layouts.dashboard')

@section('title', 'Subscription')
@section('page-name', 'Subscription')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Billing & Subscription</li>
@endsection

@section('page-title', 'Billing & Subscription')
@section('page-description', 'Manage your subscription plan and billing details.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Current Plan Card -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="card-title mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Current Plan
                    </h2>

                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold">{{ $currentPlan->name ?? 'Free Plan' }}</span>
                        <span class="badge {{ $tenant->onPaidPlan() ? 'badge-primary' : 'badge-ghost' }}">
                            {{ ucfirst($tenant->subscription_tier ?? 'free') }}
                        </span>
                    </div>

                    @if($currentPlan && $currentPlan->description)
                        <p class="text-sm text-base-content/60 mt-1">{{ $currentPlan->description }}</p>
                    @endif
                </div>

                <div class="text-right">
                    @if($tenant->onPaidPlan())
                        <div class="text-3xl font-bold">
                            ${{ number_format($tenant->billing_cycle === 'yearly' ? $currentPlan->cost_per_year / 12 : $currentPlan->cost_per_month, 2) }}
                            <span class="text-base font-normal text-base-content/60">/month</span>
                        </div>
                        <p class="text-sm text-base-content/60">
                            Billed {{ $tenant->billing_cycle ?? 'monthly' }}
                        </p>
                    @else
                        <div class="text-3xl font-bold text-success">Free</div>
                    @endif
                </div>
            </div>

            <!-- Trial/Subscription Status -->
            @if($tenant->onTrial())
                <div class="mt-4 p-4 bg-warning/10 border border-warning/30 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-warning">Trial Period</p>
                            <p class="text-sm text-base-content/70">
                                Your trial ends in <strong>{{ $tenant->trialDaysRemaining() }} days</strong>
                                ({{ $tenant->trial_ends_at->format('M d, Y') }})
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($tenant->onPaidPlan() && $tenant->subscription_expires_at)
                <div class="mt-4 p-4 bg-base-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="text-sm text-base-content/70">
                                Next billing date: <strong>{{ $tenant->subscription_expires_at->format('M d, Y') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('subscription.pricing') }}" class="btn btn-primary btn-sm">
                    @if($tenant->onFreePlan())
                        Upgrade Plan
                    @else
                        Change Plan
                    @endif
                </a>
                @if($tenant->onPaidPlan() && $tenant->paddle_subscription_id)
                    <form action="{{ route('subscription.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription? You will retain access until the end of your billing period.')">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-error">
                            Cancel Subscription
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Plan Features -->
    @if($currentPlan)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Plan Features
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Family Circles</div>
                    <div class="stat-value text-primary">{{ $currentPlan->getFormattedLimit('family_circles_limit') }}</div>
                    <div class="stat-desc">Available in your plan</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Family Members</div>
                    <div class="stat-value text-primary">{{ $currentPlan->getFormattedLimit('family_members_limit') }}</div>
                    <div class="stat-desc">Per family circle</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Document Uploads</div>
                    <div class="stat-value text-primary">{{ $currentPlan->getFormattedLimit('document_storage_limit') }}</div>
                    <div class="stat-desc">Storage limit</div>
                </div>
            </div>

            @php
                $reminderFeatures = $currentPlan->reminder_features ?? [];
            @endphp

            <div class="mt-4">
                <h3 class="text-sm font-semibold text-base-content/80 mb-2">Reminder Features</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="badge {{ in_array('email_reminder', $reminderFeatures) ? 'badge-success' : 'badge-ghost' }}">
                        Email Reminders
                    </span>
                    <span class="badge {{ in_array('push_notification', $reminderFeatures) ? 'badge-success' : 'badge-ghost' }}">
                        Push Notifications
                    </span>
                    <span class="badge {{ in_array('sms_reminder', $reminderFeatures) ? 'badge-success' : 'badge-ghost' }}">
                        SMS Reminders
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Usage Statistics -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Current Usage
            </h2>

            @php
                $familyMembersCount = \App\Models\FamilyMember::where('tenant_id', $tenant->id)->count();
                $documentsCount = \App\Models\MemberDocument::where('tenant_id', $tenant->id)->count()
                    + \App\Models\LegalDocument::where('tenant_id', $tenant->id)->count()
                    + \App\Models\AssetDocument::where('tenant_id', $tenant->id)->count();
                $familyMembersLimit = $currentPlan ? $currentPlan->family_members_limit : 0;
                $documentsLimit = $currentPlan ? $currentPlan->document_storage_limit : 0;
            @endphp

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium">Family Members</span>
                        <span class="text-sm text-base-content/60">
                            {{ $familyMembersCount }} / {{ $familyMembersLimit === 0 ? 'Unlimited' : $familyMembersLimit }}
                        </span>
                    </div>
                    @if($familyMembersLimit > 0)
                        <progress class="progress progress-primary w-full" value="{{ $familyMembersCount }}" max="{{ $familyMembersLimit }}"></progress>
                    @else
                        <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                    @endif
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium">Document Uploads</span>
                        <span class="text-sm text-base-content/60">
                            {{ $documentsCount }} / {{ $documentsLimit === 0 ? 'Unlimited' : $documentsLimit }}
                        </span>
                    </div>
                    @if($documentsLimit > 0)
                        <progress class="progress progress-primary w-full" value="{{ $documentsCount }}" max="{{ $documentsLimit }}"></progress>
                    @else
                        <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History (Placeholder) -->
    @if($tenant->onPaidPlan())
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Billing History
            </h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">
                                Billing history will be available after your first payment.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Compare Plans CTA -->
    <div class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-xl p-6 text-center">
        <h3 class="text-lg font-semibold mb-2">Need more features?</h3>
        <p class="text-sm text-base-content/70 mb-4">Compare all plans and find the one that's right for your family.</p>
        <a href="{{ route('subscription.pricing') }}" class="btn btn-primary btn-sm">
            View All Plans
        </a>
    </div>
</div>
@endsection
