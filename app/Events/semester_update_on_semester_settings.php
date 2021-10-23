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

class semester_update_on_semester_settings
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $semester;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(O_SEMESTERS $semester)
    {
        $this->semester = $semester;
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
