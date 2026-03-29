<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureStudentModule
{
    /**
     * Ensure the authenticated user is a student module user.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->module !== 'student' || is_null($user->student_id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('module.login', ['module' => 'student'])
                ->withErrors(['username' => 'Please login with a student account.']);
        }

        return $next($request);
    }
}
