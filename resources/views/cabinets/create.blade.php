@extends('layouts.app')

@section('title', 'Tambah Kabinet')
@section('page-title', 'Tambah Kabinet')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah Kabinet',
        'description' => 'Buat periode kepengurusan baru dengan penamaan yang rapi agar transisi organisasi tetap tertelusur.',
        'icon' => 'fas fa-landmark',
        'form' => [
            'action' => route('cabinets.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
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
                'value' => old('name'),
                'placeholder' => 'contoh: Kabinet Harmoni',
                'error' => $errors->first('name'),
            ],
            [
                'name' => 'year',
                'label' => 'Tahun Kepengurusan',
                'type' => 'text',
                'required' => true,
                'value' => old('year'),
                'placeholder' => 'contoh: 2025/2026',
                'error' => $errors->first('year'),
                'span' => 'half',
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'value' => old('status', 'active'),
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
