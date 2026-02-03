<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'discount_percentage',
        'plan_type',
        'package_plan_id',
        'max_uses',
        'times_used',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'times_used' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Plan types.
     */
    public const PLAN_TYPE_MONTHLY = 'monthly';
    public const PLAN_TYPE_YEARLY = 'yearly';
    public const PLAN_TYPE_BOTH = 'both';

    public const PLAN_TYPES = [
        self::PLAN_TYPE_MONTHLY => 'Monthly',
        self::PLAN_TYPE_YEARLY => 'Yearly',
        self::PLAN_TYPE_BOTH => 'Both',
    ];

    /**
     * Get the package plan this discount is for.
     */
    public function packagePlan(): BelongsTo
    {
        return $this->belongsTo(PackagePlan::class);
    }

    /**
     * Check if the discount code is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check date validity
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check usage limit
        if ($this->max_uses !== null && $this->times_used >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discounted price for monthly plan.
     */
    public function calculateDiscountedMonthlyPrice(PackagePlan $plan): float
    {
        if (!in_array($this->plan_type, [self::PLAN_TYPE_MONTHLY, self::PLAN_TYPE_BOTH])) {
            return (float) $plan->cost_per_month;
        }

        $discount = (float) $plan->cost_per_month * ((float) $this->discount_percentage / 100);
        return (float) $plan->cost_per_month - $discount;
    }

    /**
     * Calculate discounted price for yearly plan.
     */
    public function calculateDiscountedYearlyPrice(PackagePlan $plan): float
    {
        if (!in_array($this->plan_type, [self::PLAN_TYPE_YEARLY, self::PLAN_TYPE_BOTH])) {
            return (float) $plan->cost_per_year;
        }

        $discount = (float) $plan->cost_per_year * ((float) $this->discount_percentage / 100);
        return (float) $plan->cost_per_year - $discount;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }

    /**
     * Check if discount has remaining uses.
     */
    public function hasRemainingUses(): bool
    {
        if ($this->max_uses === null) {
            return true; // Unlimited
        }
        return $this->times_used < $this->max_uses;
    }

    /**
     * Get remaining uses count.
     */
    public function getRemainingUses(): ?int
    {
        if ($this->max_uses === null) {
            return null; // Unlimited
        }
        return max(0, $this->max_uses - $this->times_used);
    }

    /**
     * Scope active discounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope valid discounts (active + within date range + has remaining uses).
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereRaw('times_used < max_uses');
            });
    }
}
