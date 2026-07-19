<?php

namespace Tests\Unit;

use App\Contracts\GoogleCalendarApi;
use App\Models\Timeline;
use App\Services\GoogleCalendarService;
use Google\Service\Calendar\Event;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class GoogleCalendarServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_update_failure_does_not_create_a_duplicate_event(): void
    {
        config()->set('services.google_calendar.enabled', true);
        config()->set('services.google_calendar.calendar_id', 'calendar@example.com');

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('patchEvent')
            ->once()
            ->andThrow(new RuntimeException('temporary failure', 500));
        $api->shouldReceive('insertEvent')->never();

        $result = (new GoogleCalendarService($api))->upsertTimelineEvent($this->timeline('google-event'));

        $this->assertNull($result);
    }

    public function test_missing_event_is_recreated_with_a_deterministic_id(): void
    {
        config()->set('services.google_calendar.enabled', true);
        config()->set('services.google_calendar.calendar_id', 'calendar@example.com');

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('patchEvent')
            ->once()
            ->andThrow(new RuntimeException('not found', 404));
        $api->shouldReceive('assertCalendarAccessible')
            ->once()
            ->with('old@example.com')
            ->andReturnNull();
        $api->shouldReceive('insertEvent')
            ->once()
            ->withArgs(function (string $calendarId, Event $event): bool {
                return $calendarId === 'calendar@example.com'
                    && $event->getId() !== null
                    && preg_match('/^[a-v0-9]{5,1024}$/', $event->getId()) === 1;
            })
            ->andReturnUsing(fn (string $calendarId, Event $event): Event => new Event(['id' => $event->getId()]));

        $timeline = $this->timeline('old-event');
        $result = (new GoogleCalendarService($api))->upsertTimelineEvent($timeline);

        $this->assertSame('calendar@example.com', $result['calendar_id']);
        $this->assertMatchesRegularExpression('/^[a-v0-9]{5,1024}$/', $result['event_id']);
        $this->assertNotSame('old-event', $result['event_id']);
        $this->assertSame($result['event_id'], $timeline->google_event_id);
        $this->assertSame($result['calendar_id'], $timeline->google_calendar_id);
        $this->assertSame(1, $timeline->google_sync_generation);
    }

    public function test_missing_event_retry_reuses_the_same_replacement_id_after_an_ambiguous_insert(): void
    {
        config()->set('services.google_calendar.enabled', true);
        config()->set('services.google_calendar.calendar_id', 'calendar@example.com');

        $insertedIds = [];
        $patchedIds = [];
        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('patchEvent')
            ->twice()
            ->andReturnUsing(function (string $calendarId, string $eventId, Event $event) use (&$patchedIds): Event {
                $patchedIds[] = $eventId;

                if (count($patchedIds) === 1) {
                    throw new RuntimeException('not found', 404);
                }

                return new Event(['id' => $eventId]);
            });
        $api->shouldReceive('assertCalendarAccessible')->once()->andReturnNull();
        $api->shouldReceive('insertEvent')
            ->once()
            ->andReturnUsing(function (string $calendarId, Event $event) use (&$insertedIds): Event {
                $insertedIds[] = $event->getId();
                throw new RuntimeException('response lost', 503);
            });

        $timeline = $this->timeline('old-event');
        $service = new GoogleCalendarService($api);

        $this->assertNull($service->upsertTimelineEvent($timeline));
        $this->assertNotNull($service->upsertTimelineEvent($timeline));
        $this->assertCount(1, $insertedIds);
        $this->assertSame($insertedIds[0], $timeline->google_event_id);
        $this->assertSame($insertedIds[0], $patchedIds[1]);
    }

    public function test_conflicting_insert_recovers_by_patching_the_same_event_id(): void
    {
        config()->set('services.google_calendar.enabled', true);
        config()->set('services.google_calendar.calendar_id', 'calendar@example.com');

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('insertEvent')
            ->once()
            ->andThrow(new RuntimeException('already exists', 409));
        $api->shouldReceive('patchEvent')
            ->once()
            ->withArgs(fn (string $calendarId, string $eventId, Event $event): bool => $calendarId === 'calendar@example.com'
                && $eventId !== ''
                && $event->getId() === null)
            ->andReturnUsing(fn (string $calendarId, string $eventId, Event $event): Event => new Event(['id' => $eventId]));

        $result = (new GoogleCalendarService($api))->upsertTimelineEvent($this->timeline());

        $this->assertNotNull($result);
        $this->assertSame($result['event_id'], $this->deterministicEventId($this->timeline()));
    }

    public function test_already_deleted_event_is_treated_as_successful_deletion(): void
    {
        config()->set('services.google_calendar.enabled', true);

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('deleteEvent')
            ->once()
            ->andThrow(new RuntimeException('gone', 410));
        $api->shouldReceive('assertCalendarAccessible')
            ->once()
            ->with('calendar@example.com')
            ->andReturnNull();

        $deleted = (new GoogleCalendarService($api))->deleteTimelineEvent(
            'google-event',
            'calendar@example.com',
            10,
        );

        $this->assertTrue($deleted);
    }

    public function test_deletion_is_blocked_when_synchronization_is_disabled(): void
    {
        config()->set('services.google_calendar.enabled', false);

        $api = Mockery::mock(GoogleCalendarApi::class);
        $api->shouldReceive('deleteEvent')->never();

        $deleted = (new GoogleCalendarService($api))->deleteTimelineEvent('google-event');

        $this->assertFalse($deleted);
    }

    private function timeline(?string $googleEventId = null): Timeline
    {
        $timeline = new Timeline;
        $timeline->setRawAttributes([
            'id' => 10,
            'title' => 'Planning agenda',
            'description' => 'A timeline used by the test suite.',
            'type' => 'global',
            'start_date' => '2026-07-20',
            'end_date' => '2026-07-21',
            'google_event_id' => $googleEventId,
            'google_calendar_id' => $googleEventId ? 'old@example.com' : null,
            'updated_at' => '2026-07-18 12:00:00',
        ]);
        $timeline->setRelation('department', null);
        $timeline->setRelation('program', null);

        return $timeline;
    }

    private function deterministicEventId(Timeline $timeline): string
    {
        $version = $timeline->getRawOriginal('updated_at') ?? 'unsaved';

        return hash('sha256', "timeline|{$timeline->getKey()}|0");
    }
}
