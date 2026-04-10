<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassListStudentEnrollmentBackfillSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('students') || !Schema::hasTable('subjects') || !Schema::hasTable('student_subject')) {
            $this->command->warn('ClassListStudentEnrollmentBackfillSeeder: required tables are missing; skipping.');
            return;
        }

        $yearMap = $this->buildYearMap();
        $pools = $this->buildStudentPools($yearMap);

        if (!count($pools['global'])) {
            $this->command->warn('ClassListStudentEnrollmentBackfillSeeder: no students available to link.');
            return;
        }

        $touched = 0;
        $subjectsProcessed = 0;

        DB::table('subjects')
            ->select(['id', 'course_id', 'academic_term_id', 'year_section'])
            ->orderBy('id')
            ->chunkById(300, function ($subjects) use (&$touched, &$subjectsProcessed, $pools) {
                foreach ($subjects as $subject) {
                    $subjectsProcessed++;

                    $courseId = (int) $subject->course_id;
                    $termId = (int) $subject->academic_term_id;
                    $yearNumber = $this->extractYearNumber((string) $subject->year_section);

                    $candidates = $this->candidateStudentIds($pools, $courseId, $termId, $yearNumber);
                    if (!count($candidates)) {
                        continue;
                    }

                    $selectedStudentIds = $this->pickStudentIdsForSubject((int) $subject->id, $candidates);
                    if (!count($selectedStudentIds)) {
                        continue;
                    }

                    foreach ($selectedStudentIds as $studentId) {
                        DB::table('student_subject')->updateOrInsert(
                            [
                                'student_id' => (int) $studentId,
                                'subject_id' => (int) $subject->id,
                            ],
                            [
                                'student_id' => (int) $studentId,
                                'subject_id' => (int) $subject->id,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );

                        $touched++;
                    }
                }
            }, 'id');

        $fallbackLinked = $this->ensureEveryStudentHasAtLeastOneSubjectLink();

        $this->command->info(
            'ClassListStudentEnrollmentBackfillSeeder: processed '
            . $subjectsProcessed
            . ' subject(s) and synchronized '
            . $touched
            . ' student-subject link(s); fallback-linked '
            . $fallbackLinked
            . ' previously unlinked student(s).'
        );
    }

    private function ensureEveryStudentHasAtLeastOneSubjectLink()
    {
        $subjectPools = $this->buildSubjectPools();
        if (!count($subjectPools['global'])) {
            return 0;
        }

        $linked = 0;

        DB::table('students')
            ->select(['id', 'course_id', 'academic_term_id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$linked, $subjectPools) {
                foreach ($rows as $row) {
                    $studentId = (int) $row->id;

                    $hasSubject = DB::table('student_subject')
                        ->where('student_id', $studentId)
                        ->exists();

                    if ($hasSubject) {
                        continue;
                    }

                    $subjectId = $this->pickFallbackSubjectId(
                        $studentId,
                        $subjectPools,
                        (int) $row->course_id,
                        (int) $row->academic_term_id
                    );

                    if ($subjectId <= 0) {
                        continue;
                    }

                    DB::table('student_subject')->updateOrInsert(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                        ],
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    $linked++;
                }
            }, 'id');

        return $linked;
    }

    private function buildSubjectPools()
    {
        $pools = [
            'strict' => [],
            'course' => [],
            'term' => [],
            'global' => [],
        ];

        DB::table('subjects')
            ->select(['id', 'course_id', 'academic_term_id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$pools) {
                foreach ($rows as $row) {
                    $subjectId = (int) $row->id;
                    $courseId = (int) $row->course_id;
                    $termId = (int) $row->academic_term_id;

                    $pools['global'][] = $subjectId;

                    if ($courseId > 0) {
                        if (!isset($pools['course'][$courseId])) {
                            $pools['course'][$courseId] = [];
                        }

                        $pools['course'][$courseId][] = $subjectId;
                    }

                    if ($termId > 0) {
                        if (!isset($pools['term'][$termId])) {
                            $pools['term'][$termId] = [];
                        }

                        $pools['term'][$termId][] = $subjectId;
                    }

                    if ($courseId > 0 && $termId > 0) {
                        if (!isset($pools['strict'][$courseId])) {
                            $pools['strict'][$courseId] = [];
                        }

                        if (!isset($pools['strict'][$courseId][$termId])) {
                            $pools['strict'][$courseId][$termId] = [];
                        }

                        $pools['strict'][$courseId][$termId][] = $subjectId;
                    }
                }
            }, 'id');

        $pools['global'] = array_values(array_unique($pools['global']));

        foreach ($pools['course'] as $courseId => $subjectIds) {
            $pools['course'][$courseId] = array_values(array_unique($subjectIds));
        }

        foreach ($pools['term'] as $termId => $subjectIds) {
            $pools['term'][$termId] = array_values(array_unique($subjectIds));
        }

        foreach ($pools['strict'] as $courseId => $termMap) {
            foreach ($termMap as $termId => $subjectIds) {
                $pools['strict'][$courseId][$termId] = array_values(array_unique($subjectIds));
            }
        }

        return $pools;
    }

    private function pickFallbackSubjectId($seed, array $pools, $courseId, $termId)
    {
        $candidates = [];

        if (
            $courseId > 0
            && $termId > 0
            && isset($pools['strict'][$courseId][$termId])
            && count($pools['strict'][$courseId][$termId])
        ) {
            $candidates = $pools['strict'][$courseId][$termId];
        } elseif ($termId > 0 && isset($pools['term'][$termId]) && count($pools['term'][$termId])) {
            $candidates = $pools['term'][$termId];
        } elseif ($courseId > 0 && isset($pools['course'][$courseId]) && count($pools['course'][$courseId])) {
            $candidates = $pools['course'][$courseId];
        } else {
            $candidates = $pools['global'];
        }

        if (!count($candidates)) {
            return 0;
        }

        return (int) $candidates[$seed % count($candidates)];
    }

    private function buildYearMap()
    {
        if (!Schema::hasTable('year_blocks')) {
            return [];
        }

        return DB::table('year_blocks')
            ->orderBy('id')
            ->get(['id', 'label'])
            ->mapWithKeys(function ($row) {
                return [(int) $row->id => $this->extractYearNumber((string) $row->label)];
            })
            ->all();
    }

    private function buildStudentPools(array $yearMap)
    {
        $pools = [
            'strict' => [],
            'course_term' => [],
            'course' => [],
            'term' => [],
            'global' => [],
        ];

        DB::table('students')
            ->select(['id', 'course_id', 'academic_term_id', 'year_block_id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$pools, $yearMap) {
                foreach ($rows as $row) {
                    $studentId = (int) $row->id;
                    $courseId = (int) $row->course_id;
                    $termId = (int) $row->academic_term_id;
                    $yearBlockId = (int) $row->year_block_id;
                    $yearNumber = array_key_exists($yearBlockId, $yearMap) ? (int) $yearMap[$yearBlockId] : 0;

                    $pools['global'][] = $studentId;

                    if ($courseId > 0) {
                        if (!isset($pools['course'][$courseId])) {
                            $pools['course'][$courseId] = [];
                        }
                        $pools['course'][$courseId][] = $studentId;
                    }

                    if ($termId > 0) {
                        if (!isset($pools['term'][$termId])) {
                            $pools['term'][$termId] = [];
                        }
                        $pools['term'][$termId][] = $studentId;
                    }

                    if ($courseId > 0 && $termId > 0) {
                        if (!isset($pools['course_term'][$courseId])) {
                            $pools['course_term'][$courseId] = [];
                        }

                        if (!isset($pools['course_term'][$courseId][$termId])) {
                            $pools['course_term'][$courseId][$termId] = [];
                        }

                        $pools['course_term'][$courseId][$termId][] = $studentId;

                        if ($yearNumber > 0) {
                            if (!isset($pools['strict'][$courseId])) {
                                $pools['strict'][$courseId] = [];
                            }

                            if (!isset($pools['strict'][$courseId][$termId])) {
                                $pools['strict'][$courseId][$termId] = [];
                            }

                            if (!isset($pools['strict'][$courseId][$termId][$yearNumber])) {
                                $pools['strict'][$courseId][$termId][$yearNumber] = [];
                            }

                            $pools['strict'][$courseId][$termId][$yearNumber][] = $studentId;
                        }
                    }
                }
            }, 'id');

        $pools['global'] = array_values(array_unique($pools['global']));

        foreach ($pools['course'] as $courseId => $studentIds) {
            $pools['course'][$courseId] = array_values(array_unique($studentIds));
        }

        foreach ($pools['term'] as $termId => $studentIds) {
            $pools['term'][$termId] = array_values(array_unique($studentIds));
        }

        foreach ($pools['course_term'] as $courseId => $termMap) {
            foreach ($termMap as $termId => $studentIds) {
                $pools['course_term'][$courseId][$termId] = array_values(array_unique($studentIds));
            }
        }

        foreach ($pools['strict'] as $courseId => $termMap) {
            foreach ($termMap as $termId => $yearMapRows) {
                foreach ($yearMapRows as $yearNumber => $studentIds) {
                    $pools['strict'][$courseId][$termId][$yearNumber] = array_values(array_unique($studentIds));
                }
            }
        }

        return $pools;
    }

    private function candidateStudentIds(array $pools, $courseId, $termId, $yearNumber)
    {
        if (
            $courseId > 0
            && $termId > 0
            && $yearNumber > 0
            && isset($pools['strict'][$courseId][$termId][$yearNumber])
            && count($pools['strict'][$courseId][$termId][$yearNumber])
        ) {
            return $pools['strict'][$courseId][$termId][$yearNumber];
        }

        if (
            $courseId > 0
            && $termId > 0
            && isset($pools['course_term'][$courseId][$termId])
            && count($pools['course_term'][$courseId][$termId])
        ) {
            return $pools['course_term'][$courseId][$termId];
        }

        if ($termId > 0 && isset($pools['term'][$termId]) && count($pools['term'][$termId])) {
            return $pools['term'][$termId];
        }

        if ($courseId > 0 && isset($pools['course'][$courseId]) && count($pools['course'][$courseId])) {
            return $pools['course'][$courseId];
        }

        return $pools['global'];
    }

    private function pickStudentIdsForSubject($subjectId, array $candidateIds)
    {
        $count = count($candidateIds);
        if (!$count) {
            return [];
        }

        $target = min($count, 20);
        $start = $subjectId % $count;
        $selected = [];

        for ($i = 0; $i < $target; $i++) {
            $index = ($start + $i) % $count;
            $selected[] = (int) $candidateIds[$index];
        }

        return array_values(array_unique($selected));
    }

    private function extractYearNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        if (preg_match('/([1-9])/', $value, $matches)) {
            return (int) $matches[1];
        }

        $normalized = strtolower($value);
        if (strpos($normalized, 'first') !== false) {
            return 1;
        }

        if (strpos($normalized, 'second') !== false) {
            return 2;
        }

        if (strpos($normalized, 'third') !== false) {
            return 3;
        }

        if (strpos($normalized, 'fourth') !== false) {
            return 4;
        }

        return 0;
    }
}