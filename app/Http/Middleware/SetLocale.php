<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set application locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check URL query param if user switched language
        if ($request->has('lang')) {
            $lang = (string) $request->get('lang');
            if (in_array($lang, ['bn', 'en'], true)) {
                Session::put('locale', $lang);
            }
        }

        // 2. Check session locale
        $locale = Session::get('locale', config('app.locale', 'bn'));
        
        if (!in_array($locale, ['bn', 'en'], true)) {
            $locale = 'bn';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
