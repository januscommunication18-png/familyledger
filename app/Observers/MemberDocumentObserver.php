<?php

namespace App\Observers;

use App\Models\MemberAuditLog;
use App\Models\MemberDocument;
use Illuminate\Support\Facades\Auth;

class MemberDocumentObserver
{
    /**
     * Handle the MemberDocument "created" event.
     */
    public function created(MemberDocument $document): void
    {
        MemberAuditLog::create([
            'tenant_id' => $document->tenant_id,
            'family_member_id' => $document->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'document_added',
            'old_value' => null,
            'new_value' => $document->document_type,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle the MemberDocument "updated" event.
     */
    public function updated(MemberDocument $document): void
    {
        MemberAuditLog::create([
            'tenant_id' => $document->tenant_id,
            'family_member_id' => $document->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'document_updated',
            'old_value' => null,
            'new_value' => $document->document_type,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle the MemberDocument "deleted" event.
     */
    public function deleted(MemberDocument $document): void
    {
        MemberAuditLog::create([
            'tenant_id' => $document->tenant_id,
            'family_member_id' => $document->family_member_id,
            'user_id' => Auth::id(),
            'action' => MemberAuditLog::ACTION_UPDATED,
            'field_name' => 'document_removed',
            'old_value' => $document->document_type,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);
    }
}
