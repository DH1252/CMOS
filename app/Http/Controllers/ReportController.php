<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Program;
use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $payload = $this->buildPayload();

        return \Inertia\Inertia::render(
            'pages/ReportDashboardPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Laporan & Statistik',
                    'description' => 'Ringkasan task, program, dan evaluasi.',
                    'stats' => [
                        ['label' => 'Total Anggota', 'value' => $stats['totalUsers'], 'icon' => 'fas fa-users', 'tone' => 'primary'],
                        ['label' => 'Total Proker', 'value' => $stats['totalPrograms'], 'icon' => 'fas fa-diagram-project', 'tone' => 'info'],
                        ['label' => 'Total Task', 'value' => $stats['totalTasks'], 'icon' => 'fas fa-list-check', 'tone' => 'warning'],
                        ['label' => 'Task Selesai', 'value' => $stats['completedTasks'], 'icon' => 'fas fa-circle-check', 'tone' => 'success'],
                    ],
                    'taskDistribution' => [
                        ['label' => 'Todo', 'value' => $tasksByStatus['todo'], 'tone' => 'secondary'],
                        ['label' => 'In Progress', 'value' => $tasksByStatus['in_progress'], 'tone' => 'warning'],
                        ['label' => 'Selesai', 'value' => $tasksByStatus['done'], 'tone' => 'success'],
                    ],
                    'programDistribution' => [
                        ['label' => 'Planning', 'value' => $programsByStatus['planning'], 'tone' => 'secondary'],
                        ['label' => 'Active', 'value' => $programsByStatus['active'], 'tone' => 'info'],
                        ['label' => 'Completed', 'value' => $programsByStatus['completed'], 'tone' => 'success'],
                        ['label' => 'Cancelled', 'value' => $programsByStatus['cancelled'], 'tone' => 'warning'],
                    ],
                    'departments' => $departments->map(function ($department) {
                        $totalTasks = (int) ($department->tasks_count ?? 0);
                        $completedTasks = (int) ($department->completed_tasks_count ?? 0);

                        return [
                            'name' => $department->name,
                            'members' => (int) $department->users_count,
                            'programs' => (int) $department->programs_count,
                            'totalTasks' => $totalTasks,
                            'completedTasks' => $completedTasks,
                            'completionRate' => $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0,
                        ];
                    })->values(),
                    'topStaff' => $topStaff->values()->map(fn ($staff, $index) => [
                        'rank' => $index + 1,
                        'name' => $staff->name,
                        'avatar' => $staff->avatar_url,
                        'department' => $staff->department?->name ?? '-',
                        'score' => number_format(($staff->evaluations_avg_total_score ?? 0) / 4, 1),
                    ]),
                    'exports' => [
                        ['label' => 'Export PDF', 'href' => route('reports.export', 'pdf'), 'icon' => 'fas fa-file-pdf', 'tone' => 'danger'],
                        ['label' => 'Export Excel', 'href' => route('reports.export', 'excel'), 'icon' => 'fas fa-file-excel', 'tone' => 'success'],
                    ],
                ];

                return $props;
            })([
                'stats' => $payload['stats'],
                'departments' => $payload['departments'],
                'tasksByStatus' => $payload['tasksByStatus'],
                'programsByStatus' => $payload['programsByStatus'],
                'topStaff' => $payload['topStaff'],
            ]),
        );
    }

    public function export(Request $request, $type)
    {
        $payload = $this->buildPayload();
        $fileName = 'laporan-cmos-'.now()->format('Y-m-d');

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('reports.export-pdf', [
                'payload' => $payload,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            return $pdf->download($fileName.'.pdf');
        }

        if ($type === 'excel') {
            $rows = $this->buildExcelRows($payload);

            $export = new class($rows) implements FromArray, ShouldAutoSize, WithHeadings
            {
                public function __construct(private readonly array $rows) {}

                public function array(): array
                {
                    return $this->rows;
                }

                public function headings(): array
                {
                    return ['Bagian', 'Label', 'Nilai', 'Keterangan'];
                }
            };

            return Excel::download($export, $fileName.'.xlsx');
        }

        abort(404);
    }

    /**
     * @return array{stats: array<string, int>, departments: \Illuminate\Support\Collection<int, Department>, tasksByStatus: array<string, int>, programsByStatus: array<string, int>, topStaff: \Illuminate\Support\Collection<int, User>, averageEvaluationScore: string}
     */
    private function buildPayload(): array
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalPrograms' => Program::count(),
            'totalTasks' => Task::count(),
            'completedTasks' => Task::where('status', 'done')->count(),
        ];

        $departments = Department::query()
            ->withCount(['users', 'programs'])
            ->addSelect([
                'tasks_count' => Task::query()
                    ->selectRaw('count(*)')
                    ->leftJoin('programs', 'tasks.program_id', '=', 'programs.id')
                    ->where(function ($query) {
                        $query
                            ->whereColumn('tasks.department_id', 'departments.id')
                            ->orWhereColumn('programs.department_id', 'departments.id');
                    }),
                'completed_tasks_count' => Task::query()
                    ->selectRaw('count(*)')
                    ->leftJoin('programs', 'tasks.program_id', '=', 'programs.id')
                    ->where('tasks.status', 'done')
                    ->where(function ($query) {
                        $query
                            ->whereColumn('tasks.department_id', 'departments.id')
                            ->orWhereColumn('programs.department_id', 'departments.id');
                    }),
            ])
            ->orderBy('name')
            ->get();

        $tasksByStatus = [
            'todo' => Task::where('status', 'todo')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'done' => Task::where('status', 'done')->count(),
        ];

        $programsByStatus = [
            'planning' => Program::where('status', 'planning')->count(),
            'active' => Program::where('status', 'active')->count(),
            'completed' => Program::where('status', 'completed')->count(),
            'cancelled' => Program::where('status', 'cancelled')->count(),
        ];

        $topStaff = User::byRole('staff')
            ->with('department')
            ->withAvg('evaluations', 'total_score')
            ->get()
            ->filter(fn (User $staff) => (float) ($staff->evaluations_avg_total_score ?? 0) > 0)
            ->sortByDesc(fn (User $staff) => (float) ($staff->evaluations_avg_total_score ?? 0))
            ->take(10)
            ->values();

        $averageEvaluationScore = number_format((float) Evaluation::query()->avg('total_score') / 4, 1);

        return compact('stats', 'departments', 'tasksByStatus', 'programsByStatus', 'topStaff', 'averageEvaluationScore');
    }

    /**
     * @param  array{stats: array<string, int>, departments: Collection<int, Department>, tasksByStatus: array<string, int>, programsByStatus: array<string, int>, topStaff: Collection<int, User>, averageEvaluationScore: string}  $payload
     * @return array<int, array{string, string, string, string}>
     */
    private function buildExcelRows(array $payload): array
    {
        $rows = [];

        foreach ($payload['stats'] as $key => $value) {
            $rows[] = ['Ringkasan', $key, (string) $value, ''];
        }

        $rows[] = ['Ringkasan', 'average_evaluation_score', $payload['averageEvaluationScore'], 'Skala 1-5'];

        foreach ($payload['tasksByStatus'] as $status => $value) {
            $rows[] = ['Distribusi task', $status, (string) $value, ''];
        }

        foreach ($payload['programsByStatus'] as $status => $value) {
            $rows[] = ['Distribusi program', $status, (string) $value, ''];
        }

        foreach ($payload['departments'] as $department) {
            $rows[] = [
                'Departemen',
                $department->name,
                (string) ($department->completed_tasks_count ?? 0),
                sprintf(
                    '%d anggota, %d program, %d/%d task selesai',
                    (int) ($department->users_count ?? 0),
                    (int) ($department->programs_count ?? 0),
                    (int) ($department->completed_tasks_count ?? 0),
                    (int) ($department->tasks_count ?? 0),
                ),
            ];
        }

        foreach ($payload['topStaff'] as $staff) {
            $rows[] = [
                'Top staff',
                $staff->name,
                number_format(((float) ($staff->evaluations_avg_total_score ?? 0)) / 4, 1),
                $staff->department?->name ?? '-',
            ];
        }

        return $rows;
    }
}
