<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberContact extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'name',
        'email',
        'phone',
        'phone_country_code',
        'relationship',
        'address',
        'notes',
        'is_emergency_contact',
        'priority',
    ];

    protected $casts = [
        'is_emergency_contact' => 'boolean',
        'priority' => 'integer',
        // AES-256 encrypted PII fields
        'name' => 'encrypted',
        'email' => 'encrypted',
        'phone' => 'encrypted',
        'phone_country_code' => 'encrypted',
        'address' => 'encrypted',
        'notes' => 'encrypted',
    ];

    /**
     * Common relationship/role types for contacts.
     */
    public const RELATIONSHIP_TYPES = [
        // Family - Immediate
        'spouse' => 'Spouse',
        'partner' => 'Partner',
        'parent' => 'Parent',
        'father' => 'Father',
        'mother' => 'Mother',
        'child' => 'Child',
        'son' => 'Son',
        'daughter' => 'Daughter',
        'sibling' => 'Sibling',
        'brother' => 'Brother',
        'sister' => 'Sister',
        'legal_guardian' => 'Legal Guardian',
        // Family - Extended
        'aunt' => 'Aunt',
        'uncle' => 'Uncle',
        'cousin' => 'Cousin',
        'niece' => 'Niece',
        'nephew' => 'Nephew',
        'in_law' => 'In-Law',
        // Non-Family
        'close_friend' => 'Close Friend',
        'neighbor' => 'Neighbor',
        'roommate' => 'Roommate / Housemate',
        // Caregivers
        'caregiver' => 'Caregiver',
        'babysitter' => 'Babysitter / Nanny',
        'emergency_contact' => 'Emergency Contact (Non-Family)',
        // Education & Activities
        'teacher' => 'Teacher',
        'school_counselor' => 'School Counselor',
        'daycare' => 'Daycare',
        'after_school' => 'After-School Program',
        'coach' => 'Coach',
        // Professional
        'lawyer' => 'Lawyer / Attorney',
        'social_worker' => 'Social Worker',
        'hr_contact' => 'HR Contact',
        // Property
        'landlord' => 'Landlord',
        'property_manager' => 'Property Manager',
        'building_security' => 'Building Security',
        // Other
        'other' => 'Other',
    ];

    /**
     * Get the family member this contact belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the relationship display name.
     */
    public function getRelationshipNameAttribute(): ?string
    {
        if (!$this->relationship) {
            return null;
        }
        return self::RELATIONSHIP_TYPES[$this->relationship] ?? $this->relationship;
    }

    /**
     * Scope for emergency contacts.
     */
    public function scopeEmergency($query)
    {
        return $query->where('is_emergency_contact', true);
    }

    /**
     * Scope ordered by priority.
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}
