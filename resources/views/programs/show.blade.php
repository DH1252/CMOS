@extends('layouts.app')

@section('title', $program->name)
@section('page-title', $program->name)

@section('content')
@php
    $program->loadMissing(['department', 'creator', 'members', 'tasks.assignee', 'timelines']);
    $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

    $props = [
        'csrfToken' => csrf_token(),
        'summary' => [
            'name' => $program->name,
            'description' => $program->description,
            'statusLabel' => ucfirst($program->status),
            'statusTone' => match($program->status) {
                'active' => 'warning',
                'completed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary',
            },
            'progress' => $program->progress,
            'facts' => [
                ['label' => 'Departemen', 'value' => $program->department?->name ?? '-'],
                ['label' => 'Periode', 'value' => $program->start_date->format('d M') . ' - ' . $program->end_date->format('d M Y')],
                ['label' => 'PIC Internal', 'value' => $program->creator?->name ?? '-'],
                ['label' => 'Total Task', 'value' => $program->tasks->count()],
            ],
            'actions' => array_values(array_filter([
                $canManage ? ['href' => route('programs.edit', $program), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'] : null,
                ['href' => route('programs.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
            ])),
        ],
        'team' => [
            'members' => $program->members->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar_url,
                'roleLabel' => ucfirst($member->pivot->role),
                'roleTone' => $member->pivot->role === 'leader' ? 'primary' : 'secondary',
                'removeAction' => $canManage ? route('programs.members.remove', [$program, $member]) : null,
            ])->values(),
            'addAction' => $canManage ? route('programs.members.add', $program) : null,
            'availableUsers' => $canManage ? \App\Models\User::active()->orderBy('name')->get()->map(fn($user) => [
                'value' => $user->id,
                'label' => $user->name,
            ])->values() : [],
        ],
        'tasks' => [
            'createAction' => $canManage ? route('tasks.create', ['type' => 'program', 'id' => $program->id]) : null,
            'columns' => [
                ['label' => 'Task'],
                ['label' => 'Assignee'],
                ['label' => 'Status'],
                ['label' => 'Progress'],
                ['label' => 'Aksi'],
            ],
            'rows' => $program->tasks->map(function ($task) {
                return [
                    'cells' => [
                        ['type' => 'text', 'text' => $task->title, 'className' => 'fw-semibold'],
                        $task->assignee
                            ? ['type' => 'avatar', 'image' => $task->assignee->avatar_url, 'title' => $task->assignee->name]
                            : ['type' => 'text', 'text' => '-', 'muted' => true],
                        ['type' => 'badge', 'label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                        ['type' => 'progress', 'value' => $task->progress, 'label' => "{$task->progress}%"],
                        ['type' => 'actions', 'items' => [
                            ['href' => route('tasks.show', $task), 'label' => 'Detail Task', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                        ]],
                    ],
                ];
            })->values(),
        ],
        'timelines' => [
            'items' => $program->timelines->map(fn($timeline) => [
                'title' => $timeline->title,
                'range' => $timeline->start_date->format('d M') . ' - ' . $timeline->end_date->format('d M Y'),
                'color' => $timeline->color ?? '#7C3AED',
                'description' => $timeline->description,
            ])->values(),
        ],
    ];
@endphp

<script id="svelte-program-detail-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-program-detail-root"></div>
@endsection
