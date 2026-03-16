<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestTimingMiddleware
{
    // Umbral en milisegundos para loggear como "lento"
    const SLOW_THRESHOLD_MS = 300;

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        $context = [
            'method'   => $request->method(),
            'uri'      => $request->getRequestUri(),
            'status'   => $response->getStatusCode(),
            'duration' => $durationMs . 'ms',
            'ip'       => $request->ip(),
        ];

        if ($durationMs >= self::SLOW_THRESHOLD_MS) {
            Log::channel('api_timing')->warning('SLOW REQUEST', $context);
        } else {
            Log::channel('api_timing')->info('request', $context);
        }

        $response->headers->set('X-Response-Time', $durationMs . 'ms');

        return $response;
    }
}
