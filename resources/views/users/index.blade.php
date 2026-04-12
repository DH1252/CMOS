@extends('layouts.app')

@section('title', 'Data User')
@section('page-title', 'Data User')
@section('page-meta', 'Kelola akun anggota, peran organisasi, dan status keaktifan dalam satu tabel operasional.')

@section('content')
@php
    $props = [
        'title' => 'Daftar User',
        'description' => 'Akun organisasi dikelola di sini untuk kebutuhan akses, departemen, dan peran kerja.',
        'icon' => 'fas fa-users',
        'csrfToken' => csrf_token(),
        'enableDataTable' => true,
        'primaryAction' => [
            'label' => 'Tambah User',
            'href' => route('users.create'),
            'icon' => 'fas fa-plus',
        ],
        'columns' => [
            ['label' => 'Nama'],
            ['label' => 'Email'],
            ['label' => 'Role'],
            ['label' => 'Departemen'],
            ['label' => 'Status'],
            ['label' => 'Aksi', 'width' => '120px'],
        ],
        'rows' => $users->map(function ($user) {
            $roleTone = match($user->role?->name) {
                'admin' => 'danger',
                'bph' => 'warning',
                'kabinet' => 'info',
                default => 'secondary',
            };

            return [
                'cells' => [
                    [
                        'type' => 'avatar',
                        'image' => $user->avatar_url,
                        'title' => $user->name,
                        'href' => route('users.show', $user),
                    ],
                    ['type' => 'text', 'text' => $user->email],
                    ['type' => 'badge', 'label' => ucfirst($user->role?->name ?? '-'), 'tone' => $roleTone],
                    ['type' => 'text', 'text' => $user->department?->name ?? '-', 'muted' => !$user->department],
                    ['type' => 'badge', 'label' => ucfirst($user->status), 'tone' => $user->status === 'active' ? 'success' : 'secondary'],
                    [
                        'type' => 'actions',
                        'items' => array_values(array_filter([
                            ['href' => route('users.show', $user), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                            ['href' => route('users.edit', $user), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            $user->id !== auth()->id() ? [
                                'action' => route('users.destroy', $user),
                                'method' => 'DELETE',
                                'label' => 'Hapus',
                                'icon' => 'fas fa-trash',
                                'tone' => 'danger',
                                'confirm' => $user->name,
                                'confirmText' => "Hapus akun {$user->name}?",
                            ] : null,
                        ])),
                    ],
                ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada user',
            'text' => 'Tambahkan akun baru untuk mulai mengelola organisasi di dalam sistem.',
        ],
    ];
@endphp

<script id="svelte-crud-table-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-crud-table-root"></div>
@endsection
