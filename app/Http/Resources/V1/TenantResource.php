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
        ];
    }
}
