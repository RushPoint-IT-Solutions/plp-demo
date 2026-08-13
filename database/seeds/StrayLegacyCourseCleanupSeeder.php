<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrayLegacyCourseCleanupSeeder extends Seeder
{
    /**
     * Removes placeholder courses (BSCE, BSENTREP, BSIS) that get created by
     * older data-backfill migrations (2026_04_09_000330 and 2026_04_09_000340)
     * when the courses table is still empty at migrate time, along with every
     * row that ends up attached to them: curricula, curriculum subjects,
     * subjects, and any student_subject / student_subject_grades links that
     * later seeders attach to real students.
     *
     * Run with: php artisan db:seed --class=StrayLegacyCourseCleanupSeeder
     *
     * @return void
     */
    private const STRAY_COURSE_CODES = ['BSCE', 'BSENTREP', 'BSIS'];

    public function run()
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        $courseIds = DB::table('courses')
            ->whereIn('code', self::STRAY_COURSE_CODES)
            ->pluck('id');

        if ($courseIds->isEmpty()) {
            $this->command->info('StrayLegacyCourseCleanupSeeder: no stray courses found.');
            return;
        }

        $subjectIds = Schema::hasTable('subjects')
            ? DB::table('subjects')->whereIn('course_id', $courseIds)->pluck('id')
            : collect();

        $studentSubjectLinks = 0;
        $gradeLinks = 0;

        if (Schema::hasTable('student_subject_grades') && $subjectIds->isNotEmpty()) {
            $gradeLinks = DB::table('student_subject_grades')->whereIn('subject_id', $subjectIds)->delete();
        }

        if (Schema::hasTable('student_subject') && $subjectIds->isNotEmpty()) {
            $studentSubjectLinks = DB::table('student_subject')->whereIn('subject_id', $subjectIds)->delete();
        }

        $curriculumIds = Schema::hasTable('course_curricula')
            ? DB::table('course_curricula')->whereIn('course_id', $courseIds)->pluck('id')
            : collect();

        if (Schema::hasTable('course_curriculum_subjects') && $curriculumIds->isNotEmpty()) {
            $assignmentIds = DB::table('course_curriculum_subjects')
                ->whereIn('course_curriculum_id', $curriculumIds)
                ->pluck('id');

            if (Schema::hasTable('curriculum_subject_requisites') && $assignmentIds->isNotEmpty()) {
                DB::table('curriculum_subject_requisites')
                    ->whereIn('course_curriculum_subject_id', $assignmentIds)
                    ->delete();
            }

            DB::table('course_curriculum_subjects')->whereIn('course_curriculum_id', $curriculumIds)->delete();
        }

        if (Schema::hasTable('course_curricula') && $curriculumIds->isNotEmpty()) {
            DB::table('course_curricula')->whereIn('id', $curriculumIds)->delete();
        }

        if (Schema::hasTable('subjects') && $subjectIds->isNotEmpty()) {
            DB::table('subjects')->whereIn('id', $subjectIds)->delete();
        }

        DB::table('courses')->whereIn('id', $courseIds)->delete();

        $this->command->info(
            'StrayLegacyCourseCleanupSeeder: removed ' . $courseIds->count() . ' stray course(s), '
            . $subjectIds->count() . ' subject(s), ' . $curriculumIds->count() . ' curriculum row(s), '
            . $studentSubjectLinks . ' student_subject link(s), and ' . $gradeLinks . ' grade record(s).'
        );
    }
}
