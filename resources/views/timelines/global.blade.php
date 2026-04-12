@extends('layouts.app')

@section('title', 'Timeline Global')
@section('page-title', 'Timeline Global Organisasi')

@section('content')
@php
    $today = now()->toDateString();
    $canCreate = auth()->user()->hasRole(['admin', 'bph']);

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
        'title' => 'Timeline Global',
        'description' => 'Agenda strategis organisasi yang jadi acuan lintas departemen.',
        'icon' => 'fas fa-globe',
        'breadcrumbs' => [
            ['label' => 'Timeline', 'href' => route('timelines.index')],
            ['label' => 'Global'],
        ],
        'actions' => array_values(array_filter([
            ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
            $canCreate ? ['href' => route('timelines.create', ['type' => 'global']), 'label' => 'Tambah Timeline', 'icon' => 'fas fa-plus', 'tone' => 'primary'] : null,
        ])),
        'summary' => [
            ['label' => 'Total Global', 'value' => $timelines->count()],
            ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
            ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
        ],
        'items' => $timelines->map(fn ($timeline) => [
            'title' => $timeline->title,
            'description' => $timeline->description ?: 'Agenda global tanpa deskripsi tambahan.',
            'color' => $timeline->color ?? '#7C3AED',
            'range' => $timeline->start_date->format('d M Y') . ' - ' . $timeline->end_date->format('d M Y'),
            'scope' => ['label' => 'Global', 'tone' => 'primary'],
            'status' => $statusFor($timeline),
            'meta' => [],
        ])->values(),
        'emptyState' => [
            'title' => 'Belum ada timeline global',
            'text' => 'Agenda global organisasi belum ditambahkan.',
            'action' => $canCreate ? [
                'href' => route('timelines.create', ['type' => 'global']),
                'label' => 'Tambah Timeline',
                'icon' => 'fas fa-plus',
            ] : null,
        ],
    ];
@endphp

<script id="svelte-timeline-collection-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-collection-root"></div>
@endsection
