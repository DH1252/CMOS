@extends('layouts.app')

@section('title', 'Google Drive')
@section('page-title', 'Google Drive Organisasi')
@section('page-meta', 'Akses akun Google Drive organisasi berdasarkan departemen dan kebutuhan arsip kerja.')

@section('content')
@php
    $canManage = auth()->user()->isAdmin();

    $props = [
        'title' => 'Daftar Drive',
        'description' => 'Pilih akun Google Drive berdasarkan departemen, lalu ambil kredensialnya dari panel akses tanpa harus membuka modal terpisah.',
        'icon' => 'fab fa-google-drive',
        'csrfToken' => csrf_token(),
        'primaryAction' => $canManage ? [
            'label' => 'Tambah Drive',
            'href' => route('drives.create'),
            'icon' => 'fas fa-plus',
        ] : null,
        'groups' => $drivesByDept->map(function ($drives, $deptName) use ($canManage) {
            return [
                'name' => $deptName,
                'icon' => 'fab fa-google-drive',
                'description' => $deptName === 'Umum'
                    ? 'Akun bersama untuk kebutuhan lintas departemen dan arsip umum organisasi.'
                    : "Akun Drive yang dipakai khusus untuk operasional {$deptName}.",
                'cards' => $drives->map(function ($drive) use ($canManage) {
                    return [
                        'id' => $drive->id,
                        'title' => $drive->name,
                        'description' => 'Gunakan akun ini untuk membuka folder kerja dan arsip yang terkait dengan aktivitas organisasi.',
                        'href' => $drive->drive_url,
                        'icon' => 'fab fa-google-drive',
                        'email' => $drive->email,
                        'password' => $drive->password,
                        'badges' => [
                            ['label' => $drive->department?->name ?? 'Umum', 'tone' => $drive->department ? 'info' : 'secondary'],
                        ],
                        'meta' => [
                            ['text' => $drive->is_active ? 'Akun aktif dan siap dipakai' : 'Akun nonaktif', 'muted' => !$drive->is_active],
                        ],
                        'editHref' => $canManage ? route('drives.edit', $drive) : null,
                        'deleteAction' => $canManage ? route('drives.destroy', $drive) : null,
                        'deleteMethod' => 'DELETE',
                        'confirm' => $drive->name,
                        'confirmText' => "Hapus akun drive {$drive->name}?",
                    ];
                })->values(),
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada drive',
            'text' => 'Admin belum menambahkan akun Google Drive untuk kebutuhan organisasi.',
        ],
    ];
@endphp

<script id="svelte-drive-directory-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-drive-directory-root"></div>
@endsection
