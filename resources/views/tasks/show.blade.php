@extends('layouts.app')

@section('title', 'Detail Task')
@section('page-title', $task->title)

@section('content')
@php
    $backHref = $task->is_global
        ? route('tasks.global')
        : ($task->program_id
            ? route('tasks.program', $task->program)
            : ($task->department_id ? route('tasks.department.tasks', $task->department) : route('tasks.index')));

    $taskTypeFact = $task->is_global
        ? ['label' => 'Tipe Task', 'value' => 'Global', 'className' => 'fw-semibold']
        : ($task->program_id
            ? ['label' => 'Tipe Task', 'value' => $task->program->name, 'href' => route('tasks.program', $task->program), 'className' => 'fw-semibold']
            : ($task->department_id
                ? ['label' => 'Tipe Task', 'value' => $task->department->name, 'href' => route('tasks.department.tasks', $task->department), 'className' => 'fw-semibold']
                : ['label' => 'Tipe Task', 'value' => '-', 'className' => 'text-muted']));

    $props = [
        'summary' => [
            'title' => $task->title,
            'description' => $task->description,
            'badges' => [
                ['label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                ['label' => ucfirst($task->priority), 'tone' => $task->priority_badge],
            ],
            'actions' => [
                ['href' => $backHref, 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
            ],
        ],
        'facts' => [
            $taskTypeFact,
            ['label' => 'Departemen', 'value' => $task->program?->department?->name ?? $task->department?->name ?? 'Global', 'className' => 'fw-semibold'],
            ['label' => 'Deadline', 'value' => $task->deadline?->format('d M Y') ?? '-', 'className' => $task->is_overdue ? 'fw-semibold text-danger' : 'fw-semibold'],
            ['label' => 'Dibuat oleh', 'value' => $task->creator?->name ?? '-', 'className' => 'fw-semibold'],
        ],
        'progress' => [
            'value' => $task->progress,
            'action' => route('tasks.progress', $task),
            'csrfToken' => csrf_token(),
            'canUpdate' => auth()->id() === $task->assigned_to || auth()->user()->hasRole(['admin', 'bph', 'kabinet']),
        ],
        'assignee' => $task->assignee ? [
            'name' => $task->assignee->name,
            'avatar' => $task->assignee->avatar_url,
            'email' => $task->assignee->email,
            'roleLabel' => ucfirst($task->assignee->role?->name ?? '-'),
            'roleTone' => $task->assignee->role?->name === 'kabinet' ? 'info' : 'secondary',
        ] : null,
        'meta' => [
            ['label' => 'Dibuat', 'value' => $task->created_at->format('d M Y H:i')],
            ['label' => 'Diupdate', 'value' => $task->updated_at->format('d M Y H:i')],
        ],
    ];
@endphp

<script id="svelte-task-detail-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-task-detail-root"></div>
@endsection
