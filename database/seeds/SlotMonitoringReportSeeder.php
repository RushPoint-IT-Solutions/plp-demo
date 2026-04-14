<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SlotMonitoringReportSeeder extends Seeder
{
    private const REPORT_COURSE_CODE = 'SMRPT';
    private const REPORT_COURSE_NAME = 'Slot Monitoring Report Seed Program';
    private const REPORT_TERM = 'First';
    private const REPORT_SLOTS = 30;

    private const SUBJECT_BLUEPRINTS = [
        [
            'key' => 'actual',
            'code' => 'SMR-ACT-001',
            'name' => 'Slot Monitoring Actual Size Subject',
            'year_section' => 'SMRPT 2-A',
            'days' => 'MWF',
            'time_start' => '08:00',
            'time_end' => '09:00',
            'room' => '201',
        ],
        [
            'key' => 'under20',
            'code' => 'SMR-U20-001',
            'name' => 'Slot Monitoring Under 20 Subject',
            'year_section' => 'SMRPT 2-B',
            'days' => 'TTH',
            'time_start' => '10:00',
            'time_end' => '11:30',
            'room' => '202',
        ],
        [
            'key' => 'dissolved',
            'code' => 'SMR-DIS-001',
            'name' => 'Slot Monitoring Dissolved Subject',
            'year_section' => 'SMRPT 2-C',
            'days' => 'F',
            'time_start' => '13:00',
            'time_end' => '15:00',
            'room' => '203',
        ],
        [
            'key' => 'closed',
            'code' => 'SMR-CLS-001',
            'name' => 'Slot Monitoring Closed Subject',
            'year_section' => 'SMRPT 2-D',
            'days' => 'SAT',
            'time_start' => '07:00',
            'time_end' => '10:00',
            'room' => '204',
        ],
    ];

    private const ALLOWED_DEPARTMENT_NAMES = [
        'COLLEGE OF NURSING',
        'COLLEGE OF ARTS AND SCIENCES',
        'COLLEGE OF COMPUTER STUDIES',
        'COLLEGE OF ENGINEERING',
        'COLLEGE OF INTERNATIONAL HOSPITALITY MANAGEMENT',
        'COLLEGE OF BUSINESS AND ACCOUNTANCY',
        'COLLEGE OF BUSINESS ADMINISTRATION',
        'COLLEGE OF EDUCATION',
    ];

    public function run()
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('SlotMonitoringReportSeeder cannot run in production.');
        }

        $requiredTables = [
            'departments',
            'courses',
            'academic_terms',
            'subjects',
            'students',
            'student_subject',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->command->warn('SlotMonitoringReportSeeder: missing table ' . $table . '.');
                return;
            }
        }

        $requiredSubjectColumns = ['code', 'name', 'course_id', 'academic_term_id', 'year_section'];
        foreach ($requiredSubjectColumns as $column) {
            if (!Schema::hasColumn('subjects', $column)) {
                $this->command->warn('SlotMonitoringReportSeeder: missing subjects.' . $column . ' column.');
                return;
            }
        }

        $requiredStudentSubjectColumns = ['student_id', 'subject_id'];
        foreach ($requiredStudentSubjectColumns as $column) {
            if (!Schema::hasColumn('student_subject', $column)) {
                $this->command->warn('SlotMonitoringReportSeeder: missing student_subject.' . $column . ' column.');
                return;
            }
        }

        $now = now();

        $departmentId = $this->resolveDepartmentId($now);
        $courseId = $this->resolveCourseId($departmentId, $now);
        $academicTermId = $this->resolveAcademicTermId($now);

        $subjectIds = [];
        foreach (self::SUBJECT_BLUEPRINTS as $blueprint) {
            $subjectIds[$blueprint['key']] = $this->upsertSubject($blueprint, $courseId, $academicTermId, $now);
        }

        $studentIds = $this->upsertStudents($courseId, $academicTermId, self::REPORT_SLOTS, $now);
        if (count($studentIds) < self::REPORT_SLOTS) {
            $this->command->warn('SlotMonitoringReportSeeder: insufficient seeded students for closed report.');
            return;
        }

        $enrollmentTargets = [
            'actual' => array_slice($studentIds, 0, 24),
            'under20' => array_slice($studentIds, 0, 12),
            'dissolved' => [],
            'closed' => array_slice($studentIds, 0, self::REPORT_SLOTS),
        ];

        foreach ($enrollmentTargets as $key => $subjectStudentIds) {
            $subjectId = (int) ($subjectIds[$key] ?? 0);
            if ($subjectId <= 0) {
                continue;
            }

            $this->syncSubjectEnrollment($subjectId, $subjectStudentIds, $now);
        }

        $this->command->info('SlotMonitoringReportSeeder: seeded report subjects and enrollments for actual-size, under-20, dissolved, and closed reports.');
    }

    private function resolveDepartmentId($now)
    {
        $departmentId = (int) DB::table('departments')
            ->whereIn(DB::raw('UPPER(TRIM(COALESCE(description, "")))'), self::ALLOWED_DEPARTMENT_NAMES)
            ->orderBy('id')
            ->value('id');

        if ($departmentId > 0) {
            return $departmentId;
        }

        DB::table('departments')->updateOrInsert(
            ['code' => 'CS'],
            [
                'description' => 'College of Computer Studies',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('departments')
            ->where('code', 'CS')
            ->value('id');
    }

    private function resolveCourseId($departmentId, $now)
    {
        DB::table('courses')->updateOrInsert(
            ['code' => self::REPORT_COURSE_CODE],
            [
                'name' => self::REPORT_COURSE_NAME,
                'program_type' => 'college',
                'department_id' => $departmentId,
                'description' => 'Seeded program dedicated to Slot Monitoring report validation.',
                'slots' => self::REPORT_SLOTS,
                'track_category' => null,
                'non_filipino' => false,
                'dean_director_id' => null,
                'program_file' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('courses')
            ->where('code', self::REPORT_COURSE_CODE)
            ->value('id');
    }

    private function resolveAcademicTermId($now)
    {
        $startYear = (int) date('Y');
        $schoolYear = $startYear . '-' . ($startYear + 1);
        $term = self::REPORT_TERM;
        $canonicalKey = strtolower(trim($schoolYear) . '|' . trim($term));

        DB::table('academic_terms')->updateOrInsert(
            ['canonical_key' => $canonicalKey],
            [
                'school_year' => $schoolYear,
                'term' => $term,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('academic_terms')
            ->where('canonical_key', $canonicalKey)
            ->value('id');
    }

    private function upsertSubject(array $blueprint, $courseId, $academicTermId, $now)
    {
        $payload = [
            'name' => $blueprint['name'],
            'units' => 3.0,
            'lec' => 3,
            'lab' => 0,
            'days' => $blueprint['days'],
            'time_start' => $blueprint['time_start'],
            'time_end' => $blueprint['time_end'],
            'room' => $blueprint['room'],
            'year_section' => $blueprint['year_section'],
            'course_id' => $courseId,
            'academic_term_id' => $academicTermId,
            'added_by' => 'seed',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $payload['is_subject_file_record'] = 0;
        }

        DB::table('subjects')->updateOrInsert(
            [
                'code' => $blueprint['code'],
                'course_id' => $courseId,
                'academic_term_id' => $academicTermId,
            ],
            $payload
        );

        return (int) DB::table('subjects')
            ->where('code', $blueprint['code'])
            ->where('course_id', $courseId)
            ->where('academic_term_id', $academicTermId)
            ->orderBy('id')
            ->value('id');
    }

    private function upsertStudents($courseId, $academicTermId, $total, $now)
    {
        $numbers = [];

        for ($i = 1; $i <= $total; $i++) {
            $studentNo = 'SMR-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
            $numbers[] = $studentNo;

            $payload = [
                'name' => 'Slot Report Student ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('students', 'course_id')) {
                $payload['course_id'] = $courseId;
            }

            if (Schema::hasColumn('students', 'academic_term_id')) {
                $payload['academic_term_id'] = $academicTermId;
            }

            DB::table('students')->updateOrInsert(
                ['student_no' => $studentNo],
                $payload
            );
        }

        $idMap = DB::table('students')
            ->whereIn('student_no', $numbers)
            ->pluck('id', 'student_no');

        $orderedIds = [];
        foreach ($numbers as $studentNo) {
            if ($idMap->has($studentNo)) {
                $orderedIds[] = (int) $idMap->get($studentNo);
            }
        }

        return $orderedIds;
    }

    private function syncSubjectEnrollment($subjectId, array $studentIds, $now)
    {
        DB::table('student_subject')
            ->where('subject_id', $subjectId)
            ->delete();

        if (empty($studentIds)) {
            return;
        }

        $rows = [];
        foreach ($studentIds as $studentId) {
            $rows[] = [
                'student_id' => (int) $studentId,
                'subject_id' => $subjectId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('student_subject')->insert($chunk);
        }
    }
}