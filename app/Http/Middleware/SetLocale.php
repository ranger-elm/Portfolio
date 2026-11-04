<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = array_keys(config('portfolio.locales'));

        if (in_array($request->segment(1), $locales, true)) {
            app()->setLocale($request->segment(1));
        }

        return $next($request);
    }
}
