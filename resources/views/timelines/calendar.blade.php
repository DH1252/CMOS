@extends('layouts.app')

@section('title', 'Kalender')
@section('page-title', 'Kalender Timeline')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
@endpush

@section('content')
@php
    $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

    $props = [
        'title' => 'Kalender Timeline',
        'description' => 'Pantau timeline, program kerja, dan deadline task dalam satu kalender interaktif.',
        'listAction' => [
            'href' => route('timelines.index'),
            'label' => 'List View',
            'icon' => 'fas fa-list',
        ],
        'createAction' => $canManage ? [
            'href' => route('timelines.create'),
            'label' => 'Tambah Timeline',
            'icon' => 'fas fa-plus',
        ] : null,
        'eventsUrl' => route('timelines.calendar.data'),
        'locale' => 'id',
        'legend' => [
            ['label' => 'Timeline Global', 'color' => '#7751DE'],
            ['label' => 'Timeline Departemen', 'color' => '#D4A017'],
            ['label' => 'Program Kerja', 'color' => '#3F7A50'],
            ['label' => 'Deadline Task', 'color' => '#A96B12'],
        ],
    ];
@endphp

<script id="svelte-timeline-calendar-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-calendar-root"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
@endpush
