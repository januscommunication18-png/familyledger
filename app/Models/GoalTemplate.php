<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'title',
        'description',
        'emoji',
        'category',
        'audience',
        'goal_type',
        'habit_frequency',
        'milestone_target',
        'milestone_unit',
        'suggested_rewards',
        'suggested_reward_type',
        'suggested_check_in_frequency',
        'tenant_id',
        'is_system',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'suggested_rewards' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==================== AUDIENCES ====================

    public const AUDIENCES = [
        'kids' => [
            'label' => 'Kids',
            'emoji' => '👶',
            'description' => 'Ages 4-11',
        ],
        'teens' => [
            'label' => 'Teens',
            'emoji' => '🧑',
            'description' => 'Ages 12-17',
        ],
        'family' => [
            'label' => 'Family',
            'emoji' => '👨‍👩‍👧',
            'description' => 'For the whole family',
        ],
        'parents' => [
            'label' => 'Parents',
            'emoji' => '👨‍👩',
            'description' => 'For parents only',
        ],
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the tenant that owns this custom template.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get goals created from this template.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'template_id');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get audience details.
     */
    public function getAudienceDetailsAttribute(): array
    {
        return self::AUDIENCES[$this->audience] ?? self::AUDIENCES['family'];
    }

    /**
     * Get audience emoji.
     */
    public function getAudienceEmojiAttribute(): string
    {
        return $this->audience_details['emoji'] ?? '👨‍👩‍👧';
    }

    /**
     * Get category details from Goal model.
     */
    public function getCategoryDetailsAttribute(): array
    {
        return Goal::CATEGORIES[$this->category] ?? Goal::CATEGORIES['personal_growth'];
    }

    /**
     * Get category emoji.
     */
    public function getCategoryEmojiAttribute(): string
    {
        return $this->category_details['emoji'] ?? '🎯';
    }

    /**
     * Get goal type details from Goal model.
     */
    public function getGoalTypeDetailsAttribute(): array
    {
        return Goal::GOAL_TYPES[$this->goal_type] ?? Goal::GOAL_TYPES['one_time'];
    }

    /**
     * Get goal type emoji.
     */
    public function getGoalTypeEmojiAttribute(): string
    {
        return $this->goal_type_details['emoji'] ?? '✅';
    }

    /**
     * Get display title with emoji.
     */
    public function getDisplayTitleAttribute(): string
    {
        return ($this->emoji ?? $this->category_emoji) . ' ' . $this->title;
    }

    // ==================== SCOPES ====================

    /**
     * Scope for system templates.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for custom templates (tenant-specific).
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific audience.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where('audience', $audience);
    }

    /**
     * Scope for a specific category.
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get templates available to a tenant (system + their custom).
     */
    public function scopeAvailableTo($query, string $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->where('is_system', true)
                ->orWhere('tenant_id', $tenantId);
        });
    }

    // ==================== METHODS ====================

    /**
     * Create a goal from this template.
     */
    public function createGoal(int $tenantId, array $overrides = []): Goal
    {
        $defaults = [
            'tenant_id' => $tenantId,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'goal_type' => $this->goal_type,
            'habit_frequency' => $this->habit_frequency,
            'milestone_target' => $this->milestone_target,
            'milestone_unit' => $this->milestone_unit,
            'rewards_enabled' => $this->suggested_rewards,
            'reward_type' => $this->suggested_reward_type,
            'check_in_frequency' => $this->suggested_check_in_frequency,
            'template_id' => $this->id,
            'status' => 'active',
            'created_by' => auth()->id(),
        ];

        return Goal::create(array_merge($defaults, $overrides));
    }
}
