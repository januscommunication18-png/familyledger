<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class FamilyMember extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Relationship type constants.
     */
    public const RELATIONSHIP_SELF = 'self';
    public const RELATIONSHIP_SPOUSE = 'spouse';
    public const RELATIONSHIP_PARTNER = 'partner';
    public const RELATIONSHIP_CHILD = 'child';
    public const RELATIONSHIP_STEPCHILD = 'stepchild';
    public const RELATIONSHIP_PARENT = 'parent';
    public const RELATIONSHIP_SIBLING = 'sibling';
    public const RELATIONSHIP_GRANDPARENT = 'grandparent';
    public const RELATIONSHIP_GUARDIAN = 'guardian';
    public const RELATIONSHIP_CAREGIVER = 'caregiver';
    public const RELATIONSHIP_RELATIVE = 'relative';
    public const RELATIONSHIP_OTHER = 'other';

    public const RELATIONSHIPS = [
        self::RELATIONSHIP_SELF => 'Self',
        self::RELATIONSHIP_SPOUSE => 'Spouse',
        self::RELATIONSHIP_PARTNER => 'Partner',
        self::RELATIONSHIP_CHILD => 'Child',
        self::RELATIONSHIP_STEPCHILD => 'Stepchild',
        self::RELATIONSHIP_PARENT => 'Parent',
        self::RELATIONSHIP_SIBLING => 'Sibling',
        self::RELATIONSHIP_GRANDPARENT => 'Grandparent',
        self::RELATIONSHIP_GUARDIAN => 'Guardian',
        self::RELATIONSHIP_CAREGIVER => 'Caregiver',
        self::RELATIONSHIP_RELATIVE => 'Relative',
        self::RELATIONSHIP_OTHER => 'Other',
    ];

    /**
     * Immigration status constants.
     */
    public const IMMIGRATION_CITIZEN = 'citizen';
    public const IMMIGRATION_PERMANENT_RESIDENT = 'permanent_resident';
    public const IMMIGRATION_VISA_HOLDER = 'visa_holder';
    public const IMMIGRATION_WORK_PERMIT = 'work_permit';
    public const IMMIGRATION_STUDENT_VISA = 'student_visa';
    public const IMMIGRATION_REFUGEE_ASYLUM = 'refugee_asylum';
    public const IMMIGRATION_OTHER = 'other';

    public const IMMIGRATION_STATUSES = [
        self::IMMIGRATION_CITIZEN => 'Citizen',
        self::IMMIGRATION_PERMANENT_RESIDENT => 'Permanent Resident',
        self::IMMIGRATION_VISA_HOLDER => 'Visa Holder',
        self::IMMIGRATION_WORK_PERMIT => 'Work Permit',
        self::IMMIGRATION_STUDENT_VISA => 'Student Visa',
        self::IMMIGRATION_REFUGEE_ASYLUM => 'Refugee/Asylum',
        self::IMMIGRATION_OTHER => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'family_circle_id',
        'created_by',
        'linked_user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_country_code',
        'date_of_birth',
        'profile_image',
        'relationship',
        'father_name',
        'mother_name',
        'is_minor',
        'co_parenting_enabled',
        'immigration_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_minor' => 'boolean',
        'co_parenting_enabled' => 'boolean',
    ];

    /**
     * Get the family circle this member belongs to.
     */
    public function familyCircle(): BelongsTo
    {
        return $this->belongsTo(FamilyCircle::class);
    }

    /**
     * Get the user who created this member.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the linked user account if any.
     */
    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    /**
     * Get all documents for this member.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MemberDocument::class);
    }

    /**
     * Get the medical info for this member.
     */
    public function medicalInfo(): HasOne
    {
        return $this->hasOne(MemberMedicalInfo::class);
    }

    /**
     * Get the school info for this member.
     */
    public function schoolInfo(): HasOne
    {
        return $this->hasOne(MemberSchoolInfo::class);
    }

    /**
     * Get all contacts for this member.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(MemberContact::class);
    }

    /**
     * Get all allergies for this member.
     */
    public function allergies(): HasMany
    {
        return $this->hasMany(MemberAllergy::class);
    }

    /**
     * Get life-threatening allergies.
     */
    public function criticalAllergies(): HasMany
    {
        return $this->allergies()->where('severity', MemberAllergy::SEVERITY_LIFE_THREATENING);
    }

    /**
     * Get all healthcare providers for this member.
     */
    public function healthcareProviders(): HasMany
    {
        return $this->hasMany(MemberHealthcareProvider::class);
    }

    /**
     * Get all medications for this member.
     */
    public function medications(): HasMany
    {
        return $this->hasMany(MemberMedication::class);
    }

    /**
     * Get all medical conditions for this member.
     */
    public function medicalConditions(): HasMany
    {
        return $this->hasMany(MemberMedicalCondition::class);
    }

    /**
     * Get active medical conditions.
     */
    public function activeMedicalConditions(): HasMany
    {
        return $this->medicalConditions()->where('status', 'active');
    }

    /**
     * Get primary doctor.
     */
    public function getPrimaryDoctorAttribute(): ?MemberHealthcareProvider
    {
        return $this->healthcareProviders
            ->where('provider_type', MemberHealthcareProvider::TYPE_PRIMARY_DOCTOR)
            ->where('is_primary', true)
            ->first()
            ?? $this->healthcareProviders
                ->where('provider_type', MemberHealthcareProvider::TYPE_PRIMARY_DOCTOR)
                ->first();
    }

    /**
     * Get the full name of the member.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the age of the member.
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the profile image URL from DigitalOcean Spaces.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        return Storage::disk('do_spaces')->url($this->profile_image);
    }

    /**
     * Get the relationship display name.
     */
    public function getRelationshipNameAttribute(): string
    {
        return self::RELATIONSHIPS[$this->relationship] ?? 'Unknown';
    }

    /**
     * Get the immigration status display name.
     */
    public function getImmigrationStatusNameAttribute(): ?string
    {
        if (!$this->immigration_status) {
            return null;
        }
        return self::IMMIGRATION_STATUSES[$this->immigration_status] ?? 'Unknown';
    }

    /**
     * Check if member is a child (under 18).
     */
    public function isChild(): bool
    {
        return $this->is_minor || $this->age < 18;
    }

    /**
     * Get emergency contacts for this member.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->contacts()->where('is_emergency_contact', true)->orderBy('priority');
    }

    /**
     * Get the audit logs for this member.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(MemberAuditLog::class);
    }

    /**
     * Get driver's license document.
     */
    public function getDriversLicenseAttribute(): ?MemberDocument
    {
        return $this->documents->where('document_type', MemberDocument::TYPE_DRIVERS_LICENSE)->first();
    }

    /**
     * Get passport document.
     */
    public function getPassportAttribute(): ?MemberDocument
    {
        return $this->documents->where('document_type', MemberDocument::TYPE_PASSPORT)->first();
    }

    /**
     * Get social security document.
     */
    public function getSocialSecurityAttribute(): ?MemberDocument
    {
        return $this->documents->where('document_type', MemberDocument::TYPE_SOCIAL_SECURITY)->first();
    }

    /**
     * Get birth certificate document.
     */
    public function getBirthCertificateAttribute(): ?MemberDocument
    {
        return $this->documents->where('document_type', MemberDocument::TYPE_BIRTH_CERTIFICATE)->first();
    }

    /**
     * Get insurance policies where this member is covered.
     */
    public function insurancePolicies(): BelongsToMany
    {
        return $this->belongsToMany(InsurancePolicy::class, 'insurance_policy_members')
            ->withPivot('member_type', 'relationship_to_policyholder')
            ->withTimestamps();
    }

    /**
     * Get tax returns where this member is a taxpayer.
     */
    public function taxReturns(): BelongsToMany
    {
        return $this->belongsToMany(TaxReturn::class, 'tax_return_taxpayers')
            ->withTimestamps();
    }

    /**
     * Get assets where this member is an owner.
     */
    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_owners')
            ->withPivot('ownership_percentage', 'is_primary_owner', 'external_owner_name')
            ->withTimestamps();
    }
}
