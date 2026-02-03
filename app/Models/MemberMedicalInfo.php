<?php

namespace App\Models;

use App\Casts\SafeEncrypted;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberMedicalInfo extends Model
{
    use BelongsToTenant;

    protected $table = 'member_medical_info';

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'medications',
        'allergies',
        'medical_conditions',
        'blood_type',
        'primary_physician',
        'physician_phone',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_group_number',
        'notes',
    ];

    protected $casts = [
        // AES-256 encrypted PHI fields (using SafeEncrypted for graceful error handling)
        'blood_type' => SafeEncrypted::class,
        'medications' => SafeEncrypted::class,
        'allergies' => SafeEncrypted::class,
        'medical_conditions' => SafeEncrypted::class,
        'primary_physician' => SafeEncrypted::class,
        'physician_phone' => SafeEncrypted::class,
        'insurance_provider' => SafeEncrypted::class,
        'insurance_policy_number' => SafeEncrypted::class,
        'insurance_group_number' => SafeEncrypted::class,
        'notes' => SafeEncrypted::class,
    ];

    /**
     * Blood type options.
     */
    public const BLOOD_TYPES = [
        'A+' => 'A Positive (A+)',
        'A-' => 'A Negative (A-)',
        'B+' => 'B Positive (B+)',
        'B-' => 'B Negative (B-)',
        'AB+' => 'AB Positive (AB+)',
        'AB-' => 'AB Negative (AB-)',
        'O+' => 'O Positive (O+)',
        'O-' => 'O Negative (O-)',
    ];

    /**
     * Get the family member this medical info belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Check if member has allergies.
     */
    public function hasAllergies(): bool
    {
        return !empty($this->allergies);
    }

    /**
     * Check if member has medical conditions.
     */
    public function hasMedicalConditions(): bool
    {
        return !empty($this->medical_conditions);
    }

    /**
     * Check if member takes medications.
     */
    public function hasMedications(): bool
    {
        return !empty($this->medications);
    }

    /**
     * Check if member has insurance info.
     */
    public function hasInsurance(): bool
    {
        return !empty($this->insurance_provider);
    }
}
