<?php

namespace App\Observers;

use App\Models\MemberAuditLog;
use App\Models\MemberContact;
use Illuminate\Support\Facades\Auth;

class MemberContactObserver
{
    /**
     * Handle the MemberContact "created" event.
     */
    public function created(MemberContact $contact): void
    {
        MemberAuditLog::create([
            'tenant_id' => $contact->tenant_id,
            'family_member_id' => $contact->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'contact_added',
            'old_value' => null,
            'new_value' => $contact->name,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle the MemberContact "deleted" event.
     */
    public function deleted(MemberContact $contact): void
    {
        MemberAuditLog::create([
            'tenant_id' => $contact->tenant_id,
            'family_member_id' => $contact->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'contact_removed',
            'old_value' => $contact->name,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
