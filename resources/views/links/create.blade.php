@extends('layouts.app')

@section('title', 'Tambah Link')
@section('page-title', 'Tambah Link')

@section('content')
@php
    $props = [
        'title' => 'Form Tambah Link',
        'description' => 'Tambahkan tautan baru agar dokumen, template, dan rujukan penting lebih mudah dijangkau seluruh pengurus.',
        'icon' => 'fas fa-link',
        'form' => [
            'action' => route('links.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Simpan',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('links.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'fields' => [
            [
                'name' => 'title',
                'label' => 'Judul Link',
                'type' => 'text',
                'required' => true,
                'value' => old('title'),
                'placeholder' => 'contoh: Template SOP Medfo',
                'error' => $errors->first('title'),
            ],
            [
                'name' => 'description',
                'label' => 'Deskripsi',
                'type' => 'textarea',
                'value' => old('description'),
                'placeholder' => 'Deskripsi singkat tentang link ini',
                'error' => $errors->first('description'),
                'rows' => 2,
            ],
            [
                'name' => 'url',
                'label' => 'URL',
                'type' => 'url',
                'required' => true,
                'value' => old('url'),
                'placeholder' => 'https://...',
                'error' => $errors->first('url'),
            ],
            [
                'name' => 'category',
                'label' => 'Kategori',
                'type' => 'select',
                'required' => true,
                'value' => old('category'),
                'error' => $errors->first('category'),
                'span' => 'half',
                'options' => collect($categories)->map(fn($cat, $key) => ['value' => $key, 'label' => $cat['name']])->values(),
            ],
            [
                'name' => 'sort_order',
                'label' => 'Urutan',
                'type' => 'number',
                'value' => old('sort_order', 0),
                'error' => $errors->first('sort_order'),
                'span' => 'half',
                'min' => 0,
            ],
            [
                'name' => 'icon',
                'label' => 'Icon (Font Awesome)',
                'type' => 'text',
                'value' => old('icon', 'fas fa-link'),
                'placeholder' => 'fas fa-link',
                'error' => $errors->first('icon'),
                'note' => 'Contoh: fas fa-file-alt, fas fa-chart-line, fas fa-gavel.',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
