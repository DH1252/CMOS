@extends('layouts.app')

@section('title', 'Edit Profil')
@section('page-title', 'Edit Profil')

@section('content')
@php
    $errorMap = collect($errors->messages())->map(fn ($messages) => $messages[0])->all();

    $props = [
        'title' => 'Edit Profil',
        'description' => 'Perbarui identitas akun, foto profil, dan keamanan akses Anda.',
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'roleName' => $user->role_name,
            'department' => $user->department?->name,
            'avatarUrl' => $user->avatar_url,
            'joinedAt' => $user->created_at->toIso8601String(),
        ],
        'profileForm' => [
            'action' => route('profile.update'),
            'csrfToken' => csrf_token(),
            'spoofMethod' => 'PUT',
            'values' => [
                'name' => old('name', $user->name),
                'avatarUrl' => $user->avatar_url,
            ],
            'errors' => collect($errorMap)->only(['name', 'avatar'])->all(),
        ],
        'passwordForm' => [
            'action' => route('profile.password.update'),
            'csrfToken' => csrf_token(),
            'spoofMethod' => 'PUT',
            'errors' => collect($errorMap)->only(['current_password', 'password', 'password_confirmation'])->all(),
            'status' => session('status'),
        ],
        'removeAvatarAction' => $user->avatar ? [
            'action' => route('profile.avatar.remove'),
            'csrfToken' => csrf_token(),
        ] : null,
        'backHref' => route('dashboard'),
    ];
@endphp

<script id="svelte-profile-settings-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-profile-settings-root"></div>
@endsection
