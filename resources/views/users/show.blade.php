@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
@php
    $user->loadMissing(['tasks.program', 'evaluations.evaluator']);

    $props = [
        'csrfToken' => csrf_token(),
        'summary' => [
            'image' => $user->avatar_url,
            'title' => $user->name,
            'subtitle' => $user->email,
            'badges' => [
                [
                    'label' => ucfirst($user->role?->name ?? 'No Role'),
                    'tone' => match($user->role?->name) {
                        'admin' => 'danger',
                        'bph' => 'warning',
                        'kabinet' => 'info',
                        default => 'secondary',
                    },
                ],
                [
                    'label' => ucfirst($user->status),
                    'tone' => $user->status === 'active' ? 'success' : 'secondary',
                ],
            ],
            'facts' => [
                ['label' => 'Departemen', 'value' => $user->department?->name ?? '-'],
                ['label' => 'Bergabung', 'value' => $user->created_at->format('d M Y')],
            ],
            'actions' => [
                ['href' => route('users.edit', $user), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                ['href' => route('users.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
            ],
        ],
        'stats' => [
            ['label' => 'Total Task', 'value' => $user->task_stats['total'], 'icon' => 'fas fa-clipboard-list', 'tone' => 'info'],
            ['label' => 'In Progress', 'value' => $user->task_stats['in_progress'], 'icon' => 'fas fa-spinner', 'tone' => 'warning'],
            ['label' => 'Selesai', 'value' => $user->task_stats['done'], 'icon' => 'fas fa-check', 'tone' => 'success'],
        ],
        'sections' => [
            [
                'kind' => 'table',
                'title' => 'Task Terbaru',
                'icon' => 'fas fa-list-check',
                'columns' => [
                    ['label' => 'Task'],
                    ['label' => 'Program'],
                    ['label' => 'Status'],
                    ['label' => 'Progress'],
                ],
                'rows' => $user->tasks->take(5)->map(fn($task) => [
                    'cells' => [
                        ['type' => 'text', 'text' => $task->title, 'className' => 'fw-semibold'],
                        ['type' => 'text', 'text' => $task->program?->name ?? '-', 'muted' => !$task->program],
                        ['type' => 'badge', 'label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                        ['type' => 'progress', 'value' => $task->progress, 'label' => "{$task->progress}%"],
                    ],
                ])->values(),
                'emptyText' => 'Tidak ada task.',
            ],
            [
                'kind' => 'table',
                'title' => 'Riwayat Evaluasi',
                'icon' => 'fas fa-star',
                'columns' => [
                    ['label' => 'Periode'],
                    ['label' => 'Evaluator'],
                    ['label' => 'Tipe'],
                    ['label' => 'Total'],
                ],
                'rows' => $user->evaluations->take(5)->map(fn($evaluation) => [
                    'cells' => [
                        ['type' => 'text', 'text' => $evaluation->period ?? '-', 'className' => 'fw-semibold'],
                        ['type' => 'text', 'text' => $evaluation->evaluator?->name ?? '-', 'muted' => !$evaluation->evaluator],
                        ['type' => 'badge', 'label' => strtoupper($evaluation->evaluator_type), 'tone' => $evaluation->evaluator_type === 'bph' ? 'warning' : 'info'],
                        ['type' => 'badge', 'label' => number_format($evaluation->total_score, 2), 'tone' => $evaluation->total_score >= 4.5 ? 'success' : ($evaluation->total_score >= 3 ? 'warning' : 'danger')],
                    ],
                ])->values(),
                'emptyText' => 'Belum ada data evaluasi.',
                'spacingClass' => 'mb-0',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-detail-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-detail-root"></div>
@endsection
