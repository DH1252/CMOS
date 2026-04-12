@extends('layouts.app')

@section('title', 'Timeline')
@section('page-title', 'Timeline')

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
        'title' => 'Semua Timeline',
        'description' => 'Pantau agenda global, departemen, dan program kerja dalam satu daftar yang rapi.',
        'icon' => 'fas fa-calendar-alt',
        'actions' => array_values(array_filter([
            ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
            $canManage ? ['href' => route('timelines.create'), 'label' => 'Tambah Timeline', 'icon' => 'fas fa-plus', 'tone' => 'primary'] : null,
        ])),
        'summary' => [
            ['label' => 'Total', 'value' => $timelines->count()],
            ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
            ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
        ],
        'items' => $timelines->map(function ($timeline) use ($statusFor, $canManage) {
            $scope = match ($timeline->type) {
                'global' => ['label' => 'Global', 'tone' => 'primary'],
                'department' => ['label' => 'Departemen', 'tone' => 'info'],
                default => ['label' => 'Program', 'tone' => 'secondary'],
            };

            $meta = [];

            if ($timeline->department) {
                $meta[] = ['icon' => 'fas fa-building', 'label' => $timeline->department->name];
            }

            if ($timeline->program) {
                $meta[] = ['icon' => 'fas fa-diagram-project', 'label' => $timeline->program->name];
            }

            return [
                'title' => $timeline->title,
                'description' => $timeline->description ?: 'Tidak ada deskripsi tambahan untuk timeline ini.',
                'color' => $timeline->color ?? '#7C3AED',
                'range' => $timeline->start_date->format('d M Y') . ' - ' . $timeline->end_date->format('d M Y'),
                'scope' => $scope,
                'status' => $statusFor($timeline),
                'meta' => $meta,
                'actions' => $canManage ? [
                    [
                        'href' => route('timelines.edit', $timeline),
                        'label' => 'Edit timeline',
                        'icon' => 'fas fa-pen',
                        'tone' => 'secondary',
                        'iconOnly' => true,
                    ],
                    [
                        'href' => route('timelines.destroy', $timeline),
                        'label' => 'Hapus timeline',
                        'icon' => 'fas fa-trash',
                        'tone' => 'danger',
                        'method' => 'POST',
                        'spoofMethod' => 'DELETE',
                        'csrfToken' => csrf_token(),
                        'confirm' => $timeline->title,
                        'iconOnly' => true,
                    ],
                ] : [],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada timeline',
            'text' => 'Timeline organisasi akan tampil di sini setelah dibuat.',
            'action' => $canManage ? [
                'href' => route('timelines.create'),
                'label' => 'Tambah Timeline',
                'icon' => 'fas fa-plus',
            ] : null,
        ],
    ];
@endphp

<script id="svelte-timeline-collection-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-collection-root"></div>
@endsection
