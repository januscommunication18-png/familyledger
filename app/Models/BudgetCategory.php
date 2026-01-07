<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    protected $fillable = [
        'budget_id',
        'name',
        'icon',
        'color',
        'allocated_amount',
        'spent_amount',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Default category icons (emoji)
    public const DEFAULT_ICONS = [
        'housing' => '🏠',
        'utilities' => '💡',
        'groceries' => '🛒',
        'transportation' => '🚗',
        'healthcare' => '🏥',
        'insurance' => '🛡️',
        'dining' => '🍽️',
        'entertainment' => '🎬',
        'shopping' => '🛍️',
        'personal' => '💄',
        'education' => '📚',
        'savings' => '💰',
        'debt' => '💳',
        'gifts' => '🎁',
        'travel' => '✈️',
        'subscriptions' => '📱',
        'pets' => '🐾',
        'kids' => '👶',
        'other' => '📦',
    ];

    // Default category colors
    public const DEFAULT_COLORS = [
        '#ef4444', // red
        '#f97316', // orange
        '#f59e0b', // amber
        '#eab308', // yellow
        '#84cc16', // lime
        '#22c55e', // green
        '#10b981', // emerald
        '#14b8a6', // teal
        '#06b6d4', // cyan
        '#0ea5e9', // sky
        '#3b82f6', // blue
        '#6366f1', // indigo
        '#8b5cf6', // violet
        '#a855f7', // purple
        '#d946ef', // fuchsia
        '#ec4899', // pink
        '#f43f5e', // rose
    ];

    // ==================== RELATIONSHIPS ====================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class, 'category_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class, 'category_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ==================== ACCESSORS ====================

    public function getDisplayIconAttribute(): string
    {
        return $this->icon ?: '📦';
    }

    public function getDisplayColorAttribute(): string
    {
        return $this->color ?: '#6366f1';
    }

    // ==================== METHODS ====================

    /**
     * Get actual spent amount from transactions.
     */
    public function getSpentAmount(): float
    {
        return (float) $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');
    }

    /**
     * Get remaining amount (allocated - spent).
     */
    public function getRemainingAmount(): float
    {
        return $this->allocated_amount - $this->getSpentAmount();
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentage(): float
    {
        if ($this->allocated_amount <= 0) {
            return 0;
        }

        $percentage = ($this->getSpentAmount() / $this->allocated_amount) * 100;
        return min(100, round($percentage, 1));
    }

    /**
     * Check if category is over budget.
     */
    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->allocated_amount;
    }

    /**
     * Get transaction count.
     */
    public function getTransactionCount(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Recalculate spent_amount from transactions.
     */
    public function recalculateSpent(): void
    {
        $this->spent_amount = $this->getSpentAmount();
        $this->save();
    }

    /**
     * Get variance (for traditional budgeting).
     * Positive = under budget, Negative = over budget.
     */
    public function getVariance(): float
    {
        return $this->allocated_amount - $this->getSpentAmount();
    }

    /**
     * Get variance percentage.
     */
    public function getVariancePercentage(): float
    {
        if ($this->allocated_amount <= 0) {
            return 0;
        }

        return round(($this->getVariance() / $this->allocated_amount) * 100, 1);
    }
}
