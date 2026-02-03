<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'package_plan_id',
        'billing_cycle',
        'paddle_customer_id',
        'paddle_subscription_id',
        'trial_ends_at',
        'subscription_expires_at',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'goals' => 'array',
        'quick_setup' => 'array',
        'subscription_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
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
     * Get the package plan for this tenant.
     */
    public function packagePlan(): BelongsTo
    {
        return $this->belongsTo(PackagePlan::class);
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
     * Check if tenant is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has ended.
     */
    public function trialEnded(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->onTrial()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    /**
     * Check if tenant is on free plan.
     */
    public function onFreePlan(): bool
    {
        return $this->subscription_tier === 'free' ||
               ($this->packagePlan && $this->packagePlan->type === 'free');
    }

    /**
     * Check if tenant is on paid plan.
     */
    public function onPaidPlan(): bool
    {
        return $this->subscription_tier === 'paid' ||
               ($this->packagePlan && $this->packagePlan->type === 'paid');
    }

    /**
     * Get the current plan or default free plan.
     */
    public function getCurrentPlan(): ?PackagePlan
    {
        if ($this->packagePlan) {
            return $this->packagePlan;
        }

        // Return default free plan if no plan assigned
        return PackagePlan::where('type', 'free')->active()->first();
    }

    /**
     * Check if tenant can use a feature based on their plan.
     */
    public function canUseFeature(string $feature): bool
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            return false;
        }

        $reminderFeatures = $plan->reminder_features ?? [];

        return in_array($feature, $reminderFeatures);
    }

    /**
     * Get limit for a feature (0 = unlimited).
     */
    public function getFeatureLimit(string $feature): int
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            return 0;
        }

        return $plan->{$feature} ?? 0;
    }

    /**
     * Check if tenant has reached limit for a feature.
     */
    public function hasReachedLimit(string $feature, int $currentCount): bool
    {
        $limit = $this->getFeatureLimit($feature);

        // 0 = unlimited
        if ($limit === 0) {
            return false;
        }

        return $currentCount >= $limit;
    }

    /**
     * Subscribe tenant to a plan.
     */
    public function subscribeToPlan(PackagePlan $plan, string $billingCycle = 'monthly', ?int $trialDays = null): self
    {
        $this->package_plan_id = $plan->id;
        $this->subscription_tier = $plan->type;
        $this->billing_cycle = $billingCycle;

        if ($trialDays !== null && $trialDays > 0) {
            $this->trial_ends_at = now()->addDays($trialDays);
        } elseif ($plan->trial_period_days > 0 && !$this->trial_ends_at) {
            $this->trial_ends_at = now()->addDays($plan->trial_period_days);
        }

        // Set subscription expiry based on billing cycle
        if ($plan->type === 'paid') {
            if ($billingCycle === 'yearly') {
                $this->subscription_expires_at = now()->addYear();
            } else {
                $this->subscription_expires_at = now()->addMonth();
            }
        } else {
            // Free plans don't expire
            $this->subscription_expires_at = null;
        }

        $this->save();

        return $this;
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
