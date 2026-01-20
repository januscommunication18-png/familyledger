<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'document_type' => $this->document_type,
            'document_type_name' => $this->document_type_name,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'status_color' => $this->status_color,
            'original_location' => $this->original_location,
            'digital_copy_date' => $this->digital_copy_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'files_count' => $this->files_count ?? $this->files()->count(),
            'family_circle_id' => $this->family_circle_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}