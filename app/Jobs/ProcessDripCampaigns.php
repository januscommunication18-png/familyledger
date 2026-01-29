<?php

namespace App\Jobs;

use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDripCampaigns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600; // 10 minutes

    public function handle(): void
    {
        $activeCampaigns = DripCampaign::active()->with('steps')->get();

        foreach ($activeCampaigns as $campaign) {
            try {
                $this->processCampaign($campaign);
            } catch (\Exception $e) {
                Log::error('Error processing drip campaign: ' . $campaign->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processCampaign(DripCampaign $campaign): void
    {
        switch ($campaign->trigger_type) {
            case DripCampaign::TRIGGER_SIGNUP:
                $this->processSignupTrigger($campaign);
                break;
            case DripCampaign::TRIGGER_TRIAL_EXPIRING:
                $this->processTrialExpiringTrigger($campaign);
                break;
            case DripCampaign::TRIGGER_CUSTOM:
                // Custom campaigns are triggered manually
                break;
        }
    }

    protected function processSignupTrigger(DripCampaign $campaign): void
    {
        // Find users who signed up recently and haven't received this campaign
        $campaignDelayMinutes = $campaign->getInitialDelayInMinutes();

        // Get the signup cutoff time (users who signed up X minutes ago)
        $signupCutoff = now()->subMinutes($campaignDelayMinutes);

        // Find users who haven't received any email from this campaign
        $users = User::whereNotNull('tenant_id')
            ->where('created_at', '<=', $signupCutoff)
            ->where('created_at', '>=', $signupCutoff->copy()->subDay()) // Only check last 24 hours to limit scope
            ->whereNotIn('id', function ($query) use ($campaign) {
                $query->select('user_id')
                    ->from('drip_email_logs')
                    ->where('drip_campaign_id', $campaign->id)
                    ->whereNotNull('user_id');
            })
            ->limit(100) // Process in batches
            ->get();

        foreach ($users as $user) {
            $this->sendFirstStep($campaign, $user->email, $user->id, $user->tenant_id);
        }
    }

    protected function processTrialExpiringTrigger(DripCampaign $campaign): void
    {
        // Find tenants with trial expiring soon
        $campaignDelayDays = $campaign->delay_days;

        // Find tenants where trial expires in X days
        $trialExpireDate = now()->addDays($campaignDelayDays)->toDateString();

        $tenants = Tenant::where('trial_ends_at', '>=', now())
            ->whereDate('trial_ends_at', '<=', $trialExpireDate)
            ->whereNotIn('id', function ($query) use ($campaign) {
                $query->select('tenant_id')
                    ->from('drip_email_logs')
                    ->where('drip_campaign_id', $campaign->id)
                    ->whereNotNull('tenant_id');
            })
            ->limit(100)
            ->get();

        foreach ($tenants as $tenant) {
            // Get the owner/primary user of the tenant
            $owner = User::where('tenant_id', $tenant->id)
                ->whereHas('collaborator', function ($query) {
                    $query->where('role', 'owner');
                })
                ->first();

            if ($owner) {
                $this->sendFirstStep($campaign, $owner->email, $owner->id, $tenant->id);
            }
        }
    }

    protected function sendFirstStep(DripCampaign $campaign, string $email, ?int $userId, ?string $tenantId): void
    {
        $firstStep = $campaign->getFirstStep();

        if (!$firstStep) {
            return;
        }

        // Check if this email has already received this step
        $alreadySent = DripEmailLog::where('drip_campaign_id', $campaign->id)
            ->where('drip_email_step_id', $firstStep->id)
            ->where('email', $email)
            ->exists();

        if ($alreadySent) {
            return;
        }

        // Calculate the delay for this step
        $delayMinutes = $firstStep->getDelayInMinutes();

        if ($delayMinutes > 0) {
            SendDripEmail::dispatch($firstStep, $email, $userId, $tenantId)
                ->delay(now()->addMinutes($delayMinutes));
        } else {
            SendDripEmail::dispatch($firstStep, $email, $userId, $tenantId);
        }

        // Schedule subsequent steps
        $this->scheduleSubsequentSteps($campaign, $firstStep, $email, $userId, $tenantId);
    }

    protected function scheduleSubsequentSteps(
        DripCampaign $campaign,
        $currentStep,
        string $email,
        ?int $userId,
        ?string $tenantId
    ): void {
        $totalDelay = $currentStep->getDelayInMinutes();
        $nextStep = $campaign->getNextStep($currentStep->sequence_order);

        while ($nextStep) {
            $totalDelay += $nextStep->getDelayInMinutes();

            // Check if this step has already been scheduled
            $alreadyScheduled = DripEmailLog::where('drip_campaign_id', $campaign->id)
                ->where('drip_email_step_id', $nextStep->id)
                ->where('email', $email)
                ->exists();

            if (!$alreadyScheduled) {
                SendDripEmail::dispatch($nextStep, $email, $userId, $tenantId)
                    ->delay(now()->addMinutes($totalDelay));
            }

            $nextStep = $campaign->getNextStep($nextStep->sequence_order);
        }
    }
}
