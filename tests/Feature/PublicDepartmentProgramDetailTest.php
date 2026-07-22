<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDepartmentProgramDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_page_exposes_complete_public_program_details(): void
    {
        $this->withoutVite();
        $staffRole = Role::create(['name' => 'staff']);
        $department = Department::create([
            'name' => 'Pengembangan Sumber Daya Mahasiswa',
            'slug' => 'psdm',
            'status' => 'active',
        ]);
        $creator = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
        ]);
        $pic = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
        ]);
        $member = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
        ]);
        $program = Program::create([
            'name' => 'Workshop Teknologi',
            'description' => 'Program peningkatan kompetensi mahasiswa.',
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'status' => 'active',
        ]);
        $program->pics()->attach($pic);
        $program->members()->attach($member, ['role' => 'leader']);

        Task::create([
            'title' => 'Persiapan materi',
            'program_id' => $program->id,
            'created_by' => $creator->id,
            'status' => 'in_progress',
            'priority' => 'medium',
            'progress' => 50,
        ]);
        Task::create([
            'title' => 'Pelaksanaan acara',
            'program_id' => $program->id,
            'created_by' => $creator->id,
            'status' => 'done',
            'priority' => 'high',
            'progress' => 100,
        ]);
        $response = $this->get(route('departemen', ['slug' => 'psdm']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('public/PublicDepartmentDetailPage', false)
            ->where('programs.0.id', $program->id)
            ->where('programs.0.name', 'Workshop Teknologi')
            ->where('programs.0.progress', 75)
            ->where('programs.0.responsiblePeople.0.name', $pic->name)
            ->where('programs.0.responsiblePeople.1.name', $member->name)
            ->where('programs.0.taskSummary.total', 2)
            ->where('programs.0.taskSummary.inProgress', 1)
            ->where('programs.0.taskSummary.done', 1)
        );
    }
}
