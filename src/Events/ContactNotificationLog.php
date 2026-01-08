<?php

namespace LiviuVoica\LbContact\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactNotificationLog
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array<string, mixed> */
    public array $payload;

    /**
     * Create a new event instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
