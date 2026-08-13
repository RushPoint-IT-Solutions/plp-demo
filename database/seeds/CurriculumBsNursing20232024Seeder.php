<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumBsNursing20232024Seeder extends Seeder
{
    /**
     * Seeds the real "Revised Curriculum Academic Year 2023-2024" for BS
     * Nursing (Bachelor of Science in Nursing), transcribed from PLP's
     * official curriculum sheet: courses, units, lec/lab/hours, and
     * pre-requisites per year and term.
     *
     * Two codes were renamed from the source sheet to avoid colliding with
     * different, specific courses already seeded under the same code by
     * CurriculumAbPsych20252026Seeder: the generic "GE Elective 2"/"GE
     * Elective 3" slots (printed as GE 002 / GE 003) were renamed to
     * GEE 002 / GEE 003, matching the GEE-prefix pattern the same sheet
     * already uses for "GE Elective 1" (GEE 001).
     *
     * A handful of Second Semester Year 1 courses list "MC 0003" as a
     * pre-requisite, a code that does not correspond to any course on this
     * sheet (likely a print artifact) — those specific pre-requisite links
     * are skipped automatically since they can't resolve to a real subject.
     *
     * Run with: php artisan db:seed --class=CurriculumBsNursing20232024Seeder
     *
     * @return void
     */
    private const PROGRAM_CODE = 'BSN';
    private const CURRICULUM_YEAR = '2023-2024';

    private const CURRICULUM = [
        '1st Year' => [
            'First Semester' => [
                ['code' => 'GE 0001', 'name' => 'Understanding the Self', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 0002', 'name' => 'Reading in Philippine History', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 100', 'name' => 'Theoretical Foundation of Nursing', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NUR 101 A', 'name' => 'Anatomy and Physiology', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NUR 101 B', 'name' => 'Anatomy and Physiology (102 Hours)', 'prereq' => [], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'NUR 102 A', 'name' => 'Biochemistry', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NUR 102 B', 'name' => 'Biochemistry (102 Hours)', 'prereq' => [], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'GE 0004', 'name' => 'Mathematics in the Modern World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 1', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 1)', 'prereq' => [], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NSTP 1', 'name' => 'National Service Training Program 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'GEC 0005', 'name' => 'Purposive Communication', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 101 A', 'name' => 'Health Assessment', 'prereq' => ['MC 0003', 'NCM 100'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 101 B', 'name' => 'Health Assessment (102 Hours)', 'prereq' => ['MC 0003', 'NCM 100'], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'NCM 103 A', 'name' => 'Fundamentals of Nursing Practice', 'prereq' => ['MC 0003', 'NCM 100'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 103 B', 'name' => 'Related Learning Experience 103 (102 Hours)', 'prereq' => ['MC 0003', 'NCM 100'], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'NUR 103 A', 'name' => 'Microbiology and Parasitology', 'prereq' => ['MC 0003'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NUR 103 B', 'name' => 'Microbiology and Parasitology (51 Hours)', 'prereq' => ['MC 0003'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'GE 0008', 'name' => 'Ethics', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NSTP 2', 'name' => 'National Service Training Program 2', 'prereq' => ['NSTP 1'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 2', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 2)', 'prereq' => ['PE 1'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Summer Semester' => [
                ['code' => 'GE 0003', 'name' => 'The Contemporary World', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NUR 104', 'name' => 'Logic and Critical Thinking', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 105A', 'name' => 'Nutrition and Diet Therapy', 'prereq' => ['NCM 101 A', 'NCM 101 B'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 105B', 'name' => 'Nutrition and Diet Therapy (51 Hours)', 'prereq' => ['NCM 101 A', 'NCM 101 B'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
            ],
        ],
        '2nd Year' => [
            'First Semester' => [
                ['code' => 'NCM 104 A', 'name' => 'Community Health Nursing 1 (Individual and Family as Clients)', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 103 A'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 104 B', 'name' => 'Community Health Nursing 1 (Individual and Family as Clients) (102 Hours)', 'prereq' => ['NCM 100', 'NCM 101 B', 'NCM 103 B'], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'NCM 106', 'name' => 'Pharmacology', 'prereq' => ['NUR 102 A', 'NCM 101 A', 'NCM 103 A'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 107 A', 'name' => 'Care of Mother, Child, Adolescent (Well Clients)', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 103 A', 'NCM 105A'], 'lec' => 4, 'lab' => 0, 'units' => 4, 'hours' => 4],
                ['code' => 'NCM 107 B', 'name' => 'Related Learning Experience 107 (255 Hours)', 'prereq' => ['NCM 100', 'NCM 101 B', 'NCM 103 B', 'NCM 104 B'], 'lec' => 0, 'lab' => 5, 'units' => 5, 'hours' => 14],
                ['code' => 'NCM 108', 'name' => 'Health Care Ethics (Bioethics)', 'prereq' => ['NCM 100', 'GE 0008'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 102', 'name' => 'Health Education', 'prereq' => ['NCM 100'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GE 0007', 'name' => 'Science, Technology and Society', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 3', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 3)', 'prereq' => ['PE 2'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
            ],
            'Second Semester' => [
                ['code' => 'NCM 109 A', 'name' => 'Care of Mother, Child at Risk or with Problems (Acute and Chronic)', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 103 A', 'NCM 104 A'], 'lec' => 6, 'lab' => 0, 'units' => 6, 'hours' => 6],
                ['code' => 'NCM 109 B', 'name' => 'Related Learning Experience 109 (306 Hours)', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 103 A', 'NCM 104 A'], 'lec' => 0, 'lab' => 6, 'units' => 6, 'hours' => 17],
                ['code' => 'NCM 110 A', 'name' => 'Nursing Informatics', 'prereq' => ['NCM 107 A'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 110 B', 'name' => 'Nursing Informatics (51 Hours)', 'prereq' => ['NCM 107 A'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'TC ACT1', 'name' => 'Therapeutic Communication and Assessment of Critical Thinking 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'NCM 113 A', 'name' => 'Community Health Nursing 2 (Population Groups and Communities as Clients)', 'prereq' => ['NCM 107 A'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 113 B', 'name' => 'Community Health Nursing 2 (Population Groups and Communities as Clients) (51 Hours)', 'prereq' => ['NCM 107 A'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'GE 0006', 'name' => 'Art Appreciation', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 001', 'name' => 'GE Elective 1', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Summer Semester' => [
                ['code' => 'GEE 002', 'name' => 'GE Elective 2', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'PE 4', 'name' => 'Physical Activities Towards Health and Fitness (PATHFIT 4)', 'prereq' => ['PE 3'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'GE 009', 'name' => 'Life, Works and Writing of Rizal', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '3rd Year' => [
            'First Semester' => [
                ['code' => 'NCM 112 A', 'name' => 'Care of Clients with Problem in Oxygenation, Fluid and Electrolytes, Infectious, Inflammatory and Immunologic Response, Cellular Aberrations, Acute and Chronic', 'prereq' => ['NCM 109 A'], 'lec' => 8, 'lab' => 0, 'units' => 8, 'hours' => 8],
                ['code' => 'NCM 112 B', 'name' => 'Related Learning Experience 112 (306 Hours)', 'prereq' => ['NCM 109 A'], 'lec' => 0, 'lab' => 6, 'units' => 6, 'hours' => 17],
                ['code' => 'NCM 111', 'name' => 'Nursing Research 1 (51 Hours)', 'prereq' => ['NCM 110 A', 'NCM 109 A'], 'lec' => 2, 'lab' => 1, 'units' => 3, 'hours' => 5],
                ['code' => 'NCM 117 A', 'name' => 'Care of Clients with Maladaptive Patterns of Behavior, Acute and Chronic', 'prereq' => ['NCM 112 A'], 'lec' => 4, 'lab' => 0, 'units' => 4, 'hours' => 4],
                ['code' => 'NCM 117 B', 'name' => 'Related Learning Experience 117 (204 Hours)', 'prereq' => ['NCM 112 A'], 'lec' => 0, 'lab' => 4, 'units' => 4, 'hours' => 11.5],
                ['code' => 'NCM 120', 'name' => 'Decent Work Employment and Transcultural Nursing with Nursing Jurisprudence', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'NCM 115', 'name' => 'Nursing Research 2 (102 Hours)', 'prereq' => ['NCM 111'], 'lec' => 0, 'lab' => 2, 'units' => 2, 'hours' => 6],
                ['code' => 'NCM 116 A', 'name' => 'Care of Clients with Problems in Nutrition, and Gastro-intestinal, Metabolism and Endocrine, Perception and Coordination (Acute and Chronic)', 'prereq' => ['NCM 112 A'], 'lec' => 5, 'lab' => 0, 'units' => 5, 'hours' => 5],
                ['code' => 'NCM 116 B', 'name' => 'Related Learning Experience 116 (204 Hours)', 'prereq' => ['NCM 112 A'], 'lec' => 0, 'lab' => 4, 'units' => 4, 'hours' => 11.5],
                ['code' => 'NCM 114 A', 'name' => 'Care of Older Adult', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 102', 'NCM 103 A', 'NCM 104 A', 'NCM 106', 'NCM 107 A', 'NCM 109 A'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 114 B', 'name' => 'Care of Older Adult (Immersion-51 Hours)', 'prereq' => ['NCM 100', 'NCM 101 A', 'NCM 102', 'NCM 103 A', 'NCM 104 A', 'NCM 106', 'NCM 107 A', 'NCM 109 A'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'ACT 2', 'name' => 'Assessment of Critical Thinking 2', 'prereq' => ['TC ACT1'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
                ['code' => 'GEE 003', 'name' => 'GE Elective 3', 'prereq' => [], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
        ],
        '4th Year' => [
            'First Semester' => [
                ['code' => 'NCM 118 A', 'name' => 'Nursing Care of Clients with Life Threatening Conditions, Acutely Ill/Multi-organ problems, High Acuity and Emergency situations (Acute and Chronic)', 'prereq' => ['NCM 116 A'], 'lec' => 4, 'lab' => 0, 'units' => 4, 'hours' => 4],
                ['code' => 'NCM 118 B', 'name' => 'Related Learning Experience 118 (255 Hours)', 'prereq' => ['NCM 116 A'], 'lec' => 0, 'lab' => 5, 'units' => 5, 'hours' => 14],
                ['code' => 'NCM 121 A', 'name' => 'Disaster Nursing (36 Hours)', 'prereq' => ['NCM 119 A'], 'lec' => 2, 'lab' => 0, 'units' => 2, 'hours' => 2],
                ['code' => 'NCM 121 B', 'name' => 'Related Learning Experience 121 (51 Hours)', 'prereq' => ['NCM 119 A'], 'lec' => 0, 'lab' => 1, 'units' => 1, 'hours' => 3],
                ['code' => 'NCM 119 A', 'name' => 'Nursing Leadership and Management', 'prereq' => ['NCM 112 A', 'NCM 117 A'], 'lec' => 4, 'lab' => 0, 'units' => 4, 'hours' => 4],
                ['code' => 'NCM 119 B', 'name' => 'Related Learning Experience 119 (153 Hours)', 'prereq' => ['NCM 116 B', 'NCM 117 B'], 'lec' => 0, 'lab' => 3, 'units' => 3, 'hours' => 8.5],
                ['code' => 'ACT 3', 'name' => 'Assessment of Critical Thinking 3', 'prereq' => ['ACT 2'], 'lec' => 3, 'lab' => 0, 'units' => 3, 'hours' => 3],
            ],
            'Second Semester' => [
                ['code' => 'NCM 122', 'name' => 'Intensive Nursing Practicum (Hospital and Community settings) (408 Hours)', 'prereq' => ['NCM 119 A'], 'lec' => 0, 'lab' => 8, 'units' => 8, 'hours' => 23],
            ],
        ],
    ];

    public function run()
    {
        $course = DB::table('courses')->where('code', self::PROGRAM_CODE)->first();
        if (!$course) {
            $this->command->warn('CurriculumBsNursing20232024Seeder: program ' . self::PROGRAM_CODE . ' not found; skipping.');
            return;
        }

        $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label');
        $semesterIds = DB::table('semesters')->pluck('id', 'name');

        foreach (array_keys(self::CURRICULUM) as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                $this->command->warn("CurriculumBsNursing20232024Seeder: missing year_blocks row '{$yearLabel}'; skipping.");
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
            'date_to' => '2024-05-31',
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
                    $this->command->warn("CurriculumBsNursing20232024Seeder: missing semesters row '{$semesterName}'; skipping this term.");
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
            'CurriculumBsNursing20232024Seeder: ' . $subjectsCreated . ' subject(s) created, '
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
