<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * User role constants.
     * Any user who signs up with Family Ledger is tagged as Parent by default.
     */
    public const ROLE_PARENT = 'parent';
    public const ROLE_COPARENT = 'coparent';
    public const ROLE_GUARDIAN = 'guardian';
    public const ROLE_ADVISOR = 'advisor'; // Lawyer, CPA
    public const ROLE_VIEWER = 'viewer';   // Read-only

    /**
     * Available roles for display.
     */
    public const ROLES = [
        self::ROLE_PARENT => 'Parent',
        self::ROLE_COPARENT => 'Co-Parent',
        self::ROLE_GUARDIAN => 'Guardian',
        self::ROLE_ADVISOR => 'Advisor',
        self::ROLE_VIEWER => 'Viewer',
    ];

    /**
     * Auth provider constants.
     */
    public const PROVIDER_EMAIL = 'email';
    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_APPLE = 'apple';
    public const PROVIDER_FACEBOOK = 'facebook';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'email_hash',
        'backup_email',
        'country_code',
        'phone',
        'password',
        'role',
        'avatar',
        'auth_provider',
        'is_active',
        'mfa_enabled',
        'mfa_method',
        'phone_2fa_enabled',
        'recovery_codes',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'phone_2fa_enabled' => 'boolean',
            'recovery_codes' => 'encrypted:array',
            'password' => 'hashed',
            // AES-256 encrypted PII fields
            'name' => 'encrypted',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'email' => 'encrypted',
            'backup_email' => 'encrypted',
            'phone' => 'encrypted',
            'country_code' => 'encrypted',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate email_hash when email is set
        static::saving(function (User $user) {
            if ($user->isDirty('email') && $user->email) {
                // Get the raw (decrypted) email value for hashing
                $email = $user->email;
                $user->email_hash = hash('sha256', strtolower(trim($email)));
            }
        });
    }

    /**
     * Find a user by email using the hash.
     */
    public static function findByEmail(string $email): ?self
    {
        $hash = hash('sha256', strtolower(trim($email)));
        return static::where('email_hash', $hash)->first();
    }

    /**
     * Get the social accounts linked to this user.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Check if user has two-factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->mfa_enabled && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Check if user has SMS MFA enabled.
     */
    public function hasSmsMfaEnabled(): bool
    {
        return $this->mfa_enabled && $this->mfa_method === 'sms' && $this->phone_verified_at;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a parent (primary account holder).
     */
    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    /**
     * Check if user is a co-parent.
     */
    public function isCoparent(): bool
    {
        return $this->role === self::ROLE_COPARENT;
    }

    /**
     * Check if user is a guardian.
     */
    public function isGuardian(): bool
    {
        return $this->role === self::ROLE_GUARDIAN;
    }

    /**
     * Check if user is an advisor (lawyer, CPA).
     */
    public function isAdvisor(): bool
    {
        return $this->role === self::ROLE_ADVISOR;
    }

    /**
     * Check if user is a viewer (read-only).
     */
    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    /**
     * Check if user can manage the family circle.
     */
    public function canManageFamilyCircle(): bool
    {
        return in_array($this->role, [self::ROLE_PARENT, self::ROLE_COPARENT]);
    }

    /**
     * Check if user can edit items.
     */
    public function canEdit(): bool
    {
        return !$this->isViewer();
    }

    /**
     * Check if user signed up via social provider.
     */
    public function usedSocialAuth(): bool
    {
        return in_array($this->auth_provider, [
            self::PROVIDER_GOOGLE,
            self::PROVIDER_APPLE,
            self::PROVIDER_FACEBOOK,
        ]);
    }

    /**
     * Record login information.
     */
    public function recordLogin(): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = request()->ip();
        $this->saveQuietly();
    }

    /**
     * Get the role display name.
     */
    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? 'Unknown';
    }
}
