<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FamilyResourceFile extends Model
{
    protected $fillable = [
        'family_resource_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'folder',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'original_name' => 'encrypted',
    ];

    /**
     * Get the family resource this file belongs to.
     */
    public function familyResource(): BelongsTo
    {
        return $this->belongsTo(FamilyResource::class);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Get icon based on file type.
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower($this->extension);

        return match ($extension) {
            'pdf' => 'icon-[tabler--file-type-pdf]',
            'doc', 'docx' => 'icon-[tabler--file-type-doc]',
            'xls', 'xlsx' => 'icon-[tabler--file-type-xls]',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif' => 'icon-[tabler--photo]',
            'zip', 'rar', '7z' => 'icon-[tabler--file-zip]',
            default => 'icon-[tabler--file]',
        };
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get file URL for download/viewing.
     */
    public function getUrl(): ?string
    {
        if (!Storage::disk('do_spaces')->exists($this->file_path)) {
            return null;
        }
        return route('family-resources.files.download', ['familyResource' => $this->family_resource_id, 'file' => $this->id]);
    }
}
