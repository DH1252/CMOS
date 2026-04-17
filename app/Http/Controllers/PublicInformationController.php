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

        $query = InformationBoard::query()
            ->select(['id', 'user_id', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'published_at'])
            ->with(['user:id,name', 'categories:id,name'])
            ->published()
            ->latest('published_at');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
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
            'homeUrl' => route('home'),
            'loginUrl' => route('login'),
            'infoUrl' => route('informasi.index'),
            'logoUrl' => asset('images/logokabinet.png'),
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
                'featured' => $featuredArticle ? [
                    'title' => $featuredArticle->title,
                    'excerpt' => Str::limit(strip_tags($featuredArticle->excerpt ?: $featuredArticle->content), 220),
                    'coverImage' => $featuredArticle->cover_image_url,
                    'categories' => $featuredArticle->categories->pluck('name')->values(),
                    'author' => $featuredArticle->user?->name ?? '-',
                    'dateLabel' => $this->formatPublicDate($featuredArticle->publishedAtLocal, includeTime: true),
                    'href' => route('informasi.show', $featuredArticle->slug),
                ] : null,
                'articles' => $remainingArticles->map(fn ($article) => [
                    'title' => $article->title,
                    'excerpt' => Str::limit(strip_tags($article->excerpt ?: $article->content), 150),
                    'coverImage' => $article->cover_image_url,
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
            'homeUrl' => route('home'),
            'loginUrl' => route('login'),
            'infoUrl' => route('informasi.index'),
            'logoUrl' => asset('images/logokabinet.png'),
            'infoShow' => [
                'article' => [
                    'title' => $article->title,
                    'seoTitle' => $article->seo_title,
                    'dateLabel' => $this->formatPublicDate($article->publishedAtLocal, includeTime: true),
                    'author' => $article->user?->name ?? '-',
                    'coverImage' => $article->cover_image_url,
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
     * @return array{appName: string, organizationName: string, themeColor: string, themeVariables: array<string, string>}
     */
    private function publicSettings(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['app_name', 'organization_name', 'theme_color'], ThemePalette::settingKeys()))
            ->pluck('value', 'key');
        $themePayload = ThemePalette::payloadFromSettings($settings->all());

        return [
            'appName' => (string) $settings->get('app_name', 'CMOS'),
            'organizationName' => (string) $settings->get('organization_name', 'HIMATEKKOM ITS'),
            'themeColor' => $themePayload['color'],
            'themeVariables' => $themePayload['variables'],
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
