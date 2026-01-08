<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SharedExpensePayment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'transaction_id',
        'requested_by',
        'requested_from',
        'child_id',
        'amount',
        'split_percentage',
        'status',
        'payment_method',
        'receipt_path',
        'receipt_original_filename',
        'note',
        'response_note',
        'paid_at',
        'responded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'split_percentage' => 'decimal:2',
        'paid_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => ['label' => 'Pending', 'color' => 'warning'],
        self::STATUS_PAID => ['label' => 'Paid', 'color' => 'success'],
        self::STATUS_DECLINED => ['label' => 'Declined', 'color' => 'error'],
        self::STATUS_CANCELLED => ['label' => 'Cancelled', 'color' => 'ghost'],
    ];

    // Payment methods
    public const PAYMENT_METHODS = [
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'venmo' => 'Venmo',
        'paypal' => 'PayPal',
        'zelle' => 'Zelle',
        'check' => 'Check',
        'other' => 'Other',
    ];

    // ==================== RELATIONSHIPS ====================

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(BudgetTransaction::class, 'transaction_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_from');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'child_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('requested_by', $userId)
              ->orWhere('requested_from', $userId);
        });
    }

    public function scopeRequestedFrom($query, $userId)
    {
        return $query->where('requested_from', $userId);
    }

    public function scopeRequestedBy($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    // ==================== ACCESSORS ====================

    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES[self::STATUS_PENDING];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_info['label'];
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status_info['color'];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method ?? 'Not specified';
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    // ==================== METHODS ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function hasReceipt(): bool
    {
        return !empty($this->receipt_path);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }

        return \Storage::url($this->receipt_path);
    }

    public function markAsPaid(string $paymentMethod, ?string $receiptPath = null, ?string $receiptFilename = null, ?string $note = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_method' => $paymentMethod,
            'receipt_path' => $receiptPath,
            'receipt_original_filename' => $receiptFilename,
            'response_note' => $note,
            'paid_at' => now(),
            'responded_at' => now(),
        ]);
    }

    public function markAsDeclined(?string $note = null): void
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'response_note' => $note,
            'responded_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
}
