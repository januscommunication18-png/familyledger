<?php

namespace App\Events;

use App\Models\FamilyCircle;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyCircleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FamilyCircle $familyCircle;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(FamilyCircle $familyCircle, User $user)
    {
        $this->familyCircle = $familyCircle;
        $this->user = $user;
    }
}