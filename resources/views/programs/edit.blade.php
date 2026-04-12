@extends('layouts.app')

@section('title', 'Edit Program')
@section('page-title', 'Edit Program')

@section('content')
@php
    $props = [
        'title' => "Edit Program: {$program->name}",
        'description' => 'Perbarui identitas program, periode, atau departemen pengampu sesuai kebutuhan operasional.',
        'icon' => 'fas fa-diagram-project',
        'form' => [
            'action' => route('programs.update', $program),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('programs.show', $program),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Program', 'type' => 'text', 'required' => true, 'value' => old('name', $program->name), 'error' => $errors->first('name')],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description', $program->description), 'error' => $errors->first('description'), 'rows' => 3],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'required' => true,
                'value' => old('department_id', $program->department_id),
                'error' => $errors->first('department_id'),
                'span' => 'half',
                'options' => $departments->map(fn($department) => ['value' => $department->id, 'label' => $department->name])->values(),
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'value' => old('status', $program->status),
                'error' => $errors->first('status'),
                'span' => 'half',
                'options' => [
                    ['value' => 'planning', 'label' => 'Planning'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],
            ],
            ['name' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true, 'value' => old('start_date', $program->start_date->format('Y-m-d')), 'error' => $errors->first('start_date'), 'span' => 'half'],
            ['name' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true, 'value' => old('end_date', $program->end_date->format('Y-m-d')), 'error' => $errors->first('end_date'), 'span' => 'half'],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
