<?php

namespace App\Http\Middleware;

use App\Support\ApplicationTimezone;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyApplicationTimezone
{
    public function __construct(private readonly ApplicationTimezone $applicationTimezone) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->applicationTimezone->apply();

        return $next($request);
    }
}
