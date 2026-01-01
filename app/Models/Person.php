<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Person extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Relationship type constants.
     */
    public const RELATIONSHIP_FAMILY = 'family';
    public const RELATIONSHIP_FRIEND = 'friend';
    public const RELATIONSHIP_NEIGHBOR = 'neighbor';
    public const RELATIONSHIP_DOCTOR = 'doctor';
    public const RELATIONSHIP_LAWYER = 'lawyer';
    public const RELATIONSHIP_SCHOOL = 'school';
    public const RELATIONSHIP_CONTRACTOR = 'contractor';
    public const RELATIONSHIP_BABYSITTER = 'babysitter';
    public const RELATIONSHIP_EMERGENCY = 'emergency';
    public const RELATIONSHIP_OTHER = 'other';

    public const RELATIONSHIPS = [
        self::RELATIONSHIP_FAMILY => 'Family',
        self::RELATIONSHIP_FRIEND => 'Friend',
        self::RELATIONSHIP_NEIGHBOR => 'Neighbor',
        self::RELATIONSHIP_DOCTOR => 'Doctor',
        self::RELATIONSHIP_LAWYER => 'Lawyer',
        self::RELATIONSHIP_SCHOOL => 'School',
        self::RELATIONSHIP_CONTRACTOR => 'Contractor',
        self::RELATIONSHIP_BABYSITTER => 'Babysitter',
        self::RELATIONSHIP_EMERGENCY => 'Emergency Contact',
        self::RELATIONSHIP_OTHER => 'Other',
    ];

    /**
     * Source constants.
     */
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_GOOGLE = 'google';
    public const SOURCE_IPHONE = 'iphone';
    public const SOURCE_VCARD = 'vcard';

    public const SOURCES = [
        self::SOURCE_MANUAL => 'Manual',
        self::SOURCE_GOOGLE => 'Google Contacts',
        self::SOURCE_IPHONE => 'iPhone Contacts',
        self::SOURCE_VCARD => 'Imported vCard',
    ];

    /**
     * Visibility constants.
     */
    public const VISIBILITY_FAMILY = 'family';
    public const VISIBILITY_SPECIFIC = 'specific';
    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITIES = [
        self::VISIBILITY_FAMILY => 'Family Visible',
        self::VISIBILITY_SPECIFIC => 'Specific Members',
        self::VISIBILITY_PRIVATE => 'Private',
    ];

    protected $table = 'people';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'full_name',
        'nickname',
        'relationship',
        'custom_relationship',
        'company',
        'job_title',
        'birthday',
        'notes',
        'how_we_know',
        'tags',
        'profile_image',
        'source',
        'google_contact_id',
        'ios_contact_id',
        'last_synced_at',
        'visibility',
        'visible_to_members',
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_synced_at' => 'datetime',
        'tags' => 'array',
        'visible_to_members' => 'array',
        // AES-256 encrypted PII fields
        'full_name' => 'encrypted',
        'nickname' => 'encrypted',
        'company' => 'encrypted',
        'job_title' => 'encrypted',
        'how_we_know' => 'encrypted',
        'notes' => 'encrypted',
    ];

    /**
     * Get the user who created this person.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all emails for this person.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(PersonEmail::class);
    }

    /**
     * Get all phones for this person.
     */
    public function phones(): HasMany
    {
        return $this->hasMany(PersonPhone::class);
    }

    /**
     * Get all addresses for this person.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(PersonAddress::class);
    }

    /**
     * Get all important dates for this person.
     */
    public function importantDates(): HasMany
    {
        return $this->hasMany(PersonImportantDate::class);
    }

    /**
     * Get all attachments for this person.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(PersonAttachment::class);
    }

    /**
     * Get all links for this person.
     */
    public function links(): HasMany
    {
        return $this->hasMany(PersonLink::class);
    }

    /**
     * Get the primary email.
     */
    public function getPrimaryEmailAttribute(): ?PersonEmail
    {
        return $this->emails->where('is_primary', true)->first()
            ?? $this->emails->first();
    }

    /**
     * Get the primary phone.
     */
    public function getPrimaryPhoneAttribute(): ?PersonPhone
    {
        return $this->phones->where('is_primary', true)->first()
            ?? $this->phones->first();
    }

    /**
     * Get the primary address.
     */
    public function getPrimaryAddressAttribute(): ?PersonAddress
    {
        return $this->addresses->where('is_primary', true)->first()
            ?? $this->addresses->first();
    }

    /**
     * Get the relationship display name.
     */
    public function getRelationshipNameAttribute(): string
    {
        if ($this->relationship === self::RELATIONSHIP_OTHER && $this->custom_relationship) {
            return $this->custom_relationship;
        }
        return self::RELATIONSHIPS[$this->relationship] ?? 'Unknown';
    }

    /**
     * Get the source display name.
     */
    public function getSourceNameAttribute(): string
    {
        return self::SOURCES[$this->source] ?? 'Unknown';
    }

    /**
     * Get the visibility display name.
     */
    public function getVisibilityNameAttribute(): string
    {
        return self::VISIBILITIES[$this->visibility] ?? 'Unknown';
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }
        return Storage::disk('do_spaces')->url($this->profile_image);
    }

    /**
     * Get the relationship color for badges.
     */
    public function getRelationshipColorAttribute(): string
    {
        return match ($this->relationship) {
            self::RELATIONSHIP_FAMILY => 'primary',
            self::RELATIONSHIP_FRIEND => 'info',
            self::RELATIONSHIP_NEIGHBOR => 'secondary',
            self::RELATIONSHIP_DOCTOR => 'success',
            self::RELATIONSHIP_LAWYER => 'warning',
            self::RELATIONSHIP_SCHOOL => 'accent',
            self::RELATIONSHIP_CONTRACTOR => 'neutral',
            self::RELATIONSHIP_BABYSITTER => 'info',
            self::RELATIONSHIP_EMERGENCY => 'error',
            default => 'ghost',
        };
    }

    /**
     * Get formatted birthday.
     */
    public function getFormattedBirthdayAttribute(): ?string
    {
        return $this->birthday?->format('F j, Y');
    }

    /**
     * Get age if birthday is set.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birthday?->age;
    }

    /**
     * Check if contact has upcoming birthday (within next 30 days).
     */
    public function hasUpcomingBirthday(): bool
    {
        if (!$this->birthday) {
            return false;
        }

        $today = now()->startOfDay();
        $birthdayThisYear = $this->birthday->copy()->year($today->year);

        if ($birthdayThisYear->isPast()) {
            $birthdayThisYear->addYear();
        }

        return $birthdayThisYear->diffInDays($today) <= 30;
    }

    /**
     * Get initials for avatar.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->full_name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }

    /**
     * Scope to filter by relationship.
     */
    public function scopeByRelationship($query, string $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    /**
     * Scope to filter by tag.
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope to search by name, company, or notes.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('how_we_know', 'like', "%{$search}%");
        });
    }
}
