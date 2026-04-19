<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureParentModule
{
    /**
     * Ensure the authenticated user is a parent module user.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->module !== 'parent' || is_null($user->parent_id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('module.login', ['module' => 'parent'])
                ->withErrors(['username' => 'Please login with a parent account.']);
        }

        return $next($request);
    }
}
