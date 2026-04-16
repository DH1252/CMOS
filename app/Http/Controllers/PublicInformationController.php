<?php

namespace App\Http\Controllers;

use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PublicInformationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $categorySlug = trim((string) $request->get('kategori', ''));

        $query = InformationBoard::with(['user', 'categories'])
            ->published()
            ->latest('published_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
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

        return Inertia::render('PublicApp', $this->indexPayload(
            $articles,
            $categories,
            $search,
            $activeCategory,
        ));
    }

    public function show(InformationBoard $informationBoard)
    {
        abort_unless($informationBoard->status === 'published' && (! $informationBoard->published_at || $informationBoard->published_at->lte(now())), 404);

        $article = $informationBoard->load(['user', 'categories']);

        $latestArticles = InformationBoard::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        return Inertia::render('PublicApp', $this->showPayload($article, $latestArticles));
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

        return [
            'page' => 'info-index',
            'appName' => Setting::get('app_name', 'CMOS'),
            'organizationName' => Setting::get('organization_name', 'HIMATEKKOM ITS'),
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
                    'date' => optional($featuredArticle->publishedAtLocal)?->toIso8601String(),
                    'href' => route('informasi.show', $featuredArticle->slug),
                ] : null,
                'articles' => $remainingArticles->map(fn ($article) => [
                    'title' => $article->title,
                    'excerpt' => Str::limit(strip_tags($article->excerpt ?: $article->content), 150),
                    'coverImage' => $article->cover_image_url,
                    'categories' => $article->categories->pluck('name')->values(),
                    'author' => $article->user?->name ?? '-',
                    'date' => optional($article->publishedAtLocal)?->toIso8601String(),
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
        return [
            'page' => 'info-show',
            'appName' => Setting::get('app_name', 'CMOS'),
            'organizationName' => Setting::get('organization_name', 'HIMATEKKOM ITS'),
            'homeUrl' => route('home'),
            'loginUrl' => route('login'),
            'infoUrl' => route('informasi.index'),
            'logoUrl' => asset('images/logokabinet.png'),
            'infoShow' => [
                'article' => [
                    'title' => $article->title,
                    'seoTitle' => $article->seo_title,
                    'date' => optional($article->publishedAtLocal)?->toIso8601String(),
                    'author' => $article->user?->name ?? '-',
                    'coverImage' => $article->cover_image_url,
                    'categories' => $article->categories->pluck('name')->values(),
                    'contentHtml' => $article->content,
                    'excerpt' => $article->seo_description,
                ],
                'latestArticles' => $latestArticles->map(fn ($latest) => [
                    'title' => $latest->title,
                    'date' => optional($latest->publishedAtLocal)?->toIso8601String(),
                    'href' => route('informasi.show', $latest->slug),
                ])->values(),
            ],
        ];
    }
}
