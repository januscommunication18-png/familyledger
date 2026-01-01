<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonEmail extends Model
{
    public const LABEL_PERSONAL = 'personal';
    public const LABEL_WORK = 'work';
    public const LABEL_OTHER = 'other';

    public const LABELS = [
        self::LABEL_PERSONAL => 'Personal',
        self::LABEL_WORK => 'Work',
        self::LABEL_OTHER => 'Other',
    ];

    protected $fillable = [
        'person_id',
        'email',
        'label',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        // AES-256 encrypted PII fields
        'email' => 'encrypted',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function getLabelNameAttribute(): string
    {
        return self::LABELS[$this->label] ?? 'Other';
    }
}
