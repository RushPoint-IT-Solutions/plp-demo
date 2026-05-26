<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\Course;
use App\Http\Controllers\Controller;
use App\Student;
use App\Subject;
use App\YearBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SectionListController extends Controller
{
    public function index(Request $request)
    {
        $state = $this->resolveState($request);
        $sections = $this->sectionRows($state);
        $selectedSection = $this->selectedSection($sections, $state);
        $students = null;

        if ($selectedSection) {
            $students = $this->studentsForSection($selectedSection, $state['student_search'])
                ->paginate(25)
                ->appends($request->except('page'));
        }

        return view('registrar.services.section-list', [
            'sections' => $sections,
            'selectedSection' => $selectedSection,
            'students' => $students,
            'schoolYearOptions' => $this->schoolYearOptions(),
            'semesterOptions' => $this->semesterOptions($state['school_year']),
            'courseOptions' => $this->courseOptions(),
            'yearBlockOptions' => $this->yearBlockOptions(),
            'sectionOptions' => $this->sectionOptions($sections),
            'state' => $state,
        ]);
    }

    private function resolveState(Request $request)
    {
        return [
            'school_year' => trim((string) $request->query('school_year', '')),
            'semester' => $this->normalizeSemester((string) $request->query('semester', '')),
            'course_id' => (int) $request->query('course_id', 0),
            'year_block_id' => (int) $request->query('year_block_id', 0),
            'section' => trim((string) $request->query('section', '')),
            'student_search' => trim((string) $request->query('student_search', '')),
        ];
    }

    private function sectionRows(array $state)
    {
        $rows = collect();

        if (Schema::hasTable('student_section_assignments')) {
            $rows = $rows->merge($this->assignmentSectionRows($state));
        }

        if (Schema::hasTable('student_subject')) {
            $rows = $rows->merge($this->subjectEnrollmentSectionRows($state));
        }

        return $rows
            ->groupBy(function ($row) {
                return implode('|', [
                    (int) $row->academic_term_id,
                    (int) $row->course_id,
                    (int) $row->year_block_id,
                    trim((string) $row->section),
                ]);
            })
            ->map(function (Collection $group) {
                $preferred = $group->firstWhere('source', 'assignments') ?: $group->first();
                $preferred->student_count = (int) $group->max('student_count');
                $preferred->subject_count = (int) $group->max('subject_count');

                return $preferred;
            })
            ->sort(function ($left, $right) {
                $schoolYear = strcmp((string) $right->school_year, (string) $left->school_year);
                if ($schoolYear !== 0) {
                    return $schoolYear;
                }

                $term = (int) $right->academic_term_id <=> (int) $left->academic_term_id;
                if ($term !== 0) {
                    return $term;
                }

                $course = strcmp((string) $left->course_code, (string) $right->course_code);
                if ($course !== 0) {
                    return $course;
                }

                $year = (int) $left->year_block_sort <=> (int) $right->year_block_sort;
                if ($year !== 0) {
                    return $year;
                }

                return strcmp((string) $left->section, (string) $right->section);
            })
            ->values();
    }

    private function assignmentSectionRows(array $state)
    {
        $query = DB::table('student_section_assignments as ssa')
            ->join('academic_terms as at', 'at.id', '=', 'ssa.academic_term_id')
            ->leftJoin('courses as c', 'c.id', '=', 'ssa.course_id')
            ->leftJoin('year_blocks as yb', 'yb.id', '=', 'ssa.year_block_id')
            ->where('ssa.status', 'active')
            ->whereRaw("TRIM(COALESCE(ssa.section, '')) <> ''")
            ->select([
                'ssa.academic_term_id',
                'ssa.course_id',
                'ssa.year_block_id',
                'ssa.section',
                'at.school_year',
                'at.term as semester',
                'c.code as course_code',
                'c.name as course_name',
                'yb.label as year_level',
                'yb.id as year_block_sort',
                DB::raw('COUNT(DISTINCT ssa.student_id) as student_count'),
                DB::raw('0 as subject_count'),
                DB::raw("'assignments' as source"),
            ])
            ->groupBy('ssa.academic_term_id', 'ssa.course_id', 'ssa.year_block_id', 'ssa.section', 'at.school_year', 'at.term', 'c.code', 'c.name', 'yb.label', 'yb.id');

        $this->applySectionFilters($query, $state, 'ssa', 'at');

        return $query->get();
    }

    private function subjectEnrollmentSectionRows(array $state)
    {
        $subjectHasSchoolYear = Schema::hasColumn('subjects', 'school_year');
        $subjectHasSemester = Schema::hasColumn('subjects', 'semester');
        $studentHasSchoolYear = Schema::hasColumn('students', 'school_year');
        $studentHasSemester = Schema::hasColumn('students', 'semester');

        $schoolYearSources = ['at.school_year'];
        if ($subjectHasSchoolYear) {
            $schoolYearSources[] = 'sub.school_year';
        }
        if ($studentHasSchoolYear) {
            $schoolYearSources[] = 'st.school_year';
        }

        $semesterSources = ['at.term'];
        if ($subjectHasSemester) {
            $semesterSources[] = 'sub.semester';
        }
        if ($studentHasSemester) {
            $semesterSources[] = 'st.semester';
        }

        $groupBy = [
            'sub.academic_term_id',
            'sub.course_id',
            'st.course_id',
            'st.year_block_id',
            'sub.year_section',
            'at.school_year',
            'at.term',
            'c.code',
            'c.name',
            'yb.label',
            'yb.id',
        ];

        if ($subjectHasSchoolYear) {
            $groupBy[] = 'sub.school_year';
        }
        if ($studentHasSchoolYear) {
            $groupBy[] = 'st.school_year';
        }
        if ($subjectHasSemester) {
            $groupBy[] = 'sub.semester';
        }
        if ($studentHasSemester) {
            $groupBy[] = 'st.semester';
        }

        $query = DB::table('subjects as sub')
            ->join('student_subject as ss', 'ss.subject_id', '=', 'sub.id')
            ->leftJoin('academic_terms as at', 'at.id', '=', 'sub.academic_term_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sub.course_id')
            ->leftJoin('students as st', 'st.id', '=', 'ss.student_id')
            ->leftJoin('year_blocks as yb', 'yb.id', '=', 'st.year_block_id')
            ->whereRaw("TRIM(COALESCE(sub.year_section, '')) <> ''")
            ->select([
                DB::raw('COALESCE(sub.academic_term_id, 0) as academic_term_id'),
                DB::raw('COALESCE(sub.course_id, st.course_id, 0) as course_id'),
                DB::raw('COALESCE(st.year_block_id, 0) as year_block_id'),
                'sub.year_section as section',
                DB::raw('COALESCE(' . implode(', ', $schoolYearSources) . ", '') as school_year"),
                DB::raw('COALESCE(' . implode(', ', $semesterSources) . ", '') as semester"),
                'c.code as course_code',
                'c.name as course_name',
                'yb.label as year_level',
                DB::raw('COALESCE(yb.id, 0) as year_block_sort'),
                DB::raw('COUNT(DISTINCT ss.student_id) as student_count'),
                DB::raw('COUNT(DISTINCT sub.id) as subject_count'),
                DB::raw("'subjects' as source"),
            ])
            ->groupBy($groupBy);

        $this->applySectionFilters($query, $state, 'sub', 'at');

        return $query->get();
    }

    private function applySectionFilters($query, array $state, $sectionAlias, $termAlias)
    {
        if ($state['school_year'] !== '') {
            if ($sectionAlias === 'ssa') {
                $query->where($termAlias . '.school_year', $state['school_year']);
            } else {
                $query->where(function ($inner) use ($state, $termAlias) {
                    $inner->where($termAlias . '.school_year', $state['school_year']);

                    if (Schema::hasColumn('subjects', 'school_year')) {
                        $inner->orWhere('sub.school_year', $state['school_year']);
                    }

                    if (Schema::hasColumn('students', 'school_year')) {
                        $inner->orWhere('st.school_year', $state['school_year']);
                    }
                });
            }
        }

        if ($state['semester'] !== '') {
            if ($sectionAlias === 'ssa') {
                $query->where($termAlias . '.term', 'like', '%' . $state['semester'] . '%');
            } else {
                $query->where(function ($inner) use ($state, $termAlias) {
                    $inner->where($termAlias . '.term', 'like', '%' . $state['semester'] . '%');

                    if (Schema::hasColumn('subjects', 'semester')) {
                        $inner->orWhere('sub.semester', 'like', '%' . $state['semester'] . '%');
                    }

                    if (Schema::hasColumn('students', 'semester')) {
                        $inner->orWhere('st.semester', 'like', '%' . $state['semester'] . '%');
                    }
                });
            }
        }

        if ($state['course_id'] > 0) {
            if ($sectionAlias === 'ssa') {
                $query->where('ssa.course_id', $state['course_id']);
            } else {
                $query->where(function ($inner) use ($state) {
                    $inner->where('sub.course_id', $state['course_id'])
                        ->orWhere('st.course_id', $state['course_id']);
                });
            }
        }

        if ($state['year_block_id'] > 0) {
            if ($sectionAlias === 'ssa') {
                $query->where('ssa.year_block_id', $state['year_block_id']);
            } else {
                $query->where('st.year_block_id', $state['year_block_id']);
            }
        }

        if ($state['section'] !== '') {
            $query->where($sectionAlias === 'ssa' ? 'ssa.section' : 'sub.year_section', $state['section']);
        }
    }

    private function selectedSection(Collection $sections, array $state)
    {
        if ($state['section'] === '') {
            return null;
        }

        return $sections->first(function ($row) use ($state) {
            if (trim((string) $row->section) !== $state['section']) {
                return false;
            }

            if ($state['course_id'] > 0 && (int) $row->course_id !== $state['course_id']) {
                return false;
            }

            if ($state['year_block_id'] > 0 && (int) $row->year_block_id !== $state['year_block_id']) {
                return false;
            }

            if ($state['school_year'] !== '' && (string) $row->school_year !== $state['school_year']) {
                return false;
            }

            if ($state['semester'] !== '' && $this->normalizeSemester($row->semester) !== $state['semester']) {
                return false;
            }

            return true;
        });
    }

    private function studentsForSection($section, $search)
    {
        if ($section->source === 'assignments' && Schema::hasTable('student_section_assignments')) {
            $query = Student::query()
                ->select('students.*')
                ->join('student_section_assignments as ssa', 'ssa.student_id', '=', 'students.id')
                ->where('ssa.academic_term_id', (int) $section->academic_term_id)
                ->where('ssa.section', (string) $section->section)
                ->where('ssa.status', 'active');

            if ((int) $section->course_id > 0) {
                $query->where('ssa.course_id', (int) $section->course_id);
            }

            if ((int) $section->year_block_id > 0) {
                $query->where('ssa.year_block_id', (int) $section->year_block_id);
            }
        } else {
            $query = Student::query()
                ->select('students.*')
                ->join('student_subject as ss', 'ss.student_id', '=', 'students.id')
                ->join('subjects as sub', 'sub.id', '=', 'ss.subject_id')
                ->where('sub.year_section', (string) $section->section);

            if ((int) $section->academic_term_id > 0) {
                $query->where('sub.academic_term_id', (int) $section->academic_term_id);
            }

            if ((int) $section->course_id > 0) {
                $query->where('sub.course_id', (int) $section->course_id);
            }
        }

        return $query
            ->with(['canonicalCourse', 'yearBlock'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('students.student_no', 'like', $like)
                        ->orWhere('students.name', 'like', $like);
                });
            })
            ->distinct()
            ->orderBy('students.name')
            ->orderBy('students.student_no');
    }

    private function schoolYearOptions()
    {
        return AcademicTerm::query()
            ->whereNotNull('school_year')
            ->where('school_year', '<>', '')
            ->distinct()
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->values();
    }

    private function semesterOptions($schoolYear)
    {
        return AcademicTerm::query()
            ->when(trim((string) $schoolYear) !== '', function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear);
            })
            ->whereNotNull('term')
            ->where('term', '<>', '')
            ->distinct()
            ->orderByRaw($this->termSortSql())
            ->pluck('term')
            ->map(function ($term) {
                return $this->normalizeSemester($term);
            })
            ->filter()
            ->unique()
            ->values();
    }

    private function courseOptions()
    {
        return Course::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    private function yearBlockOptions()
    {
        return YearBlock::query()
            ->orderBy('id')
            ->get(['id', 'label']);
    }

    private function sectionOptions(Collection $sections)
    {
        return $sections
            ->pluck('section')
            ->map(function ($section) {
                return trim((string) $section);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function normalizeSemester($value)
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

        return (string) $value;
    }

    private function termSortSql()
    {
        return "CASE
            WHEN LOWER(term) LIKE '%first%' OR LOWER(term) LIKE '%1st%' THEN 1
            WHEN LOWER(term) LIKE '%second%' OR LOWER(term) LIKE '%2nd%' THEN 2
            WHEN LOWER(term) LIKE '%summer%' THEN 3
            ELSE 4
        END";
    }
}
