<?php

namespace App\Observers;

use App\Models\MemberMedicalCondition;
use App\Models\MemberAuditLog;
use Illuminate\Support\Facades\Auth;

class MemberMedicalConditionObserver
{
    public function created(MemberMedicalCondition $condition): void
    {
        MemberAuditLog::create([
            'tenant_id' => $condition->tenant_id,
            'family_member_id' => $condition->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medical_condition_added',
            'old_value' => null,
            'new_value' => $condition->name . ($condition->status_name ? ' (' . $condition->status_name . ')' : ''),
            'ip_address' => request()->ip(),
        ]);
    }

    public function updated(MemberMedicalCondition $condition): void
    {
        MemberAuditLog::create([
            'tenant_id' => $condition->tenant_id,
            'family_member_id' => $condition->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medical_condition_updated',
            'old_value' => null,
            'new_value' => $condition->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public function deleted(MemberMedicalCondition $condition): void
    {
        MemberAuditLog::create([
            'tenant_id' => $condition->tenant_id,
            'family_member_id' => $condition->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'medical_condition_removed',
            'old_value' => $condition->name,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
