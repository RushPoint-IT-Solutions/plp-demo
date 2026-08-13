<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBshm20252026Seeder extends Seeder
{
    /**
     * Seeds the real "AY 2025-2026 Curriculum" for BS Hospitality
     * Management, transcribed from PLP's official curriculum sheet (2
     * pages): courses, units, lec/lab/hours, and pre-requisites per year
     * and term.
     *
     * The sheet cites most pre-requisites with an "HM - " college prefix
     * (e.g. "HM - HPC 201", "HMTHC 108"); only the actual course code
     * portion (e.g. "HPC 201", "THC 108") is kept here, since that prefix
     * is not part of any course's own code on this sheet.
     *
     * PRAC 101's and PRAC 102's printed pre-requisites ("Completion of
     * atleast 90% 2nd/3rd year Requirements") are not course codes, so they
     * are left empty. HPC 208's printed pre-requisite ("HM 208") does not
     * resolve to any course code on this sheet either and is left empty.
     *
     * Run with: php artisan db:seed --class=CurriculumBshm20252026Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSHM';
    private const CURRICULUM_YEAR = '2025-2026';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 301', 'name' => 'Classical French Cuisine', 'prereq' => [], 'lec' => 1, 'lab' => 2, 'units' => 3, 'hours' => 7],
                ['code' => 'THC 101', 'name' => 'Risk Management as Applied to Safety, Security and Sanitation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 102', 'name' => 'Professional Development and Applied Ethics in Hospitality Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 201', 'name' => 'Fundamentals in Lodging Operations', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'HME 302', 'name' => 'Introduction to Transport Services', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 1', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 101', 'name' => 'National Service Training Program (CWTS/ROTC)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GE 005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 002', 'name' => "Readings in the Philippine History with Indigenous People's Education", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 006', 'name' => 'Art Appreciation - HM', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 202', 'name' => 'Fundamentals in Food Service Operations', 'prereq' => ['HPC 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'THC 103', 'name' => 'Macro Perspective of Tourism and Hospitality', 'prereq' => ['HPC 201'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 2', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 2)', 'prereq' => ['PE 1'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 102', 'name' => 'National Service Training Program (CWTS/ROTC)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'GE 003', 'name' => 'The Contemporary World with Peace Education', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 009', 'name' => "Rizal's Life and Works", 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 104', 'name' => 'Micro Perspective of Tourism and Hospitality', 'prereq' => ['THC 103'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 203', 'name' => 'Kitchen Essentials and Basic Food Preparation (Interchangeable to Bread and Pastry)', 'prereq' => ['HPC 202'], 'lec' => 1, 'lab' => 2, 'units' => 3, 'hours' => 7],
                ['code' => 'HPC 204A', 'name' => 'Foreign Language I (Introduction)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 205', 'name' => 'Supply Chain Management in Hospitality Industry', 'prereq' => ['HPC 202'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 303', 'name' => 'Front Office Operation', 'prereq' => ['HPC 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'HME 304', 'name' => 'Bar and Beverage Management', 'prereq' => ['HPC 202'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'PE 3', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 3)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'GEE 201', 'name' => 'The Entrepreneurial Minds', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 204B', 'name' => 'Foreign Language II (Conversational)', 'prereq' => ['HPC 204A'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 305', 'name' => 'Housekeeping Operation', 'prereq' => ['HPC 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'THC 105', 'name' => 'Legal Aspect in Tourism and Hospitality', 'prereq' => ['THC 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 106', 'name' => 'Tourism and Hospitality Marketing', 'prereq' => ['THC 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 108', 'name' => 'Philippine Culture and Tourism Geography', 'prereq' => ['THC 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 206', 'name' => 'Applied Business Tools and Technologies', 'prereq' => ['HPC 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'HME 306', 'name' => 'Bread and Pastry (Interchangeable to Kitchen Essentials and Basic Food Preparation)', 'prereq' => ['HPC 202'], 'lec' => 1, 'lab' => 2, 'units' => 3, 'hours' => 7],
                ['code' => 'PE 4', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 4)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Summer Semester' => [
                ['code' => 'PRAC 101', 'name' => 'Industry Placement: Vessel or Land Based Resort or Travel Agency (150 Hrs)', 'prereq' => [], 'lec' => 0, 'lab' => 1.5, 'units' => 1.5, 'hours' => 1.5],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'THC 107', 'name' => 'Entrepreneurship in Tourism and Hospitality', 'prereq' => ['THC 104'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 202', 'name' => 'Business Logic', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 109', 'name' => 'Multicultural Diversity in Workplace for the Tourism Professional', 'prereq' => ['THC 108'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 207', 'name' => 'Ergonomics and Facilities Planning for the Hospitality Industry', 'prereq' => ['HPC 201'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'HME 307', 'name' => 'Cost Control for Hospitality Industry', 'prereq' => ['HPC 202'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'THC 110', 'name' => 'Quality Service Management in Tourism and Hospitality', 'prereq' => ['THC 108'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GEE 203', 'name' => 'Great Books', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 308', 'name' => 'Asian Cuisine', 'prereq' => ['HPC 202'], 'lec' => 1, 'lab' => 2, 'units' => 3, 'hours' => 7],
                ['code' => 'CBMEC 101', 'name' => 'Operation Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 309', 'name' => 'Recreation and Leisure Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HPC 208', 'name' => 'Research in Hospitality', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
            ],
            'Summer Semester' => [
                ['code' => 'PRAC 102', 'name' => 'Industry Placement: Vessel or Land Based Resort or Travel Agency (150 Hrs)', 'prereq' => [], 'lec' => 0, 'lab' => 1.5, 'units' => 1.5, 'hours' => 1.5],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'PRAC 103', 'name' => 'Industry Placement: City Hotel Internship (400 hours)', 'prereq' => [], 'lec' => 0, 'lab' => 4, 'units' => 4, 'hours' => 4],
            ],
            'Second Semester' => [
                ['code' => 'HPC 209', 'name' => 'Introduction to Meetings Incentives, Conferences, and Event Management (MICE)', 'prereq' => [], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 3],
                ['code' => 'CBMEC 102', 'name' => 'Strategic Management and Total Quality Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 310', 'name' => 'Trends and Issues in Hospitality Management', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'HME 311', 'name' => 'Sustainable Hospitality', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBshm20252026Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBshm20252026Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
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
                    $this->command->warn("CurriculumBshm20252026Seeder: missing semesters row '{$semesterName}'; skipping this term.");
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
            'CurriculumBshm20252026Seeder: ' . $subjectsCreated . ' subject(s) created, '
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
