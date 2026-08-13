<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBsece20252026Seeder extends Seeder
{
    /**
     * Seeds the real "Academic Year 2025-2026" (Revised Curriculum Entry
     * Year 2025) for BS Electronics Engineering, transcribed from PLP's
     * official curriculum sheet (2 pages): courses, units, lec/lab/hours,
     * and pre-requisites per year and term.
     *
     * Second Year Second Semester's "GE Elective 2" row is printed with the
     * same code as Year 1 First Semester's "Readings in Philippine History"
     * (GE 002) — a duplicate/typo. It is seeded here as GEE 002 to match
     * the GEE-prefix elective-slot pattern (GEE 001, GEE 003) already used
     * on this sheet; this is also confirmed by the sheet's own summary
     * table, which counts exactly 3 GE electives toward its 36-unit
     * General Education total.
     *
     * Printed "co-req" references (e.g. "co-req: ECE 122") are seeded as
     * ordinary pre-requisite links, consistent with how every other
     * curriculum seeder in this project links requisites (this project's
     * schema only populates the 'pre' requisite type).
     *
     * "3rd Yr Standing" and "4th Yr Standing" printed requirements are not
     * course codes, so they are left empty rather than treated as
     * pre-requisite subjects.
     *
     * Run with: php artisan db:seed --class=CurriculumBsece20252026Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSECE';
    private const CURRICULUM_YEAR = '2025-2026';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 002', 'name' => 'Readings in Philippine History', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 003', 'name' => 'The Contemporary World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 100', 'name' => 'Engineering Fundamentals', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 101', 'name' => 'Engineering Mathematics', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 111', 'name' => 'Calculus I', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 121', 'name' => 'Chemistry for Engineers', 'prereq' => [], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 131', 'name' => 'Computer Aided Drafting', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 142a', 'name' => 'Computer Programming 1', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'PE 1', 'name' => 'Physical Activities Toward Health and Fitness (PATHFit 1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 1', 'name' => 'National Service Training Program (CWTS/ROTC)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GE 004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 006', 'name' => 'Art Appreciation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 112', 'name' => 'Calculus II', 'prereq' => ['ECE 111'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 122', 'name' => 'Physics for Engineers', 'prereq' => ['ECE 111'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 141', 'name' => 'Physics II', 'prereq' => ['ECE 122'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 142b', 'name' => 'Computer Programming 2', 'prereq' => ['ECE 142a'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'PE 2', 'name' => 'Physical Activities Toward Health and Fitness (PATHFit 2)', 'prereq' => ['PE 1'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 2', 'name' => 'National Service Training Program (CWTS/ROTC)', 'prereq' => ['NSTP 1'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'GE 007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 001', 'name' => 'GE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 113', 'name' => 'Engineering Data Analysis', 'prereq' => [], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 114', 'name' => 'Differential Equations', 'prereq' => ['ECE 112'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 143', 'name' => 'Material Science and Engineering', 'prereq' => ['ECE 121'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 144', 'name' => 'Circuits 1', 'prereq' => ['ECE 141'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 151', 'name' => 'Electronics 1: Electronic Devices and Circuits', 'prereq' => ['ECE 144'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'PE 3', 'name' => 'Physical Activities Toward Health and Fitness (PATHFit 3)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'GE 009', 'name' => 'Life and Works of Rizal', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 002', 'name' => 'GE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 145', 'name' => 'Circuits II', 'prereq' => ['ECE 144'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 152', 'name' => 'Advanced Engineering Mathematics', 'prereq' => ['ECE 114'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 153', 'name' => 'Electromagnetics', 'prereq' => ['ECE 114'], 'lec' => 4, 'lab' => 0, 'units' => 4, 'hours' => 4],
                ['code' => 'ECE 154', 'name' => 'Electronics 2: Electronic Circuit Analysis and Design', 'prereq' => ['ECE 151'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 155', 'name' => 'Communications 1: Principles of Communication Systems', 'prereq' => ['ECE 154'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'PE 4', 'name' => 'Physical Activities Toward Health and Fitness (PATHFit 4)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'GEE 003', 'name' => 'GE Elective 3', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 156', 'name' => 'ECE Laws, Contracts, Ethics, Standards and Safety', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 157', 'name' => 'Electronics 3: Electronic Systems and Design', 'prereq' => ['ECE 154'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 158', 'name' => 'Signals, Spectra, and Signal Processing', 'prereq' => ['ECE 152'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 159', 'name' => 'Communications 2: Modulation and Coding Techniques', 'prereq' => ['ECE 155'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 160', 'name' => 'Digital Electronics 1: Logic Circuits and Switching Theory', 'prereq' => ['ECE 151'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 165', 'name' => 'Methods of Research', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'ECE 146', 'name' => 'Environmental Science and Engineering', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 132', 'name' => 'Engineering Economics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 133', 'name' => 'Engineering Management', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'ECE 134', 'name' => 'Technopreneurship 101', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'ECE 161', 'name' => 'Communications 3: Data Communications', 'prereq' => ['ECE 159'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 162', 'name' => 'Communications 4: Transmission Media and Antenna System and Design', 'prereq' => ['ECE 159'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 163', 'name' => 'Digital Electronics 2: Microprocessor, Microcontroller Systems and Design', 'prereq' => ['ECE 160'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 166', 'name' => 'Design 1/Capstone Project 1', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
            ],
            'Summer Semester' => [
                ['code' => 'ECE 301', 'name' => 'On-the-Job Training (240 hours)', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'ECE 164', 'name' => 'Feedback and Control Systems', 'prereq' => ['ECE 152'], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 406', 'name' => 'Microwave Communication System Design', 'prereq' => ['ECE 159'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 167', 'name' => 'Design 2/Capstone Project 2', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 204', 'name' => 'ECE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 408', 'name' => 'ECE Synthesis 1', 'prereq' => [], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
            ],
            'Second Semester' => [
                ['code' => 'ECE 407', 'name' => 'Broadcast Engineering and Acoustics', 'prereq' => ['ECE 162'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 168', 'name' => 'Seminars/Colloquium', 'prereq' => [], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ECE 205', 'name' => 'ECE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 1, 'units' => 4, 'hours' => 6],
                ['code' => 'ECE 409', 'name' => 'ECE Synthesis 2', 'prereq' => [], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBsece20252026Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBsece20252026Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
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
            'date_to' => '2029-05-31',
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
                    $this->command->warn("CurriculumBsece20252026Seeder: missing semesters row '{$semesterName}'; skipping this term.");
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
            'CurriculumBsece20252026Seeder: ' . $subjectsCreated . ' subject(s) created, '
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
