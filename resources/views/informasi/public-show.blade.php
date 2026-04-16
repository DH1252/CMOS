@php
    $article = $publicProps['infoShow']['article'] ?? [];
    $organizationName = $publicProps['organizationName'] ?? 'HIMATEKKOM ITS';
    $fallbackTitle = ($article['seoTitle'] ?? $article['title'] ?? 'Papan Informasi') . " - {$organizationName}";
    $fallbackDescription = $article['excerpt'] ?? "Publikasi resmi {$organizationName}.";
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
    <link rel="icon" type="image/png" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {!! $publicSsr['head'] !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <script id="svelte-public-props" type="application/json">{!! str_replace('</', '<\/', json_encode($publicProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
    <div id="svelte-public-root" data-ssr="{{ $publicSsr['rendered'] ? 'true' : 'false' }}">{!! $publicSsr['html'] !!}</div>
</body>
</html>
