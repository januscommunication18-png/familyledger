<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberMedication extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Common frequency options.
     */
    public const FREQUENCIES = [
        'once_daily' => 'Once Daily',
        'twice_daily' => 'Twice Daily',
        'three_times_daily' => 'Three Times Daily',
        'four_times_daily' => 'Four Times Daily',
        'as_needed' => 'As Needed',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'other' => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'name',
        'dosage',
        'frequency',
        'notes',
    ];

    protected $casts = [
        // AES-256 encrypted PHI fields
        'name' => 'encrypted',
        'dosage' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getFrequencyNameAttribute(): ?string
    {
        if (!$this->frequency) {
            return null;
        }
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }
}
