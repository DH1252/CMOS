@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
    $fallbackTitle = 'Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi';
    $fallbackDescription = 'Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.';
    $publicProps = [
        'page' => 'landing',
        'appName' => $appName,
        'organizationName' => $organizationName,
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'logoUrl' => asset('images/logokabinet.png'),
        'latestInfo' => $latestInfo->map(fn($item) => [
            'title' => $item->title,
            'excerpt' => $item->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 140),
            'publishedAt' => optional($item->publishedAtLocal)->toIso8601String(),
            'coverImage' => $item->cover_image_url,
            'category' => $item->categories->pluck('name')->implode(', ') ?: 'Papan Informasi',
            'url' => route('informasi.show', $item->slug),
        ])->values(),
    ];
    $publicSsr = app(\App\Services\SvelteSsrRenderer::class)->render('publicApp', $publicProps);
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="public">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (trim($publicSsr['head']) === '')
        <title>{{ $fallbackTitle }}</title>
        <meta name="description" content="{{ $fallbackDescription }}">
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    {!! $publicSsr['head'] !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <script id="svelte-public-props" type="application/json">{!! str_replace('</', '<\/', json_encode($publicProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
    <div id="svelte-public-root" data-ssr="{{ $publicSsr['rendered'] ? 'true' : 'false' }}">{!! $publicSsr['html'] !!}</div>
</body>
</html>
