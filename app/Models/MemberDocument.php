<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class MemberDocument extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Document type constants.
     */
    public const TYPE_DRIVERS_LICENSE = 'drivers_license';
    public const TYPE_PASSPORT = 'passport';
    public const TYPE_SOCIAL_SECURITY = 'social_security';
    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';
    public const TYPE_OTHER = 'other';

    public const DOCUMENT_TYPES = [
        self::TYPE_DRIVERS_LICENSE => "Driver's License",
        self::TYPE_PASSPORT => 'Passport',
        self::TYPE_SOCIAL_SECURITY => 'Social Security Card',
        self::TYPE_BIRTH_CERTIFICATE => 'Birth Certificate',
        self::TYPE_OTHER => 'Other Document',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'uploaded_by',
        'document_type',
        'document_number',
        'state_of_issue',
        'country_of_issue',
        'issue_date',
        'expiry_date',
        'details',
        'front_image',
        'back_image',
        'encrypted_number',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'encrypted_number',
    ];

    /**
     * Get the family member this document belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the document type display name.
     */
    public function getDocumentTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? 'Unknown';
    }

    /**
     * Check if document is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if document expires within given days.
     */
    public function expiresWithin(int $days): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->between(now(), now()->addDays($days));
    }

    /**
     * Set the encrypted SSN.
     */
    public function setEncryptedNumberAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['encrypted_number'] = Crypt::encryptString($value);
        } else {
            $this->attributes['encrypted_number'] = null;
        }
    }

    /**
     * Get the decrypted SSN (only last 4 digits for display).
     */
    public function getDecryptedNumberAttribute(): ?string
    {
        if (!$this->attributes['encrypted_number']) {
            return null;
        }
        return Crypt::decryptString($this->attributes['encrypted_number']);
    }

    /**
     * Get masked SSN for display (XXX-XX-1234).
     */
    public function getMaskedNumberAttribute(): ?string
    {
        $number = $this->decrypted_number;
        if (!$number) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (strlen($clean) >= 4) {
            return 'XXX-XX-' . substr($clean, -4);
        }
        return 'XXX-XX-XXXX';
    }
}
