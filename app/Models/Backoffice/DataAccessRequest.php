<?php

namespace App\Models\Backoffice;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DataAccessRequest extends Model
{
    protected $fillable = [
        'admin_id',
        'tenant_id',
        'token',
        'reason',
        'status',
        'expires_at',
        'approved_at',
        'denied_at',
        'access_expires_at',
        'approved_by_email',
        'denial_reason',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'denied_at' => 'datetime',
        'access_expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_EXPIRED = 'expired';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->token)) {
                $request->token = Str::random(64);
            }
            if (empty($request->expires_at)) {
                $request->expires_at = now()->addHours(24);
            }
        });
    }

    /**
     * Get the admin who requested access.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Check if request is approved and access is still valid.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if access is still valid (approved and not expired).
     */
    public function hasValidAccess(): bool
    {
        return $this->isApproved()
            && $this->access_expires_at
            && $this->access_expires_at->isFuture();
    }

    /**
     * Check if request has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Approve the request.
     */
    public function approve(string $approverEmail, int $accessHours = 2): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by_email' => $approverEmail,
            'access_expires_at' => now()->addHours($accessHours),
        ]);
    }

    /**
     * Deny the request.
     */
    public function deny(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_DENIED,
            'denied_at' => now(),
            'denial_reason' => $reason,
        ]);
    }

    /**
     * Mark as expired.
     */
    public function markExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Get approval URL for the client.
     */
    public function getApprovalUrl(): string
    {
        return url(route('data-access.show', ['token' => $this->token], false));
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for approved requests with valid access.
     */
    public function scopeWithValidAccess($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('access_expires_at', '>', now());
    }

    /**
     * Find active request for admin and tenant.
     */
    public static function findActiveForAdminAndTenant(int $adminId, string $tenantId): ?self
    {
        return static::where('admin_id', $adminId)
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Pending and not expired
                    $q->where('status', self::STATUS_PENDING)
                        ->where('expires_at', '>', now());
                })->orWhere(function ($q) {
                    // Approved with valid access
                    $q->where('status', self::STATUS_APPROVED)
                        ->where('access_expires_at', '>', now());
                });
            })
            ->latest()
            ->first();
    }
}
