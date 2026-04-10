<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassListDataBackfillSeeder extends Seeder
{
    public function run()
    {
        list($termIdsByYear, $orderedTermIds, $termLabelsById) = $this->ensureAcademicTermsWithStandardSemesters();

        $this->syncSystemSchoolSemesters($termIdsByYear, $termLabelsById);

        $updatedSubjects = $this->backfillSubjects($orderedTermIds);

        $this->command->info(
            'ClassListDataBackfillSeeder: synchronized school-year semesters and backfilled '
            . $updatedSubjects
            . ' subject schedule/class row(s).'
        );
    }

    private function ensureAcademicTermsWithStandardSemesters()
    {
        $schoolYears = DB::table('academic_terms')
            ->whereNotNull('school_year')
            ->whereRaw("TRIM(school_year) <> ''")
            ->orderBy('school_year', 'desc')
            ->pluck('school_year')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->values()
            ->all();

        if (!count($schoolYears)) {
            $yearStart = (int) date('Y');
            $schoolYears = [$yearStart . '-' . ($yearStart + 1)];
        }

        $canonicalLabels = [
            'First' => 'First Semester',
            'Second' => 'Second Semester',
            'Summer' => 'Summer',
        ];

        $termIdsByYear = [];
        $termLabelsById = [];

        foreach ($schoolYears as $schoolYear) {
            $rows = DB::table('academic_terms')
                ->where('school_year', $schoolYear)
                ->orderBy('id')
                ->get(['id', 'term']);

            $termIdByNormalized = [];
            foreach ($rows as $row) {
                $normalized = $this->normalizeSemesterValue($row->term);
                if ($normalized === '' || isset($termIdByNormalized[$normalized])) {
                    continue;
                }

                $termIdByNormalized[$normalized] = (int) $row->id;
                $termLabelsById[(int) $row->id] = (string) $row->term;
            }

            foreach ($canonicalLabels as $normalized => $label) {
                if (!isset($termIdByNormalized[$normalized])) {
                    $canonicalKey = strtolower(trim((string) $schoolYear) . '|' . trim((string) $label));

                    $existing = DB::table('academic_terms')
                        ->where('canonical_key', $canonicalKey)
                        ->first(['id', 'term']);

                    if ($existing) {
                        $termIdByNormalized[$normalized] = (int) $existing->id;
                        $termLabelsById[(int) $existing->id] = (string) $existing->term;
                    } else {
                        $newId = (int) DB::table('academic_terms')->insertGetId([
                            'school_year' => $schoolYear,
                            'term' => $label,
                            'canonical_key' => $canonicalKey,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $termIdByNormalized[$normalized] = $newId;
                        $termLabelsById[$newId] = $label;
                    }
                }

                $termIdsByYear[$schoolYear][$normalized] = $termIdByNormalized[$normalized];
            }
        }

        $orderedTermIds = [];
        foreach ($schoolYears as $schoolYear) {
            foreach (['First', 'Second', 'Summer'] as $normalized) {
                if (isset($termIdsByYear[$schoolYear][$normalized])) {
                    $orderedTermIds[] = (int) $termIdsByYear[$schoolYear][$normalized];
                }
            }
        }

        $orderedTermIds = array_values(array_unique($orderedTermIds));

        return [$termIdsByYear, $orderedTermIds, $termLabelsById];
    }

    private function syncSystemSchoolSemesters(array $termIdsByYear, array $termLabelsById)
    {
        if (!Schema::hasTable('system_school_semesters')) {
            return;
        }

        $hasAcademicTermId = Schema::hasColumn('system_school_semesters', 'academic_term_id');
        $hasSchoolYear = Schema::hasColumn('system_school_semesters', 'school_year');
        $hasSemester = Schema::hasColumn('system_school_semesters', 'semester');
        $hasCreatedAt = Schema::hasColumn('system_school_semesters', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('system_school_semesters', 'updated_at');

        if (!$hasAcademicTermId && !$hasSchoolYear && !$hasSemester) {
            return;
        }

        foreach ($termIdsByYear as $schoolYear => $terms) {
            foreach (['First', 'Second', 'Summer'] as $normalized) {
                if (!isset($terms[$normalized])) {
                    continue;
                }

                $termId = (int) $terms[$normalized];
                $termLabel = isset($termLabelsById[$termId]) ? (string) $termLabelsById[$termId] : $this->labelFromNormalized($normalized);

                $rowId = null;

                if ($hasAcademicTermId) {
                    $rowId = DB::table('system_school_semesters')
                        ->where('academic_term_id', $termId)
                        ->value('id');
                }

                if (!$rowId && $hasSchoolYear && $hasSemester) {
                    $candidates = DB::table('system_school_semesters')
                        ->where('school_year', $schoolYear)
                        ->orderBy('id')
                        ->get(['id', 'semester']);

                    foreach ($candidates as $candidate) {
                        if ($this->normalizeSemesterValue($candidate->semester) === $normalized) {
                            $rowId = (int) $candidate->id;
                            break;
                        }
                    }
                }

                $payload = [];
                if ($hasAcademicTermId) {
                    $payload['academic_term_id'] = $termId;
                }
                if ($hasSchoolYear) {
                    $payload['school_year'] = $schoolYear;
                }
                if ($hasSemester) {
                    $payload['semester'] = $termLabel;
                }
                if ($hasUpdatedAt) {
                    $payload['updated_at'] = now();
                }

                if (!$rowId) {
                    if ($hasCreatedAt) {
                        $payload['created_at'] = now();
                    }
                    DB::table('system_school_semesters')->insert($payload);
                    continue;
                }

                DB::table('system_school_semesters')
                    ->where('id', (int) $rowId)
                    ->update($payload);
            }
        }
    }

    private function backfillSubjects(array $orderedTermIds)
    {
        if (!Schema::hasTable('subjects')) {
            return 0;
        }

        $hasDays = Schema::hasColumn('subjects', 'days');
        $hasTimeStart = Schema::hasColumn('subjects', 'time_start');
        $hasTimeEnd = Schema::hasColumn('subjects', 'time_end');
        $hasRoom = Schema::hasColumn('subjects', 'room');
        $hasCourseId = Schema::hasColumn('subjects', 'course_id');
        $hasYearSection = Schema::hasColumn('subjects', 'year_section');
        $hasAcademicTermId = Schema::hasColumn('subjects', 'academic_term_id');
        $hasFacultyId = Schema::hasColumn('subjects', 'faculty_id');
        $hasUpdatedAt = Schema::hasColumn('subjects', 'updated_at');

        $courseIds = $hasCourseId
            ? DB::table('courses')->orderBy('id')->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all()
            : [];

        $facultyIds = $hasFacultyId
            ? DB::table('faculties')->orderBy('id')->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all()
            : [];

        $roomValues = [];
        if ($hasRoom && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_number')) {
            $roomValues = DB::table('rooms')
                ->whereNotNull('room_number')
                ->orderBy('id')
                ->pluck('room_number')
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->values()
                ->all();
        }

        if (!count($roomValues)) {
            $roomValues = ['101', '102', '201', '202', '301', '302'];
        }

        if ($hasAcademicTermId && !count($orderedTermIds)) {
            $orderedTermIds = DB::table('academic_terms')
                ->orderBy('school_year', 'desc')
                ->orderBy('id')
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->values()
                ->all();
        }

        $dayOptions = ['M,W', 'T,TH', 'W,F', 'TH,F', 'S'];
        $timeSlots = [
            ['07:00AM', '08:30AM'],
            ['08:30AM', '10:00AM'],
            ['10:00AM', '11:30AM'],
            ['01:00PM', '02:30PM'],
            ['02:30PM', '04:00PM'],
            ['04:00PM', '05:30PM'],
        ];
        $yearSections = ['1-A', '1-B', '2-A', '2-B', '3-A', '3-B', '4-A', '4-B'];

        $updated = 0;

        $query = DB::table('subjects')->select([
            'id',
            'days',
            'time_start',
            'time_end',
            'room',
            'course_id',
            'year_section',
            'academic_term_id',
            'faculty_id',
        ]);

        $query->where(function ($inner) use (
            $hasDays,
            $hasTimeStart,
            $hasTimeEnd,
            $hasRoom,
            $hasCourseId,
            $hasYearSection,
            $hasAcademicTermId,
            $hasFacultyId
        ) {
            $hasCondition = false;

            if ($hasDays) {
                $inner->orWhereNull('days')->orWhereRaw("TRIM(days) = ''");
                $hasCondition = true;
            }

            if ($hasTimeStart) {
                $inner->orWhereNull('time_start')->orWhereRaw("TRIM(time_start) = ''");
                $hasCondition = true;
            }

            if ($hasTimeEnd) {
                $inner->orWhereNull('time_end')->orWhereRaw("TRIM(time_end) = ''");
                $hasCondition = true;
            }

            if ($hasRoom) {
                $inner->orWhereNull('room')->orWhereRaw("TRIM(room) = ''");
                $hasCondition = true;
            }

            if ($hasCourseId) {
                $inner->orWhereNull('course_id');
                $hasCondition = true;
            }

            if ($hasYearSection) {
                $inner->orWhereNull('year_section')->orWhereRaw("TRIM(year_section) = ''");
                $hasCondition = true;
            }

            if ($hasAcademicTermId) {
                $inner->orWhereNull('academic_term_id');
                $hasCondition = true;
            }

            if ($hasFacultyId) {
                $inner->orWhereNull('faculty_id');
                $hasCondition = true;
            }

            if (!$hasCondition) {
                $inner->whereRaw('1 = 0');
            }
        });

        $query->orderBy('id')->chunkById(500, function ($rows) use (
            &$updated,
            $hasDays,
            $hasTimeStart,
            $hasTimeEnd,
            $hasRoom,
            $hasCourseId,
            $hasYearSection,
            $hasAcademicTermId,
            $hasFacultyId,
            $hasUpdatedAt,
            $dayOptions,
            $timeSlots,
            $roomValues,
            $courseIds,
            $yearSections,
            $orderedTermIds,
            $facultyIds
        ) {
            foreach ($rows as $row) {
                $id = (int) $row->id;
                $scheduleIndex = $id % count($timeSlots);
                $slot = $timeSlots[$scheduleIndex];

                $update = [];

                if ($hasDays && (is_null($row->days) || trim((string) $row->days) === '')) {
                    $update['days'] = $dayOptions[$id % count($dayOptions)];
                }

                if ($hasTimeStart && (is_null($row->time_start) || trim((string) $row->time_start) === '')) {
                    $update['time_start'] = $slot[0];
                }

                if ($hasTimeEnd && (is_null($row->time_end) || trim((string) $row->time_end) === '')) {
                    $update['time_end'] = $slot[1];
                }

                if ($hasRoom && (is_null($row->room) || trim((string) $row->room) === '')) {
                    $update['room'] = $roomValues[$id % count($roomValues)];
                }

                if ($hasCourseId && is_null($row->course_id) && count($courseIds)) {
                    $update['course_id'] = $courseIds[$id % count($courseIds)];
                }

                if ($hasYearSection && (is_null($row->year_section) || trim((string) $row->year_section) === '')) {
                    $update['year_section'] = $yearSections[$id % count($yearSections)];
                }

                if ($hasAcademicTermId && is_null($row->academic_term_id) && count($orderedTermIds)) {
                    $update['academic_term_id'] = $orderedTermIds[$id % count($orderedTermIds)];
                }

                if ($hasFacultyId && is_null($row->faculty_id) && count($facultyIds)) {
                    $update['faculty_id'] = $facultyIds[$id % count($facultyIds)];
                }

                if (!count($update)) {
                    continue;
                }

                if ($hasUpdatedAt) {
                    $update['updated_at'] = now();
                }

                DB::table('subjects')->where('id', $id)->update($update);
                $updated++;
            }
        }, 'id');

        return $updated;
    }

    private function normalizeSemesterValue($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'summer') !== false) {
            return 'Summer';
        }

        if (strpos($normalized, 'second') !== false || strpos($normalized, '2nd') !== false || $normalized === '2') {
            return 'Second';
        }

        if (strpos($normalized, 'first') !== false || strpos($normalized, '1st') !== false || $normalized === '1') {
            return 'First';
        }

        return '';
    }

    private function labelFromNormalized($normalized)
    {
        if ($normalized === 'First') {
            return 'First Semester';
        }

        if ($normalized === 'Second') {
            return 'Second Semester';
        }

        if ($normalized === 'Summer') {
            return 'Summer';
        }

        return (string) $normalized;
    }
}
