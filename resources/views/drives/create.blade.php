@extends('layouts.app')

@section('title', 'Tambah Drive')
@section('page-title', 'Tambah Akun Drive')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah Drive',
        'description' => 'Tambahkan akun Google Drive baru agar akses arsip dan dokumen bersama bisa dibagikan dengan struktur yang jelas.',
        'icon' => 'fab fa-google-drive',
        'form' => [
            'action' => route('drives.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('drives.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            [
                'name' => 'name',
                'label' => 'Nama Drive',
                'type' => 'text',
                'required' => true,
                'value' => old('name'),
                'placeholder' => 'contoh: Drive PSDM',
                'error' => $errors->first('name'),
            ],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'value' => old('department_id'),
                'error' => $errors->first('department_id'),
                'placeholder' => '-- Umum (Semua Departemen) --',
                'span' => 'half',
                'options' => $departments->map(fn($department) => ['value' => $department->id, 'label' => $department->name])->values(),
            ],
            [
                'name' => 'email',
                'label' => 'Email Google',
                'type' => 'email',
                'required' => true,
                'value' => old('email'),
                'error' => $errors->first('email'),
                'span' => 'half',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'text',
                'required' => true,
                'value' => old('password'),
                'error' => $errors->first('password'),
                'note' => 'Password ini akan ditampilkan ke user saat mereka membuka akses drive.',
            ],
            [
                'name' => 'drive_url',
                'label' => 'URL Google Drive',
                'type' => 'url',
                'required' => true,
                'value' => old('drive_url'),
                'placeholder' => 'https://drive.google.com/drive/folders/...',
                'error' => $errors->first('drive_url'),
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
