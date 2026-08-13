<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBsba20232024Seeder extends Seeder
{
    /**
     * Seeds the real "Revised Curriculum Academic Year 2023-2024" for BS
     * Business Administration Major in Marketing Management, transcribed
     * from PLP's official curriculum sheet (2 pages): courses, units,
     * lec/lab/hours, and pre-requisites per year and term.
     *
     * BA 401's printed pre-requisite ("Completion of atleast 90% Academic
     * Requirements") is not a course code, so it is left empty rather than
     * treated as a pre-requisite subject.
     *
     * Run with: php artisan db:seed --class=CurriculumBsba20232024Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSBA';
    private const CURRICULUM_YEAR = '2023-2024';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 101', 'name' => 'Basic Microeconomics (Eco)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 202', 'name' => 'Marketing Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 201', 'name' => 'Professional Salesmanship', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 101', 'name' => 'Physical Activities Towards Health and Fitness 1 (PATHFit1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 101', 'name' => 'National Service Training Program 1 (CWTS/ROTC)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GE 002', 'name' => "Readings in Philippine History with Indigenous Peoples' Education", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 009', 'name' => "Rizal's Life and Works", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 102', 'name' => 'Good Governance and Social Responsibility', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 301', 'name' => 'Elective: Consumer Behavior', 'prereq' => ['BA 202'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 501', 'name' => 'Financial Accounting', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 102', 'name' => 'Physical Activities Towards Health and Fitness 2 (PATHFit2)', 'prereq' => ['PE 101'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 102', 'name' => 'National Service Training Program 2 (CWTS/ROTC)', 'prereq' => ['NSTP 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'GE 003', 'name' => 'The Contemporary World with Peace Education', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 006', 'name' => 'Art Appreciation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 104', 'name' => 'Taxation (Income Taxation)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 107', 'name' => 'Business Laws (Obligations and Contracts)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 204', 'name' => 'GE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 203', 'name' => 'Marketing Research', 'prereq' => ['BA 301'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 502', 'name' => 'Managerial Accounting', 'prereq' => ['BA 501'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 103', 'name' => 'Physical Activities Towards Health and Fitness 3 (PATHFit3)', 'prereq' => ['PE 102'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'GE 007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 301', 'name' => 'GE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 104', 'name' => 'GE Elective 3', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 206', 'name' => 'Distribution Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 103', 'name' => 'Business Research', 'prereq' => ['BA 203'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 204', 'name' => 'Advertising', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 205', 'name' => 'Pricing Strategy', 'prereq' => ['BA 502'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 104', 'name' => 'Physical Activities Towards Health and Fitness 4 (PATHFit4)', 'prereq' => ['PE 103'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'BA 105', 'name' => 'Human Resource Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 302', 'name' => 'Elective: E-Commerce and Internet Marketing', 'prereq' => ['BA 301'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 106', 'name' => 'Feasibility Study', 'prereq' => ['BA 103'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 207', 'name' => 'Product Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'BA 208', 'name' => 'Retail Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 503', 'name' => 'FS Implementation', 'prereq' => ['BA 106'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 306', 'name' => 'Elective: International Marketing', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CBMEC 101', 'name' => 'Operations Management (TQM)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'BA 108', 'name' => 'International Business and Trade', 'prereq' => ['BA 306'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'BA 303', 'name' => 'Elective: Services Marketing', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'CBMEC 102', 'name' => 'Strategic Management', 'prereq' => ['CBMEC 101'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'BA 401', 'name' => 'Practicum/Work-Integrated Learning (600 hours)', 'prereq' => [], 'lec' => 0, 'lab' => 6, 'units' => 6, 'hours' => 6],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBsba20232024Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBsba20232024Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
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
            'date_from' => '2023-08-01',
            'date_to' => '2027-05-31',
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
                    $this->command->warn("CurriculumBsba20232024Seeder: missing semesters row '{$semesterName}'; skipping this term.");
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
            'CurriculumBsba20232024Seeder: ' . $subjectsCreated . ' subject(s) created, '
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
