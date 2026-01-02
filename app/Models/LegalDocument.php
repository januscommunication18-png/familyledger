<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDocument extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Document type constants.
     */
    public const TYPE_WILL = 'will';
    public const TYPE_TRUST = 'trust';
    public const TYPE_POWER_OF_ATTORNEY = 'power_of_attorney';
    public const TYPE_MEDICAL_DIRECTIVE = 'medical_directive';
    public const TYPE_OTHER = 'other';

    public const DOCUMENT_TYPES = [
        self::TYPE_WILL => 'Will',
        self::TYPE_TRUST => 'Trust',
        self::TYPE_POWER_OF_ATTORNEY => 'Power of Attorney',
        self::TYPE_MEDICAL_DIRECTIVE => 'Medical Directive',
        self::TYPE_OTHER => 'Other Legal Document',
    ];

    /**
     * Status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_SUPERSEDED => 'Superseded',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_REVOKED => 'Revoked',
    ];

    protected $fillable = [
        'tenant_id',
        'created_by',
        'document_type',
        'custom_document_type',
        'name',
        'digital_copy_date',
        'original_location',
        'attorney_person_id',
        'attorney_name',
        'attorney_phone',
        'attorney_email',
        'attorney_firm',
        'notes',
        'status',
        'execution_date',
        'expiration_date',
    ];

    protected $casts = [
        'digital_copy_date' => 'date',
        'execution_date' => 'date',
        'expiration_date' => 'date',
        // Encrypted fields
        'name' => 'encrypted',
        'original_location' => 'encrypted',
        'attorney_name' => 'encrypted',
        'attorney_phone' => 'encrypted',
        'attorney_email' => 'encrypted',
        'attorney_firm' => 'encrypted',
        'notes' => 'encrypted',
    ];

    /**
     * Get the user who created this document.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attorney (from People directory).
     */
    public function attorney(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'attorney_person_id');
    }

    /**
     * Get the files for this document.
     */
    public function files(): HasMany
    {
        return $this->hasMany(LegalDocumentFile::class);
    }

    /**
     * Get the document type display name.
     */
    public function getDocumentTypeNameAttribute(): string
    {
        if ($this->document_type === self::TYPE_OTHER && $this->custom_document_type) {
            return $this->custom_document_type;
        }
        return self::DOCUMENT_TYPES[$this->document_type] ?? 'Unknown';
    }

    /**
     * Get the status display name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_SUPERSEDED => 'warning',
            self::STATUS_EXPIRED => 'error',
            self::STATUS_REVOKED => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Get document type icon.
     */
    public function getDocumentTypeIconAttribute(): string
    {
        return match ($this->document_type) {
            self::TYPE_WILL => 'icon-[tabler--file-certificate]',
            self::TYPE_TRUST => 'icon-[tabler--building-bank]',
            self::TYPE_POWER_OF_ATTORNEY => 'icon-[tabler--gavel]',
            self::TYPE_MEDICAL_DIRECTIVE => 'icon-[tabler--stethoscope]',
            default => 'icon-[tabler--file-text]',
        };
    }

    /**
     * Get the attorney display name (from linked person or manual entry).
     */
    public function getAttorneyDisplayNameAttribute(): ?string
    {
        if ($this->attorney) {
            return $this->attorney->full_name;
        }
        return $this->attorney_name;
    }

    /**
     * Get files grouped by folder.
     */
    public function getFilesGroupedByFolderAttribute(): array
    {
        $files = $this->files;
        $grouped = [];

        foreach ($files as $file) {
            $folder = $file->folder ?? 'root';
            if (!isset($grouped[$folder])) {
                $grouped[$folder] = [];
            }
            $grouped[$folder][] = $file;
        }

        return $grouped;
    }

    /**
     * Check if document is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days).
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active documents only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
