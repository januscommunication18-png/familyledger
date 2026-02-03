<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'device_id',
        'device_name',
        'entity_type',
        'entity_id',
        'operation',
        'changes',
        'synced_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
