<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementReaction;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Support\RealtimeBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::with(['user', 'comments.user', 'reactions', 'pollOptions'])
            ->latest()
            ->paginate(10);

        if ($request->expectsJson()) {
            return response()->json($this->indexPayload($announcements, $request));
        }

        return \Inertia\Inertia::render(
            'pages/AnnouncementFeedPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

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
                            'content' => session('errors')?->first('content'),
                            'poll_question' => session('errors')?->first('poll_question'),
                            'poll_options' => session('errors')?->first('poll_options'),
                            'poll_options_items' => session('errors')?->first('poll_options.*'),
                            'poll_duration' => session('errors')?->first('poll_duration'),
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
                                ->map(fn ($count, $type) => [
                                    'type' => $type,
                                    'emoji' => $reactionEmoji[$type] ?? '👍',
                                    'count' => $count,
                                ])
                                ->values(),
                            'comments' => $post->comments->take(5)->map(fn ($comment) => [
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
                                'options' => $post->pollOptions->map(fn ($option) => [
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

                return $props;
            })(compact('announcements')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'has_poll' => 'boolean',
            'poll_question' => 'required_if:has_poll,true|nullable|string|max:255',
            'poll_options' => 'required_if:has_poll,true|nullable|array|min:2|max:6',
            'poll_options.*' => 'required_if:has_poll,true|string|max:100',
            'poll_duration' => 'nullable|integer|min:1|max:168', // hours, max 7 days
        ]);

        $announcement = Announcement::create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'has_poll' => $request->boolean('has_poll'),
            'poll_question' => $validated['poll_question'] ?? null,
            'poll_ends_at' => $request->boolean('has_poll') && ! empty($validated['poll_duration'])
                ? now()->addHours((int) $validated['poll_duration'])
                : null,
        ]);

        // Create poll options if poll exists
        if ($request->boolean('has_poll') && ! empty($validated['poll_options'])) {
            foreach ($validated['poll_options'] as $optionText) {
                if (! empty(trim($optionText))) {
                    PollOption::create([
                        'announcement_id' => $announcement->id,
                        'option_text' => trim($optionText),
                    ]);
                }
            }
        }

        ActivityLog::log('created', 'Created announcement', $announcement);
        app(RealtimeBroadcaster::class)->organization(['announcements']);

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diposting!');
    }

    public function destroy(Announcement $announcement)
    {
        // Only creator can delete
        if ($announcement->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus ini.');
        }

        ActivityLog::log('deleted', 'Deleted announcement', $announcement);
        $announcement->delete();
        app(RealtimeBroadcaster::class)->organization(['announcements']);

        return back()->with('success', 'Pengumuman berhasil dihapus!');
    }

    public function comment(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        AnnouncementComment::create([
            'announcement_id' => $announcement->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        app(RealtimeBroadcaster::class)->organization(['announcements']);

        return back()->with('success', 'Komentar ditambahkan!');
    }

    public function react(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $existing = AnnouncementReaction::where('announcement_id', $announcement->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            if ($existing->type === $validated['type']) {
                // Remove reaction
                $existing->delete();

                app(RealtimeBroadcaster::class)->organization(['announcements']);

                return response()->json(['removed' => true]);
            } else {
                // Change reaction
                $existing->update(['type' => $validated['type']]);

                app(RealtimeBroadcaster::class)->organization(['announcements']);

                return response()->json(['changed' => true, 'type' => $validated['type']]);
            }
        }

        AnnouncementReaction::create([
            'announcement_id' => $announcement->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
        ]);

        app(RealtimeBroadcaster::class)->organization(['announcements']);

        return response()->json(['added' => true, 'type' => $validated['type']]);
    }

    public function vote(Request $request, Announcement $announcement)
    {
        if (! $announcement->has_poll || ! $announcement->isPollActive()) {
            return response()->json(['error' => 'Poll tidak aktif'], 400);
        }

        $validated = $request->validate([
            'option_id' => 'required|exists:poll_options,id',
        ]);

        // Check if already voted
        $existingVote = PollVote::whereIn('poll_option_id', $announcement->pollOptions->pluck('id'))
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            return response()->json(['error' => 'Anda sudah memilih'], 400);
        }

        $option = PollOption::findOrFail($validated['option_id']);

        PollVote::create([
            'poll_option_id' => $option->id,
            'user_id' => auth()->id(),
        ]);

        $option->increment('votes_count');
        app(RealtimeBroadcaster::class)->organization(['announcements']);

        // Return updated poll data
        $announcement->refresh();
        $pollData = $announcement->pollOptions->map(fn ($o) => [
            'id' => $o->id,
            'text' => $o->option_text,
            'votes' => $o->votes_count,
            'percentage' => $o->percentage,
        ]);

        return response()->json([
            'success' => true,
            'total_votes' => $announcement->total_votes,
            'options' => $pollData,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function indexPayload(LengthAwarePaginator $announcements, Request $request): array
    {
        $reactionEmoji = [
            'like' => '👍',
            'love' => '❤️',
            'haha' => '😂',
            'wow' => '😮',
            'sad' => '😢',
            'angry' => '😡',
        ];

        return [
            'posts' => $announcements->getCollection()->map(function (Announcement $post) use ($reactionEmoji, $request) {
                $userId = $request->user()->getAuthIdentifier();
                $hasVoted = $post->hasUserVoted($userId);
                $userVoteId = $post->getUserVoteOptionId($userId);
                $userReaction = $post->getUserReaction($userId);

                return [
                    'id' => $post->id,
                    'author' => [
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar_url,
                    ],
                    'createdAt' => $post->created_at->toIso8601String(),
                    'content' => $post->content,
                    'canDelete' => $post->user_id === $userId || $request->user()->isAdmin(),
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
                        ->map(fn ($count, $type) => [
                            'type' => $type,
                            'emoji' => $reactionEmoji[$type] ?? '👍',
                            'count' => $count,
                        ])
                        ->values(),
                    'comments' => $post->comments->take(5)->map(fn ($comment) => [
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
                        'options' => $post->pollOptions->map(fn ($option) => [
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
    }
}
