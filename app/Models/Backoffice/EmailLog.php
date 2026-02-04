<?php

namespace App\Models\Backoffice;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    protected $fillable = [
        'mailable_class',
        'mailable_type',
        'to_email',
        'to_name',
        'from_email',
        'from_name',
        'subject',
        'body_html',
        'body_text',
        'tenant_id',
        'user_id',
        'status',
        'error_message',
        'sent_at',
        'opened_at',
        'clicked_at',
        'tracking_token',
        'message_id',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'metadata' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_OPENED = 'opened';
    const STATUS_CLICKED = 'clicked';
    const STATUS_BOUNCED = 'bounced';

    /**
     * Map of mailable classes to friendly names.
     */
    public static array $mailableTypes = [
        'App\Mail\CollaboratorInviteMail' => 'Collaborator Invite',
        'App\Mail\CollaboratorReminderMail' => 'Collaborator Reminder',
        'App\Mail\CollaboratorWelcomeMail' => 'Collaborator Welcome',
        'App\Mail\CoparentInviteMail' => 'Co-Parent Invite',
        'App\Mail\BackofficeAccessCode' => 'Backoffice Access Code',
        'App\Mail\BackofficeSecurityCode' => 'Backoffice Security Code',
        'App\Mail\DripEmail' => 'Drip Campaign',
        'App\Mail\PaymentSuccessEmail' => 'Payment Success',
        'App\Mail\RecoveryCodeSetMail' => 'Recovery Code Set',
        'App\Mail\SubscriptionReminderEmail' => 'Subscription Reminder',
        'App\Mail\DataAccessRequestMail' => 'Data Access Request',
        'App\Mail\DailyTaskReminderMail' => 'Daily Task Reminder',
        'App\Mail\AdminDailyDigestMail' => 'Admin Daily Digest',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_OPENED => 'Opened',
            self::STATUS_CLICKED => 'Clicked',
            self::STATUS_BOUNCED => 'Bounced',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->tracking_token)) {
                $log->tracking_token = Str::random(64);
            }
            // Auto-set mailable_type from class name
            if (empty($log->mailable_type) && !empty($log->mailable_class)) {
                $log->mailable_type = self::$mailableTypes[$log->mailable_class]
                    ?? class_basename($log->mailable_class);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
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

    public function scopeOfType($query, string $type)
    {
        return $query->where('mailable_type', $type);
    }

    // Status methods
    public function markAsSent(?string $messageId = null): void
    {
        $data = [
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ];
        if ($messageId) {
            $data['message_id'] = $messageId;
        }
        $this->update($data);
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

        if (in_array($this->status, [self::STATUS_SENT, self::STATUS_OPENED])) {
            $updates['status'] = self::STATUS_CLICKED;
        }

        if (is_null($this->opened_at)) {
            $updates['opened_at'] = now();
        }

        $this->update($updates);
    }

    // Helpers
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
            self::STATUS_BOUNCED => 'orange',
            default => 'gray',
        };
    }

    public function getMailableTypeLabel(): string
    {
        return $this->mailable_type ?? 'Unknown';
    }

    /**
     * Get unique mailable types for filtering.
     */
    public static function getUniqueTypes(): array
    {
        return self::distinct()
            ->whereNotNull('mailable_type')
            ->pluck('mailable_type')
            ->sort()
            ->values()
            ->toArray();
    }
}
