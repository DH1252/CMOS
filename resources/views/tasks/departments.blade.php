@extends('layouts.app')

@section('title', 'Task Management')
@section('page-title', 'Task Management')

@section('content')
@php
    $props = [
        'title' => 'Task Management',
        'description' => 'Pantau pekerjaan global dan masuk ke task board tiap departemen dari satu pintu.',
        'icon' => 'fas fa-diagram-project',
        'cards' => array_merge([
            [
                'href' => route('tasks.global'),
                'title' => 'Global Tasks',
                'description' => 'Tugas lintas departemen untuk koordinasi organisasi.',
                'icon' => 'fas fa-globe',
                'tone' => 'primary',
                'featured' => true,
                'stats' => [
                    ['icon' => 'fas fa-list-check', 'label' => "{$globalTasksCount} tugas", 'tone' => 'secondary'],
                    ['icon' => 'fas fa-clock', 'label' => "{$globalPendingCount} aktif", 'tone' => 'warning'],
                ],
            ],
        ], $departments->map(fn ($department) => [
            'href' => route('tasks.department', $department),
            'title' => $department->name,
            'description' => $department->cabinet?->name ?? 'Belum terhubung ke kabinet',
            'icon' => 'fas fa-building',
            'tone' => 'info',
            'stats' => [
                ['icon' => 'fas fa-list-check', 'label' => ($department->total_tasks ?? 0) . ' tugas', 'tone' => 'secondary'],
                ['icon' => 'fas fa-clock', 'label' => ($department->pending_tasks ?? 0) . ' aktif', 'tone' => 'warning'],
            ],
        ])->values()->all()),
        'emptyState' => [
            'title' => 'Belum ada departemen',
            'text' => 'Departemen akan tampil di sini setelah data organisasi tersedia.',
        ],
    ];
@endphp

<script id="svelte-task-hub-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-task-hub-root"></div>
@endsection
