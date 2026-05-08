@php
    use App\Models\InformationBoard;
    use App\Models\Setting;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    $isHome = request()->routeIs('home');
    $isInfoIndex = request()->routeIs('informasi.index');
    $isInfoShow = request()->routeIs('informasi.show');
    $landingProps = $isHome && is_array($page['props'] ?? null) ? $page['props'] : [];
    $organizationName = $landingProps['organizationName'] ?? ($organizationName ?? Setting::get('organization_name', 'HIMATEKKOM ITS'));
    $archiveArticles = collect();
    $currentArticle = null;
    $relatedArticles = collect();

    $landingNavigation = collect($landingProps['navigation'] ?? [
        ['href' => '#profil', 'label' => 'Profil Organisasi'],
        ['href' => '#program-kerja', 'label' => 'Program Kerja'],
        ['href' => '#informasi', 'label' => 'Informasi'],
    ]);
    $landingHero = $landingProps['hero'] ?? [
        'titleVariants' => ['Kabinet Sentra Sinergi', 'HIMATEKKOM ITS'],
        'description' => 'Kabinet Sentra Sinergi menjaga publikasi, dokumentasi, dan akses internal melalui satu sistem yang rapi dan mudah dipantau.',
        'actions' => [
            ['href' => '#informasi', 'label' => 'Buka arsip publik', 'variant' => 'primary'],
            ['href' => '#profil', 'label' => 'Profil organisasi', 'variant' => 'secondary'],
        ],
    ];
    $landingQuickFacts = collect($landingProps['quickFacts'] ?? [
        'Website resmi HIMATEKKOM ITS 2026.',
        'Kabinet Sentra Sinergi menjaga transparansi, dokumentasi, dan kolaborasi organisasi.',
        sprintf('%s dipakai pengurus untuk kerja operasional sehari-hari.', $appName),
    ]);
    $landingProfileSection = $landingProps['profileSection'] ?? [
        'title' => 'Profil organisasi',
        'description' => 'HIMATEKKOM ITS 2026 menjalankan publikasi dan operasional kabinet dengan alur yang terstruktur, terdokumentasi, dan mudah dibaca oleh pengurus maupun publik.',
        'visionLabel' => 'Visi',
        'vision' => 'Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak, dengan tata kelola organisasi yang profesional serta budaya kolaborasi yang kuat dalam semangat Kabinet Sentra Sinergi.',
        'missionTitle' => 'Misi kerja',
        'missionItems' => [
            'Menguatkan pelayanan internal melalui sistem kerja terstruktur, evaluasi berkala, dan pengembangan kapasitas pengurus yang berkelanjutan.',
            'Mendorong kolaborasi lintas pihak, mulai dari mahasiswa, alumni, departemen, hingga mitra kegiatan, untuk memperluas dampak program kerja.',
            'Mewujudkan transparansi informasi melalui publikasi kegiatan, dokumentasi terpusat, dan akses informasi yang mudah bagi warga Teknik Komputer ITS.',
        ],
    ];
    $landingProgramSection = $landingProps['programSection'] ?? ['title' => 'Program kerja kabinet', 'description' => '', 'groups' => []];
    $landingInformationSection = $landingProps['informationSection'] ?? [
        'title' => 'Informasi terbaru',
        'description' => 'Publikasi dan dokumentasi terbaru yang sudah terbit di kanal resmi HIMATEKKOM ITS.',
        'archiveLabel' => 'Arsip lengkap',
        'emptyText' => 'Belum ada publikasi yang terbit di papan informasi.',
    ];
    $landingCtaSection = $landingProps['ctaSection'] ?? [
        'title' => 'Kabinet Sentra Sinergi',
        'description' => 'Satu sistem, satu kabinet. Transparansi dan dokumentasi kerja berjalan di satu tempat.',
        'buttonLabel' => 'Jelajahi arsip publik',
        'instagramUrl' => 'https://www.instagram.com/sentrasinergi/',
        'instagramLabel' => '@sentrasinergi',
        'instagramPrefix' => 'Ikuti',
        'instagramSuffix' => 'di Instagram',
    ];
    $landingFooter = $landingProps['footer'] ?? [
        'description' => 'Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.',
        'address' => 'Gedung Teknik Komputer, Kampus ITS Sukolilo, Surabaya.',
        'sections' => [],
    ];
    $landingHeroActions = collect($landingHero['actions'] ?? []);
    $landingHeroTitle = collect($landingHero['titleVariants'] ?? ['Kabinet Sentra Sinergi'])->first() ?: 'Kabinet Sentra Sinergi';
    $landingMissionItems = collect($landingProfileSection['missionItems'] ?? []);
    $landingProgramGroups = collect($landingProgramSection['groups'] ?? []);
    $landingLatestInfo = collect($landingProps['latestInfo'] ?? []);
    $landingFooterSections = collect($landingFooter['sections'] ?? []);

    if (Schema::hasTable('information_boards')) {
        if ($isInfoIndex) {
            $archiveArticles = InformationBoard::query()
                ->published()
                ->select(['id', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'published_at'])
                ->with(['user:id,name', 'categories:id,name'])
                ->latest('published_at')
                ->take(12)
                ->get();
        }

        if ($isInfoShow) {
            $currentArticle = request()->route('informationBoard');

            if ($currentArticle instanceof InformationBoard) {
                $currentArticle->load(['user:id,name', 'categories:id,name']);

                $relatedArticles = InformationBoard::query()
                    ->published()
                    ->select(['id', 'title', 'slug', 'published_at'])
                    ->where('id', '!=', $currentArticle->id)
                    ->latest('published_at')
                    ->take(5)
                    ->get();
            }
        }
    }

    $formatDate = static fn ($date, $includeTime = false) => $date
        ? optional($date->locale('id'))->translatedFormat($includeTime ? 'd M Y H:i' : 'd M Y')
        : '-';
