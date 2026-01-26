<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'species',
        'breed',
        'date_of_birth',
        'approx_age',
        'gender',
        'photo',
        'microchip_id',
        'status',
        'passed_away_date',
        'allergies',
        'conditions',
        'last_vet_visit',
        'notes',
        'visibility',
        'vet_name',
        'vet_phone',
        'vet_clinic',
        'vet_address',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passed_away_date' => 'date',
        'last_vet_visit' => 'date',
    ];

    // ==================== CONSTANTS ====================

    public const SPECIES = [
        'dog' => ['label' => 'Dog', 'emoji' => '🐕'],
        'cat' => ['label' => 'Cat', 'emoji' => '🐈'],
        'bird' => ['label' => 'Bird', 'emoji' => '🐦'],
        'fish' => ['label' => 'Fish', 'emoji' => '🐠'],
        'reptile' => ['label' => 'Reptile', 'emoji' => '🦎'],
        'rabbit' => ['label' => 'Rabbit', 'emoji' => '🐰'],
        'other' => ['label' => 'Other', 'emoji' => '🐾'],
    ];

    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'unknown' => 'Unknown',
    ];

    public const STATUSES = [
        'active' => ['label' => 'Active', 'color' => 'success'],
        'passed_away' => ['label' => 'Passed Away 🌈', 'color' => 'slate'],
    ];

    public const VISIBILITY = [
        'family' => 'Visible to all family',
        'caregivers_only' => 'Caregivers only',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who created this pet.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all caregivers for this pet.
     */
    public function caregivers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'pet_caregivers')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the primary caregiver.
     */
    public function primaryCaregiver(): BelongsToMany
    {
        return $this->caregivers()->wherePivot('role', 'primary');
    }

    /**
     * Get secondary caregivers.
     */
    public function secondaryCaregivers(): BelongsToMany
    {
        return $this->caregivers()->wherePivot('role', 'secondary');
    }

    /**
     * Get pet vaccinations.
     */
    public function vaccinations(): HasMany
    {
        return $this->hasMany(PetVaccination::class);
    }

    /**
     * Get pet medications.
     */
    public function medications(): HasMany
    {
        return $this->hasMany(PetMedication::class);
    }

    /**
     * Get tasks/reminders linked to this pet.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TodoItem::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get species emoji.
     */
    public function getSpeciesEmojiAttribute(): string
    {
        return self::SPECIES[$this->species]['emoji'] ?? '🐾';
    }

    /**
     * Get species label.
     */
    public function getSpeciesLabelAttribute(): string
    {
        return self::SPECIES[$this->species]['label'] ?? 'Pet';
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'slate';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Unknown';
    }

    /**
     * Get pet's age.
     */
    public function getAgeAttribute(): ?string
    {
        if ($this->date_of_birth) {
            $years = (int) $this->date_of_birth->diffInYears(now());
            $months = (int) ($this->date_of_birth->diffInMonths(now()) % 12);

            if ($years > 0) {
                return $years . ' ' . ($years === 1 ? 'year' : 'years') .
                    ($months > 0 ? ', ' . $months . ' ' . ($months === 1 ? 'month' : 'months') : '');
            }
            return $months . ' ' . ($months === 1 ? 'month' : 'months');
        }

        // Format approx_age as readable string
        if ($this->approx_age) {
            $years = (int) floor($this->approx_age);
            $months = (int) round(($this->approx_age - $years) * 12);

            if ($years > 0) {
                return $years . ' ' . ($years === 1 ? 'year' : 'years') .
                    ($months > 0 ? ', ' . $months . ' ' . ($months === 1 ? 'month' : 'months') : '');
            }
            return $months . ' ' . ($months === 1 ? 'month' : 'months');
        }

        return null;
    }

    /**
     * Check if pet is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if pet has passed away.
     */
    public function getIsPassedAwayAttribute(): bool
    {
        return $this->status === 'passed_away';
    }

    /**
     * Get upcoming vaccinations.
     */
    public function getUpcomingVaccinationsAttribute()
    {
        return $this->vaccinations()
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '>=', now())
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get overdue vaccinations.
     */
    public function getOverdueVaccinationsAttribute()
    {
        return $this->vaccinations()
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<', now())
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get active medications.
     */
    public function getActiveMedicationsAttribute()
    {
        return $this->medications()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return Storage::disk('do_spaces')->url($this->photo);
        }
        return null;
    }

    /**
     * Get default avatar based on species.
     */
    public function getDefaultAvatarAttribute(): string
    {
        return $this->species_emoji;
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only active pets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to include passed away pets.
     */
    public function scopeWithPassedAway($query)
    {
        return $query; // No filter, returns all
    }

    /**
     * Scope by species.
     */
    public function scopeOfSpecies($query, string $species)
    {
        return $query->where('species', $species);
    }

    /**
     * Scope for pets with upcoming vaccination reminders.
     */
    public function scopeNeedsVaccination($query)
    {
        return $query->whereHas('vaccinations', function ($q) {
            $q->whereNotNull('next_due_date')
                ->where('next_due_date', '<=', now()->addDays(30));
        });
    }

    // ==================== METHODS ====================

    /**
     * Mark pet as passed away.
     */
    public function markPassedAway(?Carbon $date = null): void
    {
        $this->update([
            'status' => 'passed_away',
            'passed_away_date' => $date ?? now(),
        ]);
    }

    /**
     * Add a caregiver to the pet.
     */
    public function addCaregiver(int $familyMemberId, string $role = 'secondary', ?string $notes = null): void
    {
        $this->caregivers()->syncWithoutDetaching([
            $familyMemberId => ['role' => $role, 'notes' => $notes]
        ]);
    }

    /**
     * Remove a caregiver from the pet.
     */
    public function removeCaregiver(int $familyMemberId): void
    {
        $this->caregivers()->detach($familyMemberId);
    }

    /**
     * Set the primary caregiver.
     */
    public function setPrimaryCaregiver(int $familyMemberId): void
    {
        // Remove existing primary
        $this->caregivers()->wherePivot('role', 'primary')->updateExistingPivot(
            $this->caregivers()->wherePivot('role', 'primary')->first()?->id,
            ['role' => 'secondary']
        );

        // Set new primary
        if ($this->caregivers()->find($familyMemberId)) {
            $this->caregivers()->updateExistingPivot($familyMemberId, ['role' => 'primary']);
        } else {
            $this->addCaregiver($familyMemberId, 'primary');
        }
    }
}
