@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
@php
    $props = [
        'title' => 'Notifikasi',
        'description' => 'Kotak masuk untuk task baru, pengingat deadline, evaluasi, dan publikasi terbaru.',
        'csrfToken' => csrf_token(),
        'unreadCount' => $unreadCount,
        'notifications' => $notifications->getCollection()->map(function ($notification) {
            $href = match($notification->type) {
                \App\Models\Notification::TYPE_TASK_ASSIGNED, \App\Models\Notification::TYPE_DEADLINE_REMINDER => !empty($notification->data['task_id']) ? route('tasks.show', $notification->data['task_id']) : route('notifications.index'),
                \App\Models\Notification::TYPE_EVALUATION_NEW => route('evaluations.my'),
                \App\Models\Notification::TYPE_ANNOUNCEMENT => route('announcements.index'),
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
@endphp

<script id="svelte-notification-inbox-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-notification-inbox-root"></div>
@endsection
