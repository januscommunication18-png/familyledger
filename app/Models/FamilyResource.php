<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyResource extends Model
{
    use BelongsToTenant;

    /**
     * Document type constants.
     */
    public const TYPE_EMERGENCY = 'emergency';
    public const TYPE_EVACUATION_PLAN = 'evacuation_plan';
    public const TYPE_FIRE_EXTINGUISHER = 'fire_extinguisher';
    public const TYPE_RENTAL_AGREEMENT = 'rental_agreement';
    public const TYPE_HOME_WARRANTY = 'home_warranty';
    public const TYPE_OTHER = 'other';

    public const DOCUMENT_TYPES = [
        self::TYPE_EMERGENCY => 'Emergency',
        self::TYPE_EVACUATION_PLAN => 'Evacuation Plan',
        self::TYPE_FIRE_EXTINGUISHER => 'Fire Extinguisher',
        self::TYPE_RENTAL_AGREEMENT => 'Rental Agreement / Lease',
        self::TYPE_HOME_WARRANTY => 'Home Warranty Documents',
        self::TYPE_OTHER => 'Other Family Resource',
    ];

    /**
     * Status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ARCHIVED => 'Archived',
        self::STATUS_EXPIRED => 'Expired',
    ];

    protected $fillable = [
        'tenant_id',
        'created_by',
        'document_type',
        'custom_document_type',
        'name',
        'digital_copy_date',
        'original_location',
        'notes',
        'status',
    ];

    protected $casts = [
        'digital_copy_date' => 'date',
        // Encrypted fields
        'name' => 'encrypted',
        'original_location' => 'encrypted',
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
     * Get the files for this document.
     */
    public function files(): HasMany
    {
        return $this->hasMany(FamilyResourceFile::class);
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
            self::STATUS_ARCHIVED => 'neutral',
            self::STATUS_EXPIRED => 'error',
            default => 'neutral',
        };
    }

    /**
     * Get document type icon.
     */
    public function getDocumentTypeIconAttribute(): string
    {
        return match ($this->document_type) {
            self::TYPE_EMERGENCY => 'icon-[tabler--alert-triangle]',
            self::TYPE_EVACUATION_PLAN => 'icon-[tabler--door-exit]',
            self::TYPE_FIRE_EXTINGUISHER => 'icon-[tabler--fire-extinguisher]',
            self::TYPE_RENTAL_AGREEMENT => 'icon-[tabler--home-dollar]',
            self::TYPE_HOME_WARRANTY => 'icon-[tabler--shield-check]',
            default => 'icon-[tabler--folder]',
        };
    }

    /**
     * Get document type color for icons.
     */
    public function getDocumentTypeColorAttribute(): string
    {
        return match ($this->document_type) {
            self::TYPE_EMERGENCY => 'red',
            self::TYPE_EVACUATION_PLAN => 'orange',
            self::TYPE_FIRE_EXTINGUISHER => 'rose',
            self::TYPE_RENTAL_AGREEMENT => 'blue',
            self::TYPE_HOME_WARRANTY => 'emerald',
            default => 'gray',
        };
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
