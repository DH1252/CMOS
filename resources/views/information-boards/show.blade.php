@extends('layouts.app')

@section('title', $informationBoard->title)
@section('page-title', 'Detail Artikel Informasi')
@section('page-meta', 'Baca artikel informasi lengkap beserta konteks publikasi dan artikel terbaru lainnya.')

@section('content')
@php
    $canManage = auth()->user()->isAdmin() || $informationBoard->user_id === auth()->id();

    $props = [
        'article' => [
            'title' => $informationBoard->title,
            'coverImage' => $informationBoard->cover_image_url,
            'badges' => collect([
                [
                    'label' => ucfirst($informationBoard->status),
                    'tone' => $informationBoard->status === 'published' ? 'success' : 'secondary',
                ],
            ])->merge($informationBoard->categories->map(fn($category) => [
                'label' => $category->name,
                'tone' => 'info',
            ]))->values(),
            'metadata' => [
                ['label' => $informationBoard->user?->name ?? '-', 'icon' => 'fas fa-user'],
                ['label' => optional($informationBoard->published_at)?->toIso8601String() ?? $informationBoard->created_at->toIso8601String(), 'icon' => 'fas fa-calendar'],
            ],
            'contentHtml' => $informationBoard->content,
            'backAction' => [
                'href' => route('information-boards.index'),
                'label' => 'Kembali',
                'icon' => 'fas fa-arrow-left',
            ],
            'editAction' => $canManage ? [
                'href' => route('information-boards.edit', $informationBoard),
                'label' => 'Edit Artikel',
                'icon' => 'fas fa-edit',
            ] : null,
        ],
        'latestArticles' => $latestArticles->map(fn($article) => [
            'title' => $article->title,
            'date' => optional($article->published_at)?->toIso8601String(),
            'href' => route('information-boards.show', $article),
        ])->values(),
    ];
@endphp

<script id="svelte-information-board-show-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-information-board-show-root"></div>
@endsection
