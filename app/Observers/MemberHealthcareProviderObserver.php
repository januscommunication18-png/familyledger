<?php

namespace App\Observers;

use App\Models\MemberHealthcareProvider;
use App\Models\MemberAuditLog;
use Illuminate\Support\Facades\Auth;

class MemberHealthcareProviderObserver
{
    public function created(MemberHealthcareProvider $provider): void
    {
        MemberAuditLog::create([
            'tenant_id' => $provider->tenant_id,
            'family_member_id' => $provider->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'healthcare_provider_added',
            'old_value' => null,
            'new_value' => $provider->name . ' (' . $provider->provider_type_name . ')',
            'ip_address' => request()->ip(),
        ]);
    }

    public function updated(MemberHealthcareProvider $provider): void
    {
        MemberAuditLog::create([
            'tenant_id' => $provider->tenant_id,
            'family_member_id' => $provider->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'healthcare_provider_updated',
            'old_value' => null,
            'new_value' => $provider->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public function deleted(MemberHealthcareProvider $provider): void
    {
        MemberAuditLog::create([
            'tenant_id' => $provider->tenant_id,
            'family_member_id' => $provider->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'healthcare_provider_removed',
            'old_value' => $provider->name,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
