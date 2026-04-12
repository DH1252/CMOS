@extends('layouts.app')

@section('title', 'Timeline Program')
@section('page-title', 'Timeline ' . $program->name)

@section('content')
@php
    $today = now()->toDateString();

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
        'title' => 'Agenda Program',
        'description' => 'Rangkaian agenda untuk program ' . $program->name . ($program->department ? ' di departemen ' . $program->department->name : '') . '.',
        'icon' => 'fas fa-diagram-project',
        'breadcrumbs' => [
            ['label' => 'Timeline', 'href' => route('timelines.index')],
            ['label' => $program->name],
        ],
        'actions' => [
            ['href' => route('programs.show', $program), 'label' => 'Kembali ke Program', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
        ],
        'summary' => [
            ['label' => 'Total', 'value' => $timelines->count()],
            ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
            ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
        ],
        'items' => $timelines->map(fn ($timeline) => [
            'title' => $timeline->title,
            'description' => $timeline->description ?: 'Belum ada deskripsi tambahan untuk agenda program ini.',
            'color' => $timeline->color ?? '#10B981',
            'range' => $timeline->start_date->format('d M Y') . ' - ' . $timeline->end_date->format('d M Y'),
            'scope' => ['label' => 'Program', 'tone' => 'secondary'],
            'status' => $statusFor($timeline),
            'meta' => array_values(array_filter([
                $program->department ? ['icon' => 'fas fa-building', 'label' => $program->department->name] : null,
                ['icon' => 'fas fa-diagram-project', 'label' => $program->name],
            ])),
        ])->values(),
        'emptyState' => [
            'title' => 'Belum ada timeline program',
            'text' => 'Timeline untuk program ini belum ditambahkan.',
            'action' => null,
        ],
    ];
@endphp

<script id="svelte-timeline-collection-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-collection-root"></div>
@endsection
