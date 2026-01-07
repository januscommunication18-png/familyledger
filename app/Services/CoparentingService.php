<?php

namespace App\Services;

use App\Mail\CoparentInviteMail;
use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\CoparentChild;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class CoparentingService
{
    /**
     * Get all children shared with a user (as a co-parent).
     */
    public function getSharedChildren(User $user): Collection
    {
        // Get collaborator record for this user
        $collaborator = Collaborator::where('user_id', $user->id)
            ->coparents()
            ->first();

        if (!$collaborator) {
            return collect();
        }

        return $collaborator->coparentChildren;
    }

    /**
     * Get all co-parents who have access to a specific child.
     */
    public function getCoparentsForChild(FamilyMember $child): Collection
    {
        return $child->coparents()->with('user')->get();
    }

    /**
     * Check if a user can access specific child data.
     */
    public function canAccessChildData(User $user, FamilyMember $child, string $category): bool
    {
        // Check if user is the tenant owner
        if ($user->tenant_id === $child->tenant_id) {
            return true;
        }

        // Check collaborator permissions
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('tenant_id', $child->tenant_id)
            ->coparents()
            ->first();

        if (!$collaborator) {
            return false;
        }

        // Check specific child permissions
        $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
            ->where('family_member_id', $child->id)
            ->first();

        if (!$pivot) {
            return false;
        }

        return $pivot->canView($category);
    }

    /**
     * Check if a user can edit specific child data.
     */
    public function canEditChildData(User $user, FamilyMember $child, string $category): bool
    {
        // Check if user is the tenant owner
        if ($user->tenant_id === $child->tenant_id) {
            return true;
        }

        // Check collaborator permissions
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('tenant_id', $child->tenant_id)
            ->coparents()
            ->first();

        if (!$collaborator) {
            return false;
        }

        // Check specific child permissions
        $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
            ->where('family_member_id', $child->id)
            ->first();

        if (!$pivot) {
            return false;
        }

        return $pivot->canEdit($category);
    }

    /**
     * Update permissions for a child-coparent relationship.
     */
    public function updateChildPermissions(Collaborator $coparent, FamilyMember $child, array $permissions): void
    {
        CoparentChild::updateOrCreate(
            [
                'collaborator_id' => $coparent->id,
                'family_member_id' => $child->id,
            ],
            [
                'permissions' => $permissions,
            ]
        );
    }

    /**
     * Create and send a co-parent invitation.
     */
    public function createCoparentInvite(User $inviter, string $email, array $childIds, ?string $message = null, ?string $firstName = null, ?string $lastName = null, string $parentRole = 'parent'): CollaboratorInvite
    {
        // Create the invite
        $invite = CollaboratorInvite::create([
            'tenant_id' => $inviter->tenant_id,
            'invited_by' => $inviter->id,
            'email' => strtolower($email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'message' => $message,
            'relationship_type' => 'co_parent',
            'role' => 'contributor',
            'is_coparent_invite' => true,
            'parent_role' => $parentRole,
        ]);

        // Default permissions for co-parenting
        $defaultPermissions = [
            'basic_info' => 'view',
            'medical_records' => 'view',
            'emergency_contacts' => 'view',
            'school_info' => 'view',
            'documents' => 'none',
            'insurance' => 'none',
            'vaccinations' => 'view',
            'healthcare_providers' => 'view',
        ];

        // Attach children with default permissions
        foreach ($childIds as $childId) {
            $invite->familyMembers()->attach($childId, [
                'permissions' => json_encode($defaultPermissions),
            ]);

            // Enable co-parenting on the child
            FamilyMember::where('id', $childId)->update(['co_parenting_enabled' => true]);
        }

        // Send email
        $children = FamilyMember::whereIn('id', $childIds)->get();
        Mail::to($invite->email)->send(new CoparentInviteMail($invite, $children));

        return $invite;
    }

    /**
     * Get pending co-parent invites for a user (by email).
     */
    public function getPendingInvitesForUser(User $user): Collection
    {
        return CollaboratorInvite::where('email', $user->email)
            ->coparentInvites()
            ->pending()
            ->with(['inviter', 'familyMembers'])
            ->get();
    }

    /**
     * Get sent co-parent invites by a user.
     */
    public function getSentInvites(User $user): Collection
    {
        return CollaboratorInvite::where('invited_by', $user->id)
            ->coparentInvites()
            ->with(['familyMembers'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all children with co-parenting enabled for a tenant.
     */
    public function getCoparentingChildren(string $tenantId): Collection
    {
        return FamilyMember::where('tenant_id', $tenantId)
            ->where('co_parenting_enabled', true)
            ->minors()
            ->with('coparents.user')
            ->get();
    }

    /**
     * Get active co-parents for a tenant.
     */
    public function getActiveCoparents(string $tenantId): Collection
    {
        return Collaborator::where('tenant_id', $tenantId)
            ->coparents()
            ->with(['user', 'coparentChildren'])
            ->get();
    }

    /**
     * Revoke a co-parent's access to a specific child.
     */
    public function revokeChildAccess(Collaborator $coparent, FamilyMember $child): void
    {
        CoparentChild::where('collaborator_id', $coparent->id)
            ->where('family_member_id', $child->id)
            ->delete();
    }

    /**
     * Revoke all co-parent access for a collaborator.
     */
    public function revokeAllAccess(Collaborator $coparent): void
    {
        $coparent->coparentChildren()->detach();
        $coparent->update(['coparenting_enabled' => false]);
    }

    /**
     * Get permission summary for a co-parent and child.
     */
    public function getPermissionSummary(Collaborator $coparent, FamilyMember $child): array
    {
        $pivot = CoparentChild::where('collaborator_id', $coparent->id)
            ->where('family_member_id', $child->id)
            ->first();

        if (!$pivot) {
            return [];
        }

        $permissions = $pivot->permissions ?? [];
        $summary = [];

        foreach (CoparentChild::PERMISSION_CATEGORIES as $key => $category) {
            $level = $permissions[$key] ?? 'none';
            if ($level !== 'none') {
                $summary[] = [
                    'category' => $key,
                    'label' => $category['label'],
                    'level' => $level,
                    'can_view' => in_array($level, ['view', 'edit']),
                    'can_edit' => $level === 'edit',
                ];
            }
        }

        return $summary;
    }
}
