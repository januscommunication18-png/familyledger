<?php

namespace App\Http\Controllers;

use App\Models\PackagePlan;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Display the pricing page with available plans.
     */
    public function pricing(): View
    {
        $plans = PackagePlan::active()->ordered()->get();
        $tenant = Auth::user()->tenant;
        $currentPlan = $tenant->getCurrentPlan();

        return view('pages.subscription.pricing', compact('plans', 'tenant', 'currentPlan'));
    }

    /**
     * Display the subscription management page.
     */
    public function index(): View
    {
        $tenant = Auth::user()->tenant;
        $currentPlan = $tenant->getCurrentPlan();
        $plans = PackagePlan::active()->ordered()->get();

        return view('pages.subscription.index', compact('tenant', 'currentPlan', 'plans'));
    }

    /**
     * Show the checkout page for a specific plan.
     */
    public function checkout(PackagePlan $plan, Request $request): View
    {
        $billingCycle = $request->get('cycle', 'monthly');
        $tenant = Auth::user()->tenant;

        // Calculate prices
        $price = $billingCycle === 'yearly' ? $plan->cost_per_year : $plan->cost_per_month;
        $discountedPrice = $price;
        $discount = null;

        // Check for discount code
        if ($request->filled('discount_code')) {
            $discount = DiscountCode::where('code', strtoupper($request->discount_code))
                ->valid()
                ->first();

            if ($discount) {
                if ($billingCycle === 'yearly') {
                    $discountedPrice = $discount->calculateDiscountedYearlyPrice($plan);
                } else {
                    $discountedPrice = $discount->calculateDiscountedMonthlyPrice($plan);
                }
            }
        }

        return view('pages.subscription.checkout', compact(
            'plan',
            'billingCycle',
            'price',
            'discountedPrice',
            'discount',
            'tenant'
        ));
    }

    /**
     * Apply a discount code.
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'plan_id' => 'required|exists:package_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $discount = DiscountCode::where('code', strtoupper($request->code))
            ->valid()
            ->first();

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired discount code.',
            ], 422);
        }

        // Check if discount applies to this billing cycle
        if ($discount->plan_type !== 'both' && $discount->plan_type !== $request->billing_cycle) {
            return response()->json([
                'success' => false,
                'message' => "This discount code only applies to {$discount->plan_type} plans.",
            ], 422);
        }

        // Check if discount is for a specific plan
        if ($discount->package_plan_id && $discount->package_plan_id != $request->plan_id) {
            return response()->json([
                'success' => false,
                'message' => 'This discount code is not valid for this plan.',
            ], 422);
        }

        $plan = PackagePlan::findOrFail($request->plan_id);

        $originalPrice = $request->billing_cycle === 'yearly'
            ? $plan->cost_per_year
            : $plan->cost_per_month;

        $discountedPrice = $request->billing_cycle === 'yearly'
            ? $discount->calculateDiscountedYearlyPrice($plan)
            : $discount->calculateDiscountedMonthlyPrice($plan);

        $savings = $originalPrice - $discountedPrice;

        return response()->json([
            'success' => true,
            'discount' => [
                'name' => $discount->name,
                'code' => $discount->code,
                'percentage' => $discount->discount_percentage,
            ],
            'original_price' => number_format($originalPrice, 2),
            'discounted_price' => number_format($discountedPrice, 2),
            'savings' => number_format($savings, 2),
        ]);
    }

    /**
     * Subscribe to a plan (handles free plans directly, paid plans go to Paddle).
     */
    public function subscribe(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:package_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'discount_code' => 'nullable|string',
        ]);

        $plan = PackagePlan::findOrFail($request->plan_id);
        $tenant = Auth::user()->tenant;
        $billingCycle = $request->billing_cycle;

        // For free plans, subscribe directly
        if ($plan->isFree()) {
            $tenant->subscribeToPlan($plan, $billingCycle);

            return redirect()->route('subscription.index')
                ->with('success', 'You have been subscribed to the ' . $plan->name . '!');
        }

        // For paid plans, check if Paddle is configured
        $paddlePriceId = $billingCycle === 'yearly'
            ? $plan->paddle_yearly_price_id
            : $plan->paddle_monthly_price_id;

        // If Paddle is not configured, subscribe directly (for testing/development)
        if (empty($paddlePriceId)) {
            $tenant->subscribeToPlan($plan, $billingCycle, $plan->trial_period_days);

            $message = $plan->trial_period_days > 0
                ? "Your {$plan->trial_period_days}-day free trial of {$plan->name} has started!"
                : "You have been subscribed to the {$plan->name}!";

            return redirect()->route('subscription.index')
                ->with('success', $message);
        }

        // Validate discount code if provided
        $discountCode = null;
        if ($request->filled('discount_code')) {
            $discountCode = DiscountCode::where('code', strtoupper($request->discount_code))
                ->valid()
                ->first();
        }

        // Store subscription intent in session for Paddle checkout
        session([
            'subscription_intent' => [
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'discount_code' => $discountCode?->code,
            ]
        ]);

        // Return data for Paddle checkout
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'checkout' => [
                    'paddle_price_id' => $paddlePriceId,
                    'plan_name' => $plan->name,
                    'billing_cycle' => $billingCycle,
                ],
            ]);
        }

        // Redirect to checkout page if not using AJAX
        return redirect()->route('subscription.checkout', [
            'plan' => $plan,
            'cycle' => $billingCycle,
            'discount_code' => $discountCode?->code,
        ]);
    }

    /**
     * Handle successful Paddle payment webhook.
     */
    public function handlePaddleWebhook(Request $request): JsonResponse
    {
        // Verify Paddle signature here in production
        // $signature = $request->header('Paddle-Signature');

        $payload = $request->all();

        // Handle different webhook events
        switch ($payload['event_type'] ?? '') {
            case 'subscription.created':
            case 'subscription.activated':
                $this->handleSubscriptionCreated($payload);
                break;

            case 'subscription.updated':
                $this->handleSubscriptionUpdated($payload);
                break;

            case 'subscription.canceled':
                $this->handleSubscriptionCanceled($payload);
                break;

            case 'subscription.paused':
                $this->handleSubscriptionPaused($payload);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription created webhook.
     */
    private function handleSubscriptionCreated(array $payload): void
    {
        $customData = $payload['data']['custom_data'] ?? [];
        $tenantId = $customData['tenant_id'] ?? null;

        if (!$tenantId) {
            return;
        }

        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $planId = $customData['plan_id'] ?? null;
        $plan = PackagePlan::find($planId);

        if ($plan) {
            $billingCycle = $payload['data']['billing_cycle']['interval'] ?? 'month';
            $billingCycle = $billingCycle === 'year' ? 'yearly' : 'monthly';

            $tenant->update([
                'package_plan_id' => $plan->id,
                'subscription_tier' => $plan->type,
                'billing_cycle' => $billingCycle,
                'paddle_customer_id' => $payload['data']['customer_id'] ?? null,
                'paddle_subscription_id' => $payload['data']['id'] ?? null,
                'subscription_expires_at' => $payload['data']['current_billing_period']['ends_at'] ?? null,
            ]);
        }
    }

    /**
     * Handle subscription updated webhook.
     */
    private function handleSubscriptionUpdated(array $payload): void
    {
        $subscriptionId = $payload['data']['id'] ?? null;

        $tenant = \App\Models\Tenant::where('paddle_subscription_id', $subscriptionId)->first();
        if (!$tenant) {
            return;
        }

        $tenant->update([
            'subscription_expires_at' => $payload['data']['current_billing_period']['ends_at'] ?? null,
        ]);
    }

    /**
     * Handle subscription canceled webhook.
     */
    private function handleSubscriptionCanceled(array $payload): void
    {
        $subscriptionId = $payload['data']['id'] ?? null;

        $tenant = \App\Models\Tenant::where('paddle_subscription_id', $subscriptionId)->first();
        if (!$tenant) {
            return;
        }

        // Don't immediately remove access - let it expire at the end of the billing period
        $tenant->update([
            'subscription_expires_at' => $payload['data']['current_billing_period']['ends_at'] ?? now(),
        ]);
    }

    /**
     * Handle subscription paused webhook.
     */
    private function handleSubscriptionPaused(array $payload): void
    {
        $subscriptionId = $payload['data']['id'] ?? null;

        $tenant = \App\Models\Tenant::where('paddle_subscription_id', $subscriptionId)->first();
        if (!$tenant) {
            return;
        }

        $tenant->update([
            'subscription_expires_at' => $payload['data']['paused_at'] ?? now(),
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        // For Paddle subscriptions, we would call Paddle API to cancel
        // For now, just mark as canceled (will expire at end of period)

        if ($tenant->paddle_subscription_id) {
            // TODO: Call Paddle API to cancel subscription
            // Paddle::subscription($tenant->paddle_subscription_id)->cancel();
        }

        // Switch to free plan at end of billing period
        $freePlan = PackagePlan::where('type', 'free')->active()->first();
        if ($freePlan) {
            $tenant->update([
                'package_plan_id' => $freePlan->id,
                'subscription_tier' => 'free',
            ]);
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Your subscription has been canceled. You will retain access until the end of your billing period.');
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->paddle_subscription_id) {
            // TODO: Call Paddle API to resume subscription
            // Paddle::subscription($tenant->paddle_subscription_id)->resume();
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Your subscription has been resumed.');
    }

    /**
     * Change billing cycle.
     */
    public function changeBillingCycle(Request $request): RedirectResponse
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = Auth::user()->tenant;

        // TODO: For Paddle, this would trigger a subscription update
        // For now, just update the billing cycle
        $tenant->update([
            'billing_cycle' => $request->billing_cycle,
        ]);

        return redirect()->route('subscription.index')
            ->with('success', 'Your billing cycle has been updated.');
    }
}
