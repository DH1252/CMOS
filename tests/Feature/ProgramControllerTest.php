<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_manages_existing_program_from_detail_page_and_legacy_edit_route_is_missing(): void
    {
        [$admin, $program] = $this->adminAndProgram();

        $response = $this->actingAs($admin)->get(route('programs.show', $program));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('pages/ProgramDetailPage', $page['component']);
        $this->assertSame(route('programs.update', $program), $page['props']['editor']['form']['action']);
        $this->assertSame(route('programs.destroy', $program), $page['props']['editor']['dangerAction']['action']);

        $legacyEditRouteResponse = $this->actingAs($admin)->get("/programs/{$program->id}/edit");

        $legacyEditRouteResponse->assertNotFound();
    }

    public function test_admin_can_delete_program_from_detail_page_action(): void
    {
        [$admin, $program] = $this->adminAndProgram();

        $response = $this->actingAs($admin)->delete(route('programs.destroy', $program));

        $response->assertRedirect(route('programs.index'));
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    /**
     * @return array{0: User, 1: Program}
     */
    private function adminAndProgram(): array
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Administrator',
        ]);

        $department = Department::create([
            'name' => 'Ristek',
            'description' => 'Riset dan Teknologi',
            'status' => 'active',
        ]);

        /** @var User $admin */
        $admin = User::factory()->createOne([
            'name' => 'Admin Program',
            'role_id' => $adminRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $program = Program::create([
            'name' => 'Program Monitoring',
            'description' => 'Program kerja internal untuk monitoring.',
            'department_id' => $department->id,
            'created_by' => $admin->id,
            'start_date' => now()->subWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        return [$admin, $program];
    }

    /**
     * @return array<string, mixed>
     */
    private function inertiaPage(string $html): array
    {
        preg_match('/<script data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
