<?php

namespace App\Models\Backoffice;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripCampaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'status',
        'delay_days',
        'delay_hours',
        'created_by',
    ];

    protected $casts = [
        'delay_days' => 'integer',
        'delay_hours' => 'integer',
    ];

    const TRIGGER_SIGNUP = 'signup';
    const TRIGGER_TRIAL_EXPIRING = 'trial_expiring';
    const TRIGGER_CUSTOM = 'custom';

    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_DRAFT = 'draft';

    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_SIGNUP => 'User Signup',
            self::TRIGGER_TRIAL_EXPIRING => 'Trial Expiring',
            self::TRIGGER_CUSTOM => 'Custom / Manual',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DripEmailStep::class)->orderBy('sequence_order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DripEmailLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePaused($query)
    {
        return $query->where('status', self::STATUS_PAUSED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeByTrigger($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    public function getFirstStep(): ?DripEmailStep
    {
        return $this->steps()->orderBy('sequence_order')->first();
    }

    public function getNextStep(int $currentOrder): ?DripEmailStep
    {
        return $this->steps()
            ->where('sequence_order', '>', $currentOrder)
            ->orderBy('sequence_order')
            ->first();
    }

    public function getInitialDelayInMinutes(): int
    {
        return ($this->delay_days * 24 * 60) + ($this->delay_hours * 60);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function getTriggerLabel(): string
    {
        return self::getTriggerTypes()[$this->trigger_type] ?? $this->trigger_type;
    }

    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
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

    public function getOpenRate(): float
    {
        $sent = $this->getSentCount();
        if ($sent === 0) return 0;
        return round(($this->getOpenedCount() / $sent) * 100, 1);
    }

    public function getClickRate(): float
    {
        $sent = $this->getSentCount();
        if ($sent === 0) return 0;
        return round(($this->getClickedCount() / $sent) * 100, 1);
    }
}
