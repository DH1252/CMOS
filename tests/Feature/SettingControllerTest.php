<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_color_and_general_settings(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'theme_color' => 'teal',
            'theme_primary' => '#14B8A6',
            'theme_hover' => '#0D9488',
            'theme_soft' => '#2DD4BF',
            'theme_light' => '#CCFBF1',
            'theme_secondary' => '#0F766E',
            'theme_secondary_soft' => '#D4F3EF',
            'theme_primary_foreground' => '#0F172A',
            'evaluation_period' => 'semester',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertSame('CMOS Prime', Setting::get('app_name'));
        $this->assertSame('teal', Setting::get('theme_color'));
        $this->assertSame('#14B8A6', Setting::get('theme_primary'));
        $this->assertSame('#0D9488', Setting::get('theme_hover'));
        $this->assertSame('#2DD4BF', Setting::get('theme_soft'));
        $this->assertSame('#CCFBF1', Setting::get('theme_light'));
        $this->assertSame('#0F766E', Setting::get('theme_secondary'));
        $this->assertSame('#D4F3EF', Setting::get('theme_secondary_soft'));
        $this->assertSame('#0F172A', Setting::get('theme_primary_foreground'));
        $this->assertSame('semester', Setting::get('evaluation_period'));
    }

    public function test_non_admin_cannot_update_settings(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'theme_color' => 'blue',
            'theme_primary' => '#3B82F6',
            'theme_hover' => '#2563EB',
            'theme_soft' => '#60A5FA',
            'theme_light' => '#DBEAFE',
            'theme_secondary' => '#1D4ED8',
            'theme_secondary_soft' => '#DFE8FD',
            'theme_primary_foreground' => '#FFFFFF',
            'evaluation_period' => 'yearly',
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_theme_color_is_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->from(route('settings.index'))->actingAs($admin)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'theme_color' => 'invalid-color',
            'theme_primary' => '#7C3AED',
            'theme_hover' => '#6D28D9',
            'theme_soft' => '#A78BFA',
            'theme_light' => '#EDE9FE',
            'theme_secondary' => '#5B2BA9',
            'theme_secondary_soft' => '#E9E0F8',
            'theme_primary_foreground' => '#FFFFFF',
            'evaluation_period' => 'quarterly',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors(['theme_color']);
    }

    public function test_invalid_custom_color_hex_is_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->from(route('settings.index'))->actingAs($admin)->put(route('settings.update', 'general'), [
            'app_name' => 'CMOS Prime',
            'organization_name' => 'HIMATEKKOM ITS',
            'theme_color' => 'purple',
            'theme_primary' => 'badhex',
            'theme_hover' => '#6D28D9',
            'theme_soft' => '#A78BFA',
            'theme_light' => '#EDE9FE',
            'theme_secondary' => '#5B2BA9',
            'theme_secondary_soft' => '#E9E0F8',
            'theme_primary_foreground' => '#FFFFFF',
            'evaluation_period' => 'quarterly',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors(['theme_primary']);
    }

    public function test_admin_can_view_landing_appearance_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('settings.landing'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('values.customCss')
            ->has('colors')
            ->where('previewUrl', '/')
        );
    }

    public function test_admin_can_update_landing_colors(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->put(route('settings.landing.update'), [
            'css_landing_text_strong' => '#111111',
            'css_landing_page_bg' => '#FAFAFA',
            'css_landing_brand_primary' => '#FF0000',
        ]);

        $response->assertRedirect(route('settings.landing'));
        $response->assertSessionHas('success');

        $this->assertSame('#111111', Setting::get('css_landing_text_strong'));
        $this->assertSame('#FAFAFA', Setting::get('css_landing_page_bg'));
        $this->assertSame('#FF0000', Setting::get('css_landing_brand_primary'));
    }

    public function test_non_admin_cannot_update_landing_colors(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->put(route('settings.landing.update'), [
            'css_landing_brand_primary' => '#FF0000',
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_landing_hex_is_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->from(route('settings.landing'))->actingAs($admin)->put(route('settings.landing.update'), [
            'css_landing_brand_primary' => 'not-a-hex',
        ]);

        $response->assertRedirect(route('settings.landing'));
        $response->assertSessionHasErrors(['css_landing_brand_primary']);
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
