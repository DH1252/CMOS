@extends('layouts.app')

@section('title', 'Kabinet')
@section('page-title', 'Kabinet')
@section('page-meta', 'Kelola periode kepengurusan, status kabinet, dan jumlah departemen yang berjalan di dalamnya.')

@section('content')
@php
    $props = [
        'title' => 'Daftar Kabinet',
        'description' => 'Struktur kepengurusan ditata per periode agar perpindahan kabinet, departemen, dan status aktif tetap terbaca jelas.',
        'icon' => 'fas fa-landmark',
        'csrfToken' => csrf_token(),
        'enableDataTable' => true,
        'primaryAction' => [
            'label' => 'Tambah Kabinet',
            'href' => route('cabinets.create'),
            'icon' => 'fas fa-plus',
        ],
        'columns' => [
            ['label' => 'Kabinet'],
            ['label' => 'Departemen'],
            ['label' => 'Status'],
            ['label' => 'Aksi', 'width' => '120px'],
        ],
        'rows' => $cabinets->map(function ($cabinet) {
            return [
                'cells' => [
                    [
                        'type' => 'stack',
                        'lines' => [
                            ['text' => $cabinet->name, 'href' => route('cabinets.show', $cabinet), 'className' => 'fw-semibold'],
                            ['text' => $cabinet->year, 'muted' => true],
                        ],
                    ],
                    ['type' => 'badge', 'label' => "{$cabinet->departments_count} departemen", 'tone' => 'info'],
                    [
                        'type' => 'badge',
                        'label' => $cabinet->status === 'active' ? 'Active' : 'Inactive',
                        'tone' => $cabinet->status === 'active' ? 'success' : 'secondary',
                        'icon' => $cabinet->status === 'active' ? 'fas fa-star' : null,
                    ],
                    [
                        'type' => 'actions',
                        'items' => [
                            ['href' => route('cabinets.show', $cabinet), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                            ['href' => route('cabinets.edit', $cabinet), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            [
                                'action' => route('cabinets.destroy', $cabinet),
                                'method' => 'DELETE',
                                'label' => 'Hapus',
                                'icon' => 'fas fa-trash',
                                'tone' => 'danger',
                                'confirm' => $cabinet->name,
                                'confirmText' => "Hapus kabinet {$cabinet->name}?",
                            ],
                        ],
                    ],
                ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada kabinet',
            'text' => 'Tambahkan periode kepengurusan baru untuk mulai menyusun struktur organisasi.',
        ],
    ];
@endphp

<script id="svelte-crud-table-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-crud-table-root"></div>
@endsection
