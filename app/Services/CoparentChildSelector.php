<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Collection;

class CoparentChildSelector
{
    const SESSION_KEY = 'coparenting_selected_child_id';

    /**
     * Get all co-parented children for the current user.
     */
    public static function getChildren(?User $user = null): Collection
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return collect();
        }

        $tenantId = session('tenant_id', $user->tenant_id);

        // Get children from user's own tenant with co-parenting enabled
        $ownChildren = FamilyMember::where('tenant_id', $user->tenant_id)
            ->where('co_parenting_enabled', true)
            ->where('is_minor', true)
            ->get();

        // Get children the user has co-parent access to (from other tenants)
        $collaboratorChildren = collect();
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with('coparentChildren')
            ->first();

        if ($collaborator) {
            $collaboratorChildren = $collaborator->coparentChildren;
        }

        // Merge and remove duplicates
        return $ownChildren->merge($collaboratorChildren)->unique('id');
    }

    /**
     * Check if user has multiple co-parented children.
     */
    public static function hasMultipleChildren(?User $user = null): bool
    {
        return self::getChildren($user)->count() > 1;
    }

    /**
     * Check if a child is selected in session.
     */
    public static function hasSelectedChild(): bool
    {
        return session()->has(self::SESSION_KEY) && session(self::SESSION_KEY) !== null;
    }

    /**
     * Get the selected child ID from session.
     */
    public static function getSelectedChildId(): ?int
    {
        return session(self::SESSION_KEY);
    }

    /**
     * Get the selected child model.
     */
    public static function getSelectedChild(): ?FamilyMember
    {
        $childId = self::getSelectedChildId();

        if (!$childId) {
            return null;
        }

        return FamilyMember::find($childId);
    }

    /**
     * Set the selected child in session.
     */
    public static function setSelectedChild(int $childId): void
    {
        session([self::SESSION_KEY => $childId]);
    }

    /**
     * Clear the selected child from session.
     */
    public static function clearSelectedChild(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Check if child selection is required (multiple children and none selected).
     */
    public static function needsChildSelection(?User $user = null): bool
    {
        $children = self::getChildren($user);

        // No children - no selection needed
        if ($children->isEmpty()) {
            return false;
        }

        // Only one child - auto-select it
        if ($children->count() === 1) {
            self::setSelectedChild($children->first()->id);
            return false;
        }

        // Multiple children - check if one is selected
        if (!self::hasSelectedChild()) {
            return true;
        }

        // Verify selected child is still valid
        $selectedId = self::getSelectedChildId();
        if (!$children->contains('id', $selectedId)) {
            self::clearSelectedChild();
            return true;
        }

        return false;
    }

    /**
     * Get selected child or first child if only one exists.
     */
    public static function getEffectiveChild(?User $user = null): ?FamilyMember
    {
        $children = self::getChildren($user);

        if ($children->isEmpty()) {
            return null;
        }

        if ($children->count() === 1) {
            return $children->first();
        }

        return self::getSelectedChild();
    }

    /**
     * Get data for the child picker (used in views/API).
     */
    public static function getPickerData(?User $user = null): array
    {
        $children = self::getChildren($user);
        $selectedChild = self::getSelectedChild();

        return [
            'children' => $children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->first_name ?? $child->name,
                    'full_name' => $child->full_name ?? $child->name,
                    'age' => $child->age ?? null,
                    'avatar_url' => $child->profile_image_url ?? null,
                ];
            }),
            'selected_child' => $selectedChild ? [
                'id' => $selectedChild->id,
                'name' => $selectedChild->first_name ?? $selectedChild->name,
                'full_name' => $selectedChild->full_name ?? $selectedChild->name,
                'avatar_url' => $selectedChild->profile_image_url ?? null,
            ] : null,
            'has_multiple' => $children->count() > 1,
            'needs_selection' => self::needsChildSelection($user),
        ];
    }
}
