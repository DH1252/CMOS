@extends('layouts.app')

@section('title', $title . ' - Kanban')
@section('page-title', $title)

@section('content')
@php
    $description = $type === 'global'
        ? 'Task lintas departemen untuk sinkronisasi kerja organisasi.'
        : ($type === 'department'
            ? "Papan kerja khusus untuk {$department->name}."
            : "Pantau seluruh task untuk program {$program->name}.");

    $breadcrumbs = [['label' => 'Tasks', 'href' => route('tasks.index')]];

    if ($type === 'department') {
        $breadcrumbs[] = ['label' => $department->name, 'href' => route('tasks.department', $department)];
        $breadcrumbs[] = ['label' => 'Tugas Departemen'];
    } elseif ($type === 'program') {
        $breadcrumbs[] = ['label' => $program->department->name, 'href' => route('tasks.department', $program->department)];
        $breadcrumbs[] = ['label' => $program->name];
    } else {
        $breadcrumbs[] = ['label' => 'Global Tasks'];
    }

    $statusLabels = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'pending' => 'Pending',
        'done' => 'Done',
    ];

    $props = [
        'title' => $title,
        'description' => $description,
        'breadcrumbs' => $breadcrumbs,
        'context' => [
            'type' => $type,
            'typeId' => $typeId,
        ],
        'endpoints' => [
            'storeInline' => route('tasks.inline.store'),
            'taskBase' => url('/tasks'),
        ],
        'csrfToken' => csrf_token(),
        'users' => $users->map(fn ($user) => [
            'value' => $user->id,
            'label' => $user->name . ' (' . ucfirst($user->role?->name ?? 'user') . ')',
        ])->values(),
        'columns' => collect(\App\Models\Task::STATUSES)->map(function ($status) use ($tasks, $statusLabels) {
            return [
                'status' => $status,
                'label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                'tasks' => collect($tasks->get($status, collect()))->map(fn ($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'priority_label' => $task->priority_label,
                    'progress' => $task->progress,
                    'deadline' => $task->deadline?->format('Y-m-d'),
                    'deadline_fmt' => $task->deadline?->format('d M Y'),
                    'is_overdue' => $task->is_overdue,
                    'assigned_to' => $task->assigned_to,
                    'assignee_name' => $task->assignee?->name,
                    'assignee_avatar' => $task->assignee?->avatar_url,
                    'showHref' => route('tasks.show', $task),
                ])->values(),
            ];
        })->values(),
    ];
@endphp

<script id="svelte-task-board-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-task-board-root"></div>
@endsection
