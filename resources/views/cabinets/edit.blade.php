@extends('layouts.app')

@section('title', 'Edit Kabinet')
@section('page-title', 'Edit Kabinet')

@section('content')
@php
    $props = [
        'title' => "Edit Kabinet: {$cabinet->name}",
        'description' => 'Perbarui periode kepengurusan dan status kabinet tanpa menghilangkan konteks departemen yang sudah terhubung.',
        'icon' => 'fas fa-landmark',
        'form' => [
            'action' => route('cabinets.update', $cabinet),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('cabinets.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            [
                'name' => 'name',
                'label' => 'Nama Kabinet',
                'type' => 'text',
                'required' => true,
                'value' => old('name', $cabinet->name),
                'error' => $errors->first('name'),
            ],
            [
                'name' => 'year',
                'label' => 'Tahun Kepengurusan',
                'type' => 'text',
                'required' => true,
                'value' => old('year', $cabinet->year),
                'error' => $errors->first('year'),
                'span' => 'half',
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'value' => old('status', $cabinet->status),
                'error' => $errors->first('status'),
                'span' => 'half',
                'note' => 'Hanya satu kabinet yang bisa active pada satu waktu.',
                'options' => [
                    ['value' => 'active', 'label' => 'Active (Periode Berjalan)'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
