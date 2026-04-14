@php
    $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="public">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->seo_title }} - {{ $organizationName }}</title>
    <meta name="description" content="{{ $article->seo_description }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('cmos-public-theme') || localStorage.getItem('cmos-theme');

                if (theme === 'light' || theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'public');
                }
            } catch (error) {
                // ignore storage access failures
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="mx-auto max-w-[1180px] px-5 py-10 text-foreground lg:px-8 lg:py-14">
        <article class="grid gap-10 lg:grid-cols-[minmax(0,1.12fr)_18rem] lg:items-start">
            <section class="min-w-0 space-y-8">
                <nav class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <a href="{{ route('home') }}" class="transition-colors hover:text-foreground">Beranda</a>
                    <span>/</span>
                    <a href="{{ route('informasi.index') }}" class="transition-colors hover:text-foreground">Arsip Informasi</a>
                    <span>/</span>
                    <span class="text-foreground">{{ $article->title }}</span>
                </nav>

                <header class="max-w-[70ch] space-y-5 border-b border-border pb-8">
                    <h1 class="text-4xl leading-tight text-foreground md:text-5xl">{{ $article->title }}</h1>

                    <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
                        <span>{{ optional($article->publishedAtLocal)->format('d M Y H:i') ?? '-' }}</span>
                        <span>{{ $article->user?->name ?? '-' }}</span>
                    </div>

                    @if ($article->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($article->categories as $category)
                                <span class="rounded-[8px] bg-brand-secondary-soft px-2.5 py-1 text-xs font-medium text-foreground">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </header>

                @if ($article->cover_image_url)
                    <div class="overflow-hidden rounded-[10px] border border-border bg-card">
                        <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" class="max-h-[34rem] w-full object-cover">
                    </div>
                @endif

                <div class="public-information-article-content max-w-[72ch] text-[1.02rem] leading-8 text-foreground">
                    {!! $article->content !!}
                </div>
            </section>

            <aside class="grid gap-6 lg:sticky lg:top-24">
                <div class="rounded-[10px] border border-border bg-card px-5 py-5">
                    <div class="text-sm font-semibold text-foreground">Arsip publik</div>
                    <p class="mt-3 text-sm leading-7 text-muted-foreground">Pengumuman dan dokumentasi resmi.</p>
                    <a href="{{ route('informasi.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-foreground transition-colors hover:text-brand-secondary">
                        Kembali ke arsip
                    </a>
                </div>

                <div class="rounded-[10px] border border-border bg-card px-5 py-5">
                    <h2 class="text-lg text-foreground">Artikel lainnya</h2>

                    @if ($latestArticles->isEmpty())
                        <p class="mt-3 text-sm leading-7 text-muted-foreground">Belum ada artikel lain.</p>
                    @else
                        <div class="mt-4 grid gap-4">
                            @foreach ($latestArticles as $latest)
                                <a href="{{ route('informasi.show', $latest->slug) }}" class="border-t border-border pt-4 text-inherit no-underline transition-colors hover:text-brand-secondary first:border-t-0 first:pt-0">
                                    <strong class="block text-base leading-7 text-foreground">{{ $latest->title }}</strong>
                                    <span class="mt-1 block text-sm text-muted-foreground">{{ optional($latest->publishedAtLocal)->format('d M Y H:i') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>
        </article>
    </main>

    <style>
        .public-information-article-content p,
        .public-information-article-content ul,
        .public-information-article-content ol,
        .public-information-article-content blockquote,
        .public-information-article-content h1,
        .public-information-article-content h2,
        .public-information-article-content h3,
        .public-information-article-content h4 {
            margin-top: 0;
            margin-bottom: 1.2rem;
        }

        .public-information-article-content h1,
        .public-information-article-content h2,
        .public-information-article-content h3,
        .public-information-article-content h4 {
            color: var(--foreground);
            line-height: 1.2;
        }

        .public-information-article-content a {
            color: var(--brand-secondary);
            text-decoration: underline;
            text-underline-offset: 0.2rem;
        }

        .public-information-article-content ul,
        .public-information-article-content ol {
            padding-left: 1.1rem;
        }

        .public-information-article-content blockquote {
            margin-left: 0;
            padding: 1rem 1.1rem;
            border: 1px solid var(--border);
            border-radius: 0.625rem;
            background: color-mix(in srgb, var(--brand-secondary-soft) 46%, white);
            color: var(--muted-foreground);
        }

        .public-information-article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
        }
    </style>
</body>
</html>
