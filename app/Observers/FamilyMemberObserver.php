<?php

namespace App\Observers;

use App\Models\FamilyMember;
use App\Models\MemberAuditLog;
use Illuminate\Support\Facades\Auth;

class FamilyMemberObserver
{
    /**
     * Fields to track for audit logging.
     */
    protected array $auditableFields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_country_code',
        'date_of_birth',
        'profile_image',
        'relationship',
        'father_name',
        'mother_name',
        'is_minor',
        'co_parenting_enabled',
        'immigration_status',
    ];

    /**
     * Handle the FamilyMember "created" event.
     */
    public function created(FamilyMember $familyMember): void
    {
        MemberAuditLog::create([
            'tenant_id' => $familyMember->tenant_id,
            'family_member_id' => $familyMember->id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_CREATED,
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle the FamilyMember "updated" event.
     */
    public function updated(FamilyMember $familyMember): void
    {
        $dirty = $familyMember->getDirty();
        $original = $familyMember->getOriginal();

        foreach ($this->auditableFields as $field) {
            if (array_key_exists($field, $dirty)) {
                $oldValue = $original[$field] ?? null;
                $newValue = $dirty[$field];

                // Skip if values are effectively the same
                if ($this->areValuesEqual($oldValue, $newValue)) {
                    continue;
                }

                MemberAuditLog::create([
                    'tenant_id' => $familyMember->tenant_id,
                    'family_member_id' => $familyMember->id,
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
     * Handle the FamilyMember "deleted" event.
     */
    public function deleted(FamilyMember $familyMember): void
    {
        MemberAuditLog::create([
            'tenant_id' => $familyMember->tenant_id,
            'family_member_id' => $familyMember->id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_DELETED,
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Serialize a value for storage.
     */
    protected function serializeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    /**
     * Check if two values are effectively equal.
     */
    protected function areValuesEqual($oldValue, $newValue): bool
    {
        // Handle null comparisons
        if ($oldValue === null && $newValue === null) {
            return true;
        }

        // Handle empty string vs null
        if (($oldValue === null || $oldValue === '') && ($newValue === null || $newValue === '')) {
            return true;
        }

        // Handle date comparisons
        if ($oldValue instanceof \DateTimeInterface && $newValue instanceof \DateTimeInterface) {
            return $oldValue->format('Y-m-d') === $newValue->format('Y-m-d');
        }

        // Handle boolean comparisons
        if (is_bool($oldValue) || is_bool($newValue)) {
            return (bool) $oldValue === (bool) $newValue;
        }

        return (string) $oldValue === (string) $newValue;
    }
}
