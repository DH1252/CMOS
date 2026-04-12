@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');

    $publicProps = [
        'page' => 'info-show',
        'appName' => $appName,
        'organizationName' => $organizationName,
        'homeUrl' => route('home'),
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'logoUrl' => asset('images/logokabinet.png'),
        'infoShow' => [
            'article' => [
                'title' => $article->title,
                'date' => optional($article->published_at)?->toIso8601String(),
                'author' => $article->user?->name ?? '-',
                'coverImage' => $article->cover_image_url,
                'categories' => $article->categories->pluck('name')->values(),
                'contentHtml' => $article->content,
            ],
            'latestArticles' => $latestArticles->map(fn($latest) => [
                'title' => $latest->title,
                'date' => optional($latest->published_at)?->toIso8601String(),
                'href' => route('informasi.show', $latest->slug),
            ])->values(),
        ],
    ];
    $publicSsr = app(\App\Services\SvelteSsrRenderer::class)->render('publicApp', $publicProps);
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
