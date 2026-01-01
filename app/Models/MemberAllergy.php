<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberAllergy extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Allergy type constants.
     */
    public const TYPE_FOOD = 'food';
    public const TYPE_MEDICATION = 'medication';
    public const TYPE_ENVIRONMENTAL = 'environmental';
    public const TYPE_LATEX = 'latex';
    public const TYPE_OTHER = 'other';

    public const ALLERGY_TYPES = [
        self::TYPE_FOOD => 'Food',
        self::TYPE_MEDICATION => 'Medication',
        self::TYPE_ENVIRONMENTAL => 'Environmental',
        self::TYPE_LATEX => 'Latex',
        self::TYPE_OTHER => 'Other',
    ];

    /**
     * Severity constants.
     */
    public const SEVERITY_MILD = 'mild';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_SEVERE = 'severe';
    public const SEVERITY_LIFE_THREATENING = 'life_threatening';

    public const SEVERITIES = [
        self::SEVERITY_MILD => 'Mild',
        self::SEVERITY_MODERATE => 'Moderate',
        self::SEVERITY_SEVERE => 'Severe',
        self::SEVERITY_LIFE_THREATENING => 'Life-Threatening',
    ];

    /**
     * Common symptom options.
     */
    public const SYMPTOMS = [
        'rash' => 'Rash / Hives',
        'breathing_difficulty' => 'Breathing Difficulty',
        'swelling' => 'Swelling',
        'nausea' => 'Nausea / Vomiting',
        'anaphylaxis' => 'Anaphylaxis',
        'itching' => 'Itching',
        'runny_nose' => 'Runny Nose',
        'watery_eyes' => 'Watery Eyes',
        'dizziness' => 'Dizziness',
        'other' => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'allergy_type',
        'allergen_name',
        'severity',
        'symptoms',
        'emergency_instructions',
        'notes',
    ];

    protected $casts = [
        'symptoms' => 'array',
        // AES-256 encrypted PHI fields
        'allergen_name' => 'encrypted',
        'emergency_instructions' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getAllergyTypeNameAttribute(): string
    {
        return self::ALLERGY_TYPES[$this->allergy_type] ?? 'Unknown';
    }

    public function getSeverityNameAttribute(): string
    {
        return self::SEVERITIES[$this->severity] ?? 'Unknown';
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_MILD => 'emerald',
            self::SEVERITY_MODERATE => 'amber',
            self::SEVERITY_SEVERE => 'orange',
            self::SEVERITY_LIFE_THREATENING => 'rose',
            default => 'slate',
        };
    }

    public function getSymptomNamesAttribute(): array
    {
        if (!$this->symptoms) {
            return [];
        }

        return array_map(function ($symptom) {
            return self::SYMPTOMS[$symptom] ?? $symptom;
        }, $this->symptoms);
    }

    public function isLifeThreatening(): bool
    {
        return $this->severity === self::SEVERITY_LIFE_THREATENING;
    }
}
