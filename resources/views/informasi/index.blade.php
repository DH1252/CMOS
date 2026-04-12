@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
    $articleItems = $articles->getCollection()->values();
    $featuredArticle = $articleItems->first();
    $remainingArticles = $articleItems->slice($featuredArticle ? 1 : 0)->values();

    $publicProps = [
        'page' => 'info-index',
        'appName' => $appName,
        'organizationName' => $organizationName,
        'homeUrl' => route('home'),
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'logoUrl' => asset('images/logokabinet.png'),
        'infoIndex' => [
            'title' => 'Papan Informasi',
            'kicker' => 'Publikasi Organisasi',
            'headline' => 'Artikel, pembaruan, dan dokumentasi resmi HIMATEKKOM ITS.',
            'description' => 'Temukan rilis informasi, dokumentasi kegiatan, dan pembaruan organisasi dalam satu arsip publik yang rapi dan mudah ditelusuri.',
            'stats' => [
                ['label' => 'Artikel Terbit', 'value' => $articles->total()],
                ['label' => 'Kategori', 'value' => $categories->count()],
                ['label' => 'Filter Aktif', 'value' => ($search !== '' || $activeCategory) ? 'Ya' : 'Tidak'],
            ],
            'filters' => [
                'action' => route('informasi.index'),
                'query' => $search,
                'category' => $activeCategory?->slug ?? '',
                'categories' => $categories->map(fn($category) => [
                    'value' => $category->slug,
                    'label' => $category->name,
                ])->values(),
            ],
            'featured' => $featuredArticle ? [
                'title' => $featuredArticle->title,
                'excerpt' => \Illuminate\Support\Str::limit(strip_tags($featuredArticle->excerpt ?: $featuredArticle->content), 220),
                'coverImage' => $featuredArticle->cover_image_url,
                'categories' => $featuredArticle->categories->pluck('name')->values(),
                'author' => $featuredArticle->user?->name ?? '-',
                'date' => optional($featuredArticle->published_at)?->toIso8601String(),
                'href' => route('informasi.show', $featuredArticle->slug),
            ] : null,
            'articles' => $remainingArticles->map(fn($article) => [
                'title' => $article->title,
                'excerpt' => \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->content), 150),
                'coverImage' => $article->cover_image_url,
                'categories' => $article->categories->pluck('name')->values(),
                'author' => $article->user?->name ?? '-',
                'date' => optional($article->published_at)?->toIso8601String(),
                'href' => route('informasi.show', $article->slug),
            ])->values(),
            'pagination' => [
                'currentPage' => $articles->currentPage(),
                'lastPage' => $articles->lastPage(),
                'prevUrl' => $articles->previousPageUrl(),
                'nextUrl' => $articles->nextPageUrl(),
                'from' => $articles->firstItem() ?? 0,
                'to' => $articles->lastItem() ?? 0,
                'total' => $articles->total(),
            ],
        ],
    ];
    $publicSsr = app(\App\Services\SvelteSsrRenderer::class)->render('publicApp', $publicProps);
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="public">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Informasi - {{ $organizationName }}</title>
    <meta name="description" content="Portal informasi resmi {{ $organizationName }}. Artikel, pembaruan kegiatan, dan publikasi organisasi.">
    <link rel="icon" type="image/png" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {!! $publicSsr['head'] !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <script id="svelte-public-props" type="application/json">{!! str_replace('</', '<\/', json_encode($publicProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
    <div id="svelte-public-root" data-ssr="{{ $publicSsr['rendered'] ? 'true' : 'false' }}">{!! $publicSsr['html'] !!}</div>
</body>
</html>
