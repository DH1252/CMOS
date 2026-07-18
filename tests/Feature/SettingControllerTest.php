<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\ApplicationTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_general_settings(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'evaluation_period' => 'semester',
            'app_timezone' => 'Asia/Jakarta',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertSame('CMOS Prime', Setting::get('app_name'));
        $this->assertSame('HIMATEKKOM ITS', Setting::get('organization_name'));
        $this->assertSame('semester', Setting::get('evaluation_period'));
        $this->assertSame('Asia/Jakarta', Setting::get('app_timezone'));
    }

    public function test_non_admin_cannot_update_settings(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'evaluation_period' => 'yearly',
            'app_timezone' => 'Asia/Jakarta',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_view_general_settings_page_without_color_customization(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('values.appName')
            ->has('values.organizationName')
            ->has('values.evaluationPeriod')
            ->has('values.appTimezone')
            ->has('values.timezoneOptions')
            ->has('values.periodOptions')
            ->missing('colors')
            ->missing('values.themeColor')
            ->missing('values.customCss')
        );
    }

    public function test_invalid_general_settings_are_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->from(route('settings.index'))->actingAs($admin)->put(route('settings.update', 'general'), [
            'app_name' => '',
            'organization_name' => '',
            'evaluation_period' => 'weekly',
            'app_timezone' => 'Not/A-Timezone',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors(['app_name', 'organization_name', 'evaluation_period', 'app_timezone']);
    }

    public function test_configured_timezone_is_applied_without_changing_utc_storage_timezone(): void
    {
        Setting::set('app_timezone', 'Pacific/Auckland');

        $timezone = app(ApplicationTimezone::class)->apply();

        $this->assertSame('Pacific/Auckland', $timezone);
        $this->assertSame('Pacific/Auckland', config('app.client_timezone'));
        $this->assertSame('Pacific/Auckland', config('app.schedule_timezone'));
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
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
