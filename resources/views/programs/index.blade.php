@extends('layouts.app')

@section('title', 'Program Kerja')
@section('page-title', 'Program Kerja')
@section('page-meta', 'Portofolio program kerja dan progresnya.')

@section('content')
@php
    $canManagePrograms = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

    $props = [
        'title' => 'Daftar Program Kerja',
        'description' => 'Progres dan status tiap program kerja.',
        'icon' => 'fas fa-diagram-project',
        'csrfToken' => csrf_token(),
        'enableDataTable' => true,
        'primaryAction' => $canManagePrograms ? [
            'label' => 'Tambah Program',
            'href' => route('programs.create'),
            'icon' => 'fas fa-plus',
        ] : null,
        'columns' => [
            ['label' => 'Nama'],
            ['label' => 'Departemen'],
            ['label' => 'Periode'],
            ['label' => 'Task'],
            ['label' => 'Progress'],
            ['label' => 'Status'],
            ['label' => 'Aksi', 'width' => '100px'],
        ],
        'rows' => $programs->map(function ($program) use ($canManagePrograms) {
            $progress = $program->progress;

            return [
                'cells' => [
                    ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                    ['type' => 'text', 'text' => $program->department?->name ?? '-', 'muted' => !$program->department],
                    ['type' => 'text', 'text' => $program->start_date->format('d M') . ' - ' . $program->end_date->format('d M Y'), 'muted' => true],
                    ['type' => 'badge', 'label' => "{$program->tasks_count} task", 'tone' => 'info'],
                    ['type' => 'progress', 'value' => $progress, 'label' => "{$progress}%"],
                    ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match($program->status) {
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }],
                    [
                        'type' => 'actions',
                        'items' => array_values(array_filter([
                            ['href' => route('programs.show', $program), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                            $canManagePrograms ? ['href' => route('programs.edit', $program), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'] : null,
                        ])),
                    ],
                ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada program kerja',
            'text' => 'Tambahkan program pertama untuk mulai membangun ritme kerja organisasi.',
        ],
    ];
@endphp

<script id="svelte-crud-table-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-crud-table-root"></div>
@endsection
