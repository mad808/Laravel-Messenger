<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_blocked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'phone' => __('Your account has been blocked.'),
            ]);
        }

        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'moderator'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}