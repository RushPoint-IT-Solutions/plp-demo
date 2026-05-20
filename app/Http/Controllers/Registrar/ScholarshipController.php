<?php

namespace App\Http\Controllers\Registrar;

use App\Course;
use App\Http\Controllers\Controller;
use App\ScholarshipProgram;
use App\ScholarshipStudent;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ScholarshipController extends Controller
{
    private const CATEGORIES = [
        'Academic scholarship',
        'Financial assistance',
        'Government scholarship',
        'Private/company-sponsored scholarship',
        'Athletic/cultural scholarship',
        'Employee dependent discount',
        'Sibling discount',
    ];

    private const COVERAGE_TYPES = [
        'Full tuition',
        'Partial tuition',
        'Fixed amount',
        'Percentage discount',
        'Miscellaneous fee coverage',
    ];

    private const PROGRAM_STATUSES = ['Open', 'Closed', 'Ongoing', 'Ended'];

    public function index(Request $request)
    {
        $this->ensureTables();

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $status = trim((string) $request->query('status', ''));

        $programs = ScholarshipProgram::query()
            ->withCount('scholarTags')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('school_year', 'like', '%' . $search . '%')
                        ->orWhere('semester', 'like', '%' . $search . '%');
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE status WHEN 'Open' THEN 1 WHEN 'Ongoing' THEN 2 WHEN 'Closed' THEN 3 WHEN 'Ended' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends($request->query());

        $courses = Course::orderBy('code')->get(['id', 'code', 'name']);
        $summary = [
            'programs' => ScholarshipProgram::count(),
            'open' => ScholarshipProgram::where('status', 'Open')->count(),
            'ongoing' => ScholarshipProgram::where('status', 'Ongoing')->count(),
            'scholars' => ScholarshipStudent::distinct('student_id')->count('student_id'),
        ];

        return view('registrar.scholarships.index', [
            'programs' => $programs,
            'courses' => $courses,
            'categories' => self::CATEGORIES,
            'coverageTypes' => self::COVERAGE_TYPES,
            'statuses' => self::PROGRAM_STATUSES,
            'summary' => $summary,
            'search' => $search,
            'category' => $category,
            'status' => $status,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureTables();
        ScholarshipProgram::create($this->programPayload($request));

        return redirect()->route('registrar.registrar-menu.scholarships.index')
            ->with('success', 'Scholarship program created.');
    }

    public function update(Request $request, ScholarshipProgram $scholarship)
    {
        $this->ensureTables();
        $scholarship->update($this->programPayload($request));

        return redirect()->route('registrar.registrar-menu.scholarships.index')
            ->with('success', 'Scholarship program updated.');
    }

    public function destroy(ScholarshipProgram $scholarship)
    {
        $this->ensureTables();
        $scholarship->delete();

        return redirect()->route('registrar.registrar-menu.scholarships.index')
            ->with('success', 'Scholarship program deleted.');
    }

    public function tagStudent(Request $request, Student $student)
    {
        $this->ensureTables();

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:scholarship_student,id',
            'scholarship_program_id' => 'required|integer|exists:scholarship_programs,id',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:40',
            'application_status' => 'required|string|max:40',
            'evaluation_status' => 'nullable|string|max:40',
            'approval_status' => 'nullable|string|max:40',
            'award_status' => 'required|string|max:40',
            'renewal_status' => 'nullable|string|max:40',
            'monitoring_status' => 'nullable|string|max:40',
            'financial_posting_status' => 'required|string|max:40',
            'posted_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'current_gwa' => 'nullable|numeric|min:1|max:5',
            'application_date' => 'nullable|date',
            'approval_date' => 'nullable|date',
            'renewal_due_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $payload = $validated;
        unset($payload['id']);
        $payload['student_id'] = $student->id;
        $payload['school_year'] = $payload['school_year'] ?: $student->school_year;
        $payload['semester'] = $payload['semester'] ?: $student->semester;
        $payload['tagged_by_user_id'] = optional($request->user())->id;

        if (!empty($validated['id'])) {
            ScholarshipStudent::where('id', $validated['id'])
                ->where('student_id', $student->id)
                ->update($payload);
        } else {
            ScholarshipStudent::updateOrCreate([
                'student_id' => $student->id,
                'scholarship_program_id' => $payload['scholarship_program_id'],
                'school_year' => $payload['school_year'],
                'semester' => $payload['semester'],
            ], $payload);
        }

        return redirect()->route('registrar.registrar-menu.student-mgmt.student-records.profile', $student->id)
            ->with('success', 'Student scholarship tagging saved.');
    }

    public function untagStudent(Student $student, ScholarshipStudent $tag)
    {
        $this->ensureTables();

        if ((int) $tag->student_id === (int) $student->id) {
            $tag->delete();
        }

        return redirect()->route('registrar.registrar-menu.student-mgmt.student-records.profile', $student->id)
            ->with('success', 'Scholarship tag removed.');
    }

    public function report(Request $request)
    {
        $this->ensureTables();

        $category = trim((string) $request->query('category', ''));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $awardStatus = trim((string) $request->query('award_status', ''));

        $query = ScholarshipStudent::query()
            ->with(['student.profile', 'student.canonicalCourse', 'program'])
            ->whereHas('program', function ($programQuery) use ($category) {
                if ($category !== '') {
                    $programQuery->where('category', $category);
                }
            })
            ->when($schoolYear !== '', function ($inner) use ($schoolYear) {
                $inner->where('school_year', $schoolYear);
            })
            ->when($awardStatus !== '', function ($inner) use ($awardStatus) {
                $inner->where('award_status', $awardStatus);
            })
            ->orderByDesc('school_year')
            ->orderBy('semester')
            ->orderBy('scholarship_program_id');

        $tags = $query->get();
        $scholarsByType = $tags->groupBy(function ($tag) {
            return optional($tag->program)->category ?: 'Uncategorized';
        });

        $schoolYears = ScholarshipStudent::query()
            ->whereNotNull('school_year')
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->unique()
            ->values();

        return view('registrar.scholarships.report', [
            'scholarsByType' => $scholarsByType,
            'categories' => self::CATEGORIES,
            'schoolYears' => $schoolYears,
            'category' => $category,
            'schoolYear' => $schoolYear,
            'awardStatus' => $awardStatus,
            'totalScholars' => $tags->pluck('student_id')->unique()->count(),
            'totalTags' => $tags->count(),
        ]);
    }

    private function programPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'coverage_type' => ['required', 'string', Rule::in(self::COVERAGE_TYPES)],
            'coverage_value' => 'nullable|numeric|min:0',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:40',
            'applicable_course_ids' => 'nullable|array',
            'applicable_course_ids.*' => 'integer|exists:courses,id',
            'year_level_eligibility' => 'nullable|string|max:160',
            'available_slots' => 'nullable|integer|min:0',
            'maintaining_gwa' => 'nullable|numeric|min:1|max:5',
            'status' => ['required', 'string', Rule::in(self::PROGRAM_STATUSES)],
            'description' => 'nullable|string|max:2000',
        ]);

        $validated['applicable_course_ids'] = json_encode(array_values($validated['applicable_course_ids'] ?? []));

        return $validated;
    }

    private function ensureTables(): void
    {
        abort_unless(Schema::hasTable('scholarship_programs') && Schema::hasTable('scholarship_student'), 500, 'Scholarship tables are not available. Please run migrations.');
    }
}
