<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SeedTrashGradingSheetSubjects extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('faculties') || !Schema::hasTable('subjects') || !Schema::hasTable('students')) {
            return;
        }

        $now = Carbon::now();
        $fallbackFacultyId = DB::table('faculties')->value('id');

        if (!$fallbackFacultyId) {
            $fallbackFacultyId = DB::table('faculties')->insertGetId([
                'code' => 'TRASH-FAC',
                'name' => 'Trash Faculty',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (Schema::hasTable('users')) {
                DB::table('users')->updateOrInsert(
                    ['username' => 'trash.faculty'],
                    [
                        'name' => 'Trash Faculty',
                        'email' => null,
                        'password' => Hash::make('password'),
                        'module' => 'faculty',
                        'force_password_reset' => false,
                        'faculty_id' => $fallbackFacultyId,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        $studentIds = DB::table('students')->orderBy('id')->limit(15)->pluck('id')->all();
        if (count($studentIds) < 10) {
            for ($i = 1; $i <= 15; $i += 1) {
                $studentNo = 'TR-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                DB::table('students')->updateOrInsert(
                    ['student_no' => $studentNo],
                    $this->filterColumns('students', [
                        'name' => 'Trash Student ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'sex' => $i % 2 === 0 ? 'Female' : 'Male',
                        'age' => 20,
                        'college' => 'Trash College',
                        'program' => 'BSA',
                        'curriculum' => '2025-2026',
                        'year_level' => '4',
                        'school_year' => '2025-2026',
                        'semester' => 'First Semester',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ])
                );
            }

            $studentIds = DB::table('students')->orderBy('id')->limit(15)->pluck('id')->all();
        }

        if (!count($studentIds)) {
            return;
        }

        $faculties = DB::table('faculties')->orderBy('id')->get(['id', 'code', 'name']);
        foreach ($faculties as $faculty) {
            if (Schema::hasTable('users')) {
                $hasFacultyLogin = DB::table('users')
                    ->where('faculty_id', $faculty->id)
                    ->where('module', 'faculty')
                    ->exists();

                if (!$hasFacultyLogin) {
                    DB::table('users')->updateOrInsert(
                        ['username' => 'trash.faculty.' . $faculty->id],
                        $this->filterColumns('users', [
                            'name' => $faculty->name ?: 'Trash Faculty ' . $faculty->id,
                            'email' => null,
                            'password' => Hash::make('password'),
                            'module' => 'faculty',
                            'force_password_reset' => false,
                            'faculty_id' => $faculty->id,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ])
                    );
                }
            }

            $subjectCode = 'TRASH-GS-' . str_pad((string) $faculty->id, 3, '0', STR_PAD_LEFT);
            $subjectId = DB::table('subjects')
                ->where('code', $subjectCode)
                ->where('faculty_id', $faculty->id)
                ->value('id');

            $subjectPayload = $this->filterColumns('subjects', [
                'code' => $subjectCode,
                'name' => 'Trash Grading Sheet Subject',
                'units' => 3,
                'lec' => 3,
                'lab' => 0,
                'hours' => 3,
                'course_type' => 'Trash Data',
                'days' => 'MWF',
                'time_start' => '08:00AM',
                'time_end' => '09:00AM',
                'room' => 'TRASH-101',
                'faculty_id' => $faculty->id,
                'faculty' => $faculty->name,
                'year_section' => '4A',
                'course' => 'BSA',
                'semester' => 'First Semester',
                'school_year' => '2025-2026',
                'grading_status' => 'Open For Encoding',
                'is_subject_file_record' => false,
                'added_by' => 'trash-seed',
                'updated_at' => $now,
                'created_at' => $now,
            ]);

            if ($subjectId) {
                unset($subjectPayload['created_at']);
                DB::table('subjects')->where('id', $subjectId)->update($subjectPayload);
            } else {
                $subjectId = DB::table('subjects')->insertGetId($subjectPayload);
            }

            foreach ($studentIds as $studentId) {
                DB::table('student_subject')->updateOrInsert(
                    [
                        'student_id' => (int) $studentId,
                        'subject_id' => (int) $subjectId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                if (Schema::hasTable('student_subject_grades')) {
                    DB::table('student_subject_grades')->updateOrInsert(
                        [
                            'student_id' => (int) $studentId,
                            'subject_id' => (int) $subjectId,
                        ],
                        $this->filterColumns('student_subject_grades', [
                            'prelim' => null,
                            'midterm' => null,
                            'final' => null,
                            'final_average' => null,
                            'remarks' => null,
                            'status' => '',
                            'grade_rule_id' => null,
                            'draft_saved_at' => null,
                            'midterm_posted_at' => null,
                            'final_posted_at' => null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ])
                    );
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('subjects')) {
            $subjectIds = DB::table('subjects')
                ->where('code', 'like', 'TRASH-GS-%')
                ->pluck('id');

            if (Schema::hasTable('student_subject_grades')) {
                DB::table('student_subject_grades')->whereIn('subject_id', $subjectIds)->delete();
            }

            if (Schema::hasTable('student_subject')) {
                DB::table('student_subject')->whereIn('subject_id', $subjectIds)->delete();
            }

            DB::table('subjects')->whereIn('id', $subjectIds)->delete();
        }

        if (Schema::hasTable('students')) {
            DB::table('students')
                ->where('student_no', 'like', 'TR-000%')
                ->where('name', 'like', 'Trash Student %')
                ->delete();
        }

        if (Schema::hasTable('users')) {
            DB::table('users')->where('username', 'trash.faculty')->delete();
            DB::table('users')->where('username', 'like', 'trash.faculty.%')->delete();
        }

        if (Schema::hasTable('faculties')) {
            DB::table('faculties')->where('code', 'TRASH-FAC')->delete();
        }
    }

    private function filterColumns(string $table, array $payload): array
    {
        return array_filter(
            $payload,
            function ($key) use ($table) {
                return Schema::hasColumn($table, $key);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
