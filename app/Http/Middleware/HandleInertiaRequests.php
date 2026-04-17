<?php

namespace App\Http\Middleware;

use App\Support\AuthShellData;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly AuthShellData $authShellData) {}

    public function handle(Request $request, Closure $next): Response
    {
        config(['inertia.ssr.enabled' => true]);

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
        return [
            ...parent::share($request),
            'shell' => fn () => $this->authShellData->forRequest($request),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
