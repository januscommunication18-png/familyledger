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
                ->whereHas('familyMembers', function ($query) use ($member) {
                    $query->where('family_member_id', $member->id);
                })
                ->first();

            if ($this->collaborator) {
                $this->isCollaborator = true;
                $this->isOwner = false;
                $this->permissions = $this->collaborator->getPermissionsForMember($member->id);
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
     */
    public function hasFullAccess(): bool
    {
        if ($this->isOwner) {
            return true;
        }

        return collect($this->permissions)->every(fn($level) => $level === 'full');
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
