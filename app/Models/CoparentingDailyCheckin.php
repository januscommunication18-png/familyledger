<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CoparentingDailyCheckin extends Model
{
    use BelongsToTenant;

    protected $table = 'coparenting_daily_checkins';

    protected $fillable = [
        'tenant_id',
        'checked_by',
        'family_member_id',
        'checkin_date',
        'parent_role',
        'mood',
        'notes',
    ];

    protected $casts = [
        'checkin_date' => 'date',
    ];

    // Available moods with emojis
    public const MOODS = [
        'happy' => ['emoji' => '😊', 'label' => 'Happy'],
        'excited' => ['emoji' => '🤩', 'label' => 'Excited'],
        'calm' => ['emoji' => '😌', 'label' => 'Calm'],
        'tired' => ['emoji' => '😴', 'label' => 'Tired'],
        'sad' => ['emoji' => '😢', 'label' => 'Sad'],
        'angry' => ['emoji' => '😠', 'label' => 'Angry'],
        'anxious' => ['emoji' => '😰', 'label' => 'Anxious'],
        'sick' => ['emoji' => '🤒', 'label' => 'Sick'],
        'playful' => ['emoji' => '🤪', 'label' => 'Playful'],
        'loved' => ['emoji' => '🥰', 'label' => 'Loved'],
    ];

    // ==================== RELATIONSHIPS ====================

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'family_member_id');
    }

    // ==================== ACCESSORS ====================

    public function getMoodEmojiAttribute(): string
    {
        return self::MOODS[$this->mood]['emoji'] ?? '😊';
    }

    public function getMoodLabelAttribute(): string
    {
        return self::MOODS[$this->mood]['label'] ?? ucfirst($this->mood);
    }

    // ==================== SCOPES ====================

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('checkin_date', $date);
    }

    public function scopeForChild($query, $childId)
    {
        return $query->where('family_member_id', $childId);
    }

    public function scopeForParent($query, $parentRole)
    {
        return $query->where('parent_role', $parentRole);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('checkin_date', '>=', now()->subDays($days));
    }

    // ==================== STATIC METHODS ====================

    /**
     * Get today's check-in for a child if exists.
     */
    public static function getTodayCheckin(string $tenantId, int $childId): ?self
    {
        return self::where('tenant_id', $tenantId)
            ->where('family_member_id', $childId)
            ->whereDate('checkin_date', now()->toDateString())
            ->first();
    }

    /**
     * Get check-in history for a child.
     */
    public static function getHistory(string $tenantId, int $childId, int $days = 30): Collection
    {
        return self::where('tenant_id', $tenantId)
            ->where('family_member_id', $childId)
            ->where('checkin_date', '>=', now()->subDays($days))
            ->orderBy('checkin_date', 'desc')
            ->get();
    }

    /**
     * Determine which parent has custody today based on active schedule.
     */
    public static function getCustodyParentForDate(string $tenantId, Carbon $date, ?int $childId = null): ?string
    {
        $query = CoparentingSchedule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('begins_at', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $date);
            });

        // Filter by child if provided
        if ($childId) {
            $query->whereHas('children', fn($q) => $q->where('family_member_id', $childId));
        }

        $schedule = $query->first();

        if (!$schedule) {
            return null;
        }

        // Generate events for just this day
        $events = $schedule->generateEventsForRange($date->copy(), $date->copy());

        // Compare dates only (ignore time) since schedule events are date-based
        $checkDate = $date->copy()->startOfDay();

        foreach ($events as $event) {
            $eventStart = Carbon::parse($event['start'])->startOfDay();
            $eventEnd = Carbon::parse($event['end'])->endOfDay();

            if ($checkDate->between($eventStart, $eventEnd)) {
                return $event['parent'];
            }
        }

        return null;
    }

    /**
     * Check if user can do check-in today (is the custody parent).
     */
    public static function canUserCheckinToday(User $user, string $tenantId, ?int $childId = null): bool
    {
        // Get user's parent role from collaborator
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('coparenting_enabled', true)
            ->first();

        $parentRole = null;

        // If user is the tenant owner
        if (!$collaborator && $user->tenant_id === $tenantId) {
            // Owner's role is the OPPOSITE of the co-parent's role
            // Find the co-parent collaborator to determine owner's role
            $coparentCollaborator = Collaborator::where('tenant_id', $tenantId)
                ->where('coparenting_enabled', true)
                ->whereNotNull('parent_role')
                ->first();

            if ($coparentCollaborator) {
                // Owner is the opposite role of the collaborator
                $parentRole = $coparentCollaborator->parent_role === 'mother' ? 'father' : 'mother';
            } else {
                // No co-parent set up yet, allow check-in
                return true;
            }
        } elseif ($collaborator) {
            $parentRole = $collaborator->parent_role;
        } else {
            return false;
        }

        // Get custody parent for today based on child's schedule
        $custodyParent = self::getCustodyParentForDate($tenantId, now(), $childId);

        // If no schedule/custody info set, allow check-in
        if (!$custodyParent) {
            return true;
        }

        return $parentRole === $custodyParent;
    }

    /**
     * Get co-parented children for a user.
     */
    public static function getCoparentedChildren(User $user, string $tenantId): Collection
    {
        // Check if user is collaborator with co-parenting enabled
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('coparenting_enabled', true)
            ->first();

        if ($collaborator) {
            return $collaborator->coparentChildren;
        }

        // If user is tenant owner, get children with co_parenting_enabled
        if ($user->tenant_id === $tenantId) {
            return FamilyMember::where('tenant_id', $tenantId)
                ->where('co_parenting_enabled', true)
                ->where('is_minor', true)
                ->get();
        }

        return collect();
    }
}
