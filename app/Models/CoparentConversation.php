<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\FamilyMember;
use App\Models\Collaborator;
use App\Models\CoparentChild;

class CoparentConversation extends Model
{
    use BelongsToTenant;

    protected $table = 'coparent_conversations';

    protected $fillable = [
        'tenant_id',
        'child_id',
        'subject',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function child(): BelongsTo
    {
        return $this->belongsTo(CoparentChild::class, 'child_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CoparentMessage::class, 'conversation_id');
    }

    // ==================== SCOPES ====================

    public function scopeForUser($query, int $userId)
    {
        // Get the user to check their tenant
        $user = User::find($userId);

        // Get own children (family_member_ids) with co-parenting enabled
        $ownChildrenIds = FamilyMember::where('tenant_id', $user->tenant_id)
            ->where('co_parenting_enabled', true)
            ->pluck('id')
            ->toArray();

        // Get collaborator IDs for this user (co-parent access)
        $collaboratorIds = Collaborator::where('user_id', $userId)
            ->where('coparenting_enabled', true)
            ->pluck('id')
            ->toArray();

        // Get family_member_ids the user has co-parent access to
        $sharedChildrenIds = CoparentChild::whereIn('collaborator_id', $collaboratorIds)
            ->pluck('family_member_id')
            ->toArray();

        // Merge all family_member IDs the user has access to
        $allFamilyMemberIds = array_unique(array_merge($ownChildrenIds, $sharedChildrenIds));

        // Get ALL CoparentChild IDs for these family members
        $allCoparentChildIds = CoparentChild::whereIn('family_member_id', $allFamilyMemberIds)
            ->pluck('id')
            ->toArray();

        return $query->whereIn('child_id', $allCoparentChildIds);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_message_at');
    }

    // ==================== METHODS ====================

    /**
     * Get participants of this conversation.
     * Includes ALL co-parents who have access to the same child (by family_member_id).
     */
    public function getParticipants()
    {
        $participants = collect();

        if (!$this->child || !$this->child->familyMember) {
            return $participants;
        }

        $familyMemberId = $this->child->family_member_id;

        // Get ALL co-parents who have access to this child (by family_member_id)
        $allCoparentChildren = CoparentChild::where('family_member_id', $familyMemberId)
            ->with('collaborator.user')
            ->get();

        foreach ($allCoparentChildren as $coparentChild) {
            if ($coparentChild->collaborator && $coparentChild->collaborator->user) {
                $participants->push($coparentChild->collaborator->user);
            }
        }

        // Get the primary parent (family circle creator)
        if ($this->child->familyMember->familyCircle) {
            $participants->push($this->child->familyMember->familyCircle->creator);
        }

        return $participants->unique('id');
    }

    /**
     * Get unread count for a specific user.
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count();
    }

    /**
     * Get the last message in the conversation.
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()->orderByDesc('created_at')->first();
    }

    /**
     * Check if user is a participant in this conversation.
     */
    public function isParticipant(int $userId): bool
    {
        return $this->getParticipants()->contains('id', $userId);
    }

    /**
     * Update the last_message_at timestamp.
     */
    public function touchLastMessage(): void
    {
        $this->update(['last_message_at' => now()]);
    }
}
