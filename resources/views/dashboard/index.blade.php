@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $myPrograms = \App\Models\Program::forUser(auth()->id())->with('department')->take(3)->get();

    $props = [
        'user' => [
            'name' => auth()->user()->name,
        ],
        'stats' => $stats,
        'tasksByStatus' => $tasksByStatus,
        'recentTasks' => $recentTasks,
        'upcomingTimelines' => $upcomingTimelines,
        'staffRanking' => $staffRanking ?? [],
        'departmentProgress' => $departmentProgress ?? [],
        'monthlyTrends' => $monthlyTrends ?? [],
        'myPrograms' => $myPrograms,
        'links' => [
            'tasksCreate' => auth()->user()->hasRole(['admin', 'bph', 'kabinet']) ? route('tasks.create') : null,
            'timelinesCalendar' => route('timelines.calendar'),
            'drivesIndex' => route('drives.index'),
            'linksIndex' => route('links.index'),
            'tasksIndex' => route('tasks.index'),
            'programsMy' => route('programs.my'),
        ],
        'now' => [
            'iso' => now()->toIso8601String(),
        ],
    ];
@endphp

<div id="svelte-dashboard-root"></div>
<script id="svelte-dashboard-props" type="application/json">{!! str_replace('</', '<\\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
@endsection
