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

        $article = InformationBoard::query()->published()->firstOrFail();

        $response = $this->get(route('informasi.show', $article));

        $response->assertOk();
        $response->assertSee($article->title);
        $response->assertSee($article->content, false);
    }

    public function test_internal_article_route_resolves_by_slug_for_authenticated_user(): void
    {
        $this->seed();

        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $user = User::query()->where('role_id', $role->id)->firstOrFail();
        $article = InformationBoard::query()->firstOrFail();

        $response = $this->actingAs($user)->get(route('information-boards.show', $article));

        $response->assertOk();
        $response->assertSee($article->title);
        $response->assertSee($article->content, false);
    }
}
