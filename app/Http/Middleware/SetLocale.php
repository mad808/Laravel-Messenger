<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale') ?? $request->cookie('locale') ?? config('app.locale');
        
        if (in_array($locale, ['en', 'tk', 'ru'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}