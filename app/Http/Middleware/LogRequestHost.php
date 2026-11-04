<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestHost
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('HOST_DIAGNOSTIC', [
            'host_header' => $request->headers->get('host'),
            'request_host' => $request->getHost(),
            'app_url_host' => parse_url(
                (string) config('app.url'),
                PHP_URL_HOST
            ),
            'environment' => app()->environment(),
        ]);

        return $next($request);
    }
}
