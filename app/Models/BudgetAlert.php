<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAlert extends Model
{
    protected $fillable = [
        'budget_id',
        'category_id',
        'type',
        'threshold',
        'is_active',
        'last_triggered_at',
    ];

    protected $casts = [
        'threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    // Alert types
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_AMOUNT = 'amount';

    public const TYPES = [
        self::TYPE_PERCENTAGE => [
            'label' => 'Percentage',
            'description' => 'Alert when spending reaches a percentage of budget',
            'suffix' => '%',
        ],
        self::TYPE_AMOUNT => [
            'label' => 'Amount',
            'description' => 'Alert when spending reaches a specific amount',
            'suffix' => '',
        ],
    ];

    // Common percentage thresholds
    public const COMMON_THRESHOLDS = [50, 75, 80, 90, 100];

    // ==================== RELATIONSHIPS ====================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBudget($query, int $budgetId)
    {
        return $query->where('budget_id', $budgetId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBudgetWide($query)
    {
        return $query->whereNull('category_id');
    }

    // ==================== ACCESSORS ====================

    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES[self::TYPE_PERCENTAGE];
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type_info['label'];
    }

    public function getThresholdDisplayAttribute(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return $this->threshold . '%';
        }

        return '$' . number_format($this->threshold, 2);
    }

    public function getTargetNameAttribute(): string
    {
        if ($this->category) {
            return $this->category->name;
        }

        return 'Overall Budget';
    }

    public function getDescriptionAttribute(): string
    {
        $target = $this->target_name;

        if ($this->type === self::TYPE_PERCENTAGE) {
            return "Alert when {$target} reaches {$this->threshold}% of budget";
        }

        return "Alert when {$target} spending reaches \${$this->threshold}";
    }

    // ==================== METHODS ====================

    /**
     * Check if this alert should trigger based on current spending.
     */
    public function shouldTrigger(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $currentValue = $this->getCurrentValue();
        $thresholdValue = $this->getThresholdValue();

        return $currentValue >= $thresholdValue;
    }

    /**
     * Get current spending value for this alert.
     */
    public function getCurrentValue(): float
    {
        if ($this->category_id) {
            return $this->category->getSpentAmount();
        }

        return $this->budget->getTotalSpent();
    }

    /**
     * Get threshold value in dollars.
     */
    public function getThresholdValue(): float
    {
        if ($this->type === self::TYPE_AMOUNT) {
            return $this->threshold;
        }

        // Percentage type - calculate based on budget/category amount
        $totalAmount = $this->category_id
            ? $this->category->allocated_amount
            : $this->budget->total_amount;

        return ($this->threshold / 100) * $totalAmount;
    }

    /**
     * Get current progress percentage toward threshold.
     */
    public function getProgressPercentage(): float
    {
        $thresholdValue = $this->getThresholdValue();

        if ($thresholdValue <= 0) {
            return 0;
        }

        $current = $this->getCurrentValue();
        return min(100, round(($current / $thresholdValue) * 100, 1));
    }

    /**
     * Mark alert as triggered.
     */
    public function markTriggered(): void
    {
        $this->last_triggered_at = now();
        $this->save();
    }

    /**
     * Check if alert was recently triggered (within 24 hours).
     */
    public function wasRecentlyTriggered(): bool
    {
        if (!$this->last_triggered_at) {
            return false;
        }

        return $this->last_triggered_at->gt(now()->subHours(24));
    }

    /**
     * Trigger the alert and send notification.
     */
    public function trigger(): void
    {
        if ($this->wasRecentlyTriggered()) {
            return; // Don't trigger again within 24 hours
        }

        $this->markTriggered();

        // TODO: Send notification to budget owner and shared users
        // This could be email, push notification, or in-app notification
    }

    /**
     * Check and trigger if threshold is met.
     */
    public function checkAndTrigger(): bool
    {
        if ($this->shouldTrigger() && !$this->wasRecentlyTriggered()) {
            $this->trigger();
            return true;
        }

        return false;
    }

    /**
     * Get status label.
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'Disabled';
        }

        if ($this->shouldTrigger()) {
            return 'Triggered';
        }

        $progress = $this->getProgressPercentage();

        if ($progress >= 90) {
            return 'Warning';
        }

        return 'Active';
    }

    /**
     * Get status color.
     */
    public function getStatusColor(): string
    {
        return match ($this->getStatus()) {
            'Disabled' => 'gray',
            'Triggered' => 'red',
            'Warning' => 'amber',
            default => 'green',
        };
    }
}
