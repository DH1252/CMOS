<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\DriveAccount;
use Illuminate\Http\Request;

class DriveController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = DriveAccount::with('department')
            ->active()
            ->orderBy('name');

        // Staff and Kabinet only see their department's drive
        if ($user->isStaff() || $user->isKabinet()) {
            $query->where('department_id', $user->department_id);
        }

        $drives = $query->get();

        // Group by department for display
        $drivesByDept = $drives->groupBy(fn ($d) => $d->department?->name ?? 'Umum');

        return \Inertia\Inertia::render(
            'pages/DriveDirectoryPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $canManage = auth()->user()->hasRole(['admin', 'bph']);

                $props = [
                    'title' => 'Google Drive Organisasi',
                    'description' => 'Akses folder Google Drive yang terhubung dengan departemen atau kebutuhan umum organisasi.',
                    'icon' => 'fab fa-google-drive',
                    'csrfToken' => csrf_token(),
                    'primaryAction' => $canManage ? [
                        'label' => 'Tambah Drive',
                        'href' => route('drives.create'),
                        'icon' => 'fas fa-plus',
                    ] : null,
                    'groups' => $drivesByDept->map(function ($drives, $deptName) use ($canManage) {
                        return [
                            'name' => $deptName,
                            'icon' => 'fab fa-google-drive',
                            'description' => $deptName === 'Umum'
                                ? 'Folder bersama untuk kebutuhan lintas departemen dan arsip umum organisasi.'
                                : "Folder Google Drive untuk operasional {$deptName}.",
                            'cards' => $drives->map(function ($drive) use ($canManage) {
                                return [
                                    'id' => $drive->id,
                                    'title' => $drive->name,
                                    'description' => 'Gunakan kredensial ini untuk membuka folder kerja dan arsip yang terkait dengan aktivitas organisasi.',
                                    'href' => $drive->drive_url,
                                    'icon' => 'fab fa-google-drive',
                                    'email' => $drive->email,
                                    'password' => $drive->password,
                                    'badges' => [
                                        ['label' => $drive->department?->name ?? 'Umum', 'tone' => $drive->department ? 'info' : 'secondary'],
                                    ],
                                    'meta' => [
                                        ['text' => $drive->is_active ? 'Aktif dan siap dipakai' : 'Nonaktif', 'muted' => ! $drive->is_active],
                                    ],
                                    'editHref' => $canManage ? route('drives.edit', $drive) : null,
                                    'deleteAction' => $canManage ? route('drives.destroy', $drive) : null,
                                    'deleteMethod' => 'DELETE',
                                    'confirm' => $drive->name,
                                    'confirmText' => "Hapus akun drive {$drive->name}?",
                                ];
                            })->values(),
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada drive',
                        'text' => 'Admin belum menambahkan akun Google Drive untuk kebutuhan organisasi.',
                    ],
                ];

                return $props;
            })(compact('drives', 'drivesByDept')),
        );
    }

    public function create()
    {
        $departments = Department::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Form Tambah Drive',
                    'description' => 'Tambahkan akun Google Drive baru agar akses arsip dan dokumen bersama bisa dibagikan dengan struktur yang jelas.',
                    'icon' => 'fab fa-google-drive',
                    'form' => [
                        'action' => route('drives.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Simpan',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('drives.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        [
                            'name' => 'name',
                            'label' => 'Nama Drive',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('name'),
                            'placeholder' => 'contoh: Drive PSDM',
                            'error' => session('errors')?->first('name'),
                        ],
                        [
                            'name' => 'department_id',
                            'label' => 'Departemen',
                            'type' => 'select',
                            'value' => old('department_id'),
                            'error' => session('errors')?->first('department_id'),
                            'placeholder' => '-- Umum (Semua Departemen) --',
                            'span' => 'half',
                            'options' => $departments->map(fn ($department) => ['value' => $department->id, 'label' => $department->name])->values(),
                        ],
                        [
                            'name' => 'email',
                            'label' => 'Email Google',
                            'type' => 'email',
                            'required' => true,
                            'value' => old('email'),
                            'error' => session('errors')?->first('email'),
                            'span' => 'half',
                        ],
                        [
                            'name' => 'password',
                            'label' => 'Password',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('password'),
                            'error' => session('errors')?->first('password'),
                            'note' => 'Password ini akan ditampilkan ke user saat mereka membuka akses drive.',
                        ],
                        [
                            'name' => 'drive_url',
                            'label' => 'URL Google Drive',
                            'type' => 'url',
                            'required' => true,
                            'value' => old('drive_url'),
                            'placeholder' => 'https://drive.google.com/drive/folders/...',
                            'error' => session('errors')?->first('drive_url'),
                        ],
                    ],
                ];

                return $props;
            })(compact('departments')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'email' => 'required|email',
            'password' => 'required|string',
            'drive_url' => 'required|url',
        ]);

        $drive = DriveAccount::create($validated);

        ActivityLog::log('created', "Created drive account: {$drive->name}", $drive);

        return redirect()->route('drives.index')
            ->with('success', 'Akun Drive berhasil ditambahkan!');
    }

    public function edit(DriveAccount $drive)
    {
        $departments = Department::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit Drive: {$drive->name}",
                    'description' => 'Perbarui kredensial, penempatan departemen, dan status akun Drive organisasi.',
                    'icon' => 'fab fa-google-drive',
                    'form' => [
                        'action' => route('drives.update', $drive),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Update',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('drives.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        [
                            'name' => 'name',
                            'label' => 'Nama Drive',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('name', $drive->name),
                            'error' => session('errors')?->first('name'),
                        ],
                        [
                            'name' => 'department_id',
                            'label' => 'Departemen',
                            'type' => 'select',
                            'value' => old('department_id', $drive->department_id),
                            'error' => session('errors')?->first('department_id'),
                            'placeholder' => '-- Umum (Semua Departemen) --',
                            'span' => 'half',
                            'options' => $departments->map(fn ($department) => ['value' => $department->id, 'label' => $department->name])->values(),
                        ],
                        [
                            'name' => 'email',
                            'label' => 'Email Google',
                            'type' => 'email',
                            'required' => true,
                            'value' => old('email', $drive->email),
                            'error' => session('errors')?->first('email'),
                            'span' => 'half',
                        ],
                        [
                            'name' => 'password',
                            'label' => 'Password',
                            'type' => 'text',
                            'required' => true,
                            'value' => old('password', $drive->password),
                            'error' => session('errors')?->first('password'),
                        ],
                        [
                            'name' => 'drive_url',
                            'label' => 'URL Google Drive',
                            'type' => 'url',
                            'required' => true,
                            'value' => old('drive_url', $drive->drive_url),
                            'error' => session('errors')?->first('drive_url'),
                        ],
                        [
                            'name' => 'is_active',
                            'label' => 'Aktif',
                            'type' => 'checkbox',
                            'value' => old('is_active', $drive->is_active),
                            'error' => session('errors')?->first('is_active'),
                            'note' => 'Nonaktifkan bila akun Drive sudah tidak dipakai oleh pengurus.',
                            'span' => 'half',
                        ],
                    ],
                ];

                return $props;
            })(compact('drive', 'departments')),
        );
    }

    public function update(Request $request, DriveAccount $drive)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'email' => 'required|email',
            'password' => 'required|string',
            'drive_url' => 'required|url',
            'is_active' => 'boolean',
        ]);

        $drive->update($validated);

        ActivityLog::log('updated', "Updated drive account: {$drive->name}", $drive);

        return redirect()->route('drives.index')
            ->with('success', 'Akun Drive berhasil diupdate!');
    }

    public function destroy(DriveAccount $drive)
    {
        $name = $drive->name;

        ActivityLog::log('deleted', "Deleted drive account: {$name}", $drive);

        $drive->delete();

        return redirect()->route('drives.index')
            ->with('success', "Akun Drive {$name} berhasil dihapus!");
    }
}
