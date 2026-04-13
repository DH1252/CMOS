<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeChannelsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, string>  $channels
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private array $channels,
        private string $scope = 'organization',
        private ?int $userId = null,
        private array $snapshot = [],
        private array $context = [],
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->scope === 'user' && $this->userId) {
            return [new PrivateChannel('users.'.$this->userId)];
        }

        return [
            new PrivateChannel('organization'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'realtime.channels.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'changed' => $this->channels,
            'scope' => $this->scope,
            'userId' => $this->userId,
            'snapshot' => $this->snapshot,
            'context' => $this->context,
        ];
    }
}
