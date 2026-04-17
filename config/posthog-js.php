<?php

return [
    'key' => env('VITE_POSTHOG_KEY', env('POSTHOG_PROJECT_TOKEN', '')),
    'host' => env('VITE_POSTHOG_HOST', env('POSTHOG_HOST', 'https://app.posthog.com')),
    'disabled' => env('VITE_POSTHOG_DISABLED', env('POSTHOG_DISABLED', false)),
];
