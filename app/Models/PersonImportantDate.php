<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonImportantDate extends Model
{
    protected $fillable = [
        'person_id',
        'label',
        'date',
        'recurring_yearly',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'recurring_yearly' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    /**
     * Check if this date is upcoming (within next 30 days).
     */
    public function isUpcoming(): bool
    {
        $today = now()->startOfDay();

        if ($this->recurring_yearly) {
            $dateThisYear = $this->date->copy()->year($today->year);

            if ($dateThisYear->isPast()) {
                $dateThisYear->addYear();
            }

            return $dateThisYear->diffInDays($today) <= 30;
        }

        return $this->date->isFuture() && $this->date->diffInDays($today) <= 30;
    }

    /**
     * Get the next occurrence of this date.
     */
    public function getNextOccurrenceAttribute(): ?\Carbon\Carbon
    {
        $today = now()->startOfDay();

        if ($this->recurring_yearly) {
            $dateThisYear = $this->date->copy()->year($today->year);

            if ($dateThisYear->isPast()) {
                return $dateThisYear->addYear();
            }

            return $dateThisYear;
        }

        return $this->date->isFuture() ? $this->date : null;
    }
}
