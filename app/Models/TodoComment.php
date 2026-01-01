<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoComment extends Model
{
    protected $fillable = [
        'todo_item_id',
        'user_id',
        'content',
    ];

    /**
     * Get the todo item this comment belongs to.
     */
    public function todoItem(): BelongsTo
    {
        return $this->belongsTo(TodoItem::class);
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
