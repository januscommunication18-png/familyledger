<?php

namespace App\Console\Commands;

use App\Mail\AdminDailyDigestMail;
use App\Models\Backoffice\Admin;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminDailyDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:send-daily-digest
                            {--admin= : Send only to a specific admin ID}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily digest email to backoffice admins with sign-ups, payments, and business stats';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting admin daily digest emails...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $specificAdminId = $this->option('admin');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
            $this->newLine();
        }

        // Gather data
        $this->info('Gathering business data...');

        // New sign-ups today
        $newSignups = Tenant::whereDate('created_at', now()->toDateString())
            ->with(['users' => function ($q) {
                $q->orderBy('created_at')->limit(1);
            }])
            ->get();

        // Add owner relationship
        foreach ($newSignups as $tenant) {
            $tenant->owner = User::where('tenant_id', $tenant->id)->orderBy('created_at')->first();
        }

        // Today's payments
        $todayPayments = Invoice::whereDate('paid_at', now()->toDateString())
            ->where('status', 'paid')
            ->with(['user', 'packagePlan'])
            ->get();

        // Pending payments
        $pendingPayments = Invoice::where('status', 'pending')
            ->with(['user', 'packagePlan'])
            ->orderBy('due_date')
            ->get();

        // Calculate stats
        $stats = [
            'total_tenants' => Tenant::where('is_active', true)->count(),
            'today_revenue' => $todayPayments->sum('total_amount'),
            'pending_amount' => $pendingPayments->sum('total_amount'),
            'today_logins' => User::whereDate('last_login_at', now()->toDateString())->count(),
            'month_signups' => Tenant::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'month_revenue' => Invoice::whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'active_subscriptions' => Tenant::where('is_active', true)
                ->whereNotNull('subscription_expires_at')
                ->where('subscription_expires_at', '>', now())
                ->count(),
        ];

        $this->info("  New sign-ups today: {$newSignups->count()}");
        $this->info("  Payments today: {$todayPayments->count()} (\${$stats['today_revenue']})");
        $this->info("  Pending payments: {$pendingPayments->count()} (\${$stats['pending_amount']})");
        $this->info("  Logins today: {$stats['today_logins']}");
        $this->newLine();

        // Get admins to send to
        $adminsQuery = Admin::where('is_active', true);
        if ($specificAdminId) {
            $adminsQuery->where('id', $specificAdminId);
        }
        $admins = $adminsQuery->get();

        $this->info("Sending to {$admins->count()} admin(s)...");
        $this->newLine();

        $emailsSent = 0;
        $emailsFailed = 0;

        foreach ($admins as $admin) {
            $this->line("  - {$admin->email}");

            if (!$dryRun) {
                try {
                    Mail::to($admin->email)->send(new AdminDailyDigestMail(
                        $admin,
                        $newSignups,
                        $todayPayments,
                        $pendingPayments,
                        $stats
                    ));

                    $emailsSent++;
                    $this->info("    ✓ Email sent successfully");

                    Log::info('Admin daily digest sent', [
                        'admin_id' => $admin->id,
                        'email' => $admin->email,
                        'new_signups' => $newSignups->count(),
                        'today_payments' => $todayPayments->count(),
                        'pending_payments' => $pendingPayments->count(),
                    ]);

                } catch (\Exception $e) {
                    $emailsFailed++;
                    $this->error("    ✗ Failed: {$e->getMessage()}");

                    Log::error('Failed to send admin daily digest', [
                        'admin_id' => $admin->id,
                        'email' => $admin->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $emailsSent++;
                $this->info("    [DRY RUN] Would send email");
            }
        }

        $this->newLine();
        $this->info("========================================");
        $this->info("Admin Daily Digest Complete!");
        $this->info("========================================");
        $this->info("Emails sent: {$emailsSent}");
        if ($emailsFailed > 0) {
            $this->error("Emails failed: {$emailsFailed}");
        }

        return $emailsFailed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
