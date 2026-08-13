<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureLeadAttribution
{
    private const PARAMETERS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::PARAMETERS as $parameter) {
            $value = $request->query($parameter);

            if (! $request->session()->has("lead_attribution.{$parameter}") && is_string($value) && filled(trim($value))) {
                $request->session()->put("lead_attribution.{$parameter}", mb_substr(trim($value), 0, 255));
            }
        }

        return $next($request);
    }
}
