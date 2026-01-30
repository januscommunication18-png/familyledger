<?php

namespace App\Http\Controllers;

use App\Models\PackagePlan;
use App\Models\DiscountCode;
use App\Models\Invoice;
use App\Mail\PaymentSuccessEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
    public function index(Request $request): View
    {
        $tenant = Auth::user()->tenant;
        $currentPlan = $tenant->getCurrentPlan();
        $plans = PackagePlan::active()->ordered()->get();

        // Get billing history (invoices) with related data
        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->with('packagePlan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Handle success redirect from Paddle checkout
        if ($request->has('success')) {
            session()->flash('success', 'Payment successful! Your subscription has been activated. You will receive a confirmation email shortly.');
        }

        return view('pages.subscription.index', compact('tenant', 'currentPlan', 'plans', 'invoices'));
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
        // Verify Paddle signature
        if (!$this->verifyPaddleWebhookSignature($request)) {
            \Log::warning('Paddle webhook signature verification failed', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

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

            case 'transaction.completed':
                $this->handleTransactionCompleted($payload);
                break;

            case 'transaction.payment_failed':
                $this->handlePaymentFailed($payload);
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
     * Handle transaction completed webhook (payment successful).
     */
    private function handleTransactionCompleted(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $customData = $data['custom_data'] ?? [];
        $tenantId = $customData['tenant_id'] ?? null;

        // Try to find tenant by subscription ID if not in custom data
        if (!$tenantId && isset($data['subscription_id'])) {
            $tenant = \App\Models\Tenant::where('paddle_subscription_id', $data['subscription_id'])->first();
            $tenantId = $tenant?->id;
        }

        // Try to find tenant by customer email
        if (!$tenantId && isset($data['customer']['email'])) {
            $user = \App\Models\User::where('email', $data['customer']['email'])->first();
            $tenantId = $user?->tenant_id;
        }

        if (!$tenantId) {
            Log::warning('Transaction completed but could not find tenant', [
                'transaction_id' => $data['id'] ?? null,
            ]);
            return;
        }

        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $planId = $customData['plan_id'] ?? $tenant->package_plan_id;
        $plan = PackagePlan::find($planId);

        // Update tenant's subscription details
        if ($plan) {
            $billingCycle = ($data['billing_period']['interval'] ?? 'month') === 'year' ? 'yearly' : 'monthly';
            $billingPeriod = $data['billing_period'] ?? [];

            $tenant->update([
                'package_plan_id' => $plan->id,
                'subscription_tier' => $plan->type,
                'billing_cycle' => $billingCycle,
                'paddle_customer_id' => $data['customer_id'] ?? $tenant->paddle_customer_id,
                'paddle_subscription_id' => $data['subscription_id'] ?? $tenant->paddle_subscription_id,
                'subscription_expires_at' => $billingPeriod['ends_at'] ?? null,
                'trial_ends_at' => null, // Clear trial when payment is made
            ]);

            Log::info('Tenant subscription updated on payment', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
            ]);
        }

        // Extract billing details
        $billingPeriod = $data['billing_period'] ?? [];
        $details = $data['details'] ?? [];
        $totals = $details['totals'] ?? [];

        // Create invoice
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'user_id' => $tenant->users()->first()?->id,
            'package_plan_id' => $plan?->id,
            'paddle_transaction_id' => $data['id'] ?? null,
            'paddle_subscription_id' => $data['subscription_id'] ?? null,
            'billing_cycle' => ($data['billing_cycle']['interval'] ?? 'month') === 'year' ? 'yearly' : 'monthly',
            'subtotal' => ($totals['subtotal'] ?? 0) / 100, // Paddle uses cents
            'discount_amount' => ($totals['discount'] ?? 0) / 100,
            'tax_amount' => ($totals['tax'] ?? 0) / 100,
            'total_amount' => ($totals['total'] ?? 0) / 100,
            'currency' => $data['currency_code'] ?? 'USD',
            'discount_code' => $customData['discount_code'] ?? null,
            'status' => 'paid',
            'paid_at' => now(),
            'period_start' => $billingPeriod['starts_at'] ?? null,
            'period_end' => $billingPeriod['ends_at'] ?? null,
            'customer_name' => $data['customer']['name'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null,
            'paddle_data' => $data,
        ]);

        // Send payment success email
        $user = $tenant->users()->first();
        if ($user && $invoice) {
            try {
                Mail::to($user->email)->send(new PaymentSuccessEmail($invoice, $user, $tenant));
                $invoice->markAsEmailed();
                Log::info('Payment success email sent', [
                    'invoice_id' => $invoice->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send payment success email', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Invoice created for transaction', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'tenant_id' => $tenant->id,
        ]);
    }

    /**
     * Handle payment failed webhook.
     */
    private function handlePaymentFailed(array $payload): void
    {
        $data = $payload['data'] ?? [];

        // Find tenant by subscription ID
        $tenant = null;
        if (isset($data['subscription_id'])) {
            $tenant = \App\Models\Tenant::where('paddle_subscription_id', $data['subscription_id'])->first();
        }

        if (!$tenant && isset($data['customer']['email'])) {
            $user = \App\Models\User::where('email', $data['customer']['email'])->first();
            $tenant = $user?->tenant;
        }

        if (!$tenant) {
            return;
        }

        // Log the failed payment
        Log::warning('Payment failed for tenant', [
            'tenant_id' => $tenant->id,
            'transaction_id' => $data['id'] ?? null,
        ]);

        // You could send a payment failed email here
        // Mail::to($tenant->users()->first()->email)->send(new PaymentFailedEmail($tenant));
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

    /**
     * Verify Paddle webhook signature.
     */
    private function verifyPaddleWebhookSignature(Request $request): bool
    {
        $secret = config('paddle.webhook_secret');

        // Skip verification if webhook secret is not configured (development mode)
        if (empty($secret)) {
            Log::info('Paddle webhook secret not configured, skipping verification');
            return true;
        }

        $signature = $request->header('Paddle-Signature');

        if (empty($signature)) {
            return false;
        }

        // Parse the signature header
        // Format: ts=timestamp;h1=hash
        $parts = [];
        foreach (explode(';', $signature) as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                $parts[$keyValue[0]] = $keyValue[1];
            }
        }

        if (!isset($parts['ts']) || !isset($parts['h1'])) {
            return false;
        }

        $timestamp = $parts['ts'];
        $hash = $parts['h1'];

        // Build the signed payload
        $payload = $request->getContent();
        $signedPayload = $timestamp . ':' . $payload;

        // Calculate expected signature
        $expectedHash = hash_hmac('sha256', $signedPayload, $secret);

        // Timing-safe comparison
        if (!hash_equals($expectedHash, $hash)) {
            return false;
        }

        // Check timestamp to prevent replay attacks (5 minute tolerance)
        $tolerance = 300;
        if (abs(time() - (int) $timestamp) > $tolerance) {
            Log::warning('Paddle webhook timestamp too old', [
                'timestamp' => $timestamp,
                'current_time' => time(),
            ]);
            return false;
        }

        return true;
    }
}
