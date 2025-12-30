<?php

namespace App\Observers;

use App\Models\MemberMedication;
use App\Models\MemberAuditLog;
use Illuminate\Support\Facades\Auth;

class MemberMedicationObserver
{
    public function created(MemberMedication $medication): void
    {
        MemberAuditLog::create([
            'tenant_id' => $medication->tenant_id,
            'family_member_id' => $medication->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medication_added',
            'old_value' => null,
            'new_value' => $medication->name . ($medication->dosage ? ' (' . $medication->dosage . ')' : ''),
            'ip_address' => request()->ip(),
        ]);
    }

    public function updated(MemberMedication $medication): void
    {
        MemberAuditLog::create([
            'tenant_id' => $medication->tenant_id,
            'family_member_id' => $medication->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medication_updated',
            'old_value' => null,
            'new_value' => $medication->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public function deleted(MemberMedication $medication): void
    {
        MemberAuditLog::create([
            'tenant_id' => $medication->tenant_id,
            'family_member_id' => $medication->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medication_removed',
            'old_value' => $medication->name,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
