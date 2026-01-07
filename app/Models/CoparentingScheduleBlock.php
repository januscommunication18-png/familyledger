<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoparentingScheduleBlock extends Model
{
    protected $fillable = [
        'schedule_id',
        'parent_role',
        'starts_at',
        'ends_at',
        'is_recurring',
        'recurrence_pattern',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_recurring' => 'boolean',
        'metadata' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CoparentingSchedule::class, 'schedule_id');
    }

    // ==================== ACCESSORS ====================

    public function getParentLabelAttribute(): string
    {
        return ucfirst($this->parent_role);
    }

    public function getDurationInDaysAttribute(): int
    {
        return $this->starts_at->diffInDays($this->ends_at) + 1;
    }
}
