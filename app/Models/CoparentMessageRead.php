<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoparentMessageRead extends Model
{
    public $timestamps = false;

    protected $table = 'coparent_message_reads';

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
        'ip_address',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function message(): BelongsTo
    {
        return $this->belongsTo(CoparentMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== ACCESSORS ====================

    public function getFormattedReadAtAttribute(): string
    {
        return $this->read_at->format('M j, Y \a\t g:i A');
    }
}
