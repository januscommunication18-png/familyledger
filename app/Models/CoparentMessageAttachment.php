<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CoparentMessageAttachment extends Model
{
    use BelongsToTenant;

    protected $table = 'coparent_message_attachments';

    protected $fillable = [
        'tenant_id',
        'message_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // Allowed file types
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
    ];

    public const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    // ==================== RELATIONSHIPS ====================

    public function message(): BelongsTo
    {
        return $this->belongsTo(CoparentMessage::class, 'message_id');
    }

    // ==================== ACCESSORS ====================

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ]);
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return '🖼️';
        } elseif ($this->is_pdf) {
            return '📄';
        } elseif ($this->is_document) {
            return '📝';
        }

        return '📎';
    }

    // ==================== METHODS ====================

    /**
     * Check if mime type is allowed.
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES);
    }

    /**
     * Check if file size is within limit.
     */
    public static function isValidFileSize(int $size): bool
    {
        return $size <= self::MAX_FILE_SIZE;
    }

    /**
     * Delete the physical file.
     */
    public function deleteFile(): bool
    {
        return Storage::delete($this->path);
    }
}
