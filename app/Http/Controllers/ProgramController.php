<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\PostHogService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ProgramController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendarService) {}

    public function index()
    {
        $user = auth()->user();

        $query = Program::with(['department', 'creator', 'members', 'pics', 'tasks'])
            ->withCount('tasks');

        // Filter by department for kabinet
        if ($user->isKabinet() && $user->department_id) {
            $query->where('department_id', $user->department_id);
        }

        // Staff only sees their programs
        if ($user->isStaff()) {
            $query->forUser($user->id);
        }

        $programs = $query->orderByDesc('created_at')->get();

        return \Inertia\Inertia::render(
            'pages/CrudTablePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $canCreatePrograms = auth()->user()->can('create', \App\Models\Program::class);

                $props = [
                    'title' => 'Daftar Program Kerja',
                    'description' => 'Progres dan status tiap program kerja.',
                    'icon' => 'fas fa-diagram-project',
                    'csrfToken' => csrf_token(),
                    'enableDataTable' => true,
                    'primaryAction' => $canCreatePrograms ? [
                        'label' => 'Tambah Program',
                        'href' => route('programs.create'),
                        'icon' => 'fas fa-plus',
                    ] : null,
                    'columns' => [
                        ['label' => 'Nama'],
                        ['label' => 'Departemen'],
                        ['label' => 'Periode'],
                        ['label' => 'Task'],
                        ['label' => 'Progress'],
                        ['label' => 'Status'],
                        ['label' => 'Aksi', 'width' => '100px'],
                    ],
                    'rows' => $programs->map(function ($program) {
                        $canManageProgram = auth()->user()->can('update', $program);
                        $progress = $program->progress;
                        $programHref = route('programs.show', $program).($canManageProgram ? '#program-editor' : '');

                        return [
                            'cells' => [
                                ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                                ['type' => 'text', 'text' => $program->department?->name ?? '-', 'muted' => ! $program->department],
                                ['type' => 'text', 'text' => $program->start_date->format('d M').' - '.$program->end_date->format('d M Y'), 'muted' => true],
                                ['type' => 'badge', 'label' => "{$program->tasks_count} task", 'tone' => 'info'],
                                ['type' => 'progress', 'value' => $progress, 'label' => "{$progress}%"],
                                ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match ($program->status) {
                                    'active' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                }],
                                [
                                    'type' => 'actions',
                                    'items' => [[
                                        'href' => $programHref,
                                        'label' => $canManageProgram ? 'Kelola' : 'Detail',
                                        'icon' => $canManageProgram ? 'fas fa-pen' : 'fas fa-eye',
                                        'tone' => $canManageProgram ? 'primary' : 'secondary',
                                    ]],
                                ],
                            ],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada program kerja',
                        'text' => 'Tambahkan program pertama untuk mulai membangun ritme kerja organisasi.',
                    ],
                ];

                return $props;
            })(compact('programs')),
        );
    }

    public function create()
    {
        $user = auth()->user();
        Gate::authorize('create', Program::class);

        $departments = $this->programDepartmentsFor($user);

        return \Inertia\Inertia::render(
            'pages/EntityFormPage',
            [
                'title' => 'Form Tambah Program',
                'description' => 'Tetapkan identitas program, departemen pengampu, dan periode kerjanya sejak awal.',
                'icon' => 'fas fa-diagram-project',
                'form' => [
                    'action' => route('programs.store'),
                    'method' => 'POST',
                    'csrfToken' => csrf_token(),
                    'submitLabel' => 'Simpan',
                    'submitIcon' => 'fas fa-save',
                ],
                'cancelAction' => [
                    'href' => route('programs.index'),
                    'label' => 'Kembali',
                    'icon' => 'fas fa-arrow-left',
                ],
                'fields' => $this->programFormFields($departments),
            ],
        );
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Program::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,cancelled',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $this->authorizeDepartmentSelection((int) $validated['department_id']);

        foreach ($validated['members'] ?? [] as $memberId) {
            $this->authorizeProgramUser(User::findOrFail($memberId));
        }

        $validated['created_by'] = auth()->id();

        $program = Program::create($validated);

        // Add members
        if (! empty($request->members)) {
            $program->members()->attach($request->members, ['role' => 'member']);
        }

        ActivityLog::log('created', "Created program: {$program->name}", $program);

        app(PostHogService::class)->capture((string) auth()->id(), 'program_created', [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'status' => $program->status,
            'department_id' => $program->department_id,
        ]);

        return redirect()->route('programs.index')
            ->with('success', 'Program kerja berhasil ditambahkan!');
    }

    public function show(Program $program)
    {
        $user = auth()->user();
        Gate::authorize('view', $program);

        $program->load(['department', 'creator', 'members', 'pics', 'tasks.assignee', 'timelines']);
        $program->loadMissing(['department', 'creator', 'members', 'pics', 'tasks.assignee', 'timelines']);

        $canManage = $user->can('update', $program);
        $backRoute = $user->isStaff() ? route('programs.my') : route('programs.index');
        $departments = $canManage ? $this->programDepartmentsFor($user) : collect();
        $availableUsers = $canManage
            ? $this->programUsersFor($user)->map(fn ($availableUser) => [
                'value' => $availableUser->id,
                'label' => $availableUser->name,
            ])->values()
            : [];

        return \Inertia\Inertia::render('pages/ProgramDetailPage', [
            'csrfToken' => csrf_token(),
            'summary' => [
                'name' => $program->name,
                'description' => $program->description,
                'statusLabel' => ucfirst($program->status),
                'statusTone' => match ($program->status) {
                    'active' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    default => 'secondary',
                },
                'progress' => $program->progress,
                'facts' => [
                    ['label' => 'Departemen', 'value' => $program->department?->name ?? '-'],
                    ['label' => 'Periode', 'value' => $program->start_date->format('d M').' - '.$program->end_date->format('d M Y')],
                    ['label' => 'Dibuat oleh', 'value' => $program->creator?->name ?? '-'],
                    ['label' => 'Total Task', 'value' => $program->tasks->count()],
                ],
                'actions' => [
                    ['href' => $backRoute, 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                ],
                'dangerAction' => $canManage ? [
                    'action' => route('programs.destroy', $program),
                    'method' => 'DELETE',
                    'label' => 'Hapus Program',
                    'icon' => 'fas fa-trash',
                    'confirm' => $program->name,
                    'confirmText' => "Hapus program {$program->name}?",
                    'confirmButtonText' => 'Hapus',
                ] : null,
            ],
            'editor' => $canManage ? [
                'title' => 'Editor Program',
                'description' => '',
                'form' => [
                    'action' => route('programs.update', $program),
                    'method' => 'PUT',
                    'csrfToken' => csrf_token(),
                    'submitLabel' => 'Update Program',
                    'submitIcon' => 'fas fa-save',
                ],
                'fields' => $this->programFormFields($departments, $program),
                'dangerAction' => [
                    'action' => route('programs.destroy', $program),
                    'method' => 'DELETE',
                    'label' => 'Hapus Program',
                    'icon' => 'fas fa-trash',
                    'confirm' => $program->name,
                    'confirmText' => "Hapus program {$program->name}?",
                    'confirmButtonText' => 'Hapus',
                ],
            ] : null,
            'pics' => [
                'items' => $program->pics->map(fn ($pic) => [
                    'id' => $pic->id,
                    'name' => $pic->name,
                    'avatar' => $pic->avatar_url,
                    'removeAction' => $canManage ? route('programs.pics.remove', [$program, $pic]) : null,
                ])->values(),
                'addAction' => $canManage ? route('programs.pics.add', $program) : null,
                'availableUsers' => $availableUsers,
            ],
            'team' => [
                'members' => $program->members->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar_url,
                    'roleLabel' => ucfirst($member->pivot->role),
                    'roleTone' => $member->pivot->role === 'leader' ? 'primary' : 'secondary',
                    'removeAction' => $canManage ? route('programs.members.remove', [$program, $member]) : null,
                ])->values(),
                'addAction' => $canManage ? route('programs.members.add', $program) : null,
                'availableUsers' => $availableUsers,
            ],
            'tasks' => [
                'createAction' => $canManage ? route('tasks.create', ['type' => 'program', 'id' => $program->id]) : null,
                'columns' => [
                    ['label' => 'Task'],
                    ['label' => 'Assignee'],
                    ['label' => 'Status'],
                    ['label' => 'Progress'],
                    ['label' => 'Aksi'],
                ],
                'rows' => $program->tasks->map(function ($task) {
                    return [
                        'cells' => [
                            ['type' => 'text', 'text' => $task->title, 'className' => 'fw-semibold'],
                            $task->assignee
                                ? ['type' => 'avatar', 'image' => $task->assignee->avatar_url, 'title' => $task->assignee->name]
                                : ['type' => 'text', 'text' => '-', 'muted' => true],
                            ['type' => 'badge', 'label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                            ['type' => 'progress', 'value' => $task->progress, 'label' => "{$task->progress}%"],
                            ['type' => 'actions', 'items' => [
                                ['href' => route('tasks.show', $task), 'label' => 'Detail Task', 'icon' => 'fas fa-eye', 'tone' => 'secondary'],
                            ]],
                        ],
                    ];
                })->values(),
            ],
            'timelines' => [
                'items' => $program->timelines->map(fn ($timeline) => [
                    'title' => $timeline->title,
                    'range' => $timeline->start_date->format('d M').' - '.$timeline->end_date->format('d M Y'),
                    'color' => $timeline->color ?? '#7C3AED',
                    'description' => $timeline->description,
                ])->values(),
            ],
        ]);
    }

    private function programDepartmentsFor(User $user): Collection
    {
        $query = $user->isKabinet()
            ? Department::where('id', $user->department_id)
            : Department::active();

        return $query->get();
    }

    /**
     * @param  Collection<int, Department>  $departments
     * @return array<int, array<string, mixed>>
     */
    private function programFormFields(Collection $departments, ?Program $program = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Nama Program', 'type' => 'text', 'required' => true, 'value' => old('name', $program?->name), 'error' => session('errors')?->first('name')],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => old('description', $program?->description), 'error' => session('errors')?->first('description'), 'rows' => 3],
            [
                'name' => 'department_id',
                'label' => 'Departemen',
                'type' => 'select',
                'required' => true,
                'value' => old('department_id', $program?->department_id),
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
                'value' => old('status', $program?->status ?? 'planning'),
                'error' => session('errors')?->first('status'),
                'span' => 'half',
                'options' => [
                    ['value' => 'planning', 'label' => 'Planning'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],
            ],
            ['name' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true, 'value' => old('start_date', $program?->start_date?->format('Y-m-d')), 'error' => session('errors')?->first('start_date'), 'span' => 'half'],
            ['name' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true, 'value' => old('end_date', $program?->end_date?->format('Y-m-d')), 'error' => session('errors')?->first('end_date'), 'span' => 'half'],
        ];
    }

    public function update(Request $request, Program $program)
    {
        Gate::authorize('update', $program);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,cancelled',
        ]);

        $this->authorizeDepartmentSelection((int) $validated['department_id']);

        $previousStatus = $program->status;
        $program->update($validated);

        if ($program->wasChanged('department_id')) {
            $program->timelines()->update(['department_id' => $program->department_id]);
        }

        ActivityLog::log('updated', "Updated program: {$program->name}", $program);

        app(PostHogService::class)->capture((string) auth()->id(), 'program_updated', [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'status' => $program->status,
            'previous_status' => $previousStatus,
            'department_id' => $program->department_id,
        ]);

        return redirect()->route('programs.show', $program)
            ->with('success', 'Program kerja berhasil diupdate!');
    }

    public function destroy(Program $program)
    {
        Gate::authorize('delete', $program);

        $name = $program->name;

        if (! $this->googleCalendarService->deleteTimelineEvents($program->timelines()->get())) {
            return back()->with(
                'error',
                'Program tidak dihapus karena satu atau lebih event Google Calendar belum berhasil dihapus. Coba lagi atau periksa log aplikasi.',
            );
        }

        $googleDeleteMessage = $this->googleCalendarService->deletionQueued()
            ? ' Penghapusan event Google Calendar dijadwalkan untuk dicoba kembali.'
            : '';

        ActivityLog::log('deleted', "Deleted program: {$name}", $program);

        $program->delete();

        return redirect()->route('programs.index')
            ->with('success', "Program {$name} berhasil dihapus!{$googleDeleteMessage}");
    }

    public function addMember(Request $request, Program $program)
    {
        Gate::authorize('update', $program);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:leader,member',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $this->authorizeProgramUser($user);

        // Check if already member
        if ($program->members()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'User sudah menjadi anggota program ini.');
        }

        $program->members()->attach($request->user_id, ['role' => $request->role]);

        ActivityLog::log('updated', "Added {$user->name} to program: {$program->name}", $program);

        return back()->with('success', 'Anggota berhasil ditambahkan!');
    }

    public function removeMember(Program $program, User $user)
    {
        Gate::authorize('update', $program);

        $program->members()->detach($user->id);

        ActivityLog::log('updated', "Removed {$user->name} from program: {$program->name}", $program);

        return back()->with('success', 'Anggota berhasil dihapus dari program!');
    }

    public function addPic(Request $request, Program $program)
    {
        Gate::authorize('update', $program);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $this->authorizeProgramUser($user);

        // Check if already PIC
        if ($program->pics()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'User sudah menjadi PIC program ini.');
        }

        $program->pics()->attach($request->user_id);

        ActivityLog::log('updated', "Added {$user->name} as PIC for: {$program->name}", $program);

        return back()->with('success', 'PIC berhasil ditambahkan!');
    }

    public function removePic(Program $program, User $user)
    {
        Gate::authorize('update', $program);

        $program->pics()->detach($user->id);

        ActivityLog::log('updated', "Removed {$user->name} as PIC from: {$program->name}", $program);

        return back()->with('success', 'PIC berhasil dihapus dari program!');
    }

    private function authorizeDepartmentSelection(int $departmentId): void
    {
        $user = auth()->user();

        if ($user->isKabinet() && $user->department_id !== $departmentId) {
            abort(403, 'Anda tidak dapat mengelola program departemen lain.');
        }
    }

    private function authorizeProgramUser(User $programUser): void
    {
        $user = auth()->user();

        if ($user->isKabinet() && $programUser->department_id !== $user->department_id) {
            abort(403, 'Anda tidak dapat menambahkan user dari departemen lain.');
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function programUsersFor(User $user): Collection
    {
        return User::active()
            ->when($user->isKabinet(), fn ($query) => $query->where('department_id', $user->department_id))
            ->orderBy('name')
            ->get();
    }

    public function myPrograms()
    {
        $user = auth()->user();

        $programs = Program::forUser($user->id)
            ->with(['department', 'pics', 'members', 'tasks'])
            ->withCount('tasks')
            ->orderByDesc('created_at')
            ->get();

        return \Inertia\Inertia::render(
            'pages/CrudTablePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $currentUser = auth()->user();

                $props = [
                    'title' => 'Daftar Proker Saya',
                    'description' => 'Fokuskan perhatian pada program yang secara langsung menjadi tanggung jawab Anda.',
                    'icon' => 'fas fa-folder-open',
                    'csrfToken' => csrf_token(),
                    'columns' => [
                        ['label' => 'Nama Proker'],
                        ['label' => 'Departemen'],
                        ['label' => 'Peran'],
                        ['label' => 'Status'],
                        ['label' => 'Progress'],
                        ['label' => 'Periode'],
                    ],
                    'rows' => $programs->map(function ($program) use ($currentUser) {
                        $isPic = $program->pics->contains('id', $currentUser->id);
                        $isMember = $program->members->contains('id', $currentUser->id);

                        return [
                            'cells' => [
                                ['type' => 'text', 'text' => $program->name, 'href' => route('programs.show', $program), 'className' => 'fw-semibold'],
                                ['type' => 'text', 'text' => $program->department?->name ?? '-', 'muted' => ! $program->department],
                                ['type' => 'badges', 'items' => array_values(array_filter([
                                    $isPic ? ['label' => 'PIC', 'tone' => 'primary', 'icon' => 'fas fa-star'] : null,
                                    ! $isPic && $isMember ? ['label' => 'Member', 'tone' => 'info'] : null,
                                ]))],
                                ['type' => 'badge', 'label' => ucfirst($program->status), 'tone' => match ($program->status) {
                                    'active' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                }],
                                ['type' => 'progress', 'value' => $program->progress, 'label' => "{$program->progress}%"],
                                ['type' => 'text', 'text' => $program->start_date->format('d M').' - '.$program->end_date->format('d M Y'), 'muted' => true],
                            ],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada program yang diikuti',
                        'text' => 'Program yang melibatkan Anda akan muncul di sini.',
                    ],
                ];

                return $props;
            })(compact('programs')),
        );
    }
}
