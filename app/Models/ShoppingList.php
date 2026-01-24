<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingList extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'store',
        'color',
        'is_default',
        'recurring',
        'version',
        'last_modified_device',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Recurring frequencies.
     */
    public const RECURRING_FREQUENCIES = [
        'none' => 'Not Recurring',
        'weekly' => 'Weekly',
        'bi_weekly' => 'Bi-Weekly',
        'monthly' => 'Monthly',
    ];

    /**
     * Get the recurring label.
     */
    public function getRecurringLabelAttribute(): ?string
    {
        return $this->recurring ? (self::RECURRING_FREQUENCIES[$this->recurring] ?? null) : null;
    }

    /**
     * Store types.
     */
    public const STORES = [
        'grocery' => 'Grocery Store',
        'costco' => 'Costco',
        'sams_club' => 'Sam\'s Club',
        'target' => 'Target',
        'walmart' => 'Walmart',
        'amazon' => 'Amazon',
        'whole_foods' => 'Whole Foods',
        'trader_joes' => 'Trader Joe\'s',
        'pharmacy' => 'Pharmacy',
        'home_depot' => 'Home Depot',
        'lowes' => 'Lowe\'s',
        'other' => 'Other',
    ];

    /**
     * Available colors for lists.
     */
    public const COLORS = [
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'sky' => 'Sky',
        'blue' => 'Blue',
        'violet' => 'Violet',
        'amber' => 'Amber',
        'orange' => 'Orange',
        'rose' => 'Rose',
    ];

    /**
     * Get the items for this list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingItem::class);
    }

    /**
     * Get the tenant that owns this list.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get family members this list is shared with.
     */
    public function sharedWithMembers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'shopping_list_shares')
            ->withTimestamps();
    }

    /**
     * Get unchecked items count.
     */
    public function getUncheckedCountAttribute(): int
    {
        return $this->items()->where('is_checked', false)->count();
    }

    /**
     * Get checked items count.
     */
    public function getCheckedCountAttribute(): int
    {
        return $this->items()->where('is_checked', true)->count();
    }

    /**
     * Get the store name.
     */
    public function getStoreNameAttribute(): ?string
    {
        return $this->store ? (self::STORES[$this->store] ?? $this->store) : null;
    }

    /**
     * Get color class for display.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->color) {
            'emerald' => 'bg-emerald-500',
            'teal' => 'bg-teal-500',
            'sky' => 'bg-sky-500',
            'blue' => 'bg-blue-500',
            'violet' => 'bg-violet-500',
            'amber' => 'bg-amber-500',
            'orange' => 'bg-orange-500',
            'rose' => 'bg-rose-500',
            default => 'bg-emerald-500',
        };
    }

    /**
     * Get store icon class.
     */
    public function getStoreIconAttribute(): string
    {
        return match($this->store) {
            'costco', 'sams_club' => 'icon-[tabler--building-warehouse]',
            'target' => 'icon-[tabler--target]',
            'amazon' => 'icon-[tabler--brand-amazon]',
            'pharmacy' => 'icon-[tabler--pill]',
            'home_depot', 'lowes' => 'icon-[tabler--tools]',
            default => 'icon-[tabler--shopping-cart]',
        };
    }
}
