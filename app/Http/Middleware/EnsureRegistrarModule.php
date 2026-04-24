<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureRegistrarModule
{
    /**
     * Ensure the authenticated user can access registrar pages.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $module = (string) ($user->module ?? '');
        $hasRegistrarLink = !is_null($user->registrar_id ?? null);
        $isRegistrarUser = $module === 'registrar' && $hasRegistrarLink;
        $isAdminUser = $module === 'admin';

        if (!$user || (!$isRegistrarUser && !$isAdminUser)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('module.login', ['module' => 'registrar'])
                ->withErrors(['username' => 'Please login with a registrar account.']);
        }

        return $next($request);
    }
}