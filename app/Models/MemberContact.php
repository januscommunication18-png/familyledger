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
    ];

    /**
     * Common relationship/role types for contacts.
     */
    public const RELATIONSHIP_TYPES = [
        'doctor' => 'Doctor',
        'pediatrician' => 'Pediatrician',
        'dentist' => 'Dentist',
        'therapist' => 'Therapist',
        'teacher' => 'Teacher',
        'coach' => 'Coach',
        'tutor' => 'Tutor',
        'babysitter' => 'Babysitter',
        'nanny' => 'Nanny',
        'neighbor' => 'Neighbor',
        'family_friend' => 'Family Friend',
        'relative' => 'Relative',
        'emergency' => 'Emergency Contact',
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
