<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InformationBoardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $status = $request->get('status');
        $category = $request->get('category');

        $query = InformationBoard::with(['user', 'categories'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['draft', 'published'], true)) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->whereHas('categories', fn ($q) => $q->where('information_categories.id', $category));
        }

        $informationBoards = $query->paginate(12)->withQueryString();
        $categories = InformationCategory::orderBy('name')->get();
        $totalCount = InformationBoard::count();
        $publishedCount = InformationBoard::where('status', 'published')->count();
        $draftCount = InformationBoard::where('status', 'draft')->count();

        return view('information-boards.index', compact(
            'informationBoards',
            'categories',
            'search',
            'status',
            'category',
            'totalCount',
            'publishedCount',
            'draftCount'
        ));
    }

    public function create()
    {
        $categories = InformationCategory::orderBy('name')->get();

        return view('information-boards.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:draft,published',
            'publish_mode' => 'nullable|in:immediately,scheduled',
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $request->string('status')->value() === 'published'
                    && $request->string('publish_mode')->value() === 'scheduled'),
            ],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:information_categories,id',
        ]);

        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $validated['user_id'] = $user->id;
        $validated['slug'] = InformationBoard::generateUniqueSlug($validated['title']);
        $validated['content'] = $this->sanitizeHtml($validated['content']);
        $validated['published_at'] = $this->resolvePublishedAt(
            $validated['status'],
            $validated['publish_mode'] ?? 'immediately',
            $validated['published_at'] ?? null,
        );
        unset($validated['publish_mode']);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('information-boards', 'public');
        }

        $article = InformationBoard::create($validated);
        $article->categories()->sync($validated['category_ids'] ?? []);

        ActivityLog::log('created', "Created information article: {$article->title}", $article);

        return redirect()->route('information-boards.index')
            ->with('success', 'Artikel papan informasi berhasil ditambahkan.');
    }

    public function show(InformationBoard $informationBoard)
    {
        $informationBoard->load(['user', 'categories']);
        $latestArticles = InformationBoard::published()
            ->where('id', '!=', $informationBoard->id)
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('information-boards.show', compact('informationBoard', 'latestArticles'));
    }

    public function edit(InformationBoard $informationBoard)
    {
        $this->authorizeEdit($informationBoard, request()->user());
        $categories = InformationCategory::orderBy('name')->get();

        return view('information-boards.edit', compact('informationBoard', 'categories'));
    }

    public function update(Request $request, InformationBoard $informationBoard)
    {
        $this->authorizeEdit($informationBoard, $request->user());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:draft,published',
            'publish_mode' => 'nullable|in:immediately,scheduled',
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $request->string('status')->value() === 'published'
                    && $request->string('publish_mode')->value() === 'scheduled'),
            ],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:information_categories,id',
        ]);

        $validated['slug'] = InformationBoard::generateUniqueSlug($validated['title'], $informationBoard->id);
        $validated['content'] = $this->sanitizeHtml($validated['content']);
        $validated['published_at'] = $this->resolvePublishedAt(
            $validated['status'],
            $validated['publish_mode'] ?? 'immediately',
            $validated['published_at'] ?? null,
        );
        unset($validated['publish_mode']);

        if ($request->hasFile('cover_image')) {
            $this->deleteCoverImage($informationBoard->cover_image);

            $validated['cover_image'] = $request->file('cover_image')->store('information-boards', 'public');
        }

        $informationBoard->update($validated);
        $informationBoard->categories()->sync($validated['category_ids'] ?? []);

        ActivityLog::log('updated', "Updated information article: {$informationBoard->title}", $informationBoard);

        return redirect()->route('information-boards.index')
            ->with('success', 'Artikel papan informasi berhasil diperbarui.');
    }

    public function destroy(InformationBoard $informationBoard)
    {
        $this->authorizeEdit($informationBoard, request()->user());

        $this->deleteCoverImage($informationBoard->cover_image);

        $title = $informationBoard->title;
        ActivityLog::log('deleted', "Deleted information article: {$title}", $informationBoard);
        $informationBoard->delete();

        return redirect()->route('information-boards.index')
            ->with('success', "Artikel {$title} berhasil dihapus.");
    }

    private function authorizeEdit(InformationBoard $informationBoard, ?User $user): void
    {
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->isAdmin() && $informationBoard->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola artikel ini.');
        }
    }

    private function resolvePublishedAt(string $status, string $publishMode, mixed $publishedAt): mixed
    {
        if ($status === 'draft') {
            return null;
        }

        if ($publishMode !== 'scheduled' || ! $publishedAt) {
            return now('UTC');
        }

        return Carbon::parse($publishedAt, config('app.client_timezone', 'Asia/Jakarta'))
            ->setTimezone('UTC');
    }

    private function sanitizeHtml(string $content): string
    {
        $clean = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content) ?? '';

        return strip_tags($clean, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote>');
    }

    private function deleteCoverImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $legacyPublicPath = public_path('storage/'.$path);

        if (is_file($legacyPublicPath)) {
            @unlink($legacyPublicPath);
        }
    }
}
