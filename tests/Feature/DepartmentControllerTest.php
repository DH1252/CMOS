<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_staff_json_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('departments.store'), [
            'name' => 'Media dan Informasi',
            'status' => 'active',
            'staff_graphics' => '[{"image":',
            'staff_order' => '[invalid]',
        ]);

        $response->assertSessionHasErrors(['staff_graphics', 'staff_order']);
        $this->assertDatabaseMissing('departments', ['name' => 'Media dan Informasi']);
    }

    public function test_duplicate_inferred_slug_is_returned_as_a_validation_error(): void
    {
        $admin = $this->createAdmin();
        Department::create([
            'name' => 'Media dan Informasi',
            'slug' => 'medfo',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('departments.store'), [
            'name' => 'Biro Media Informasi',
            'slug' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('departments', 1);
    }

    private function createAdmin(): User
    {
        $role = Role::create([
            'name' => 'admin',
            'description' => 'Administrator',
        ]);

        return User::factory()->createOne([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
