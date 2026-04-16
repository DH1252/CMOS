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

        return \Inertia\Inertia::render(
            'pages/InformationBoardIndexPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Manajemen Artikel Informasi',
                    'description' => 'Kelola artikel dan status publikasi.',
                    'icon' => 'fas fa-newspaper',
                    'csrfToken' => csrf_token(),
                    'primaryAction' => [
                        'label' => 'Tulis Artikel',
                        'href' => route('information-boards.create'),
                        'icon' => 'fas fa-plus',
                    ],
                    'filters' => [
                        'action' => route('information-boards.index'),
                        'query' => $search,
                        'status' => $status,
                        'category' => $category,
                        'statusOptions' => [
                            ['value' => 'draft', 'label' => 'Draft'],
                            ['value' => 'published', 'label' => 'Published'],
                        ],
                        'categoryOptions' => $categories->map(fn ($cat) => ['value' => $cat->id, 'label' => $cat->name])->values(),
                    ],
                    'stats' => [
                        ['label' => 'Total Artikel', 'value' => $totalCount, 'icon' => 'fas fa-newspaper', 'tone' => 'primary'],
                        ['label' => 'Published', 'value' => $publishedCount, 'icon' => 'fas fa-check-circle', 'tone' => 'success'],
                        ['label' => 'Draft', 'value' => $draftCount, 'icon' => 'fas fa-pen-nib', 'tone' => 'warning'],
                    ],
                    'articles' => $informationBoards->getCollection()->map(function ($article) {
                        $canManage = auth()->user()->isAdmin() || $article->user_id === auth()->id();
                        $previewSource = html_entity_decode($article->excerpt ?: $article->content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $previewSource = str_replace("\u{00A0}", ' ', $previewSource);
                        $previewText = \Illuminate\Support\Str::squish(strip_tags($previewSource));

                        return [
                            'title' => $article->title,
                            'excerpt' => \Illuminate\Support\Str::limit($previewText, 180),
                            'coverImage' => $article->cover_image_url,
                            'coverThumb' => $article->cover_image_url,
                            'categories' => $article->categories->pluck('name')->values(),
                            'statusLabel' => ucfirst($article->status),
                            'statusTone' => $article->status === 'published' ? 'success' : 'secondary',
                            'author' => $article->user?->name ?? '-',
                            'date' => $article->created_at->toIso8601String(),
                            'showHref' => route('information-boards.show', $article),
                            'editHref' => $canManage ? route('information-boards.edit', $article) : null,
                            'deleteAction' => $canManage ? route('information-boards.destroy', $article) : null,
                            'confirm' => $article->title,
                            'confirmText' => "Hapus artikel {$article->title}?",
                        ];
                    })->values(),
                    'pagination' => [
                        'currentPage' => $informationBoards->currentPage(),
                        'lastPage' => $informationBoards->lastPage(),
                        'prevUrl' => $informationBoards->previousPageUrl(),
                        'nextUrl' => $informationBoards->nextPageUrl(),
                        'from' => $informationBoards->firstItem() ?? 0,
                        'to' => $informationBoards->lastItem() ?? 0,
                        'total' => $informationBoards->total(),
                    ],
                    'emptyState' => [
                        'title' => 'Belum ada artikel',
                        'text' => 'Mulai dengan menulis artikel pertama untuk papan informasi internal.',
                    ],
                ];

                return $props;
            })(compact(
                'informationBoards',
                'categories',
                'search',
                'status',
                'category',
                'totalCount',
                'publishedCount',
                'draftCount'
            )),
        );
    }

    public function create()
    {
        $categories = InformationCategory::orderBy('name')->get();

        return \Inertia\Inertia::render(
            'pages/InformationBoardEditorPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Form Artikel Baru',
                    'description' => 'Tulis artikel informasi baru dengan struktur editorial yang rapi untuk dibaca pengurus.',
                    'icon' => 'fas fa-pen',
                    'form' => [
                        'action' => route('information-boards.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'enctype' => 'multipart/form-data',
                        'attachmentUploadUrl' => route('information-boards.attachments.upload'),
                        'submitLabel' => 'Simpan',
                    ],
                    'article' => [
                        'title' => old('title'),
                        'excerpt' => old('excerpt'),
                        'content' => old('content'),
                        'status' => old('status', 'published'),
                        'publishMode' => old('publish_mode', 'immediately'),
                        'publishedAt' => old('published_at'),
                        'metaTitle' => old('meta_title'),
                        'metaDescription' => old('meta_description'),
                        'categoryIds' => old('category_ids', []),
                        'coverImage' => null,
                    ],
                    'categories' => $categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->values(),
                    'errors' => [
                        'title' => session('errors')?->first('title'),
                        'excerpt' => session('errors')?->first('excerpt'),
                        'content' => session('errors')?->first('content'),
                        'status' => session('errors')?->first('status'),
                        'publish_mode' => session('errors')?->first('publish_mode'),
                        'published_at' => session('errors')?->first('published_at'),
                        'meta_title' => session('errors')?->first('meta_title'),
                        'meta_description' => session('errors')?->first('meta_description'),
                        'cover_image' => session('errors')?->first('cover_image'),
                        'category_ids' => session('errors')?->first('category_ids'),
                        'category_ids_items' => session('errors')?->first('category_ids.*'),
                    ],
                    'cancelAction' => [
                        'href' => route('information-boards.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'editorId' => 'information-board-create-content',
                ];

                return $props;
            })(compact('categories')),
        );
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

    public function uploadAttachment(Request $request)
    {
        $validated = $request->validate([
            'attachment' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar',
        ]);

        $path = $validated['attachment']->store('information-boards/attachments', 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
            'href' => asset('storage/'.$path),
            'path' => $path,
            'filename' => $validated['attachment']->getClientOriginalName(),
            'filesize' => $validated['attachment']->getSize(),
            'contentType' => $validated['attachment']->getClientMimeType(),
        ], 201);
    }

    public function show(InformationBoard $informationBoard)
    {
        $informationBoard->load(['user', 'categories']);
        $latestArticles = InformationBoard::published()
            ->where('id', '!=', $informationBoard->id)
            ->latest('published_at')
            ->take(6)
            ->get();

        $canManage = request()->user()?->isAdmin() || $informationBoard->user_id === auth()->id();

        return \Inertia\Inertia::render(
            'pages/InformationBoardShowPage',
            array_merge([
                'article' => [
                    'title' => $informationBoard->title,
                    'coverImage' => $informationBoard->cover_image_url,
                    'badges' => [
                        [
                            'label' => ucfirst($informationBoard->status),
                            'tone' => $informationBoard->status === 'published' ? 'success' : 'secondary',
                        ],
                        ...$informationBoard->categories->map(fn ($category) => [
                            'label' => $category->name,
                            'tone' => 'info',
                        ])->values()->all(),
                    ],
                    'metadata' => [
                        [
                            'icon' => 'fas fa-calendar',
                            'label' => optional($informationBoard->publishedAtLocal)->toIso8601String()
                                ?? $informationBoard->created_at->setTimezone(config('app.client_timezone', 'Asia/Jakarta'))->toIso8601String(),
                        ],
                        [
                            'icon' => 'fas fa-user',
                            'label' => $informationBoard->user?->name ?? '-',
                        ],
                    ],
                    'contentHtml' => $informationBoard->content,
                    'backAction' => [
                        'href' => route('information-boards.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'editAction' => $canManage ? [
                        'href' => route('information-boards.edit', $informationBoard),
                        'label' => 'Edit Artikel',
                        'icon' => 'fas fa-edit',
                    ] : null,
                ],
                'latestArticles' => $latestArticles->map(fn ($latest) => [
                    'title' => $latest->title,
                    'date' => optional($latest->publishedAtLocal)->toIso8601String()
                        ?? $latest->created_at->setTimezone(config('app.client_timezone', 'Asia/Jakarta'))->toIso8601String(),
                    'href' => route('information-boards.show', $latest),
                ])->values(),
            ], [
                'pageTitle' => 'Papan Informasi',
                'pageMeta' => '',
            ]),
        );
    }

    public function edit(InformationBoard $informationBoard)
    {
        $this->authorizeEdit($informationBoard, request()->user());
        $categories = InformationCategory::orderBy('name')->get();

        return \Inertia\Inertia::render(
            'pages/InformationBoardEditorPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit Artikel: {$informationBoard->title}",
                    'description' => '',
                    'icon' => 'fas fa-edit',
                    'form' => [
                        'action' => route('information-boards.update', $informationBoard),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'enctype' => 'multipart/form-data',
                        'attachmentUploadUrl' => route('information-boards.attachments.upload'),
                        'submitLabel' => 'Update',
                    ],
                    'article' => [
                        'title' => old('title', $informationBoard->title),
                        'excerpt' => old('excerpt', $informationBoard->excerpt),
                        'content' => old('content', $informationBoard->content),
                        'status' => old('status', $informationBoard->status),
                        'publishMode' => old('publish_mode', $informationBoard->published_at?->isFuture() ? 'scheduled' : 'immediately'),
                        'publishedAt' => old('published_at', optional($informationBoard->published_at)?->setTimezone(config('app.client_timezone', 'Asia/Jakarta'))->format('Y-m-d\\TH:i')),
                        'metaTitle' => old('meta_title', $informationBoard->meta_title),
                        'metaDescription' => old('meta_description', $informationBoard->meta_description),
                        'categoryIds' => old('category_ids', $informationBoard->categories->pluck('id')->all()),
                        'coverImage' => $informationBoard->cover_image_url,
                    ],
                    'categories' => $categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->values(),
                    'errors' => [
                        'title' => session('errors')?->first('title'),
                        'excerpt' => session('errors')?->first('excerpt'),
                        'content' => session('errors')?->first('content'),
                        'status' => session('errors')?->first('status'),
                        'publish_mode' => session('errors')?->first('publish_mode'),
                        'published_at' => session('errors')?->first('published_at'),
                        'meta_title' => session('errors')?->first('meta_title'),
                        'meta_description' => session('errors')?->first('meta_description'),
                        'cover_image' => session('errors')?->first('cover_image'),
                        'category_ids' => session('errors')?->first('category_ids'),
                        'category_ids_items' => session('errors')?->first('category_ids.*'),
                    ],
                    'cancelAction' => [
                        'href' => route('information-boards.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'dangerAction' => [
                        'action' => route('information-boards.destroy', $informationBoard),
                        'method' => 'DELETE',
                        'label' => 'Hapus',
                        'icon' => 'fas fa-trash',
                        'confirm' => $informationBoard->title,
                        'confirmText' => "Hapus artikel {$informationBoard->title}?",
                    ],
                    'editorId' => 'information-board-edit-content',
                ];

                return $props;
            })(compact('informationBoard', 'categories')),
        );
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

        return strip_tags($clean, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote><figure><figcaption><img><action-text-attachment>');
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
