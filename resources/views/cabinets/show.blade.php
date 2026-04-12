@extends('layouts.app')

@section('title', 'Detail Kabinet')
@section('page-title', $cabinet->name)
@section('page-meta', 'Lihat komposisi departemen, jumlah anggota, dan status kabinet dalam satu ringkasan kepengurusan.')

@section('content')
@php
    $cabinet->loadMissing(['departments.users.role', 'departments.programs']);

    $activeDepartments = $cabinet->departments->where('status', 'active')->count();
    $totalMembers = $cabinet->departments->sum(fn($department) => $department->users->count());
    $totalPrograms = $cabinet->departments->sum(fn($department) => $department->programs->count());

    $props = [
        'csrfToken' => csrf_token(),
        'summary' => [
            'icon' => 'fas fa-landmark',
            'title' => $cabinet->name,
            'subtitle' => $cabinet->year,
            'badges' => [
                [
                    'label' => $cabinet->status === 'active' ? 'Kabinet Aktif' : 'Inactive',
                    'tone' => $cabinet->status === 'active' ? 'success' : 'secondary',
                    'icon' => $cabinet->status === 'active' ? 'fas fa-star' : null,
                ],
            ],
            'description' => 'Kabinet ini menjadi payung untuk struktur departemen, jumlah anggota, dan distribusi program kerja pada periode terkait.',
            'facts' => [
                ['label' => 'Total Departemen', 'value' => $cabinet->departments->count()],
                ['label' => 'Departemen Aktif', 'value' => $activeDepartments],
                ['label' => 'Total Anggota', 'value' => $totalMembers . ' orang'],
                ['label' => 'Total Proker', 'value' => $totalPrograms . ' proker'],
            ],
            'actions' => [
                ['href' => route('cabinets.edit', $cabinet), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                ['href' => route('cabinets.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
            ],
        ],
        'stats' => [
            ['label' => 'Departemen', 'value' => $cabinet->departments->count(), 'icon' => 'fas fa-building', 'tone' => 'primary'],
            ['label' => 'Anggota', 'value' => $totalMembers, 'icon' => 'fas fa-users', 'tone' => 'info'],
            ['label' => 'Proker', 'value' => $totalPrograms, 'icon' => 'fas fa-diagram-project', 'tone' => 'warning'],
        ],
        'sections' => [
            [
                'kind' => 'table',
                'title' => 'Departemen',
                'icon' => 'fas fa-building',
                'columns' => [
                    ['label' => 'Nama'],
                    ['label' => 'Kepala Departemen'],
                    ['label' => 'Anggota'],
                    ['label' => 'Proker'],
                    ['label' => 'Status'],
                ],
                'rows' => $cabinet->departments->map(function ($department) {
                    $head = $department->users->first(function ($user) {
                        return $user->role?->name === 'kabinet';
                    });

                    return [
                        'cells' => [
                            [
                                'type' => 'stack',
                                'lines' => [
                                    ['text' => $department->name, 'href' => route('departments.show', $department), 'className' => 'fw-semibold'],
                                ],
                            ],
                            $head
                                ? ['type' => 'avatar', 'image' => $head->avatar_url, 'title' => $head->name, 'subtitle' => $head->email]
                                : ['type' => 'text', 'text' => '-', 'muted' => true],
                            ['type' => 'badge', 'label' => $department->users->count() . ' orang', 'tone' => 'info'],
                            ['type' => 'badge', 'label' => $department->programs->count() . ' proker', 'tone' => 'primary'],
                            ['type' => 'badge', 'label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
                        ],
                    ];
                })->values(),
                'emptyText' => 'Belum ada departemen di kabinet ini.',
                'spacingClass' => 'mb-0',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-detail-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-detail-root"></div>
@endsection
