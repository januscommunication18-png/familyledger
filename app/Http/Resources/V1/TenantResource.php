<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('packagePlan');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'subscription_tier' => $this->subscription_tier,
            'onboarding_completed' => (bool) $this->onboarding_completed,
            'onboarding_step' => $this->onboarding_step,
            'goals' => $this->goals,
            'created_at' => $this->created_at?->toISOString(),
            // Subscription info
            'subscription_expires_at' => $this->subscription_expires_at?->toISOString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'billing_cycle' => $this->billing_cycle,
            'package_plan' => $this->packagePlan ? [
                'id' => $this->packagePlan->id,
                'name' => $this->packagePlan->name,
                'type' => $this->packagePlan->type,
            ] : null,
        ];
    }
}
