@extends('layouts.app')

@section('title', 'Timeline Departemen')
@section('page-title', $department?->name ? 'Timeline ' . $department->name : 'Timeline Departemen')

@section('content')
@php
    $today = now()->toDateString();
    $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

    $statusFor = function ($timeline) use ($today) {
        if ($timeline->end_date->toDateString() < $today) {
            return ['label' => 'Selesai', 'tone' => 'secondary'];
        }

        if ($timeline->start_date->toDateString() > $today) {
            return ['label' => 'Akan Datang', 'tone' => 'info'];
        }

        return ['label' => 'Berlangsung', 'tone' => 'success'];
    };

    $props = [
        'title' => $department ? 'Agenda ' . $department->name : 'Agenda Departemen',
        'description' => $department
            ? 'Lihat agenda departemen terpilih beserta timeline yang terkait dengan program kerjanya.'
            : 'Pilih departemen untuk melihat agenda yang relevan.',
        'icon' => 'fas fa-building',
        'breadcrumbs' => [
            ['label' => 'Timeline', 'href' => route('timelines.index')],
            ['label' => 'Departemen'],
            $department ? ['label' => $department->name] : null,
        ],
        'actions' => array_values(array_filter([
            ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
            $canManage ? [
                'href' => $department
                    ? route('timelines.create', ['type' => 'department', 'department_id' => $department->id])
                    : route('timelines.create', ['type' => 'department']),
                'label' => 'Tambah Timeline',
                'icon' => 'fas fa-plus',
                'tone' => 'primary',
            ] : null,
        ])),
        'summary' => $department ? [
            ['label' => 'Total', 'value' => $timelines->count()],
            ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
            ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
        ] : [],
        'sidebar' => [
            'title' => 'Pilih Departemen',
            'icon' => 'fas fa-building',
            'description' => 'Buka agenda berdasarkan departemen yang ingin ditinjau.',
            'items' => $departments->map(fn ($dept) => [
                'href' => route('timelines.department', $dept),
                'label' => $dept->name,
                'active' => $department?->id === $dept->id,
            ])->values(),
        ],
        'items' => $department ? $timelines->map(fn ($timeline) => [
            'title' => $timeline->title,
            'description' => $timeline->description ?: 'Belum ada deskripsi tambahan untuk agenda ini.',
            'color' => $timeline->color ?? '#3B82F6',
            'range' => $timeline->start_date->format('d M Y') . ' - ' . $timeline->end_date->format('d M Y'),
            'scope' => ['label' => 'Departemen', 'tone' => 'info'],
            'status' => $statusFor($timeline),
            'meta' => array_values(array_filter([
                ['icon' => 'fas fa-building', 'label' => $department->name],
                $timeline->program ? ['icon' => 'fas fa-diagram-project', 'label' => $timeline->program->name] : null,
            ])),
        ])->values() : [],
        'emptyState' => $department
            ? [
                'title' => 'Belum ada timeline departemen',
                'text' => 'Belum ada agenda yang terdaftar untuk departemen ini.',
                'action' => $canManage ? [
                    'href' => route('timelines.create', ['type' => 'department', 'department_id' => $department->id]),
                    'label' => 'Tambah Timeline',
                    'icon' => 'fas fa-plus',
                ] : null,
            ]
            : [
                'title' => 'Pilih departemen terlebih dahulu',
                'text' => 'Setelah departemen dipilih, agenda yang relevan akan tampil di panel utama.',
                'action' => null,
            ],
    ];

    $props['breadcrumbs'] = array_values(array_filter($props['breadcrumbs']));
@endphp

<script id="svelte-timeline-collection-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-collection-root"></div>
@endsection
