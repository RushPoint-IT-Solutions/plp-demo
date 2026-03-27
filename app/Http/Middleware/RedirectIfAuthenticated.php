<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();

            if ($user) {
                switch ($user->module) {
                    case 'registrar':
                        return redirect()->route('registrar.dashboard');
                    case 'faculty':
                        return redirect()->route('faculty.load');
                    case 'applicant':
                        return redirect()->route('applicant.application-form');
                    case 'student':
                    default:
                        return redirect()->route('student.access-module');
                }
            }

            return redirect('/home');
        }

        return $next($request);
    }
}
