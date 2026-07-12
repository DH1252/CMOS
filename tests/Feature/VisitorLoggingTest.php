<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitorLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_optimization_request_is_not_logged_as_a_visit(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $this->get(route('images.optimize', ['path' => 'information-boards/test.jpg', 'f' => 'webp']))
            ->assertOk();

        $this->assertSame(0, Visitor::count());
    }

    public function test_public_page_visit_is_logged_as_a_visit(): void
    {
        $this->withoutVite();

        $this->get(route('home'))->assertOk();

        $this->assertSame(1, Visitor::count());
        $visit = Visitor::first();
        $this->assertNotNull($visit);
        $this->assertSame(route('home'), $visit->url);
    }

    public function test_authenticated_route_is_not_logged(): void
    {
        $user = $this->createUserWithRole('staff');

        $this->withoutVite();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertSame(0, Visitor::count());
    }

    public function test_duplicate_ip_on_same_day_is_logged_once(): void
    {
        $this->withoutVite();

        $this->get(route('home'))->assertOk();
        $this->get(route('informasi.index'))->assertOk();
        $this->get(route('acara.index'))->assertOk();

        $this->assertSame(1, Visitor::count());
    }

    public function test_post_request_is_not_logged_as_a_visit(): void
    {
        $this->withoutVite();

        $this->post('/broadcasting/auth', ['channel_name' => 'private-test', 'socket_id' => '1']);

        $this->assertSame(0, Visitor::count());
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::create([
            'name' => $roleName,
            'description' => ucfirst($roleName),
        ]);

        $cabinet = Cabinet::create([
            'name' => 'Kabinet Tes',
            'year' => '2026/2027',
            'status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Ristek',
            'description' => 'Departemen Ristek',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);

        return User::factory()->createOne([
            'role_id' => $role->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);
    }
}
