<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagePlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'trial_period_days',
        'cost_per_month',
        'cost_per_year',
        'family_circles_limit',
        'family_members_limit',
        'document_storage_limit',
        'reminder_features',
        'paddle_product_id',
        'paddle_monthly_price_id',
        'paddle_yearly_price_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'cost_per_month' => 'decimal:2',
        'cost_per_year' => 'decimal:2',
        'reminder_features' => 'array',
        'is_active' => 'boolean',
        'trial_period_days' => 'integer',
        'family_circles_limit' => 'integer',
        'family_members_limit' => 'integer',
        'document_storage_limit' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Plan types.
     */
    public const TYPE_FREE = 'free';
    public const TYPE_PAID = 'paid';

    public const TYPES = [
        self::TYPE_FREE => 'Free Plan',
        self::TYPE_PAID => 'Paid Plan',
    ];

    /**
     * Available reminder features.
     */
    public const REMINDER_PUSH = 'push_notification';
    public const REMINDER_EMAIL = 'email_reminder';
    public const REMINDER_SMS = 'sms_reminder';

    public const REMINDER_FEATURES = [
        self::REMINDER_PUSH => 'Push Notification',
        self::REMINDER_EMAIL => 'Email Reminder',
        self::REMINDER_SMS => 'SMS Reminder',
    ];

    /**
     * Get the discount codes for this plan.
     */
    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    /**
     * Check if the plan is free.
     */
    public function isFree(): bool
    {
        return $this->type === self::TYPE_FREE;
    }

    /**
     * Check if the plan is paid.
     */
    public function isPaid(): bool
    {
        return $this->type === self::TYPE_PAID;
    }

    /**
     * Check if a feature limit is unlimited (0 = unlimited).
     */
    public function isUnlimited(string $feature): bool
    {
        return ($this->{$feature} ?? 0) === 0;
    }

    /**
     * Get formatted limit display.
     */
    public function getFormattedLimit(string $feature): string
    {
        $value = $this->{$feature} ?? 0;
        return $value === 0 ? 'Unlimited' : (string) $value;
    }

    /**
     * Scope active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
