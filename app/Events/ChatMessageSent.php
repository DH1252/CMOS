<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private Message $message)
    {
        $this->message->loadMissing([
            'sender.role',
            'sender.department',
            'receiver.role',
            'receiver.department',
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->message->sender_id),
            new PrivateChannel('users.'.$this->message->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'senderId' => $this->message->sender_id,
                'receiverId' => $this->message->receiver_id,
                'isRead' => (bool) $this->message->is_read,
                'createdAt' => $this->message->created_at?->toIso8601String(),
            ],
            'unreadCounts' => [
                (string) $this->message->sender_id => Message::unreadCountFor($this->message->sender_id),
                (string) $this->message->receiver_id => Message::unreadCountFor($this->message->receiver_id),
            ],
        ];
    }
}
