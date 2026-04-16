<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cabinet;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['cabinet', 'users'])
            ->withCount(['users', 'programs'])
            ->orderBy('name')
            ->get();

        return \Inertia\Inertia::render(
            'pages/CrudTablePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Daftar Departemen',
                    'description' => 'Setiap departemen membawa anggota, program kerja, dan konteks operasionalnya masing-masing.',
                    'icon' => 'fas fa-building',
                    'csrfToken' => csrf_token(),
                    'enableDataTable' => true,
                    'primaryAction' => [
                        'label' => 'Tambah Departemen',
                        'href' => route('departments.create'),
                        'icon' => 'fas fa-plus',
                    ],
                    'columns' => [
                        ['label' => 'Nama'],
                        ['label' => 'Deskripsi'],
                        ['label' => 'Kabinet'],
                        ['label' => 'Anggota'],
                        ['label' => 'Proker'],
                        ['label' => 'Status'],
                        ['label' => 'Aksi', 'width' => '120px'],
                    ],
                    'rows' => $departments->map(function ($department) {
                        return [
                            'cells' => [
                                ['type' => 'text', 'text' => $department->name, 'href' => route('departments.show', $department), 'className' => 'fw-semibold'],
                                ['type' => 'text', 'text' => \Illuminate\Support\Str::limit($department->description ?: '-', 58), 'muted' => true],
                                ['type' => 'text', 'text' => $department->cabinet?->name ?? '-', 'muted' => ! $department->cabinet],
                                ['type' => 'badge', 'label' => "{$department->users_count} orang", 'tone' => 'info'],
                                ['type' => 'badge', 'label' => "{$department->programs_count} proker", 'tone' => 'primary'],
                                ['type' => 'badge', 'label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
                                [
                                    'type' => 'actions',
                                    'items' => [
                                        ['href' => route('departments.show', $department), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                                        ['href' => route('departments.edit', $department), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                                        [
                                            'action' => route('departments.destroy', $department),
                                            'method' => 'DELETE',
                                            'label' => 'Hapus',
                                            'icon' => 'fas fa-trash',
                                            'tone' => 'danger',
                                            'confirm' => $department->name,
                                            'confirmText' => "Hapus departemen {$department->name}?",
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada departemen',
                        'text' => 'Tambahkan departemen untuk membangun struktur kerja organisasi.',
                    ],
                ];

                return $props;
            })(compact('departments')),
        );
    }

    public function create()
    {
        $cabinets = Cabinet::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Form Tambah Departemen',
                    'description' => 'Buat departemen baru dan hubungkan ke kabinet aktif yang relevan.',
                    'icon' => 'fas fa-building',
                    'form' => [
                        'action' => route('departments.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Simpan',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('departments.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        ['name' => 'name', 'label' => 'Nama Departemen', 'type' => 'text', 'required' => true, 'value' => old('name'), 'error' => session('errors')?->first('name')],
                        ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description'), 'error' => session('errors')?->first('description'), 'rows' => 3],
                        [
                            'name' => 'cabinet_id',
                            'label' => 'Kabinet',
                            'type' => 'select',
                            'value' => old('cabinet_id'),
                            'error' => session('errors')?->first('cabinet_id'),
                            'placeholder' => '-- Pilih Kabinet --',
                            'span' => 'half',
                            'options' => $cabinets->map(fn ($cabinet) => ['value' => $cabinet->id, 'label' => "{$cabinet->name} ({$cabinet->year})"])->values(),
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', 'active'),
                            'error' => session('errors')?->first('status'),
                            'span' => 'half',
                            'options' => [
                                ['value' => 'active', 'label' => 'Active'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(compact('cabinets')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cabinet_id' => 'nullable|exists:cabinets,id',
            'status' => 'required|in:active,inactive',
        ]);

        $department = Department::create($validated);

        ActivityLog::log('created', "Created department: {$department->name}", $department);

        return redirect()->route('departments.index')
            ->with('success', 'Departemen berhasil ditambahkan!');
    }

    public function show(Department $department)
    {
        $department->load(['cabinet', 'users.role', 'programs']);

        return \Inertia\Inertia::render(
            'pages/EntityDetailPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $department->loadMissing(['users.role', 'programs.department']);

                $props = [
                    'csrfToken' => csrf_token(),
                    'summary' => [
                        'icon' => 'fas fa-building',
                        'title' => $department->name,
                        'badges' => [
                            ['label' => ucfirst($department->status), 'tone' => $department->status === 'active' ? 'success' : 'secondary'],
                        ],
                        'description' => $department->description,
                        'facts' => [
                            ['label' => 'Kabinet', 'value' => $department->cabinet?->name ?? '-'],
                            ['label' => 'Total Anggota', 'value' => $department->users->count().' orang'],
                            ['label' => 'Total Proker', 'value' => $department->programs->count().' proker'],
                        ],
                        'actions' => [
                            ['href' => route('departments.edit', $department), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            ['href' => route('departments.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                        ],
                    ],
                    'sections' => [
                        [
                            'kind' => 'table',
                            'title' => 'Anggota Departemen',
                            'icon' => 'fas fa-users',
                            'columns' => [
                                ['label' => 'Nama'],
                                ['label' => 'Email'],
                                ['label' => 'Role'],
                                ['label' => 'Status'],
                            ],
                            'rows' => $department->users->map(function ($user) {
                                return [
                                    'cells' => [
                                        ['type' => 'avatar', 'image' => $user->avatar_url, 'title' => $user->name],
                                        ['type' => 'text', 'text' => $user->email],
                                        ['type' => 'badge', 'label' => ucfirst($user->role?->name ?? '-'), 'tone' => $user->role?->name === 'kabinet' ? 'info' : 'secondary'],
                                        ['type' => 'badge', 'label' => ucfirst($user->status), 'tone' => $user->status === 'active' ? 'success' : 'secondary'],
                                    ],
                                ];
                            })->values(),
                            'emptyText' => 'Belum ada anggota.',
                        ],
                        [
                            'kind' => 'table',
                            'title' => 'Program Kerja',
                            'icon' => 'fas fa-diagram-project',
                            'columns' => [
                                ['label' => 'Nama Proker'],
                                ['label' => 'Periode'],
                                ['label' => 'Status'],
                            ],
                            'rows' => $department->programs->map(function ($program) {
                                return [
                                    'cells' => [
                                        ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                                        ['type' => 'text', 'text' => $program->start_date->format('d M').' - '.$program->end_date->format('d M Y'), 'muted' => true],
                                        ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match ($program->status) {
                                            'completed' => 'success',
                                            'active' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'secondary',
                                        }],
                                    ],
                                ];
                            })->values(),
                            'emptyText' => 'Belum ada program kerja.',
                            'spacingClass' => 'mb-0',
                        ],
                    ],
                ];

                return $props;
            })(compact('department')),
        );
    }

    public function edit(Department $department)
    {
        $cabinets = Cabinet::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit Departemen: {$department->name}",
                    'description' => 'Perbarui identitas departemen tanpa memutus konteks kabinet dan operasionalnya.',
                    'icon' => 'fas fa-building',
                    'form' => [
                        'action' => route('departments.update', $department),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Update',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('departments.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        ['name' => 'name', 'label' => 'Nama Departemen', 'type' => 'text', 'required' => true, 'value' => old('name', $department->name), 'error' => session('errors')?->first('name')],
                        ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description', $department->description), 'error' => session('errors')?->first('description'), 'rows' => 3],
                        [
                            'name' => 'cabinet_id',
                            'label' => 'Kabinet',
                            'type' => 'select',
                            'value' => old('cabinet_id', $department->cabinet_id),
                            'error' => session('errors')?->first('cabinet_id'),
                            'placeholder' => '-- Pilih Kabinet --',
                            'span' => 'half',
                            'options' => $cabinets->map(fn ($cabinet) => ['value' => $cabinet->id, 'label' => "{$cabinet->name} ({$cabinet->year})"])->values(),
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', $department->status),
                            'error' => session('errors')?->first('status'),
                            'span' => 'half',
                            'options' => [
                                ['value' => 'active', 'label' => 'Active'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(compact('department', 'cabinets')),
        );
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cabinet_id' => 'nullable|exists:cabinets,id',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        ActivityLog::log('updated', "Updated department: {$department->name}", $department);

        return redirect()->route('departments.index')
            ->with('success', 'Departemen berhasil diupdate!');
    }

    public function destroy(Department $department)
    {
        $name = $department->name;

        if ($department->users()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus departemen yang masih memiliki anggota!');
        }

        ActivityLog::log('deleted', "Deleted department: {$name}", $department);

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', "Departemen {$name} berhasil dihapus!");
    }
}
