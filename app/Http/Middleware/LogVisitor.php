<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogVisitor
{
    /**
     * Public-facing route patterns that should be tracked.
     */
    private const PUBLIC_ROUTE_PATTERNS = [
        'home',
        'informasi.*',
        'acara.*',
        'tentang',
        'departemen',
        'departemen.overview',
        'kompetisi',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldLog($request)) {
            $this->log($request);
        }

        return $next($request);
    }

    /**
     * Only log unique visitors (one per IP per day) on public-facing pages.
     */
    private function shouldLog(Request $request): bool
    {
        if (! $request->isMethod('get')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        return $request->routeIs(...self::PUBLIC_ROUTE_PATTERNS);
    }

    private function log(Request $request): void
    {
        try {
            $ip = $this->resolveClientIp($request);

            if (! $ip) {
                return;
            }

            $tz = (string) config('app.client_timezone', 'Asia/Jakarta');
            $dayStart = now($tz)->startOfDay()->setTimezone('UTC');
            $dayEnd = $dayStart->copy()->addDay();

            $alreadyLogged = Visitor::query()
                ->where('ip_address', $ip)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->exists();

            if ($alreadyLogged) {
                return;
            }

            $visitor = Visitor::create([
                'ip_address' => $ip,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info("Unique visitor logged - IP: {$visitor->ip_address}, URL: {$visitor->url}");
        } catch (\Exception $e) {
            Log::warning("Failed to log visitor: {$e->getMessage()}");
        }
    }

    /**
     * Resolve the real client IP behind Docker proxies.
     *
     * Laravel's TrustProxies may return the Docker bridge IP when the
     * upstream proxy (Coolify Traefik) doesn't send X-Forwarded-For.
     * This checks Cloudflare and X-Forwarded-For directly for a public
     * IP before falling back to $request->ip().
     */
    private function resolveClientIp(Request $request): ?string
    {
        $cfIp = $request->headers->get('CF-Connecting-IP');
        if (filter_var($cfIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV | FILTER_FLAG_NO_RES)) {
            return $cfIp;
        }

        $forwardedFor = $request->headers->get('X-Forwarded-For', '');
        foreach (explode(',', $forwardedFor) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV | FILTER_FLAG_NO_RES)) {
                return $candidate;
            }
        }

        $realIp = $request->headers->get('X-Real-IP');
        if (filter_var($realIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV | FILTER_FLAG_NO_RES)) {
            return $realIp;
        }

        return $request->ip();
    }
}
