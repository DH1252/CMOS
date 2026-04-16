<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'department'])
            ->orderBy('name')
            ->get();

        return \Inertia\Inertia::render(
            'pages/CrudTablePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Daftar User',
                    'description' => 'Akun organisasi dikelola di sini untuk kebutuhan akses, departemen, dan peran kerja.',
                    'icon' => 'fas fa-users',
                    'csrfToken' => csrf_token(),
                    'enableDataTable' => true,
                    'primaryAction' => [
                        'label' => 'Tambah User',
                        'href' => route('users.create'),
                        'icon' => 'fas fa-plus',
                    ],
                    'columns' => [
                        ['label' => 'Nama'],
                        ['label' => 'Email'],
                        ['label' => 'Role'],
                        ['label' => 'Departemen'],
                        ['label' => 'Status'],
                        ['label' => 'Aksi', 'width' => '120px'],
                    ],
                    'rows' => $users->map(function ($user) {
                        $roleTone = match ($user->role?->name) {
                            'admin' => 'danger',
                            'bph' => 'warning',
                            'kabinet' => 'info',
                            default => 'secondary',
                        };

                        return [
                            'cells' => [
                                [
                                    'type' => 'avatar',
                                    'image' => $user->avatar_url,
                                    'title' => $user->name,
                                    'href' => route('users.show', $user),
                                ],
                                ['type' => 'text', 'text' => $user->email],
                                ['type' => 'badge', 'label' => ucfirst($user->role?->name ?? '-'), 'tone' => $roleTone],
                                ['type' => 'text', 'text' => $user->department?->name ?? '-', 'muted' => ! $user->department],
                                ['type' => 'badge', 'label' => ucfirst($user->status), 'tone' => $user->status === 'active' ? 'success' : 'secondary'],
                                [
                                    'type' => 'actions',
                                    'items' => array_values(array_filter([
                                        ['href' => route('users.show', $user), 'label' => 'Detail', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                                        ['href' => route('users.edit', $user), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                                        $user->id !== auth()->id() ? [
                                            'action' => route('users.destroy', $user),
                                            'method' => 'DELETE',
                                            'label' => 'Hapus',
                                            'icon' => 'fas fa-trash',
                                            'tone' => 'danger',
                                            'confirm' => $user->name,
                                            'confirmText' => "Hapus akun {$user->name}?",
                                        ] : null,
                                    ])),
                                ],
                            ],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada user',
                        'text' => 'Tambahkan akun baru untuk mulai mengelola organisasi di dalam sistem.',
                    ],
                ];

                return $props;
            })(compact('users')),
        );
    }

    public function create()
    {
        $roles = Role::all();
        $departments = Department::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Form Tambah User',
                    'description' => 'Buat akun baru dan tetapkan peran organisasi serta departemen yang relevan.',
                    'icon' => 'fas fa-user-plus',
                    'form' => [
                        'action' => route('users.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Simpan',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('users.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        ['name' => 'name', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'value' => old('name'), 'error' => session('errors')?->first('name'), 'span' => 'half'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => old('email'), 'error' => session('errors')?->first('email'), 'span' => 'half'],
                        ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'value' => '', 'error' => session('errors')?->first('password'), 'note' => 'Minimal 8 karakter.', 'span' => 'half'],
                        ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'type' => 'password', 'required' => true, 'value' => '', 'span' => 'half'],
                        [
                            'name' => 'role_id',
                            'label' => 'Role',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('role_id'),
                            'error' => session('errors')?->first('role_id'),
                            'placeholder' => '-- Pilih Role --',
                            'span' => 'half',
                            'options' => $roles->map(fn ($role) => ['value' => $role->id, 'label' => ucfirst($role->name)])->values(),
                        ],
                        [
                            'name' => 'department_id',
                            'label' => 'Departemen',
                            'type' => 'select',
                            'value' => old('department_id'),
                            'error' => session('errors')?->first('department_id'),
                            'placeholder' => '-- Pilih Departemen --',
                            'span' => 'half',
                            'options' => $departments->map(fn ($department) => ['value' => $department->id, 'label' => $department->name])->values(),
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', 'active'),
                            'error' => session('errors')?->first('status'),
                            'options' => [
                                ['value' => 'active', 'label' => 'Active'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(compact('roles', 'departments')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::log('created', "Created user: {$user->name}", $user);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function show(User $user)
    {
        $user->load(['role', 'department', 'tasks', 'evaluations', 'programs']);

        return \Inertia\Inertia::render(
            'pages/EntityDetailPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $user->loadMissing(['tasks.program', 'evaluations.evaluator']);

                $props = [
                    'csrfToken' => csrf_token(),
                    'summary' => [
                        'image' => $user->avatar_url,
                        'title' => $user->name,
                        'subtitle' => $user->email,
                        'badges' => [
                            [
                                'label' => ucfirst($user->role?->name ?? 'No Role'),
                                'tone' => match ($user->role?->name) {
                                    'admin' => 'danger',
                                    'bph' => 'warning',
                                    'kabinet' => 'info',
                                    default => 'secondary',
                                },
                            ],
                            [
                                'label' => ucfirst($user->status),
                                'tone' => $user->status === 'active' ? 'success' : 'secondary',
                            ],
                        ],
                        'facts' => [
                            ['label' => 'Departemen', 'value' => $user->department?->name ?? '-'],
                            ['label' => 'Bergabung', 'value' => $user->created_at->format('d M Y')],
                        ],
                        'actions' => [
                            ['href' => route('users.edit', $user), 'label' => 'Edit', 'icon' => 'fas fa-pen', 'tone' => 'primary'],
                            ['href' => route('users.index'), 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                        ],
                    ],
                    'stats' => [
                        ['label' => 'Total Task', 'value' => $user->task_stats['total'], 'icon' => 'fas fa-clipboard-list', 'tone' => 'info'],
                        ['label' => 'In Progress', 'value' => $user->task_stats['in_progress'], 'icon' => 'fas fa-spinner', 'tone' => 'warning'],
                        ['label' => 'Selesai', 'value' => $user->task_stats['done'], 'icon' => 'fas fa-check', 'tone' => 'success'],
                    ],
                    'sections' => [
                        [
                            'kind' => 'table',
                            'title' => 'Task Terbaru',
                            'icon' => 'fas fa-list-check',
                            'columns' => [
                                ['label' => 'Task'],
                                ['label' => 'Program'],
                                ['label' => 'Status'],
                                ['label' => 'Progress'],
                            ],
                            'rows' => $user->tasks->take(5)->map(fn ($task) => [
                                'cells' => [
                                    ['type' => 'text', 'text' => $task->title, 'className' => 'fw-semibold'],
                                    ['type' => 'text', 'text' => $task->program?->name ?? '-', 'muted' => ! $task->program],
                                    ['type' => 'badge', 'label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                                    ['type' => 'progress', 'value' => $task->progress, 'label' => "{$task->progress}%"],
                                ],
                            ])->values(),
                            'emptyText' => 'Tidak ada task.',
                        ],
                        [
                            'kind' => 'table',
                            'title' => 'Riwayat Evaluasi',
                            'icon' => 'fas fa-star',
                            'columns' => [
                                ['label' => 'Periode'],
                                ['label' => 'Evaluator'],
                                ['label' => 'Tipe'],
                                ['label' => 'Total'],
                            ],
                            'rows' => $user->evaluations->take(5)->map(fn ($evaluation) => [
                                'cells' => [
                                    ['type' => 'text', 'text' => $evaluation->period ?? '-', 'className' => 'fw-semibold'],
                                    ['type' => 'text', 'text' => $evaluation->evaluator?->name ?? '-', 'muted' => ! $evaluation->evaluator],
                                    ['type' => 'badge', 'label' => strtoupper($evaluation->evaluator_type), 'tone' => $evaluation->evaluator_type === 'bph' ? 'warning' : 'info'],
                                    ['type' => 'badge', 'label' => number_format($evaluation->total_score, 2), 'tone' => $evaluation->total_score >= 4.5 ? 'success' : ($evaluation->total_score >= 3 ? 'warning' : 'danger')],
                                ],
                            ])->values(),
                            'emptyText' => 'Belum ada data evaluasi.',
                            'spacingClass' => 'mb-0',
                        ],
                    ],
                ];

                return $props;
            })(compact('user')),
        );
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::active()->get();

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => "Edit User: {$user->name}",
                    'description' => 'Perbarui identitas, peran, atau status akun tanpa meninggalkan konteks kerja.',
                    'icon' => 'fas fa-user-pen',
                    'form' => [
                        'action' => route('users.update', $user),
                        'method' => 'PUT',
                        'csrfToken' => csrf_token(),
                        'submitLabel' => 'Update',
                        'submitIcon' => 'fas fa-save',
                    ],
                    'cancelAction' => [
                        'href' => route('users.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'fields' => [
                        ['name' => 'name', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'value' => old('name', $user->name), 'error' => session('errors')?->first('name'), 'span' => 'half'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => old('email', $user->email), 'error' => session('errors')?->first('email'), 'span' => 'half'],
                        ['name' => 'password', 'label' => 'Password Baru', 'type' => 'password', 'value' => '', 'error' => session('errors')?->first('password'), 'note' => 'Kosongkan jika tidak diubah.', 'span' => 'half'],
                        ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password', 'type' => 'password', 'value' => '', 'span' => 'half'],
                        [
                            'name' => 'role_id',
                            'label' => 'Role',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('role_id', $user->role_id),
                            'error' => session('errors')?->first('role_id'),
                            'span' => 'half',
                            'options' => $roles->map(fn ($role) => ['value' => $role->id, 'label' => ucfirst($role->name)])->values(),
                        ],
                        [
                            'name' => 'department_id',
                            'label' => 'Departemen',
                            'type' => 'select',
                            'value' => old('department_id', $user->department_id),
                            'error' => session('errors')?->first('department_id'),
                            'placeholder' => '-- Pilih Departemen --',
                            'span' => 'half',
                            'options' => $departments->map(fn ($department) => ['value' => $department->id, 'label' => $department->name])->values(),
                        ],
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => true,
                            'value' => old('status', $user->status),
                            'error' => session('errors')?->first('status'),
                            'options' => [
                                ['value' => 'active', 'label' => 'Active'],
                                ['value' => 'inactive', 'label' => 'Inactive'],
                            ],
                        ],
                    ],
                ];

                return $props;
            })(compact('user', 'roles', 'departments')),
        );
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log('updated', "Updated user: {$user->name}", $user);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $name = $user->name;

        ActivityLog::log('deleted', "Deleted user: {$name}", $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$name} berhasil dihapus!");
    }

    /**
     * Show import form with CSV format guide
     */
    public function importForm()
    {
        $departments = Department::active()->get();
        $roles = Role::all();

        return \Inertia\Inertia::render(
            'pages/UserImportPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $results = session('import_results', ['success' => [], 'errors' => []]);

                $props = [
                    'title' => 'Import User dari CSV',
                    'description' => 'Unggah data anggota secara massal menggunakan template resmi dan tinjau hasil impor langsung dari halaman ini.',
                    'form' => [
                        'action' => route('users.import.process'),
                        'csrfToken' => csrf_token(),
                        'templateUrl' => route('users.import.template'),
                    ],
                    'roles' => $roles->pluck('name')->values(),
                    'departments' => $departments->pluck('name')->values(),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'results' => [
                        'success' => array_values($results['success'] ?? []),
                        'errors' => array_values($results['errors'] ?? []),
                    ],
                ];

                return $props;
            })(compact('departments', 'roles')),
        );
    }

    /**
     * Process CSV import
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $results = [
            'success' => [],
            'errors' => [],
        ];

        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Map CSV columns
            $data = [
                'name' => trim($row[0] ?? ''),
                'email' => trim($row[1] ?? ''),
                'password' => trim($row[2] ?? ''),
                'role' => strtolower(trim($row[3] ?? '')),
                'department' => trim($row[4] ?? ''),
            ];

            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['name'] ?: $data['email'] ?: "Row {$rowNumber}",
                    'message' => 'Kolom wajib tidak lengkap (name, email, password, role)',
                ];

                continue;
            }

            // Check email unique
            if (User::where('email', $data['email'])->exists()) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['email'],
                    'message' => 'Email sudah terdaftar',
                ];

                continue;
            }

            // Validate email format
            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['email'],
                    'message' => 'Format email tidak valid',
                ];

                continue;
            }

            // Password length
            if (strlen($data['password']) < 6) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['name'],
                    'message' => 'Password minimal 6 karakter',
                ];

                continue;
            }

            // Find role
            $role = Role::where('name', $data['role'])->first();
            if (! $role) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['name'],
                    'message' => "Role '{$data['role']}' tidak ditemukan (gunakan: admin, bph, kabinet, staff)",
                ];

                continue;
            }

            // Find department (optional)
            $departmentId = null;
            if (! empty($data['department'])) {
                $department = Department::where('name', 'LIKE', "%{$data['department']}%")->first();
                if ($department) {
                    $departmentId = $department->id;
                }
            }

            // Create user
            try {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $role->id,
                    'department_id' => $departmentId,
                    'status' => 'active',
                ]);

                $results['success'][] = [
                    'row' => $rowNumber,
                    'data' => $user->name,
                    'message' => 'User berhasil ditambahkan',
                ];

                ActivityLog::log('created', "Imported user: {$user->name}", $user);

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $data['name'],
                    'message' => 'Gagal menyimpan: '.$e->getMessage(),
                ];
            }
        }

        fclose($handle);

        return redirect()->route('users.import')
            ->with('import_results', $results)
            ->with('success', count($results['success']).' user berhasil diimport, '.count($results['errors']).' gagal');
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $content = "name,email,password,role,department\n";
        $content .= "John Doe,john@example.com,password123,staff,Divisi IT\n";
        $content .= "Jane Doe,jane@example.com,password456,kabinet,Divisi Humas\n";
        $content .= "Admin User,admin@example.com,admin123,admin,\n";

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="user_import_template.csv"');
    }
}
