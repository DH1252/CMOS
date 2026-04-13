<?php

namespace App\Http\Controllers;

use App\Models\InformationBoard;
use App\Models\InformationCategory;
use Illuminate\Http\Request;

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

        return view('informasi.index', compact('articles', 'categories', 'search', 'activeCategory'));
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

        return view('informasi.show', compact('article', 'latestArticles'));
    }
}
