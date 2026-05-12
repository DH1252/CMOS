@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName());
    $isPublicRoute = request()->routeIs('home') || request()->routeIs('informasi.*');
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
<html lang="id" data-theme="{{ $isPublicRoute ? 'public' : 'dark' }}" data-brand="{{ $themeColor }}" data-js="false"@if($landingStyle) style="{{ $landingStyle }}"@endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <link rel="icon" type="image/avif" href="{{ asset('images/logokabinet.avif') }}">
    <link rel="icon" type="image/webp" href="{{ asset('images/logokabinet.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preload" href="{{ asset('fonts/public-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    @if ($isPublicRoute)
        <link rel="preload" href="{{ asset('fonts/jetbrains-mono-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
    @if ($isPublicRoute)
    <script>
        window.addEventListener('message', function (e) {
            if (e.data && e.data.type === 'preview-css' && e.data.vars) {
                var html = document.documentElement;
                for (var key in e.data.vars) {
                    if (e.data.vars.hasOwnProperty(key)) {
                        html.style.setProperty(key, e.data.vars[key]);
                    }
                }
            }
        });
    </script>
    @endif
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    @inertia
</body>
</html>
