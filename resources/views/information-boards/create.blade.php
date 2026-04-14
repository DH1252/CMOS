@extends('layouts.app')

@section('title', 'Tulis Artikel')
@section('page-title', 'Tulis Artikel Papan Informasi')
@section('page-meta', 'Susun artikel informasi baru lengkap dengan kategori, status publikasi, dan metadata SEO.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/trix@2.1.3/dist/trix.css">
@endpush

@section('content')
@php
    $props = [
        'title' => 'Form Artikel Baru',
        'description' => 'Tulis artikel informasi baru dengan struktur editorial yang rapi untuk dibaca pengurus.',
        'icon' => 'fas fa-pen',
        'form' => [
            'action' => route('information-boards.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'enctype' => 'multipart/form-data',
            'submitLabel' => 'Simpan',
        ],
        'article' => [
            'title' => old('title'),
            'excerpt' => old('excerpt'),
            'content' => old('content'),
            'status' => old('status', 'published'),
            'publishMode' => old('publish_mode', 'immediately'),
            'publishedAt' => old('published_at'),
            'metaTitle' => old('meta_title'),
            'metaDescription' => old('meta_description'),
            'categoryIds' => old('category_ids', []),
            'coverImage' => null,
        ],
        'categories' => $categories->map(fn($category) => ['value' => $category->id, 'label' => $category->name])->values(),
        'errors' => [
            'title' => $errors->first('title'),
            'excerpt' => $errors->first('excerpt'),
            'content' => $errors->first('content'),
            'status' => $errors->first('status'),
            'publish_mode' => $errors->first('publish_mode'),
            'published_at' => $errors->first('published_at'),
            'meta_title' => $errors->first('meta_title'),
            'meta_description' => $errors->first('meta_description'),
            'cover_image' => $errors->first('cover_image'),
            'category_ids' => $errors->first('category_ids'),
            'category_ids_items' => $errors->first('category_ids.*'),
        ],
        'cancelAction' => [
            'href' => route('information-boards.index'),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
        'editorId' => 'information-board-create-content',
    ];
@endphp

<script id="svelte-information-board-editor-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-information-board-editor-root"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/trix@2.1.3/dist/trix.umd.min.js"></script>
@endpush
