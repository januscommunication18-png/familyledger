<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConflictResolution extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'entity_type',
        'entity_id',
        'server_data',
        'client_data',
        'resolution',
        'resolved_data',
        'resolved_by',
    ];

    protected $casts = [
        'server_data' => 'array',
        'client_data' => 'array',
        'resolved_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPending(): bool
    {
        return $this->resolution === 'pending';
    }
}
