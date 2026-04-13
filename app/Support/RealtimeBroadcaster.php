<?php

namespace App\Support;

use App\Events\ChatMessageSent;
use App\Events\RealtimeChannelsUpdated;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class RealtimeBroadcaster
{
    public function __construct(private RealtimeSnapshot $realtimeSnapshot) {}

    public function chatMessage(Message $message): void
    {
        Log::channel('reverb')->info('Broadcasting chat message over Reverb', [
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'content_length' => mb_strlen((string) $message->content),
        ]);

        $this->broadcast(new ChatMessageSent($message));

        Log::channel('reverb')->info('Broadcasted chat message over Reverb', [
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
        ]);
    }

    public function user(User|int $user, array $channels, array $context = []): void
    {
        $resolvedUser = $user instanceof User ? $user : User::query()->find($user);

        if (! $resolvedUser) {
            return;
        }

        $this->broadcast(new RealtimeChannelsUpdated(
            channels: $channels,
            scope: 'user',
            userId: $resolvedUser->id,
            snapshot: $this->realtimeSnapshot->forUser($resolvedUser),
            context: $context,
        ));
    }

    public function organization(array $channels, array $context = []): void
    {
        $this->broadcast(new RealtimeChannelsUpdated(
            channels: $channels,
            scope: 'organization',
            context: $context,
        ));
    }

    private function broadcast(object $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $exception) {
            Log::channel('reverb')->error('Reverb broadcast failed', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);

            report($exception);
        }
    }
}
