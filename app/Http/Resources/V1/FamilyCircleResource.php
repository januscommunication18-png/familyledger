<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FamilyCircleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image ? Storage::disk('do_spaces')->url($this->cover_image) : null,
            'members_count' => $this->whenCounted('familyMembers', $this->family_members_count ?? $this->familyMembers()->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Include members when loaded
            'members' => FamilyMemberResource::collection($this->whenLoaded('familyMembers')),
        ];
    }
}
