<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CollaboratorPermissionService
{
    protected User $user;
    protected ?Collaborator $collaborator = null;
    protected array $permissions = [];
    protected bool $isOwner = false;
    protected bool $isCollaborator = false;

    /**
     * Create a new permission service instance for a family member.
     */
    public static function forMember(FamilyMember $member, ?User $user = null): self
    {
        return (new self())->loadForMember($member, $user);
    }

    /**
     * Load permissions for a specific family member.
     */
    protected function loadForMember(FamilyMember $member, ?User $user = null): self
    {
        $this->user = $user ?? Auth::user();

        // Check if user is the owner (same tenant)
        if ($member->tenant_id === $this->user->tenant_id) {
            $this->isOwner = true;
            $this->isCollaborator = false;
            // Owners have full access to everything
            $this->permissions = array_fill_keys(
                array_keys(CollaboratorInvite::PERMISSION_CATEGORIES),
                'full'
            );
        } else {
            // Check if user is a collaborator with access to this member
            $this->collaborator = Collaborator::where('user_id', $this->user->id)
                ->where('tenant_id', $member->tenant_id)
                ->where('is_active', true)
                ->where(function ($query) use ($member) {
                    // Check regular collaborator access
                    $query->whereHas('familyMembers', function ($q) use ($member) {
                        $q->where('family_member_id', $member->id);
                    })
                    // OR check co-parent access
                    ->orWhere(function ($q) use ($member) {
                        $q->where('coparenting_enabled', true)
                          ->whereHas('coparentChildren', function ($cq) use ($member) {
                              $cq->where('family_member_id', $member->id);
                          });
                    });
                })
                ->first();

            if ($this->collaborator) {
                $this->isCollaborator = true;
                $this->isOwner = false;

                // Check if this is co-parent access
                if ($this->collaborator->coparenting_enabled &&
                    $this->collaborator->hasCoparentAccessToChild($member->id)) {
                    // Load co-parent permissions from coparent_children table
                    $this->permissions = $this->loadCoparentPermissions($member->id);
                } else {
                    // Load regular collaborator permissions
                    $this->permissions = $this->collaborator->getPermissionsForMember($member->id);
                }
            } else {
                // No access
                $this->permissions = array_fill_keys(
                    array_keys(CollaboratorInvite::PERMISSION_CATEGORIES),
                    'none'
                );
            }
        }

        return $this;
    }

    /**
     * Load co-parent permissions and map them to the standard permission categories.
     */
    protected function loadCoparentPermissions(int $memberId): array
    {
        $coparentPerms = $this->collaborator->getCoparentPermissionsForChild($memberId);

        // Map CoparentChild permission categories to CollaboratorInvite categories
        // CoparentChild uses: basic_info, medical_records, emergency_contacts, school_info,
        //                     documents, insurance, tax_returns, assets, healthcare_providers,
        //                     legal_documents, family_resources
        // CollaboratorInvite uses: date_of_birth, immigration_status, drivers_license, passport,
        //                          ssn, birth_certificate, medical, emergency_contacts, school,
        //                          insurance, tax_returns, assets

        $mapped = [];

        // Map basic_info -> date_of_birth, immigration_status
        $basicInfo = $coparentPerms['basic_info'] ?? 'none';
        $mapped['date_of_birth'] = $basicInfo;
        $mapped['immigration_status'] = $basicInfo;

        // Map documents -> drivers_license, passport, ssn, birth_certificate
        $documents = $coparentPerms['documents'] ?? 'none';
        $mapped['drivers_license'] = $documents;
        $mapped['passport'] = $documents;
        $mapped['ssn'] = $documents;
        $mapped['birth_certificate'] = $documents;

        // Map medical_records -> medical
        $mapped['medical'] = $coparentPerms['medical_records'] ?? 'none';

        // Direct mappings
        $mapped['emergency_contacts'] = $coparentPerms['emergency_contacts'] ?? 'none';
        $mapped['school'] = $coparentPerms['school_info'] ?? 'none';
        $mapped['insurance'] = $coparentPerms['insurance'] ?? 'none';
        $mapped['tax_returns'] = $coparentPerms['tax_returns'] ?? 'none';
        $mapped['assets'] = $coparentPerms['assets'] ?? 'none';

        return $mapped;
    }

    /**
     * Check if user is the owner (not a collaborator).
     */
    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    /**
     * Check if user is a collaborator.
     */
    public function isCollaborator(): bool
    {
        return $this->isCollaborator;
    }

    /**
     * Check if user has any access.
     */
    public function hasAccess(): bool
    {
        return $this->isOwner || $this->isCollaborator;
    }

    /**
     * Get all permissions array.
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get permission level for a specific category.
     */
    public function getPermission(string $category): string
    {
        return $this->permissions[$category] ?? 'none';
    }

    /**
     * Check if user has at least the given permission level for a category.
     * Permission hierarchy: none < view < edit < full
     */
    public function hasPermission(string $category, string $minLevel = 'view'): bool
    {
        $perm = $this->permissions[$category] ?? 'none';
        $hierarchy = ['none' => 0, 'view' => 1, 'edit' => 2, 'full' => 3];
        return ($hierarchy[$perm] ?? 0) >= ($hierarchy[$minLevel] ?? 0);
    }

    /**
     * Check if user can view data in a category.
     */
    public function canView(string $category): bool
    {
        return $this->hasPermission($category, 'view');
    }

    /**
     * Check if user can edit existing data in a category.
     * Requires 'edit' or 'full' permission.
     */
    public function canEdit(string $category): bool
    {
        return $this->hasPermission($category, 'edit');
    }

    /**
     * Check if user can create new data in a category.
     * Requires 'full' permission only.
     */
    public function canCreate(string $category): bool
    {
        $perm = $this->permissions[$category] ?? 'none';
        return $perm === 'full';
    }

    /**
     * Check if user can delete data in a category.
     * Requires 'full' permission only.
     */
    public function canDelete(string $category): bool
    {
        $perm = $this->permissions[$category] ?? 'none';
        return $perm === 'full';
    }

    /**
     * Check if user has full access to ALL categories.
     * For owners: always true (they have 'full' level on everything)
     * For collaborators/co-parents: true if ALL categories have at least 'edit' level
     */
    public function hasFullAccess(): bool
    {
        if ($this->isOwner) {
            return true;
        }

        // For collaborators, check if they have 'edit' or 'full' access on all categories
        $hierarchy = ['none' => 0, 'view' => 1, 'edit' => 2, 'full' => 3];
        $editLevel = $hierarchy['edit'];

        return collect($this->permissions)->every(function($level) use ($hierarchy, $editLevel) {
            return ($hierarchy[$level] ?? 0) >= $editLevel;
        });
    }

    /**
     * Check if user can edit ALL categories (has 'edit' or 'full' on everything).
     */
    public function canEditAll(): bool
    {
        if ($this->isOwner) {
            return true;
        }

        return collect($this->permissions)->every(fn($level) => in_array($level, ['edit', 'full']));
    }

    /**
     * Get the collaborator model if user is a collaborator.
     */
    public function getCollaborator(): ?Collaborator
    {
        return $this->collaborator;
    }

    /**
     * Convert to array for passing to views.
     */
    public function toArray(): array
    {
        return [
            'isOwner' => $this->isOwner,
            'isCollaborator' => $this->isCollaborator,
            'hasAccess' => $this->hasAccess(),
            'hasFullAccess' => $this->hasFullAccess(),
            'permissions' => $this->permissions,
        ];
    }

    /**
     * Get a view-friendly object with helper methods.
     */
    public function forView(): object
    {
        $service = $this;

        return new class($service) {
            protected CollaboratorPermissionService $service;

            public function __construct(CollaboratorPermissionService $service)
            {
                $this->service = $service;
            }

            public function __get($name)
            {
                return match ($name) {
                    'isOwner' => $this->service->isOwner(),
                    'isCollaborator' => $this->service->isCollaborator(),
                    'hasAccess' => $this->service->hasAccess(),
                    'hasFullAccess' => $this->service->hasFullAccess(),
                    'permissions' => $this->service->getPermissions(),
                    default => null,
                };
            }

            public function canView(string $category): bool
            {
                return $this->service->canView($category);
            }

            public function canEdit(string $category): bool
            {
                return $this->service->canEdit($category);
            }

            public function canCreate(string $category): bool
            {
                return $this->service->canCreate($category);
            }

            public function canDelete(string $category): bool
            {
                return $this->service->canDelete($category);
            }

            public function hasPermission(string $category, string $minLevel = 'view'): bool
            {
                return $this->service->hasPermission($category, $minLevel);
            }

            public function getPermission(string $category): string
            {
                return $this->service->getPermission($category);
            }

            /**
             * Get badge HTML based on permission level for a category.
             */
            public function getBadge(string $category): string
            {
                if ($this->service->canCreate($category)) {
                    return ''; // Full access, no badge needed
                } elseif ($this->service->canEdit($category)) {
                    return '<span class="badge badge-success badge-xs">Can Edit</span>';
                } elseif ($this->service->canView($category)) {
                    return '<span class="badge badge-ghost badge-xs">View Only</span>';
                }
                return '<span class="badge badge-error badge-xs">No Access</span>';
            }
        };
    }
}
