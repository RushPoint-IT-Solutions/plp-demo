<?php

namespace App\Support;

use App\AcademicTerm;
use App\SystemSchoolSemester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemConfigSchoolTermOptions
{
    const DEFAULT_SEMESTERS = ['First', 'Second', 'Summer'];

    public static function resolveOptions(): array
    {
        $schoolYears = [];
        $semesterMap = [];

        $appendOption = function ($schoolYear, $semester) use (&$schoolYears, &$semesterMap) {
            $normalizedYear = trim((string) $schoolYear);
            $normalizedSemester = self::normalizeSemester((string) $semester);

            if ($normalizedYear === '' || $normalizedSemester === '') {
                return;
            }

            if (!array_key_exists($normalizedYear, $semesterMap)) {
                $semesterMap[$normalizedYear] = [];
                $schoolYears[] = $normalizedYear;
            }

            if (!in_array($normalizedSemester, $semesterMap[$normalizedYear], true)) {
                $semesterMap[$normalizedYear][] = $normalizedSemester;
            }
        };

        if (Schema::hasTable('system_school_semesters')) {
            $hasSchoolYearColumn = Schema::hasColumn('system_school_semesters', 'school_year');
            $hasSemesterColumn = Schema::hasColumn('system_school_semesters', 'semester');
            $hasAcademicTermColumn = Schema::hasColumn('system_school_semesters', 'academic_term_id');

            $selectColumns = ['id'];
            if ($hasSchoolYearColumn) {
                $selectColumns[] = 'school_year';
            }
            if ($hasSemesterColumn) {
                $selectColumns[] = 'semester';
            }
            if ($hasAcademicTermColumn) {
                $selectColumns[] = 'academic_term_id';
            }

            $configRowsQuery = SystemSchoolSemester::query()
                ->select($selectColumns)
                ->orderByDesc('id');

            if ($hasAcademicTermColumn) {
                $configRowsQuery->with('academicTerm:id,school_year,term');
            }

            $configRows = $configRowsQuery->get();

            foreach ($configRows as $configRow) {
                $schoolYear = $hasSchoolYearColumn
                    ? trim((string) $configRow->getAttribute('school_year'))
                    : '';
                $semester = $hasSemesterColumn
                    ? (string) $configRow->getAttribute('semester')
                    : '';

                if (($schoolYear === '' || trim($semester) === '') && $configRow->academicTerm) {
                    if ($schoolYear === '') {
                        $schoolYear = trim((string) $configRow->academicTerm->school_year);
                    }

                    if (trim($semester) === '') {
                        $semester = (string) $configRow->academicTerm->term;
                    }
                }

                $appendOption($schoolYear, $semester);
            }

            if (!count($schoolYears)
                && $hasAcademicTermColumn
                && Schema::hasTable('academic_terms')) {
                $linkedTerms = DB::table('system_school_semesters as sss')
                    ->join('academic_terms as at', 'at.id', '=', 'sss.academic_term_id')
                    ->select(['at.school_year', 'at.term'])
                    ->orderBy('sss.id', 'desc')
                    ->get();

                foreach ($linkedTerms as $termRow) {
                    $appendOption(
                        (string) ($termRow->school_year ?? ''),
                        (string) ($termRow->term ?? '')
                    );
                }
            }
        }

        // Fallback only when Admin Tools configuration has no rows yet.
        if (!count($schoolYears) && Schema::hasTable('academic_terms')) {
            $academicTerms = AcademicTerm::query()
                ->orderBy('school_year', 'desc')
                ->orderByRaw(self::termSortSql())
                ->orderBy('term')
                ->get(['school_year', 'term']);

            foreach ($academicTerms as $academicTerm) {
                $appendOption((string) $academicTerm->school_year, (string) $academicTerm->term);
            }
        }

        foreach ($semesterMap as $schoolYear => $semesters) {
            $normalizedSemesters = array_values(array_filter(array_map(function ($semester) {
                return self::normalizeSemester((string) $semester);
            }, (array) $semesters), function ($semester) {
                return $semester !== '';
            }));

            usort($normalizedSemesters, function ($left, $right) {
                return self::semesterWeight((string) $left) <=> self::semesterWeight((string) $right);
            });

            $semesterMap[$schoolYear] = array_values(array_unique($normalizedSemesters));
        }

        $schoolYears = array_values(array_unique(array_filter(array_map(function ($schoolYear) {
            return trim((string) $schoolYear);
        }, $schoolYears), function ($schoolYear) {
            return $schoolYear !== '';
        })));

        rsort($schoolYears);

        $orderedSemesterMap = [];
        foreach ($schoolYears as $schoolYear) {
            $orderedSemesterMap[$schoolYear] = array_key_exists($schoolYear, $semesterMap)
                ? array_values($semesterMap[$schoolYear])
                : [];
        }

        $semesterMap = $orderedSemesterMap;

        if (!count($schoolYears)) {
            $yearStart = (int) date('Y');
            $fallbackYear = $yearStart . '-' . ($yearStart + 1);
            $schoolYears = [$fallbackYear];
            $semesterMap = [
                $fallbackYear => self::DEFAULT_SEMESTERS,
            ];
        }

        $defaultSchoolYear = count($schoolYears) ? (string) $schoolYears[0] : '';
        $defaultSemesters = self::semesterOptionsForYear($semesterMap, $defaultSchoolYear);
        $defaultSemester = count($defaultSemesters)
            ? (string) $defaultSemesters[0]
            : (string) self::DEFAULT_SEMESTERS[0];

        return [
            'school_years' => $schoolYears,
            'semester_map' => $semesterMap,
            'default_school_year' => $defaultSchoolYear,
            'default_semester' => $defaultSemester,
        ];
    }

    public static function semesterOptionsForYear(array $semesterMap, $schoolYear): array
    {
        $normalizedYear = trim((string) $schoolYear);
        if ($normalizedYear !== ''
            && array_key_exists($normalizedYear, $semesterMap)
            && count((array) $semesterMap[$normalizedYear])) {
            return array_values((array) $semesterMap[$normalizedYear]);
        }

        $allSemesters = [];
        foreach ($semesterMap as $semesters) {
            foreach ((array) $semesters as $semester) {
                $normalizedSemester = self::normalizeSemester((string) $semester);
                if ($normalizedSemester !== '' && !in_array($normalizedSemester, $allSemesters, true)) {
                    $allSemesters[] = $normalizedSemester;
                }
            }
        }

        if (!count($allSemesters)) {
            $allSemesters = self::DEFAULT_SEMESTERS;
        }

        usort($allSemesters, function ($left, $right) {
            return self::semesterWeight((string) $left) <=> self::semesterWeight((string) $right);
        });

        return array_values(array_unique($allSemesters));
    }

    public static function normalizeSemester($semester): string
    {
        $normalized = strtolower(trim((string) $semester));

        if ($normalized === '') {
            return '';
        }

        $aliasMap = [
            'first' => 'First',
            '1st' => 'First',
            '1st semester' => 'First',
            'first semester' => 'First',
            'second' => 'Second',
            '2nd' => 'Second',
            '2nd semester' => 'Second',
            'second semester' => 'Second',
            'summer' => 'Summer',
            'summer semester' => 'Summer',
        ];

        return array_key_exists($normalized, $aliasMap)
            ? $aliasMap[$normalized]
            : '';
    }

    private static function semesterWeight(string $semester): int
    {
        $normalized = self::normalizeSemester($semester);

        if ($normalized === 'First') {
            return 1;
        }

        if ($normalized === 'Second') {
            return 2;
        }

        if ($normalized === 'Summer') {
            return 3;
        }

        return 99;
    }

    private static function termSortSql(): string
    {
        return "FIELD(term, 'First', '1st Semester', 'First Semester', 'Second', '2nd Semester', 'Second Semester', 'Summer', 'Summer Semester')";
    }
}
