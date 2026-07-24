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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'ar'];
        $locale = null;

        // Web portals (Admin/Client): explicit choice persisted via the /lang/{locale} switcher.
        if ($request->hasSession() && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        // Stateless API/mobile clients (Sanctum tokens) have no session — read Accept-Language instead.
        if (! $locale && $request->headers->has('Accept-Language')) {
            $preferred = substr(trim(explode(',', $request->header('Accept-Language'))[0]), 0, 2);
            $locale = strtolower($preferred);
        }

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
