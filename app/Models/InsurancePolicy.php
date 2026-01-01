<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePolicy extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'insurance_type',
        'provider_name',
        'policy_number',
        'group_number',
        'plan_name',
        'premium_amount',
        'payment_frequency',
        'effective_date',
        'expiration_date',
        'status',
        'agent_name',
        'agent_phone',
        'agent_email',
        'claims_phone',
        'card_front_image',
        'card_back_image',
        'policy_documents',
        'coverage_details',
        'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'premium_amount' => 'decimal:2',
        'policy_documents' => 'array',
        // AES-256 encrypted PII fields
        'provider_name' => 'encrypted',
        'policy_number' => 'encrypted',
        'group_number' => 'encrypted',
        'plan_name' => 'encrypted',
        'agent_name' => 'encrypted',
        'agent_phone' => 'encrypted',
        'agent_email' => 'encrypted',
        'claims_phone' => 'encrypted',
        'coverage_details' => 'encrypted',
        'notes' => 'encrypted',
    ];

    /**
     * Insurance types.
     */
    public const INSURANCE_TYPES = [
        'health' => 'Health Insurance',
        'dental' => 'Dental Insurance',
        'vision' => 'Vision Insurance',
        'life' => 'Life Insurance',
        'auto' => 'Auto Insurance',
        'home' => 'Home Insurance',
        'renters' => 'Renters Insurance',
        'disability' => 'Disability Insurance',
        'umbrella' => 'Umbrella Insurance',
        'pet' => 'Pet Insurance',
        'travel' => 'Travel Insurance',
        'other' => 'Other',
    ];

    /**
     * Policy statuses.
     */
    public const STATUSES = [
        'active' => 'Active',
        'pending' => 'Pending',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Payment frequencies.
     */
    public const PAYMENT_FREQUENCIES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'semi_annual' => 'Semi-Annual',
        'annual' => 'Annual',
    ];

    /**
     * Get all policyholders.
     */
    public function policyholders(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'insurance_policy_members')
            ->wherePivot('member_type', 'policyholder')
            ->withPivot('member_type', 'relationship_to_policyholder')
            ->withTimestamps();
    }

    /**
     * Get all covered family members.
     */
    public function coveredMembers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'insurance_policy_members')
            ->wherePivot('member_type', 'covered')
            ->withPivot('member_type', 'relationship_to_policyholder')
            ->withTimestamps();
    }

    /**
     * Check if policy is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if policy is expiring soon (within 30 days).
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'expired' => 'error',
            'cancelled' => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Get insurance type icon.
     */
    public function getTypeIcon(): string
    {
        return match ($this->insurance_type) {
            'health' => 'icon-[tabler--heart-rate-monitor]',
            'dental' => 'icon-[tabler--dental]',
            'vision' => 'icon-[tabler--eye]',
            'life' => 'icon-[tabler--heart]',
            'auto' => 'icon-[tabler--car]',
            'home' => 'icon-[tabler--home]',
            'renters' => 'icon-[tabler--building]',
            'disability' => 'icon-[tabler--wheelchair]',
            'umbrella' => 'icon-[tabler--umbrella]',
            'pet' => 'icon-[tabler--paw]',
            'travel' => 'icon-[tabler--plane]',
            default => 'icon-[tabler--file-certificate]',
        };
    }
}
