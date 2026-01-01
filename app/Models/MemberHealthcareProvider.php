<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberHealthcareProvider extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Provider type constants.
     */
    public const TYPE_PRIMARY_DOCTOR = 'primary_doctor';
    public const TYPE_SPECIALIST = 'specialist';
    public const TYPE_DENTIST = 'dentist';
    public const TYPE_THERAPIST = 'therapist';
    public const TYPE_EMERGENCY_DOCTOR = 'emergency_doctor';
    public const TYPE_OTHER = 'other';

    public const PROVIDER_TYPES = [
        self::TYPE_PRIMARY_DOCTOR => 'Primary Doctor',
        self::TYPE_SPECIALIST => 'Specialist',
        self::TYPE_DENTIST => 'Dentist',
        self::TYPE_THERAPIST => 'Therapist',
        self::TYPE_EMERGENCY_DOCTOR => 'Emergency Contact Doctor',
        self::TYPE_OTHER => 'Other',
    ];

    /**
     * Common specialties.
     */
    public const SPECIALTIES = [
        'general_practitioner' => 'General Practitioner (GP)',
        'pediatrician' => 'Pediatrician',
        'cardiologist' => 'Cardiologist',
        'dermatologist' => 'Dermatologist',
        'neurologist' => 'Neurologist',
        'orthopedist' => 'Orthopedist',
        'psychiatrist' => 'Psychiatrist',
        'psychologist' => 'Psychologist',
        'allergist' => 'Allergist / Immunologist',
        'endocrinologist' => 'Endocrinologist',
        'gastroenterologist' => 'Gastroenterologist',
        'ophthalmologist' => 'Ophthalmologist',
        'ent' => 'ENT (Ear, Nose, Throat)',
        'obgyn' => 'OB-GYN',
        'urologist' => 'Urologist',
        'pulmonologist' => 'Pulmonologist',
        'oncologist' => 'Oncologist',
        'rheumatologist' => 'Rheumatologist',
        'other' => 'Other',
    ];

    /**
     * Contact methods.
     */
    public const CONTACT_METHODS = [
        'phone' => 'Phone',
        'email' => 'Email',
        'portal' => 'Patient Portal',
        'text' => 'Text Message',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'provider_type',
        'name',
        'specialty',
        'clinic_name',
        'phone',
        'phone_country_code',
        'email',
        'address',
        'preferred_contact',
        'notes',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        // AES-256 encrypted PII fields
        'name' => 'encrypted',
        'clinic_name' => 'encrypted',
        'phone' => 'encrypted',
        'phone_country_code' => 'encrypted',
        'email' => 'encrypted',
        'address' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getProviderTypeNameAttribute(): string
    {
        return self::PROVIDER_TYPES[$this->provider_type] ?? 'Unknown';
    }

    public function getSpecialtyNameAttribute(): ?string
    {
        if (!$this->specialty) {
            return null;
        }
        return self::SPECIALTIES[$this->specialty] ?? $this->specialty;
    }

    public function getPreferredContactNameAttribute(): ?string
    {
        if (!$this->preferred_contact) {
            return null;
        }
        return self::CONTACT_METHODS[$this->preferred_contact] ?? $this->preferred_contact;
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('provider_type', $type);
    }
}
