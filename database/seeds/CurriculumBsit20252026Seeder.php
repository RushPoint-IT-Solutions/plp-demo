<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBsit20252026Seeder extends Seeder
{
    /**
     * Seeds the real "AY 2025-2026 Curriculum" for BS Information Technology,
     * transcribed from PLP's official curriculum sheet (2 pages): courses,
     * units, lec/lab/hours, and pre-requisites per year and term.
     *
     * "3rd Yr Standing" (ITA 303's, IT 110's and ITA 304's printed
     * requirement) is not a course code, so it is left unresolvable/empty
     * rather than treated as a pre-requisite subject.
     *
     * Run with: php artisan db:seed --class=CurriculumBsit20252026Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSIT';
    private const CURRICULUM_YEAR = '2025-2026';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 003', 'name' => 'The Contemporary World with Peace Education', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 006', 'name' => 'Art Appreciation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 101', 'name' => 'Introduction to Computing', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'COMP 102', 'name' => 'Fundamentals of Programming', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 1', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 101', 'name' => 'National Service Training Program 1 (CWTS/ROTC)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GE 001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 001', 'name' => 'GE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 103', 'name' => 'Intermediate Programming', 'prereq' => ['COMP 102'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 101', 'name' => 'Discrete Mathematics', 'prereq' => ['COMP 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 2', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 2)', 'prereq' => ['PE 1'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 102', 'name' => 'National Service Training Program 2 (CWTS/ROTC)', 'prereq' => ['NSTP 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'GE 008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 002', 'name' => 'GE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 104', 'name' => 'Data Structures and Algorithms', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'COMP 105', 'name' => 'Information Management', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 102', 'name' => 'Quantitative Methods', 'prereq' => ['IT 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ITE 201', 'name' => 'IT Elective 1', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITE 202', 'name' => 'IT Elective 2', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 3', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 3)', 'prereq' => ['PE 1', 'PE 2'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'GE 002', 'name' => "Readings in Philippine History with Indigenous Peoples' Education", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 003', 'name' => 'GE Elective 3', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'IT 103', 'name' => 'Advanced Database Systems', 'prereq' => ['COMP 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 104', 'name' => 'Integrative Programming and Technologies I', 'prereq' => ['ITE 201', 'ITE 202'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 105', 'name' => 'Networking I', 'prereq' => ['ITE 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITA 301', 'name' => 'Web Programming', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'COMP 106', 'name' => 'Applications Development and Emerging Technologies', 'prereq' => ['COMP 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 4', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 4)', 'prereq' => ['PE 1', 'PE 2'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'GE 009', 'name' => "Rizal's Life and Works", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'IT 106', 'name' => 'Systems Integration and Architecture 1', 'prereq' => ['IT 104'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 107', 'name' => 'Networking II', 'prereq' => ['IT 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITA 302', 'name' => 'Software Engineering', 'prereq' => ['COMP 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITA 303', 'name' => 'Technopreneurship', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'IT 110', 'name' => 'Social and Professional Issues', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ITA 305', 'name' => 'Web Development', 'prereq' => ['ITA 301'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Second Semester' => [
                ['code' => 'IT 108', 'name' => 'Information Assurance and Security I', 'prereq' => ['IT 106'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 109', 'name' => 'Introduction to Human Computer Interaction', 'prereq' => ['COMP 102'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITA 304', 'name' => 'IT Governance and Compliance', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ITE 203', 'name' => 'IT Elective 3', 'prereq' => ['IT 104'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'ITA 306', 'name' => 'Multimedia and Technologies', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Summer Semester' => [
                ['code' => 'IT 111', 'name' => 'IT Capstone Project I', 'prereq' => ['IT 108', 'COMP 106', 'ITA 302'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'IT 112', 'name' => 'Information Assurance and Security II', 'prereq' => ['IT 108', 'COMP 106'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'IT 113', 'name' => 'System Administration and Maintenance', 'prereq' => ['IT 112'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'IT 114', 'name' => 'IT Capstone Project II', 'prereq' => ['IT 111'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ITE 204', 'name' => 'IT Elective 4', 'prereq' => ['IT 106'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Second Semester' => [
                ['code' => 'IT 115', 'name' => 'On-the-Job Training (486 hours)', 'prereq' => ['IT 108', 'COMP 106'], 'lec' => 6, 'lab' => 0, 'units' => 6, 'hours' => 6],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBsit20252026Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBsit20252026Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
                return;
            }
        }

        $now = now();

        $curriculumId = DB::table('course_curricula')->where([
            'course_id' => $course->id,
            'curriculum_year_code' => self::CURRICULUM_YEAR,
        ])->value('id');

        $curriculumPayload = [
            'course_id' => $course->id,
            'curriculum_year_code' => self::CURRICULUM_YEAR,
            'date_from' => '2025-08-01',
            'date_to' => '2026-05-31',
            'title' => $course->name . ' Curriculum ' . self::CURRICULUM_YEAR,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($curriculumId) {
            DB::table('course_curricula')->where('id', $curriculumId)->update($curriculumPayload);
        } else {
            $curriculumPayload['created_at'] = $now;
            $curriculumId = DB::table('course_curricula')->insertGetId($curriculumPayload);
        }

        $subjectIdByCode = [];
        $assignmentIdByCode = [];
        $subjectsCreated = 0;
        $assignmentsSaved = 0;

        foreach (self::CURRICULUM as $yearLabel => $semesterRows) {
            $yearBlockId = (int) $yearBlockIds[$yearLabel];

            foreach ($semesterRows as $semesterName => $courseRows) {
                if (!isset($semesterIds[$semesterName])) {
                    $this->command->warn("CurriculumBsit20252026Seeder: missing semesters row '{$semesterName}'; skipping this term.");
                    continue;
                }

                $semesterId = (int) $semesterIds[$semesterName];
                $displayOrder = 0;

                foreach ($courseRows as $row) {
                    $subjectId = $this->resolveSubjectId($row, $subjectIdByCode, $now, $subjectsCreated);
                    $subjectIdByCode[$row['code']] = $subjectId;

                    $displayOrder++;
                    $assignmentId = DB::table('course_curriculum_subjects')->where([
                        'course_curriculum_id' => $curriculumId,
                        'subject_id' => $subjectId,
                        'year_block_id' => $yearBlockId,
                        'semester_id' => $semesterId,
                    ])->value('id');

                    $assignmentPayload = [
                        'credited_units' => $row['units'],
                        'display_order' => $displayOrder,
                        'updated_at' => $now,
                    ];

                    if ($assignmentId) {
                        DB::table('course_curriculum_subjects')->where('id', $assignmentId)->update($assignmentPayload);
                    } else {
                        $assignmentPayload['course_curriculum_id'] = $curriculumId;
                        $assignmentPayload['subject_id'] = $subjectId;
                        $assignmentPayload['year_block_id'] = $yearBlockId;
                        $assignmentPayload['semester_id'] = $semesterId;
                        $assignmentPayload['created_at'] = $now;
                        $assignmentId = DB::table('course_curriculum_subjects')->insertGetId($assignmentPayload);
                    }

                    $assignmentIdByCode[$row['code']] = $assignmentId;
                    $assignmentsSaved++;
                }
            }
        }

        $preRequisiteTypeId = DB::table('curriculum_requisite_types')->where('code', 'pre')->value('id');
        $requisitesSaved = 0;
        $requisitesSkipped = 0;

        if ($preRequisiteTypeId) {
            foreach (self::CURRICULUM as $semesterRows) {
                foreach ($semesterRows as $courseRows) {
                    foreach ($courseRows as $row) {
                        if (empty($row['prereq'])) {
                            continue;
                        }

                        $ownerAssignmentId = $assignmentIdByCode[$row['code']] ?? null;
                        if (!$ownerAssignmentId) {
                            continue;
                        }

                        foreach ($row['prereq'] as $sortOrder => $prereqCode) {
                            $requisiteSubjectId = $subjectIdByCode[$prereqCode] ?? null;
                            if (!$requisiteSubjectId) {
                                $requisitesSkipped++;
                                continue;
                            }

                            DB::table('curriculum_subject_requisites')->updateOrInsert(
                                [
                                    'course_curriculum_subject_id' => $ownerAssignmentId,
                                    'requisite_subject_id' => $requisiteSubjectId,
                                    'curriculum_requisite_type_id' => $preRequisiteTypeId,
                                ],
                                [
                                    'sort_order' => $sortOrder,
                                    'updated_at' => $now,
                                    'created_at' => $now,
                                ]
                            );
                            $requisitesSaved++;
                        }
                    }
                }
            }
        }

        $this->command->info(
            'CurriculumBsit20252026Seeder: ' . $subjectsCreated . ' subject(s) created, '
            . $assignmentsSaved . ' curriculum course assignment(s) saved, '
            . $requisitesSaved . ' pre-requisite link(s) saved, '
            . $requisitesSkipped . ' pre-requisite reference(s) skipped (code not found on this sheet).'
        );
    }

    private function resolveSubjectId(array $row, array $subjectIdByCode, $now, int &$subjectsCreated): int
    {
        if (isset($subjectIdByCode[$row['code']])) {
            return $subjectIdByCode[$row['code']];
        }

        $existingId = DB::table('subjects')
            ->where('code', $row['code'])
            ->where('is_subject_file_record', true)
            ->value('id');

        $payload = [
            'name' => $row['name'],
            'units' => $row['units'],
            'lec' => $row['lec'],
            'lab' => $row['lab'],
            'hours' => $row['hours'],
            'course_type' => $this->resolveCourseType($row['code']),
            'updated_at' => $now,
        ];

        if ($existingId) {
            DB::table('subjects')->where('id', $existingId)->update($payload);
            return (int) $existingId;
        }

        $payload['code'] = $row['code'];
        $payload['is_subject_file_record'] = true;
        $payload['created_at'] = $now;
        $subjectsCreated++;

        return (int) DB::table('subjects')->insertGetId($payload);
    }

    private function resolveCourseType(string $code): string
    {
        $prefix = strtoupper(trim(preg_replace('/[0-9].*$/', '', $code)));

        if (in_array($prefix, ['GE', 'GEE', 'GEC'], true)) {
            return 'General Education';
        }

        if ($prefix === 'PE') {
            return 'PE';
        }

        if ($prefix === 'NSTP') {
            return 'NSTP';
        }

        return 'Major';
    }
}
