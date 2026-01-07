<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetGoal extends Model
{
    protected $fillable = [
        'budget_id',
        'name',
        'description',
        'type',
        'target_amount',
        'current_amount',
        'icon',
        'color',
        'target_date',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Goal types
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_INCOME = 'income';
    public const TYPE_SAVING = 'saving';

    public const TYPES = [
        self::TYPE_EXPENSE => [
            'label' => 'Spending Limit',
            'description' => 'Track spending to stay under a limit',
            'icon' => '📉',
            'color' => '#ef4444',
        ],
        self::TYPE_INCOME => [
            'label' => 'Income Target',
            'description' => 'Track income to reach a target',
            'icon' => '📈',
            'color' => '#22c55e',
        ],
        self::TYPE_SAVING => [
            'label' => 'Savings Goal',
            'description' => 'Save towards a specific amount',
            'icon' => '🎯',
            'color' => '#3b82f6',
        ],
    ];

    // Default icons for goals
    public const ICONS = [
        '🎯', '💰', '🏠', '🚗', '✈️', '🎓', '💍', '🏥',
        '📱', '💻', '🛒', '🎁', '🏦', '📈', '📉', '💵',
    ];

    // ==================== RELATIONSHIPS ====================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExpenseGoals($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeIncomeGoals($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeSavingGoals($query)
    {
        return $query->where('type', self::TYPE_SAVING);
    }

    // ==================== ACCESSORS ====================

    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES[self::TYPE_EXPENSE];
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type_info['label'];
    }

    public function getTypeIconAttribute(): string
    {
        return $this->type_info['icon'];
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type_info['color'];
    }

    public function getDisplayIconAttribute(): string
    {
        return $this->icon ?? $this->type_info['icon'];
    }

    public function getDisplayColorAttribute(): string
    {
        return $this->color ?? $this->type_info['color'];
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return round(min(100, max(0, $percentage)), 1);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    public function getFormattedTargetAttribute(): string
    {
        return '$' . number_format($this->target_amount, 2);
    }

    public function getFormattedCurrentAttribute(): string
    {
        return '$' . number_format($this->current_amount, 2);
    }

    public function getFormattedRemainingAttribute(): string
    {
        return '$' . number_format($this->remaining_amount, 2);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->target_date) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->target_date, false));
    }

    // ==================== METHODS ====================

    /**
     * Check if goal is achieved.
     */
    public function isAchieved(): bool
    {
        if ($this->type === self::TYPE_EXPENSE) {
            // For expense goals, staying under target is success
            return $this->current_amount <= $this->target_amount;
        }

        // For income and saving goals, reaching target is success
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Check if goal is over budget (for expense type).
     */
    public function isOverBudget(): bool
    {
        return $this->type === self::TYPE_EXPENSE && $this->current_amount > $this->target_amount;
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        if ($this->type === self::TYPE_EXPENSE) {
            if ($this->current_amount > $this->target_amount) {
                return 'Over Budget';
            } elseif ($this->progress_percentage >= 90) {
                return 'Near Limit';
            }
            return 'On Track';
        }

        if ($this->current_amount >= $this->target_amount) {
            return 'Achieved';
        } elseif ($this->progress_percentage >= 75) {
            return 'Almost There';
        }
        return 'In Progress';
    }

    /**
     * Get status color class.
     */
    public function getStatusColorClass(): string
    {
        if ($this->type === self::TYPE_EXPENSE) {
            if ($this->current_amount > $this->target_amount) {
                return 'text-red-600';
            } elseif ($this->progress_percentage >= 90) {
                return 'text-amber-600';
            }
            return 'text-emerald-600';
        }

        if ($this->current_amount >= $this->target_amount) {
            return 'text-emerald-600';
        }
        return 'text-blue-600';
    }

    /**
     * Get progress bar color class.
     */
    public function getProgressColorClass(): string
    {
        if ($this->type === self::TYPE_EXPENSE) {
            if ($this->progress_percentage > 100) {
                return 'bg-red-500';
            } elseif ($this->progress_percentage >= 90) {
                return 'bg-amber-500';
            }
            return 'bg-emerald-500';
        }

        // For income and saving goals
        if ($this->progress_percentage >= 100) {
            return 'bg-emerald-500';
        }
        return 'bg-blue-500';
    }

    /**
     * Calculate current amount from transactions (for period-based tracking).
     */
    public function calculateCurrentFromTransactions(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = $this->budget->transactions();

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        if ($this->type === self::TYPE_EXPENSE) {
            return (float) $query->where('type', 'expense')->sum('amount');
        } elseif ($this->type === self::TYPE_INCOME) {
            return (float) $query->where('type', 'income')->sum('amount');
        } else {
            // Saving = income - expense
            $income = (float) $query->where('type', 'income')->sum('amount');
            $expense = (float) $query->where('type', 'expense')->sum('amount');
            return max(0, $income - $expense);
        }
    }

    /**
     * Update current amount from transactions.
     */
    public function updateCurrentAmount(?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        $this->current_amount = $this->calculateCurrentFromTransactions($startDate, $endDate);
        $this->save();
    }
}
