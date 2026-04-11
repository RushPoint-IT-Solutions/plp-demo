<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $faculty = DB::table('faculties')->where('code', 'FAC-001')->first();

        $hasCourseColumn = Schema::hasColumn('subjects', 'course');
        $hasCourseIdColumn = Schema::hasColumn('subjects', 'course_id');
        $hasSemesterColumn = Schema::hasColumn('subjects', 'semester');
        $hasSchoolYearColumn = Schema::hasColumn('subjects', 'school_year');
        $hasAcademicTermIdColumn = Schema::hasColumn('subjects', 'academic_term_id');
        $hasGradingStatusColumn = Schema::hasColumn('subjects', 'grading_status');
        $hasGradingStatusIdColumn = Schema::hasColumn('subjects', 'grading_status_id');

        $courseIdsByCode = $hasCourseIdColumn
            ? DB::table('courses')->pluck('id', 'code')->toArray()
            : [];

        $academicTermIds = [];
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermIds = DB::table('academic_terms')
                ->select('id', 'school_year', 'term')
                ->get()
                ->mapWithKeys(function ($row) {
                    $key = strtolower(trim((string) $row->school_year) . '|' . trim((string) $row->term));

                    return [$key => (int) $row->id];
                })
                ->all();
        }

        $gradingStatusIds = [];
        if ($hasGradingStatusIdColumn && Schema::hasTable('subject_grading_statuses')) {
            $gradingStatusIds = DB::table('subject_grading_statuses')
                ->select('id', 'code')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [strtolower(trim((string) $row->code)) => (int) $row->id];
                })
                ->all();
        }

        $subjects = [
            [
                'code'           => 'SAM125',
                'name'           => 'System Administration and Maintenance',
                'units'          => 3.0,
                'days'           => 'M,Th',
                'time_start'     => '01:00PM',
                'time_end'       => '02:00PM',
                'room'           => '5',
                'faculty_id'     => $faculty ? $faculty->id : null,
                'year_section'   => '4-B',
                'course'         => 'BSCS',
                'grading_status' => 'Submitted',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'OOP111',
                'name'           => 'Object-Oriented Programming',
                'units'          => 3.0,
                'days'           => 'T,F',
                'time_start'     => '04:00PM',
                'time_end'       => '05:00PM',
                'room'           => '2',
                'faculty_id'     => $faculty ? $faculty->id : null,
                'year_section'   => '4-B',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'SPI128',
                'name'           => 'Social And Professional Issues',
                'units'          => 2.0,
                'days'           => 'W,Th',
                'time_start'     => '08:00AM',
                'time_end'       => '10:00AM',
                'room'           => '1',
                'faculty_id'     => $faculty ? $faculty->id : null,
                'year_section'   => '4-A',
                'course'         => 'BSIT',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'UTS12',
                'name'           => 'Understanding The Self',
                'units'          => 2.0,
                'days'           => 'F,Th',
                'time_start'     => '03:00PM',
                'time_end'       => '05:00PM',
                'room'           => '8',
                'faculty_id'     => $faculty ? $faculty->id : null,
                'year_section'   => '1-C',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'MATH101',
                'name'           => 'College Algebra',
                'units'          => 3.0,
                'days'           => 'M,W',
                'time_start'     => '09:00AM',
                'time_end'       => '10:30AM',
                'room'           => '3',
                'faculty_id'     => null,
                'year_section'   => '1-A',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'ENG101',
                'name'           => 'Purposive Communication',
                'units'          => 3.0,
                'days'           => 'T,Th',
                'time_start'     => '10:30AM',
                'time_end'       => '12:00PM',
                'room'           => '4',
                'faculty_id'     => null,
                'year_section'   => '1-A',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'NSTP102',
                'name'           => 'National Service Training Program 2',
                'units'          => 3.0,
                'days'           => 'F,Sa',
                'time_start'     => '01:00PM',
                'time_end'       => '02:30PM',
                'room'           => '6',
                'faculty_id'     => null,
                'year_section'   => '1-B',
                'course'         => 'BSIT',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
        ];

        foreach ($subjects as $subject) {
            $academicTermKey = strtolower(trim((string) $subject['school_year']) . '|' . trim((string) $subject['semester']));
            $academicTermId = isset($academicTermIds[$academicTermKey]) ? (int) $academicTermIds[$academicTermKey] : null;

            $insertPayload = [
                'code' => $subject['code'],
                'name' => $subject['name'],
                'units' => $subject['units'],
                'days' => $subject['days'],
                'time_start' => $subject['time_start'],
                'time_end' => $subject['time_end'],
                'room' => $subject['room'],
                'faculty_id' => $subject['faculty_id'],
                'year_section' => $subject['year_section'],
            ];

            if ($hasGradingStatusColumn) {
                $insertPayload['grading_status'] = $subject['grading_status'];
            }

            if ($hasGradingStatusIdColumn) {
                $statusKey = strtolower(trim((string) $subject['grading_status']));
                $insertPayload['grading_status_id'] = isset($gradingStatusIds[$statusKey])
                    ? (int) $gradingStatusIds[$statusKey]
                    : null;
            }

            if ($hasCourseColumn) {
                $insertPayload['course'] = $subject['course'];
            }

            if ($hasCourseIdColumn) {
                $insertPayload['course_id'] = isset($courseIdsByCode[$subject['course']])
                    ? (int) $courseIdsByCode[$subject['course']]
                    : null;
            }

            if ($hasSemesterColumn) {
                $insertPayload['semester'] = $subject['semester'];
            }

            if ($hasSchoolYearColumn) {
                $insertPayload['school_year'] = $subject['school_year'];
            }

            if ($hasAcademicTermIdColumn) {
                $insertPayload['academic_term_id'] = $academicTermId;
            }

            $identity = ['code' => $subject['code']];
            if ($hasAcademicTermIdColumn && $academicTermId) {
                $identity['academic_term_id'] = $academicTermId;
            } elseif ($hasSemesterColumn && $hasSchoolYearColumn) {
                $identity['semester'] = $subject['semester'];
                $identity['school_year'] = $subject['school_year'];
            }

            DB::table('subjects')->updateOrInsert(
                $identity,
                array_merge($insertPayload, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        // Enrol the baseline seeded student into all subjects.
        $student = DB::table('students')->where('student_no', '1234567891012')->first();
        if (!$student) {
            return;
        }

        $subjectIds = DB::table('subjects')
            ->whereIn('code', array_column($subjects, 'code'))
            ->pluck('id');

        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                ['student_id' => $student->id, 'subject_id' => $subjectId],
                ['student_id' => $student->id, 'subject_id' => $subjectId]
            );
        }
    }
}
