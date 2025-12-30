<?php

namespace App\Observers;

use App\Models\MemberAuditLog;
use App\Models\MemberMedicalInfo;
use Illuminate\Support\Facades\Auth;

class MemberMedicalInfoObserver
{
    /**
     * Fields to track for audit logging.
     */
    protected array $auditableFields = [
        'blood_type',
        'allergies',
        'medications',
        'medical_conditions',
        'primary_physician',
        'physician_phone',
        'insurance_provider',
        'insurance_policy_number',
    ];

    /**
     * Handle the MemberMedicalInfo "created" event.
     */
    public function created(MemberMedicalInfo $medicalInfo): void
    {
        // Log each non-null field as a change
        foreach ($this->auditableFields as $field) {
            $value = $medicalInfo->$field;
            if ($value !== null && $value !== '') {
                MemberAuditLog::create([
                    'tenant_id' => $medicalInfo->tenant_id,
                    'family_member_id' => $medicalInfo->family_member_id,
                    'user_id' => Auth::id(),
                    'action' => MemberAuditLog::ACTION_UPDATED,
                    'field_name' => $field,
                    'old_value' => null,
                    'new_value' => $this->serializeValue($value),
                    'ip_address' => request()->ip(),
                ]);
            }
        }
    }

    /**
     * Handle the MemberMedicalInfo "updated" event.
     */
    public function updated(MemberMedicalInfo $medicalInfo): void
    {
        $dirty = $medicalInfo->getDirty();
        $original = $medicalInfo->getOriginal();

        foreach ($this->auditableFields as $field) {
            if (array_key_exists($field, $dirty)) {
                $oldValue = $original[$field] ?? null;
                $newValue = $dirty[$field];

                // Skip if values are effectively the same
                if ($this->areValuesEqual($oldValue, $newValue)) {
                    continue;
                }

                MemberAuditLog::create([
                    'tenant_id' => $medicalInfo->tenant_id,
                    'family_member_id' => $medicalInfo->family_member_id,
                    'user_id' => Auth::id(),
                    'action' => MemberAuditLog::ACTION_UPDATED,
                    'field_name' => $field,
                    'old_value' => $this->serializeValue($oldValue),
                    'new_value' => $this->serializeValue($newValue),
                    'ip_address' => request()->ip(),
                ]);
            }
        }
    }

    /**
     * Serialize a value for storage.
     */
    protected function serializeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Check if two values are effectively equal.
     */
    protected function areValuesEqual($oldValue, $newValue): bool
    {
        if ($oldValue === null && $newValue === null) {
            return true;
        }

        if (($oldValue === null || $oldValue === '') && ($newValue === null || $newValue === '')) {
            return true;
        }

        return (string) $oldValue === (string) $newValue;
    }
}
