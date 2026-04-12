@extends('layouts.app')

@section('title', 'Departemen')
@section('page-title', 'Departemen')
@section('page-meta', 'Lihat struktur departemen, kapasitas anggota, dan keterhubungannya dengan kabinet serta program kerja.')

@section('content')
@php
    $props = [
        'title' => 'Daftar Departemen',
        'description' => 'Setiap departemen membawa anggota, program kerja, dan konteks operasionalnya masing-masing.',
        'icon' => 'fas fa-building',
        'csrfToken' => csrf_token(),
        'enableDataTable' => true,
        'primaryAction' => [
            'label' => 'Tambah Departemen',
            'href' => route('departments.create'),
            'icon' => 'fas fa-plus',
        ],
        'columns' => [
            ['label' => 'Nama'],
            ['label' => 'Deskripsi'],
            ['label' => 'Kabinet'],
            ['label' => 'Anggota'],
            ['label' => 'Proker'],
            ['label' => 'Status'],
            ['label' => 'Aksi', 'width' => '120px'],
        ],
        'rows' => $departments->map(function ($department) {
            return [
                'cells' => [
                    ['type' => 'text', 'text' => $department->name, 'href' => route('departments.show', $department), 'className' => 'fw-semibold'],
                    ['type' => 'text', 'text' => \Illuminate\Support\Str::limit($department->description ?: '-', 58), 'muted' => true],
                    ['type' => 'text', 'text' => $department->cabinet?->name ?? '-', 'muted' => !$department->cabinet],
                    ['type' => 'badge', 'label' => "{$department->users_count} orang", 'tone' => 'info'],
                    ['type' => 'badge', 'label' => "{$department->programs_count} proker", 'tone' => 'primary'],
                    ['type' => 'badge', 'label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
                    [
                        'type' => 'actions',
                        'items' => [
                            ['href' => route('departments.show', $department), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                            ['href' => route('departments.edit', $department), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            [
                                'action' => route('departments.destroy', $department),
                                'method' => 'DELETE',
                                'label' => 'Hapus',
                                'icon' => 'fas fa-trash',
                                'tone' => 'danger',
                                'confirm' => $department->name,
                                'confirmText' => "Hapus departemen {$department->name}?",
                            ],
                        ],
                    ],
                ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada departemen',
            'text' => 'Tambahkan departemen untuk membangun struktur kerja organisasi.',
        ],
    ];
@endphp

<script id="svelte-crud-table-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-crud-table-root"></div>
@endsection
