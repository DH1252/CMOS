@extends('layouts.app')

@section('title', 'Edit Timeline')
@section('page-title', 'Edit Timeline')

@section('content')
@php
    $props = [
        'title' => 'Edit Timeline',
        'description' => 'Perbarui judul, konteks, dan rentang agenda agar timeline tetap akurat.',
        'form' => [
            'action' => route('timelines.update', $timeline),
            'method' => 'POST',
            'spoofMethod' => 'PUT',
            'csrfToken' => csrf_token(),
        ],
        'submitLabel' => 'Update Timeline',
        'values' => [
            'title' => old('title', $timeline->title),
            'description' => old('description', $timeline->description),
            'type' => old('type', $timeline->type),
            'department_id' => old('department_id', $timeline->department_id),
            'program_id' => old('program_id', $timeline->program_id),
            'start_date' => old('start_date', $timeline->start_date->format('Y-m-d')),
            'end_date' => old('end_date', $timeline->end_date->format('Y-m-d')),
        ],
        'departments' => $departments->map(fn ($department) => [
            'value' => $department->id,
            'label' => $department->name,
        ])->values(),
        'programs' => $programs->map(fn ($program) => [
            'value' => $program->id,
            'label' => $program->name . ($program->department ? ' (' . $program->department->name . ')' : ''),
        ])->values(),
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
        'cancelAction' => [
            'href' => route('timelines.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
    ];
@endphp

<script id="svelte-timeline-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-timeline-form-root"></div>
@endsection
