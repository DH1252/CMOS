<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportValuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_uses_the_one_to_five_evaluation_scale_and_includes_pending_tasks(): void
    {
        $bphRole = Role::create(['name' => 'bph', 'description' => 'BPH']);
        $staffRole = Role::create(['name' => 'staff', 'description' => 'Staff']);
        $cabinet = Cabinet::create([
            'name' => 'Kabinet Laporan',
            'year' => '2026/2027',
            'status' => 'active',
        ]);
        $department = Department::create([
            'name' => 'Departemen Laporan',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);
        $bph = User::factory()->createOne([
            'role_id' => $bphRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);
        $staff = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        Task::create([
            'title' => 'Menunggu review',
            'department_id' => $department->id,
            'assigned_to' => $staff->id,
            'created_by' => $bph->id,
            'status' => 'pending',
            'progress' => 80,
            'priority' => 'medium',
            'deadline' => now()->addDay(),
            'is_global' => false,
        ]);

        Evaluation::create([
            'user_id' => $staff->id,
            'evaluator_id' => $bph->id,
            'evaluator_type' => 'bph',
            'period' => '2026-07',
            'kehadiran' => 4,
            'kedisiplinan' => 4,
            'tanggung_jawab' => 5,
            'kerjasama' => 4,
            'inisiatif' => 5,
            'komunikasi' => 5,
        ]);

        $response = $this->actingAs($bph)->get(route('reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('averageEvaluationScore', '4.5')
            ->where('topStaff.0.score', '4.5')
            ->where('taskDistribution.2.label', 'Pending')
            ->where('taskDistribution.2.value', 1)
            ->where('taskDistribution.3.label', 'Selesai')
        );
    }

    public function test_pdf_view_uses_the_one_to_five_staff_score(): void
    {
        $html = view('reports.export-pdf', [
            'generatedAt' => Carbon::parse('2026-07-22 12:00:00'),
            'payload' => [
                'stats' => [
                    'totalUsers' => 1,
                    'totalPrograms' => 0,
                    'totalTasks' => 0,
                    'completedTasks' => 0,
                ],
                'averageEvaluationScore' => '4.5',
                'tasksByStatus' => [],
                'programsByStatus' => [],
                'departments' => new Collection,
                'topStaff' => collect([(object) [
                    'name' => 'Staff Teladan',
                    'department' => null,
                    'evaluations_avg_total_score' => 4.5,
                ]]),
            ],
        ])->render();

        $this->assertStringContainsString('<td>4.5</td>', $html);
        $this->assertStringNotContainsString('<td>1.1</td>', $html);
    }
}
