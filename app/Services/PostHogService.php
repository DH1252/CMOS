<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class PostHogService
{
    private string $postHogClass;

    public function __construct()
    {
        $this->postHogClass = 'PostHog\\PostHog';
    }

    public function capture(string $distinctId, string $event, array $properties = []): void
    {
        if ($this->isDisabled()) {
            return;
        }

        try {
            $this->postHogClass::capture([
                'distinctId' => $distinctId,
                'event' => $event,
                'properties' => $properties,
            ]);
        } catch (Throwable $exception) {
            Log::warning('PostHog capture failed', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function identify(string $distinctId, array $properties = []): void
    {
        if ($this->isDisabled()) {
            return;
        }

        try {
            $this->postHogClass::identify([
                'distinctId' => $distinctId,
                'properties' => $properties,
            ]);
        } catch (Throwable $exception) {
            Log::warning('PostHog identify failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function isDisabled(): bool
    {
        if (config('posthog.disabled')) {
            return true;
        }

        return ! class_exists($this->postHogClass);
    }
}
