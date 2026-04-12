@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
@php
    $props = [
        'title' => "Edit User: {$user->name}",
        'description' => 'Perbarui identitas, peran, atau status akun tanpa meninggalkan konteks kerja.',
        'icon' => 'fas fa-user-pen',
        'form' => [
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('users.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            ['name' => 'name', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'value' => old('name', $user->name), 'error' => $errors->first('name'), 'span' => 'half'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => old('email', $user->email), 'error' => $errors->first('email'), 'span' => 'half'],
            ['name' => 'password', 'label' => 'Password Baru', 'type' => 'password', 'value' => '', 'error' => $errors->first('password'), 'note' => 'Kosongkan jika tidak diubah.', 'span' => 'half'],
            ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'type' => 'password', 'value' => '', 'span' => 'half'],
            [
                'name' => 'role_id',
                'label' => 'Role',
                'type' => 'select',
                'required' => true,
                'value' => old('role_id', $user->role_id),
                'error' => $errors->first('role_id'),
                'span' => 'half',
                'options' => $roles->map(fn($role) => ['value' => $role->id, 'label' => ucfirst($role->name)])->values(),
            ],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'value' => old('department_id', $user->department_id),
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
                'value' => old('status', $user->status),
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
