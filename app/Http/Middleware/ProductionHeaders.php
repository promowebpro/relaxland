<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductionHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        if (! config('seo.indexing_enabled') || $request->is('admin', 'admin/*', 'api', 'api/*', 'up') || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if (config('operations.hsts_enabled') && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age='.(int) config('operations.hsts_max_age', 31536000).'; includeSubDomains',
            );
        }

        return $response;
    }
}
