<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Collaborator;

class Budget extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'type',
        'total_amount',
        'period',
        'start_date',
        'end_date',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Budget types
    public const TYPE_ENVELOPE = 'envelope';
    public const TYPE_TRADITIONAL = 'traditional';

    public const TYPES = [
        self::TYPE_ENVELOPE => [
            'label' => 'Envelope Budgeting',
            'description' => 'Allocate fixed amounts to categories. Money is "stored" in virtual envelopes.',
        ],
        self::TYPE_TRADITIONAL => [
            'label' => 'Traditional Budgeting',
            'description' => 'Track spending against budget goals. See variances and adjust as needed.',
        ],
    ];

    // Budget periods
    public const PERIODS = [
        'weekly' => 'Weekly',
        'biweekly' => 'Bi-weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class)->orderBy('sort_order');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(BudgetShare::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(BudgetGoal::class)->orderBy('sort_order');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            });
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get budgets accessible by the current user.
     * Includes budgets created by the user OR shared with the user (even across tenants).
     */
    public function scopeAccessibleByUser($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        // Get ALL collaborator IDs for the current user across ALL tenants
        // This is necessary because a user can be a collaborator in multiple tenants
        $collaboratorIds = Collaborator::where('user_id', $userId)
            ->pluck('id')
            ->toArray();

        return $query->where(function ($q) use ($userId, $collaboratorIds) {
            // Budgets created by the user
            $q->where('created_by', $userId);

            // OR budgets shared with the user (via any of their collaborator records)
            if (!empty($collaboratorIds)) {
                $q->orWhereHas('shares', function ($shareQuery) use ($collaboratorIds) {
                    $shareQuery->whereIn('collaborator_id', $collaboratorIds);
                });
            }
        });
    }

    // ==================== ACCESSORS ====================

    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES[self::TYPE_ENVELOPE];
    }

    public function getPeriodLabelAttribute(): string
    {
        return self::PERIODS[$this->period] ?? 'Monthly';
    }

    public function getIsEnvelopeAttribute(): bool
    {
        return $this->type === self::TYPE_ENVELOPE;
    }

    public function getIsTraditionalAttribute(): bool
    {
        return $this->type === self::TYPE_TRADITIONAL;
    }

    // ==================== METHODS ====================

    /**
     * Get total spent amount for this budget.
     */
    public function getTotalSpent(): float
    {
        return (float) $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');
    }

    /**
     * Get total income for this budget.
     */
    public function getTotalIncome(): float
    {
        return (float) $this->transactions()
            ->where('type', 'income')
            ->sum('amount');
    }

    /**
     * Get remaining amount.
     */
    public function getRemainingAmount(): float
    {
        return $this->total_amount - $this->getTotalSpent();
    }

    /**
     * Get progress percentage (spent / total).
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        $percentage = ($this->getTotalSpent() / $this->total_amount) * 100;
        return min(100, round($percentage, 1));
    }

    /**
     * Get total allocated to categories.
     */
    public function getTotalAllocated(): float
    {
        return (float) $this->categories()->sum('allocated_amount');
    }

    /**
     * Get unallocated amount (for envelope budgeting).
     */
    public function getUnallocatedAmount(): float
    {
        return $this->total_amount - $this->getTotalAllocated();
    }

    /**
     * Check if budget is over spent.
     */
    public function isOverBudget(): bool
    {
        return $this->getTotalSpent() > $this->total_amount;
    }

    /**
     * Get spending by category.
     */
    public function getSpendingByCategory(): array
    {
        $spending = [];

        foreach ($this->categories as $category) {
            $spent = $this->transactions()
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->sum('amount');

            $spending[$category->id] = [
                'category' => $category,
                'spent' => (float) $spent,
                'allocated' => (float) $category->allocated_amount,
                'remaining' => (float) ($category->allocated_amount - $spent),
                'percentage' => $category->allocated_amount > 0
                    ? round(($spent / $category->allocated_amount) * 100, 1)
                    : 0,
            ];
        }

        return $spending;
    }

    /**
     * Get uncategorized spending.
     */
    public function getUncategorizedSpending(): float
    {
        return (float) $this->transactions()
            ->whereNull('category_id')
            ->where('type', 'expense')
            ->sum('amount');
    }

    // ==================== PERIOD METHODS ====================

    /**
     * Get the period length in days.
     */
    public function getPeriodLengthInDays(): int
    {
        return match ($this->period) {
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30, // Approximate for calculations
            'yearly' => 365,
            default => 30,
        };
    }

    /**
     * Calculate period dates for a given offset.
     * Offset 0 = current period, -1 = previous period, etc.
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getPeriodDates(int $offset = 0): array
    {
        $startDate = Carbon::parse($this->start_date);
        $now = Carbon::now();

        switch ($this->period) {
            case 'weekly':
                // Find which week we're in since start
                $weeksSinceStart = $startDate->diffInWeeks($now);
                $targetWeek = $weeksSinceStart + $offset;

                $periodStart = $startDate->copy()->addWeeks(max(0, $targetWeek));
                $periodEnd = $periodStart->copy()->addWeek()->subDay();

                $label = $periodStart->format('M j') . ' - ' . $periodEnd->format('M j, Y');
                break;

            case 'biweekly':
                // Find which 2-week period we're in since start
                $biweeksSinceStart = floor($startDate->diffInDays($now) / 14);
                $targetBiweek = $biweeksSinceStart + $offset;

                $periodStart = $startDate->copy()->addDays(max(0, $targetBiweek) * 14);
                $periodEnd = $periodStart->copy()->addDays(13);

                $label = $periodStart->format('M j') . ' - ' . $periodEnd->format('M j, Y');
                break;

            case 'monthly':
                // Find which month period we're in since start
                $monthsSinceStart = $startDate->diffInMonths($now);
                $targetMonth = $monthsSinceStart + $offset;

                // Use the day of month from start_date
                $dayOfMonth = $startDate->day;
                $periodStart = $startDate->copy()->addMonths(max(0, $targetMonth));

                // End is one month later minus one day
                $periodEnd = $periodStart->copy()->addMonth()->subDay();

                $label = $periodStart->format('M j') . ' - ' . $periodEnd->format('M j, Y');
                break;

            case 'yearly':
                // Find which year period we're in since start
                $yearsSinceStart = $startDate->diffInYears($now);
                $targetYear = $yearsSinceStart + $offset;

                $periodStart = $startDate->copy()->addYears(max(0, $targetYear));
                $periodEnd = $periodStart->copy()->addYear()->subDay();

                $label = $periodStart->format('M j, Y') . ' - ' . $periodEnd->format('M j, Y');
                break;

            default:
                $periodStart = $startDate->copy();
                $periodEnd = $now->copy();
                $label = 'All Time';
        }

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'label' => $label,
        ];
    }

    /**
     * Get current period dates.
     */
    public function getCurrentPeriodDates(): array
    {
        return $this->getPeriodDates(0);
    }

    /**
     * Get available historical periods (periods that have transactions).
     *
     * @return array
     */
    public function getAvailablePeriods(int $limit = 12): array
    {
        $periods = [];
        $startDate = Carbon::parse($this->start_date);
        $now = Carbon::now();

        // Don't go beyond the start date
        $maxOffset = 0;

        // Calculate how many periods back we can go
        switch ($this->period) {
            case 'weekly':
                $maxOffset = min($startDate->diffInWeeks($now), $limit);
                break;
            case 'biweekly':
                $maxOffset = min(floor($startDate->diffInDays($now) / 14), $limit);
                break;
            case 'monthly':
                $maxOffset = min($startDate->diffInMonths($now), $limit);
                break;
            case 'yearly':
                $maxOffset = min($startDate->diffInYears($now), $limit);
                break;
        }

        // Generate periods from current going back
        for ($i = 0; $i >= -$maxOffset; $i--) {
            $periodDates = $this->getPeriodDates($i);

            // Check if this period has any transactions
            $hasTransactions = $this->transactions()
                ->whereBetween('transaction_date', [$periodDates['start'], $periodDates['end']])
                ->exists();

            $periods[] = [
                'offset' => $i,
                'start' => $periodDates['start'],
                'end' => $periodDates['end'],
                'label' => $periodDates['label'],
                'is_current' => $i === 0,
                'has_transactions' => $hasTransactions,
            ];
        }

        return $periods;
    }

    // ==================== PERIOD-FILTERED STATS ====================

    /**
     * Get total spent for a specific period.
     */
    public function getTotalSpentForPeriod(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        if (!$startDate || !$endDate) {
            $period = $this->getCurrentPeriodDates();
            $startDate = $period['start'];
            $endDate = $period['end'];
        }

        return (float) $this->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get total income for a specific period.
     */
    public function getTotalIncomeForPeriod(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        if (!$startDate || !$endDate) {
            $period = $this->getCurrentPeriodDates();
            $startDate = $period['start'];
            $endDate = $period['end'];
        }

        return (float) $this->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get remaining amount for a specific period.
     */
    public function getRemainingAmountForPeriod(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        return $this->total_amount - $this->getTotalSpentForPeriod($startDate, $endDate);
    }

    /**
     * Get progress percentage for a specific period.
     */
    public function getProgressPercentageForPeriod(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        $spent = $this->getTotalSpentForPeriod($startDate, $endDate);
        $percentage = ($spent / $this->total_amount) * 100;
        return min(100, round($percentage, 1));
    }

    /**
     * Get spending by category for a specific period.
     */
    public function getSpendingByCategoryForPeriod(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if (!$startDate || !$endDate) {
            $period = $this->getCurrentPeriodDates();
            $startDate = $period['start'];
            $endDate = $period['end'];
        }

        $spending = [];

        foreach ($this->categories as $category) {
            $spent = $this->transactions()
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $spending[$category->id] = [
                'category' => $category,
                'spent' => (float) $spent,
                'allocated' => (float) $category->allocated_amount,
                'remaining' => (float) ($category->allocated_amount - $spent),
                'percentage' => $category->allocated_amount > 0
                    ? round(($spent / $category->allocated_amount) * 100, 1)
                    : 0,
            ];
        }

        return $spending;
    }

    // ==================== SHARING HELPERS ====================

    /**
     * Check if this budget is owned by the given user.
     */
    public function isOwnedBy(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        return $this->created_by === $userId;
    }

    /**
     * Check if this budget is shared with the given user.
     */
    public function isSharedWith(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        // If user owns the budget, it's not "shared with" them
        if ($this->isOwnedBy($userId)) {
            return false;
        }

        // Check if user has a collaborator record that has access to this budget
        $collaboratorIds = Collaborator::where('user_id', $userId)->pluck('id')->toArray();

        if (empty($collaboratorIds)) {
            return false;
        }

        return $this->shares()->whereIn('collaborator_id', $collaboratorIds)->exists();
    }

    /**
     * Get the share record for a given user (if shared with them).
     */
    public function getShareForUser(?int $userId = null): ?BudgetShare
    {
        $userId = $userId ?? Auth::id();

        $collaboratorIds = Collaborator::where('user_id', $userId)->pluck('id')->toArray();

        if (empty($collaboratorIds)) {
            return null;
        }

        return $this->shares()->whereIn('collaborator_id', $collaboratorIds)->first();
    }

    /**
     * Get the permission level for a given user.
     * Returns 'owner' for the owner, or the share permission for shared users.
     */
    public function getUserPermission(?int $userId = null): string
    {
        $userId = $userId ?? Auth::id();

        if ($this->isOwnedBy($userId)) {
            return 'owner';
        }

        $share = $this->getShareForUser($userId);

        return $share ? $share->permission : 'none';
    }

    /**
     * Check if user can view this budget.
     */
    public function canUserView(?int $userId = null): bool
    {
        $permission = $this->getUserPermission($userId);
        return in_array($permission, ['owner', 'view', 'edit', 'admin']);
    }

    /**
     * Check if user can edit transactions in this budget.
     */
    public function canUserEdit(?int $userId = null): bool
    {
        $permission = $this->getUserPermission($userId);
        return in_array($permission, ['owner', 'edit', 'admin']);
    }

    /**
     * Check if user has admin access to this budget.
     */
    public function canUserAdmin(?int $userId = null): bool
    {
        $permission = $this->getUserPermission($userId);
        return in_array($permission, ['owner', 'admin']);
    }

    /**
     * Get the owner user of this budget.
     */
    public function getOwner(): ?User
    {
        return $this->creator;
    }

    /**
     * Get the owner's name.
     */
    public function getOwnerName(): string
    {
        return $this->creator?->name ?? 'Unknown';
    }
}
