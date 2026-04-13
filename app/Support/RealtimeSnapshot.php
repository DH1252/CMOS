<?php

namespace App\Support;

use App\Models\Message;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RealtimeSnapshot
{
    /**
     * @return array<string, array<string, int|string>>
     */
    public function forUser(User $user): array
    {
        return [
            'messages' => [
                'signature' => $this->messagesSignature($user),
                'unreadCount' => Message::unreadCountFor($user->id),
            ],
            'notifications' => [
                'signature' => $this->notificationsSignature($user),
                'unreadCount' => Notification::forUser($user->id)->unread()->count(),
            ],
            'announcements' => [
                'signature' => $this->announcementsSignature(),
            ],
            'tasks' => [
                'signature' => $this->tasksSignature(),
            ],
        ];
    }

    private function messagesSignature(User $user): string
    {
        $query = Message::query()->where(function ($builder) use ($user): void {
            $builder->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        });

        return $this->signature([
            'count' => (clone $query)->count(),
            'latest_created_at' => $this->latestValue((clone $query)->max('created_at')),
            'unread_count' => Message::unreadCountFor($user->id),
        ]);
    }

    private function notificationsSignature(User $user): string
    {
        $query = Notification::forUser($user->id);

        return $this->signature([
            'count' => (clone $query)->count(),
            'latest_updated_at' => $this->latestValue((clone $query)->max('updated_at')),
            'unread_count' => Notification::forUser($user->id)->unread()->count(),
        ]);
    }

    private function announcementsSignature(): string
    {
        $latestChange = collect([
            $this->latestValue(DB::table('announcements')->max('updated_at')),
            $this->latestValue(DB::table('announcement_comments')->max('updated_at')),
            $this->latestValue(DB::table('announcement_reactions')->max('updated_at')),
            $this->latestValue(DB::table('poll_options')->max('updated_at')),
            $this->latestValue(DB::table('poll_votes')->max('updated_at')),
        ])->filter()->max();

        return $this->signature([
            'announcements_count' => DB::table('announcements')->count(),
            'comments_count' => DB::table('announcement_comments')->count(),
            'reactions_count' => DB::table('announcement_reactions')->count(),
            'poll_votes_count' => DB::table('poll_votes')->count(),
            'latest_change' => $latestChange,
        ]);
    }

    private function tasksSignature(): string
    {
        return $this->signature([
            'count' => Task::query()->count(),
            'latest_updated_at' => $this->latestValue(Task::query()->max('updated_at')),
        ]);
    }

    /**
     * @param  array<string, int|string|null>  $payload
     */
    private function signature(array $payload): string
    {
        return sha1(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]');
    }

    private function latestValue(mixed $value): ?string
    {
        return $value ? (string) $value : null;
    }
}
