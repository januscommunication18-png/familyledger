<?php

namespace App\Http\Resources\V1;

use App\Models\MemberDocument;
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
            'allergies' => $this->when(
                $this->relationLoaded('allergies'),
                fn () => $this->allergies->map(fn ($a) => [
                    'id' => $a->id,
                    'allergen_name' => $a->allergen_name,
                    'allergen_type' => $a->allergen_type,
                    'severity' => $a->severity,
                    'severity_color' => $a->severity_color ?? 'gray',
                    'reaction' => $a->reaction,
                    'notes' => $a->notes,
                ])
            ),
            'medications' => $this->when(
                $this->relationLoaded('medications'),
                fn () => $this->medications->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'dosage' => $m->dosage,
                    'frequency' => $m->frequency,
                    'prescribing_doctor' => $m->prescribing_doctor,
                    'start_date' => $m->start_date?->format('Y-m-d'),
                    'end_date' => $m->end_date?->format('Y-m-d'),
                    'is_active' => $m->is_active ?? true,
                    'notes' => $m->notes,
                ])
            ),
            'medical_conditions' => $this->when(
                $this->relationLoaded('medicalConditions'),
                fn () => $this->medicalConditions->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'condition_type' => $c->condition_type,
                    'status' => $c->status,
                    'status_color' => $c->status_color ?? 'gray',
                    'diagnosed_date' => $c->diagnosed_date?->format('Y-m-d'),
                    'notes' => $c->notes,
                ])
            ),
            'healthcare_providers' => $this->when(
                $this->relationLoaded('healthcareProviders'),
                fn () => $this->healthcareProviders->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'provider_type' => $p->provider_type,
                    'specialty' => $p->specialty,
                    'phone' => $p->phone,
                    'email' => $p->email,
                    'address' => $p->address,
                    'is_primary' => $p->is_primary ?? false,
                ])
            ),
            'vaccinations' => $this->when(
                $this->relationLoaded('vaccinations'),
                fn () => $this->vaccinations->map(fn ($v) => [
                    'id' => $v->id,
                    'vaccine_name' => $v->vaccine_name,
                    'date_administered' => $v->date_administered?->format('Y-m-d'),
                    'administered_by' => $v->administered_by,
                    'next_due_date' => $v->next_due_date?->format('Y-m-d'),
                    'notes' => $v->notes,
                ])
            ),
            // Include specific document types when documents are loaded
            'drivers_license' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->formatDocument($this->drivers_license)
            ),
            'passport' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->formatDocument($this->passport)
            ),
            'social_security' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->formatDocument($this->social_security)
            ),
            'birth_certificate' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->formatDocument($this->birth_certificate)
            ),
        ];
    }

    /**
     * Format a document for API response.
     */
    protected function formatDocument(?MemberDocument $document): ?array
    {
        if (!$document) {
            return null;
        }

        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_number' => $document->document_number,
            'issuing_authority' => $document->issuing_authority,
            'issuing_country' => $document->issuing_country,
            'issuing_state' => $document->issuing_state,
            'issue_date' => $document->issue_date?->format('Y-m-d'),
            'expiry_date' => $document->expiry_date?->format('Y-m-d'),
            'is_expired' => $document->isExpired(),
            'days_until_expiry' => $document->expiry_date ? now()->diffInDays($document->expiry_date, false) : null,
            'status' => $document->isExpired() ? 'expired' : 'valid',
        ];
    }
}
