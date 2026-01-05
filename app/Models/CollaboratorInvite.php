<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CollaboratorInvite extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invited_by',
        'email',
        'first_name',
        'last_name',
        'message',
        'relationship_type',
        'role',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'declined_at',
        'revoked_at',
        'accepted_user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const RELATIONSHIP_TYPES = [
        'parent' => ['label' => 'Parent', 'icon' => 'tabler--user', 'color' => 'primary'],
        'spouse' => ['label' => 'Spouse / Partner', 'icon' => 'tabler--heart', 'color' => 'pink-500'],
        'co_parent' => ['label' => 'Co-Parent', 'icon' => 'tabler--users', 'color' => 'amber-500'],
        'child' => ['label' => 'Child (13+)', 'icon' => 'tabler--user', 'color' => 'success'],
        'guardian' => ['label' => 'Guardian', 'icon' => 'tabler--shield', 'color' => 'info'],
        'grandparent' => ['label' => 'Grandparent', 'icon' => 'tabler--heart-handshake', 'color' => 'purple-500'],
        'relative' => ['label' => 'Relative', 'icon' => 'tabler--users-group', 'color' => 'cyan-500'],
        'caregiver' => ['label' => 'Caregiver / Nanny', 'icon' => 'tabler--nurse', 'color' => 'orange-500'],
        'advisor' => ['label' => 'Advisor (Coming Soon)', 'icon' => 'tabler--briefcase', 'color' => 'slate-500', 'disabled' => true],
        'emergency_contact' => ['label' => 'Emergency Contact', 'icon' => 'tabler--urgent', 'color' => 'error'],
        'other' => ['label' => 'Other', 'icon' => 'tabler--dots', 'color' => 'slate-400'],
    ];

    public const ROLES = [
        'owner' => [
            'label' => 'Owner',
            'description' => 'Full access to everything',
            'color' => 'error',
            'icon' => 'tabler--crown',
        ],
        'admin' => [
            'label' => 'Admin',
            'description' => 'Manage family & collaborators',
            'color' => 'warning',
            'icon' => 'tabler--settings',
        ],
        'contributor' => [
            'label' => 'Contributor',
            'description' => 'Add & edit assigned data',
            'color' => 'success',
            'icon' => 'tabler--edit',
        ],
        'viewer' => [
            'label' => 'Viewer',
            'description' => 'Read-only access',
            'color' => 'info',
            'icon' => 'tabler--eye',
        ],
        'emergency_only' => [
            'label' => 'Emergency Only',
            'description' => 'Limited emergency info',
            'color' => 'slate-500',
            'icon' => 'tabler--first-aid-kit',
        ],
    ];

    public const STATUSES = [
        'pending' => ['label' => 'Pending', 'color' => 'warning', 'icon' => 'tabler--clock'],
        'accepted' => ['label' => 'Accepted', 'color' => 'success', 'icon' => 'tabler--check'],
        'declined' => ['label' => 'Declined', 'color' => 'error', 'icon' => 'tabler--x'],
        'expired' => ['label' => 'Expired', 'color' => 'slate-400', 'icon' => 'tabler--clock-off'],
        'revoked' => ['label' => 'Revoked', 'color' => 'slate-500', 'icon' => 'tabler--ban'],
    ];

    public const PERMISSION_CATEGORIES = [
        // Basic Info
        'date_of_birth' => ['label' => 'Date of Birth', 'group' => 'Basic Info'],
        'immigration_status' => ['label' => 'Immigration Status', 'group' => 'Basic Info'],
        // Identity Documents
        'drivers_license' => ['label' => "Driver's License", 'group' => 'Documents'],
        'passport' => ['label' => 'Passport', 'group' => 'Documents'],
        'ssn' => ['label' => 'Social Security', 'group' => 'Documents'],
        'birth_certificate' => ['label' => 'Birth Certificate', 'group' => 'Documents'],
        // Medical
        'medical' => ['label' => 'Medical Records', 'group' => 'Health'],
        'emergency_contacts' => ['label' => 'Emergency Contacts', 'group' => 'Health'],
        // Education
        'school' => ['label' => 'School Info', 'group' => 'Education'],
        // Financial
        'insurance' => ['label' => 'Insurance Policies', 'group' => 'Financial'],
        'tax_returns' => ['label' => 'Tax Returns', 'group' => 'Financial'],
        'assets' => ['label' => 'Assets', 'group' => 'Financial'],
    ];

    public const PERMISSION_LEVELS = [
        'none' => ['label' => 'Hidden', 'icon' => 'tabler--eye-off', 'color' => 'slate-400'],
        'view' => ['label' => 'View Only', 'icon' => 'tabler--eye', 'color' => 'info'],
        'edit' => ['label' => 'Can Edit', 'icon' => 'tabler--edit', 'color' => 'success'],
        'full' => ['label' => 'Full Access', 'icon' => 'tabler--shield-check', 'color' => 'warning'],
    ];

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(64);
            }
            if (empty($invite->expires_at)) {
                $invite->expires_at = now()->addDays(7);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    public function familyMembers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'collaborator_invite_family_member')
            ->withPivot('permissions')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getFullNameAttribute(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }
        return $this->email;
    }

    public function getRelationshipInfoAttribute(): array
    {
        return self::RELATIONSHIP_TYPES[$this->relationship_type] ?? self::RELATIONSHIP_TYPES['other'];
    }

    public function getRoleInfoAttribute(): array
    {
        return self::ROLES[$this->role] ?? self::ROLES['viewer'];
    }

    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['pending'];
    }

    public function getInviteLinkAttribute(): string
    {
        return route('collaborator.accept', ['token' => $this->token]);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast() && $this->status === 'pending';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending' && !$this->is_expired;
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', strtolower($email));
    }

    // ==================== METHODS ====================

    public function accept(User $user): Collaborator
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_user_id' => $user->id,
        ]);

        // Create collaborator record
        $collaborator = Collaborator::create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $user->id,
            'invited_by' => $this->invited_by,
            'invite_id' => $this->id,
            'relationship_type' => $this->relationship_type,
            'role' => $this->role,
        ]);

        // Copy family member permissions
        foreach ($this->familyMembers as $member) {
            $collaborator->familyMembers()->attach($member->id, [
                'permissions' => $member->pivot->permissions,
            ]);
        }

        return $collaborator;
    }

    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    public function revoke(): void
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);
    }

    public function resend(): void
    {
        $this->update([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }
}
