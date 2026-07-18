<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApplicationTimezone
{
    public function apply(): string
    {
        $timezone = $this->resolve();

        config()->set('app.client_timezone', $timezone);
        config()->set('app.schedule_timezone', $timezone);

        return $timezone;
    }

    public function resolve(): string
    {
        $fallback = (string) config('app.default_timezone', 'Asia/Jakarta');

        try {
            $timezone = Schema::hasTable('settings')
                ? (string) Setting::get('app_timezone', $fallback)
                : $fallback;
        } catch (Throwable) {
            $timezone = $fallback;
        }

        return in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone
            : 'Asia/Jakarta';
    }
}
