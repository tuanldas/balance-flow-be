<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Set application locale based on Accept-Language header.
     * Falls back to default locale if header is missing or unsupported.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from Accept-Language header
        $locale = $request->header('Accept-Language');

        // Get supported locales from config
        $supportedLocales = config('app.supported_locales', ['en', 'vi']);

        // Validate and set locale
        if ($locale && in_array($locale, $supportedLocales, true)) {
            App::setLocale($locale);
        } else {
            // Use default locale from config
            App::setLocale(config('app.locale', 'vi'));
        }

        return $next($request);
    }
}
