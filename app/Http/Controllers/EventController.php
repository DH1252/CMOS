<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\UploadedImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        $query = Event::with(['user'])->orderByDesc('starts_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['draft', 'published'], true)) {
            $query->where('status', $status);
        }

        $events = $query->paginate(12)->withQueryString();

        return \Inertia\Inertia::render('pages/EventIndexPage', [
            'title' => 'Manajemen Acara',
            'description' => 'Kelola acara mendatang dan status publikasinya.',
            'icon' => 'fas fa-calendar-days',
            'csrfToken' => csrf_token(),
            'primaryAction' => [
                'label' => 'Tambah Acara',
                'href' => route('events.create'),
                'icon' => 'fas fa-plus',
            ],
            'filters' => [
                'action' => route('events.index'),
                'query' => $search,
                'status' => $status,
                'statusOptions' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'published', 'label' => 'Published'],
                ],
            ],
            'stats' => [
                ['label' => 'Total', 'value' => Event::count(), 'icon' => 'fas fa-calendar-days', 'tone' => 'primary'],
                ['label' => 'Published', 'value' => Event::where('status', 'published')->count(), 'icon' => 'fas fa-check-circle', 'tone' => 'success'],
                ['label' => 'Mendatang', 'value' => Event::published()->upcoming()->count(), 'icon' => 'fas fa-clock', 'tone' => 'info'],
            ],
            'events' => $events->getCollection()->map(function (Event $event) {
                $canManage = auth()->user()->isAdmin() || $event->user_id === auth()->id();

                return [
                    'title' => $event->title,
                    'location' => $event->location,
                    'poster' => $event->poster_image_optimized,
                    'startsAt' => optional($event->startsAtLocal)->toIso8601String(),
                    'statusLabel' => ucfirst($event->status),
                    'statusTone' => $event->status === 'published' ? 'success' : 'secondary',
                    'author' => $event->user?->name ?? '-',
                    'showHref' => route('events.show', $event),
                    'editHref' => $canManage ? route('events.edit', $event) : null,
                    'deleteAction' => $canManage ? route('events.destroy', $event) : null,
                    'confirmText' => "Hapus acara {$event->title}?",
                ];
            })->values(),
            'pagination' => [
                'currentPage' => $events->currentPage(),
                'lastPage' => $events->lastPage(),
                'prevUrl' => $events->previousPageUrl(),
                'nextUrl' => $events->nextPageUrl(),
                'from' => $events->firstItem() ?? 0,
                'to' => $events->lastItem() ?? 0,
                'total' => $events->total(),
            ],
            'emptyState' => [
                'title' => 'Belum ada acara',
                'text' => 'Tambahkan acara pertama untuk ditampilkan di halaman publik.',
            ],
        ]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('pages/EventEditorPage', [
            'title' => 'Form Acara Baru',
            'description' => 'Tambahkan acara mendatang untuk dipublikasikan ke halaman publik.',
            'icon' => 'fas fa-calendar-plus',
            'form' => [
                'action' => route('events.store'),
                'method' => 'POST',
                'csrfToken' => csrf_token(),
                'enctype' => 'multipart/form-data',
                'submitLabel' => 'Simpan',
            ],
            'event' => [
                'title' => old('title'),
                'description' => old('description'),
                'location' => old('location'),
                'startsAt' => old('starts_at'),
                'endsAt' => old('ends_at'),
                'status' => old('status', 'published'),
                'publishMode' => old('publish_mode', 'immediately'),
                'publishedAt' => old('published_at'),
                'metaTitle' => old('meta_title'),
                'metaDescription' => old('meta_description'),
                'poster' => null,
            ],
            'errors' => $this->editorErrors(),
            'cancelAction' => [
                'href' => route('events.index'),
                'label' => 'Kembali',
                'icon' => 'fas fa-arrow-left',
            ],
            'editorId' => 'event-create-description',
        ]);
    }

    public function store(Request $request, UploadedImageOptimizer $imageOptimizer, HtmlSanitizer $sanitizer)
    {
        $validated = $this->validatePayload($request);

        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $validated['user_id'] = $user->id;
        $validated['slug'] = Event::generateUniqueSlug($validated['title']);
        $validated['description'] = $sanitizer->sanitize($validated['description']);
        $validated['starts_at'] = $this->parseLocalDateTime($validated['starts_at']);
        $validated['ends_at'] = isset($validated['ends_at']) ? $this->parseLocalDateTime($validated['ends_at']) : null;
        $validated['published_at'] = $this->resolvePublishedAt(
            $validated['status'],
            $validated['publish_mode'] ?? 'immediately',
            $validated['published_at'] ?? null,
        );
        unset($validated['publish_mode']);

        if ($request->hasFile('poster_image')) {
            $validated['poster_image'] = $imageOptimizer->store(
                $request->file('poster_image'),
                'events',
            )['path'];
        }

        $event = Event::create($validated);

        ActivityLog::log('created', "Created event: {$event->title}", $event);

        return redirect()->route('events.index')
            ->with('success', 'Acara berhasil ditambahkan.');
    }

    public function show(Event $event)
    {
        $event->load(['user']);
        $upcomingEvents = Event::published()
            ->upcoming()
            ->where('id', '!=', $event->id)
            ->take(6)
            ->get();

        $canManage = request()->user()?->isAdmin() || $event->user_id === auth()->id();

        return \Inertia\Inertia::render('pages/EventShowPage', [
            'event' => [
                'title' => $event->title,
                'location' => $event->location,
                'poster' => $event->poster_image_optimized,
                'badges' => [
                    [
                        'label' => ucfirst($event->status),
                        'tone' => $event->status === 'published' ? 'success' : 'secondary',
                    ],
                ],
                'startsAt' => optional($event->startsAtLocal)->toIso8601String(),
                'endsAt' => optional($event->endsAtLocal)->toIso8601String(),
                'contentHtml' => $event->description_optimized,
                'backAction' => [
                    'href' => route('events.index'),
                    'label' => 'Kembali',
                    'icon' => 'fas fa-arrow-left',
                ],
                'editAction' => $canManage ? [
                    'href' => route('events.edit', $event),
                    'label' => 'Edit Acara',
                    'icon' => 'fas fa-edit',
                ] : null,
            ],
            'upcomingEvents' => $upcomingEvents->map(fn (Event $item) => [
                'title' => $item->title,
                'startsAt' => optional($item->startsAtLocal)->toIso8601String(),
                'href' => route('events.show', $item),
            ])->values(),
        ]);
    }

    public function edit(Event $event)
    {
        $this->authorizeEdit($event, request()->user());

        return \Inertia\Inertia::render('pages/EventEditorPage', [
            'title' => "Edit Acara: {$event->title}",
            'description' => '',
            'icon' => 'fas fa-edit',
            'form' => [
                'action' => route('events.update', $event),
                'method' => 'PUT',
                'csrfToken' => csrf_token(),
                'enctype' => 'multipart/form-data',
                'submitLabel' => 'Update',
            ],
            'event' => [
                'title' => old('title', $event->title),
                'description' => old('description', $event->description),
                'location' => old('location', $event->location),
                'startsAt' => old('starts_at', optional($event->startsAtLocal)?->format('Y-m-d\TH:i')),
                'endsAt' => old('ends_at', optional($event->endsAtLocal)?->format('Y-m-d\TH:i')),
                'status' => old('status', $event->status),
                'publishMode' => old('publish_mode', $event->published_at?->isFuture() ? 'scheduled' : 'immediately'),
                'publishedAt' => old('published_at', optional($event->publishedAtLocal)?->format('Y-m-d\TH:i')),
                'metaTitle' => old('meta_title', $event->meta_title),
                'metaDescription' => old('meta_description', $event->meta_description),
                'poster' => $event->poster_image_optimized,
            ],
            'errors' => $this->editorErrors(),
            'cancelAction' => [
                'href' => route('events.index'),
                'label' => 'Kembali',
                'icon' => 'fas fa-arrow-left',
            ],
            'dangerAction' => [
                'action' => route('events.destroy', $event),
                'method' => 'DELETE',
                'label' => 'Hapus',
                'icon' => 'fas fa-trash',
                'confirmText' => "Hapus acara {$event->title}?",
            ],
            'editorId' => 'event-edit-description',
        ]);
    }

    public function update(Request $request, Event $event, UploadedImageOptimizer $imageOptimizer, HtmlSanitizer $sanitizer)
    {
        $this->authorizeEdit($event, $request->user());

        $validated = $this->validatePayload($request);

        $validated['slug'] = Event::generateUniqueSlug($validated['title'], $event->id);
        $validated['description'] = $sanitizer->sanitize($validated['description']);
        $validated['starts_at'] = $this->parseLocalDateTime($validated['starts_at']);
        $validated['ends_at'] = isset($validated['ends_at']) ? $this->parseLocalDateTime($validated['ends_at']) : null;
        $validated['published_at'] = $this->resolvePublishedAt(
            $validated['status'],
            $validated['publish_mode'] ?? 'immediately',
            $validated['published_at'] ?? null,
        );
        unset($validated['publish_mode']);

        if ($request->hasFile('poster_image')) {
            $this->deletePoster($event->poster_image);
            $validated['poster_image'] = $imageOptimizer->store(
                $request->file('poster_image'),
                'events',
            )['path'];
        }

        $event->update($validated);

        ActivityLog::log('updated', "Updated event: {$event->title}", $event);

        return redirect()->route('events.index')
            ->with('success', 'Acara berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEdit($event, request()->user());

        $this->deletePoster($event->poster_image);

        $title = $event->title;
        ActivityLog::log('deleted', "Deleted event: {$title}", $event);
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', "Acara {$title} berhasil dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'poster_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/avif,image/webp|max:2048',
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
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function editorErrors(): array
    {
        $errors = session('errors');

        return [
            'title' => $errors?->first('title'),
            'description' => $errors?->first('description'),
            'location' => $errors?->first('location'),
            'starts_at' => $errors?->first('starts_at'),
            'ends_at' => $errors?->first('ends_at'),
            'status' => $errors?->first('status'),
            'publish_mode' => $errors?->first('publish_mode'),
            'published_at' => $errors?->first('published_at'),
            'meta_title' => $errors?->first('meta_title'),
            'meta_description' => $errors?->first('meta_description'),
            'poster_image' => $errors?->first('poster_image'),
        ];
    }

    private function authorizeEdit(Event $event, ?User $user): void
    {
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->isAdmin() && $event->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola acara ini.');
        }
    }

    private function parseLocalDateTime(string $value): Carbon
    {
        $tz = config('app.client_timezone', 'Asia/Jakarta');

        return Carbon::parse($value, $tz)->setTimezone('UTC');
    }

    private function resolvePublishedAt(string $status, string $publishMode, mixed $publishedAt): mixed
    {
        if ($status === 'draft') {
            return null;
        }

        if ($publishMode !== 'scheduled' || ! $publishedAt) {
            return now('UTC');
        }

        $tz = config('app.client_timezone');

        return Carbon::parse($publishedAt, $tz)->setTimezone('UTC');
    }

    private function deletePoster(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $legacy = public_path('storage/'.$path);

        if (is_file($legacy)) {
            @unlink($legacy);
        }
    }
}
