<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\AuthShellData;
use App\Support\ThemePalette;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly AuthShellData $authShellData) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('images.optimize')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    public function rootView(Request $request): string
    {
        if ($request->routeIs('home') || $request->routeIs('informasi.*')) {
            return 'public-app';
        }

        return $this->rootView;
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $themeSettings = Setting::query()
            ->whereIn('key', array_merge(['theme_color'], ThemePalette::settingKeys(), ThemePalette::cssVariableKeys()))
            ->pluck('value', 'key')
            ->all();
        $themePayload = ThemePalette::payloadFromSettings($themeSettings);

        return [
            ...parent::share($request),
            'shell' => fn () => $this->authShellData->forRequest($request),
            'theme' => [
                'color' => $themePayload['color'],
                'variables' => $themePayload['variables'],
                'customCss' => $themePayload['customCss'],
            ],
            'posthog' => [
                'key' => (string) config('posthog-js.key', ''),
                'host' => (string) config('posthog-js.host', 'https://app.posthog.com'),
                'disabled' => (bool) config('posthog-js.disabled', false),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
