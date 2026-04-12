@extends('layouts.app')

@section('title', 'Edit Drive')
@section('page-title', 'Edit Akun Drive')

@section('content')
@php
    $props = [
        'title' => "Edit Drive: {$drive->name}",
        'description' => 'Perbarui kredensial, penempatan departemen, dan status akun Drive organisasi.',
        'icon' => 'fab fa-google-drive',
        'form' => [
            'action' => route('drives.update', $drive),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
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
                'value' => old('name', $drive->name),
                'error' => $errors->first('name'),
            ],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'value' => old('department_id', $drive->department_id),
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
                'value' => old('email', $drive->email),
                'error' => $errors->first('email'),
                'span' => 'half',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'text',
                'required' => true,
                'value' => old('password', $drive->password),
                'error' => $errors->first('password'),
            ],
            [
                'name' => 'drive_url',
                'label' => 'URL Google Drive',
                'type' => 'url',
                'required' => true,
                'value' => old('drive_url', $drive->drive_url),
                'error' => $errors->first('drive_url'),
            ],
            [
                'name' => 'is_active',
                'label' => 'Aktif',
                'type' => 'checkbox',
                'value' => old('is_active', $drive->is_active),
                'error' => $errors->first('is_active'),
                'note' => 'Nonaktifkan bila akun Drive sudah tidak dipakai oleh pengurus.',
                'span' => 'half',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
