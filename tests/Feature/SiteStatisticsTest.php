<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_site_statistics_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        Visitor::create([
            'ip_address' => '127.0.0.1',
            'url' => route('home'),
            'user_agent' => 'Tester/1.0',
        ]);

        $response = $this->actingAs($admin)->get(route('statistics.index'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('pages/SiteStatisticsPage', $page['component']);
        $this->assertNotEmpty($page['props']['stats']);
        $this->assertGreaterThanOrEqual(1, $page['props']['stats'][0]['value']); // today (includes this request)
        $this->assertNotEmpty($page['props']['visitorTrend']);
        $this->assertNotEmpty($page['props']['recentVisitors']);
    }

    public function test_non_admin_cannot_view_site_statistics_page(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->get(route('statistics.index'));

        $response->assertForbidden();
    }

    public function test_public_page_exposes_visitor_counts_via_shared_props(): void
    {
        $this->withoutVite();

        Visitor::create([
            'ip_address' => '127.0.0.1',
            'url' => route('home'),
            'user_agent' => 'Tester/1.0',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertArrayHasKey('visitorStats', $page['props']);
        $this->assertGreaterThanOrEqual(1, $page['props']['visitorStats']['today']);
        $this->assertGreaterThanOrEqual(1, $page['props']['visitorStats']['thisMonth']);
        $this->assertGreaterThanOrEqual(1, $page['props']['visitorStats']['total']);
    }

    public function test_auth_shell_statistics_link_available_for_admin(): void
    {
        $admin = $this->createUserWithRole('admin');

        Visitor::create([
            'ip_address' => '127.0.0.1',
            'url' => route('home'),
            'user_agent' => 'Tester/1.0',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertNotNull($page['props']['shell']['links']['statistics']);
        $this->assertArrayNotHasKey('visitorStats', $page['props']['shell']);
    }

    public function test_statistics_nav_item_appears_for_admin(): void
    {
        $admin = $this->createUserWithRole('admin');

        $page = $this->inertiaPage(
            $this->actingAs($admin)->get(route('dashboard'))->getContent(),
        );

        $adminSettings = collect($page['props']['shell']['navSections'])
            ->firstWhere('title', 'Pengaturan')['items'] ?? [];

        $this->assertTrue(collect($adminSettings)->contains(fn (array $item) => $item['label'] === 'Statistik Situs'));
    }

    public function test_statistics_nav_item_hidden_for_staff(): void
    {
        $staff = $this->createUserWithRole('staff');

        $page = $this->inertiaPage(
            $this->actingAs($staff)->get(route('dashboard'))->getContent(),
        );

        $this->assertNull(
            collect($page['props']['shell']['navSections'])
                ->firstWhere('title', 'Pengaturan')
        );
    }

    public function test_admin_can_trigger_competition_manual_sync(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->post(route('statistics.fetch-competitions'));

        $response->assertOk();
        $response->assertJsonStructure(['success', 'output']);
    }

    public function test_non_admin_cannot_trigger_competition_manual_sync(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->post(route('statistics.fetch-competitions'));

        $response->assertForbidden();
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
