<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetVaccination extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pet_id',
        'name',
        'date_administered',
        'next_due_date',
        'administered_by',
        'notes',
    ];

    protected $casts = [
        'date_administered' => 'date',
        'next_due_date' => 'date',
    ];

    // ==================== COMMON VACCINATIONS ====================

    public const COMMON_DOG_VACCINATIONS = [
        'Rabies',
        'DHPP (Distemper, Hepatitis, Parvo, Parainfluenza)',
        'Bordetella (Kennel Cough)',
        'Lyme Disease',
        'Leptospirosis',
        'Canine Influenza',
    ];

    public const COMMON_CAT_VACCINATIONS = [
        'Rabies',
        'FVRCP (Feline Distemper)',
        'FeLV (Feline Leukemia)',
        'FIV (Feline Immunodeficiency)',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the pet this vaccination belongs to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Check if vaccination is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->next_due_date && $this->next_due_date->isPast();
    }

    /**
     * Check if vaccination is due soon (within 30 days).
     */
    public function getIsDueSoonAttribute(): bool
    {
        if (!$this->next_due_date) return false;
        return $this->next_due_date->isFuture() && $this->next_due_date->diffInDays(now()) <= 30;
    }

    /**
     * Get days until next due.
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->next_due_date) return null;
        return now()->diffInDays($this->next_due_date, false);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for overdue vaccinations.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('next_due_date')
            ->where('next_due_date', '<', now());
    }

    /**
     * Scope for upcoming vaccinations.
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->whereNotNull('next_due_date')
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays($days));
    }
}
