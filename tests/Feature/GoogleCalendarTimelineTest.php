<?php

namespace Tests\Feature;

use App\Contracts\GoogleCalendarApi;
use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use App\Models\Timeline;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class GoogleCalendarTimelineTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function test_timeline_remains_when_google_delete_fails(): void
    {
        config()->set('services.google_calendar.enabled', true);
        [$admin, $timeline] = $this->timelineFixture();

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('deleteEvent')
            ->once()
            ->andThrow(new RuntimeException('Google unavailable', 503));
        $this->app->instance(GoogleCalendarApi::class, $api);

        $response = $this->actingAs($admin)->delete(route('timelines.destroy', $timeline));

        $response->assertRedirect(route('timelines.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
        $this->assertDatabaseHas('google_calendar_deletions', [
            'google_event_id' => 'google-event',
            'google_calendar_id' => 'calendar@example.com',
        ]);
    }

    public function test_program_delete_cleans_mapped_google_events_before_cascade(): void
    {
        config()->set('services.google_calendar.enabled', true);
        [$admin, $timeline] = $this->timelineFixture(withProgram: true);

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('deleteEvent')
            ->once()
            ->with('calendar@example.com', 'google-event')
            ->andReturnNull();
        $this->app->instance(GoogleCalendarApi::class, $api);

        $response = $this->actingAs($admin)->delete(route('programs.destroy', $timeline->program));

        $response->assertRedirect(route('programs.index'));
        $this->assertDatabaseMissing('programs', ['id' => $timeline->program_id]);
        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
    }

    public function test_department_delete_cleans_direct_google_events_before_cascade(): void
    {
        config()->set('services.google_calendar.enabled', true);
        [$admin, $timeline] = $this->timelineFixture(withProgram: false, type: 'department');

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('deleteEvent')
            ->once()
            ->with('calendar@example.com', 'google-event')
            ->andReturnNull();
        $this->app->instance(GoogleCalendarApi::class, $api);

        $response = $this->actingAs($admin)->delete(route('departments.destroy', $timeline->department));

        $response->assertRedirect(route('departments.index'));
        $this->assertDatabaseMissing('departments', ['id' => $timeline->department_id]);
        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
    }

    public function test_reconciliation_command_persists_calendar_mapping(): void
    {
        config()->set('services.google_calendar.enabled', true);
        [, $timeline] = $this->timelineFixture();

        $service = Mockery::mock(GoogleCalendarService::class);
        $service->shouldReceive('enabled')->once()->andReturn(true);
        $service->shouldReceive('retryQueuedDeletions')->once()->andReturn([
            'processed' => 0,
            'failed' => 0,
        ]);
        $service->shouldReceive('upsertTimelineEvent')->once()->andReturn([
            'event_id' => 'reconciled-event',
            'calendar_id' => 'calendar@example.com',
        ]);
        $this->app->instance(GoogleCalendarService::class, $service);

        $this->artisan('google-calendar:sync')
            ->assertExitCode(0)
            ->expectsOutput('Sinkronisasi selesai: 1 berhasil, 0 gagal.');

        $this->assertDatabaseHas('timelines', [
            'id' => $timeline->id,
            'google_event_id' => 'reconciled-event',
            'google_calendar_id' => 'calendar@example.com',
        ]);
    }

    public function test_reconciliation_command_fails_when_queued_deletion_retry_fails(): void
    {
        config()->set('services.google_calendar.enabled', true);

        $service = Mockery::mock(GoogleCalendarService::class);
        $service->shouldReceive('enabled')->once()->andReturn(true);
        $service->shouldReceive('retryQueuedDeletions')->once()->andReturn([
            'processed' => 0,
            'failed' => 1,
        ]);
        $this->app->instance(GoogleCalendarService::class, $service);

        $this->artisan('google-calendar:sync')
            ->expectsOutput('Penghapusan tertunda: 0 berhasil, 1 gagal.')
            ->expectsOutput('Sinkronisasi selesai: 0 berhasil, 0 gagal.')
            ->assertExitCode(1);
    }

    /**
     * @return array{0: User, 1: Timeline}
     */
    private function timelineFixture(bool $withProgram = false, string $type = 'global'): array
    {
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $department = Department::create([
            'name' => 'Calendar Department',
            'description' => 'Department for calendar tests.',
            'status' => 'active',
        ]);
        $admin = User::factory()->createOne([
            'role_id' => $adminRole->id,
            'department_id' => null,
            'status' => 'active',
        ]);

        $program = null;
        if ($withProgram) {
            $program = Program::create([
                'name' => 'Calendar Program',
                'description' => 'Program for calendar tests.',
                'department_id' => $department->id,
                'created_by' => $admin->id,
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-21',
                'status' => 'active',
            ]);
        }

        $timeline = Timeline::create([
            'title' => 'Calendar timeline',
            'description' => 'Timeline for calendar tests.',
            'type' => $type === 'department' ? 'department' : ($withProgram ? 'program' : 'global'),
            'department_id' => $type === 'department' ? $department->id : null,
            'program_id' => $program?->id,
            'start_date' => '2026-07-20',
            'end_date' => '2026-07-21',
            'color' => '#7C3AED',
            'google_event_id' => 'google-event',
            'google_calendar_id' => 'calendar@example.com',
        ]);

        return [$admin, $timeline->fresh(['department', 'program'])];
    }
}
