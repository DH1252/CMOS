@extends('layouts.app')

@section('title', 'Proker Saya')
@section('page-title', 'Proker Saya')
@section('page-meta', 'Ringkasan program yang Anda ikuti sebagai PIC atau anggota aktif.')

@section('content')
@php
    $currentUser = auth()->user();

    $props = [
        'title' => 'Daftar Proker Saya',
        'description' => 'Fokuskan perhatian pada program yang secara langsung menjadi tanggung jawab Anda.',
        'icon' => 'fas fa-folder-open',
        'csrfToken' => csrf_token(),
        'columns' => [
            ['label' => 'Nama Proker'],
            ['label' => 'Departemen'],
            ['label' => 'Peran'],
            ['label' => 'Status'],
            ['label' => 'Progress'],
            ['label' => 'Periode'],
        ],
        'rows' => $programs->map(function ($program) use ($currentUser) {
            $isPic = $program->pics->contains('id', $currentUser->id);
            $isMember = $program->members->contains('id', $currentUser->id);

            return [
                'cells' => [
                    ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                    ['type' => 'text', 'text' => $program->department?->name ?? '-', 'muted' => !$program->department],
                    ['type' => 'badges', 'items' => array_values(array_filter([
                        $isPic ? ['label' => 'PIC', 'tone' => 'primary', 'icon' => 'fas fa-star'] : null,
                        !$isPic && $isMember ? ['label' => 'Member', 'tone' => 'info'] : null,
                    ]))],
                    ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match($program->status) {
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }],
                    ['type' => 'progress', 'value' => $program->progress, 'label' => "{$program->progress}%"],
                    ['type' => 'text', 'text' => $program->start_date->format('d M') . ' - ' . $program->end_date->format('d M Y'), 'muted' => true],
                ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada program yang diikuti',
            'text' => 'Program yang melibatkan Anda akan muncul di sini.',
        ],
    ];
@endphp

<script id="svelte-crud-table-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-crud-table-root"></div>
@endsection
