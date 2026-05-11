<?php

namespace App\Http\Middleware;

use App\Support\UserAccessGate;
use Closure;

class EnsureUserAccessControl
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Please sign in to continue.');
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        if (strpos((string) $routeName, 'registrar.') === 0) {
            $role = strtolower(trim((string) ($user->module ?: '')));
            if (!in_array($role, ['registrar', 'admin'], true)) {
                abort(403, 'Your role is not allowed to access the Registrar module.');
            }
        }

        foreach ([
            'student.' => ['student', 'admin'],
            'parent.' => ['parent', 'admin'],
            'applicant.' => ['applicant', 'admin'],
            'faculty.' => ['faculty', 'admin'],
        ] as $routePrefix => $allowedRoles) {
            if (strpos((string) $routeName, $routePrefix) === 0) {
                $role = strtolower(trim((string) ($user->module ?: '')));
                if (!in_array($role, $allowedRoles, true)) {
                    abort(403, 'Your role is not allowed to access this module.');
                }
            }
        }

        $moduleCode = UserAccessGate::routeModuleCode($routeName);
        if (!$moduleCode) {
            return $next($request);
        }

        $permissionCode = UserAccessGate::permissionForMethod($request->method());
        if (!UserAccessGate::allows($user, $moduleCode, $permissionCode)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
