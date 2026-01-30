<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderEmail;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:send-reminders
                            {--days=* : Specific days to send reminders for (e.g., 7, 3, 0)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send subscription reminder emails (7 days, 3 days, 0 days before expiry, and on expiration day)';

    /**
     * Days before expiry to send reminders (negative values = days after expiry).
     */
    protected array $reminderDays = [7, 3, 0, -1];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificDays = $this->option('days');

        // Use specific days if provided, otherwise use defaults
        $daysToCheck = !empty($specificDays) ? array_map('intval', $specificDays) : $this->reminderDays;

        $this->info('Checking subscription reminders for days: ' . implode(', ', $daysToCheck));

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
        }

        $totalSent = 0;

        foreach ($daysToCheck as $days) {
            $sent = $this->sendRemindersForDay($days, $dryRun);
            $totalSent += $sent;
        }

        $this->info("Completed. Total reminders " . ($dryRun ? 'would be ' : '') . "sent: {$totalSent}");

        return Command::SUCCESS;
    }

    /**
     * Send reminders for a specific number of days before/after expiry.
     */
    protected function sendRemindersForDay(int $days, bool $dryRun): int
    {
        $targetDate = Carbon::today()->addDays($days);
        $isExpiredReminder = $days < 0;

        // Find tenants with subscriptions expiring on the target date
        $tenants = Tenant::query()
            ->whereNotNull('subscription_expires_at')
            ->whereDate('subscription_expires_at', $targetDate)
            ->where('subscription_tier', '!=', 'free')
            ->get();

        // For expired reminders, we don't require paddle_subscription_id
        // as the subscription may have been cancelled
        if (!$isExpiredReminder) {
            $tenants = $tenants->filter(fn($t) => $t->paddle_subscription_id !== null);
        }

        $label = $isExpiredReminder
            ? abs($days) . ' day(s) after expiry'
            : "{$days} days before expiry";

        if ($tenants->isEmpty()) {
            $this->line("  No reminders needed for {$label}");
            return 0;
        }

        $this->info("  Found {$tenants->count()} tenant(s) for {$label}");

        $sent = 0;

        foreach ($tenants as $tenant) {
            $user = $tenant->users()->first();

            if (!$user || !$user->email) {
                $this->warn("    Skipping tenant {$tenant->id} - no user with email");
                continue;
            }

            // Check if we already sent this reminder today
            $cacheKey = "subscription_reminder_{$tenant->id}_{$days}_{$targetDate->format('Y-m-d')}";
            if (cache()->has($cacheKey)) {
                $this->line("    Skipping tenant {$tenant->id} - reminder already sent today");
                continue;
            }

            if ($dryRun) {
                $this->line("    Would send {$days}-day reminder to {$user->email} (Tenant: {$tenant->name})");
            } else {
                try {
                    Mail::to($user->email)->send(new SubscriptionReminderEmail($tenant, $user, $days));

                    // Mark as sent (cache for 24 hours)
                    cache()->put($cacheKey, true, now()->addHours(24));

                    $this->line("    Sent {$days}-day reminder to {$user->email}");

                    Log::info('Subscription reminder sent', [
                        'tenant_id' => $tenant->id,
                        'user_email' => $user->email,
                        'days_remaining' => $days,
                        'expiry_date' => $tenant->subscription_expires_at,
                    ]);

                    $sent++;
                } catch (\Exception $e) {
                    $this->error("    Failed to send to {$user->email}: {$e->getMessage()}");

                    Log::error('Failed to send subscription reminder', [
                        'tenant_id' => $tenant->id,
                        'user_email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $dryRun ? $tenants->count() : $sent;
    }
}
