<?php

namespace App\Console\Commands;

use App\Jobs\SendDripEmail;
use App\Mail\PaymentSuccessEmail;
use App\Mail\SubscriptionReminderEmail;
use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailStep;
use App\Models\Invoice;
use App\Models\PackagePlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestDripEmails extends Command
{
    protected $signature = 'test:emails
                            {--type=all : Type of emails to test (all, drip, invoice, reminder)}
                            {--email= : Email address to send test emails to}
                            {--list : Just list available emails without sending}
                            {--log : Force use log driver (ignores .env setting)}';

    protected $description = 'Test all email templates by sending them or logging them';

    public function handle(): int
    {
        $type = $this->option('type');
        $testEmail = $this->option('email') ?? 'test@example.com';
        $listOnly = $this->option('list');
        $forceLog = $this->option('log');

        // Force log driver if --log option is used
        if ($forceLog) {
            config(['mail.default' => 'log']);
        }

        $this->info('');
        $this->info('==============================================');
        $this->info('  EMAIL TESTING TOOL');
        $this->info('==============================================');
        $this->info('');
        $this->info("Mail Driver: " . config('mail.default'));
        if ($forceLog) {
            $this->info("  (forced via --log option)");
        }
        $this->info("Test Email: {$testEmail}");
        $this->info('');

        if ($listOnly) {
            $this->listAllEmails();
            return 0;
        }

        // Get or create test data
        $tenant = Tenant::first();
        $user = User::first();
        $plan = PackagePlan::first();

        if (!$tenant || !$user) {
            $this->error('No tenant or user found in database. Please create test data first.');
            return 1;
        }

        $results = [];

        if (in_array($type, ['all', 'drip'])) {
            $results = array_merge($results, $this->testDripEmails($testEmail, $user, $tenant));
        }

        if (in_array($type, ['all', 'invoice'])) {
            $results = array_merge($results, $this->testInvoiceEmail($testEmail, $user, $tenant, $plan));
        }

        if (in_array($type, ['all', 'reminder'])) {
            $results = array_merge($results, $this->testReminderEmails($testEmail, $user, $tenant, $plan));
        }

        // Summary
        $this->info('');
        $this->info('==============================================');
        $this->info('  SUMMARY');
        $this->info('==============================================');

        $this->table(
            ['Email Type', 'Subject', 'Status'],
            $results
        );

        $this->info('');

        if (config('mail.default') === 'log') {
            $this->warn('Emails were logged to: storage/logs/laravel.log');
            $this->info('');
            $this->info('To view emails, run:');
            $this->info('  tail -n 500 storage/logs/laravel.log | grep -A 100 "Message-ID"');
            $this->info('');
            $this->info('Or use the command:');
            $this->info('  php artisan test:emails --type=all --list');
        } else {
            $this->info("Emails were sent to: {$testEmail}");
        }

        return 0;
    }

    protected function listAllEmails(): void
    {
        $this->info('AVAILABLE EMAIL TYPES:');
        $this->info('');

        // Drip Campaigns
        $this->info('1. DRIP CAMPAIGN EMAILS:');
        $campaigns = DripCampaign::with('steps')->get();

        if ($campaigns->isEmpty()) {
            $this->warn('   No drip campaigns found.');
        } else {
            foreach ($campaigns as $campaign) {
                $this->info("   Campaign: {$campaign->name} ({$campaign->trigger_type})");
                foreach ($campaign->steps as $step) {
                    $delay = $step->delay_days > 0 ? "{$step->delay_days}d" : ($step->delay_hours > 0 ? "{$step->delay_hours}h" : "immediate");
                    $this->info("     - Step {$step->sequence_order}: {$step->subject} [{$delay}]");
                }
            }
        }

        $this->info('');
        $this->info('2. INVOICE EMAILS:');
        $this->info('   - Payment Success / Invoice Email');

        $this->info('');
        $this->info('3. SUBSCRIPTION REMINDER EMAILS:');
        $this->info('   - 7 Days Before Expiry');
        $this->info('   - 3 Days Before Expiry');
        $this->info('   - Expiry Day (0 days)');

        $this->info('');
        $this->info('TO TEST EMAILS:');
        $this->info('  php artisan test:emails --type=all');
        $this->info('  php artisan test:emails --type=drip');
        $this->info('  php artisan test:emails --type=invoice');
        $this->info('  php artisan test:emails --type=reminder');
    }

    protected function testDripEmails(string $testEmail, User $user, Tenant $tenant): array
    {
        $results = [];
        $this->info('Testing Drip Campaign Emails...');

        $campaigns = DripCampaign::active()->with('steps')->get();

        if ($campaigns->isEmpty()) {
            $this->warn('No active drip campaigns found.');
            return [['Drip', 'N/A', 'No campaigns']];
        }

        foreach ($campaigns as $campaign) {
            $this->info("  Campaign: {$campaign->name}");

            foreach ($campaign->steps as $step) {
                try {
                    // Send directly without queue for testing
                    $mailable = new \App\Mail\DripEmail($step, $user, $tenant);
                    Mail::to($testEmail)->send($mailable);

                    $results[] = ['Drip: ' . $campaign->name, $step->subject, '✓ Sent'];
                    $this->info("    ✓ Step {$step->sequence_order}: {$step->subject}");

                    // Small delay to avoid rate limiting (when using SMTP)
                    if (config('mail.default') !== 'log') {
                        usleep(500000); // 0.5 second
                    }
                } catch (\Exception $e) {
                    $results[] = ['Drip: ' . $campaign->name, $step->subject, '✗ ' . $e->getMessage()];
                    $this->error("    ✗ Step {$step->sequence_order}: {$e->getMessage()}");
                }
            }
        }

        return $results;
    }

    protected function testInvoiceEmail(string $testEmail, User $user, Tenant $tenant, ?PackagePlan $plan): array
    {
        $results = [];
        $this->info('Testing Invoice Email...');

        // Create a fake invoice for testing
        $invoice = new Invoice([
            'invoice_number' => 'INV-TEST-' . now()->format('YmdHis'),
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
            'customer_email' => $testEmail,
        ]);

        // Set the relationships manually for the test
        $invoice->setRelation('tenant', $tenant);
        $invoice->setRelation('user', $user);
        $invoice->setRelation('packagePlan', $plan);

        try {
            Mail::to($testEmail)->send(new PaymentSuccessEmail($invoice, $user, $tenant));
            $results[] = ['Invoice', 'Payment Success / Invoice', '✓ Sent'];
            $this->info("  ✓ Payment Success Email");

            // Small delay to avoid rate limiting
            if (config('mail.default') !== 'log') {
                usleep(500000); // 0.5 second
            }
        } catch (\Exception $e) {
            $results[] = ['Invoice', 'Payment Success / Invoice', '✗ ' . $e->getMessage()];
            $this->error("  ✗ Payment Success Email: {$e->getMessage()}");
        }

        return $results;
    }

    protected function testReminderEmails(string $testEmail, User $user, Tenant $tenant, ?PackagePlan $plan): array
    {
        $results = [];
        $this->info('Testing Subscription Reminder Emails...');

        $daysRemainingOptions = [7, 3, 0];

        foreach ($daysRemainingOptions as $daysRemaining) {
            try {
                // SubscriptionReminderEmail expects: Tenant, User, int $daysRemaining
                Mail::to($testEmail)->send(new SubscriptionReminderEmail($tenant, $user, $daysRemaining));

                $label = $daysRemaining === 0 ? 'Expiry Day' : "{$daysRemaining} Days Remaining";
                $results[] = ['Reminder', "Subscription Renewal ({$label})", '✓ Sent'];
                $this->info("  ✓ {$label} Reminder");

                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 second
            } catch (\Exception $e) {
                $results[] = ['Reminder', "{$daysRemaining} Days Reminder", '✗ ' . $e->getMessage()];
                $this->error("  ✗ {$daysRemaining} Days Reminder: {$e->getMessage()}");
            }
        }

        return $results;
    }
}
