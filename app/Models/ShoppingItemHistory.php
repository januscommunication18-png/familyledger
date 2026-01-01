<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ShoppingItemHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'shopping_item_history';

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'quantity',
        'purchase_count',
        'last_purchased_at',
    ];

    protected $casts = [
        'purchase_count' => 'integer',
        'last_purchased_at' => 'datetime',
    ];

    /**
     * Record a purchase of an item.
     */
    public static function recordPurchase(string $tenantId, string $name, string $category, ?string $quantity = null): self
    {
        return static::updateOrCreate(
            ['tenant_id' => $tenantId, 'name' => strtolower(trim($name))],
            [
                'category' => $category,
                'quantity' => $quantity,
                'last_purchased_at' => now(),
            ]
        )->tap(function ($item) {
            $item->increment('purchase_count');
        });
    }

    /**
     * Get frequently bought items.
     */
    public static function getFrequentItems(string $tenantId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('tenant_id', $tenantId)
            ->orderByDesc('purchase_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Search items by name.
     */
    public static function searchByName(string $tenantId, string $query, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('tenant_id', $tenantId)
            ->where('name', 'like', '%' . strtolower($query) . '%')
            ->orderByDesc('purchase_count')
            ->limit($limit)
            ->get();
    }
}
