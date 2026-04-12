<?php

namespace Tests\Feature;

use App\Services\SvelteSsrRenderer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicPageSsrTest extends TestCase
{
    public function test_landing_page_includes_server_rendered_public_app_markup(): void
    {
        $this->withoutVite();
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        $this->app->instance(SvelteSsrRenderer::class, new class extends SvelteSsrRenderer
        {
            /**
             * @param  array<string, mixed>  $props
             * @return array{html: string, head: string, rendered: bool}
             */
            public function render(string $component, array $props = []): array
            {
                return [
                    'html' => '<main data-test-ssr="public-app">SSR landing shell</main>',
                    'head' => '<meta name="ssr-test" content="landing">',
                    'rendered' => true,
                ];
            }
        });

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<meta name="ssr-test" content="landing">', false);
        $response->assertSee('<main data-test-ssr="public-app">SSR landing shell</main>', false);
        $response->assertSee('data-ssr="true"', false);
    }
}
