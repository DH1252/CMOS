<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

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

        return \Inertia\Inertia::render('pages/NotificationInboxPage', [
            'title' => 'Notifikasi',
            'description' => 'Kotak masuk untuk task baru, pengingat deadline, evaluasi, dan publikasi terbaru.',
            'csrfToken' => csrf_token(),
            'refreshUrl' => route('notifications.index'),
            'realtimeSnapshot' => route('realtime.snapshot'),
            'unreadCount' => $unreadCount,
            'notifications' => $notifications->getCollection()->map(fn (Notification $notification): array => $this->notificationPayload($notification))->values(),
            'pagination' => [
                'currentPage' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
                'previousUrl' => $notifications->previousPageUrl(),
                'nextUrl' => $notifications->nextPageUrl(),
                'markAllUrl' => route('notifications.mark-all-read'),
                'clearAllUrl' => route('notifications.clear-all'),
            ],
        ]);
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
            'notifications' => $notifications->map(fn (Notification $notification): array => $this->notificationPayload($notification))->values(),
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
        if ((int) $notification->user_id !== (int) Auth::id()) {
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
     * Clear all notifications.
     */
    public function clearAll(Request $request): JsonResponse|
    \Illuminate\Http\RedirectResponse
    {
        $deletedCount = Notification::forUser($request->user()->id)->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'deleted' => $deletedCount]);
        }

        return back()->with('success', 'Semua notifikasi dibersihkan');
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse|
    \Illuminate\Http\RedirectResponse
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
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
            'notifications' => $notifications->getCollection()->map(fn (Notification $notification): array => $this->notificationPayload($notification))->values(),
            'pagination' => [
                'currentPage' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
                'previousUrl' => $notifications->previousPageUrl(),
                'nextUrl' => $notifications->nextPageUrl(),
                'markAllUrl' => route('notifications.mark-all-read'),
                'clearAllUrl' => route('notifications.clear-all'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function notificationPayload(Notification $notification): array
    {
        $href = $this->notificationHref($notification);
        $readAt = $notification->read_at?->toIso8601String();
        $createdAt = $notification->created_at->toIso8601String();

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->icon,
            'tone' => $notification->color,
            'type' => $notification->type,
            'href' => $href,
            'readAt' => $readAt,
            'read_at' => $readAt,
            'createdAt' => $createdAt,
            'created_at' => $createdAt,
            'readUrl' => route('notifications.read', $notification),
            'deleteUrl' => route('notifications.destroy', $notification),
        ];
    }

    private function notificationHref(Notification $notification): string
    {
        return match ($notification->type) {
            Notification::TYPE_TASK_ASSIGNED,
            Notification::TYPE_DEADLINE_REMINDER => ! empty($notification->data['task_id']) ? route('tasks.show', $notification->data['task_id']) : route('notifications.index'),
            Notification::TYPE_EVALUATION_NEW => route('evaluations.my'),
            Notification::TYPE_ANNOUNCEMENT => route('announcements.index'),
            default => route('notifications.index'),
        };
    }
}
