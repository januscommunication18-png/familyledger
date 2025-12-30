<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAuditLog extends Model
{
    use BelongsToTenant;

    /**
     * Field labels for human-readable display.
     */
    public const FIELD_LABELS = [
        // Member fields
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'phone_country_code' => 'Country Code',
        'date_of_birth' => 'Date of Birth',
        'profile_image' => 'Profile Photo',
        'relationship' => 'Relationship',
        'father_name' => 'Father\'s Name',
        'mother_name' => 'Mother\'s Name',
        'is_minor' => 'Minor Status',
        'co_parenting_enabled' => 'Co-Parenting',
        'immigration_status' => 'Immigration Status',
        // Medical info fields
        'blood_type' => 'Blood Type',
        'allergies' => 'Allergies',
        'medications' => 'Medications',
        'medical_conditions' => 'Medical Conditions',
        'primary_physician' => 'Primary Physician',
        'physician_phone' => 'Physician Phone',
        'insurance_provider' => 'Insurance Provider',
        'insurance_policy_number' => 'Insurance Policy',
        // Document fields
        'document_added' => 'Document',
        'document_updated' => 'Document',
        'document_removed' => 'Document',
        'document_number' => 'Document Number',
        'expiry_date' => 'Expiry Date',
        'issue_date' => 'Issue Date',
        // Contact fields
        'contact_added' => 'Contact',
        'contact_removed' => 'Contact',
        // Allergy fields
        'allergy_added' => 'Allergy',
        'allergy_updated' => 'Allergy',
        'allergy_removed' => 'Allergy',
        // Healthcare provider fields
        'healthcare_provider_added' => 'Healthcare Provider',
        'healthcare_provider_updated' => 'Healthcare Provider',
        'healthcare_provider_removed' => 'Healthcare Provider',
        // Medication fields
        'medication_added' => 'Medication',
        'medication_updated' => 'Medication',
        'medication_removed' => 'Medication',
        // Medical condition fields
        'medical_condition_added' => 'Medical Condition',
        'medical_condition_updated' => 'Medical Condition',
        'medical_condition_removed' => 'Medical Condition',
    ];

    /**
     * Action types.
     */
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'ip_address',
    ];

    /**
     * Get the family member this audit log belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the human-readable field label.
     */
    public function getFieldLabelAttribute(): ?string
    {
        if (!$this->field_name) {
            return null;
        }

        return self::FIELD_LABELS[$this->field_name] ?? ucfirst(str_replace('_', ' ', $this->field_name));
    }

    /**
     * Get formatted old value for display.
     */
    public function getFormattedOldValueAttribute(): ?string
    {
        return $this->formatValue($this->field_name, $this->old_value);
    }

    /**
     * Get formatted new value for display.
     */
    public function getFormattedNewValueAttribute(): ?string
    {
        return $this->formatValue($this->field_name, $this->new_value);
    }

    /**
     * Format a value based on field type.
     */
    protected function formatValue(?string $field, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'relationship' => FamilyMember::RELATIONSHIPS[$value] ?? $value,
            'immigration_status' => FamilyMember::IMMIGRATION_STATUSES[$value] ?? $value,
            'blood_type' => MemberMedicalInfo::BLOOD_TYPES[$value] ?? $value,
            'is_minor' => $value ? 'Yes' : 'No',
            'co_parenting_enabled' => $value ? 'Enabled' : 'Disabled',
            'date_of_birth', 'expiry_date', 'issue_date' => $this->formatDate($value),
            'profile_image' => $value ? 'Photo uploaded' : 'Photo removed',
            'document_added', 'document_updated', 'document_removed' => $this->formatDocumentType($value),
            default => $value,
        };
    }

    /**
     * Format document type for display.
     */
    protected function formatDocumentType(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return match ($value) {
            'drivers_license' => "Driver's License",
            'passport' => 'Passport',
            'social_security' => 'Social Security Card',
            'birth_certificate' => 'Birth Certificate',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    /**
     * Format a date value.
     */
    protected function formatDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get the action description.
     */
    public function getActionDescriptionAttribute(): string
    {
        // Handle document-related actions
        if (in_array($this->field_name, ['document_added', 'document_updated', 'document_removed'])) {
            $docType = $this->formatDocumentType($this->new_value ?? $this->old_value);
            return match ($this->field_name) {
                'document_added' => "$docType added",
                'document_updated' => "$docType updated",
                'document_removed' => "$docType removed",
                default => $this->field_label . ' changed',
            };
        }

        // Handle contact-related actions
        if (in_array($this->field_name, ['contact_added', 'contact_removed'])) {
            $contactName = $this->new_value ?? $this->old_value;
            return match ($this->field_name) {
                'contact_added' => "Contact added: $contactName",
                'contact_removed' => "Contact removed: $contactName",
                default => $this->field_label . ' changed',
            };
        }

        // Handle allergy-related actions
        if (in_array($this->field_name, ['allergy_added', 'allergy_updated', 'allergy_removed'])) {
            $allergyName = $this->new_value ?? $this->old_value;
            return match ($this->field_name) {
                'allergy_added' => "Allergy added: $allergyName",
                'allergy_updated' => "Allergy updated: $allergyName",
                'allergy_removed' => "Allergy removed: $allergyName",
                default => $this->field_label . ' changed',
            };
        }

        // Handle healthcare provider-related actions
        if (in_array($this->field_name, ['healthcare_provider_added', 'healthcare_provider_updated', 'healthcare_provider_removed'])) {
            $providerName = $this->new_value ?? $this->old_value;
            return match ($this->field_name) {
                'healthcare_provider_added' => "Provider added: $providerName",
                'healthcare_provider_updated' => "Provider updated: $providerName",
                'healthcare_provider_removed' => "Provider removed: $providerName",
                default => $this->field_label . ' changed',
            };
        }

        // Handle medication-related actions
        if (in_array($this->field_name, ['medication_added', 'medication_updated', 'medication_removed'])) {
            $medicationName = $this->new_value ?? $this->old_value;
            return match ($this->field_name) {
                'medication_added' => "Medication added: $medicationName",
                'medication_updated' => "Medication updated: $medicationName",
                'medication_removed' => "Medication removed: $medicationName",
                default => $this->field_label . ' changed',
            };
        }

        // Handle medical condition-related actions
        if (in_array($this->field_name, ['medical_condition_added', 'medical_condition_updated', 'medical_condition_removed'])) {
            $conditionName = $this->new_value ?? $this->old_value;
            return match ($this->field_name) {
                'medical_condition_added' => "Condition added: $conditionName",
                'medical_condition_updated' => "Condition updated: $conditionName",
                'medical_condition_removed' => "Condition removed: $conditionName",
                default => $this->field_label . ' changed',
            };
        }

        return match ($this->action) {
            self::ACTION_CREATED => 'Member created',
            self::ACTION_DELETED => 'Member deleted',
            self::ACTION_UPDATED => $this->field_label . ' changed',
            default => ucfirst($this->action),
        };
    }
}
