<?php

namespace App\Contracts;

use Google\Service\Calendar\Event;

interface GoogleCalendarApi
{
    public function insertEvent(string $calendarId, Event $event): Event;

    public function patchEvent(string $calendarId, string $eventId, Event $event): Event;

    public function deleteEvent(string $calendarId, string $eventId): void;

    public function assertCalendarAccessible(string $calendarId): void;
}
