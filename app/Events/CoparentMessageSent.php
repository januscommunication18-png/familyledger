<?php

namespace App\Events;

use App\Models\CoparentMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoparentMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CoparentMessage $message;

    /**
     * Create a new event instance.
     */
    public function __construct(CoparentMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('coparent.conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->message->load(['sender', 'attachments']);
        $catInfo = CoparentMessage::CATEGORIES[$this->message->category] ?? CoparentMessage::CATEGORIES['General'];

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'category' => $this->message->category,
            'category_icon' => $catInfo['icon'],
            'category_label' => $catInfo['label'],
            'category_color' => $catInfo['color'],
            'content' => $this->message->content,
            'created_at' => $this->message->created_at->format('M j, g:i A'),
            'created_at_iso' => $this->message->created_at->toISOString(),
            'attachments' => $this->message->attachments->map(function ($att) {
                return [
                    'id' => $att->id,
                    'original_filename' => $att->original_filename,
                    'formatted_size' => $att->formatted_size,
                    'icon' => $att->icon,
                    'download_url' => route('coparenting.messages.downloadAttachment', $att),
                ];
            }),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
