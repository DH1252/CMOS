@extends('layouts.app')

@section('title', 'Pengumuman')
@section('page-title', 'Pengumuman')
@section('page-meta', 'Pengumuman internal dan polling cepat.')

@section('content')
@php
    $oldPollOptions = old('poll_options', ['', '']);
    if (count($oldPollOptions) < 2) {
        $oldPollOptions = array_pad($oldPollOptions, 2, '');
    }

    $reactionEmoji = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    $props = [
        'title' => 'Feed Pengumuman',
        'description' => 'Pengumuman internal, polling, dan komentar.',
        'csrfToken' => csrf_token(),
        'refreshUrl' => route('announcements.index'),
        'realtimeSnapshot' => route('realtime.snapshot'),
        'createForm' => [
            'action' => route('announcements.store'),
            'avatar' => auth()->user()->avatar_url,
            'content' => old('content'),
            'hasPoll' => old('has_poll') ? true : false,
            'pollQuestion' => old('poll_question'),
            'pollDuration' => old('poll_duration', '24'),
            'pollOptions' => array_values($oldPollOptions),
            'errors' => [
                'content' => $errors->first('content'),
                'poll_question' => $errors->first('poll_question'),
                'poll_options' => $errors->first('poll_options'),
                'poll_options_items' => $errors->first('poll_options.*'),
                'poll_duration' => $errors->first('poll_duration'),
            ],
        ],
        'posts' => $announcements->getCollection()->map(function ($post) use ($reactionEmoji) {
            $hasVoted = $post->hasUserVoted(auth()->id());
            $userVoteId = $post->getUserVoteOptionId(auth()->id());
            $userReaction = $post->getUserReaction(auth()->id());

            return [
                'id' => $post->id,
                'author' => [
                    'name' => $post->user->name,
                    'avatar' => $post->user->avatar_url,
                ],
                'createdAt' => $post->created_at->toIso8601String(),
                'content' => $post->content,
                'canDelete' => $post->user_id === auth()->id() || auth()->user()->isAdmin(),
                'deleteAction' => route('announcements.destroy', $post),
                'deletePayload' => [
                    'confirm' => 'pengumuman ini',
                    'confirmText' => 'Hapus pengumuman ini?',
                ],
                'reactUrl' => route('announcements.react', $post),
                'voteUrl' => route('announcements.vote', $post),
                'commentAction' => route('announcements.comment', $post),
                'userReaction' => $userReaction,
                'reactionSummary' => collect($post->reaction_counts)
                    ->map(fn($count, $type) => [
                        'type' => $type,
                        'emoji' => $reactionEmoji[$type] ?? '👍',
                        'count' => $count,
                    ])
                    ->values(),
                'comments' => $post->comments->take(5)->map(fn($comment) => [
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar_url,
                    ],
                    'content' => $comment->content,
                    'createdAt' => $comment->created_at->toIso8601String(),
                ])->values(),
                'poll' => $post->has_poll ? [
                    'question' => $post->poll_question,
                    'hasVoted' => $hasVoted,
                    'userVoteOptionId' => $userVoteId,
                    'isActive' => $post->isPollActive(),
                    'totalVotes' => $post->total_votes,
                    'pollEndsAt' => $post->poll_ends_at?->toIso8601String(),
                    'options' => $post->pollOptions->map(fn($option) => [
                        'id' => $option->id,
                        'text' => $option->option_text,
                        'votes' => $option->votes_count,
                        'percentage' => $hasVoted ? $option->percentage : 0,
                    ])->values(),
                ] : null,
            ];
        })->values(),
        'pagination' => [
            'currentPage' => $announcements->currentPage(),
            'lastPage' => $announcements->lastPage(),
            'prevUrl' => $announcements->previousPageUrl(),
            'nextUrl' => $announcements->nextPageUrl(),
            'from' => $announcements->firstItem() ?? 0,
            'to' => $announcements->lastItem() ?? 0,
            'total' => $announcements->total(),
        ],
    ];
@endphp

<script id="svelte-announcement-feed-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-announcement-feed-root"></div>
@endsection
