<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('posthog.disabled')) {
            return;
        }

        $apiKey = (string) config('posthog.api_key', '');

        if ($apiKey === '') {
            return;
        }

        $postHogClass = 'PostHog\\PostHog';

        if (! class_exists($postHogClass)) {
            Log::warning('PostHog package is not available; analytics disabled.');

            return;
        }

        $options = [];
        $host = config('posthog.host');

        if (is_string($host) && trim($host) !== '') {
            $options['host'] = $host;
        }

        try {
            $postHogClass::init($apiKey, $options);
        } catch (Throwable $exception) {
            Log::warning('PostHog initialization failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
