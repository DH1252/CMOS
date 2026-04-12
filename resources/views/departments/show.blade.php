@extends('layouts.app')

@section('title', 'Detail Departemen')
@section('page-title', $department->name)

@section('content')
@php
    $department->loadMissing(['users.role', 'programs.department']);

    $props = [
        'csrfToken' => csrf_token(),
        'summary' => [
            'icon' => 'fas fa-building',
            'title' => $department->name,
            'badges' => [
                ['label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
            ],
            'description' => $department->description,
            'facts' => [
                ['label' => 'Kabinet', 'value' => $department->cabinet?->name ?? '-'],
                ['label' => 'Total Anggota', 'value' => $department->users->count() . ' orang'],
                ['label' => 'Total Proker', 'value' => $department->programs->count() . ' proker'],
            ],
            'actions' => [
                ['href' => route('departments.edit', $department), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                ['href' => route('departments.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
            ],
        ],
        'sections' => [
            [
                'kind' => 'table',
                'title' => 'Anggota Departemen',
                'icon' => 'fas fa-users',
                'columns' => [
                    ['label' => 'Nama'],
                    ['label' => 'Email'],
                    ['label' => 'Role'],
                    ['label' => 'Status'],
                ],
                'rows' => $department->users->map(function ($user) {
                    return [
                        'cells' => [
                            ['type' => 'avatar', 'image' => $user->avatar_url, 'title' => $user->name],
                            ['type' => 'text', 'text' => $user->email],
                            ['type' => 'badge', 'label' => ucfirst($user->role?->name ?? '-'), 'tone' => $user->role?->name === 'kabinet' ? 'info' : 'secondary'],
                            ['type' => 'badge', 'label' => ucfirst($user->status), 'tone' => $user->status === 'active' ? 'success' : 'secondary'],
                        ],
                    ];
                })->values(),
                'emptyText' => 'Belum ada anggota.',
            ],
            [
                'kind' => 'table',
                'title' => 'Program Kerja',
                'icon' => 'fas fa-diagram-project',
                'columns' => [
                    ['label' => 'Nama Proker'],
                    ['label' => 'Periode'],
                    ['label' => 'Status'],
                ],
                'rows' => $department->programs->map(function ($program) {
                    return [
                        'cells' => [
                            ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                            ['type' => 'text', 'text' => $program->start_date->format('d M') . ' - ' . $program->end_date->format('d M Y'), 'muted' => true],
                            ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match($program->status) {
                                'completed' => 'success',
                                'active' => 'warning',
                                'cancelled' => 'danger',
                                default => 'secondary',
                            }],
                        ],
                    ];
                })->values(),
                'emptyText' => 'Belum ada program kerja.',
                'spacingClass' => 'mb-0',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-detail-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-detail-root"></div>
@endsection
