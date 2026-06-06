<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();

        return User::query()->where('role_id', $role->id)->firstOrFail();
    }

    public function test_admin_sees_correct_event_counts(): void
    {
        $this->seed();
        $response = $this->actingAs($this->adminUser())->get(route('events.index'));
        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());

        // pluck('label', 'value') → keyed by numeric value, so get('4') = 'Total Acara', get('3') = 'Published', get('2') = 'Mendatang'.
        $stats = collect($page['props']['stats'] ?? [])->pluck('label', 'value');

        $this->assertSame('Total', $stats->get('4'));
        $this->assertSame('Published', $stats->get('3'));
        $this->assertSame('Mendatang', $stats->get('2'));
    }

    public function test_status_filter_shows_draft_only(): void
    {
        $this->seed();
        $this->actingAs($this->adminUser());

        $response = $this->get(route('events.index').'?status=draft');
        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());
        $titles = collect($page['props']['events'] ?? [])->pluck('title');

        $this->assertTrue($titles->contains('Company Visit (Draft)'));
        $this->assertFalse($titles->contains('LKMM TD 2026'));
    }

    public function test_status_filter_shows_published_only(): void
    {
        $this->seed();
        $this->actingAs($this->adminUser());

        $response = $this->get(route('events.index').'?status=published');
        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());
        $titles = collect($page['props']['events'] ?? [])->pluck('title');

        $this->assertTrue($titles->contains('LKMM TD 2026'));
        $this->assertTrue($titles->contains('TEKKOM Insight Night'));
        $this->assertFalse($titles->contains('Company Visit (Draft)'));
    }

    public function test_admin_sees_all_events_including_draft(): void
    {
        $this->seed();

        $response = $this->actingAs($this->adminUser())->get(route('events.index'));
        $response->assertOk();

        $page = $this->inertiaPage($response->getContent());
        $titles = collect($page['props']['events'] ?? [])->pluck('title');

        $this->assertCount(4, $titles);
        $this->assertTrue($titles->contains('Company Visit (Draft)'));
        $this->assertTrue($titles->contains('Open Recruitment Staff 2026 (Selesai)'));
    }

    public function test_non_admin_can_see_event_management_pages(): void
    {
        $this->seed();

        $staffRole = Role::query()->where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create(['role_id' => $staffRole->id]);

        // All authenticated users can view the management UI. Authorization gates edit/update/destroy.
        $this->actingAs($staff)->get(route('events.index'))->assertOk();
        $this->actingAs($staff)->get(route('events.create'))->assertOk();
    }

    public function test_non_admin_cannot_edit_other_users_events(): void
    {
        $this->seed();

        // Draft event owned by admin.
        $draft = Event::query()->where('status', 'draft')->firstOrFail();
        $staffRole = Role::query()->where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create(['role_id' => $staffRole->id]);

        $this->actingAs($staff)->get(route('events.edit', $draft))->assertForbidden();
        $this->actingAs($staff)->put(route('events.update', $draft), [
            'title' => 'Hacked title',
            'description' => '<p>Hacked</p>',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'status' => 'draft',
        ])->assertForbidden();
        $this->actingAs($staff)->delete(route('events.destroy', $draft))->assertForbidden();
    }

    public function test_staff_can_manage_own_events(): void
    {
        $this->seed();

        $staffRole = Role::query()->where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create(['role_id' => $staffRole->id]);

        $event = Event::factory()->create([
            'user_id' => $staff->id,
            'title' => 'Staff Own Event',
            'status' => 'draft',
            'description' => '<p>My event</p>',
            'starts_at' => now()->addMonth()->setTimezone('UTC'),
        ]);

        $this->actingAs($staff)->get(route('events.edit', $event))->assertOk();
        $startsAtLocal = $event->startsAtLocal?->format('Y-m-d\TH:i') ?? now()->addMonth()->format('Y-m-d\TH:i');
        $this->actingAs($staff)->put(route('events.update', $event), [
            'title' => 'Staff Own Event Updated',
            'description' => '<p>Updated description</p>',
            'starts_at' => $startsAtLocal,
            'status' => 'draft',
        ])->assertRedirect();
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
