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
    $ssr = isset($page) && is_array($page)
        ? app(\App\Services\SvelteSsrRenderer::class)->renderPage($page)
        : ['html' => '', 'head' => '', 'rendered' => false];
    $fontCss = str_replace('url("', 'url("'.asset('fonts').'/', file_get_contents(public_path('fonts/public-sans.css')) ?: '');
    $talingFontCss = str_replace('url("', 'url("'.asset('fonts').'/', file_get_contents(public_path('fonts/taling-fonts.css')) ?: '');
    $usesTalingFonts = isset($page['component']) && (in_array($page['component'], ['LandingPage', 'PublicApp', 'PublicComingSoonPage'], true) || str_starts_with($page['component'], 'public/'));
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
    <link rel="icon" type="image/webp" href="{{ asset('images/logokabinet.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logokabinet.png') }}">
    <style>
        @view-transition {
            navigation: auto;
        }
    </style>
    <link rel="preload" href="{{ asset('fonts/public-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/jetbrains-mono-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    @if ($usesTalingFonts)
        <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-400.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-600.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('fonts/The Seasons Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('fonts/josefin-sans-400.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif
    <style>{!! $fontCss !!}</style>
    @if ($usesTalingFonts)
        <style>{!! $talingFontCss !!}</style>
    @endif
    <style>
        .no-js-shell {
            --taling-public-cream: oklch(0.985 0.018 92);
            --taling-public-paper: oklch(0.955 0.035 88);
            --taling-public-yellow: oklch(0.86 0.16 87);
            --taling-public-orange: oklch(0.73 0.19 50);
            --taling-public-purple: oklch(0.32 0.2 300);
            --taling-public-purple-deep: oklch(0.24 0.15 302);
            --taling-public-ink: oklch(0.22 0.025 300);
            --font-terminal: 'The Seasons', Georgia, 'Times New Roman', serif;
            --font-public: 'Josefin Sans', 'Public Sans', ui-sans-serif, system-ui, sans-serif;
            --landing-terminal-bg: var(--taling-public-cream);
            --landing-terminal-panel: color-mix(in oklch, var(--taling-public-cream) 82%, var(--taling-public-yellow));
            --landing-terminal-panel-soft: color-mix(in oklch, var(--taling-public-yellow) 42%, var(--taling-public-cream));
            --landing-terminal-line: color-mix(in oklch, var(--taling-public-purple) 58%, transparent);
            --landing-terminal-text: var(--taling-public-ink);
            --landing-terminal-heading: var(--taling-public-purple-deep);
            --landing-terminal-soft: color-mix(in oklch, var(--taling-public-ink) 76%, var(--taling-public-cream));
            --landing-terminal-muted: color-mix(in oklch, var(--taling-public-ink) 58%, var(--taling-public-cream));
            --landing-terminal-accent: var(--taling-public-yellow);
            --landing-terminal-interactive: var(--taling-public-purple);
            --landing-terminal-command: var(--taling-public-orange);
            --landing-terminal-button-text: var(--taling-public-purple-deep);
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 8%, color-mix(in oklch, var(--taling-public-yellow) 34%, transparent), transparent 18rem),
                linear-gradient(180deg, var(--taling-public-cream), var(--taling-public-paper));
            color: var(--landing-terminal-text, #241c2d);
            font-family: var(--font-public);
        }

        .no-js-header,
        .no-js-meta,
        .no-js-tag,
        .no-js-button {
            font-family: var(--font-terminal);
        }

        .no-js-header {
            border-bottom: 2px solid var(--landing-terminal-line, #6d2ca8);
            background: var(--landing-terminal-bg, #18141e);
            box-shadow: 0 10px 0 color-mix(in oklch, var(--taling-public-purple) 8%, transparent);
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
            font-weight: 900;
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
            font-weight: 800;
        }

        .no-js-nav a,
        .no-js-link {
            color: var(--landing-terminal-interactive, var(--taling-public-purple));
            text-decoration: none;
        }

        .no-js-nav a:hover,
        .no-js-link:hover {
            color: var(--landing-terminal-command, var(--taling-public-orange));
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
            border: 2px solid var(--landing-terminal-line, #8a7a3c);
            border-radius: 0.35rem;
            background: var(--landing-terminal-panel, #221f2e);
            box-shadow: 8px 8px 0 color-mix(in oklch, var(--taling-public-purple) 18%, transparent);
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
            font-family: var(--font-terminal);
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
            border: 2px solid var(--taling-public-purple, #2a0078);
            border-radius: 999px;
            color: inherit;
            font-weight: 900;
            text-decoration: none;
        }

        .no-js-button-primary {
            background: linear-gradient(90deg, var(--taling-public-orange, #ff7a1a), var(--landing-terminal-accent, #d9ae43));
            color: var(--landing-terminal-button-text, #251c0a);
            box-shadow: 5px 5px 0 color-mix(in oklch, var(--taling-public-purple) 24%, transparent);
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
            font-family: var(--font-terminal);
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
            border-radius: 999px;
            background: color-mix(in srgb, var(--landing-terminal-panel-soft, #2c283a) 72%, transparent);
            font-size: 0.75rem;
            font-weight: 800;
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
            font-family: var(--font-terminal);
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
            border: 2px solid var(--landing-terminal-line, #8a7a3c);
            border-radius: 0.35rem;
            background: var(--landing-terminal-panel, #221f2e);
            box-shadow: 8px 8px 0 color-mix(in oklch, var(--taling-public-purple) 18%, transparent);
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
            border: 2px solid var(--landing-terminal-line, #8a7a3c);
            border-radius: 0.35rem;
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-canvas-frame,
        .no-js-frame {
            margin: 0;
        }

        .no-js-canvas-media,
        .no-js-frame-media {
            overflow: hidden;
            border-bottom: 2px solid var(--landing-terminal-line, #8a7a3c);
        }

        .no-js-canvas-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 0.9rem;
            border-bottom: 2px solid var(--landing-terminal-line, #8a7a3c);
            color: var(--landing-terminal-soft, #cabe9e);
            font-family: var(--font-terminal);
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
            font-family: var(--font-terminal);
            font-size: 0.72rem;
        }

        .no-js-command-block {
            display: grid;
            border: 2px solid var(--landing-terminal-line, #8a7a3c);
            border-radius: 0.35rem;
            background: var(--landing-terminal-panel, #221f2e);
        }

        .no-js-command-row,
        .no-js-list-item {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: 3rem minmax(0, 1fr);
            padding: 0.95rem 1rem;
            border-top: 2px solid var(--landing-terminal-line, #8a7a3c);
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
            border: 2px solid var(--landing-terminal-line, #8a7a3c);
            border-radius: 0.35rem;
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
            font-family: var(--font-terminal);
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
    @if ($ssr['rendered'] && $ssr['head'] !== '')
        {!! $ssr['head'] !!}
    @endif
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
    @if ($ssr['rendered'])
        {!! $ssr['html'] !!}
    @else
        <script data-page="app" type="application/json">{!! json_encode($page, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <div id="app"></div>
    @endif
</body>
</html>
