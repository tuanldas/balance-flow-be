<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy locale từ header Accept-Language hoặc query parameter
        $locale = $request->header('Accept-Language') 
            ?? $request->query('locale') 
            ?? config('app.locale', 'vi');

        // Chỉ cho phép các locale được hỗ trợ
        $supportedLocales = ['vi', 'en'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.locale', 'vi');
        }

        // Set locale cho ứng dụng
        App::setLocale($locale);

        return $next($request);
    }
}