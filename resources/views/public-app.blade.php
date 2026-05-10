@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName());
    $landingCss = \App\Support\ThemePalette::payloadFromSettings(\App\Models\Setting::query()
        ->whereIn('key', array_merge(['theme_color'], \App\Support\ThemePalette::settingKeys(), \App\Support\ThemePalette::cssVariableKeys()))
        ->pluck('value', 'key')
        ->all())['customCss']['landing'] ?? [];
    $landingStyle = '';
    if (!empty($landingCss)) {
        $vars = [];
        foreach ($landingCss as $var => $value) {
            $vars[] = "--{$var}: {$value}";
        }
        $landingStyle = implode('; ', $vars);
    }
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="public" data-brand="{{ $themeColor }}" data-js="false"@if($landingStyle) style="{{ $landingStyle }}"@endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        document.documentElement.setAttribute('data-js', 'true');
    </script>
    <title>{{ $appName }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logokabinet.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preload" href="{{ asset('fonts/public-sans.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('fonts/public-sans.css') }}">
    </noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" media="print" onload="this.onload=null;this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    <style>
        .no-js-shell {
            min-height: 100vh;
            background: var(--page-bg, #18141e);
            color: var(--landing-terminal-text, #f0e6c8);
            font-family: 'Public Sans', sans-serif;
        }

        .no-js-header,
        .no-js-meta,
        .no-js-tag,
        .no-js-button {
            font-family: 'JetBrains Mono', monospace;
        }

        .no-js-header {
            border-bottom: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-bg, #18141e);
        }

        .no-js-header-inner,
        .no-js-main {
            width: min(1180px, calc(100% - 2.5rem));
            margin: 0 auto;
        }

        .no-js-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
        }

        .no-js-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: inherit;
            text-decoration: none;
        }

        .no-js-brand img {
            width: auto;
            height: 2.5rem;
        }

        .no-js-brand-copy {
            display: grid;
            gap: 0.15rem;
        }

        .no-js-brand-title {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .no-js-brand-subtitle,
        .no-js-meta {
            color: var(--landing-terminal-soft, #cabe9e);
            font-size: 0.82rem;
        }

        .no-js-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .no-js-nav a,
        .no-js-link {
            color: inherit;
            text-decoration: none;
        }

        .no-js-main {
            padding: 2rem 0 3rem;
        }

        .no-js-stack {
            display: grid;
            gap: 1.5rem;
        }

        .no-js-section,
        .no-js-card,
        .no-js-article {
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-section,
        .no-js-card {
            padding: 1.5rem;
        }

        .no-js-kicker {
            color: var(--landing-terminal-command, #d9ae43);
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .no-js-title {
            margin: 0.6rem 0 0;
            color: var(--landing-terminal-heading, #f0e6c8);
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(2rem, 5vw, 3.4rem);
            line-height: 1.1;
        }

        .no-js-copy {
            margin: 1rem 0 0;
            max-width: 66ch;
            color: var(--landing-terminal-soft, #cabe9e);
            line-height: 1.7;
        }

        .no-js-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .no-js-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.65rem 1rem;
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            color: inherit;
            text-decoration: none;
        }

        .no-js-button-primary {
            background: var(--landing-terminal-accent, #d9ae43);
            color: var(--landing-terminal-button-text, #251c0a);
        }

        .no-js-grid {
            display: grid;
            gap: 1rem;
        }

        .no-js-article {
            display: grid;
            gap: 1rem;
            padding: 1rem;
        }

        .no-js-article img {
            width: 100%;
            height: auto;
            display: block;
        }

        .no-js-article-title {
            margin: 0;
            color: var(--landing-terminal-heading, #f0e6c8);
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.25rem;
            line-height: 1.3;
        }

        .no-js-article-title a {
            color: inherit;
            text-decoration: none;
        }

        .no-js-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .no-js-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.6rem;
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: color-mix(in srgb, var(--landing-terminal-panel-soft, #2c283a) 72%, transparent);
            font-size: 0.75rem;
        }

        .no-js-divider {
            border-top: 1px solid var(--landing-terminal-line, #8a7a3c);
            margin: 0;
        }

        .no-js-prose {
            color: var(--landing-terminal-text, #f0e6c8);
            line-height: 1.7;
        }

        .no-js-prose h2,
        .no-js-prose h3,
        .no-js-prose h4 {
            color: var(--landing-terminal-heading, #f0e6c8);
            font-family: 'JetBrains Mono', monospace;
        }

        .no-js-prose a {
            color: var(--landing-terminal-interactive, #d9ae43);
            text-decoration: underline;
            text-underline-offset: 0.2rem;
        }

        .no-js-prose img {
            max-width: 100%;
            height: auto;
        }

        .no-js-empty {
            color: var(--landing-terminal-soft, #cabe9e);
        }

        .no-js-hero,
        .no-js-section-head,
        .no-js-section-head-inline,
        .no-js-footer,
        .no-js-gallery-grid,
        .no-js-program-grid {
            display: grid;
            gap: 1.5rem;
        }

        .no-js-hero,
        .no-js-gallery-strip,
        .no-js-panel,
        .no-js-footer {
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-hero,
        .no-js-gallery-strip,
        .no-js-panel,
        .no-js-footer {
            padding: 1.5rem;
        }

        .no-js-hero-copy,
        .no-js-footer-primary {
            display: grid;
            gap: 1.5rem;
        }

        .no-js-hero-title-wrap {
            display: grid;
            gap: 1rem;
        }

        .no-js-hero-visual,
        .no-js-canvas-frame,
        .no-js-frame,
        .no-js-panel-sub {
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-canvas-frame,
        .no-js-frame {
            margin: 0;
        }

        .no-js-canvas-media,
        .no-js-frame-media {
            overflow: hidden;
            border-bottom: 1px solid var(--landing-terminal-line, #8a7a3c);
        }

        .no-js-canvas-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid var(--landing-terminal-line, #8a7a3c);
            color: var(--landing-terminal-soft, #cabe9e);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
        }

        .no-js-canvas-media img,
        .no-js-frame-media img,
        .no-js-article img {
            display: block;
            width: 100%;
            height: auto;
        }

        .no-js-canvas-caption,
        .no-js-frame-caption {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.65rem 0.85rem;
            color: var(--landing-terminal-muted, #cabe9e);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
        }

        .no-js-command-block {
            display: grid;
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-command-row,
        .no-js-list-item {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: 3rem minmax(0, 1fr);
            padding: 0.95rem 1rem;
            border-top: 1px solid var(--landing-terminal-line, #8a7a3c);
        }

        .no-js-command-row:first-child,
        .no-js-list-item:first-child {
            border-top: none;
        }

        .no-js-command-index {
            color: var(--landing-terminal-command, #d9ae43);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .no-js-gallery-strip {
            margin: 0;
        }

        .no-js-gallery-grid,
        .no-js-program-grid,
        .no-js-footer-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .no-js-ordered-list {
            display: grid;
            gap: 0;
            margin: 1.25rem 0 0;
            padding: 0;
            list-style: none;
            border: 1px solid var(--landing-terminal-line, #8a7a3c);
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-article-link {
            color: inherit;
            text-decoration: none;
        }

        .no-js-article-tight {
            padding: 0;
            border: 0;
            background: transparent;
        }

        .no-js-footer-links {
            margin-top: 1rem;
        }

        .no-js-kicker {
            color: var(--landing-terminal-command, #d9ae43);
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .no-js-section-title {
            margin: 0;
            color: var(--landing-terminal-heading, #f0e6c8);
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.1;
        }

        .no-js-section-head-inline {
            align-items: start;
        }

        .no-js-cta {
            text-align: center;
        }

        @media (min-width: 768px) {
            .no-js-grid-home {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .no-js-grid-index {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .no-js-hero {
                grid-template-columns: minmax(0, 1fr) 30rem;
                align-items: start;
            }

            .no-js-section-head-inline {
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: end;
            }

            .no-js-gallery-grid,
            .no-js-program-grid,
            .no-js-footer-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
    @vite(['resources/css/public.css', 'resources/js/public.js'])
    @inertiaHead
    @if (is_string(data_get($page, 'props.seo.jsonLd')) && data_get($page, 'props.seo.jsonLd') !== '')
        <script type="application/ld+json">{!! data_get($page, 'props.seo.jsonLd') !!}</script>
    @endif
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
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <noscript>
        @include('partials.public-noscript')
    </noscript>
    @inertia
</body>
</html>
