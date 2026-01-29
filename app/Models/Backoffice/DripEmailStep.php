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
    ];

    protected $casts = [
        'delay_days' => 'integer',
        'delay_hours' => 'integer',
        'sequence_order' => 'integer',
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
}
