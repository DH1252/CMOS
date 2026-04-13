@extends('layouts.app')

@section('title', $informationBoard->title)
@section('page-title', 'Papan Informasi')
@section('page-meta', '')

@push('styles')
<style>
    .article-content-block p,
    .article-content-block ul,
    .article-content-block ol,
    .article-content-block blockquote,
    .article-content-block h1,
    .article-content-block h2,
    .article-content-block h3,
    .article-content-block h4 {
        margin-top: 0;
        margin-bottom: 1.2rem;
    }

    .article-content-block h1,
    .article-content-block h2,
    .article-content-block h3,
    .article-content-block h4 {
        color: var(--foreground);
        line-height: 1.2;
    }

    .article-content-block a {
        color: var(--brand-secondary);
        text-decoration: underline;
        text-underline-offset: 0.2rem;
    }

    .article-content-block ul,
    .article-content-block ol {
        padding-left: 1.1rem;
    }

    .article-content-block blockquote {
        margin-left: 0;
        padding: 1rem 1.1rem;
        border: 1px solid var(--border);
        border-radius: 0.625rem;
        background: color-mix(in srgb, var(--brand-secondary-soft) 46%, white);
        color: var(--muted-foreground);
    }

    .article-content-block img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
@php
    $canManage = auth()->check() && (auth()->user()->isAdmin() || $informationBoard->user_id === auth()->id());
    $backHref = route('information-boards.index');
    $editHref = $canManage ? route('information-boards.edit', $informationBoard) : null;
@endphp

<article class="grid gap-10 lg:grid-cols-[minmax(0,1.12fr)_18rem] lg:items-start">
    <section class="min-w-0 space-y-8">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('dashboard') }}" class="transition-colors hover:text-foreground">Dashboard</a>
            <span>/</span>
            <a href="{{ $backHref }}" class="transition-colors hover:text-foreground">Papan Informasi</a>
            <span>/</span>
            <span class="text-foreground">{{ $informationBoard->title }}</span>
        </nav>

        <header class="max-w-[70ch] space-y-5 border-b border-border pb-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-[8px] px-2.5 py-1 text-xs font-medium {{ $informationBoard->status === 'published' ? 'bg-brand-secondary-soft text-foreground' : 'bg-muted text-muted-foreground' }}">
                    {{ ucfirst($informationBoard->status) }}
                </span>

                @foreach ($informationBoard->categories as $category)
                    <span class="rounded-[8px] bg-brand-secondary-soft px-2.5 py-1 text-xs font-medium text-foreground">{{ $category->name }}</span>
                @endforeach
            </div>

            <h1 class="text-4xl leading-tight text-foreground md:text-5xl">{{ $informationBoard->title }}</h1>

            <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
                <span>{{ optional($informationBoard->published_at)->format('d M Y H:i') ?? $informationBoard->created_at->format('d M Y H:i') }}</span>
                <span>{{ $informationBoard->user?->name ?? '-' }}</span>
            </div>

            <div class="flex flex-wrap gap-3 pt-1">
                <a href="{{ $backHref }}" class="inline-flex items-center gap-2 rounded-[10px] border border-border bg-card px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                @if ($editHref)
                    <a href="{{ $editHref }}" class="inline-flex items-center gap-2 rounded-[10px] border border-[var(--brand-secondary-soft)] bg-[var(--brand-secondary-soft)] px-4 py-2 text-sm font-medium text-[var(--white-soft)] transition-colors hover:border-[color:color-mix(in_srgb,var(--brand-secondary-soft)_86%,black)] hover:bg-[color:color-mix(in_srgb,var(--brand-secondary-soft)_86%,black)]">
                        <i class="fas fa-edit"></i>
                        <span>Edit Artikel</span>
                    </a>
                @endif
            </div>
        </header>

        @if ($informationBoard->cover_image_url)
            <div class="overflow-hidden rounded-[10px] border border-border bg-card">
                <img src="{{ $informationBoard->cover_image_url }}" alt="{{ $informationBoard->title }}" class="max-h-[34rem] w-full object-cover" loading="lazy" decoding="async" data-fallback-image="{{ asset('images/logokabinet.png') }}" onerror="this.onerror=null;this.src=this.dataset.fallbackImage">
            </div>
        @endif

        <div class="article-content-block max-w-[72ch] text-[1.02rem] leading-8 text-foreground">
            {!! $informationBoard->content !!}
        </div>
    </section>

    <aside class="grid gap-6 lg:sticky lg:top-24">
        <div class="rounded-[10px] border border-border bg-card px-5 py-5">
            <h2 class="text-lg text-foreground">Artikel terbaru</h2>

            @if ($latestArticles->isEmpty())
                <p class="mt-3 text-sm leading-7 text-muted-foreground">Belum ada artikel lain yang tampil di arsip saat ini.</p>
            @else
                <div class="mt-4 grid gap-4">
                    @foreach ($latestArticles as $latest)
                        <a href="{{ route('information-boards.show', $latest) }}" class="border-t border-border pt-4 text-inherit no-underline transition-colors hover:text-brand-secondary first:border-t-0 first:pt-0">
                            <strong class="block text-base leading-7 text-foreground">{{ $latest->title }}</strong>
                            <span class="mt-1 block text-sm text-muted-foreground">{{ optional($latest->published_at)->format('d M Y H:i') ?? $latest->created_at->format('d M Y H:i') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>
</article>
@endsection
