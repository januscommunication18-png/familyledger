<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberMedicalCondition extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Condition status options.
     */
    public const STATUSES = [
        'active' => 'Active',
        'managed' => 'Managed',
        'resolved' => 'Resolved',
        'monitoring' => 'Monitoring',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'name',
        'status',
        'diagnosed_date',
        'notes',
    ];

    protected $casts = [
        'diagnosed_date' => 'date',
    ];

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getStatusNameAttribute(): ?string
    {
        if (!$this->status) {
            return null;
        }
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'rose',
            'managed' => 'emerald',
            'resolved' => 'slate',
            'monitoring' => 'amber',
            default => 'slate',
        };
    }
}
