<?php

namespace App\Events;

use App\Models\CoparentMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoparentMessageNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $recipientId;
    public string $senderName;
    public string $childName;
    public string $messagePreview;
    public int $conversationId;
    public string $category;

    /**
     * Create a new event instance.
     */
    public function __construct(CoparentMessage $message, int $recipientId)
    {
        $this->recipientId = $recipientId;
        $this->senderName = $message->sender->name;
        $this->childName = $message->conversation->child->familyMember->full_name ?? 'your child';
        $this->messagePreview = \Illuminate\Support\Str::limit($message->content, 50);
        $this->conversationId = $message->conversation_id;
        $this->category = $message->category;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.notifications.' . $this->recipientId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'coparent.message.received';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'sender_name' => $this->senderName,
            'child_name' => $this->childName,
            'message_preview' => $this->messagePreview,
            'conversation_id' => $this->conversationId,
            'category' => $this->category,
        ];
    }
}
