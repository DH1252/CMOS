<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UsefulLink;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index()
    {
        $links = UsefulLink::with('creator')
            ->active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        $linksByCategory = $links->groupBy('category');
        $categories = UsefulLink::getCategories();

        return \Inertia\Inertia::render(
            'pages/LinkDirectoryPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $canManage = auth()->user()->hasRole(['admin', 'bph']);

                $groups = collect($categories)
                    ->map(function ($category, $key) use ($linksByCategory, $canManage) {
                        $links = $linksByCategory->get($key, collect());

                        if ($links->isEmpty()) {
                            return null;
                        }

                        return [
                            'name' => $category['name'],
                            'icon' => $category['icon'],
                            'description' => match ($key) {
                                'template' => 'Template dan format kerja yang sering dipakai lintas kepengurusan.',
                                'tracker' => 'Tautan pelacakan progres, arsip, dan ritme operasional.',
                                'rules' => 'Dokumen rujukan untuk kebijakan, aturan, dan ketertiban kerja.',
                                'form' => 'Akses cepat ke form pengajuan, peminjaman, dan kebutuhan administrasi.',
                                'resource' => 'Perpustakaan file dan referensi penunjang kegiatan organisasi.',
                                default => 'Tautan umum yang sering dibuka oleh pengurus dalam pekerjaan harian.',
                            },
                            'cards' => $links->map(function ($link) use ($canManage) {
                                return [
                                    'title' => $link->title,
                                    'description' => $link->description,
                                    'href' => $link->url,
                                    'icon' => $link->icon ?: 'fas fa-link',
                                    'primaryLabel' => 'Buka Link',
                                    'badges' => array_values(array_filter([
                                        $link->sort_order ? ['label' => 'Urutan '.$link->sort_order, 'tone' => 'secondary'] : null,
                                        $link->creator?->name ? ['label' => $link->creator->name, 'tone' => 'info'] : null,
                                    ])),
                                    'meta' => array_values(array_filter([
                                        parse_url($link->url, PHP_URL_HOST) ? ['text' => parse_url($link->url, PHP_URL_HOST), 'muted' => true] : null,
                                    ])),
                                    'editHref' => $canManage ? route('links.edit', $link) : null,
                                    'deleteAction' => $canManage ? route('links.destroy', $link) : null,
                                    'deleteMethod' => 'DELETE',
                                    'confirm' => $link->title,
                                    'confirmText' => "Hapus link {$link->title}?",
                                ];
                            })->values(),
                        ];
                    })
                    ->filter()
                    ->values();

                $props = [
                    'title' => 'Link Berguna',
                    'description' => 'Template, tracker, dan referensi kerja.',
                    'icon' => 'fas fa-link',
                    'csrfToken' => csrf_token(),
                    'primaryAction' => $canManage ? [
                        'label' => 'Tambah Link',
                        'href' => route('links.create'),
                        'icon' => 'fas fa-plus',
                    ] : null,
                    'groups' => $groups,
                    'emptyState' => [
                        'title' => 'Belum ada link',
                        'text' => 'Admin atau BPH belum menambahkan tautan kerja untuk dibagikan ke pengurus.',
                    ],
                ];

                return $props;
            })(compact('links', 'linksByCategory', 'categories')),
        );
    }

    public function create()
    {
        $categories = UsefulLink::getCategories();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Form Tambah Link',
                    'description' => 'Tambahkan tautan kerja baru.',
                    'icon' => 'fas fa-link',
                    'form' => [
                        'action' => route('links.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Simpan',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('links.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Judul Link',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('title'),
                            'placeholder' => 'contoh: Template SOP Medfo',
                            'error' => session('errors')?->first('title'),
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'value' => old('description'),
                            'placeholder' => 'Deskripsi singkat tentang link ini',
                            'error' => session('errors')?->first('description'),
                            'rows' => 2,
                        ],
                        [
                            'name' => 'url',
                            'label' => 'URL',
                            'type' => 'url',
                            'required' => true,
                            'value' => old('url'),
                            'placeholder' => 'https://...',
                            'error' => session('errors')?->first('url'),
                        ],
                        [
                            'name' => 'category',
                            'label' => 'Kategori',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('category'),
                            'error' => session('errors')?->first('category'),
                            'span' => 'half',
                            'options' => collect($categories)->map(fn ($cat, $key) => ['value' => $key, 'label' => $cat['name']])->values(),
                        ],
                        [
                            'name' => 'sort_order',
                            'label' => 'Urutan',
                            'type' => 'number',
                            'value' => old('sort_order', 0),
                            'error' => session('errors')?->first('sort_order'),
                            'span' => 'half',
                            'min' => 0,
                        ],
                        [
                            'name' => 'icon',
                            'label' => 'Icon (Font Awesome)',
                            'type' => 'text',
                            'value' => old('icon', 'fas fa-link'),
                            'placeholder' => 'fas fa-link',
                            'error' => session('errors')?->first('icon'),
                            'note' => 'Contoh: fas fa-file-alt, fas fa-chart-line, fas fa-gavel.',
                        ],
                    ],
                ];

                return $props;
            })(compact('categories')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['icon'] = $validated['icon'] ?? 'fas fa-link';

        $link = UsefulLink::create($validated);

        ActivityLog::log('created', "Created link: {$link->title}", $link);

        return redirect()->route('links.index')
            ->with('success', 'Link berhasil ditambahkan!');
    }

    public function edit(UsefulLink $link)
    {
        $categories = UsefulLink::getCategories();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit Link: {$link->title}",
                    'description' => 'Perbarui tautan, kategori, dan status aktifnya agar direktori tetap rapi dan relevan.',
                    'icon' => 'fas fa-link',
                    'form' => [
                        'action' => route('links.update', $link),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Update',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('links.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'dangerAction' => [
                        'action' => route('links.destroy', $link),
                        'method' => 'DELETE',
                        'label' => 'Hapus',
                        'icon' => 'fas fa-trash',
                        'confirm' => $link->title,
                        'confirmText' => "Hapus link {$link->title}?",
                    ],
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Judul Link',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('title', $link->title),
                            'error' => session('errors')?->first('title'),
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'value' => old('description', $link->description),
                            'error' => session('errors')?->first('description'),
                            'rows' => 2,
                        ],
                        [
                            'name' => 'url',
                            'label' => 'URL',
                            'type' => 'url',
                            'required' => true,
                            'value' => old('url', $link->url),
                            'error' => session('errors')?->first('url'),
                        ],
                        [
                            'name' => 'category',
                            'label' => 'Kategori',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('category', $link->category),
                            'error' => session('errors')?->first('category'),
                            'span' => 'half',
                            'options' => collect($categories)->map(fn ($cat, $key) => ['value' => $key, 'label' => $cat['name']])->values(),
                        ],
                        [
                            'name' => 'sort_order',
                            'label' => 'Urutan',
                            'type' => 'number',
                            'value' => old('sort_order', $link->sort_order),
                            'error' => session('errors')?->first('sort_order'),
                            'span' => 'half',
                            'min' => 0,
                        ],
                        [
                            'name' => 'icon',
                            'label' => 'Icon (Font Awesome)',
                            'type' => 'text',
                            'value' => old('icon', $link->icon),
                            'error' => session('errors')?->first('icon'),
                            'note' => 'Gunakan kelas Font Awesome yang konsisten dengan kategori link.',
                        ],
                        [
                            'name' => 'is_active',
                            'label' => 'Aktif',
                            'type' => 'checkbox',
                            'value' => old('is_active', $link->is_active),
                            'error' => session('errors')?->first('is_active'),
                            'note' => 'Nonaktifkan bila link tidak lagi perlu tampil di direktori.',
                            'span' => 'half',
                        ],
                    ],
                ];

                return $props;
            })(compact('link', 'categories')),
        );
    }

    public function update(Request $request, UsefulLink $link)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $link->update($validated);

        ActivityLog::log('updated', "Updated link: {$link->title}", $link);

        return redirect()->route('links.index')
            ->with('success', 'Link berhasil diupdate!');
    }

    public function destroy(UsefulLink $link)
    {
        $title = $link->title;

        ActivityLog::log('deleted', "Deleted link: {$title}", $link);

        $link->delete();

        return redirect()->route('links.index')
            ->with('success', "Link {$title} berhasil dihapus!");
    }
}
