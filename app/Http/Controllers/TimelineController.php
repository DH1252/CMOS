<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Program;
use App\Models\Task;
use App\Models\Timeline;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendarService) {}

    public function index()
    {
        $user = auth()->user();

        $query = Timeline::with(['department', 'program']);

        if ($user->isStaff() || $user->isKabinet()) {
            $query->where(function ($q) use ($user) {
                $q->where('type', 'global')
                    ->orWhere('department_id', $user->department_id);
            });
        }

        $timelines = $query->orderBy('start_date')->orderBy('end_date')->get();

        return \Inertia\Inertia::render(
            'pages/TimelineCollectionPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $today = now()->toDateString();
                $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

                $statusFor = function ($timeline) use ($today) {
                    if ($timeline->end_date->toDateString() < $today) {
                        return ['label' => 'Selesai', 'tone' => 'secondary'];
                    }

                    if ($timeline->start_date->toDateString() > $today) {
                        return ['label' => 'Akan Datang', 'tone' => 'info'];
                    }

                    return ['label' => 'Berlangsung', 'tone' => 'success'];
                };

                $props = [
                    'title' => 'Semua Timeline',
                    'description' => 'Pantau agenda global, departemen, dan program kerja dalam satu daftar yang rapi.',
                    'icon' => 'fas fa-calendar-alt',
                    'actions' => array_values(array_filter([
                        ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
                        $canManage ? ['href' => route('timelines.create'), 'label' => 'Tambah Timeline', 'icon' => 'fas fa-plus', 'tone' => 'primary'] : null,
                    ])),
                    'summary' => [
                        ['label' => 'Total', 'value' => $timelines->count()],
                        ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
                        ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
                    ],
                    'items' => $timelines->map(function ($timeline) use ($statusFor, $canManage) {
                        $scope = match ($timeline->type) {
                            'global' => ['label' => 'Global', 'tone' => 'primary'],
                            'department' => ['label' => 'Departemen', 'tone' => 'info'],
                            default => ['label' => 'Program', 'tone' => 'secondary'],
                        };

                        $meta = [];

                        if ($timeline->department) {
                            $meta[] = ['icon' => 'fas fa-building', 'label' => $timeline->department->name];
                        }

                        if ($timeline->program) {
                            $meta[] = ['icon' => 'fas fa-diagram-project', 'label' => $timeline->program->name];
                        }

                        return [
                            'title' => $timeline->title,
                            'description' => $timeline->description ?: 'Tidak ada deskripsi tambahan untuk timeline ini.',
                            'color' => $timeline->color ?? '#7C3AED',
                            'range' => $timeline->start_date->format('d M Y').' - '.$timeline->end_date->format('d M Y'),
                            'scope' => $scope,
                            'status' => $statusFor($timeline),
                            'meta' => $meta,
                            'actions' => $canManage ? [
                                [
                                    'href' => route('timelines.edit', $timeline),
                                    'label' => 'Edit timeline',
                                    'icon' => 'fas fa-pen',
                                    'tone' => 'secondary',
                                    'iconOnly' => true,
                                ],
                                [
                                    'href' => route('timelines.destroy', $timeline),
                                    'label' => 'Hapus timeline',
                                    'icon' => 'fas fa-trash',
                                    'tone' => 'danger',
                                    'method' => 'POST',
                                    'spoofMethod' => 'DELETE',
                                    'csrfToken' => csrf_token(),
                                    'confirm' => $timeline->title,
                                    'iconOnly' => true,
                                ],
                            ] : [],
                        ];
                    })->values(),
                    'emptyState' => [
                        'title' => 'Belum ada timeline',
                        'text' => 'Timeline organisasi akan tampil di sini setelah dibuat.',
                        'action' => $canManage ? [
                            'href' => route('timelines.create'),
                            'label' => 'Tambah Timeline',
                            'icon' => 'fas fa-plus',
                        ] : null,
                    ],
                ];

                return $props;
            })(compact('timelines')),
        );
    }

    public function calendar()
    {
        return \Inertia\Inertia::render(
            'pages/TimelineCalendarPage',
            (static function (): array {
                $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

                $props = [
                    'title' => 'Kalender Timeline',
                    'description' => 'Pantau timeline, program kerja, dan deadline task dalam satu kalender interaktif.',
                    'listAction' => [
                        'href' => route('timelines.index'),
                        'label' => 'List View',
                        'icon' => 'fas fa-list',
                    ],
                    'createAction' => $canManage ? [
                        'href' => route('timelines.create'),
                        'label' => 'Tambah Timeline',
                        'icon' => 'fas fa-plus',
                    ] : null,
                    'eventsUrl' => route('timelines.calendar.data'),
                    'locale' => 'id',
                    'legend' => [
                        ['label' => 'Timeline Global', 'color' => '#7751DE'],
                        ['label' => 'Timeline Departemen', 'color' => '#D4A017'],
                        ['label' => 'Program Kerja', 'color' => '#3F7A50'],
                        ['label' => 'Deadline Task', 'color' => '#A96B12'],
                    ],
                ];

                return $props;
            })(),
        );
    }

    public function calendarData()
    {
        $user = auth()->user();
        $events = [];

        // Get timelines
        $timelinesQuery = Timeline::with(['department', 'program']);

        if ($user->isStaff() || $user->isKabinet()) {
            $timelinesQuery->where(function ($q) use ($user) {
                $q->where('type', 'global')
                    ->orWhere('department_id', $user->department_id);
            });
        }

        $timelines = $timelinesQuery->get();

        foreach ($timelines as $timeline) {
            $events[] = [
                'id' => 'timeline_'.$timeline->id,
                'title' => $timeline->title,
                'start' => $timeline->start_date->format('Y-m-d'),
                'end' => $timeline->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'color' => $timeline->color ?? $this->getTimelineColor($timeline->type),
                'extendedProps' => [
                    'type' => 'timeline',
                    'timeline_type' => $timeline->type,
                    'description' => $timeline->description,
                    'department' => $timeline->department?->name,
                    'program' => $timeline->program?->name,
                ],
            ];
        }

        // Get programs as events
        $programsQuery = Program::with('department');

        if ($user->isKabinet() && $user->department_id) {
            $programsQuery->where('department_id', $user->department_id);
        } elseif ($user->isStaff()) {
            $programsQuery->forUser($user->id);
        }

        $programs = $programsQuery->where('status', '!=', 'cancelled')->get();

        foreach ($programs as $program) {
            $events[] = [
                'id' => 'program_'.$program->id,
                'title' => '📋 '.$program->name,
                'start' => $program->start_date->format('Y-m-d'),
                'end' => $program->end_date->addDay()->format('Y-m-d'),
                'color' => '#10B981', // Green for programs
                'url' => route('programs.show', $program),
                'extendedProps' => [
                    'type' => 'program',
                    'department' => $program->department?->name,
                    'status' => $program->status,
                ],
            ];
        }

        // Get tasks with deadlines
        $tasksQuery = Task::with(['program', 'assignee']);

        if ($user->isStaff()) {
            $tasksQuery->where('assigned_to', $user->id);
        } elseif ($user->isKabinet() && $user->department_id) {
            $tasksQuery->whereHas('program', fn ($q) => $q->where('department_id', $user->department_id));
        }

        $tasks = $tasksQuery->whereNotNull('deadline')->get();

        foreach ($tasks as $task) {
            $events[] = [
                'id' => 'task_'.$task->id,
                'title' => '✅ '.$task->title,
                'start' => $task->deadline->format('Y-m-d'),
                'color' => $this->getTaskColor($task),
                'url' => route('tasks.show', $task),
                'extendedProps' => [
                    'type' => 'task',
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'progress' => $task->progress,
                ],
            ];
        }

        return response()->json($events);
    }

    private function getTimelineColor($type): string
    {
        return match ($type) {
            'global' => '#7751DE',
            'department' => '#D4A017',
            'program' => '#3F7A50',
            default => '#786F62',
        };
    }

    private function getTaskColor($task): string
    {
        if ($task->status === 'done') {
            return '#3F7A50';
        }

        if ($task->is_overdue) {
            return '#B44C40';
        }

        return match ($task->priority) {
            'high' => '#A96B12',
            'medium' => '#7751DE',
            default => '#786F62',
        };
    }

    public function global()
    {
        $timelines = Timeline::where('type', 'global')
            ->with(['department', 'program'])
            ->orderBy('start_date')
            ->get();

        return \Inertia\Inertia::render(
            'pages/TimelineCollectionPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $today = now()->toDateString();
                $canCreate = auth()->user()->hasRole(['admin', 'bph']);

                $statusFor = function ($timeline) use ($today) {
                    if ($timeline->end_date->toDateString() < $today) {
                        return ['label' => 'Selesai', 'tone' => 'secondary'];
                    }

                    if ($timeline->start_date->toDateString() > $today) {
                        return ['label' => 'Akan Datang', 'tone' => 'info'];
                    }

                    return ['label' => 'Berlangsung', 'tone' => 'success'];
                };

                $props = [
                    'title' => 'Timeline Global',
                    'description' => 'Agenda strategis organisasi yang jadi acuan lintas departemen.',
                    'icon' => 'fas fa-globe',
                    'breadcrumbs' => [
                        ['label' => 'Timeline', 'href' => route('timelines.index')],
                        ['label' => 'Global'],
                    ],
                    'actions' => array_values(array_filter([
                        ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
                        $canCreate ? ['href' => route('timelines.create', ['type' => 'global']), 'label' => 'Tambah Timeline', 'icon' => 'fas fa-plus', 'tone' => 'primary'] : null,
                    ])),
                    'summary' => [
                        ['label' => 'Total Global', 'value' => $timelines->count()],
                        ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
                        ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
                    ],
                    'items' => $timelines->map(fn ($timeline) => [
                        'title' => $timeline->title,
                        'description' => $timeline->description ?: 'Agenda global tanpa deskripsi tambahan.',
                        'color' => $timeline->color ?? '#7C3AED',
                        'range' => $timeline->start_date->format('d M Y').' - '.$timeline->end_date->format('d M Y'),
                        'scope' => ['label' => 'Global', 'tone' => 'primary'],
                        'status' => $statusFor($timeline),
                        'meta' => [],
                    ])->values(),
                    'emptyState' => [
                        'title' => 'Belum ada timeline global',
                        'text' => 'Agenda global organisasi belum ditambahkan.',
                        'action' => $canCreate ? [
                            'href' => route('timelines.create', ['type' => 'global']),
                            'label' => 'Tambah Timeline',
                            'icon' => 'fas fa-plus',
                        ] : null,
                    ],
                ];

                return $props;
            })(compact('timelines')),
        );
    }

    public function department(?Department $department = null)
    {
        $user = auth()->user();

        if (! $department && $user->department_id) {
            $department = Department::find($user->department_id);
        }

        if ($department && $user->isStaff() && $user->department_id !== $department->id) {
            abort(403, 'Anda tidak memiliki akses ke departemen ini.');
        }

        if ($department && $user->isKabinet() && $user->department_id !== $department->id) {
            abort(403, 'Anda tidak memiliki akses ke departemen ini.');
        }

        $departments = Department::active()->orderBy('name')->get();

        $timelines = $department
            ? Timeline::with(['program'])->where('department_id', $department->id)->orderBy('start_date')->get()
            : collect();

        return \Inertia\Inertia::render(
            'pages/TimelineCollectionPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $today = now()->toDateString();
                $canManage = auth()->user()->hasRole(['admin', 'bph', 'kabinet']);

                $statusFor = function ($timeline) use ($today) {
                    if ($timeline->end_date->toDateString() < $today) {
                        return ['label' => 'Selesai', 'tone' => 'secondary'];
                    }

                    if ($timeline->start_date->toDateString() > $today) {
                        return ['label' => 'Akan Datang', 'tone' => 'info'];
                    }

                    return ['label' => 'Berlangsung', 'tone' => 'success'];
                };

                $props = [
                    'title' => $department ? 'Agenda '.$department->name : 'Agenda Departemen',
                    'description' => $department
                        ? 'Lihat agenda departemen terpilih beserta timeline yang terkait dengan program kerjanya.'
                        : 'Pilih departemen untuk melihat agenda yang relevan.',
                    'icon' => 'fas fa-building',
                    'breadcrumbs' => [
                        ['label' => 'Timeline', 'href' => route('timelines.index')],
                        ['label' => 'Departemen'],
                        $department ? ['label' => $department->name] : null,
                    ],
                    'actions' => array_values(array_filter([
                        ['href' => route('timelines.calendar'), 'label' => 'Kalender', 'icon' => 'fas fa-calendar-days', 'tone' => 'secondary'],
                        $canManage ? [
                            'href' => $department
                                ? route('timelines.create', ['type' => 'department', 'department_id' => $department->id])
                                : route('timelines.create', ['type' => 'department']),
                            'label' => 'Tambah Timeline',
                            'icon' => 'fas fa-plus',
                            'tone' => 'primary',
                        ] : null,
                    ])),
                    'summary' => $department ? [
                        ['label' => 'Total', 'value' => $timelines->count()],
                        ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
                        ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
                    ] : [],
                    'sidebar' => [
                        'title' => 'Pilih Departemen',
                        'icon' => 'fas fa-building',
                        'description' => 'Buka agenda berdasarkan departemen yang ingin ditinjau.',
                        'items' => $departments->map(fn ($dept) => [
                            'href' => route('timelines.department', $dept),
                            'label' => $dept->name,
                            'active' => $department?->id === $dept->id,
                        ])->values(),
                    ],
                    'items' => $department ? $timelines->map(fn ($timeline) => [
                        'title' => $timeline->title,
                        'description' => $timeline->description ?: 'Belum ada deskripsi tambahan untuk agenda ini.',
                        'color' => $timeline->color ?? '#3B82F6',
                        'range' => $timeline->start_date->format('d M Y').' - '.$timeline->end_date->format('d M Y'),
                        'scope' => ['label' => 'Departemen', 'tone' => 'info'],
                        'status' => $statusFor($timeline),
                        'meta' => array_values(array_filter([
                            ['icon' => 'fas fa-building', 'label' => $department->name],
                            $timeline->program ? ['icon' => 'fas fa-diagram-project', 'label' => $timeline->program->name] : null,
                        ])),
                    ])->values() : [],
                    'emptyState' => $department
                        ? [
                            'title' => 'Belum ada timeline departemen',
                            'text' => 'Belum ada agenda yang terdaftar untuk departemen ini.',
                            'action' => $canManage ? [
                                'href' => route('timelines.create', ['type' => 'department', 'department_id' => $department->id]),
                                'label' => 'Tambah Timeline',
                                'icon' => 'fas fa-plus',
                            ] : null,
                        ]
                        : [
                            'title' => 'Pilih departemen terlebih dahulu',
                            'text' => 'Setelah departemen dipilih, agenda yang relevan akan tampil di panel utama.',
                            'action' => null,
                        ],
                ];

                $props['breadcrumbs'] = array_values(array_filter($props['breadcrumbs']));

                return $props;
            })(compact('timelines', 'departments', 'department')),
        );
    }

    public function program(Program $program)
    {
        $user = auth()->user();

        if ($user->isStaff() && ! $program->hasMemberOrPic($user->id)) {
            abort(403, 'Anda tidak memiliki akses ke program ini.');
        }

        if ($user->isKabinet() && $user->department_id !== $program->department_id) {
            abort(403, 'Anda tidak memiliki akses ke program ini.');
        }

        $program->loadMissing('department');

        $timelines = Timeline::where('program_id', $program->id)
            ->with(['department'])
            ->orderBy('start_date')
            ->get();

        return \Inertia\Inertia::render(
            'pages/TimelineCollectionPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $today = now()->toDateString();

                $statusFor = function ($timeline) use ($today) {
                    if ($timeline->end_date->toDateString() < $today) {
                        return ['label' => 'Selesai', 'tone' => 'secondary'];
                    }

                    if ($timeline->start_date->toDateString() > $today) {
                        return ['label' => 'Akan Datang', 'tone' => 'info'];
                    }

                    return ['label' => 'Berlangsung', 'tone' => 'success'];
                };

                $props = [
                    'title' => 'Agenda Program',
                    'description' => 'Rangkaian agenda untuk program '.$program->name.($program->department ? ' di departemen '.$program->department->name : '').'.',
                    'icon' => 'fas fa-diagram-project',
                    'breadcrumbs' => [
                        ['label' => 'Timeline', 'href' => route('timelines.index')],
                        ['label' => $program->name],
                    ],
                    'actions' => [
                        ['href' => route('programs.show', $program), 'label' => 'Kembali ke Program', 'icon' => 'fas fa-arrow-left', 'tone' => 'secondary'],
                    ],
                    'summary' => [
                        ['label' => 'Total', 'value' => $timelines->count()],
                        ['label' => 'Berlangsung', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'success')->count()],
                        ['label' => 'Akan Datang', 'value' => $timelines->filter(fn ($timeline) => $statusFor($timeline)['tone'] === 'info')->count()],
                    ],
                    'items' => $timelines->map(fn ($timeline) => [
                        'title' => $timeline->title,
                        'description' => $timeline->description ?: 'Belum ada deskripsi tambahan untuk agenda program ini.',
                        'color' => $timeline->color ?? '#10B981',
                        'range' => $timeline->start_date->format('d M Y').' - '.$timeline->end_date->format('d M Y'),
                        'scope' => ['label' => 'Program', 'tone' => 'secondary'],
                        'status' => $statusFor($timeline),
                        'meta' => array_values(array_filter([
                            $program->department ? ['icon' => 'fas fa-building', 'label' => $program->department->name] : null,
                            ['icon' => 'fas fa-diagram-project', 'label' => $program->name],
                        ])),
                    ])->values(),
                    'emptyState' => [
                        'title' => 'Belum ada timeline program',
                        'text' => 'Timeline untuk program ini belum ditambahkan.',
                        'action' => null,
                    ],
                ];

                return $props;
            })(compact('timelines', 'program')),
        );
    }

    public function create(Request $request)
    {
        $type = $request->get('type');
        $departmentId = $request->get('department_id');
        $programId = $request->get('program_id');

        if (! $type && $departmentId) {
            $type = 'department';
        } elseif (! $type && $programId) {
            $type = 'program';
        }

        $type = $type ?: 'global';

        $departments = Department::active()->orderBy('name')->get();
        $programs = Program::with('department')
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return \Inertia\Inertia::render(
            'pages/TimelineFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $selectedType = old('type', $type ?? 'global');

                $props = [
                    'title' => 'Form Tambah Timeline',
                    'description' => 'Susun agenda baru untuk organisasi, departemen, atau program kerja dengan rentang waktu yang jelas.',
                    'form' => [
                        'action' => route('timelines.store'),
                        'method' => 'POST',
                        'csrfToken' => csrf_token(),
                    ],
                    'submitLabel' => 'Simpan Timeline',
                    'values' => [
                        'title' => old('title'),
                        'description' => old('description'),
                        'type' => $selectedType,
                        'department_id' => old('department_id', $selectedType === 'department' ? $departmentId : null),
                        'program_id' => old('program_id', $selectedType === 'program' ? $programId : null),
                        'start_date' => old('start_date'),
                        'end_date' => old('end_date'),
                    ],
                    'departments' => $departments->map(fn ($department) => [
                        'value' => $department->id,
                        'label' => $department->name,
                    ])->values(),
                    'programs' => $programs->map(fn ($program) => [
                        'value' => $program->id,
                        'label' => $program->name.($program->department ? ' ('.$program->department->name.')' : ''),
                    ])->values(),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'cancelAction' => [
                        'href' => route('timelines.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                ];

                return $props;
            })(compact('departments', 'programs', 'type', 'departmentId', 'programId')),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'department_id' => 'nullable|required_if:type,department|exists:departments,id',
            'program_id' => 'nullable|required_if:type,program|exists:programs,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Clear unrelated fields based on type
        if ($validated['type'] === 'global') {
            $validated['department_id'] = null;
            $validated['program_id'] = null;
        } elseif ($validated['type'] === 'department') {
            $validated['program_id'] = null;
        }

        $validated['color'] = $this->getTimelineColor($validated['type']);

        $timeline = Timeline::create($validated);

        $googleSyncError = null;
        $googleSyncWarning = null;
        $googleSyncSuccess = null;
        $googleEventId = $this->googleCalendarService->upsertTimelineEvent($timeline->loadMissing(['department', 'program']));
        if ($googleEventId) {
            $timeline->update(['google_event_id' => $googleEventId]);
            $googleSyncSuccess = $this->formatGoogleSyncSuccess('ditambahkan');
        } elseif ($this->googleCalendarService->enabled()) {
            $googleSyncError = $this->formatGoogleSyncError($this->googleCalendarService->getLastError(), 'menambahkan');
        } else {
            $googleSyncWarning = $this->formatGoogleSyncDisabledWarning();
        }

        ActivityLog::log('created', "Created timeline: {$timeline->title}", $timeline);

        $redirect = redirect()->route('timelines.index')
            ->with('success', 'Timeline berhasil ditambahkan!');

        if ($googleSyncError) {
            $redirect->with('error', $googleSyncError);
        }
        if ($googleSyncWarning) {
            $redirect->with('warning', $googleSyncWarning);
        }
        if ($googleSyncSuccess) {
            $redirect->with('success', "Timeline berhasil ditambahkan! {$googleSyncSuccess}");
        }

        return $redirect;
    }

    public function edit(Timeline $timeline)
    {
        $timeline->loadMissing(['department', 'program']);

        $departments = Department::active()->orderBy('name')->get();
        $programs = Program::with('department')
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return \Inertia\Inertia::render(
            'pages/TimelineFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Edit Timeline',
                    'description' => 'Perbarui judul, konteks, dan rentang agenda agar timeline tetap akurat.',
                    'form' => [
                        'action' => route('timelines.update', $timeline),
                        'method' => 'POST',
                        'spoofMethod' => 'PUT',
                        'csrfToken' => csrf_token(),
                    ],
                    'submitLabel' => 'Update Timeline',
                    'values' => [
                        'title' => old('title', $timeline->title),
                        'description' => old('description', $timeline->description),
                        'type' => old('type', $timeline->type),
                        'department_id' => old('department_id', $timeline->department_id),
                        'program_id' => old('program_id', $timeline->program_id),
                        'start_date' => old('start_date', $timeline->start_date->format('Y-m-d')),
                        'end_date' => old('end_date', $timeline->end_date->format('Y-m-d')),
                    ],
                    'departments' => $departments->map(fn ($department) => [
                        'value' => $department->id,
                        'label' => $department->name,
                    ])->values(),
                    'programs' => $programs->map(fn ($program) => [
                        'value' => $program->id,
                        'label' => $program->name.($program->department ? ' ('.$program->department->name.')' : ''),
                    ])->values(),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'cancelAction' => [
                        'href' => route('timelines.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                ];

                return $props;
            })(compact('timeline', 'departments', 'programs')),
        );
    }

    public function update(Request $request, Timeline $timeline)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'department_id' => 'nullable|required_if:type,department|exists:departments,id',
            'program_id' => 'nullable|required_if:type,program|exists:programs,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validated['type'] === 'global') {
            $validated['department_id'] = null;
            $validated['program_id'] = null;
        } elseif ($validated['type'] === 'department') {
            $validated['program_id'] = null;
        }

        $validated['color'] = $this->getTimelineColor($validated['type']);

        $timeline->update($validated);

        $googleSyncError = null;
        $googleSyncWarning = null;
        $googleSyncSuccess = null;
        $googleEventId = $this->googleCalendarService->upsertTimelineEvent($timeline->fresh(['department', 'program']));
        if ($googleEventId && $googleEventId !== $timeline->google_event_id) {
            $timeline->update(['google_event_id' => $googleEventId]);
            $googleSyncSuccess = $this->formatGoogleSyncSuccess('diupdate');
        } elseif ($googleEventId) {
            $googleSyncSuccess = $this->formatGoogleSyncSuccess('diupdate');
        } elseif (! $googleEventId && $this->googleCalendarService->enabled()) {
            $googleSyncError = $this->formatGoogleSyncError($this->googleCalendarService->getLastError(), 'mengupdate');
        } else {
            $googleSyncWarning = $this->formatGoogleSyncDisabledWarning();
        }

        ActivityLog::log('updated', "Updated timeline: {$timeline->title}", $timeline);

        $redirect = redirect()->route('timelines.index')
            ->with('success', 'Timeline berhasil diupdate!');

        if ($googleSyncError) {
            $redirect->with('error', $googleSyncError);
        }
        if ($googleSyncWarning) {
            $redirect->with('warning', $googleSyncWarning);
        }
        if ($googleSyncSuccess) {
            $redirect->with('success', "Timeline berhasil diupdate! {$googleSyncSuccess}");
        }

        return $redirect;
    }

    public function destroy(Timeline $timeline)
    {
        $title = $timeline->title;
        $googleEventId = $timeline->google_event_id;

        ActivityLog::log('deleted', "Deleted timeline: {$title}", $timeline);

        $timeline->delete();

        $googleDeleteError = null;
        $googleDeleteWarning = null;
        $googleDeleteSuccess = null;
        $deleted = $this->googleCalendarService->deleteTimelineEvent($googleEventId, $timeline->id);
        if ($googleEventId && ! $deleted && $this->googleCalendarService->enabled()) {
            $googleDeleteError = $this->formatGoogleSyncError($this->googleCalendarService->getLastError(), 'menghapus');
        } elseif ($googleEventId && $deleted) {
            $googleDeleteSuccess = 'Event Google Calendar juga berhasil dihapus.';
        } elseif (! $this->googleCalendarService->enabled()) {
            $googleDeleteWarning = $this->formatGoogleSyncDisabledWarning();
        }

        $redirect = redirect()->route('timelines.index')
            ->with('success', "Timeline {$title} berhasil dihapus!");

        if ($googleDeleteError) {
            $redirect->with('error', $googleDeleteError);
        }
        if ($googleDeleteWarning) {
            $redirect->with('warning', $googleDeleteWarning);
        }
        if ($googleDeleteSuccess) {
            $redirect->with('success', "Timeline {$title} berhasil dihapus! {$googleDeleteSuccess}");
        }

        return $redirect;
    }

    private function formatGoogleSyncError(?string $reason, string $action): string
    {
        $suffix = $reason ? " Detail: {$reason}" : '';

        return "Timeline berhasil {$action} di aplikasi, tapi gagal sinkron ke Google Calendar.{$suffix}";
    }

    private function formatGoogleSyncSuccess(string $action): string
    {
        return "Sinkron Google Calendar berhasil {$action}.";
    }

    private function formatGoogleSyncDisabledWarning(): string
    {
        return 'Sinkron Google Calendar sedang nonaktif (GOOGLE_CALENDAR_ENABLED=false).';
    }
}
