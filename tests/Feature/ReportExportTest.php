<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Program;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bph_user_can_download_report_pdf(): void
    {
        $user = $this->createReportDataset();

        $response = $this->actingAs($user)->get(route('reports.export', 'pdf'));

        $response->assertOk();
        $response->assertDownload('laporan-cmos-'.now()->format('Y-m-d').'.pdf');
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_bph_user_can_download_report_excel(): void
    {
        $user = $this->createReportDataset();

        $response = $this->actingAs($user)->get(route('reports.export', 'excel'));

        $response->assertOk();
        $response->assertDownload('laporan-cmos-'.now()->format('Y-m-d').'.xlsx');
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function createReportDataset(): User
    {
        $bphRole = Role::create([
            'name' => 'bph',
            'description' => 'BPH',
        ]);

        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Staff',
        ]);

        $cabinet = Cabinet::create([
            'name' => 'Kabinet Sentra Sinergi',
            'year' => '2026/2027',
            'status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Media',
            'description' => 'Departemen media',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);

        $bphUser = User::factory()->createOne([
            'role_id' => $bphRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $staffUser = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $program = Program::create([
            'name' => 'Publikasi Semester',
            'description' => 'Program kerja media',
            'department_id' => $department->id,
            'created_by' => $bphUser->id,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        Task::create([
            'title' => 'Susun kalender konten',
            'program_id' => $program->id,
            'department_id' => $department->id,
            'assigned_to' => $staffUser->id,
            'created_by' => $bphUser->id,
            'status' => 'done',
            'sort_order' => 1,
            'progress' => 100,
            'priority' => 'high',
            'deadline' => now()->addDay()->toDateString(),
            'is_global' => false,
        ]);

        Task::create([
            'title' => 'Review draft publikasi',
            'program_id' => $program->id,
            'department_id' => $department->id,
            'assigned_to' => $staffUser->id,
            'created_by' => $bphUser->id,
            'status' => 'in_progress',
            'sort_order' => 2,
            'progress' => 60,
            'priority' => 'medium',
            'deadline' => now()->addDays(2)->toDateString(),
            'is_global' => false,
        ]);

        Evaluation::create([
            'user_id' => $staffUser->id,
            'evaluator_id' => $bphUser->id,
            'evaluator_type' => 'bph',
            'period' => now()->format('Y-m'),
            'kehadiran' => 4,
            'kedisiplinan' => 4,
            'tanggung_jawab' => 5,
            'kerjasama' => 4,
            'inisiatif' => 4,
            'komunikasi' => 5,
            'notes' => 'Baik',
        ]);

        return $bphUser;
    }
}
