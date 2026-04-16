<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cabinet;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function index()
    {
        $cabinets = Cabinet::withCount('departments')
            ->orderByDesc('year')
            ->get();

        return \Inertia\Inertia::render(
            'pages/CrudTablePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Daftar Kabinet',
                    'description' => 'Struktur kepengurusan ditata per periode agar perpindahan kabinet, departemen, dan status aktif tetap terbaca jelas.',
                    'icon' => 'fas fa-landmark',
                    'csrfToken' => csrf_token(),
                    'enableDataTable' => true,
                    'primaryAction' => [
                        'label' => 'Tambah Kabinet',
                        'href' => route('cabinets.create'),
                        'icon' => 'fas fa-plus',
                    ],
                    'columns' => [
                        ['label' => 'Kabinet'],
                        ['label' => 'Departemen'],
                        ['label' => 'Status'],
                        ['label' => 'Aksi', 'width' => '120px'],
                    ],
                    'rows' => $cabinets->map(function ($cabinet) {
                        return [
                            'cells' => [
                                [
                                    'type' => 'stack',
                                    'lines' => [
                                        ['text' => $cabinet->name, 'href' => route('cabinets.show', $cabinet), 'className' => 'fw-semibold'],
                                        ['text' => $cabinet->year, 'muted' => true],
                                    ],
                                ],
                                ['type' => 'badge', 'label' => "{$cabinet->departments_count} departemen", 'tone' => 'info'],
                                [
                                    'type' => 'badge',
                                    'label' => $cabinet->status === 'active' ? 'Active' : 'Inactive',
                                    'tone' => $cabinet->status === 'active' ? 'success' : 'secondary',
                                    'icon' => $cabinet->status === 'active' ? 'fas fa-star' : null,
                                ],
                                [
                                    'type' => 'actions',
                                    'items' => [
                                        ['href' => route('cabinets.show', $cabinet), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                                        ['href' => route('cabinets.edit', $cabinet), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                                        [
                                            'action' => route('cabinets.destroy', $cabinet),
                                            'method' => 'DELETE',
                                            'label' => 'Hapus',
                                            'icon' => 'fas fa-trash',
                                            'tone' => 'danger',
                                            'confirm' => $cabinet->name,
                                            'confirmText' => "Hapus kabinet {$cabinet->name}?",
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada kabinet',
                        'text' => 'Tambahkan periode kepengurusan baru untuk mulai menyusun struktur organisasi.',
                    ],
                ];

                return $props;
            })(compact('cabinets')),
        );
    }

    public function create()
    {
        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (): array {
                $props = [
                    'title' => 'Form Tambah Kabinet',
                    'description' => 'Buat periode kepengurusan baru dengan penamaan yang rapi agar transisi organisasi tetap tertelusur.',
                    'icon' => 'fas fa-landmark',
                    'form' => [
                        'action' => route('cabinets.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Simpan',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('cabinets.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        [
                            'name' => 'name',
                            'label' => 'Nama Kabinet',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('name'),
                            'placeholder' => 'contoh: Kabinet Harmoni',
                            'error' => session('errors')?->first('name'),
                        ],
                        [
                            'name' => 'year',
                            'label' => 'Tahun Kepengurusan',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('year'),
                            'placeholder' => 'contoh: 2025/2026',
                            'error' => session('errors')?->first('year'),
                            'span' => 'half',
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', 'active'),
                            'error' => session('errors')?->first('status'),
                            'span' => 'half',
                            'note' => 'Hanya satu kabinet yang bisa active pada satu waktu.',
                            'options' => [
                                ['value' => 'active', 'label' => 'Active (Periode Berjalan)'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:9',
            'status' => 'required|in:active,inactive',
        ]);

        // If new cabinet is active, deactivate others
        if ($validated['status'] === 'active') {
            Cabinet::where('status', 'active')->update(['status' => 'inactive']);
        }

        $cabinet = Cabinet::create($validated);

        ActivityLog::log('created', "Created cabinet: {$cabinet->name}", $cabinet);

        return redirect()->route('cabinets.index')
            ->with('success', 'Kabinet berhasil ditambahkan!');
    }

    public function show(Cabinet $cabinet)
    {
        $cabinet->load(['departments.users.role', 'departments.programs']);

        return \Inertia\Inertia::render(
            'pages/EntityDetailPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $cabinet->loadMissing(['departments.users.role', 'departments.programs']);

                $activeDepartments = $cabinet->departments->where('status', 'active')->count();
                $totalMembers = $cabinet->departments->sum(fn ($department) => $department->users->count());
                $totalPrograms = $cabinet->departments->sum(fn ($department) => $department->programs->count());

                $props = [
                    'csrfToken' => csrf_token(),
                    'summary' => [
                        'icon' => 'fas fa-landmark',
                        'title' => $cabinet->name,
                        'subtitle' => $cabinet->year,
                        'badges' => [
                            [
                                'label' => $cabinet->status === 'active' ? 'Kabinet Aktif' : 'Inactive',
                                'tone' => $cabinet->status === 'active' ? 'success' : 'secondary',
                                'icon' => $cabinet->status === 'active' ? 'fas fa-star' : null,
                            ],
                        ],
                        'description' => 'Kabinet ini menjadi payung untuk struktur departemen, jumlah anggota, dan distribusi program kerja pada periode terkait.',
                        'facts' => [
                            ['label' => 'Total Departemen', 'value' => $cabinet->departments->count()],
                            ['label' => 'Departemen Aktif', 'value' => $activeDepartments],
                            ['label' => 'Total Anggota', 'value' => $totalMembers.' orang'],
                            ['label' => 'Total Proker', 'value' => $totalPrograms.' proker'],
                        ],
                        'actions' => [
                            ['href' => route('cabinets.edit', $cabinet), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            ['href' => route('cabinets.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                        ],
                    ],
                    'stats' => [
                        ['label' => 'Departemen', 'value' => $cabinet->departments->count(), 'icon' => 'fas fa-building', 'tone' => 'primary'],
                        ['label' => 'Anggota', 'value' => $totalMembers, 'icon' => 'fas fa-users', 'tone' => 'info'],
                        ['label' => 'Proker', 'value' => $totalPrograms, 'icon' => 'fas fa-diagram-project', 'tone' => 'warning'],
                    ],
                    'sections' => [
                        [
                            'kind' => 'table',
                            'title' => 'Departemen',
                            'icon' => 'fas fa-building',
                            'columns' => [
                                ['label' => 'Nama'],
                                ['label' => 'Kepala Departemen'],
                                ['label' => 'Anggota'],
                                ['label' => 'Proker'],
                                ['label' => 'Status'],
                            ],
                            'rows' => $cabinet->departments->map(function ($department) {
                                $head = $department->users->first(function ($user) {
                                    return $user->role?->name === 'kabinet';
                                });

                                return [
                                    'cells' => [
                                        [
                                            'type' => 'stack',
                                            'lines' => [
                                                ['text' => $department->name, 'href' => route('departments.show', $department), 'className' => 'fw-semibold'],
                                            ],
                                        ],
                                        $head
                                            ? ['type' => 'avatar', 'image' => $head->avatar_url, 'title' => $head->name, 'subtitle' => $head->email]
                                            : ['type' => 'text', 'text' => '-', 'muted' => true],
                                        ['type' => 'badge', 'label' => $department->users->count().' orang', 'tone' => 'info'],
                                        ['type' => 'badge', 'label' => $department->programs->count().' proker', 'tone' => 'primary'],
                                        ['type' => 'badge', 'label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
                                    ],
                                ];
                            })->values(),
                            'emptyText' => 'Belum ada departemen di kabinet ini.',
                            'spacingClass' => 'mb-0',
                        ],
                    ],
                ];

                return $props;
            })(compact('cabinet')),
        );
    }

    public function edit(Cabinet $cabinet)
    {
        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit Kabinet: {$cabinet->name}",
                    'description' => 'Perbarui periode kepengurusan dan status kabinet tanpa menghilangkan konteks departemen yang sudah terhubung.',
                    'icon' => 'fas fa-landmark',
                    'form' => [
                        'action' => route('cabinets.update', $cabinet),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Update',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('cabinets.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        [
                            'name' => 'name',
                            'label' => 'Nama Kabinet',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('name', $cabinet->name),
                            'error' => session('errors')?->first('name'),
                        ],
                        [
                            'name' => 'year',
                            'label' => 'Tahun Kepengurusan',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('year', $cabinet->year),
                            'error' => session('errors')?->first('year'),
                            'span' => 'half',
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', $cabinet->status),
                            'error' => session('errors')?->first('status'),
                            'span' => 'half',
                            'note' => 'Hanya satu kabinet yang bisa active pada satu waktu.',
                            'options' => [
                                ['value' => 'active', 'label' => 'Active (Periode Berjalan)'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(compact('cabinet')),
        );
    }

    public function update(Request $request, Cabinet $cabinet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:9',
            'status' => 'required|in:active,inactive',
        ]);

        // If changing to active, deactivate others
        if ($validated['status'] === 'active' && $cabinet->status !== 'active') {
            Cabinet::where('status', 'active')
                ->where('id', '!=', $cabinet->id)
                ->update(['status' => 'inactive']);
        }

        $cabinet->update($validated);

        ActivityLog::log('updated', "Updated cabinet: {$cabinet->name}", $cabinet);

        return redirect()->route('cabinets.index')
            ->with('success', 'Kabinet berhasil diupdate!');
    }

    public function destroy(Cabinet $cabinet)
    {
        $name = $cabinet->name;

        if ($cabinet->departments()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kabinet yang masih memiliki departemen!');
        }

        ActivityLog::log('deleted', "Deleted cabinet: {$name}", $cabinet);

        $cabinet->delete();

        return redirect()->route('cabinets.index')
            ->with('success', "Kabinet {$name} berhasil dihapus!");
    }
}
