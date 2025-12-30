<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invited_by',
        'email',
        'phone',
        'role',
        'relationship',
        'token',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const ROLES = [
        'parent' => 'Parent / Primary Guardian',
        'coparent' => 'Co-parent',
        'guardian' => 'Guardian',
        'family_member' => 'Family Member',
        'advisor' => 'Advisor',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public function isPending(): bool
    {
        return !$this->isExpired() && !$this->isAccepted();
    }

    public function accept(): void
    {
        $this->update(['accepted_at' => now()]);
    }
}
