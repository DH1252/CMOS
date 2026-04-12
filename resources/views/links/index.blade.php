@extends('layouts.app')

@section('title', 'Kumpulan Link')
@section('page-title', 'Kumpulan Link')
@section('page-meta', 'Direktori tautan kerja dan referensi organisasi yang dikelompokkan per kebutuhan operasional.')

@section('content')
@php
    $canManage = auth()->user()->hasRole(['admin', 'bph']);

    $groups = collect($categories)
        ->map(function ($category, $key) use ($linksByCategory, $canManage) {
            $links = $linksByCategory->get($key, collect());

            if ($links->isEmpty()) {
                return null;
            }

            return [
                'name' => $category['name'],
                'icon' => $category['icon'],
                'description' => match ($key) {
                    'template' => 'Template dan format kerja yang sering dipakai lintas kepengurusan.',
                    'tracker' => 'Tautan pelacakan progres, arsip, dan ritme operasional.',
                    'rules' => 'Dokumen rujukan untuk kebijakan, aturan, dan ketertiban kerja.',
                    'form' => 'Akses cepat ke form pengajuan, peminjaman, dan kebutuhan administrasi.',
                    'resource' => 'Perpustakaan file dan referensi penunjang kegiatan organisasi.',
                    default => 'Tautan umum yang sering dibuka oleh pengurus dalam pekerjaan harian.',
                },
                'cards' => $links->map(function ($link) use ($canManage) {
                    return [
                        'title' => $link->title,
                        'description' => $link->description,
                        'href' => $link->url,
                        'icon' => $link->icon ?: 'fas fa-link',
                        'primaryLabel' => 'Buka Link',
                        'badges' => array_values(array_filter([
                            $link->sort_order ? ['label' => 'Urutan ' . $link->sort_order, 'tone' => 'secondary'] : null,
                            $link->creator?->name ? ['label' => $link->creator->name, 'tone' => 'info'] : null,
                        ])),
                        'meta' => array_values(array_filter([
                            parse_url($link->url, PHP_URL_HOST) ? ['text' => parse_url($link->url, PHP_URL_HOST), 'muted' => true] : null,
                        ])),
                        'editHref' => $canManage ? route('links.edit', $link) : null,
                        'deleteAction' => $canManage ? route('links.destroy', $link) : null,
                        'deleteMethod' => 'DELETE',
                        'confirm' => $link->title,
                        'confirmText' => "Hapus link {$link->title}?",
                    ];
                })->values(),
            ];
        })
        ->filter()
        ->values();

    $props = [
        'title' => 'Link Berguna',
        'description' => 'Ruang ini merangkum tautan kerja yang paling sering dipakai pengurus, dari template sampai arsip rujukan.',
        'icon' => 'fas fa-link',
        'csrfToken' => csrf_token(),
        'primaryAction' => $canManage ? [
            'label' => 'Tambah Link',
            'href' => route('links.create'),
            'icon' => 'fas fa-plus',
        ] : null,
        'groups' => $groups,
        'emptyState' => [
            'title' => 'Belum ada link',
            'text' => 'Admin atau BPH belum menambahkan tautan kerja untuk dibagikan ke pengurus.',
        ],
    ];
@endphp

<script id="svelte-link-directory-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-link-directory-root"></div>
@endsection
