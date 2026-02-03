<?php

namespace App\Models\Backoffice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripEmailStep extends Model
{
    protected $fillable = [
        'drip_campaign_id',
        'subject',
        'body',
        'delay_days',
        'delay_hours',
        'sequence_order',
        'condition_type',
        'trigger_type',
        'trigger_event',
        'skip_if_event_sent',
    ];

    // Condition types - checks before sending
    const CONDITION_NONE = 'none';
    const CONDITION_HAS_FAMILY_CIRCLE = 'has_family_circle';
    const CONDITION_NO_FAMILY_CIRCLE = 'no_family_circle';
    const CONDITION_HAS_LOGGED_IN = 'has_logged_in';
    const CONDITION_HAS_FAMILY_MEMBER = 'has_family_member';
    const CONDITION_INACTIVE_5_DAYS = 'inactive_5_days';
    const CONDITION_FREE_PLAN = 'free_plan';
    const CONDITION_INACTIVE_14_DAYS = 'inactive_14_days';

    // Trigger types
    const TRIGGER_TIME_BASED = 'time_based';
    const TRIGGER_EVENT_BASED = 'event_based';

    // Event names
    const EVENT_FAMILY_CIRCLE_CREATED = 'family_circle_created';
    const EVENT_FAMILY_MEMBER_ADDED = 'family_member_added';
    const EVENT_DOCUMENT_UPLOADED = 'document_uploaded';
    const EVENT_LIMIT_REACHED = 'limit_reached';
    const EVENT_CHILD_ADDED = 'child_added';
    const EVENT_PET_ADDED = 'pet_added';
    const EVENT_HOME_ADDED = 'home_added';
    const EVENT_COPARENT_ADDED = 'coparent_added';

    public static function getConditionTypes(): array
    {
        return [
            self::CONDITION_NONE => 'No Condition',
            self::CONDITION_HAS_FAMILY_CIRCLE => 'Has Family Circle',
            self::CONDITION_NO_FAMILY_CIRCLE => 'No Family Circle',
            self::CONDITION_HAS_LOGGED_IN => 'Has Logged In',
            self::CONDITION_HAS_FAMILY_MEMBER => 'Has Family Member',
            self::CONDITION_INACTIVE_5_DAYS => 'Inactive for 5+ Days',
            self::CONDITION_FREE_PLAN => 'Free Plan User',
            self::CONDITION_INACTIVE_14_DAYS => 'Inactive for 14+ Days',
        ];
    }

    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_TIME_BASED => 'Time Based (uses delay)',
            self::TRIGGER_EVENT_BASED => 'Event Based (triggered by action)',
        ];
    }

    public static function getTriggerEvents(): array
    {
        return [
            self::EVENT_FAMILY_CIRCLE_CREATED => 'Family Circle Created',
            self::EVENT_FAMILY_MEMBER_ADDED => 'Family Member Added',
            self::EVENT_DOCUMENT_UPLOADED => 'Document Uploaded',
            self::EVENT_LIMIT_REACHED => 'Plan Limit Reached',
            self::EVENT_CHILD_ADDED => 'Child Added',
            self::EVENT_PET_ADDED => 'Pet Added',
            self::EVENT_HOME_ADDED => 'Home Added',
            self::EVENT_COPARENT_ADDED => 'Co-Parent Added',
        ];
    }

    protected $casts = [
        'delay_days' => 'integer',
        'delay_hours' => 'integer',
        'sequence_order' => 'integer',
        'skip_if_event_sent' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class, 'drip_campaign_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DripEmailLog::class, 'drip_email_step_id');
    }

    public function getDelayInMinutes(): int
    {
        return ($this->delay_days * 24 * 60) + ($this->delay_hours * 60);
    }

    public function getFormattedDelay(): string
    {
        $parts = [];

        if ($this->delay_days > 0) {
            $parts[] = $this->delay_days . ' ' . ($this->delay_days === 1 ? 'day' : 'days');
        }

        if ($this->delay_hours > 0) {
            $parts[] = $this->delay_hours . ' ' . ($this->delay_hours === 1 ? 'hour' : 'hours');
        }

        return empty($parts) ? 'Immediately' : implode(', ', $parts);
    }

    public function getSentCount(): int
    {
        return $this->logs()->where('status', DripEmailLog::STATUS_SENT)->count();
    }

    public function getOpenedCount(): int
    {
        return $this->logs()->where('status', DripEmailLog::STATUS_OPENED)->count();
    }

    public function getClickedCount(): int
    {
        return $this->logs()->where('status', DripEmailLog::STATUS_CLICKED)->count();
    }

    public function isTimeBased(): bool
    {
        return $this->trigger_type === self::TRIGGER_TIME_BASED;
    }

    public function isEventBased(): bool
    {
        return $this->trigger_type === self::TRIGGER_EVENT_BASED;
    }

    public function hasCondition(): bool
    {
        return $this->condition_type !== self::CONDITION_NONE;
    }

    public function getConditionLabel(): string
    {
        return self::getConditionTypes()[$this->condition_type] ?? $this->condition_type;
    }

    public function getTriggerTypeLabel(): string
    {
        return self::getTriggerTypes()[$this->trigger_type] ?? $this->trigger_type;
    }

    public function getTriggerEventLabel(): string
    {
        if (!$this->trigger_event) {
            return 'N/A';
        }
        return self::getTriggerEvents()[$this->trigger_event] ?? $this->trigger_event;
    }

    /**
     * Check if this step's condition is met for a given user/tenant
     */
    public function checkCondition(?string $tenantId): bool
    {
        if ($this->condition_type === self::CONDITION_NONE) {
            return true;
        }

        if (!$tenantId) {
            return false;
        }

        return match ($this->condition_type) {
            self::CONDITION_HAS_FAMILY_CIRCLE => \App\Models\FamilyCircle::where('tenant_id', $tenantId)->exists(),
            self::CONDITION_NO_FAMILY_CIRCLE => !\App\Models\FamilyCircle::where('tenant_id', $tenantId)->exists(),
            self::CONDITION_HAS_FAMILY_MEMBER => \App\Models\FamilyMember::where('tenant_id', $tenantId)->exists(),
            self::CONDITION_HAS_LOGGED_IN => \App\Models\User::where('tenant_id', $tenantId)
                ->whereNotNull('last_login_at')
                ->exists(),
            self::CONDITION_INACTIVE_5_DAYS => \App\Models\User::where('tenant_id', $tenantId)
                ->where(function ($query) {
                    $query->whereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(5));
                })
                ->exists(),
            self::CONDITION_FREE_PLAN => \App\Models\Tenant::where('id', $tenantId)
                ->where(function ($query) {
                    $query->whereNull('package_plan_id')
                        ->orWhereHas('packagePlan', function ($q) {
                            $q->where('price', 0);
                        });
                })
                ->exists(),
            self::CONDITION_INACTIVE_14_DAYS => \App\Models\User::where('tenant_id', $tenantId)
                ->where(function ($query) {
                    $query->whereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(14));
                })
                ->exists(),
            default => true,
        };
    }
}
