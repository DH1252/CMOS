@extends('layouts.app')

@section('title', 'Edit Artikel')
@section('page-title', 'Edit Artikel Papan Informasi')
@section('page-meta', 'Perbarui artikel informasi, kategori, dan waktu publikasinya tanpa keluar dari alur editorial.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/trix@2.1.3/dist/trix.css">
@endpush

@section('content')
@php
    $props = [
        'title' => "Edit Artikel: {$informationBoard->title}",
        'description' => 'Perbarui isi artikel, kategori, dan metadata publikasinya dari satu halaman editor.',
        'icon' => 'fas fa-edit',
        'form' => [
            'action' => route('information-boards.update', $informationBoard),
            'method' => 'PUT',
            'csrfToken' => csrf_token(),
            'enctype' => 'multipart/form-data',
            'submitLabel' => 'Update',
        ],
        'article' => [
            'title' => old('title', $informationBoard->title),
            'excerpt' => old('excerpt', $informationBoard->excerpt),
            'content' => old('content', $informationBoard->content),
            'status' => old('status', $informationBoard->status),
            'publishedAt' => old('published_at', optional($informationBoard->published_at)->format('Y-m-d\\TH:i')),
            'metaTitle' => old('meta_title', $informationBoard->meta_title),
            'metaDescription' => old('meta_description', $informationBoard->meta_description),
            'categoryIds' => old('category_ids', $informationBoard->categories->pluck('id')->all()),
            'coverImage' => $informationBoard->cover_image_url,
        ],
        'categories' => $categories->map(fn($category) => ['value' => $category->id, 'label' => $category->name])->values(),
        'errors' => [
            'title' => $errors->first('title'),
            'excerpt' => $errors->first('excerpt'),
            'content' => $errors->first('content'),
            'status' => $errors->first('status'),
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
        'dangerAction' => [
            'action' => route('information-boards.destroy', $informationBoard),
            'method' => 'DELETE',
            'label' => 'Hapus',
            'icon' => 'fas fa-trash',
            'confirm' => $informationBoard->title,
            'confirmText' => "Hapus artikel {$informationBoard->title}?",
        ],
        'editorId' => 'information-board-edit-content',
    ];
@endphp

<script id="svelte-information-board-editor-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-information-board-editor-root"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/trix@2.1.3/dist/trix.umd.min.js"></script>
@endpush
