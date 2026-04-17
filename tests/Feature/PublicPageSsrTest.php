<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicPageSsrTest extends TestCase
{
    public function test_landing_page_returns_public_inertia_page_payload(): void
    {
        $this->withoutVite();
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="app"', false);
        $response->assertSee('data-page=', false);
        $response->assertDontSee('id="svelte-public-root"', false);

        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('PublicApp', $page['component']);
        $this->assertSame('landing', $page['props']['page']);
        $this->assertSame('purple', $page['props']['themeColor']);
        $this->assertArrayHasKey('brand-primary-base', $page['props']['themeVariables']);
        $response->assertSee('data-brand="purple"', false);
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
