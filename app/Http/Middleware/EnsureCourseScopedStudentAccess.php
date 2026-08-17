<?php

namespace App\Http\Middleware;

use App\Student;
use App\Support\CourseScopeGate;
use Closure;

class EnsureCourseScopedStudentAccess
{
    public function handle($request, Closure $next)
    {
        $student = $request->route('student');

        if ($student instanceof Student) {
            $user = $request->user();
            if (!CourseScopeGate::canAccessStudent($user, $student)) {
                abort(403, 'You do not have access to this student\'s records.');
            }
        }

        return $next($request);
    }
}
