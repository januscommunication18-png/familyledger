<?php

namespace App\Jobs;

use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailLog;
use App\Models\Backoffice\DripEmailStep;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send immediate drip campaign emails when a user signs up.
 * This is triggered at registration time, not by the daily scheduler.
 */
class SendImmediateDripEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        protected User $user
    ) {}

    public function handle(): void
    {
        // Find all active signup-triggered campaigns
        $campaigns = DripCampaign::active()
            ->where('trigger_type', DripCampaign::TRIGGER_SIGNUP)
            ->with(['steps' => function ($query) {
                $query->orderBy('sequence_order');
            }])
            ->get();

        foreach ($campaigns as $campaign) {
            try {
                $this->processCampaignForUser($campaign);
            } catch (\Exception $e) {
                Log::error('Error sending immediate drip email', [
                    'campaign_id' => $campaign->id,
                    'user_id' => $this->user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processCampaignForUser(DripCampaign $campaign): void
    {
        $email = $this->user->email;
        $userId = $this->user->id;
        $tenantId = $this->user->tenant_id;

        // Check if user already received any email from this campaign
        $alreadyEnrolled = DripEmailLog::where('drip_campaign_id', $campaign->id)
            ->where('email', $email)
            ->exists();

        if ($alreadyEnrolled) {
            return;
        }

        // Get all time-based steps
        $steps = $campaign->steps
            ->where('trigger_type', DripEmailStep::TRIGGER_TIME_BASED)
            ->sortBy('sequence_order');

        if ($steps->isEmpty()) {
            return;
        }

        $totalDelayMinutes = 0;

        foreach ($steps as $step) {
            $stepDelay = $step->getDelayInMinutes();
            $totalDelayMinutes += $stepDelay;

            // Check if this step was already processed
            $alreadyProcessed = DripEmailLog::where('drip_campaign_id', $campaign->id)
                ->where('drip_email_step_id', $step->id)
                ->where('email', $email)
                ->exists();

            if ($alreadyProcessed) {
                continue;
            }

            // For immediate emails (0 delay), send now
            // For delayed emails, schedule them
            if ($totalDelayMinutes === 0) {
                // Send immediately
                SendDripEmail::dispatch($step, $email, $userId, $tenantId);

                Log::info('Immediate drip email dispatched', [
                    'campaign_id' => $campaign->id,
                    'step_id' => $step->id,
                    'step_subject' => $step->subject,
                    'email' => $email,
                ]);
            } else {
                // Schedule for later
                SendDripEmail::dispatch($step, $email, $userId, $tenantId)
                    ->delay(now()->addMinutes($totalDelayMinutes));

                Log::info('Drip email scheduled', [
                    'campaign_id' => $campaign->id,
                    'step_id' => $step->id,
                    'step_subject' => $step->subject,
                    'email' => $email,
                    'delay_minutes' => $totalDelayMinutes,
                ]);
            }
        }
    }
}
