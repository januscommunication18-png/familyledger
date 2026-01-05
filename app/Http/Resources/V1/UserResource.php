<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'role_name' => $this->role_name,
            'avatar' => $this->avatar,
            'auth_provider' => $this->auth_provider,
            'email_verified' => (bool) $this->email_verified_at,
            'mfa_enabled' => (bool) $this->mfa_enabled,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
