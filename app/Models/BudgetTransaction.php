<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetTransaction extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'budget_transactions';

    protected $fillable = [
        'tenant_id',
        'budget_id',
        'category_id',
        'created_by',
        'type',
        'amount',
        'description',
        'payee',
        'transaction_date',
        'source',
        'import_reference',
        'metadata',
        'is_shared',
        'shared_for_child_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'metadata' => 'array',
        'is_shared' => 'boolean',
    ];

    // Transaction types
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_INCOME = 'income';
    public const TYPE_TRANSFER = 'transfer';

    public const TYPES = [
        self::TYPE_EXPENSE => ['label' => 'Expense', 'icon' => '📤', 'color' => '#ef4444'],
        self::TYPE_INCOME => ['label' => 'Income', 'icon' => '📥', 'color' => '#22c55e'],
        self::TYPE_TRANSFER => ['label' => 'Transfer', 'icon' => '🔄', 'color' => '#3b82f6'],
    ];

    // Transaction sources
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_CSV_IMPORT = 'csv_import';
    public const SOURCE_BANK_SYNC = 'bank_sync';

    public const SOURCES = [
        self::SOURCE_MANUAL => 'Manual Entry',
        self::SOURCE_CSV_IMPORT => 'CSV Import',
        self::SOURCE_BANK_SYNC => 'Bank Sync',
    ];

    // ==================== RELATIONSHIPS ====================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sharedForChild(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'shared_for_child_id');
    }

    // ==================== SCOPES ====================

    public function scopeExpenses($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeUncategorized($query)
    {
        return $query->whereNull('category_id');
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit($limit);
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

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? 'Unknown';
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === self::TYPE_EXPENSE ? '-' : '+';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->transaction_date->format('M j, Y');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Uncategorized';
    }

    // ==================== METHODS ====================

    /**
     * Check if transaction is an expense.
     */
    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    /**
     * Check if transaction is income.
     */
    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    /**
     * Check if transaction is a transfer.
     */
    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    /**
     * Check if transaction is from CSV import.
     */
    public function isImported(): bool
    {
        return $this->source === self::SOURCE_CSV_IMPORT || $this->source === self::SOURCE_BANK_SYNC;
    }

    /**
     * Get signed amount (negative for expenses, positive for income).
     */
    public function getSignedAmount(): float
    {
        return $this->type === self::TYPE_EXPENSE ? -$this->amount : $this->amount;
    }

    /**
     * Generate import reference for deduplication.
     */
    public static function generateImportReference(array $data): string
    {
        return md5(implode('|', [
            $data['transaction_date'] ?? '',
            $data['amount'] ?? '',
            $data['description'] ?? '',
            $data['payee'] ?? '',
        ]));
    }

    /**
     * Check if a similar transaction exists (for import deduplication).
     */
    public static function isDuplicate(int $budgetId, string $importReference): bool
    {
        return self::where('budget_id', $budgetId)
            ->where('import_reference', $importReference)
            ->exists();
    }

    /**
     * Get the child this expense is shared for.
     */
    public function getSharedChildAttribute()
    {
        if (!$this->is_shared || !$this->shared_for_child_id) {
            return null;
        }

        return $this->sharedForChild;
    }

    /**
     * Get shared child name.
     */
    public function getSharedChildNameAttribute(): string
    {
        if (!$this->is_shared || !$this->shared_for_child_id) {
            return '';
        }

        return $this->sharedForChild?->full_name ?? '';
    }

    /**
     * Get co-parents this expense is shared with (via the child).
     */
    public function getSharedCoparentsAttribute()
    {
        if (!$this->is_shared || !$this->shared_for_child_id) {
            return collect();
        }

        return $this->sharedForChild?->coparents ?? collect();
    }
}
