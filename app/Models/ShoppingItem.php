<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingItem extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'shopping_list_id',
        'name',
        'category',
        'quantity',
        'notes',
        'is_checked',
        'added_by',
        'checked_by',
        'checked_at',
        'sort_order',
        'version',
        'last_modified_device',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Shopping item categories.
     */
    public const CATEGORIES = [
        'produce' => 'Produce',
        'dairy' => 'Dairy & Eggs',
        'meat' => 'Meat & Seafood',
        'bakery' => 'Bakery',
        'frozen' => 'Frozen',
        'pantry' => 'Pantry',
        'snacks' => 'Snacks',
        'beverages' => 'Beverages',
        'household' => 'Household',
        'personal_care' => 'Personal Care',
        'baby' => 'Baby Care',
        'pet' => 'Pet Supplies',
        'pharmacy' => 'Pharmacy',
        'other' => 'Other',
    ];

    /**
     * Get the shopping list this item belongs to.
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    /**
     * Get the user who added this item.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the user who checked this item.
     */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Get the category name.
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get category icon class.
     */
    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'produce' => 'icon-[tabler--apple]',
            'dairy' => 'icon-[tabler--egg]',
            'meat' => 'icon-[tabler--meat]',
            'bakery' => 'icon-[tabler--bread]',
            'frozen' => 'icon-[tabler--snowflake]',
            'pantry' => 'icon-[tabler--bottle]',
            'snacks' => 'icon-[tabler--cookie]',
            'beverages' => 'icon-[tabler--cup]',
            'household' => 'icon-[tabler--home]',
            'personal_care' => 'icon-[tabler--bath]',
            'baby' => 'icon-[tabler--baby-carriage]',
            'pet' => 'icon-[tabler--paw]',
            'pharmacy' => 'icon-[tabler--pill]',
            default => 'icon-[tabler--package]',
        };
    }

    /**
     * Get category color class.
     */
    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'produce' => 'bg-green-100 text-green-700',
            'dairy' => 'bg-amber-100 text-amber-700',
            'meat' => 'bg-red-100 text-red-700',
            'bakery' => 'bg-orange-100 text-orange-700',
            'frozen' => 'bg-sky-100 text-sky-700',
            'pantry' => 'bg-stone-100 text-stone-700',
            'snacks' => 'bg-pink-100 text-pink-700',
            'beverages' => 'bg-blue-100 text-blue-700',
            'household' => 'bg-slate-100 text-slate-700',
            'personal_care' => 'bg-purple-100 text-purple-700',
            'baby' => 'bg-rose-100 text-rose-700',
            'pet' => 'bg-teal-100 text-teal-700',
            'pharmacy' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Toggle checked status.
     */
    public function toggleChecked(int $userId): void
    {
        if ($this->is_checked) {
            $this->update([
                'is_checked' => false,
                'checked_by' => null,
                'checked_at' => null,
            ]);
        } else {
            $this->update([
                'is_checked' => true,
                'checked_by' => $userId,
                'checked_at' => now(),
            ]);
        }
    }
}
