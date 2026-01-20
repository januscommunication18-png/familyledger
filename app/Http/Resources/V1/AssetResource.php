<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_url,
            'asset_category' => $this->asset_category,
            'asset_type' => $this->asset_type,
            'description' => $this->description,
            'notes' => $this->notes,

            // Valuation
            'acquisition_date' => $this->acquisition_date?->format('Y-m-d'),
            'purchase_value' => $this->purchase_value,
            'current_value' => $this->current_value,
            'currency' => $this->currency ?? 'USD',
            'formatted_current_value' => $this->current_value
                ? '$' . number_format($this->current_value, 2)
                : null,

            // Location
            'location_address' => $this->location_address,
            'location_city' => $this->location_city,
            'location_state' => $this->location_state,
            'location_zip' => $this->location_zip,
            'location_country' => $this->location_country,
            'storage_location' => $this->storage_location,
            'room_location' => $this->room_location,

            // Status
            'status' => $this->status,
            'status_color' => $this->status_color,
            'ownership_type' => $this->ownership_type,

            // Insurance
            'is_insured' => (bool) $this->is_insured,
            'insurance_provider' => $this->insurance_provider,
            'insurance_policy_number' => $this->insurance_policy_number,
            'insurance_renewal_date' => $this->insurance_renewal_date?->format('Y-m-d'),

            // Vehicle-specific
            'vehicle_make' => $this->when($this->asset_category === 'vehicle', $this->vehicle_make),
            'vehicle_model' => $this->when($this->asset_category === 'vehicle', $this->vehicle_model),
            'vehicle_year' => $this->when($this->asset_category === 'vehicle', $this->vehicle_year),
            'vin_registration' => $this->when($this->asset_category === 'vehicle', $this->vin_registration),
            'license_plate' => $this->when($this->asset_category === 'vehicle', $this->license_plate),
            'mileage' => $this->when($this->asset_category === 'vehicle', $this->mileage),

            // Collectable-specific
            'collectable_category' => $this->when($this->asset_category === 'valuable', $this->collectable_category),
            'condition' => $this->when($this->asset_category === 'valuable', $this->condition),
            'appraised_by' => $this->when($this->asset_category === 'valuable', $this->appraised_by),
            'appraisal_date' => $this->when($this->asset_category === 'valuable', $this->appraisal_date?->format('Y-m-d')),
            'appraisal_value' => $this->when($this->asset_category === 'valuable', $this->appraisal_value),

            // Inventory-specific
            'serial_number' => $this->when($this->asset_category === 'inventory', $this->serial_number),
            'warranty_expiry' => $this->when($this->asset_category === 'inventory', $this->warranty_expiry?->format('Y-m-d')),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relations
            'owners' => AssetOwnerResource::collection($this->whenLoaded('owners')),
            'documents_count' => $this->whenCounted('documents'),
        ];
    }
}
