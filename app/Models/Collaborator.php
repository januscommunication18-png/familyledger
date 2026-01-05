<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collaborator extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'invited_by',
        'invite_id',
        'relationship_type',
        'role',
        'is_active',
        'deactivated_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    // Use constants from CollaboratorInvite
    public const RELATIONSHIP_TYPES = CollaboratorInvite::RELATIONSHIP_TYPES;
    public const ROLES = CollaboratorInvite::ROLES;
    public const PERMISSION_CATEGORIES = CollaboratorInvite::PERMISSION_CATEGORIES;
    public const PERMISSION_LEVELS = CollaboratorInvite::PERMISSION_LEVELS;

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invite(): BelongsTo
    {
        return $this->belongsTo(CollaboratorInvite::class, 'invite_id');
    }

    public function familyMembers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'collaborator_family_member')
            ->withPivot('permissions')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getRelationshipInfoAttribute(): array
    {
        return self::RELATIONSHIP_TYPES[$this->relationship_type] ?? self::RELATIONSHIP_TYPES['other'];
    }

    public function getRoleInfoAttribute(): array
    {
        return self::ROLES[$this->role] ?? self::ROLES['viewer'];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user->name ?? 'Unknown User';
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email ?? '';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->user->avatar_url ?? null;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeWithRelationship($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    // ==================== METHODS ====================

    public function activate(): void
    {
        $this->update([
            'is_active' => true,
            'deactivated_at' => null,
        ]);
    }

    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    public function updateRole(string $role): void
    {
        $this->update(['role' => $role]);
    }

    public function canAccessFamilyMember(int $familyMemberId): bool
    {
        return $this->familyMembers()->where('family_member_id', $familyMemberId)->exists();
    }

    public function getPermissionsForMember(int $familyMemberId): array
    {
        $member = $this->familyMembers()->where('family_member_id', $familyMemberId)->first();

        if (!$member) {
            return [];
        }

        return json_decode($member->pivot->permissions ?? '{}', true) ?: [];
    }

    public function hasPermission(int $familyMemberId, string $category, string $level = 'view'): bool
    {
        $permissions = $this->getPermissionsForMember($familyMemberId);
        $memberPermission = $permissions[$category] ?? 'none';

        // Permission hierarchy: full > edit > view > none
        $levelHierarchy = ['none' => 0, 'view' => 1, 'edit' => 2, 'full' => 3];

        $requiredLevel = $levelHierarchy[$level] ?? 0;
        $actualLevel = $levelHierarchy[$memberPermission] ?? 0;

        return $actualLevel >= $requiredLevel;
    }

    public function syncFamilyMembers(array $memberPermissions): void
    {
        $syncData = [];

        foreach ($memberPermissions as $memberId => $permissions) {
            $syncData[$memberId] = [
                'permissions' => json_encode($permissions),
            ];
        }

        $this->familyMembers()->sync($syncData);
    }

    public function getAccessSummary(): array
    {
        $summary = [];

        foreach ($this->familyMembers as $member) {
            $permissions = json_decode($member->pivot->permissions ?? '{}', true) ?: [];
            $accessList = [];

            foreach ($permissions as $category => $level) {
                if ($level !== 'none') {
                    $categoryInfo = self::PERMISSION_CATEGORIES[$category] ?? ['label' => $category];
                    $levelInfo = self::PERMISSION_LEVELS[$level] ?? ['label' => $level, 'color' => 'slate-400'];
                    $accessList[] = [
                        'category' => $category,
                        'label' => $categoryInfo['label'],
                        'level' => $level,
                        'level_label' => $levelInfo['label'] ?? $level,
                        'level_color' => $levelInfo['color'] ?? 'slate-400',
                    ];
                }
            }

            $summary[] = [
                'member' => $member,
                'access' => $accessList,
            ];
        }

        return $summary;
    }

    /**
     * Check if collaborator is the owner role
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if collaborator is admin or higher
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /**
     * Check if collaborator can edit data
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'contributor']);
    }
}
