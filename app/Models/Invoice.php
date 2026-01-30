<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'package_plan_id',
        'invoice_number',
        'paddle_transaction_id',
        'paddle_subscription_id',
        'billing_cycle',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'discount_code',
        'discount_percentage',
        'status',
        'paid_at',
        'due_date',
        'period_start',
        'period_end',
        'customer_name',
        'customer_email',
        'billing_address',
        'emailed_at',
        'email_count',
        'paddle_data',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'emailed_at' => 'datetime',
        'paddle_data' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Get the tenant that owns the invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user associated with the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package plan for this invoice.
     */
    public function packagePlan(): BelongsTo
    {
        return $this->belongsTo(PackagePlan::class);
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice has been emailed.
     */
    public function hasBeenEmailed(): bool
    {
        return $this->emailed_at !== null;
    }

    /**
     * Mark invoice as emailed.
     */
    public function markAsEmailed(): void
    {
        $this->update([
            'emailed_at' => now(),
            'email_count' => $this->email_count + 1,
        ]);
    }

    /**
     * Get formatted total amount with currency.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted subtotal amount.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted discount amount.
     */
    public function getFormattedDiscountAttribute(): string
    {
        return '-$' . number_format($this->discount_amount, 2);
    }

    /**
     * Get formatted tax amount.
     */
    public function getFormattedTaxAttribute(): string
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
