<?php

namespace App\Support;

use App\Student;
use App\User;
use Illuminate\Support\Facades\Schema;

/**
 * Restricts a registrar user to only the course(s) assigned via
 * user_course_scopes. A user with no scope rows is unrestricted
 * (preserves existing behavior for ordinary registrar accounts).
 */
class CourseScopeGate
{
    public static function isScoped(User $user): bool
    {
        return self::allowedCourseIds($user) !== null;
    }

    /**
     * @return int[]|null Null when the user is unrestricted.
     */
    public static function allowedCourseIds(User $user): ?array
    {
        if (!Schema::hasTable('user_course_scopes')) {
            return null;
        }

        $ids = $user->courseScopes()->pluck('course_id')->map(function ($id) {
            return (int) $id;
        })->all();

        return empty($ids) ? null : $ids;
    }

    public static function canAccessCourseId(?User $user, $courseId): bool
    {
        if (!$user) {
            return false;
        }

        $allowed = self::allowedCourseIds($user);
        if ($allowed === null) {
            return true;
        }

        return in_array((int) $courseId, $allowed, true);
    }

    public static function canAccessStudent(?User $user, ?Student $student): bool
    {
        if (!$user || !$student) {
            return false;
        }

        return self::canAccessCourseId($user, $student->course_id);
    }
}
