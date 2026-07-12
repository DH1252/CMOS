<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Illuminate\Support\Facades\Log;

class LogVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $visitor = Visitor::create([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info("Visitor logged - IP: {$visitor->ip_address}, URL: {$visitor->url}");
            error_log("Visitor logged - IP: {$visitor->ip_address}, URL: {$visitor->url}");
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }

        return $next($request);
    }
}
