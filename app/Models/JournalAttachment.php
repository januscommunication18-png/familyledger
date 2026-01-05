<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JournalAttachment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'journal_entry_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'thumbnail_path',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    // ==================== CONSTANTS ====================

    public const TYPES = [
        'photo' => ['label' => 'Photo', 'icon' => 'tabler--photo'],
        'file' => ['label' => 'File', 'icon' => 'tabler--file'],
    ];

    public const MAX_PHOTOS = 5;
    public const MAX_FILES = 1;
    public const MAX_PHOTO_SIZE = 10 * 1024 * 1024; // 10MB
    public const MAX_FILE_SIZE = 25 * 1024 * 1024; // 25MB

    public const ALLOWED_PHOTO_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public const ALLOWED_FILE_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
    ];

    // ==================== RELATIONSHIPS ====================

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    // ==================== ACCESSORS ====================

    public function getUrlAttribute(): string
    {
        return Storage::disk('do_spaces')->url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('do_spaces')->url($this->thumbnail_path);
        }
        return $this->isPhoto() ? $this->url : null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getTypeInfoAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['file'];
    }

    // ==================== SCOPES ====================

    public function scopePhotos($query)
    {
        return $query->where('type', 'photo');
    }

    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    // ==================== METHODS ====================

    public function isPhoto(): bool
    {
        return $this->type === 'photo';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function delete(): bool
    {
        // Delete files from storage
        if ($this->file_path && Storage::disk('do_spaces')->exists($this->file_path)) {
            Storage::disk('do_spaces')->delete($this->file_path);
        }

        if ($this->thumbnail_path && Storage::disk('do_spaces')->exists($this->thumbnail_path)) {
            Storage::disk('do_spaces')->delete($this->thumbnail_path);
        }

        return parent::delete();
    }
}
