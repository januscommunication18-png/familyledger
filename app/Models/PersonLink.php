<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonLink extends Model
{
    public const LABEL_WEBSITE = 'website';
    public const LABEL_LINKEDIN = 'linkedin';
    public const LABEL_TWITTER = 'twitter';
    public const LABEL_FACEBOOK = 'facebook';
    public const LABEL_INSTAGRAM = 'instagram';
    public const LABEL_OTHER = 'other';

    public const LABELS = [
        self::LABEL_WEBSITE => 'Website',
        self::LABEL_LINKEDIN => 'LinkedIn',
        self::LABEL_TWITTER => 'Twitter/X',
        self::LABEL_FACEBOOK => 'Facebook',
        self::LABEL_INSTAGRAM => 'Instagram',
        self::LABEL_OTHER => 'Other',
    ];

    public const ICONS = [
        self::LABEL_WEBSITE => 'icon-[tabler--world]',
        self::LABEL_LINKEDIN => 'icon-[tabler--brand-linkedin]',
        self::LABEL_TWITTER => 'icon-[tabler--brand-x]',
        self::LABEL_FACEBOOK => 'icon-[tabler--brand-facebook]',
        self::LABEL_INSTAGRAM => 'icon-[tabler--brand-instagram]',
        self::LABEL_OTHER => 'icon-[tabler--link]',
    ];

    protected $fillable = [
        'person_id',
        'label',
        'url',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function getLabelNameAttribute(): string
    {
        return self::LABELS[$this->label] ?? 'Other';
    }

    public function getIconClassAttribute(): string
    {
        return self::ICONS[$this->label] ?? 'icon-[tabler--link]';
    }
}
