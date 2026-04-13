@extends('layouts.app')

@section('title', $department->name . ' - Programs')
@section('page-title', $department->name)

@section('content')
@php
    $departmentProgress = $deptTasksCount > 0 ? round(($deptDoneCount / $deptTasksCount) * 100) : 0;

    $props = [
        'title' => $department->name,
        'description' => 'Pilih board departemen atau program kerja.',
        'icon' => 'fas fa-building',
        'breadcrumbs' => [
            ['label' => 'Tasks', 'href' => route('tasks.index')],
            ['label' => $department->name],
        ],
        'cards' => array_merge([
            [
                'href' => route('tasks.department.tasks', $department),
                'title' => 'Tugas Departemen',
                'description' => "Task untuk {$department->name}.",
                'icon' => 'fas fa-folder-tree',
                'tone' => 'primary',
                'featured' => true,
                'progress' => $departmentProgress,
                'stats' => [
                    ['icon' => 'fas fa-check', 'label' => "{$deptDoneCount} selesai", 'tone' => 'success'],
                    ['icon' => 'fas fa-clock', 'label' => "{$deptPendingCount} aktif", 'tone' => 'warning'],
                ],
            ],
        ], $programs->map(fn ($program) => [
            'href' => route('tasks.program', $program),
            'title' => $program->name,
            'description' => \Illuminate\Support\Str::limit($program->description ?: 'Belum ada deskripsi program.', 88),
            'icon' => 'fas fa-sitemap',
            'tone' => 'success',
            'progress' => $program->total_tasks > 0 ? round((($program->done_tasks ?? 0) / $program->total_tasks) * 100) : 0,
            'stats' => [
                ['icon' => 'fas fa-check', 'label' => ($program->done_tasks ?? 0) . ' selesai', 'tone' => 'success'],
                ['icon' => 'fas fa-clock', 'label' => ($program->pending_tasks ?? 0) . ' aktif', 'tone' => 'warning'],
            ],
        ])->values()->all()),
        'emptyState' => [
            'title' => 'Belum ada program',
            'text' => 'Departemen ini belum memiliki program kerja untuk diturunkan menjadi task board.',
        ],
    ];
@endphp

<script id="svelte-task-hub-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-task-hub-root"></div>
@endsection
