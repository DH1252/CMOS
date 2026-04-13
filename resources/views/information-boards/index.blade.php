@extends('layouts.app')

@section('title', 'Papan Informasi')
@section('page-title', 'Papan Informasi')
@section('page-meta', 'Artikel internal, status publikasi, dan arsip.')

@section('content')
@php
    $props = [
        'title' => 'Manajemen Artikel Informasi',
        'description' => 'Kelola artikel dan status publikasi.',
        'icon' => 'fas fa-newspaper',
        'csrfToken' => csrf_token(),
        'primaryAction' => [
            'label' => 'Tulis Artikel',
            'href' => route('information-boards.create'),
            'icon' => 'fas fa-plus',
        ],
        'filters' => [
            'action' => route('information-boards.index'),
            'query' => $search,
            'status' => $status,
            'category' => $category,
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
            ],
            'categoryOptions' => $categories->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])->values(),
        ],
        'stats' => [
            ['label' => 'Total Artikel', 'value' => $totalCount, 'icon' => 'fas fa-newspaper', 'tone' => 'primary'],
            ['label' => 'Published', 'value' => $publishedCount, 'icon' => 'fas fa-check-circle', 'tone' => 'success'],
            ['label' => 'Draft', 'value' => $draftCount, 'icon' => 'fas fa-pen-nib', 'tone' => 'warning'],
        ],
        'articles' => $informationBoards->getCollection()->map(function ($article) {
            $canManage = auth()->user()->isAdmin() || $article->user_id === auth()->id();

            return [
                'title' => $article->title,
                'excerpt' => \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->content), 180),
                'coverImage' => $article->cover_image_url,
                'categories' => $article->categories->pluck('name')->values(),
                'statusLabel' => ucfirst($article->status),
                'statusTone' => $article->status === 'published' ? 'success' : 'secondary',
                'author' => $article->user?->name ?? '-',
                'date' => $article->created_at->toIso8601String(),
                'showHref' => route('information-boards.show', $article),
                'editHref' => $canManage ? route('information-boards.edit', $article) : null,
                'deleteAction' => $canManage ? route('information-boards.destroy', $article) : null,
                'confirm' => $article->title,
                'confirmText' => "Hapus artikel {$article->title}?",
            ];
        })->values(),
        'pagination' => [
            'currentPage' => $informationBoards->currentPage(),
            'lastPage' => $informationBoards->lastPage(),
            'prevUrl' => $informationBoards->previousPageUrl(),
            'nextUrl' => $informationBoards->nextPageUrl(),
            'from' => $informationBoards->firstItem() ?? 0,
            'to' => $informationBoards->lastItem() ?? 0,
            'total' => $informationBoards->total(),
        ],
        'emptyState' => [
            'title' => 'Belum ada artikel',
            'text' => 'Mulai dengan menulis artikel pertama untuk papan informasi internal.',
        ],
    ];
@endphp

<script id="svelte-information-board-index-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-information-board-index-root"></div>
@endsection
