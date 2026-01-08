<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoparentMessageEdit extends Model
{
    use BelongsToTenant;

    protected $table = 'coparent_message_edits';

    protected $fillable = [
        'tenant_id',
        'message_id',
        'previous_content',
        'new_content',
        'ip_address',
    ];

    protected $casts = [
        'previous_content' => 'encrypted',
        'new_content' => 'encrypted',
    ];

    // ==================== RELATIONSHIPS ====================

    public function message(): BelongsTo
    {
        return $this->belongsTo(CoparentMessage::class, 'message_id');
    }

    // ==================== ACCESSORS ====================

    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M j, Y \a\t g:i A');
    }

    /**
     * Get the editor (same as message sender).
     */
    public function getEditorAttribute()
    {
        return $this->message->sender;
    }
}
