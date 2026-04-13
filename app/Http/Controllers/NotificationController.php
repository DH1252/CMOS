<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        if ($request->expectsJson()) {
            return response()->json($this->indexPayload($notifications, $unreadCount));
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread count (AJAX)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (AJAX for dropdown)
     */
    public function recent(Request $request): JsonResponse
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $unreadCount = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse|
    \Illuminate\Http\RedirectResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect based on notification type
        $redirect = match ($notification->type) {
            Notification::TYPE_TASK_ASSIGNED,
            Notification::TYPE_DEADLINE_REMINDER => route('tasks.show', $notification->data['task_id'] ?? 0),
            Notification::TYPE_EVALUATION_NEW => route('evaluations.my'),
            Notification::TYPE_ANNOUNCEMENT => route('announcements.index'),
            default => route('notifications.index'),
        };

        return redirect($redirect);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(Request $request): JsonResponse|
    \Illuminate\Http\RedirectResponse
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse|
    \Illuminate\Http\RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus');
    }

    /**
     * @return array<string, mixed>
     */
    private function indexPayload(LengthAwarePaginator $notifications, int $unreadCount): array
    {
        return [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications->getCollection()->map(function (Notification $notification) {
                $href = match ($notification->type) {
                    Notification::TYPE_TASK_ASSIGNED, Notification::TYPE_DEADLINE_REMINDER => ! empty($notification->data['task_id']) ? route('tasks.show', $notification->data['task_id']) : route('notifications.index'),
                    Notification::TYPE_EVALUATION_NEW => route('evaluations.my'),
                    Notification::TYPE_ANNOUNCEMENT => route('announcements.index'),
                    default => route('notifications.index'),
                };

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'tone' => $notification->color,
                    'href' => $href,
                    'readAt' => $notification->read_at?->toIso8601String(),
                    'createdAt' => $notification->created_at->toIso8601String(),
                    'readUrl' => route('notifications.read', $notification),
                    'deleteUrl' => route('notifications.destroy', $notification),
                ];
            })->values(),
            'pagination' => [
                'currentPage' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
                'previousUrl' => $notifications->previousPageUrl(),
                'nextUrl' => $notifications->nextPageUrl(),
                'markAllUrl' => route('notifications.mark-all-read'),
            ],
        ];
    }
}
