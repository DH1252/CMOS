<?php

namespace Tests\Feature;

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
