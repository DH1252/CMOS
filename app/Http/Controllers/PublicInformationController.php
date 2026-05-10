<?php

namespace App\Http\Controllers;

use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\Setting;
use App\Support\StructuredData;
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
        $homeUrl = route('home');
        $infoUrl = route('informasi.index');
        $logoUrl = asset('images/logokabinet.png');
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $breadcrumbId = $infoUrl.'#breadcrumb';
        $itemListId = $infoUrl.'#item-list';
        $articleSchemaItems = $articleItems->map(fn (InformationBoard $article): array => [
            'name' => $article->title,
            'url' => route('informasi.show', $article->slug),
        ])->all();
        $jsonLdNodes = [
            StructuredData::organization($settings['organizationName'], $homeUrl, $logoUrl),
            StructuredData::website($settings['organizationName'], $homeUrl, $infoUrl, $organizationId),
            StructuredData::collectionPage([
                '@id' => $infoUrl.'#webpage',
                'url' => $infoUrl,
                'name' => 'Papan Informasi - '.$settings['organizationName'],
                'description' => 'Artikel, pembaruan kegiatan, dan publikasi organisasi HIMATEKKOM ITS dalam satu arsip yang mudah ditelusuri.',
                'isPartOf' => ['@id' => $websiteId],
                'publisher' => ['@id' => $organizationId],
                'breadcrumb' => ['@id' => $breadcrumbId],
                'mainEntity' => $articleSchemaItems === [] ? null : ['@id' => $itemListId],
                'inLanguage' => 'id-ID',
            ]),
            StructuredData::breadcrumb([
                ['name' => 'Beranda', 'url' => $homeUrl],
                ['name' => 'Papan Informasi', 'url' => $infoUrl],
            ], $breadcrumbId),
        ];

        if ($articleSchemaItems !== []) {
            $jsonLdNodes[] = StructuredData::itemList($articleSchemaItems, $itemListId);
        }

        return [
            'page' => 'info-index',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => $homeUrl,
            'loginUrl' => route('login'),
            'infoUrl' => $infoUrl,
            'logoUrl' => $logoUrl,
            'seo' => $this->buildSeoPayload(
                title: 'Papan Informasi - '.$settings['organizationName'],
                description: 'Artikel, pembaruan kegiatan, dan publikasi organisasi HIMATEKKOM ITS dalam satu arsip yang mudah ditelusuri.',
                canonical: $infoUrl,
                image: $logoUrl,
                type: 'website',
                jsonLd: StructuredData::graph($jsonLdNodes),
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
                    'action' => $infoUrl,
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
        $homeUrl = route('home');
        $infoUrl = route('informasi.index');
        $canonicalUrl = route('informasi.show', $article);
        $logoUrl = asset('images/logokabinet.png');
        $coverImageUrl = $article->cover_image_url;
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $articleCategories = $article->categories->pluck('name')->values()->all();
        $author = $article->user?->name
            ? ['@type' => 'Person', 'name' => $article->user->name]
            : ['@id' => $organizationId];
        $showJsonLdNodes = [
            StructuredData::organization($settings['organizationName'], $homeUrl, $logoUrl),
            StructuredData::website($settings['organizationName'], $homeUrl, $infoUrl, $organizationId),
            StructuredData::article([
                '@id' => $canonicalUrl.'#article',
                'headline' => $article->seo_title,
                'description' => $article->seo_description,
                'url' => $canonicalUrl,
                'mainEntityOfPage' => ['@id' => $canonicalUrl.'#webpage'],
                'author' => $author,
                'publisher' => ['@id' => $organizationId],
                'image' => $coverImageUrl ? [$coverImageUrl] : null,
                'datePublished' => optional($article->publishedAtLocal)?->toIso8601String(),
                'dateModified' => optional($article->updated_at)?->toIso8601String(),
                'articleSection' => $articleCategories,
                'keywords' => implode(', ', $articleCategories),
                'isAccessibleForFree' => true,
                'inLanguage' => 'id-ID',
            ]),
            StructuredData::page([
                '@id' => $canonicalUrl.'#webpage',
                'url' => $canonicalUrl,
                'name' => $article->seo_title,
                'description' => $article->seo_description,
                'isPartOf' => ['@id' => $websiteId],
                'primaryImageOfPage' => $coverImageUrl ? ['@id' => $canonicalUrl.'#primary-image'] : null,
                'breadcrumb' => ['@id' => $canonicalUrl.'#breadcrumb'],
                'inLanguage' => 'id-ID',
            ]),
            StructuredData::breadcrumb([
                ['name' => 'Beranda', 'url' => $homeUrl],
                ['name' => 'Papan Informasi', 'url' => $infoUrl],
                ['name' => $article->title, 'url' => $canonicalUrl],
            ], $canonicalUrl.'#breadcrumb'),
        ];

        if ($coverImageUrl) {
            $showJsonLdNodes[] = [
                '@type' => 'ImageObject',
                '@id' => $canonicalUrl.'#primary-image',
                'url' => $coverImageUrl,
            ];
        }

        return [
            'page' => 'info-show',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => $homeUrl,
            'loginUrl' => route('login'),
            'infoUrl' => $infoUrl,
            'logoUrl' => $logoUrl,
            'seo' => $this->buildSeoPayload(
                title: $article->seo_title.' - '.$settings['organizationName'],
                description: $article->seo_description,
                canonical: $canonicalUrl,
                image: $coverImageUrl ?? $logoUrl,
                type: 'article',
                jsonLd: StructuredData::graph($showJsonLdNodes),
            ),
            'infoShow' => [
                'article' => [
                    'title' => $article->title,
                    'seoTitle' => $article->seo_title,
                    'dateLabel' => $this->formatPublicDate($article->publishedAtLocal, includeTime: true),
                    'author' => $article->user?->name ?? '-',
                    'coverImage' => $article->cover_image_optimized,
                    'categories' => $articleCategories,
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
            'jsonLd' => $jsonLd === [] ? null : StructuredData::encode($jsonLd),
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
