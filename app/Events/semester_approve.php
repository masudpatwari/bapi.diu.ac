<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\O_SEMESTERS;

class semester_approve
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $semesters;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(O_SEMESTERS $semesters)
    {
        $this->semesters = $semesters;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
