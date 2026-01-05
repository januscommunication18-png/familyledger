<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetMedication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pet_id',
        'name',
        'dosage',
        'frequency',
        'start_date',
        'end_date',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    public const FREQUENCIES = [
        'once_daily' => 'Once Daily',
        'twice_daily' => 'Twice Daily',
        'three_times_daily' => 'Three Times Daily',
        'every_other_day' => 'Every Other Day',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'as_needed' => 'As Needed',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the pet this medication belongs to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency ?? 'Unknown';
    }

    /**
     * Check if medication has ended.
     */
    public function getIsEndedAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active medications.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive medications.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
