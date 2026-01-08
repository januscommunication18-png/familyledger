<?php

namespace App\Models\Backoffice;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewCode extends Model
{
    protected $table = 'backoffice_view_codes';

    protected $fillable = [
        'admin_id',
        'tenant_id',
        'code',
        'expires_at',
        'is_used',
        'used_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the admin that owns this code.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the tenant this code is for.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Generate a new view code for a tenant.
     */
    public static function generateForTenant(int $adminId, string $tenantId): self
    {
        // Invalidate any existing codes for this admin/tenant combination
        self::where('admin_id', $adminId)
            ->where('tenant_id', $tenantId)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'admin_id' => $adminId,
            'tenant_id' => $tenantId,
            'code' => bcrypt($code),
            'expires_at' => now()->addMinutes(5),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Verify a code.
     */
    public function verify(string $code): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at->isPast()) {
            return false;
        }

        return password_verify($code, $this->code);
    }

    /**
     * Mark code as used.
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }
}
