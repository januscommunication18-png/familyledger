<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->age,
            'relationship' => $this->relationship,
            'relationship_name' => $this->relationship_name,
            'is_minor' => (bool) $this->is_minor,
            'profile_image_url' => $this->profile_image_url,
            'immigration_status' => $this->immigration_status,
            'immigration_status_name' => $this->immigration_status_name,
            'co_parenting_enabled' => (bool) $this->co_parenting_enabled,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Include related data when loaded
            'documents_count' => $this->whenCounted('documents'),
            'medical_info' => $this->whenLoaded('medicalInfo'),
            'contacts' => $this->whenLoaded('contacts'),
        ];
    }
}
