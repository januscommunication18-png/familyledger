<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonAddress extends Model
{
    public const LABEL_HOME = 'home';
    public const LABEL_WORK = 'work';
    public const LABEL_OTHER = 'other';

    public const LABELS = [
        self::LABEL_HOME => 'Home',
        self::LABEL_WORK => 'Work',
        self::LABEL_OTHER => 'Other',
    ];

    protected $fillable = [
        'person_id',
        'label',
        'street_address',
        'street_address_2',
        'city',
        'state',
        'zip_code',
        'country',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function getLabelNameAttribute(): string
    {
        return self::LABELS[$this->label] ?? 'Other';
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->street_address_2,
            $this->city,
            $this->state . ' ' . $this->zip_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getCityStateAttribute(): string
    {
        $parts = array_filter([
            $this->city,
            $this->state,
        ]);

        return implode(', ', $parts);
    }
}
