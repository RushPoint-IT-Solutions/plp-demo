<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherAllowedSubjectSampleSeeder extends Seeder
{
    private const SUBJECTS_PER_TEACHER = 8;

    public function run()
    {
        if (!Schema::hasTable('faculties') || !Schema::hasTable('subjects') || !Schema::hasTable('teacher_allowed_subjects')) {
            $this->command->warn('TeacherAllowedSubjectSampleSeeder: required tables are missing.');
            return;
        }

        $facultyIds = DB::table('faculties')->orderBy('id')->pluck('id')->map(function ($id) {
            return (int) $id;
        })->filter()->values()->all();

        $subjectQuery = DB::table('subjects')->orderBy('course_id')->orderBy('code')->orderBy('name')->orderBy('id');
        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $subjectQuery->where(function ($query) {
                $query->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 1)
                    ->orWhere('is_subject_file_record', true);
            });
        }

        $subjectIds = $subjectQuery->pluck('id')->map(function ($id) {
            return (int) $id;
        })->filter()->values()->all();

        if (!count($facultyIds) || !count($subjectIds)) {
            $this->command->warn('TeacherAllowedSubjectSampleSeeder: no faculty or subjects found.');
            return;
        }

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id')
            ?: User::query()->orderBy('id')->value('id'));

        $created = 0;
        $perTeacher = min(self::SUBJECTS_PER_TEACHER, count($subjectIds));

        DB::beginTransaction();
        try {
            foreach ($facultyIds as $facultyIndex => $facultyId) {
                $start = ($facultyIndex * $perTeacher) % count($subjectIds);

                for ($i = 0; $i < $perTeacher; $i++) {
                    $subjectId = $subjectIds[($start + $i) % count($subjectIds)];
                    $before = DB::table('teacher_allowed_subjects')
                        ->where('faculty_id', $facultyId)
                        ->where('subject_id', $subjectId)
                        ->exists();

                    DB::table('teacher_allowed_subjects')->updateOrInsert([
                        'faculty_id' => $facultyId,
                        'subject_id' => $subjectId,
                    ], [
                        'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (!$before) {
                        $created++;
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->info('TeacherAllowedSubjectSampleSeeder: added ' . $created . ' teacher allowed-subject links.');
    }
}
