@extends('layouts.app')

@section('title', 'Pesan')
@section('page-title', 'Pesan Internal')
@section('page-meta', 'Kontak aktif dan riwayat percakapan.')

@section('content')
@php
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
@endphp

<script id="svelte-messages-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-messages-root"></div>
@endsection
