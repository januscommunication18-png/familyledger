<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberVaccination extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Common vaccine types.
     */
    public const VACCINE_TYPES = [
        'covid_19' => 'COVID-19',
        'dtap' => 'DTaP',
        'hepa' => 'HepA',
        'hepb' => 'HepB',
        'hib' => 'Hib',
        'hpv' => 'HPV',
        'influenza' => 'Influenza (Flu)',
        'meningococcal' => 'Meningococcal',
        'meningococcal_b' => 'Meningococcal B',
        'mmr' => 'MMR',
        'pneumococcal' => 'Pneumococcal',
        'polio' => 'Polio',
        'rotavirus' => 'Rotavirus',
        'tdap' => 'Tdap',
        'varicella' => 'Varicella (Chickenpox)',
        'other' => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'vaccine_type',
        'custom_vaccine_name',
        'vaccination_date',
        'next_vaccination_date',
        'administered_by',
        'lot_number',
        'notes',
        'document_path',
        'document_name',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'next_vaccination_date' => 'date',
        // AES-256 encrypted PHI fields
        'custom_vaccine_name' => 'encrypted',
        'administered_by' => 'encrypted',
        'lot_number' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the display name for the vaccine.
     */
    public function getVaccineNameAttribute(): string
    {
        if ($this->vaccine_type === 'other' && $this->custom_vaccine_name) {
            return $this->custom_vaccine_name;
        }
        return self::VACCINE_TYPES[$this->vaccine_type] ?? $this->vaccine_type;
    }

    /**
     * Check if the next vaccination is due.
     */
    public function getIsDueAttribute(): bool
    {
        if (!$this->next_vaccination_date) {
            return false;
        }
        return $this->next_vaccination_date->isPast() || $this->next_vaccination_date->isToday();
    }

    /**
     * Check if the next vaccination is coming soon (within 30 days).
     */
    public function getIsComingSoonAttribute(): bool
    {
        if (!$this->next_vaccination_date) {
            return false;
        }
        return $this->next_vaccination_date->isFuture() &&
               $this->next_vaccination_date->diffInDays(now()) <= 30;
    }
}
