<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AssetDocument extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'document_type',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'tags',
        'is_encrypted',
        'uploaded_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_encrypted' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the asset this document belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
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
        return Asset::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type ?? 'Unknown';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if the document is an image.
     */
    public function isImage(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * Check if the document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get the document icon based on type.
     */
    public function getDocumentIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'icon-[tabler--photo]';
        }

        if ($this->isPdf()) {
            return 'icon-[tabler--file-type-pdf]';
        }

        return match ($this->document_type) {
            'deed' => 'icon-[tabler--file-certificate]',
            'title' => 'icon-[tabler--file-certificate]',
            'registration' => 'icon-[tabler--id]',
            'appraisal' => 'icon-[tabler--file-analytics]',
            'insurance' => 'icon-[tabler--shield-check]',
            'receipt' => 'icon-[tabler--receipt]',
            'photo' => 'icon-[tabler--photo]',
            'service_record' => 'icon-[tabler--tool]',
            default => 'icon-[tabler--file]',
        };
    }

    /**
     * Get tags as array.
     */
    public function getTagsArrayAttribute(): array
    {
        if (!$this->tags) {
            return [];
        }

        // Handle both string and array format
        if (is_string($this->tags)) {
            return json_decode($this->tags, true) ?? [];
        }

        return $this->tags;
    }

    /**
     * Check if file exists in storage.
     */
    public function fileExists(): bool
    {
        return Storage::disk('private')->exists($this->file_path);
    }
}
