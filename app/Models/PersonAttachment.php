<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PersonAttachment extends Model
{
    use BelongsToTenant;

    public const TYPE_BUSINESS_CARD = 'business_card';
    public const TYPE_VCARD = 'vcard';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_BUSINESS_CARD => 'Business Card',
        self::TYPE_VCARD => 'vCard',
        self::TYPE_DOCUMENT => 'Document',
        self::TYPE_OTHER => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'person_id',
        'file_path',
        'original_filename',
        'file_type',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileTypeNameAttribute(): string
    {
        return self::TYPES[$this->file_type] ?? 'Other';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
