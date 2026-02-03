<?php

namespace App\Events;

use App\Models\PendingCoparentEdit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PendingEditCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $ownerId;
    public string $requesterName;
    public string $childName;
    public string $fieldLabel;
    public string $editableTypeLabel;
    public int $pendingEditId;
    public int $pendingCount;

    /**
     * Create a new event instance.
     */
    public function __construct(PendingCoparentEdit $pendingEdit, int $ownerId)
    {
        $this->ownerId = $ownerId;
        $this->requesterName = $pendingEdit->requester->name ?? 'Unknown';
        $this->childName = $pendingEdit->familyMember->full_name ?? 'Unknown';
        $this->fieldLabel = $pendingEdit->field_label;
        $this->editableTypeLabel = $pendingEdit->editable_type_label;
        $this->pendingEditId = $pendingEdit->id;

        // Get total pending count for badge update
        $this->pendingCount = PendingCoparentEdit::where('tenant_id', $pendingEdit->tenant_id)
            ->pending()
            ->count();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.notifications.' . $this->ownerId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'coparent.edit.requested';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'requester_name' => $this->requesterName,
            'child_name' => $this->childName,
            'field_label' => $this->fieldLabel,
            'editable_type_label' => $this->editableTypeLabel,
            'pending_edit_id' => $this->pendingEditId,
            'pending_count' => $this->pendingCount,
            'message' => "{$this->requesterName} requested to edit {$this->fieldLabel} for {$this->childName}",
        ];
    }
}
