<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetShare extends Model
{
    protected $fillable = [
        'budget_id',
        'collaborator_id',
        'permission',
    ];

    // Permission levels
    public const PERMISSION_VIEW = 'view';
    public const PERMISSION_EDIT = 'edit';
    public const PERMISSION_ADMIN = 'admin';

    public const PERMISSIONS = [
        self::PERMISSION_VIEW => [
            'label' => 'View Only',
            'description' => 'Can view budget, categories, and transactions',
            'can_view' => true,
            'can_add_transactions' => false,
            'can_edit_categories' => false,
            'can_delete_budget' => false,
        ],
        self::PERMISSION_EDIT => [
            'label' => 'Editor',
            'description' => 'Can view and add/edit transactions',
            'can_view' => true,
            'can_add_transactions' => true,
            'can_edit_categories' => false,
            'can_delete_budget' => false,
        ],
        self::PERMISSION_ADMIN => [
            'label' => 'Admin',
            'description' => 'Full access including categories and budget settings',
            'can_view' => true,
            'can_add_transactions' => true,
            'can_edit_categories' => true,
            'can_delete_budget' => true,
        ],
    ];

    // ==================== RELATIONSHIPS ====================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    // ==================== ACCESSORS ====================

    public function getPermissionInfoAttribute(): array
    {
        return self::PERMISSIONS[$this->permission] ?? self::PERMISSIONS[self::PERMISSION_VIEW];
    }

    public function getPermissionLabelAttribute(): string
    {
        return $this->permission_info['label'];
    }

    public function getPermissionDescriptionAttribute(): string
    {
        return $this->permission_info['description'];
    }

    // ==================== METHODS ====================

    /**
     * Check if can view budget.
     */
    public function canView(): bool
    {
        return $this->permission_info['can_view'] ?? false;
    }

    /**
     * Check if can add/edit transactions.
     */
    public function canAddTransactions(): bool
    {
        return $this->permission_info['can_add_transactions'] ?? false;
    }

    /**
     * Check if can edit categories.
     */
    public function canEditCategories(): bool
    {
        return $this->permission_info['can_edit_categories'] ?? false;
    }

    /**
     * Check if can delete budget.
     */
    public function canDeleteBudget(): bool
    {
        return $this->permission_info['can_delete_budget'] ?? false;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->permission === self::PERMISSION_ADMIN;
    }

    /**
     * Check if user is editor or admin.
     */
    public function isEditorOrAbove(): bool
    {
        return in_array($this->permission, [self::PERMISSION_EDIT, self::PERMISSION_ADMIN]);
    }

    /**
     * Update permission level.
     */
    public function updatePermission(string $permission): void
    {
        if (array_key_exists($permission, self::PERMISSIONS)) {
            $this->permission = $permission;
            $this->save();
        }
    }

    /**
     * Get the shared user (through collaborator).
     */
    public function getSharedUser(): ?User
    {
        return $this->collaborator?->user;
    }

    /**
     * Get display name for the shared user.
     */
    public function getSharedUserName(): string
    {
        $user = $this->getSharedUser();
        if ($user) {
            return $user->name;
        }

        return $this->collaborator?->email ?? 'Unknown';
    }
}