@endphp

<div class="no-js-shell">
    <header class="no-js-header">
        <div class="no-js-header-inner">
            <a href="{{ route('home') }}" class="no-js-brand">
                <img src="{{ asset('images/logokabinet.png') }}" alt="{{ $appName }}">
                <div class="no-js-brand-copy">
                    <span class="no-js-brand-title">{{ $appName }}</span>
                    <span class="no-js-brand-subtitle">HIMATEKKOM ITS</span>
                </div>
            </a>

            <nav class="no-js-nav">
                @if ($isHome)
                    @foreach ($landingNavigation as $item)
                        <a href="{{ $item['href'] ?? '#' }}">{{ $item['label'] ?? 'Tautan' }}</a>
                    @endforeach
                @else
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('informasi.index') }}">Informasi</a>
                    <a href="{{ route('login') }}">Masuk</a>
                @endif
            </nav>
        </div>
    </header>

    <main id="main-content" class="no-js-main">
        @if ($isHome)
            <div class="no-js-stack">
                <section class="no-js-section">
                    <h1 class="no-js-title">{{ $landingHeroTitle }}</h1>
                    <p class="no-js-copy">{{ $landingHero['description'] ?? '' }}</p>

                    @if ($landingHeroActions->isNotEmpty())
                        <div class="no-js-actions">
                            @foreach ($landingHeroActions as $action)
                                <a href="{{ $action['href'] ?? '#' }}" class="no-js-button {{ ($action['variant'] ?? 'primary') === 'primary' ? 'no-js-button-primary' : '' }}">{{ $action['label'] ?? 'Buka' }}</a>
                            @endforeach
                        </div>
                    @endif

                    @if ($landingQuickFacts->isNotEmpty())
                        <div class="no-js-stack" style="margin-top: 1.5rem;">
                            @foreach ($landingQuickFacts as $fact)
                                <div class="no-js-card">
                                    <div class="no-js-meta">$</div>
                                    <p class="no-js-copy">{{ $fact }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <div class="no-js-grid no-js-grid-index">
                    <section id="profil" class="no-js-section">
                        <h2 class="no-js-title" style="font-size: 1.8rem;">{{ $landingProfileSection['title'] ?? 'Profil organisasi' }}</h2>
                        <p class="no-js-copy">{{ $landingProfileSection['description'] ?? '' }}</p>

                        <div class="no-js-stack" style="margin-top: 1.5rem;">
                            <div>
                                <div class="no-js-kicker">{{ $landingProfileSection['visionLabel'] ?? 'Visi' }}</div>
                                <p class="no-js-copy">{{ $landingProfileSection['vision'] ?? '' }}</p>
                            </div>

                            @if ($landingMissionItems->isNotEmpty())
                                <div>
                                    <h3 class="no-js-article-title">{{ $landingProfileSection['missionTitle'] ?? 'Misi kerja' }}</h3>
                                    <div class="no-js-stack" style="margin-top: 1rem;">
                                        @foreach ($landingMissionItems as $index => $mission)
                                            <div class="no-js-card">
                                                <div class="no-js-meta">0{{ $index + 1 }}</div>
                                                <p class="no-js-copy">{{ $mission }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section id="program-kerja" class="no-js-section">
                        <h2 class="no-js-title" style="font-size: 1.8rem;">{{ $landingProgramSection['title'] ?? 'Program kerja kabinet' }}</h2>
                        @if (! empty($landingProgramSection['description']))
                            <p class="no-js-copy">{{ $landingProgramSection['description'] }}</p>
                        @endif

                        <div class="no-js-stack" style="margin-top: 1.5rem;">
                            @foreach ($landingProgramGroups as $group)
                                <section class="no-js-card">
                                    <h3 class="no-js-article-title">{{ $group['title'] ?? 'Program' }}</h3>
                                    <p class="no-js-copy">{{ $group['description'] ?? '' }}</p>

                                    @if (! empty($group['items']))
                                        <div class="no-js-stack" style="margin-top: 1rem;">
                                            @foreach ($group['items'] as $item)
                                                <article class="no-js-article">
                                                    <div class="no-js-meta">{{ $item['unit'] ?? '-' }}</div>
                                                    <h4 class="no-js-article-title">{{ $item['name'] ?? 'Item' }}</h4>
                                                    <p class="no-js-copy">{{ $item['description'] ?? '' }}</p>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                            @endforeach
                        </div>
                    </section>
                </div>

                <section id="informasi" class="no-js-section">
                    <div class="no-js-actions" style="margin-top: 0; justify-content: space-between; align-items: center;">
                        <div>
                            <h2 class="no-js-title" style="font-size: 1.8rem;">{{ $landingInformationSection['title'] ?? 'Informasi terbaru' }}</h2>
                            <p class="no-js-copy">{{ $landingInformationSection['description'] ?? '' }}</p>
                        </div>
                        <a href="{{ route('informasi.index') }}" class="no-js-link">{{ $landingInformationSection['archiveLabel'] ?? 'Arsip lengkap' }}</a>
                    </div>

                    @if ($landingLatestInfo->isEmpty())
                        <div class="no-js-empty" style="margin-top: 1.5rem;">{{ $landingInformationSection['emptyText'] ?? 'Belum ada publikasi yang terbit di papan informasi.' }}</div>
                    @else
                        <div class="no-js-stack" style="margin-top: 1.5rem;">
                            @foreach ($landingLatestInfo as $article)
                                <article class="no-js-article">
                                    @if (! empty($article['coverImage']))
                                        <img src="{{ $article['coverImage'] }}" alt="{{ $article['title'] ?? 'Artikel' }}" loading="lazy" decoding="async">
                                    @endif
                                    <div class="no-js-meta">{{ $article['publishedAtLabel'] ?? 'Publikasi baru' }} · {{ $article['category'] ?? 'Papan Informasi' }}</div>
                                    <h3 class="no-js-article-title"><a href="{{ $article['url'] ?? route('informasi.index') }}">{{ $article['title'] ?? 'Artikel' }}</a></h3>
                                    <p class="no-js-copy">{{ $article['excerpt'] ?? '' }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="no-js-section">
                    <h2 class="no-js-title" style="font-size: 1.8rem;">{{ $landingCtaSection['title'] ?? 'Kabinet Sentra Sinergi' }}</h2>
                    <p class="no-js-copy">{{ $landingCtaSection['description'] ?? '' }}</p>
                    <div class="no-js-actions">
                        <a href="#informasi" class="no-js-button no-js-button-primary">{{ $landingCtaSection['buttonLabel'] ?? 'Jelajahi arsip publik' }}</a>
                    </div>
                    <p class="no-js-copy">
                        {{ $landingCtaSection['instagramPrefix'] ?? 'Ikuti' }}
                        <a href="{{ $landingCtaSection['instagramUrl'] ?? 'https://www.instagram.com/sentrasinergi/' }}" class="no-js-link">{{ $landingCtaSection['instagramLabel'] ?? '@sentrasinergi' }}</a>
                        {{ $landingCtaSection['instagramSuffix'] ?? 'di Instagram' }}
                    </p>
                </section>

                <section class="no-js-section">
                    <h2 class="no-js-title" style="font-size: 1.6rem;">{{ $organizationName }}</h2>
                    <p class="no-js-copy">{{ $landingFooter['description'] ?? '' }}</p>
                    <p class="no-js-copy">{{ $landingFooter['address'] ?? '' }}</p>

                    @if ($landingFooterSections->isNotEmpty())
                        <div class="no-js-grid no-js-grid-index" style="margin-top: 1.5rem;">
                            @foreach ($landingFooterSections as $section)
                                <div class="no-js-card">
                                    <h3 class="no-js-article-title">{{ $section['title'] ?? 'Navigasi' }}</h3>
                                    <div class="no-js-stack" style="margin-top: 1rem;">
                                        @foreach ($section['links'] ?? [] as $link)
                                            <a href="{{ $link['href'] ?? '#' }}" class="no-js-link">{{ $link['label'] ?? 'Tautan' }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @elseif ($isInfoIndex)
            <div class="no-js-stack">
                <section class="no-js-section">
                    <div class="no-js-kicker">Publikasi Organisasi</div>
                    <h1 class="no-js-title">Arsip informasi resmi</h1>
                    <p class="no-js-copy">Daftar artikel tetap dapat dibaca tanpa JavaScript.</p>
                </section>

                @if ($archiveArticles->isEmpty())
                    <div class="no-js-empty">Belum ada publikasi.</div>
                @else
                    <div class="no-js-grid no-js-grid-index">
                        @foreach ($archiveArticles as $article)
                            <article class="no-js-article">
                                @if ($article->cover_image_url)
                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" decoding="async">
                                @endif
                                <div class="no-js-meta">{{ $formatDate($article->publishedAtLocal, true) }} · {{ $article->user?->name ?? '-' }}</div>
                                <h2 class="no-js-article-title"><a href="{{ route('informasi.show', $article->slug) }}">{{ $article->title }}</a></h2>
                                <p class="no-js-copy">{{ Str::limit(strip_tags($article->excerpt ?: $article->content), 180) }}</p>
                                @if ($article->categories->isNotEmpty())
                                    <div class="no-js-tags">
                                        @foreach ($article->categories as $category)
                                            <span class="no-js-tag">{{ $category->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif ($isInfoShow && $currentArticle)
            <div class="no-js-stack">
                <nav class="no-js-meta">
                    <a href="{{ route('home') }}" class="no-js-link">Beranda</a>
                    /
                    <a href="{{ route('informasi.index') }}" class="no-js-link">Arsip Informasi</a>
                    /
                    <span>{{ $currentArticle->title }}</span>
                </nav>

                <section class="no-js-section">
                    <div class="no-js-meta">{{ $formatDate($currentArticle->publishedAtLocal, true) }} · {{ $currentArticle->user?->name ?? '-' }}</div>
                    <h1 class="no-js-title">{{ $currentArticle->title }}</h1>

                    @if ($currentArticle->categories->isNotEmpty())
                        <div class="no-js-tags">
                            @foreach ($currentArticle->categories as $category)
                                <span class="no-js-tag">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="no-js-section no-js-prose">
                    @if ($currentArticle->cover_image_url)
                        <img src="{{ $currentArticle->cover_image_url }}" alt="{{ $currentArticle->title }}" loading="lazy" decoding="async">
                        <hr class="no-js-divider">
                    @endif

                    {!! $currentArticle->content !!}
                </section>

                @if ($relatedArticles->isNotEmpty())
                    <section class="no-js-stack">
                        <h2 class="no-js-title" style="font-size: 1.6rem;">Artikel lainnya</h2>
                        @foreach ($relatedArticles as $article)
                            <article class="no-js-card">
                                <div class="no-js-meta">{{ $formatDate($article->publishedAtLocal, true) }}</div>
                                <h3 class="no-js-article-title"><a href="{{ route('informasi.show', $article->slug) }}">{{ $article->title }}</a></h3>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        @endif
    </main>
</div>
