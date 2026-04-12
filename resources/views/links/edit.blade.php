@extends('layouts.app')

@section('title', 'Edit Link')
@section('page-title', 'Edit Link')

@section('content')
@php
    $props = [
        'title' => "Edit Link: {$link->title}",
        'description' => 'Perbarui tautan, kategori, dan status aktifnya agar direktori tetap rapi dan relevan.',
        'icon' => 'fas fa-link',
        'form' => [
            'action' => route('links.update', $link),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'submitLabel' => 'Update',
            'submitIcon' => 'fas fa-save',
        ],
        'cancelAction' => [
            'href' => route('links.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'dangerAction' => [
            'action' => route('links.destroy', $link),
            'method' => 'DELETE',
            'label' => 'Hapus',
            'icon' => 'fas fa-trash',
            'confirm' => $link->title,
            'confirmText' => "Hapus link {$link->title}?",
        ],
        'fields' => [
            [
                'name' => 'title',
                'label' => 'Judul Link',
                'type' => 'text',
                'required' => true,
                'value' => old('title', $link->title),
                'error' => $errors->first('title'),
            ],
            [
                'name' => 'description',
                'label' => 'Deskripsi',
                'type' => 'textarea',
                'value' => old('description', $link->description),
                'error' => $errors->first('description'),
                'rows' => 2,
            ],
            [
                'name' => 'url',
                'label' => 'URL',
                'type' => 'url',
                'required' => true,
                'value' => old('url', $link->url),
                'error' => $errors->first('url'),
            ],
            [
                'name' => 'category',
                'label' => 'Kategori',
                'type' => 'select',
                'required' => true,
                'value' => old('category', $link->category),
                'error' => $errors->first('category'),
                'span' => 'half',
                'options' => collect($categories)->map(fn($cat, $key) => ['value' => $key, 'label' => $cat['name']])->values(),
            ],
            [
                'name' => 'sort_order',
                'label' => 'Urutan',
                'type' => 'number',
                'value' => old('sort_order', $link->sort_order),
                'error' => $errors->first('sort_order'),
                'span' => 'half',
                'min' => 0,
            ],
            [
                'name' => 'icon',
                'label' => 'Icon (Font Awesome)',
                'type' => 'text',
                'value' => old('icon', $link->icon),
                'error' => $errors->first('icon'),
                'note' => 'Gunakan kelas Font Awesome yang konsisten dengan kategori link.',
            ],
            [
                'name' => 'is_active',
                'label' => 'Aktif',
                'type' => 'checkbox',
                'value' => old('is_active', $link->is_active),
                'error' => $errors->first('is_active'),
                'note' => 'Nonaktifkan bila link tidak lagi perlu tampil di direktori.',
                'span' => 'half',
            ],
        ],
    ];
@endphp

<script id="svelte-entity-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-entity-form-root"></div>
@endsection
