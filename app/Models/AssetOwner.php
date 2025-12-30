<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetOwner extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'family_member_id',
        'external_owner_name',
        'external_owner_email',
        'external_owner_phone',
        'ownership_percentage',
        'is_primary_owner',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'is_primary_owner' => 'boolean',
    ];

    /**
     * Get the asset this owner belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the family member if linked.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Check if this is a family member owner.
     */
    public function isFamilyMember(): bool
    {
        return $this->family_member_id !== null;
    }

    /**
     * Check if this is an external owner.
     */
    public function isExternalOwner(): bool
    {
        return $this->family_member_id === null && $this->external_owner_name !== null;
    }

    /**
     * Get the owner's display name.
     */
    public function getOwnerNameAttribute(): string
    {
        if ($this->isFamilyMember() && $this->familyMember) {
            return $this->familyMember->full_name;
        }

        return $this->external_owner_name ?? 'Unknown Owner';
    }

    /**
     * Get the owner's email.
     */
    public function getOwnerEmailAttribute(): ?string
    {
        if ($this->isFamilyMember() && $this->familyMember) {
            return $this->familyMember->email;
        }

        return $this->external_owner_email;
    }

    /**
     * Get the owner's phone.
     */
    public function getOwnerPhoneAttribute(): ?string
    {
        if ($this->isFamilyMember() && $this->familyMember) {
            return $this->familyMember->phone;
        }

        return $this->external_owner_phone;
    }

    /**
     * Get formatted ownership percentage.
     */
    public function getFormattedOwnershipPercentageAttribute(): string
    {
        return number_format($this->ownership_percentage, 2) . '%';
    }
}
