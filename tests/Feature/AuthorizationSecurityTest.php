<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_kabinet_cannot_create_program_for_another_department(): void
    {
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);

        $response = $this->actingAs($kabinet)->post(route('programs.store'), [
            'name' => 'Forged Program',
            'department_id' => $otherDepartment->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'planning',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('programs', ['name' => 'Forged Program']);
    }

    public function test_program_management_data_is_scoped_to_kabinet_department(): void
    {
        $this->withoutVite();
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);
        $ownStaff = $this->user('staff', $ownDepartment);
        $otherStaff = $this->user('staff', $otherDepartment);
        $program = $this->program($ownDepartment, $kabinet);

        $response = $this->actingAs($kabinet)->get(route('programs.show', $program));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());
        $availableUserIds = collect($page['props']['team']['availableUsers'])->pluck('value');

        $this->assertTrue($availableUserIds->contains($ownStaff->id));
        $this->assertFalse($availableUserIds->contains($otherStaff->id));
        $this->assertSame(
            [$ownDepartment->id],
            collect($page['props']['editor']['fields'][2]['options'])->pluck('value')->all(),
        );
    }

    public function test_kabinet_cannot_evaluate_staff_from_another_department(): void
    {
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);
        $otherStaff = $this->user('staff', $otherDepartment);

        $response = $this->actingAs($kabinet)->post(route('evaluations.store'), [
            'user_id' => $otherStaff->id,
            'period' => now()->format('Y-m'),
            'kehadiran' => 4,
            'kedisiplinan' => 4,
            'tanggung_jawab' => 4,
            'kerjasama' => 4,
            'inisiatif' => 4,
            'komunikasi' => 4,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_kabinet_cannot_create_timeline_for_another_department(): void
    {
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);

        $response = $this->actingAs($kabinet)->post(route('timelines.store'), [
            'title' => 'Forged Timeline',
            'type' => 'department',
            'department_id' => $otherDepartment->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('timelines', ['title' => 'Forged Timeline']);
    }

    public function test_program_timeline_uses_its_program_department(): void
    {
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);
        $program = $this->program($ownDepartment, $kabinet);

        $response = $this->actingAs($kabinet)->post(route('timelines.store'), [
            'title' => 'Canonical Program Timeline',
            'type' => 'program',
            'department_id' => $otherDepartment->id,
            'program_id' => $program->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('timelines.index'));
        $this->assertDatabaseHas('timelines', [
            'title' => 'Canonical Program Timeline',
            'department_id' => $ownDepartment->id,
            'program_id' => $program->id,
        ]);
    }

    public function test_task_view_access_does_not_grant_staff_inline_mutation_access(): void
    {
        $this->withoutVite();
        [$department] = $this->departments();
        $staff = $this->user('staff', $department);
        $creator = $this->user('kabinet', $department);
        $task = Task::create([
            'title' => 'Department Task',
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'status' => 'todo',
            'priority' => 'medium',
            'progress' => 0,
        ]);

        $this->actingAs($staff)->get(route('tasks.show', $task))->assertOk();

        $response = $this->actingAs($staff)->patchJson(route('tasks.inline.update', $task), [
            'title' => 'Unauthorized Change',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Department Task',
        ]);
    }

    public function test_legacy_task_destroy_rejects_cross_department_kabinet(): void
    {
        [$ownDepartment, $otherDepartment] = $this->departments();
        $kabinet = $this->user('kabinet', $ownDepartment);
        $creator = $this->user('kabinet', $otherDepartment);
        $task = Task::create([
            'title' => 'Other Department Task',
            'department_id' => $otherDepartment->id,
            'created_by' => $creator->id,
            'status' => 'todo',
            'priority' => 'medium',
            'progress' => 0,
        ]);

        $response = $this->actingAs($kabinet)->delete(route('tasks.destroy', $task));

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_global_tasks_are_viewable_without_granting_kabinet_management(): void
    {
        [$department] = $this->departments();
        $kabinet = $this->user('kabinet', $department);
        $creator = $this->user('bph', $department);
        $task = Task::create([
            'title' => 'Organization Task',
            'is_global' => true,
            'created_by' => $creator->id,
            'status' => 'todo',
            'priority' => 'medium',
            'progress' => 0,
        ]);

        $this->assertTrue($kabinet->can('view', $task));
        $this->assertFalse($kabinet->can('update', $task));
        $this->assertFalse($kabinet->can('delete', $task));
        $this->assertFalse($kabinet->can('updateStatus', $task));
    }

    /**
     * @return array{Department, Department}
     */
    private function departments(): array
    {
        return [
            Department::create(['name' => 'Department One', 'status' => 'active']),
            Department::create(['name' => 'Department Two', 'status' => 'active']),
        ];
    }

    private function user(string $roleName, Department $department): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->createOne([
            'role_id' => $role->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);
    }

    private function program(Department $department, User $creator): Program
    {
        return Program::create([
            'name' => 'Owned Program',
            'department_id' => $department->id,
            'created_by' => $creator->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'active',
        ]);
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
