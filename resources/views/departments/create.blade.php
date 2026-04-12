@extends('layouts.app')

@section('title', 'Tambah Departemen')
@section('page-title', 'Tambah Departemen')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah Departemen',
        'description' => 'Buat departemen baru dan hubungkan ke kabinet aktif yang relevan.',
        'icon' => 'fas fa-building',
        'form' => [
            'action' => route('departments.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('departments.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Departemen', 'type' => 'text', 'required' => true, 'value' => old('name'), 'error' => $errors->first('name')],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description'), 'error' => $errors->first('description'), 'rows' => 3],
            [
                'name' => 'cabinet_id',
                'label' => 'Kabinet',
                'type' => 'select',
                'value' => old('cabinet_id'),
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
                'value' => old('status', 'active'),
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
