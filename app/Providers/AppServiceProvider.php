<?php

namespace App\Providers;

use App\Contracts\GoogleCalendarApi;
use App\Services\GoogleCalendarApiClient;
use App\Support\ApplicationTimezone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleCalendarApi::class, GoogleCalendarApiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(ApplicationTimezone::class)->apply();

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by(Str::transliterate($email).'|'.$request->ip());
        });

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
