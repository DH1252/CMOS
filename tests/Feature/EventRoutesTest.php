<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_route_resolves_by_slug(): void
    {
        $this->seed();
        $this->withoutVite();

        $event = Event::query()->published()->upcoming()->firstOrFail();

        $response = $this->get(route('acara.show', $event));

        $response->assertOk();
        $response->assertSee('id="app"', false);
        $response->assertSee($event->title, false);

        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('PublicApp', $page['component']);
        $this->assertSame('acara-show', $page['props']['page']);
        $this->assertSame($event->title, $page['props']['acaraShow']['event']['title']);
        $this->assertSame(route('acara.show', $event), $page['props']['seo']['canonical']);
        $this->assertContains('Event', $this->jsonLdTypes($page['props']['seo']['jsonLd']));
        $this->assertContains('BreadcrumbList', $this->jsonLdTypes($page['props']['seo']['jsonLd']));
        $response->assertSee('<script type="application/ld+json">', false);
        $response->assertSee('"@type":"Event"', false);
    }

    public function test_public_event_index_lists_only_upcoming_published_events(): void
    {
        $this->seed();
        $this->withoutVite();

        $response = $this->get(route('acara.index'));

        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('PublicApp', $page['component']);
        $this->assertSame('acara-index', $page['props']['page']);

        $titles = collect($page['props']['acaraIndex']['events'])->pluck('title');

        $this->assertTrue($titles->contains('LKMM TD 2026'));
        // Past published event must not appear (upcoming scope).
        $this->assertFalse($titles->contains('Open Recruitment Staff 2026 (Selesai)'));
        // Draft event must not appear (published scope).
        $this->assertFalse($titles->contains('Company Visit (Draft)'));

        $this->assertContains('CollectionPage', $this->jsonLdTypes($page['props']['seo']['jsonLd']));
    }

    public function test_draft_event_returns_404_on_public_show(): void
    {
        $this->seed();
        $this->withoutVite();

        $draft = Event::query()->where('status', 'draft')->firstOrFail();

        $this->get(route('acara.show', $draft))->assertNotFound();
    }

    public function test_internal_event_route_resolves_for_authenticated_user(): void
    {
        $this->seed();

        $event = Event::query()->firstOrFail();

        $response = $this->actingAs($this->adminUser())->get(route('events.show', $event));

        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());

        $this->assertSame('pages/EventShowPage', $page['component']);
        $this->assertSame($event->title, $page['props']['event']['title']);
    }

    public function test_guest_cannot_access_internal_event_management(): void
    {
        $this->seed();

        $this->get(route('events.index'))->assertRedirect(route('login'));
    }

    private function adminUser(): User
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();

        return User::query()->where('role_id', $role->id)->firstOrFail();
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

    /**
     * @return array<int, string>
     */
    private function jsonLdTypes(string $jsonLd): array
    {
        $decoded = json_decode($jsonLd, true, 512, JSON_THROW_ON_ERROR);

        return collect($decoded['@graph'] ?? [])
            ->pluck('@type')
            ->filter()
            ->values()
            ->all();
    }
}
