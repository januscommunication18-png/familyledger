<?php

namespace App\Http\Resources\V1;

use App\Models\MemberDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'profile_image_url' => $this->getProfileImageWithFallback(),
            'immigration_status' => $this->immigration_status,
            'immigration_status_name' => $this->immigration_status_name,
            'co_parenting_enabled' => (bool) $this->co_parenting_enabled,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Include related data when loaded
            'documents_count' => $this->whenCounted('documents'),
            'medical_info' => $this->whenLoaded('medicalInfo'),
            'contacts' => $this->when(
                $this->relationLoaded('contacts'),
                fn () => $this->contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'relationship' => $c->relationship,
                    'relationship_name' => $c->relationship_name,
                    'address' => $c->address,
                    'notes' => $c->notes,
                    'is_emergency_contact' => $c->is_emergency_contact,
                    'priority' => $c->priority,
                ])
            ),
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
                    'vaccine_type' => $v->vaccine_type,
                    'vaccine_name' => $v->vaccine_name,
                    'custom_vaccine_name' => $v->custom_vaccine_name,
                    'vaccination_date' => $v->vaccination_date?->format('Y-m-d'),
                    'next_vaccination_date' => $v->next_vaccination_date?->format('Y-m-d'),
                    'administered_by' => $v->administered_by,
                    'lot_number' => $v->lot_number,
                    'notes' => $v->notes,
                    'is_due' => $v->is_due,
                    'is_coming_soon' => $v->is_coming_soon,
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
            // School/Education info
            'school_info' => $this->when(
                $this->relationLoaded('schoolInfo'),
                fn () => $this->schoolInfo ? [
                    'id' => $this->schoolInfo->id,
                    'school_name' => $this->schoolInfo->school_name,
                    'grade_level' => $this->schoolInfo->grade_level,
                    'grade_level_name' => $this->schoolInfo->grade_level_name,
                    'school_year' => $this->schoolInfo->school_year,
                    'is_current' => (bool) $this->schoolInfo->is_current,
                    'start_date' => $this->schoolInfo->start_date?->format('Y-m-d'),
                    'end_date' => $this->schoolInfo->end_date?->format('Y-m-d'),
                    'student_id' => $this->schoolInfo->student_id,
                    'school_address' => $this->schoolInfo->school_address,
                    'school_phone' => $this->schoolInfo->school_phone,
                    'school_email' => $this->schoolInfo->school_email,
                    'teacher_name' => $this->schoolInfo->teacher_name,
                    'teacher_email' => $this->schoolInfo->teacher_email,
                    'counselor_name' => $this->schoolInfo->counselor_name,
                    'counselor_email' => $this->schoolInfo->counselor_email,
                    'bus_number' => $this->schoolInfo->bus_number,
                    'bus_pickup_time' => $this->schoolInfo->bus_pickup_time,
                    'bus_dropoff_time' => $this->schoolInfo->bus_dropoff_time,
                    'notes' => $this->schoolInfo->notes,
                ] : null
            ),
            'school_records' => $this->when(
                $this->relationLoaded('schoolRecords'),
                fn () => $this->schoolRecords->map(fn ($s) => [
                    'id' => $s->id,
                    'school_name' => $s->school_name,
                    'grade_level' => $s->grade_level,
                    'grade_level_name' => $s->grade_level_name,
                    'school_year' => $s->school_year,
                    'is_current' => (bool) $s->is_current,
                    'start_date' => $s->start_date?->format('Y-m-d'),
                    'end_date' => $s->end_date?->format('Y-m-d'),
                    'student_id' => $s->student_id,
                    'school_address' => $s->school_address,
                    'school_phone' => $s->school_phone,
                    'school_email' => $s->school_email,
                    'teacher_name' => $s->teacher_name,
                    'teacher_email' => $s->teacher_email,
                    'counselor_name' => $s->counselor_name,
                    'counselor_email' => $s->counselor_email,
                    'bus_number' => $s->bus_number,
                    'bus_pickup_time' => $s->bus_pickup_time,
                    'bus_dropoff_time' => $s->bus_dropoff_time,
                    'notes' => $s->notes,
                    'documents' => $s->documents->map(fn ($d) => [
                        'id' => $d->id,
                        'document_type' => $d->document_type,
                        'document_type_name' => $d->document_type_name,
                        'title' => $d->title,
                        'description' => $d->description,
                        'school_year' => $d->school_year,
                        'grade_level' => $d->grade_level,
                        'file_name' => $d->file_name,
                        'file_size' => $d->file_size,
                        'formatted_file_size' => $d->formatted_file_size,
                        'mime_type' => $d->mime_type,
                        'file_url' => $d->file_url,
                        'created_at' => $d->created_at?->toISOString(),
                    ]),
                ])
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
            'front_image_url' => $document->front_image ? Storage::disk('do_spaces')->url($document->front_image) : null,
            'back_image_url' => $document->back_image ? Storage::disk('do_spaces')->url($document->back_image) : null,
        ];
    }

    /**
     * Get profile image URL with fallback to user avatar if member has matching email.
     */
    protected function getProfileImageWithFallback(): ?string
    {
        // First, try the member's own profile image
        if ($this->profile_image_url) {
            return $this->profile_image_url;
        }

        // Fallback: Check if there's a user with matching email and use their avatar
        if ($this->email) {
            $user = User::where('email', $this->email)->first();
            if ($user && $user->avatar) {
                return Storage::disk('do_spaces')->url($user->avatar);
            }
        }

        return null;
    }
}
