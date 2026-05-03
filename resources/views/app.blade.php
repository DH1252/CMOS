@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName());
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="dark" data-brand="{{ $themeColor }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('cmos-theme');

                if (theme === 'light' || theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (error) {
                // ignore storage access failures
            }
        })();
    </script>
    <title>{{ $appName }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    {{-- Self-hosted font CSS (subsetted woff2, ~53 KB total) --}}
    <link rel="preload" href="{{ asset('fonts/public-sans.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('fonts/public-sans.css') }}">
    </noscript>
    {{-- Preconnect to CDN for icon font --}}
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    {{-- Non-critical icon font: low priority, non-blocking --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" fetchpriority="low" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    @inertia
</body>
</html>
