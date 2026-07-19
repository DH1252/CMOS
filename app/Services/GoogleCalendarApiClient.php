<?php

namespace App\Services;

use App\Contracts\GoogleCalendarApi;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use RuntimeException;

class GoogleCalendarApiClient implements GoogleCalendarApi
{
    private ?Calendar $calendar = null;

    public function insertEvent(string $calendarId, Event $event): Event
    {
        return $this->calendar()->events->insert($calendarId, $event);
    }

    public function patchEvent(string $calendarId, string $eventId, Event $event): Event
    {
        return $this->calendar()->events->patch($calendarId, $eventId, $event);
    }

    public function deleteEvent(string $calendarId, string $eventId): void
    {
        $this->calendar()->events->delete($calendarId, $eventId);
    }

    public function assertCalendarAccessible(string $calendarId): void
    {
        $this->calendar()->events->listEvents($calendarId, [
            'maxResults' => 1,
            'showDeleted' => false,
        ]);
    }

    private function calendar(): Calendar
    {
        if ($this->calendar) {
            return $this->calendar;
        }

        $credentialsPath = trim((string) config('services.google_calendar.service_account_json'));

        if ($credentialsPath === '') {
            throw new RuntimeException('Google service account credential path is not configured.');
        }

        if (! str_starts_with($credentialsPath, DIRECTORY_SEPARATOR)) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (! is_file($credentialsPath)) {
            throw new RuntimeException("Google service account JSON file not found at: {$credentialsPath}");
        }

        $client = new Client;
        $client->setApplicationName((string) config('services.google_calendar.application_name', 'CMOS'));
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([Calendar::CALENDAR_EVENTS]);

        $impersonateUser = trim((string) config('services.google_calendar.impersonate_user'));
        if ($impersonateUser !== '') {
            $client->setSubject($impersonateUser);
        }

        return $this->calendar = new Calendar($client);
    }
}
