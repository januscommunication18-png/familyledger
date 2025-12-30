<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One-Time Password model for email OTP and SMS verification.
 */
class Otp extends Model
{
    protected $fillable = [
        'identifier',
        'type',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * OTP type constants.
     */
    public const TYPE_EMAIL_LOGIN = 'email_login';
    public const TYPE_EMAIL_VERIFY = 'email_verify';
    public const TYPE_SMS_MFA = 'sms_mfa';
    public const TYPE_PHONE_VERIFY = 'phone_verify';
    public const TYPE_PASSWORD_RESET = 'password_reset';

    /**
     * Maximum verification attempts.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * OTP expiry in minutes.
     */
    public const EXPIRY_MINUTES = 10;

    /**
     * Generate a new OTP.
     */
    public static function generate(string $identifier, string $type, int $length = 6): self
    {
        // Invalidate any existing OTPs for this identifier and type
        static::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        // Generate numeric OTP code
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }

        return static::create([
            'identifier' => $identifier,
            'type' => $type,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);
    }

    /**
     * Verify an OTP code.
     */
    public static function verify(string $identifier, string $type, string $code): bool
    {
        $otp = static::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return false;
        }

        // Check max attempts
        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->delete();
            return false;
        }

        // Increment attempts
        $otp->increment('attempts');

        // Verify code
        if (!hash_equals($otp->code, $code)) {
            return false;
        }

        // Mark as verified
        $otp->update(['verified_at' => now()]);

        return true;
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if max attempts reached.
     */
    public function hasMaxAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining attempts.
     */
    public function remainingAttempts(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }
}
