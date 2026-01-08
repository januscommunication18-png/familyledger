<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CoparentMessageTemplate extends Model
{
    use BelongsToTenant;

    protected $table = 'coparent_message_templates';

    protected $fillable = [
        'tenant_id',
        'category',
        'title',
        'content',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Message categories
    public const CATEGORY_GENERAL = 'General';
    public const CATEGORY_SCHEDULE = 'Schedule';
    public const CATEGORY_MEDICAL = 'Medical';
    public const CATEGORY_EXPENSE = 'Expense';
    public const CATEGORY_EMERGENCY = 'Emergency';

    public const CATEGORIES = [
        self::CATEGORY_GENERAL => ['label' => 'General', 'icon' => '💬', 'color' => '#3b82f6'],
        self::CATEGORY_SCHEDULE => ['label' => 'Schedule', 'icon' => '📅', 'color' => '#8b5cf6'],
        self::CATEGORY_MEDICAL => ['label' => 'Medical', 'icon' => '🏥', 'color' => '#ef4444'],
        self::CATEGORY_EXPENSE => ['label' => 'Expense', 'icon' => '💰', 'color' => '#22c55e'],
        self::CATEGORY_EMERGENCY => ['label' => 'Emergency', 'icon' => '🚨', 'color' => '#f97316'],
    ];

    // ==================== SCOPES ====================

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSystemTemplates($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeUserTemplates($query)
    {
        return $query->where('is_system', false);
    }

    // ==================== ACCESSORS ====================

    public function getCategoryInfoAttribute(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES[self::CATEGORY_GENERAL];
    }

    public function getCategoryIconAttribute(): string
    {
        return $this->category_info['icon'];
    }

    public function getCategoryColorAttribute(): string
    {
        return $this->category_info['color'];
    }
}
