<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBscs20252026Seeder extends Seeder
{
    /**
     * Seeds the real "AY 2025-2026 Curriculum" for BS Computer Science,
     * transcribed from PLP's official curriculum sheet (2 pages): courses,
     * units, lec/lab/hours, and pre-requisites per year and term.
     *
     * "4th Year Standing" (CS 113's requirement) is not a course code, so it
     * is left unresolvable/empty rather than treated as a pre-requisite
     * subject. Likewise "2nd Yr Standing" (CSM 301's and CSA 402's printed
     * requirement) is not a course code.
     *
     * Run with: php artisan db:seed --class=CurriculumBscs20252026Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSCS';
    private const CURRICULUM_YEAR = '2025-2026';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 003', 'name' => 'The Contemporary World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 006', 'name' => 'Art Appreciation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 101', 'name' => 'Introduction to Computing', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 102', 'name' => 'Fundamentals of Programming', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 1', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 101', 'name' => 'National Service Training Program 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GE 001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 001', 'name' => 'GE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 103', 'name' => 'Intermediate Programming', 'prereq' => ['COMP 102'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CS 101', 'name' => 'Discrete Structures I', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 2', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 2)', 'prereq' => ['PE 1'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 102', 'name' => 'National Service Training Program 2', 'prereq' => ['NSTP 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'GE 008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 002', 'name' => 'GE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'COMP 104', 'name' => 'Data Structures and Algorithms', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'COMP 105', 'name' => 'Information Management', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CS 102', 'name' => 'Discrete Structures II', 'prereq' => ['CS 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 103', 'name' => 'Object-Oriented Programming', 'prereq' => ['CS 101', 'COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 3', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 3)', 'prereq' => ['PE 1', 'PE 2'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'GE 002', 'name' => 'Readings in Philippine History', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 003', 'name' => 'GE Elective 3', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 104', 'name' => 'Algorithms and Complexity', 'prereq' => ['CS 101', 'COMP 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 105', 'name' => 'Software Engineering 1', 'prereq' => ['COMP 105', 'CS 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSM 301', 'name' => 'Linear Algebra', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CSA 402', 'name' => 'Web Programming Development', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'COMP 106', 'name' => 'Application Development and Emerging Technologies', 'prereq' => ['COMP 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 4', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 4)', 'prereq' => ['PE 1', 'PE 2'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'GE 009', 'name' => 'Life, Works and Writings of Rizal', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CSA 401', 'name' => 'Digital Design', 'prereq' => ['CS 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 106', 'name' => 'Automata Theory and Formal Languages', 'prereq' => ['CS 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 107', 'name' => 'Architecture and Organization', 'prereq' => ['CS 101', 'COMP 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CS 108', 'name' => 'Information Assurance and Security', 'prereq' => ['COMP 105'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'CS 109', 'name' => 'Software Engineering II', 'prereq' => ['CS 105'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSE 201', 'name' => 'CS Elective 1', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSA 403', 'name' => 'Open Source Programming with Database', 'prereq' => ['CSA 402'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Second Semester' => [
                ['code' => 'CS 110', 'name' => 'Operating Systems', 'prereq' => ['COMP 104'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CS 111', 'name' => 'Programming Languages', 'prereq' => ['COMP 104'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSA 406', 'name' => 'Robotics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CSE 202', 'name' => 'CS Elective 2', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSA 405', 'name' => 'Open Source Programming with Framework', 'prereq' => ['CSA 403'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CSA 404', 'name' => 'Multimedia Systems', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Summer Semester' => [
                ['code' => 'CS 115', 'name' => 'CS Thesis 1', 'prereq' => ['CS 109'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'CS 114', 'name' => 'Human Computer Interaction', 'prereq' => ['COMP 103'], 'lec' => 1, 'lab' => 0, 'units' => 1, 'hours' => 1],
                ['code' => 'CS 117', 'name' => 'CS Thesis 2', 'prereq' => ['CS 115'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CSE 203', 'name' => 'CS Elective 3', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CS 112', 'name' => 'Social Issues and Professional Practice', 'prereq' => ['CS 109'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'CS 116', 'name' => 'Networks and Communications', 'prereq' => ['COMP 103'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'CS 113', 'name' => 'On-the-Job Training Program (162 hours)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBscs20252026Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBscs20252026Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
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
                    $this->command->warn("CurriculumBscs20252026Seeder: missing semesters row '{$semesterName}'; skipping this term.");
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
            'CurriculumBscs20252026Seeder: ' . $subjectsCreated . ' subject(s) created, '
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
