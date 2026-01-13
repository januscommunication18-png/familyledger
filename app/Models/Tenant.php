<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant model representing a Family Circle.
 * Uses single-database multi-tenancy (tenant_id column on related tables).
 */
class Tenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'timezone',
        'family_type',
        'goals',
        'quick_setup',
        'onboarding_completed',
        'onboarding_skipped',
        'onboarding_step',
        'data',
        'subscription_tier',
        'subscription_expires_at',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'goals' => 'array',
        'quick_setup' => 'array',
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'onboarding_completed' => 'boolean',
        'onboarding_skipped' => 'boolean',
    ];

    protected $attributes = [
        'subscription_tier' => 'free',
        'is_active' => true,
    ];

    /**
     * Get the users belonging to this tenant (family circle).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the tenant has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->subscription_expires_at === null) {
            return true; // Lifetime or free tier
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Get a setting from the data JSON.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Set a setting in the data JSON.
     */
    public function setSetting(string $key, mixed $value): self
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;

        return $this;
    }
}
