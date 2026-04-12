@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah User',
        'description' => 'Buat akun baru dan tetapkan peran organisasi serta departemen yang relevan.',
        'icon' => 'fas fa-user-plus',
        'form' => [
            'action' => route('users.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('users.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'value' => old('name'), 'error' => $errors->first('name'), 'span' => 'half'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => old('email'), 'error' => $errors->first('email'), 'span' => 'half'],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'value' => '', 'error' => $errors->first('password'), 'note' => 'Minimal 8 karakter.', 'span' => 'half'],
            ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'type' => 'password', 'required' => true, 'value' => '', 'span' => 'half'],
            [
                'name' => 'role_id',
                'label' => 'Role',
                'type' => 'select',
                'required' => true,
                'value' => old('role_id'),
                'error' => $errors->first('role_id'),
                'placeholder' => '-- Pilih Role --',
                'span' => 'half',
                'options' => $roles->map(fn($role) => ['value' => $role->id, 'label' => ucfirst($role->name)])->values(),
            ],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
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
                'value' => old('status', 'active'),
                'error' => $errors->first('status'),
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
