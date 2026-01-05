<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_member_id' => $this->family_member_id,
            'owner_name' => $this->owner_name,
            'owner_email' => $this->owner_email,
            'owner_phone' => $this->owner_phone,
            'ownership_percentage' => $this->ownership_percentage,
            'formatted_ownership_percentage' => $this->formatted_ownership_percentage,
            'is_primary_owner' => (bool) $this->is_primary_owner,
            'is_family_member' => $this->isFamilyMember(),
            'is_external_owner' => $this->isExternalOwner(),
            'family_member' => new FamilyMemberResource($this->whenLoaded('familyMember')),
        ];
    }
}
