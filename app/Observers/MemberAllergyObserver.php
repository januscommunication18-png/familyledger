<?php

namespace App\Observers;

use App\Models\MemberAllergy;
use App\Models\MemberAuditLog;
use Illuminate\Support\Facades\Auth;

class MemberAllergyObserver
{
    public function created(MemberAllergy $allergy): void
    {
        MemberAuditLog::create([
            'tenant_id' => $allergy->tenant_id,
            'family_member_id' => $allergy->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'allergy_added',
            'old_value' => null,
            'new_value' => $allergy->allergen_name . ' (' . $allergy->allergy_type_name . ')',
            'ip_address' => request()->ip(),
        ]);
    }

    public function updated(MemberAllergy $allergy): void
    {
        MemberAuditLog::create([
            'tenant_id' => $allergy->tenant_id,
            'family_member_id' => $allergy->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'allergy_updated',
            'old_value' => null,
            'new_value' => $allergy->allergen_name,
            'ip_address' => request()->ip(),
        ]);
    }

    public function deleted(MemberAllergy $allergy): void
    {
        MemberAuditLog::create([
            'tenant_id' => $allergy->tenant_id,
            'family_member_id' => $allergy->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'allergy_removed',
            'old_value' => $allergy->allergen_name,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
