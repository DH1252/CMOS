<?php

namespace Tests\Feature;

use App\Models\InformationBoard;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformationBoardRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_article_route_resolves_by_slug(): void
    {
        $this->seed();
        $this->withoutVite();

        $article = InformationBoard::query()->published()->firstOrFail();

        $response = $this->get(route('informasi.show', $article));

        $response->assertOk();
        $response->assertSee('id="app"', false);
        $response->assertSee('data-page=', false);
        $response->assertSee($article->title, false);
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('PublicApp', $page['component']);
        $this->assertSame('info-show', $page['props']['page']);
        $this->assertSame($article->title, $page['props']['infoShow']['article']['title']);
        $this->assertSame($article->seo_title, $page['props']['infoShow']['article']['seoTitle']);
        $this->assertSame($article->content, $page['props']['infoShow']['article']['contentHtml']);
    }

    public function test_internal_article_route_resolves_by_slug_for_authenticated_user(): void
    {
        $this->seed();

        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $user = User::query()->where('role_id', $role->id)->firstOrFail();
        $article = InformationBoard::query()->firstOrFail();

        $response = $this->actingAs($user)->get(route('information-boards.show', $article));

        $response->assertOk();
        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('pages/InformationBoardShowPage', $page['component']);
        $this->assertSame($article->title, $page['props']['article']['title']);
        $this->assertSame($article->content, $page['props']['article']['contentHtml']);
        $this->assertArrayHasKey('previewTheme', $page['props']);
        $this->assertNotSame('', $page['props']['previewTheme']['backgroundColor'] ?? '');
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
