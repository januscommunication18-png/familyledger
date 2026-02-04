<?php

namespace App\Models\Backoffice;

use App\Notifications\Backoffice\ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Authenticatable implements CanResetPassword
{
    use Notifiable;

    protected $table = 'backoffice_admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'security_code',
        'security_code_expires_at',
        'access_code',
        'access_code_expires_at',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'security_code',
        'access_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'security_code_expires_at' => 'datetime',
        'access_code_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the view codes for this admin.
     */
    public function viewCodes(): HasMany
    {
        return $this->hasMany(ViewCode::class, 'admin_id');
    }

    /**
     * Get the activity logs for this admin.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'admin_id');
    }

    /**
     * Generate a new security code for login.
     */
    public function generateSecurityCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'security_code' => bcrypt($code),
            'security_code_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    /**
     * Verify security code.
     */
    public function verifySecurityCode(string $code): bool
    {
        if (!$this->security_code || !$this->security_code_expires_at) {
            return false;
        }

        if ($this->security_code_expires_at->isPast()) {
            return false;
        }

        return password_verify($code, $this->security_code);
    }

    /**
     * Clear security code after successful verification.
     */
    public function clearSecurityCode(): void
    {
        $this->update([
            'security_code' => null,
            'security_code_expires_at' => null,
        ]);
    }

    /**
     * Generate a new access code for pre-authentication.
     */
    public function generateAccessCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'access_code' => bcrypt($code),
            'access_code_expires_at' => now()->addMinutes(5),
        ]);

        return $code;
    }

    /**
     * Verify access code.
     */
    public function verifyAccessCode(string $code): bool
    {
        if (!$this->access_code || !$this->access_code_expires_at) {
            return false;
        }

        if ($this->access_code_expires_at->isPast()) {
            return false;
        }

        return password_verify($code, $this->access_code);
    }

    /**
     * Clear access code after successful verification.
     */
    public function clearAccessCode(): void
    {
        $this->update([
            'access_code' => null,
            'access_code_expires_at' => null,
        ]);
    }

    /**
     * Log activity.
     */
    public function logActivity(string $action, ?string $tenantId = null, ?string $details = null): void
    {
        $this->activityLogs()->create([
            'action' => $action,
            'tenant_id' => $tenantId,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the email address for password reset.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->email;
    }
}
