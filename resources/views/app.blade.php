@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName());
    $isPublicRoute = request()->routeIs('home') || request()->routeIs('informasi.*') || request()->routeIs('acara.*') || request()->routeIs('login') || request()->routeIs('departemen') || request()->routeIs('departemen.*') || request()->routeIs('tentang') || request()->routeIs('kompetisi') || request()->routeIs('kristal');
    $internalBrand = $isPublicRoute ? $themeColor : 'gold';
    $landingStyle = '';

    if ($isPublicRoute) {
        $landingCss = \App\Support\ThemePalette::payloadFromSettings(\App\Models\Setting::query()
            ->whereIn('key', array_merge(['theme_color'], \App\Support\ThemePalette::settingKeys(), \App\Support\ThemePalette::cssVariableKeys()))
            ->pluck('value', 'key')
            ->all())['customCss']['landing'] ?? [];

        if (! empty($landingCss)) {
            $vars = [];

            foreach ($landingCss as $var => $value) {
                $vars[] = "--{$var}: {$value}";
            }

            $landingStyle = implode('; ', $vars);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="{{ $isPublicRoute ? 'public' : 'dark' }}" data-brand="{{ $internalBrand }}" data-js="false"@if($landingStyle) style="{{ $landingStyle }}"@endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://ui-avatars.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
        (() => {
            document.documentElement.setAttribute('data-js', 'true');

            try {
                const theme = localStorage.getItem('cmos-theme');

                if (document.documentElement.getAttribute('data-theme') !== 'public' && (theme === 'light' || theme === 'dark')) {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (error) {
                // ignore storage access failures
            }
        })();
    </script>
    <title>{{ $appName }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logokabinet.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logokabinet.png') }}">
    <style>
        @view-transition {
            navigation: auto;
        }
    </style>
    <link rel="preload" href="{{ asset('fonts/public-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    @if ($isPublicRoute)
        <link rel="preload" href="{{ asset('fonts/jetbrains-mono-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
        {{-- Josefin Sans (navbar) and The Seasons (section headings) for landing page --}}
        <link rel="preload" href="{{ asset('fonts/josefin-sans-400.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('fonts/The Seasons Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif
    {{-- Self-hosted font CSS (subsetted woff2, ~85 KB total) --}}
    <link rel="preload" href="{{ asset('fonts/public-sans.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('fonts/public-sans.css') }}">
    </noscript>
    @unless ($isPublicRoute)
        {{-- Self-hosted Font Awesome 6.5.1 (~360 KB woff2 + 103 KB CSS) --}}
        <link rel="preload" href="{{ asset('fonts/font-awesome/css/all.min.css') }}" as="style" fetchpriority="low" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="{{ asset('fonts/font-awesome/css/all.min.css') }}">
        </noscript>
    @endunless
    @if ($isPublicRoute)
        <style>
            /* Landing page / TALING design tokens — injected from server-side theme settings */
            @layer base {
                [data-theme="public"] {
                    --taling-purple: #2a0078;
                    --taling-yellow: #ffd344;
                    --taling-orange: #ff7a1a;
                    --taling-white: #fffaf0;
                    --taling-ink: #120622;
                    --taling-surface: #ffffff;
                    --taling-text: #120622;
                    --taling-text-soft: rgba(18, 6, 34, 0.74);
                    --taling-text-muted: rgba(18, 6, 34, 0.55);
                    --taling-line: rgba(18, 6, 34, 0.14);
                    --taling-font-serif: "The Seasons", Georgia, "Times New Roman", serif;
                }
            }
        </style>
        <style>{!! str_replace('url("', 'url("'.asset('fonts').'/', file_get_contents(public_path('fonts/taling-fonts.css')) ?: '') !!}</style>
    @endif
    @include('partials.realtime-config')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    @inertia
</body>
</html>
