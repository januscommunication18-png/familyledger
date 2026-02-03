<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PendingCoparentEdit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'editable_type',
        'editable_id',
        'family_member_id',
        'field_name',
        'old_value',
        'new_value',
        'is_create',
        'create_data',
        'is_delete',
        'requested_by',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'ip_address',
        'request_notes',
    ];

    protected $casts = [
        'is_create' => 'boolean',
        'is_delete' => 'boolean',
        'create_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';

    public const STATUSES = [
        self::STATUS_PENDING => ['label' => 'Pending', 'color' => 'warning', 'icon' => 'tabler--clock'],
        self::STATUS_APPROVED => ['label' => 'Approved', 'color' => 'success', 'icon' => 'tabler--check'],
        self::STATUS_REJECTED => ['label' => 'Rejected', 'color' => 'error', 'icon' => 'tabler--x'],
        self::STATUS_CANCELED => ['label' => 'Canceled', 'color' => 'slate-400', 'icon' => 'tabler--ban'],
    ];

    // Map editable_type to human-readable names
    public const EDITABLE_TYPE_LABELS = [
        'App\\Models\\FamilyMember' => 'Basic Info',
        'App\\Models\\MemberMedicalInfo' => 'Medical Info',
        'App\\Models\\MemberDocument' => 'Document',
        'App\\Models\\MemberAllergy' => 'Allergy',
        'App\\Models\\MemberMedication' => 'Medication',
        'App\\Models\\MemberMedicalCondition' => 'Medical Condition',
        'App\\Models\\MemberHealthcareProvider' => 'Healthcare Provider',
        'App\\Models\\MemberContact' => 'Emergency Contact',
        'App\\Models\\MemberSchoolInfo' => 'School Info',
        'App\\Models\\MemberVaccination' => 'Vaccination',
        'App\\Models\\Asset' => 'Asset',
        'App\\Models\\AssetOwner' => 'Asset Owner',
        'App\\Models\\AssetDocument' => 'Asset Document',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the editable model (polymorphic).
     */
    public function editable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the family member this edit relates to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the user who requested the edit (coparent).
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who reviewed the edit (owner).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get status info (label, color, icon).
     */
    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES[self::STATUS_PENDING];
    }

    /**
     * Get human-readable editable type label.
     */
    public function getEditableTypeLabelAttribute(): string
    {
        return self::EDITABLE_TYPE_LABELS[$this->editable_type] ?? 'Unknown';
    }

    /**
     * Get human-readable field label.
     */
    public function getFieldLabelAttribute(): string
    {
        // Check if it's a special field first
        if ($this->is_create) {
            return 'New ' . $this->editable_type_label;
        }
        if ($this->is_delete) {
            return 'Delete ' . $this->editable_type_label;
        }

        // Use MemberAuditLog field labels
        return MemberAuditLog::FIELD_LABELS[$this->field_name]
            ?? ucfirst(str_replace('_', ' ', $this->field_name));
    }

    /**
     * Check if pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get formatted old value for display.
     */
    public function getFormattedOldValueAttribute(): ?string
    {
        if ($this->is_create) {
            return null;
        }
        if ($this->is_delete) {
            return 'Record exists';
        }
        return $this->old_value ?: '(empty)';
    }

    /**
     * Get formatted new value for display.
     */
    public function getFormattedNewValueAttribute(): ?string
    {
        if ($this->is_create) {
            return 'New record';
        }
        if ($this->is_delete) {
            return 'Will be deleted';
        }
        return $this->new_value ?: '(empty)';
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only pending edits.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to only approved edits.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to only rejected edits.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to edits for a specific owner (via family member's tenant).
     */
    public function scopeForOwner($query, int $ownerId)
    {
        return $query->whereHas('familyMember', function ($q) use ($ownerId) {
            $q->whereHas('familyCircle', function ($q2) use ($ownerId) {
                $q2->whereHas('tenant', function ($q3) use ($ownerId) {
                    $q3->where('user_id', $ownerId);
                });
            });
        });
    }

    // ==================== METHODS ====================

    /**
     * Approve this pending edit.
     */
    public function approve(User $reviewer, ?string $notes = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Apply the edit to the actual record
        $this->applyEdit();

        return true;
    }

    /**
     * Reject this pending edit.
     */
    public function reject(User $reviewer, ?string $notes = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return true;
    }

    /**
     * Cancel this pending edit (by the requester).
     */
    public function cancel(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELED,
        ]);

        return true;
    }

    /**
     * Apply the approved edit to the actual record.
     */
    protected function applyEdit(): void
    {
        if ($this->is_create && $this->create_data) {
            // Create new record
            $modelClass = $this->editable_type;

            // Special handling for Asset creation (needs owners created too)
            if ($modelClass === 'App\\Models\\Asset') {
                $this->applyAssetCreate();
                return;
            }

            $modelClass::create($this->create_data);
        } elseif ($this->is_delete) {
            // Delete the record
            $this->editable?->delete();
        } else {
            // Update existing record
            $model = $this->editable;
            if ($model) {
                $model->update([$this->field_name => $this->new_value]);
            }
        }
    }

    /**
     * Apply asset creation with owners.
     */
    protected function applyAssetCreate(): void
    {
        $data = $this->create_data;
        $owners = $data['_owners'] ?? [];
        unset($data['_owners']);

        // Create the asset
        $asset = Asset::create($data);

        // Create owners
        foreach ($owners as $ownerData) {
            AssetOwner::create([
                'asset_id' => $asset->id,
                'family_member_id' => $ownerData['family_member_id'] ?? null,
                'external_owner_name' => $ownerData['external_owner_name'] ?? null,
                'ownership_percentage' => $ownerData['ownership_percentage'] ?? 100,
                'is_primary_owner' => $ownerData['is_primary_owner'] ?? false,
            ]);
        }
    }

    /**
     * Get pending edits count for a tenant.
     */
    public static function getPendingCountForTenant(string $tenantId): int
    {
        return static::where('tenant_id', $tenantId)
            ->pending()
            ->count();
    }
}
