@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
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
            'publishedAt' => optional($item->published_at)->toIso8601String(),
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
    <title>Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi</title>
    <meta name="description" content="Platform resmi HIMATEKKOM ITS untuk koordinasi program kerja, evaluasi staff, dan informasi organisasi.">
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
