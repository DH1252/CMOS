<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Support\RealtimeBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all users except current
        $users = User::where('id', '!=', $user->id)
            ->where('status', 'active')
            ->with(['role', 'department'])
            ->orderBy('name')
            ->get();

        // Get conversations
        $conversations = Message::getConversations($user->id);

        return \Inertia\Inertia::render(
            'pages/MessagesPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $initialUserId = request()->integer('user') ?: null;

                $props = [
                    'title' => 'Pesan Internal',
                    'description' => 'Percakapan internal antar pengurus.',
                    'csrfToken' => csrf_token(),
                    'initialUserId' => $initialUserId,
                    'contacts' => $users->map(fn ($chatUser) => [
                        'id' => $chatUser->id,
                        'name' => $chatUser->name,
                        'avatar' => $chatUser->avatar_url,
                        'role' => $chatUser->role_name,
                        'department' => $chatUser->department?->name ?? null,
                    ])->values(),
                    'conversations' => $conversations->values()->map(fn ($conversation) => [
                        'id' => $conversation->id,
                        'name' => $conversation->name,
                        'avatar' => $conversation->avatar_url,
                        'role' => $conversation->role_name,
                        'department' => $conversation->department?->name ?? null,
                        'lastMessage' => $conversation->last_message?->content ?? '',
                        'lastMessageAt' => $conversation->last_message?->created_at?->toIso8601String(),
                        'unreadCount' => (int) ($conversation->unread_count ?? 0),
                    ]),
                    'endpoints' => [
                        'realtimeSnapshot' => route('realtime.snapshot'),
                        'sidebarData' => route('messages.sidebar-data'),
                        'conversationBase' => url('/messages/conversation'),
                        'sendBase' => url('/messages/send'),
                        'readBase' => url('/messages/read'),
                    ],
                ];

                return $props;
            })(compact('users', 'conversations')),
        );
    }

    public function unreadCount(Request $request)
    {
        $count = Message::unreadCountFor($request->user()->id);

        return response()->json(['count' => $count]);
    }

    public function sidebarData(Request $request): JsonResponse
    {
        $user = $request->user();

        $users = User::query()
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->with(['role', 'department'])
            ->orderBy('name')
            ->get();

        $conversations = Message::getConversations($user->id);

        return response()->json([
            'users' => $users->map(fn (User $chatUser) => [
                'id' => $chatUser->id,
                'name' => $chatUser->name,
                'avatar' => $chatUser->avatar_url,
                'role' => $chatUser->role_name,
                'department' => $chatUser->department?->name ?? '-',
            ])->values(),
            'conversations' => $conversations->values()->map(fn (User $conversation) => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'avatar' => $conversation->avatar_url,
                'role' => $conversation->role_name,
                'department' => $conversation->department?->name ?? '-',
                'unreadCount' => $conversation->unread_count ?? 0,
                'lastMessage' => $conversation->last_message?->content,
                'lastMessageAt' => $conversation->last_message?->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function conversation(Request $request, User $user)
    {
        $currentUser = $request->user();

        $messages = Message::betweenUsers($currentUser->id, $user->id)
            ->orderBy('created_at')
            ->get();

        // Mark as read
        $updated = Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            app(RealtimeBroadcaster::class)->user($currentUser->id, ['messages']);
        }

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'content' => $m->content,
                'is_mine' => $m->sender_id === $currentUser->id,
                'is_read' => $m->is_read,
                'created_at' => $m->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                'date' => $m->created_at->setTimezone('Asia/Jakarta')->format('d M'),
                'created_at_raw' => $m->created_at->toIso8601String(),
            ]),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
            ],
            'unreadCount' => Message::unreadCountFor($currentUser->id),
        ]);
    }

    public function send(Request $request, User $user)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'is_mine' => true,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function markRead(Request $request, User $user)
    {
        $updated = Message::where('sender_id', $user->id)
            ->where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            app(RealtimeBroadcaster::class)->user($request->user()->id, ['messages']);
        }

        return response()->json(['success' => true]);
    }
}
