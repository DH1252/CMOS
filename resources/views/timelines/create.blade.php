@extends('layouts.app')

@section('title', 'Tambah Timeline')
@section('page-title', 'Tambah Timeline')

@section('content')
@php
    $selectedType = old('type', $type ?? 'global');

    $props = [
        'title' => 'Form Tambah Timeline',
        'description' => 'Susun agenda baru untuk organisasi, departemen, atau program kerja dengan rentang waktu yang jelas.',
        'form' => [
            'action' => route('timelines.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
        ],
        'submitLabel' => 'Simpan Timeline',
        'values' => [
            'title' => old('title'),
            'description' => old('description'),
            'type' => $selectedType,
            'department_id' => old('department_id', $selectedType === 'department' ? $departmentId : null),
            'program_id' => old('program_id', $selectedType === 'program' ? $programId : null),
            'start_date' => old('start_date'),
            'end_date' => old('end_date'),
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
