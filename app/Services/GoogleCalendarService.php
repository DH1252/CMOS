<?php

namespace App\Services;

use App\Contracts\GoogleCalendarApi;
use App\Models\GoogleCalendarDeletion;
use App\Models\Timeline;
use Google\Service\Calendar\Event;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleCalendarService
{
    private ?string $lastError = null;

    private bool $deletionQueued = false;

    public function __construct(private GoogleCalendarApi $calendarApi) {}

    public function enabled(): bool
    {
        return (bool) config('services.google_calendar.enabled', false);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function deletionQueued(): bool
    {
        return $this->deletionQueued;
    }

    /**
     * @return array{event_id: string, calendar_id: string}|null
     */
    public function upsertTimelineEvent(Timeline $timeline): ?array
    {
        $this->clearLastError();

        if (! $this->enabled()) {
            return null;
        }

        try {
            $configuredCalendarId = $this->configuredCalendarId();

            $existingEventId = trim((string) $timeline->google_event_id);
            if ($existingEventId !== '') {
                try {
                    $calendarId = $this->timelineCalendarId($timeline, $configuredCalendarId);
                    $updated = $this->calendarApi->patchEvent(
                        $calendarId,
                        $existingEventId,
                        $this->buildTimelineEventData($timeline),
                    );

                    return [
                        'event_id' => $updated->getId() ?: $existingEventId,
                        'calendar_id' => $calendarId,
                    ];
                } catch (Throwable $exception) {
                    if (! in_array($this->statusCode($exception), [404, 410], true)) {
                        throw $exception;
                    }

                    $this->calendarApi->assertCalendarAccessible($calendarId);
                    $this->advanceGeneration($timeline);

                    Log::notice('Google Calendar event is missing; recreating it idempotently.', [
                        'timeline_id' => $timeline->id,
                        'google_event_id' => $timeline->google_event_id,
                        'google_calendar_id' => $calendarId,
                    ]);
                }
            }

            return $this->createTimelineEvent($timeline, $configuredCalendarId);
        } catch (Throwable $exception) {
            $this->setLastError($exception->getMessage());
            Log::error('Failed syncing timeline to Google Calendar.', [
                'timeline_id' => $timeline->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function deleteTimelineEvent(
        ?string $googleEventId,
        ?string $googleCalendarId = null,
        ?int $timelineId = null,
    ): bool {
        $this->clearLastError();

        return $this->deleteTimelineEventNow($googleEventId, $googleCalendarId, $timelineId);
    }

    /**
     * @param  iterable<int, Timeline>  $timelines
     */
    public function deleteTimelineEvents(iterable $timelines): bool
    {
        $this->clearLastError();

        foreach ($timelines as $timeline) {
            if (! $this->deleteTimelineEventNow(
                $timeline->google_event_id,
                $timeline->google_calendar_id,
                $timeline->id,
            )) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{processed: int, failed: int}
     */
    public function retryQueuedDeletions(): array
    {
        $processed = 0;
        $failed = 0;

        if (! $this->enabled()) {
            return compact('processed', 'failed');
        }

        GoogleCalendarDeletion::query()->eachById(function (GoogleCalendarDeletion $deletion) use (&$processed, &$failed): void {
            try {
                $this->calendarApi->deleteEvent($deletion->google_calendar_id, $deletion->google_event_id);
            } catch (Throwable $exception) {
                if (in_array($this->statusCode($exception), [404, 410], true)) {
                    try {
                        $this->calendarApi->assertCalendarAccessible($deletion->google_calendar_id);
                        $deletion->delete();
                        $processed++;

                        return;
                    } catch (Throwable $calendarException) {
                        $exception = $calendarException;
                    }
                }

                $deletion->updateQuietly([
                    'attempts' => $deletion->attempts + 1,
                    'last_error' => $this->cleanError($exception->getMessage()),
                    'last_attempt_at' => now(),
                ]);
                $failed++;

                return;
            }

            $deletion->delete();
            $processed++;
        });

        return compact('processed', 'failed');
    }

    private function clearLastError(): void
    {
        $this->lastError = null;
        $this->deletionQueued = false;
    }

    private function setLastError(string $message): void
    {
        $this->lastError = $this->cleanError($message);
    }

    private function cleanError(string $message): string
    {
        return mb_strimwidth(trim(str_replace(["\r", "\n"], ' ', $message)), 0, 250, '...');
    }

    private function deleteTimelineEventNow(
        ?string $googleEventId,
        ?string $googleCalendarId,
        ?int $timelineId,
    ): bool {
        $eventId = trim((string) $googleEventId);
        if ($eventId === '') {
            return true;
        }

        $calendarId = trim((string) $googleCalendarId);
        if ($calendarId === '') {
            try {
                $calendarId = $this->configuredCalendarId();
            } catch (Throwable $exception) {
                $this->setLastError($exception->getMessage());

                return false;
            }
        }

        if (! $this->enabled()) {
            $this->setLastError('Google Calendar synchronization is disabled.');

            return $this->queueDeletion($timelineId, $eventId, $calendarId);
        }

        try {
            $this->calendarApi->deleteEvent($calendarId, $eventId);

            return true;
        } catch (Throwable $exception) {
            if (in_array($this->statusCode($exception), [404, 410], true)) {
                try {
                    $this->calendarApi->assertCalendarAccessible($calendarId);

                    return true;
                } catch (Throwable $calendarException) {
                    $exception = $calendarException;
                }
            }

            $this->setLastError($exception->getMessage());
            Log::error('Failed deleting Google Calendar event.', [
                'timeline_id' => $timelineId,
                'google_event_id' => $eventId,
                'error' => $exception->getMessage(),
            ]);

            return $this->queueDeletion($timelineId, $eventId, $calendarId);
        }
    }

    private function queueDeletion(?int $timelineId, string $googleEventId, string $calendarId): bool
    {
        if (! $timelineId) {
            return false;
        }

        try {
            GoogleCalendarDeletion::query()->updateOrCreate(
                [
                    'google_event_id' => $googleEventId,
                    'google_calendar_id' => $calendarId,
                ],
                [
                    'timeline_id' => $timelineId,
                ],
            );
            $this->deletionQueued = true;

            return true;
        } catch (Throwable $exception) {
            Log::error('Failed queueing Google Calendar event deletion.', [
                'timeline_id' => $timelineId,
                'google_event_id' => $googleEventId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function advanceGeneration(Timeline $timeline): void
    {
        $timeline->forceFill([
            'google_event_id' => null,
            'google_calendar_id' => null,
            'google_sync_generation' => ((int) $timeline->google_sync_generation) + 1,
        ]);

        if ($timeline->exists) {
            $timeline->saveQuietly();
        }
    }

    private function configuredCalendarId(): string
    {
        $calendarId = trim((string) config('services.google_calendar.calendar_id'));

        if ($calendarId === '') {
            throw new RuntimeException('Google Calendar ID is not configured.');
        }

        return $calendarId;
    }

    private function timelineCalendarId(Timeline $timeline, string $fallback): string
    {
        $calendarId = trim((string) $timeline->google_calendar_id);

        return $calendarId !== '' ? $calendarId : $fallback;
    }

    /**
     * @return array{event_id: string, calendar_id: string}
     */
    private function createTimelineEvent(Timeline $timeline, string $calendarId): array
    {
        $eventId = $this->deterministicEventId($timeline);
        $this->persistProvisionalMapping($timeline, $eventId, $calendarId);

        try {
            $created = $this->calendarApi->insertEvent(
                $calendarId,
                $this->buildTimelineEventData($timeline, $eventId),
            );
        } catch (Throwable $exception) {
            if ($this->statusCode($exception) !== 409) {
                throw $exception;
            }

            $created = $this->calendarApi->patchEvent(
                $calendarId,
                $eventId,
                $this->buildTimelineEventData($timeline),
            );
        }

        return [
            'event_id' => $created->getId() ?: $eventId,
            'calendar_id' => $calendarId,
        ];
    }

    private function persistProvisionalMapping(Timeline $timeline, string $eventId, string $calendarId): void
    {
        $timeline->forceFill([
            'google_event_id' => $eventId,
            'google_calendar_id' => $calendarId,
        ]);

        if ($timeline->exists && $timeline->isDirty()) {
            $timeline->saveQuietly();
        }
    }

    private function deterministicEventId(Timeline $timeline): string
    {
        $generation = (int) ($timeline->google_sync_generation ?? 0);

        return hash('sha256', "timeline|{$timeline->getKey()}|{$generation}");
    }

    private function statusCode(Throwable $exception): int
    {
        return (int) $exception->getCode();
    }

    private function buildTimelineEventData(Timeline $timeline, ?string $eventId = null): Event
    {
        $descriptionLines = [];

        if ($timeline->description) {
            $descriptionLines[] = $timeline->description;
            $descriptionLines[] = '';
        }

        $descriptionLines[] = 'Sumber: CMOS Timeline';
        $descriptionLines[] = 'Tipe: '.ucfirst($timeline->type);

        if ($timeline->department?->name) {
            $descriptionLines[] = 'Departemen: '.$timeline->department->name;
        }

        if ($timeline->program?->name) {
            $descriptionLines[] = 'Program: '.$timeline->program->name;
        }

        $payload = [
            'summary' => $timeline->title,
            'description' => implode("\n", $descriptionLines),
            'start' => [
                'date' => $timeline->start_date->format('Y-m-d'),
            ],
            'end' => [
                'date' => $timeline->end_date->copy()->addDay()->format('Y-m-d'),
            ],
        ];

        if ($eventId) {
            $payload['id'] = $eventId;
        }

        return new Event($payload);
    }
}
