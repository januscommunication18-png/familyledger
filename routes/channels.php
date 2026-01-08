<?php

use App\Models\CoparentConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Co-parent conversation channel - only participants can listen
Broadcast::channel('coparent.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = CoparentConversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    return $conversation->isParticipant($user->id);
});

// User notifications channel - for global notifications
Broadcast::channel('user.notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
