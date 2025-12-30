<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a tenant (Family Circle).
 * Automatically scopes queries to the current tenant in single-database mode.
 *
 * Note: This trait should NOT be used on the User model to avoid infinite loops.
 * For User model, tenant scoping is handled explicitly in queries.
 */
trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant(): void
    {
        // Automatically set tenant_id when creating records
        static::creating(function (Model $model) {
            if (!$model->tenant_id) {
                // Get tenant_id from session or authenticated user
                $tenantId = session('tenant_id');
                if (!$tenantId && auth()->check()) {
                    $tenantId = auth()->user()->tenant_id;
                }
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    /**
     * Get the tenant that owns this model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to a specific tenant.
     */
    public function scopeForTenant(Builder $query, $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Scope a query to the current user's tenant.
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where($this->getTable() . '.tenant_id', auth()->user()->tenant_id);
        }
        return $query->whereRaw('1 = 0'); // Return no results if not authenticated
    }
}
