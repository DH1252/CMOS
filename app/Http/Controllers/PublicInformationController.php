<?php

namespace App\Http\Controllers;

use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\Setting;
use App\Support\ThemePalette;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PublicInformationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $categorySlug = trim((string) $request->get('kategori', ''));
        $supportsFullText = in_array(InformationBoard::query()->getConnection()->getDriverName(), ['mysql', 'mariadb'], true);

        $query = InformationBoard::query()
            ->select(['id', 'user_id', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'published_at'])
            ->with(['user:id,name', 'categories:id,name'])
            ->published()
            ->latest('published_at');

        if ($search !== '') {
            if ($supportsFullText && mb_strlen($search) >= 3) {
                $query->selectRaw(
                    'MATCH(title, excerpt, content, meta_title, meta_description) AGAINST (? IN NATURAL LANGUAGE MODE) AS search_score',
                    [$search],
                );
            }

            $query->where(function (Builder $q) use ($search, $supportsFullText): void {
                if ($supportsFullText && mb_strlen($search) >= 3) {
                    $q->whereFullText(
                        ['title', 'excerpt', 'content', 'meta_title', 'meta_description'],
                        $search,
                    )->orWhere('title', 'like', "%{$search}%");
                } else {
                    $q->where('title', 'like', "%{$search}%");
                }

                $q->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%")
                    ->orWhere('meta_description', 'like', "%{$search}%")
                    ->orWhereHas('categories', function (Builder $categoryQuery) use ($search): void {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });

            if ($supportsFullText && mb_strlen($search) >= 3) {
                $query->orderByDesc('search_score');
            }
        }

        $activeCategory = null;

        if ($categorySlug !== '') {
            $activeCategory = InformationCategory::where('slug', $categorySlug)->first();

            if ($activeCategory) {
                $query->whereHas('categories', fn ($q) => $q->where('information_categories.id', $activeCategory->id));
            }
        }

        $articles = $query->paginate(9)->withQueryString();
        $categories = InformationCategory::orderBy('name')->get();

        return \Inertia\Inertia::render('PublicApp', $this->indexPayload(
            $articles,
            $categories,
            $search,
            $activeCategory,
        ));
    }

    public function show(InformationBoard $informationBoard)
    {
        abort_unless($informationBoard->status === 'published' && (! $informationBoard->published_at || $informationBoard->published_at->lte(now())), 404);

        $article = $informationBoard->load(['user:id,name', 'categories:id,name']);

        $latestArticles = InformationBoard::published()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        return \Inertia\Inertia::render('PublicApp', $this->showPayload($article, $latestArticles));
    }

    /**
     * @param  LengthAwarePaginator<int, InformationBoard>  $articles
     * @param  \Illuminate\Support\Collection<int, InformationCategory>  $categories
     * @return array<string, mixed>
     */
    private function indexPayload($articles, $categories, string $search, ?InformationCategory $activeCategory): array
    {
        $articleItems = $articles->getCollection()->values();
        $featuredArticle = $articleItems->first();
        $remainingArticles = $articleItems->slice($featuredArticle ? 1 : 0)->values();
        $settings = $this->publicSettings();

        return [
            'page' => 'info-index',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => route('home'),
            'loginUrl' => route('login'),
            'infoUrl' => route('informasi.index'),
            'logoUrl' => asset('images/logokabinet.png'),
            'seo' => $this->buildSeoPayload(
                title: 'Papan Informasi - '.$settings['organizationName'],
                description: 'Artikel, pembaruan kegiatan, dan publikasi organisasi HIMATEKKOM ITS dalam satu arsip yang mudah ditelusuri.',
                canonical: route('informasi.index'),
                image: asset('images/logokabinet.png'),
                type: 'website',
                jsonLd: [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $settings['organizationName'].' | Papan Informasi',
                    'url' => route('informasi.index'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('informasi.index').'?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ),
            'infoIndex' => [
                'title' => 'Papan Informasi',
                'kicker' => 'Publikasi Organisasi',
                'headline' => 'Artikel, pembaruan, dan dokumentasi resmi HIMATEKKOM ITS.',
                'description' => 'Temukan rilis informasi, dokumentasi kegiatan, dan pembaruan organisasi dalam satu arsip publik yang rapi dan mudah ditelusuri.',
                'stats' => [
                    ['label' => 'Artikel Terbit', 'value' => $articles->total()],
                    ['label' => 'Kategori', 'value' => $categories->count()],
                    ['label' => 'Filter Aktif', 'value' => ($search !== '' || $activeCategory) ? 'Ya' : 'Tidak'],
                ],
                'filters' => [
                    'action' => route('informasi.index'),
                    'query' => $search,
                    'category' => $activeCategory?->slug ?? '',
                    'categories' => $categories->map(fn ($category) => [
                        'value' => $category->slug,
                        'label' => $category->name,
                    ])->values(),
                ],
                'searchSummary' => $search !== ''
                    ? sprintf('Menampilkan hasil untuk "%s" di judul, ringkasan, isi, kategori, dan penulis.', $search)
                    : 'Pencarian arsip mendukung kata kunci dari judul, ringkasan, isi artikel, kategori, dan nama penulis.',
                'featured' => $featuredArticle ? [
                    'title' => $featuredArticle->title,
                    'excerpt' => Str::limit(strip_tags($featuredArticle->excerpt ?: $featuredArticle->content), 220),
                    'coverImage' => $featuredArticle->cover_image_optimized,
                    'categories' => $featuredArticle->categories->pluck('name')->values(),
                    'author' => $featuredArticle->user?->name ?? '-',
                    'dateLabel' => $this->formatPublicDate($featuredArticle->publishedAtLocal, includeTime: true),
                    'href' => route('informasi.show', $featuredArticle->slug),
                ] : null,
                'articles' => $remainingArticles->map(fn ($article) => [
                    'title' => $article->title,
                    'excerpt' => Str::limit(strip_tags($article->excerpt ?: $article->content), 150),
                    'coverImage' => $article->cover_image_optimized,
                    'categories' => $article->categories->pluck('name')->values(),
                    'author' => $article->user?->name ?? '-',
                    'dateLabel' => $this->formatPublicDate($article->publishedAtLocal, includeTime: true),
                    'href' => route('informasi.show', $article->slug),
                ])->values(),
                'pagination' => [
                    'currentPage' => $articles->currentPage(),
                    'lastPage' => $articles->lastPage(),
                    'prevUrl' => $articles->previousPageUrl(),
                    'nextUrl' => $articles->nextPageUrl(),
                    'from' => $articles->firstItem() ?? 0,
                    'to' => $articles->lastItem() ?? 0,
                    'total' => $articles->total(),
                ],
            ],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, InformationBoard>  $latestArticles
     * @return array<string, mixed>
     */
    private function showPayload(InformationBoard $article, $latestArticles): array
    {
        $settings = $this->publicSettings();

        return [
            'page' => 'info-show',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => route('home'),
            'loginUrl' => route('login'),
            'infoUrl' => route('informasi.index'),
            'logoUrl' => asset('images/logokabinet.png'),
            'seo' => $this->buildSeoPayload(
                title: $article->seo_title.' - '.$settings['organizationName'],
                description: $article->seo_description,
                canonical: route('informasi.show', $article),
                image: $article->cover_image_url ?? asset('images/logokabinet.png'),
                type: 'article',
                jsonLd: [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $article->seo_title,
                    'description' => $article->seo_description,
                    'author' => [
                        '@type' => 'Person',
                        'name' => $article->user?->name ?? $settings['organizationName'],
                    ],
                    'image' => $article->cover_image_url ?? asset('images/logokabinet.png'),
                    'datePublished' => optional($article->publishedAtLocal)?->toIso8601String(),
                    'dateModified' => optional($article->updated_at)?->toIso8601String(),
                    'mainEntityOfPage' => route('informasi.show', $article),
                    'keywords' => $article->categories->pluck('name')->implode(', '),
                ],
            ),
            'infoShow' => [
                'article' => [
                    'title' => $article->title,
                    'seoTitle' => $article->seo_title,
                    'dateLabel' => $this->formatPublicDate($article->publishedAtLocal, includeTime: true),
                    'author' => $article->user?->name ?? '-',
                    'coverImage' => $article->cover_image_optimized,
                    'categories' => $article->categories->pluck('name')->values(),
                    'contentHtml' => $article->content,
                    'excerpt' => $article->seo_description,
                ],
                'latestArticles' => $latestArticles->map(fn ($latest) => [
                    'title' => $latest->title,
                    'dateLabel' => $this->formatPublicDate($latest->publishedAtLocal, includeTime: true),
                    'href' => route('informasi.show', $latest->slug),
                ])->values(),
            ],
        ];
    }

    /**
     * @return array{appName: string, organizationName: string, themeColor: string, themeVariables: array<string, string>, themeCustomCss: array{light: array<string, string>, dark: array<string, string>, shared: array<string, string>}}
     */
    private function publicSettings(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['app_name', 'organization_name', 'theme_color'], ThemePalette::settingKeys(), ThemePalette::cssVariableKeys()))
            ->pluck('value', 'key');
        $themePayload = ThemePalette::payloadFromSettings($settings->all());

        return [
            'appName' => (string) $settings->get('app_name', 'CMOS'),
            'organizationName' => (string) $settings->get('organization_name', 'HIMATEKKOM ITS'),
            'themeColor' => $themePayload['color'],
            'themeVariables' => $themePayload['variables'],
            'themeCustomCss' => $themePayload['customCss'],
        ];
    }

    /**
     * @param  array<string, mixed>  $jsonLd
     * @return array{title: string, description: string, canonical: string, image: string|null, type: string, jsonLd: string|null}
     */
    private function buildSeoPayload(string $title, string $description, string $canonical, ?string $image = null, string $type = 'website', array $jsonLd = []): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $image,
            'type' => $type,
            'jsonLd' => $jsonLd === [] ? null : json_encode(
                $jsonLd,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
            ),
        ];
    }

    private function formatPublicDate(?DateTimeInterface $date, bool $includeTime = false): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::instance($date)
            ->locale('id')
            ->translatedFormat($includeTime ? 'd M Y H:i' : 'd M Y');
    }
}
