<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodoList extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Available colors for lists.
     */
    public const COLORS = [
        'violet' => 'Violet',
        'indigo' => 'Indigo',
        'blue' => 'Blue',
        'sky' => 'Sky',
        'teal' => 'Teal',
        'emerald' => 'Emerald',
        'amber' => 'Amber',
        'orange' => 'Orange',
        'rose' => 'Rose',
        'pink' => 'Pink',
    ];

    /**
     * Get the items for this list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TodoItem::class);
    }

    /**
     * Get the tenant that owns this list.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get pending items count.
     */
    public function getPendingCountAttribute(): int
    {
        return $this->items()->where('status', 'pending')->count();
    }

    /**
     * Get completed items count.
     */
    public function getCompletedCountAttribute(): int
    {
        return $this->items()->where('status', 'completed')->count();
    }

    /**
     * Get color class for display.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->color) {
            'violet' => 'bg-violet-500',
            'indigo' => 'bg-indigo-500',
            'blue' => 'bg-blue-500',
            'sky' => 'bg-sky-500',
            'teal' => 'bg-teal-500',
            'emerald' => 'bg-emerald-500',
            'amber' => 'bg-amber-500',
            'orange' => 'bg-orange-500',
            'rose' => 'bg-rose-500',
            'pink' => 'bg-pink-500',
            default => 'bg-violet-500',
        };
    }
}
