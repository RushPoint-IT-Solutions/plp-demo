<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->force_password_reset) {
            // Prevent infinite redirect loops by ensuring they aren't already on the reset route
            if (!$request->routeIs('password.first_reset') && !$request->routeIs('password.first_reset.update')) {
                return redirect()->route('password.first_reset');
            }
        }

        return $next($request);
    }
}
