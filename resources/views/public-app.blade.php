@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName());
    $landingCss = \App\Support\ThemePalette::payloadFromSettings(\App\Models\Setting::query()
        ->whereIn('key', array_merge(['theme_color'], \App\Support\ThemePalette::settingKeys(), \App\Support\ThemePalette::cssVariableKeys()))
        ->pluck('value', 'key')
        ->all())['customCss']['landing'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="public" data-brand="{{ $themeColor }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appName }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.onload=null;this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    @if(!empty($landingCss))
    <style>
        [data-theme="public"] {
            @foreach($landingCss as $var => $value)
            --{{ $var }}: {{ $value }};
            @endforeach
        }
    </style>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
    <script>
        window.addEventListener('message', function (e) {
            if (e.data && e.data.type === 'preview-css') {
                var style = document.getElementById('preview-style');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'preview-style';
                    document.head.appendChild(style);
                }
                style.textContent = e.data.css || '';
            }
        });
    </script>
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    @inertia
</body>
</html>
