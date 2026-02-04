<?php

namespace App\Console\Commands;

use App\Mail\DailyTaskReminderMail;
use App\Models\TaskOccurrence;
use App\Models\Tenant;
use App\Models\TodoItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-daily
                            {--tenant= : Send only for a specific tenant ID}
                            {--user= : Send only for a specific user ID}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send consolidated daily task reminder emails to users with tasks due today';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily task reminder emails...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $specificTenantId = $this->option('tenant');
        $specificUserId = $this->option('user');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
            $this->newLine();
        }

        $emailsSent = 0;
        $emailsFailed = 0;

        // Get tenants to process
        $tenantsQuery = Tenant::where('is_active', true);
        if ($specificTenantId) {
            $tenantsQuery->where('id', $specificTenantId);
        }
        $tenants = $tenantsQuery->get();

        $this->info("Processing {$tenants->count()} tenant(s)...");
        $this->newLine();

        foreach ($tenants as $tenant) {
            $this->line("Tenant: {$tenant->name} ({$tenant->id})");

            // Get users for this tenant
            $usersQuery = User::where('tenant_id', $tenant->id);
            if ($specificUserId) {
                $usersQuery->where('id', $specificUserId);
            }
            $users = $usersQuery->get();

            foreach ($users as $user) {
                // Skip users without email
                if (!$user->email) {
                    continue;
                }

                // Get tasks due today (non-recurring or standalone)
                $todayTasks = TodoItem::where('tenant_id', $tenant->id)
                    ->dueToday()
                    ->where(function ($q) {
                        $q->where('is_recurring', false)
                            ->orWhereNull('is_recurring');
                    })
                    ->whereNull('parent_task_id')
                    ->with(['assignedTo', 'assignees'])
                    ->get();

                // Get task occurrences due today (recurring)
                $todayOccurrences = TaskOccurrence::where('tenant_id', $tenant->id)
                    ->dueToday()
                    ->with(['task', 'assignee'])
                    ->get();

                // Get overdue tasks
                $overdueTasks = TodoItem::where('tenant_id', $tenant->id)
                    ->overdue()
                    ->where(function ($q) {
                        $q->where('is_recurring', false)
                            ->orWhereNull('is_recurring');
                    })
                    ->whereNull('parent_task_id')
                    ->with(['assignedTo', 'assignees'])
                    ->get();

                // Get overdue occurrences
                $overdueOccurrences = TaskOccurrence::where('tenant_id', $tenant->id)
                    ->overdue()
                    ->with(['task', 'assignee'])
                    ->get();

                $totalTasks = $todayTasks->count() + $todayOccurrences->count();
                $totalOverdue = $overdueTasks->count() + $overdueOccurrences->count();

                // Only send if there are tasks
                if ($totalTasks === 0 && $totalOverdue === 0) {
                    $this->line("  - {$user->email}: No tasks, skipping");
                    continue;
                }

                $this->line("  - {$user->email}: {$totalTasks} today, {$totalOverdue} overdue");

                if (!$dryRun) {
                    try {
                        Mail::to($user->email)->send(new DailyTaskReminderMail(
                            $user,
                            $todayTasks,
                            $todayOccurrences,
                            $overdueTasks,
                            $overdueOccurrences
                        ));

                        $emailsSent++;
                        $this->info("    ✓ Email sent successfully");

                        Log::info('Daily task reminder sent', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'tenant_id' => $tenant->id,
                            'today_tasks' => $totalTasks,
                            'overdue_tasks' => $totalOverdue,
                        ]);

                    } catch (\Exception $e) {
                        $emailsFailed++;
                        $this->error("    ✗ Failed: {$e->getMessage()}");

                        Log::error('Failed to send daily task reminder', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    $emailsSent++;
                    $this->info("    [DRY RUN] Would send email");
                }
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("========================================");
        $this->info("Daily Task Reminders Complete!");
        $this->info("========================================");
        $this->info("Emails sent: {$emailsSent}");
        if ($emailsFailed > 0) {
            $this->error("Emails failed: {$emailsFailed}");
        }

        return $emailsFailed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
