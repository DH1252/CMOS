<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Program;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Landing page - Show all department cards + Global Tasks card
     * Kabinet/Staff: Redirect to their department
     * Admin/BPH: See all departments
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Kabinet and Staff: redirect to their department
        if ($user->hasRole(['kabinet', 'staff']) && $user->department_id) {
            return redirect()->route('tasks.department', $user->department_id);
        }

        // Admin and BPH: show all departments
        $departments = Department::with('cabinet')->withCount([
            'tasks as total_tasks' => fn ($q) => $q->whereNull('program_id'),
            'tasks as pending_tasks' => fn ($q) => $q->whereNull('program_id')->where('status', '!=', 'done'),
        ])->orderBy('name')->get();

        $globalTasksCount = Task::global()->count();
        $globalPendingCount = Task::global()->where('status', '!=', 'done')->count();

        return \Inertia\Inertia::render(
            'pages/TaskHubPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Task Management',
                    'description' => 'Pilih board global atau per departemen.',
                    'icon' => 'fas fa-diagram-project',
                    'cards' => array_merge([
                        [
                            'href' => route('tasks.global'),
                            'title' => 'Global Tasks',
                            'description' => 'Task lintas departemen.',
                            'icon' => 'fas fa-globe',
                            'tone' => 'primary',
                            'featured' => true,
                            'stats' => [
                                ['icon' => 'fas fa-list-check', 'label' => "{$globalTasksCount} tugas", 'tone' => 'secondary'],
                                ['icon' => 'fas fa-clock', 'label' => "{$globalPendingCount} aktif", 'tone' => 'warning'],
                            ],
                        ],
                    ], $departments->map(fn ($department) => [
                        'href' => route('tasks.department', $department),
                        'title' => $department->name,
                        'description' => $department->cabinet?->name ?? 'Belum terhubung ke kabinet',
                        'icon' => 'fas fa-building',
                        'tone' => 'info',
                        'stats' => [
                            ['icon' => 'fas fa-list-check', 'label' => ($department->total_tasks ?? 0).' tugas', 'tone' => 'secondary'],
                            ['icon' => 'fas fa-clock', 'label' => ($department->pending_tasks ?? 0).' aktif', 'tone' => 'warning'],
                        ],
                    ])->values()->all()),
                    'emptyState' => [
                        'title' => 'Belum ada departemen',
                        'text' => 'Departemen akan tampil di sini setelah data organisasi tersedia.',
                    ],
                ];

                return $props;
            })(compact('departments', 'globalTasksCount', 'globalPendingCount')),
        );
    }

    /**
     * Global tasks kanban board
     */
    public function global(Request $request)
    {
        $user = auth()->user();
        $query = Task::global()->with(['assignee', 'creator']);

        if ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        $payload = $this->boardPayload(
            title: 'Global Tasks',
            description: 'Task lintas departemen untuk sinkronisasi kerja organisasi.',
            type: 'global',
            typeId: null,
            users: $users,
            tasks: $tasks,
            breadcrumbs: [
                ['label' => 'Tasks', 'href' => route('tasks.index')],
                ['label' => 'Global Tasks'],
            ],
            refreshUrl: route('tasks.global'),
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return \Inertia\Inertia::render(
            'pages/TaskBoardPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $description = $type === 'global'
        ? 'Task lintas departemen.'
        : ($type === 'department'
            ? "Board {$department->name}."
            : "Task {$program->name}.");

                $breadcrumbs = [['label' => 'Tasks', 'href' => route('tasks.index')]];

                if ($type === 'department') {
                    $breadcrumbs[] = ['label' => $department->name, 'href' => route('tasks.department', $department)];
                    $breadcrumbs[] = ['label' => 'Tugas Departemen'];
                } elseif ($type === 'program') {
                    $breadcrumbs[] = ['label' => $program->department->name, 'href' => route('tasks.department', $program->department)];
                    $breadcrumbs[] = ['label' => $program->name];
                } else {
                    $breadcrumbs[] = ['label' => 'Global Tasks'];
                }

                $statusLabels = [
                    'todo' => 'To Do',
                    'in_progress' => 'In Progress',
                    'pending' => 'Pending',
                    'done' => 'Done',
                ];

                $props = [
                    'title' => $title,
                    'description' => $description,
                    'refreshUrl' => $type === 'global'
                        ? route('tasks.global')
                        : ($type === 'department'
                            ? route('tasks.department.tasks', $department)
                            : route('tasks.program', $program)),
                    'realtimeSnapshot' => route('realtime.snapshot'),
                    'breadcrumbs' => $breadcrumbs,
                    'context' => [
                        'type' => $type,
                        'typeId' => $typeId,
                    ],
                    'endpoints' => [
                        'storeInline' => route('tasks.inline.store'),
                        'taskBase' => url('/tasks'),
                    ],
                    'csrfToken' => csrf_token(),
                    'users' => $users->map(fn ($user) => [
                        'value' => $user->id,
                        'label' => $user->name.' ('.ucfirst($user->role?->name ?? 'user').')',
                    ])->values(),
                    'columns' => collect(\App\Models\Task::STATUSES)->map(function ($status) use ($tasks, $statusLabels) {
                        return [
                            'status' => $status,
                            'label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                            'tasks' => collect($tasks->get($status, collect()))->map(fn ($task) => [
                                'id' => $task->id,
                                'title' => $task->title,
                                'description' => $task->description,
                                'status' => $task->status,
                                'priority' => $task->priority,
                                'priority_label' => $task->priority_label,
                                'progress' => $task->progress,
                                'deadline' => $task->deadline?->format('Y-m-d'),
                                'deadline_fmt' => $task->deadline?->format('d M Y'),
                                'is_overdue' => $task->is_overdue,
                                'assigned_to' => $task->assigned_to,
                                'assignee_name' => $task->assignee?->name,
                                'assignee_avatar' => $task->assignee?->avatar_url,
                                'showHref' => route('tasks.show', $task),
                            ])->values(),
                        ];
                    })->values(),
                ];

                return $props;
            })([
                'tasks' => $tasks,
                'users' => $users,
                'title' => 'Global Tasks',
                'backUrl' => route('tasks.index'),
                'createUrl' => route('tasks.create', ['type' => 'global']),
                'type' => 'global',
                'typeId' => null,
            ]),
        );
    }

    /**
     * Department view - Show program cards + Department Tasks card
     * Kabinet/Staff can only access their own department
     */
    public function department(Department $department)
    {
        $this->authorizeDepartmentAccess($department);

        $programs = $department->programs()
            ->withCount([
                'tasks as total_tasks',
                'tasks as pending_tasks' => fn ($q) => $q->where('status', '!=', 'done'),
                'tasks as done_tasks' => fn ($q) => $q->where('status', 'done'),
            ])
            ->orderBy('name')
            ->get();

        $departmentTasks = Task::forDepartment($department->id);

        $deptTasksCount = (clone $departmentTasks)->count();
        $deptPendingCount = (clone $departmentTasks)->where('status', '!=', 'done')->count();
        $deptDoneCount = (clone $departmentTasks)->done()->count();

        return \Inertia\Inertia::render(
            'pages/TaskHubPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $departmentProgress = $deptTasksCount > 0 ? round(($deptDoneCount / $deptTasksCount) * 100) : 0;

                $props = [
                    'title' => $department->name,
                    'description' => 'Pilih board departemen atau program kerja.',
                    'icon' => 'fas fa-building',
                    'breadcrumbs' => [
                        ['label' => 'Tasks', 'href' => route('tasks.index')],
                        ['label' => $department->name],
                    ],
                    'cards' => array_merge([
                        [
                            'href' => route('tasks.department.tasks', $department),
                            'title' => 'Tugas Departemen',
                            'description' => "Task untuk {$department->name}.",
                            'icon' => 'fas fa-folder-tree',
                            'tone' => 'primary',
                            'featured' => true,
                            'progress' => $departmentProgress,
                            'stats' => [
                                ['icon' => 'fas fa-check', 'label' => "{$deptDoneCount} selesai", 'tone' => 'success'],
                                ['icon' => 'fas fa-clock', 'label' => "{$deptPendingCount} aktif", 'tone' => 'warning'],
                            ],
                        ],
                    ], $programs->map(fn ($program) => [
                        'href' => route('tasks.program', $program),
                        'title' => $program->name,
                        'description' => \Illuminate\Support\Str::limit($program->description ?: 'Belum ada deskripsi program.', 88),
                        'icon' => 'fas fa-sitemap',
                        'tone' => 'success',
                        'progress' => $program->total_tasks > 0 ? round((($program->done_tasks ?? 0) / $program->total_tasks) * 100) : 0,
                        'stats' => [
                            ['icon' => 'fas fa-check', 'label' => ($program->done_tasks ?? 0).' selesai', 'tone' => 'success'],
                            ['icon' => 'fas fa-clock', 'label' => ($program->pending_tasks ?? 0).' aktif', 'tone' => 'warning'],
                        ],
                    ])->values()->all()),
                    'emptyState' => [
                        'title' => 'Belum ada program',
                        'text' => 'Departemen ini belum memiliki program kerja untuk diturunkan menjadi task board.',
                    ],
                ];

                return $props;
            })(compact('department', 'programs', 'deptTasksCount', 'deptPendingCount', 'deptDoneCount')),
        );
    }

    /**
     * Department tasks kanban board
     * Kabinet/Staff can only access their own department
     */
    public function departmentTasks(Request $request, Department $department)
    {
        $this->authorizeDepartmentAccess($department);

        $tasks = Task::forDepartment($department->id)
            ->with(['assignee', 'creator'])
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        $payload = $this->boardPayload(
            title: "Tugas {$department->name}",
            description: "Papan kerja khusus untuk {$department->name}.",
            type: 'department',
            typeId: $department->id,
            users: $users,
            tasks: $tasks,
            breadcrumbs: [
                ['label' => 'Tasks', 'href' => route('tasks.index')],
                ['label' => $department->name, 'href' => route('tasks.department', $department)],
                ['label' => 'Tugas Departemen'],
            ],
            refreshUrl: route('tasks.department.tasks', $department),
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return \Inertia\Inertia::render(
            'pages/TaskBoardPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $description = $type === 'global'
        ? 'Task lintas departemen.'
        : ($type === 'department'
            ? "Board {$department->name}."
            : "Task {$program->name}.");

                $breadcrumbs = [['label' => 'Tasks', 'href' => route('tasks.index')]];

                if ($type === 'department') {
                    $breadcrumbs[] = ['label' => $department->name, 'href' => route('tasks.department', $department)];
                    $breadcrumbs[] = ['label' => 'Tugas Departemen'];
                } elseif ($type === 'program') {
                    $breadcrumbs[] = ['label' => $program->department->name, 'href' => route('tasks.department', $program->department)];
                    $breadcrumbs[] = ['label' => $program->name];
                } else {
                    $breadcrumbs[] = ['label' => 'Global Tasks'];
                }

                $statusLabels = [
                    'todo' => 'To Do',
                    'in_progress' => 'In Progress',
                    'pending' => 'Pending',
                    'done' => 'Done',
                ];

                $props = [
                    'title' => $title,
                    'description' => $description,
                    'refreshUrl' => $type === 'global'
                        ? route('tasks.global')
                        : ($type === 'department'
                            ? route('tasks.department.tasks', $department)
                            : route('tasks.program', $program)),
                    'realtimeSnapshot' => route('realtime.snapshot'),
                    'breadcrumbs' => $breadcrumbs,
                    'context' => [
                        'type' => $type,
                        'typeId' => $typeId,
                    ],
                    'endpoints' => [
                        'storeInline' => route('tasks.inline.store'),
                        'taskBase' => url('/tasks'),
                    ],
                    'csrfToken' => csrf_token(),
                    'users' => $users->map(fn ($user) => [
                        'value' => $user->id,
                        'label' => $user->name.' ('.ucfirst($user->role?->name ?? 'user').')',
                    ])->values(),
                    'columns' => collect(\App\Models\Task::STATUSES)->map(function ($status) use ($tasks, $statusLabels) {
                        return [
                            'status' => $status,
                            'label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                            'tasks' => collect($tasks->get($status, collect()))->map(fn ($task) => [
                                'id' => $task->id,
                                'title' => $task->title,
                                'description' => $task->description,
                                'status' => $task->status,
                                'priority' => $task->priority,
                                'priority_label' => $task->priority_label,
                                'progress' => $task->progress,
                                'deadline' => $task->deadline?->format('Y-m-d'),
                                'deadline_fmt' => $task->deadline?->format('d M Y'),
                                'is_overdue' => $task->is_overdue,
                                'assigned_to' => $task->assigned_to,
                                'assignee_name' => $task->assignee?->name,
                                'assignee_avatar' => $task->assignee?->avatar_url,
                                'showHref' => route('tasks.show', $task),
                            ])->values(),
                        ];
                    })->values(),
                ];

                return $props;
            })([
                'tasks' => $tasks,
                'users' => $users,
                'title' => "Tugas {$department->name}",
                'backUrl' => route('tasks.department', $department),
                'createUrl' => route('tasks.create', ['type' => 'department', 'id' => $department->id]),
                'type' => 'department',
                'typeId' => $department->id,
                'department' => $department,
            ]),
        );
    }

    /**
     * Program tasks kanban board
     */
    public function program(Request $request, Program $program)
    {
        $this->authorizeProgramAccess($program);

        $tasks = Task::forProgram($program->id)
            ->with(['assignee', 'creator'])
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        $payload = $this->boardPayload(
            title: $program->name,
            description: "Pantau seluruh task untuk program {$program->name}.",
            type: 'program',
            typeId: $program->id,
            users: $users,
            tasks: $tasks,
            breadcrumbs: [
                ['label' => 'Tasks', 'href' => route('tasks.index')],
                ['label' => $program->department->name, 'href' => route('tasks.department', $program->department)],
                ['label' => $program->name],
            ],
            refreshUrl: route('tasks.program', $program),
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return \Inertia\Inertia::render(
            'pages/TaskBoardPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $description = $type === 'global'
        ? 'Task lintas departemen.'
        : ($type === 'department'
            ? "Board {$department->name}."
            : "Task {$program->name}.");

                $breadcrumbs = [['label' => 'Tasks', 'href' => route('tasks.index')]];

                if ($type === 'department') {
                    $breadcrumbs[] = ['label' => $department->name, 'href' => route('tasks.department', $department)];
                    $breadcrumbs[] = ['label' => 'Tugas Departemen'];
                } elseif ($type === 'program') {
                    $breadcrumbs[] = ['label' => $program->department->name, 'href' => route('tasks.department', $program->department)];
                    $breadcrumbs[] = ['label' => $program->name];
                } else {
                    $breadcrumbs[] = ['label' => 'Global Tasks'];
                }

                $statusLabels = [
                    'todo' => 'To Do',
                    'in_progress' => 'In Progress',
                    'pending' => 'Pending',
                    'done' => 'Done',
                ];

                $props = [
                    'title' => $title,
                    'description' => $description,
                    'refreshUrl' => $type === 'global'
                        ? route('tasks.global')
                        : ($type === 'department'
                            ? route('tasks.department.tasks', $department)
                            : route('tasks.program', $program)),
                    'realtimeSnapshot' => route('realtime.snapshot'),
                    'breadcrumbs' => $breadcrumbs,
                    'context' => [
                        'type' => $type,
                        'typeId' => $typeId,
                    ],
                    'endpoints' => [
                        'storeInline' => route('tasks.inline.store'),
                        'taskBase' => url('/tasks'),
                    ],
                    'csrfToken' => csrf_token(),
                    'users' => $users->map(fn ($user) => [
                        'value' => $user->id,
                        'label' => $user->name.' ('.ucfirst($user->role?->name ?? 'user').')',
                    ])->values(),
                    'columns' => collect(\App\Models\Task::STATUSES)->map(function ($status) use ($tasks, $statusLabels) {
                        return [
                            'status' => $status,
                            'label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                            'tasks' => collect($tasks->get($status, collect()))->map(fn ($task) => [
                                'id' => $task->id,
                                'title' => $task->title,
                                'description' => $task->description,
                                'status' => $task->status,
                                'priority' => $task->priority,
                                'priority_label' => $task->priority_label,
                                'progress' => $task->progress,
                                'deadline' => $task->deadline?->format('Y-m-d'),
                                'deadline_fmt' => $task->deadline?->format('d M Y'),
                                'is_overdue' => $task->is_overdue,
                                'assigned_to' => $task->assigned_to,
                                'assignee_name' => $task->assignee?->name,
                                'assignee_avatar' => $task->assignee?->avatar_url,
                                'showHref' => route('tasks.show', $task),
                            ])->values(),
                        ];
                    })->values(),
                ];

                return $props;
            })([
                'tasks' => $tasks,
                'users' => $users,
                'title' => $program->name,
                'backUrl' => route('tasks.department', $program->department),
                'createUrl' => route('tasks.create', ['type' => 'program', 'id' => $program->id]),
                'type' => 'program',
                'typeId' => $program->id,
                'program' => $program,
            ]),
        );
    }

    /**
     * Create task form
     */
    public function create(Request $request)
    {
        $type = $request->get('type');
        $typeId = $request->get('id');

        if (! $type && $request->filled('program_id')) {
            $type = 'program';
            $typeId = $request->get('program_id');
        } elseif (! $type && $request->filled('department_id')) {
            $type = 'department';
            $typeId = $request->get('department_id');
        }

        $type = $type ?: 'program';

        $users = User::active()->with('role')->orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return \Inertia\Inertia::render(
            'pages/TaskFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $typeLocked = filled($typeId);
                $contextName = null;

                if ($type === 'program' && $typeId) {
                    $contextName = $programs->firstWhere('id', (int) $typeId)?->name;
                } elseif ($type === 'department' && $typeId) {
                    $contextName = $departments->firstWhere('id', (int) $typeId)?->name;
                }

                $description = match ($type) {
                    'global' => 'Buat tugas lintas departemen untuk koordinasi organisasi.',
                    'department' => $contextName ? "Susun penugasan baru untuk {$contextName}." : 'Buat tugas pada level departemen.',
                    default => $contextName ? "Susun penugasan baru untuk program {$contextName}." : 'Buat tugas baru untuk program kerja.',
                };

                $cancelHref = match ($type) {
                    'global' => route('tasks.global'),
                    'department' => $typeId ? route('tasks.department.tasks', $typeId) : route('tasks.index'),
                    default => $typeId ? route('tasks.program', $typeId) : route('tasks.index'),
                };

                $props = [
                    'title' => 'Form Tambah Task',
                    'description' => $description,
                    'form' => [
                        'action' => route('tasks.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                    ],
                    'taskType' => $type,
                    'typeLocked' => $typeLocked,
                    'typeId' => $typeId,
                    'values' => [
                        'title' => old('title'),
                        'description' => old('description'),
                        'program_id' => old('program_id', $type === 'program' ? $typeId : null),
                        'department_id' => old('department_id', $type === 'department' ? $typeId : null),
                        'assigned_to' => old('assigned_to'),
                        'priority' => old('priority', 'medium'),
                        'deadline' => old('deadline'),
                    ],
                    'users' => $users->map(fn ($user) => [
                        'value' => $user->id,
                        'label' => $user->name.' ('.ucfirst($user->role?->name ?? 'user').')',
                    ])->values(),
                    'programs' => $programs->map(fn ($program) => [
                        'value' => $program->id,
                        'label' => $program->name.' ('.($program->department?->name ?? 'Tanpa departemen').')',
                    ])->values(),
                    'departments' => $departments->map(fn ($department) => [
                        'value' => $department->id,
                        'label' => $department->name,
                    ])->values(),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'cancelAction' => [
                        'href' => $cancelHref,
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                ];

                return $props;
            })(compact('type', 'typeId', 'users', 'programs', 'departments')),
        );
    }

    /**
     * Store new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'program_id' => 'required_if:type,program|nullable|exists:programs,id',
            'department_id' => 'required_if:type,department|nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['type'] === 'program' ? $validated['program_id'] : null,
            'department_id' => $validated['type'] === 'department' ? $validated['department_id'] : null,
            'is_global' => $validated['type'] === 'global',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'status' => 'todo',
            'sort_order' => $this->nextSortOrder($validated['type'], $validated['type'] === 'program' ? $validated['program_id'] : ($validated['type'] === 'department' ? $validated['department_id'] : null), 'todo'),
            'priority' => $validated['priority'],
            'progress' => 0,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        ActivityLog::log('created', "Created task: {$task->title}", $task);

        // Redirect back to appropriate kanban
        $redirectUrl = match ($validated['type']) {
            'global' => route('tasks.global'),
            'department' => route('tasks.department.tasks', $validated['department_id']),
            'program' => route('tasks.program', $validated['program_id']),
        };

        return redirect($redirectUrl)->with('success', 'Task berhasil ditambahkan!');
    }

    /**
     * Show task detail
     */
    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['program.department', 'department', 'assignee.role', 'creator']);

        return \Inertia\Inertia::render(
            'pages/TaskDetailPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $backHref = $task->is_global
        ? route('tasks.global')
        : ($task->program_id
            ? route('tasks.program', $task->program)
            : ($task->department_id ? route('tasks.department.tasks', $task->department) : route('tasks.index')));

                $taskTypeFact = $task->is_global
                    ? ['label' => 'Tipe Task', 'value' => 'Global', 'className' => 'fw-semibold']
                    : ($task->program_id
                        ? ['label' => 'Tipe Task', 'value' => $task->program->name, 'href' => route('tasks.program', $task->program), 'className' => 'fw-semibold']
                        : ($task->department_id
                            ? ['label' => 'Tipe Task', 'value' => $task->department->name, 'href' => route('tasks.department.tasks', $task->department), 'className' => 'fw-semibold']
                            : ['label' => 'Tipe Task', 'value' => '-', 'className' => 'text-muted']));

                $props = [
                    'summary' => [
                        'title' => $task->title,
                        'description' => $task->description,
                        'badges' => [
                            ['label' => ucfirst(str_replace('_', ' ', $task->status)), 'tone' => $task->status_badge],
                            ['label' => ucfirst($task->priority), 'tone' => $task->priority_badge],
                        ],
                        'actions' => [
                            ['href' => $backHref, 'label' => 'Kembali', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                        ],
                    ],
                    'facts' => [
                        $taskTypeFact,
                        ['label' => 'Departemen', 'value' => $task->program?->department?->name ?? $task->department?->name ?? 'Global', 'className' => 'fw-semibold'],
                        ['label' => 'Deadline', 'value' => $task->deadline?->format('d M Y') ?? '-', 'className' => $task->is_overdue ? 'fw-semibold text-danger' : 'fw-semibold'],
                        ['label' => 'Dibuat oleh', 'value' => $task->creator?->name ?? '-', 'className' => 'fw-semibold'],
                    ],
                    'progress' => [
                        'value' => $task->progress,
                        'action' => route('tasks.progress', $task),
                        'csrfToken' => csrf_token(),
                        'canUpdate' => auth()->id() === $task->assigned_to || auth()->user()->hasRole(['admin', 'bph', 'kabinet']),
                    ],
                    'assignee' => $task->assignee ? [
                        'name' => $task->assignee->name,
                        'avatar' => $task->assignee->avatar_url,
                        'email' => $task->assignee->email,
                        'roleLabel' => ucfirst($task->assignee->role?->name ?? '-'),
                        'roleTone' => $task->assignee->role?->name === 'kabinet' ? 'info' : 'secondary',
                    ] : null,
                    'meta' => [
                        ['label' => 'Dibuat', 'value' => $task->created_at->format('d M Y H:i')],
                        ['label' => 'Diupdate', 'value' => $task->updated_at->format('d M Y H:i')],
                    ],
                ];

                return $props;
            })(compact('task')),
        );
    }

    /**
     * Update task (full update)
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,pending,done',
            'priority' => 'required|in:low,medium,high',
            'progress' => 'required|integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        // Auto-update status based on progress
        if ($validated['progress'] == 100) {
            $validated['status'] = 'done';
        }

        $task->update($validated);

        ActivityLog::log('updated', "Updated task: {$task->title}", $task);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task berhasil diupdate!');
    }

    /**
     * Update task status via drag-drop (AJAX)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,pending,done',
            'column_orders' => 'sometimes|array',
            'column_orders.*' => 'array',
        ]);

        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $validated): void {
            $task->status = $validated['status'];

            if ($validated['status'] === 'done') {
                $task->progress = 100;
            } elseif ($validated['status'] === 'todo' && $task->progress > 0) {
                $task->progress = 0;
            }

            $task->save();

            if (! empty($validated['column_orders'])) {
                $this->applyColumnOrders($task, $validated['column_orders']);
            }
        });

        ActivityLog::log('updated', "Changed task status: {$task->title} from {$oldStatus} to {$task->status}", $task);

        $task->load(['assignee', 'creator']);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->progress = $validated['progress'];

        // Auto-update status
        if ($task->progress == 100) {
            $task->status = 'done';
        } elseif ($task->progress > 0 && $task->status === 'todo') {
            $task->status = 'in_progress';
        }

        $task->save();

        ActivityLog::log('updated', "Updated progress for task: {$task->title} to {$task->progress}%", $task);

        return back()->with('success', 'Progress berhasil diupdate!');
    }

    /**
     * Delete task
     */
    public function destroy(Task $task)
    {
        $title = $task->title;

        ActivityLog::log('deleted', "Deleted task: {$title}", $task);

        $task->delete();

        return back()->with('success', "Task {$title} berhasil dihapus!");
    }

    /**
     * Inline store — JSON response for kanban board
     */
    public function storeInline(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'program_id' => 'required_if:type,program|nullable|exists:programs,id',
            'department_id' => 'required_if:type,department|nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:todo,in_progress,pending,done',
        ]);

        $this->authorizeTaskStore(
            $validated['type'],
            $validated['type'] === 'program' ? $validated['program_id'] : null,
            $validated['type'] === 'department' ? $validated['department_id'] : null
        );

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['type'] === 'program' ? $validated['program_id'] : null,
            'department_id' => $validated['type'] === 'department' ? $validated['department_id'] : null,
            'is_global' => $validated['type'] === 'global',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'status' => $validated['status'] ?? 'todo',
            'sort_order' => $this->nextSortOrder($validated['type'], $validated['type'] === 'program' ? $validated['program_id'] : ($validated['type'] === 'department' ? $validated['department_id'] : null), $validated['status'] ?? 'todo'),
            'priority' => $validated['priority'],
            'progress' => 0,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        ActivityLog::log('created', "Created task: {$task->title}", $task);

        $task->load(['assignee', 'creator']);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Inline update — JSON response for kanban board
     */
    public function updateInline(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:todo,in_progress,pending,done',
            'priority' => 'sometimes|required|in:low,medium,high',
            'progress' => 'sometimes|required|integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        // Auto-update status based on progress
        if (isset($validated['progress'])) {
            if ($validated['progress'] == 100) {
                $validated['status'] = 'done';
            } elseif ($validated['progress'] > 0 && $task->status === 'todo') {
                $validated['status'] = 'in_progress';
            }
        }

        $task->update($validated);
        $task->load(['assignee', 'creator']);

        ActivityLog::log('updated', "Updated task: {$task->title}", $task);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Inline destroy — JSON response for kanban board
     */
    public function destroyInline(Task $task)
    {
        $this->authorizeTaskAccess($task);

        if (! auth()->user()->hasRole(['admin', 'bph', 'kabinet'])) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus task ini.');
        }

        $title = $task->title;
        ActivityLog::log('deleted', "Deleted task: {$title}", $task);
        $task->delete();

        return response()->json(['success' => true, 'message' => "Task \"{$title}\" dihapus."]);
    }

    /**
     * Format task for JSON response
     */
    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'sort_order' => $task->sort_order,
            'priority' => $task->priority,
            'priority_label' => $task->priority_label,
            'progress' => $task->progress,
            'deadline' => $task->deadline ? $task->deadline->format('Y-m-d') : null,
            'deadline_fmt' => $task->deadline ? $task->deadline->format('d M Y') : null,
            'is_overdue' => $task->is_overdue,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'assignee_avatar' => $task->assignee?->avatar_url,
            'created_by' => $task->created_by,
            'creator_name' => $task->creator?->name,
            'showHref' => route('tasks.show', $task),
        ];
    }

    private function nextSortOrder(string $type, ?int $typeId, string $status): int
    {
        $query = Task::query()->where('status', $status);

        if ($type === 'global') {
            $query->where('is_global', true);
        } elseif ($type === 'department') {
            $query->where('department_id', $typeId)->whereNull('program_id');
        } else {
            $query->where('program_id', $typeId);
        }

        return ((int) $query->max('sort_order')) + 1;
    }

    private function applyColumnOrders(Task $task, array $columnOrders): void
    {
        $contextQuery = $this->contextTaskQuery($task);

        foreach ($columnOrders as $status => $orderedIds) {
            if (! in_array($status, Task::STATUSES, true) || ! is_array($orderedIds)) {
                continue;
            }

            $allowedIds = (clone $contextQuery)
                ->where('status', $status)
                ->pluck('id')
                ->map(fn (mixed $id) => (int) $id)
                ->all();

            $filteredIds = array_values(array_filter(
                array_map(static fn (mixed $id): int => (int) $id, $orderedIds),
                static fn (int $id): bool => in_array($id, $allowedIds, true),
            ));

            $count = count($filteredIds);

            foreach ($filteredIds as $index => $id) {
                Task::query()
                    ->whereKey($id)
                    ->update(['sort_order' => $count - $index]);
            }
        }
    }

    private function contextTaskQuery(Task $task)
    {
        $query = Task::query();

        if ($task->is_global) {
            return $query->where('is_global', true);
        }

        if ($task->program_id) {
            return $query->where('program_id', $task->program_id);
        }

        return $query->where('department_id', $task->department_id)->whereNull('program_id');
    }

    private function authorizeDepartmentAccess(Department $department): void
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'bph'])) {
            return;
        }

        if ($user->isKabinet() && $user->department_id !== $department->id) {
            abort(403, 'Anda tidak memiliki akses ke departemen ini.');
        }

        if ($user->isStaff() && $user->department_id !== $department->id) {
            abort(403, 'Anda tidak memiliki akses ke departemen ini.');
        }
    }

    private function authorizeProgramAccess(Program $program): void
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'bph'])) {
            return;
        }

        if ($user->isKabinet() && $program->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses ke program ini.');
        }

        if ($user->isStaff() && ! $program->hasMemberOrPic($user->id)) {
            abort(403, 'Anda tidak memiliki akses ke program ini.');
        }
    }

    private function authorizeTaskAccess(Task $task): void
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'bph'])) {
            return;
        }

        if ($user->isKabinet() && $user->department_id) {
            if ($task->program_id) {
                $task->loadMissing('program');
                if ($task->program?->department_id === $user->department_id) {
                    return;
                }
            }
            if ($task->department_id === $user->department_id) {
                return;
            }
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }

        if ($user->isStaff()) {
            if ($task->assigned_to === $user->id) {
                return;
            }
            if ($task->program_id) {
                $task->loadMissing('program');
                if ($task->program?->hasMemberOrPic($user->id)) {
                    return;
                }
            }
            if ($task->department_id === $user->department_id) {
                return;
            }
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }
    }

    private function authorizeTaskStore(string $type, ?int $programId, ?int $departmentId): void
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'bph'])) {
            return;
        }

        if ($user->isKabinet() && $user->department_id) {
            if ($type === 'department' && $departmentId === $user->department_id) {
                return;
            }
            if ($type === 'program') {
                $program = Program::find($programId);
                if ($program && $program->department_id === $user->department_id) {
                    return;
                }
            }
            abort(403, 'Anda tidak memiliki izin untuk membuat task di konteks ini.');
        }

        if ($user->isStaff()) {
            if ($type === 'department' && $departmentId === $user->department_id) {
                return;
            }
            if ($type === 'program') {
                $program = Program::find($programId);
                if ($program && $program->hasMemberOrPic($user->id)) {
                    return;
                }
            }
            abort(403, 'Anda tidak memiliki izin untuk membuat task di konteks ini.');
        }
    }
}
