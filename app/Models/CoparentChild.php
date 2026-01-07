<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CoparentChild extends Pivot
{
    protected $table = 'coparent_children';

    public $incrementing = true;

    protected $fillable = [
        'collaborator_id',
        'family_member_id',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Permission categories for co-parenting
    public const PERMISSION_CATEGORIES = [
        'basic_info' => ['label' => 'Basic Info', 'description' => 'Name, date of birth, blood type, immigration status'],
        'medical_records' => ['label' => 'Medical Records', 'description' => 'Medical conditions, medications, allergies'],
        'emergency_contacts' => ['label' => 'Emergency Contacts', 'description' => 'Emergency contact list'],
        'school_info' => ['label' => 'School Info', 'description' => 'School, grades, teachers'],
        'documents' => ['label' => 'Documents', 'description' => "Driver's license, passport, SSN, birth certificate"],
        'insurance' => ['label' => 'Insurance', 'description' => 'Insurance policies'],
        'tax_returns' => ['label' => 'Tax Returns', 'description' => 'Tax return information'],
        'assets' => ['label' => 'Assets', 'description' => 'Asset and property information'],
        'healthcare_providers' => ['label' => 'Healthcare Providers', 'description' => 'Doctors, specialists'],
    ];

    public const PERMISSION_LEVELS = [
        'none' => ['label' => 'No Access', 'value' => 0],
        'view' => ['label' => 'View Only', 'value' => 1],
        'edit' => ['label' => 'Can Edit', 'value' => 2],
    ];

    // ==================== RELATIONSHIPS ====================

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    // Alias for the child
    public function child(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'family_member_id');
    }

    // ==================== METHODS ====================

    /**
     * Get permission level for a specific category.
     */
    public function getPermission(string $category): string
    {
        $permissions = $this->permissions ?? [];
        return $permissions[$category] ?? 'none';
    }

    /**
     * Check if the co-parent has at least the specified permission level for a category.
     */
    public function hasPermission(string $category, string $requiredLevel = 'view'): bool
    {
        $levelHierarchy = ['none' => 0, 'view' => 1, 'edit' => 2];

        $actualLevel = $levelHierarchy[$this->getPermission($category)] ?? 0;
        $required = $levelHierarchy[$requiredLevel] ?? 0;

        return $actualLevel >= $required;
    }

    /**
     * Check if the co-parent can view a category.
     */
    public function canView(string $category): bool
    {
        return $this->hasPermission($category, 'view');
    }

    /**
     * Check if the co-parent can edit a category.
     */
    public function canEdit(string $category): bool
    {
        return $this->hasPermission($category, 'edit');
    }

    /**
     * Set permission for a category.
     */
    public function setPermission(string $category, string $level): void
    {
        $permissions = $this->permissions ?? [];
        $permissions[$category] = $level;
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Get all granted permissions (excluding 'none').
     */
    public function getGrantedPermissions(): array
    {
        $permissions = $this->permissions ?? [];
        return array_filter($permissions, fn($level) => $level !== 'none');
    }

    /**
     * Get default permissions array with all categories set to 'none'.
     */
    public static function getDefaultPermissions(): array
    {
        return array_fill_keys(array_keys(self::PERMISSION_CATEGORIES), 'none');
    }
}
