@extends('layouts.app')

@section('title', 'Edit Departemen')
@section('page-title', 'Edit Departemen')

@section('content')
@php
    $props = [
        'title' => "Edit Departemen: {$department->name}",
        'description' => 'Perbarui identitas departemen tanpa memutus konteks kabinet dan operasionalnya.',
        'icon' => 'fas fa-building',
        'form' => [
            'action' => route('departments.update', $department),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('departments.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Departemen', 'type' => 'text', 'required' => true, 'value' => old('name', $department->name), 'error' => $errors->first('name')],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description', $department->description), 'error' => $errors->first('description'), 'rows' => 3],
            [
                'name' => 'cabinet_id',
                'label' => 'Kabinet',
                'type' => 'select',
                'value' => old('cabinet_id', $department->cabinet_id),
                'error' => $errors->first('cabinet_id'),
                'placeholder' => '-- Pilih Kabinet --',
                'span' => 'half',
                'options' => $cabinets->map(fn($cabinet) => ['value' => $cabinet->id, 'label' => "{$cabinet->name} ({$cabinet->year})"])->values(),
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'value' => old('status', $department->status),
                'error' => $errors->first('status'),
                'span' => 'half',
                'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
