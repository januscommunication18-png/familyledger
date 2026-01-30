<?php

/**
 * EMAIL PREVIEW ROUTES - FOR DEVELOPMENT/TESTING ONLY
 *
 * Visit these URLs in your browser to preview email templates:
 * - /email-preview/drip/{campaignId}/{stepId}
 * - /email-preview/invoice
 * - /email-preview/reminder/{days}
 *
 * REMOVE THIS FILE IN PRODUCTION!
 */

use App\Mail\DripEmail;
use App\Mail\PaymentSuccessEmail;
use App\Mail\SubscriptionReminderEmail;
use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailStep;
use App\Models\Invoice;
use App\Models\PackagePlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Only enable in local/development environment
if (app()->environment('local', 'development', 'staging')) {

    Route::prefix('email-preview')->group(function () {

        // List all available previews
        Route::get('/', function () {
            $campaigns = DripCampaign::with('steps')->get();

            $html = '<html><head><title>Email Previews</title>';
            $html .= '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:20px}';
            $html .= 'h1{color:#4F46E5}h2{color:#6B7280;margin-top:30px}a{color:#4F46E5}';
            $html .= 'ul{line-height:2}.badge{background:#E0E7FF;color:#4338CA;padding:2px 8px;border-radius:4px;font-size:12px}</style>';
            $html .= '</head><body>';
            $html .= '<h1>📧 Email Preview</h1>';
            $html .= '<p>Click any link below to preview the email template in your browser.</p>';

            // Drip Campaigns
            $html .= '<h2>Drip Campaign Emails</h2>';
            foreach ($campaigns as $campaign) {
                $html .= "<h3>{$campaign->name} <span class='badge'>{$campaign->trigger_type}</span></h3>";
                $html .= '<ul>';
                foreach ($campaign->steps as $step) {
                    $html .= "<li><a href='/email-preview/drip/{$campaign->id}/{$step->id}'>{$step->subject}</a></li>";
                }
                $html .= '</ul>';
            }

            // Invoice
            $html .= '<h2>Invoice / Payment Emails</h2>';
            $html .= '<ul><li><a href="/email-preview/invoice">Payment Success / Invoice Email</a></li></ul>';

            // Reminders
            $html .= '<h2>Subscription Reminder Emails</h2>';
            $html .= '<ul>';
            $html .= '<li><a href="/email-preview/reminder/7">7 Days Before Expiry</a></li>';
            $html .= '<li><a href="/email-preview/reminder/3">3 Days Before Expiry</a></li>';
            $html .= '<li><a href="/email-preview/reminder/0">Expiry Day (0 days)</a></li>';
            $html .= '</ul>';

            $html .= '<hr style="margin-top:40px"><p style="color:#9CA3AF;font-size:12px">⚠️ These previews are only available in development/staging environments.</p>';
            $html .= '</body></html>';

            return $html;
        });

        // Preview drip email
        Route::get('/drip/{campaignId}/{stepId}', function ($campaignId, $stepId) {
            $step = DripEmailStep::findOrFail($stepId);
            $user = User::first();
            $tenant = Tenant::first();

            return new DripEmail($step, $user, $tenant);
        });

        // Preview invoice email
        Route::get('/invoice', function () {
            $tenant = Tenant::first();
            $user = User::first();
            $plan = PackagePlan::first();

            // Create a fake invoice for preview
            $invoice = new Invoice([
                'invoice_number' => 'INV-PREVIEW-001',
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'package_plan_id' => $plan?->id,
                'billing_cycle' => 'monthly',
                'subtotal' => 29.99,
                'discount_amount' => 5.00,
                'tax_amount' => 0,
                'total_amount' => 24.99,
                'currency' => 'USD',
                'status' => 'paid',
                'paid_at' => now(),
                'period_start' => now(),
                'period_end' => now()->addMonth(),
                'customer_name' => $user->name,
                'customer_email' => $user->email,
            ]);

            $invoice->setRelation('tenant', $tenant);
            $invoice->setRelation('user', $user);
            $invoice->setRelation('packagePlan', $plan);

            return new PaymentSuccessEmail($invoice, $user, $tenant);
        });

        // Preview reminder email
        Route::get('/reminder/{days}', function ($days) {
            $tenant = Tenant::first();
            $user = User::first();

            return new SubscriptionReminderEmail($tenant, $user, (int) $days);
        });
    });
}
