<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class JournalTag extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'color',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    // ==================== CONSTANTS ====================

    public const COLORS = [
        'slate' => '#64748b',
        'red' => '#ef4444',
        'orange' => '#f97316',
        'amber' => '#f59e0b',
        'yellow' => '#eab308',
        'lime' => '#84cc16',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'cyan' => '#06b6d4',
        'sky' => '#0ea5e9',
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'violet' => '#8b5cf6',
        'purple' => '#a855f7',
        'fuchsia' => '#d946ef',
        'pink' => '#ec4899',
        'rose' => '#f43f5e',
    ];

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(JournalEntry::class, 'journal_entry_tag')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getColorHexAttribute(): string
    {
        return self::COLORS[$this->color] ?? self::COLORS['slate'];
    }

    // ==================== SCOPES ====================

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // ==================== METHODS ====================

    public static function findOrCreateByName(string $name, string $tenantId, ?string $color = null): self
    {
        $slug = Str::slug($name);

        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => $slug],
            ['name' => $name, 'color' => $color ?? array_rand(self::COLORS)]
        );
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }
}
