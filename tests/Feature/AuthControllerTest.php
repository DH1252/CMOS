<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_defaults_remember_to_false(): void
    {
        $this->withoutVite();

        $response = $this->get(route('login'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('LoginPage', $page['component']);
        $this->assertFalse($page['props']['remember']);
    }

    public function test_login_page_preserves_old_remember_input_as_boolean(): void
    {
        $this->withoutVite();

        $response = $this
            ->withSession([
                '_old_input' => [
                    'email' => 'user@example.com',
                    'remember' => '1',
                ],
            ])
            ->get(route('login'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertTrue($page['props']['remember']);
        $this->assertSame('user@example.com', $page['props']['email']);
    }

    public function test_login_page_exposes_configured_theme_color(): void
    {
        $this->withoutVite();
        Setting::set('theme_color', 'teal');
        Setting::set('theme_primary', '#14B8A6');

        $response = $this->get(route('login'));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('teal', $page['props']['themeColor']);
        $this->assertSame('#14B8A6', $page['props']['themeVariables']['brand-primary-base']);
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
