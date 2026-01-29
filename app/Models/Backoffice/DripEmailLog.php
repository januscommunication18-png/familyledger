<?php

namespace App\Models\Backoffice;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DripEmailLog extends Model
{
    protected $fillable = [
        'drip_campaign_id',
        'drip_email_step_id',
        'tenant_id',
        'user_id',
        'email',
        'sent_at',
        'status',
        'error_message',
        'opened_at',
        'clicked_at',
        'tracking_token',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_OPENED = 'opened';
    const STATUS_CLICKED = 'clicked';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_OPENED => 'Opened',
            self::STATUS_CLICKED => 'Clicked',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->tracking_token)) {
                $log->tracking_token = Str::random(64);
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class, 'drip_campaign_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(DripEmailStep::class, 'drip_email_step_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeOpened($query)
    {
        return $query->where('status', self::STATUS_OPENED);
    }

    public function scopeClicked($query)
    {
        return $query->where('status', self::STATUS_CLICKED);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $message,
        ]);
    }

    public function markAsOpened(): void
    {
        if ($this->status === self::STATUS_SENT) {
            $this->update([
                'status' => self::STATUS_OPENED,
                'opened_at' => now(),
            ]);
        } elseif ($this->status !== self::STATUS_CLICKED && is_null($this->opened_at)) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function markAsClicked(): void
    {
        $updates = ['clicked_at' => now()];

        if ($this->status === self::STATUS_SENT || $this->status === self::STATUS_OPENED) {
            $updates['status'] = self::STATUS_CLICKED;
        }

        if (is_null($this->opened_at)) {
            $updates['opened_at'] = now();
        }

        $this->update($updates);
    }

    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_SENT => 'blue',
            self::STATUS_FAILED => 'red',
            self::STATUS_OPENED => 'green',
            self::STATUS_CLICKED => 'purple',
            default => 'gray',
        };
    }
}
