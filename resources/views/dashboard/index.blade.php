@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $props = [
        'stats' => $stats,
        'tasksByStatus' => $tasksByStatus,
        'recentTasks' => $recentTasks,
        'upcomingTimelines' => $upcomingTimelines,
        'latestInformationBoards' => collect($latestInformationBoards ?? [])->map(fn ($article) => [
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'publishedAt' => optional($article->publishedAtLocal)->toIso8601String(),
            'href' => route('information-boards.show', $article),
        ])->values(),
        'departmentProgress' => $departmentProgress ?? [],
        'staffRanking' => collect($staffRanking ?? [])->map(fn ($staff) => [
            'name' => $staff->name,
            'department' => $staff->department?->name,
            'score' => $staff->evaluations_avg_total_score,
        ])->values(),
        'monthlyTrends' => $monthlyTrends ?? [],
        'links' => [
            'timelinesIndex' => route('timelines.index'),
            'timelinesCalendar' => route('timelines.calendar'),
            'tasksIndex' => route('tasks.index'),
        ],
        'now' => [
            'iso' => now()->toIso8601String(),
        ],
    ];
@endphp

<div id="svelte-dashboard-root"></div>
<script id="svelte-dashboard-props" type="application/json">{!! str_replace('</', '<\\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
@endsection
