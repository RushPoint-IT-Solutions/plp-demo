<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureApplicantModule
{
    /**
     * Ensure the authenticated user is an applicant module user.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->module !== 'applicant' || is_null($user->applicant_id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('module.login', ['module' => 'applicant'])
                ->withErrors(['username' => 'Please login with an applicant account.']);
        }

        return $next($request);
    }
}
