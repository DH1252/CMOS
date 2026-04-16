<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\InformationBoard;
use App\Models\Program;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_department_progress_without_ambiguous_status_query(): void
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Administrator',
        ]);

        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Staff',
        ]);

        $cabinet = Cabinet::create([
            'name' => 'Kabinet Harmoni',
            'year' => '2026/2027',
            'status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Ristek',
            'description' => 'Riset dan Teknologi',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);

        /** @var User $admin */
        $admin = User::factory()->createOne([
            'name' => 'Admin Dashboard',
            'role_id' => $adminRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        /** @var User $staff */
        $staff = User::factory()->createOne([
            'name' => 'Staff Dashboard',
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $program = Program::create([
            'name' => 'Internal Dashboard',
            'description' => 'Program kerja monitoring internal.',
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'start_date' => now()->subWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        Task::create([
            'title' => 'Task selesai',
            'description' => 'Task dashboard yang selesai.',
            'program_id' => $program->id,
            'department_id' => $department->id,
            'assigned_to' => $staff->id,
            'created_by' => $admin->id,
            'status' => 'done',
            'progress' => 100,
            'priority' => 'high',
            'deadline' => now()->addDay()->toDateString(),
            'is_global' => false,
        ]);

        Task::create([
            'title' => 'Task berjalan',
            'description' => 'Task dashboard yang masih aktif.',
            'program_id' => $program->id,
            'department_id' => $department->id,
            'assigned_to' => $staff->id,
            'created_by' => $admin->id,
            'status' => 'in_progress',
            'progress' => 50,
            'priority' => 'medium',
            'deadline' => now()->addDays(2)->toDateString(),
            'is_global' => false,
        ]);

        InformationBoard::create([
            'user_id' => $admin->id,
            'title' => 'Artikel dashboard terbaru',
            'slug' => 'artikel-dashboard-terbaru',
            'excerpt' => 'Ringkasan artikel yang harus tampil pada dashboard.',
            'content' => '<p>Konten artikel dashboard.</p>',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('DashboardPage', $page['component']);
        $this->assertSame('Artikel dashboard terbaru', $page['props']['latestInformationBoards'][0]['title']);
        $this->assertArrayHasKey('quickChat', $page['props']['shell']);
        $this->assertArrayNotHasKey('users', $page['props']['shell']['quickChat']);
        $this->assertArrayNotHasKey('conversations', $page['props']['shell']['quickChat']);
        $this->assertSame(route('messages.sidebar-data'), $page['props']['shell']['quickChat']['endpoints']['sidebarData']);
        Assert::assertTrue(collect($page['props']['departmentProgress'])->contains(function (array $department): bool {
            return ($department['name'] ?? null) === 'Ristek'
                && (int) ($department['total'] ?? 0) === 2
                && (int) ($department['done'] ?? 0) === 1
                && (int) ($department['percentage'] ?? 0) === 50;
        }));
    }

    /**
     * @return array<string, mixed>
     */
    private function inertiaPage(string $html): array
    {
        preg_match('/data-page="([^"]+)"/', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true, 512, JSON_THROW_ON_ERROR);
    }
}
