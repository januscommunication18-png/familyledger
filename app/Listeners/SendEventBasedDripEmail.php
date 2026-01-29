<?php

namespace App\Listeners;

use App\Events\FamilyCircleCreated;
use App\Jobs\SendDripEmail;
use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailLog;
use App\Models\Backoffice\DripEmailStep;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventBasedDripEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(FamilyCircleCreated $event): void
    {
        $user = $event->user;
        $tenantId = $user->tenant_id;

        // Find all active signup campaigns with event-based steps for this event
        $campaigns = DripCampaign::active()
            ->where('trigger_type', DripCampaign::TRIGGER_SIGNUP)
            ->whereHas('steps', function ($query) {
                $query->where('trigger_type', DripEmailStep::TRIGGER_EVENT_BASED)
                    ->where('trigger_event', DripEmailStep::EVENT_FAMILY_CIRCLE_CREATED);
            })
            ->with(['steps' => function ($query) {
                $query->orderBy('sequence_order');
            }])
            ->get();

        foreach ($campaigns as $campaign) {
            $this->processEventForCampaign($campaign, $user, $tenantId);
        }
    }

    /**
     * Process the event for a specific campaign
     */
    protected function processEventForCampaign(DripCampaign $campaign, $user, ?string $tenantId): void
    {
        // Check if user is enrolled in this campaign (has received at least one email)
        $isEnrolled = DripEmailLog::where('drip_campaign_id', $campaign->id)
            ->where('email', $user->email)
            ->exists();

        if (!$isEnrolled) {
            // User hasn't started this campaign yet, skip
            return;
        }

        // Find the event-based step for this event
        $eventStep = $campaign->steps
            ->where('trigger_type', DripEmailStep::TRIGGER_EVENT_BASED)
            ->where('trigger_event', DripEmailStep::EVENT_FAMILY_CIRCLE_CREATED)
            ->first();

        if (!$eventStep) {
            return;
        }

        // Check if already sent
        $alreadySent = DripEmailLog::where('drip_campaign_id', $campaign->id)
            ->where('drip_email_step_id', $eventStep->id)
            ->where('email', $user->email)
            ->exists();

        if ($alreadySent) {
            return;
        }

        // Check condition if any
        if ($eventStep->hasCondition() && !$eventStep->checkCondition($tenantId)) {
            Log::info('Drip email condition not met', [
                'campaign_id' => $campaign->id,
                'step_id' => $eventStep->id,
                'email' => $user->email,
                'condition' => $eventStep->condition_type,
            ]);
            return;
        }

        // Send the event-based email
        SendDripEmail::dispatch($eventStep, $user->email, $user->id, $tenantId);

        Log::info('Event-based drip email dispatched', [
            'campaign_id' => $campaign->id,
            'step_id' => $eventStep->id,
            'email' => $user->email,
            'event' => DripEmailStep::EVENT_FAMILY_CIRCLE_CREATED,
        ]);

        // If this step should skip subsequent time-based steps, cancel them
        if ($eventStep->skip_if_event_sent) {
            $this->cancelPendingTimeBasedSteps($campaign, $eventStep, $user->email);
        }
    }

    /**
     * Cancel pending time-based steps that come after this event step
     */
    protected function cancelPendingTimeBasedSteps(DripCampaign $campaign, DripEmailStep $eventStep, string $email): void
    {
        // Find time-based steps that should be skipped
        $stepsToSkip = $campaign->steps
            ->where('trigger_type', DripEmailStep::TRIGGER_TIME_BASED)
            ->where('sequence_order', '>', $eventStep->sequence_order)
            ->pluck('id');

        if ($stepsToSkip->isEmpty()) {
            return;
        }

        // Mark pending logs as skipped (we'll add a 'skipped' status or just prevent sending)
        // For now, we'll create a log entry so it won't be sent again
        foreach ($stepsToSkip as $stepId) {
            $exists = DripEmailLog::where('drip_campaign_id', $campaign->id)
                ->where('drip_email_step_id', $stepId)
                ->where('email', $email)
                ->exists();

            if (!$exists) {
                // Create a "skipped" log entry to prevent future sending
                DripEmailLog::create([
                    'drip_campaign_id' => $campaign->id,
                    'drip_email_step_id' => $stepId,
                    'email' => $email,
                    'status' => 'skipped',
                    'error_message' => 'Skipped due to event-based step: ' . $eventStep->subject,
                ]);
            }
        }

        Log::info('Cancelled pending time-based drip steps', [
            'campaign_id' => $campaign->id,
            'email' => $email,
            'skipped_steps' => $stepsToSkip->toArray(),
        ]);
    }
}