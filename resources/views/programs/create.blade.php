@extends('layouts.app')

@section('title', 'Tambah Program')
@section('page-title', 'Tambah Program Kerja')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah Program',
        'description' => 'Tetapkan identitas program, departemen pengampu, dan periode kerjanya sejak awal.',
        'icon' => 'fas fa-diagram-project',
        'form' => [
            'action' => route('programs.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('programs.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Program', 'type' => 'text', 'required' => true, 'value' => old('name'), 'error' => $errors->first('name')],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description'), 'error' => $errors->first('description'), 'rows' => 3],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'required' => true,
                'value' => old('department_id'),
                'error' => $errors->first('department_id'),
                'placeholder' => '-- Pilih Departemen --',
                'span' => 'half',
                'options' => $departments->map(fn($department) => ['value' => $department->id, 'label' => $department->name])->values(),
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'value' => old('status', 'planning'),
                'error' => $errors->first('status'),
                'span' => 'half',
                'options' => [
                    ['value' => 'planning', 'label' => 'Planning'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],
            ],
            ['name' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true, 'value' => old('start_date'), 'error' => $errors->first('start_date'), 'span' => 'half'],
            ['name' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true, 'value' => old('end_date'), 'error' => $errors->first('end_date'), 'span' => 'half'],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
