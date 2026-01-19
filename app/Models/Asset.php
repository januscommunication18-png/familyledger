<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'image',
        'asset_category',
        'asset_type',
        'ownership_type',
        'status',
        'description',
        'notes',
        'acquisition_date',
        'purchase_value',
        'current_value',
        'currency',
        // Location fields
        'location_address',
        'location_city',
        'location_state',
        'location_zip',
        'location_country',
        'storage_location',
        // Vehicle-specific fields
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'vin_registration',
        'vehicle_ownership',
        'license_plate',
        'mileage',
        // Collectable-specific fields
        'collectable_category',
        'appraised_by',
        'appraisal_date',
        'appraisal_value',
        'condition',
        'provenance',
        // Inventory-specific fields
        'serial_number',
        'warranty_expiry',
        'room_location',
        // Insurance linkage
        'insurance_provider',
        'insurance_policy_number',
        'insurance_renewal_date',
        'is_insured',
        // Security
        'is_encrypted',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'purchase_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'appraisal_date' => 'date',
        'appraisal_value' => 'decimal:2',
        'warranty_expiry' => 'date',
        'insurance_renewal_date' => 'date',
        'is_insured' => 'boolean',
        'is_encrypted' => 'boolean',
        'vehicle_year' => 'integer',
        'mileage' => 'integer',
    ];

    /**
     * Asset categories.
     */
    public const CATEGORIES = [
        'property' => 'Property',
        'vehicle' => 'Vehicles',
        'valuable' => 'Valuables & Collectables',
        'inventory' => 'Home Inventory',
    ];

    /**
     * Property types.
     */
    public const PROPERTY_TYPES = [
        'primary_home' => 'Primary Home',
        'secondary_home' => 'Secondary / Vacation Home',
        'commercial' => 'Commercial Property',
        'land' => 'Land',
    ];

    /**
     * Vehicle types.
     */
    public const VEHICLE_TYPES = [
        'car' => 'Car',
        'motorcycle' => 'Motorcycle',
        'rv_camper' => 'RV / Camper',
        'boat' => 'Boat',
        'other_vehicle' => 'Other Vehicle',
    ];

    /**
     * Valuable types.
     */
    public const VALUABLE_TYPES = [
        'collectables' => 'Collectables',
        'electronics' => 'Electronics',
        'appliances' => 'Appliances',
        'furniture' => 'Furniture',
        'tools' => 'Tools',
        'high_value_item' => 'High-Value Item',
    ];

    /**
     * Collectable categories.
     */
    public const COLLECTABLE_CATEGORIES = [
        'art' => 'Art',
        'jewelry' => 'Jewelry',
        'watch' => 'Watch',
        'antique' => 'Antique',
        'coin' => 'Coins',
        'stamp' => 'Stamps',
        'wine' => 'Wine',
        'other' => 'Other',
    ];

    /**
     * Ownership types.
     */
    public const OWNERSHIP_TYPES = [
        'individual' => 'Individual',
        'joint' => 'Joint',
        'trust_company' => 'Trust / Company',
    ];

    /**
     * Vehicle ownership types.
     */
    public const VEHICLE_OWNERSHIP = [
        'owned' => 'Owned',
        'leased' => 'Leased',
        'financed' => 'Financed',
    ];

    /**
     * Condition types.
     */
    public const CONDITIONS = [
        'mint' => 'Mint',
        'excellent' => 'Excellent',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    /**
     * Status types.
     */
    public const STATUSES = [
        'active' => 'Active',
        'sold' => 'Sold',
        'disposed' => 'Disposed',
        'transferred' => 'Transferred',
    ];

    /**
     * Document types.
     */
    public const DOCUMENT_TYPES = [
        'deed' => 'Deed',
        'title' => 'Title',
        'registration' => 'Registration',
        'appraisal' => 'Appraisal',
        'insurance' => 'Insurance Policy',
        'receipt' => 'Receipt',
        'photo' => 'Photo',
        'service_record' => 'Service Record',
        'other' => 'Other',
    ];

    /**
     * Room locations.
     */
    public const ROOM_LOCATIONS = [
        'living_room' => 'Living Room',
        'bedroom' => 'Bedroom',
        'kitchen' => 'Kitchen',
        'bathroom' => 'Bathroom',
        'garage' => 'Garage',
        'basement' => 'Basement',
        'attic' => 'Attic',
        'office' => 'Office',
        'dining_room' => 'Dining Room',
        'outdoor' => 'Outdoor',
        'storage' => 'Storage',
        'other' => 'Other',
    ];

    /**
     * Get the owners for this asset.
     */
    public function owners(): HasMany
    {
        return $this->hasMany(AssetOwner::class);
    }

    /**
     * Get family member owners through the pivot.
     */
    public function familyMemberOwners(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'asset_owners')
            ->withPivot('ownership_percentage', 'is_primary_owner', 'external_owner_name')
            ->withTimestamps();
    }

    /**
     * Get the documents for this asset.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    /**
     * Get the primary owner.
     */
    public function getPrimaryOwnerAttribute(): ?AssetOwner
    {
        return $this->owners->where('is_primary_owner', true)->first();
    }

    /**
     * Check if asset is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'sold' => 'info',
            'disposed' => 'warning',
            'transferred' => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Get category icon.
     */
    public function getCategoryIcon(): string
    {
        return match ($this->asset_category) {
            'property' => 'icon-[tabler--home]',
            'vehicle' => 'icon-[tabler--car]',
            'valuable' => 'icon-[tabler--diamond]',
            'inventory' => 'icon-[tabler--box]',
            default => 'icon-[tabler--package]',
        };
    }

    /**
     * Get the types for a given category.
     */
    public static function getTypesForCategory(string $category): array
    {
        return match ($category) {
            'property' => self::PROPERTY_TYPES,
            'vehicle' => self::VEHICLE_TYPES,
            'valuable' => self::VALUABLE_TYPES,
            'inventory' => self::VALUABLE_TYPES,
            default => [],
        };
    }

    /**
     * Get category display name.
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->asset_category] ?? 'Unknown';
    }

    /**
     * Get type display name.
     */
    public function getTypeNameAttribute(): string
    {
        $types = self::getTypesForCategory($this->asset_category);
        return $types[$this->asset_type] ?? $this->asset_type ?? 'Unknown';
    }

    /**
     * Get ownership type display name.
     */
    public function getOwnershipTypeNameAttribute(): string
    {
        return self::OWNERSHIP_TYPES[$this->ownership_type] ?? 'Unknown';
    }

    /**
     * Get status display name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    /**
     * Get condition display name.
     */
    public function getConditionNameAttribute(): ?string
    {
        if (!$this->condition) {
            return null;
        }
        return self::CONDITIONS[$this->condition] ?? 'Unknown';
    }

    /**
     * Get vehicle ownership display name.
     */
    public function getVehicleOwnershipNameAttribute(): ?string
    {
        if (!$this->vehicle_ownership) {
            return null;
        }
        return self::VEHICLE_OWNERSHIP[$this->vehicle_ownership] ?? 'Unknown';
    }

    /**
     * Get collectable category display name.
     */
    public function getCollectableCategoryNameAttribute(): ?string
    {
        if (!$this->collectable_category) {
            return null;
        }
        return self::COLLECTABLE_CATEGORIES[$this->collectable_category] ?? 'Unknown';
    }

    /**
     * Get room location display name.
     */
    public function getRoomLocationNameAttribute(): ?string
    {
        if (!$this->room_location) {
            return null;
        }
        return self::ROOM_LOCATIONS[$this->room_location] ?? 'Unknown';
    }

    /**
     * Get formatted current value with currency.
     */
    public function getFormattedCurrentValueAttribute(): ?string
    {
        if ($this->current_value === null) {
            return null;
        }
        $symbol = $this->currency === 'USD' ? '$' : $this->currency . ' ';
        return $symbol . number_format($this->current_value, 2);
    }

    /**
     * Get formatted purchase value with currency.
     */
    public function getFormattedPurchaseValueAttribute(): ?string
    {
        if ($this->purchase_value === null) {
            return null;
        }
        $symbol = $this->currency === 'USD' ? '$' : $this->currency . ' ';
        return $symbol . number_format($this->purchase_value, 2);
    }

    /**
     * Get full location string.
     */
    public function getFullLocationAttribute(): ?string
    {
        $parts = array_filter([
            $this->location_address,
            $this->location_city,
            $this->location_state,
            $this->location_zip,
            $this->location_country,
        ]);

        return count($parts) > 0 ? implode(', ', $parts) : null;
    }

    /**
     * Get vehicle description.
     */
    public function getVehicleDescriptionAttribute(): ?string
    {
        if ($this->asset_category !== 'vehicle') {
            return null;
        }

        $parts = array_filter([
            $this->vehicle_year,
            $this->vehicle_make,
            $this->vehicle_model,
        ]);

        return count($parts) > 0 ? implode(' ', $parts) : null;
    }

    /**
     * Check if insurance is expiring soon (within 30 days).
     */
    public function isInsuranceExpiringSoon(): bool
    {
        if (!$this->insurance_renewal_date || !$this->is_insured) {
            return false;
        }
        return $this->insurance_renewal_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if warranty is expiring soon (within 30 days).
     */
    public function isWarrantyExpiringSoon(): bool
    {
        if (!$this->warranty_expiry) {
            return false;
        }
        return $this->warranty_expiry->isBetween(now(), now()->addDays(30));
    }

    /**
     * Get asset image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return \Illuminate\Support\Facades\Storage::disk('do_spaces')->url($this->image);
        }
        return null;
    }
}
