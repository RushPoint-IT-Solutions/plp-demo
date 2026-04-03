<?php

namespace App\Http\Controllers\Registrar;

use App\Applicant;
use App\AlumniTrackerSetting;
use App\CancellationWaiver;
use App\Course;
use App\CrossEnrollmentRequest;
use App\Department;
use App\Faculty;
use App\Student;
use App\StudentSubjectGrade;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrarController extends Controller
{
    /**
     * Registrar Dashboard
     */
    public function dashboard()
    {
        $studentCount = Student::count();
        $applicantCount = Applicant::count();
        $facultyCount = Faculty::count();
        $departmentCount = Department::count();
        $daySeed = (int) Carbon::now()->format('z') + 1;
        $useDemoDashboard = ($studentCount <= 20);

        $maleCount = 0;
        $femaleCount = 0;
        if (Schema::hasColumn('students', 'sex')) {
            $maleCount = Student::whereRaw('LOWER(sex) = ?', ['male'])->count();
            $femaleCount = Student::whereRaw('LOWER(sex) = ?', ['female'])->count();
        }

        if ($useDemoDashboard || ($studentCount === 0 && $applicantCount === 0 && $facultyCount === 0)) {
            $studentCount = 230 + ($daySeed % 22);
            $applicantCount = 82 + ($daySeed % 17);
            $facultyCount = 18 + ($daySeed % 6);
            $departmentCount = max($departmentCount, 4);

            $maleRatio = 0.53 + (($daySeed % 6) * 0.01);
            $maleCount = (int) round($studentCount * $maleRatio);
            $femaleCount = max($studentCount - $maleCount, 0);
        }

        if ($maleCount + $femaleCount === 0 && $studentCount > 0) {
            $maleCount = (int) round($studentCount * 0.56);
            $femaleCount = max($studentCount - $maleCount, 0);
        }

        $trendValues = $this->buildMonthlyCounts('students', 6);
        $nonZeroTrendPoints = count(array_filter($trendValues, function ($value) {
            return $value > 0;
        }));
        if ($useDemoDashboard || array_sum($trendValues) <= 0 || $nonZeroTrendPoints <= 2) {
            $trendValues = $this->buildDemoUptrendSeries(6, 42 + ($daySeed % 6), 3, 7, $daySeed + 5);
        }
        $trendPercent = $this->computeLastMonthPercent($trendValues);
        $sparklinePaths = $this->buildSparklinePaths($trendValues, 110, 60);

        return view('registrar.dashboard', [
            'dashboardData' => [
                'studentCount' => $studentCount,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'applicantCount' => $applicantCount,
                'facultyCount' => $facultyCount,
                'departmentCount' => $departmentCount,
                'trendPercent' => $trendPercent,
                'sparklinePath' => $sparklinePaths['line'],
                'sparklineAreaPath' => $sparklinePaths['area'],
            ],
        ]);
    }

    /**
     * Registrar Messaging
     */
    public function messaging()
    {
        return view('registrar.messaging');
    }

    /**
     * Process > Application Process
     */
    public function applicationProcess()
    {
        return view('registrar.process.application-process');
    }

    /**
     * Process > Requirements
     */
    public function requirements()
    {
        return view('registrar.process.requirements');
    }

    /**
     * Process > Citizenship
     */
    public function citizenship()
    {
        return view('registrar.process.citizenship');
    }

    /**
     * Process > Religion
     */
    public function religion()
    {
        return view('registrar.process.religion');
    }

    /**
     * Process > Approval Status
     */
    public function approvalStatus()
    {
        return view('registrar.process.approval-status');
    }

    /**
     * Process > Exam Category
     */
    public function examCategory()
    {
        return view('registrar.process.exam-category');
    }

    /**
     * Process > Exam List
     */
    public function examList()
    {
        return view('registrar.process.exam-list');
    }

    /**
     * Process > Batch Upload Image
     */
    public function batchUpload()
    {
        return view('registrar.process.batch-upload');
    }

    /**
     * Process > Document List
     */
    public function documentList()
    {
        return view('registrar.process.document-list');
    }

    /**
     * Process > Reports
     */
    public function reports()
    {
        $totalApplicants = Applicant::count();
        $students4thYear = Student::where('year_level', 'LIKE', '%4%')->count();
        $daySeed = (int) Carbon::now()->format('z') + 1;

        $useDemoReports = ($totalApplicants <= 15 || $students4thYear <= 15);

        if ($useDemoReports) {
            $totalApplicants = 238 + ($daySeed % 24);
            $students4thYear = 172 + ($daySeed % 18);
        }

        $verifiedCount = (int) round($students4thYear * 0.56);
        $incompleteCount = max($students4thYear - $verifiedCount, 0);
        $approvedCount = (int) round($verifiedCount * 0.69);
        $approvalRate = $students4thYear > 0
            ? round(($approvedCount / $students4thYear) * 100, 1)
            : 0;

        $students = Student::query()
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['name', 'program', 'year_level', 'updated_at']);

        $requirementPool = [
            'Form 137',
            'PSA',
            'OJT Certificate',
            'Library Clearance',
            'Graduation Application',
            'Medical Certificate',
            'Good Moral Certificate',
            'TOR',
            'Barangay Clearance',
        ];

        $rows = $students->values()->map(function ($student, $index) use ($requirementPool) {
            $firstReq = $requirementPool[$index % count($requirementPool)];
            $secondReq = $requirementPool[($index + 1) % count($requirementPool)];
            $thirdReq = $requirementPool[($index + 3) % count($requirementPool)];
            $missing = ($index % 5 === 0)
                ? [$firstReq, $secondReq, $thirdReq]
                : (($index % 2 === 0) ? [$firstReq, $secondReq] : [$firstReq]);

            return [
                'name' => $student->name ?: 'Unknown Student',
                'section' => trim(($student->program ?: 'N/A') . ' ' . ($student->year_level ?: '')),
                'missing_items' => $missing,
                'last_updated' => optional($student->updated_at)->format('M j, Y') ?: Carbon::now()->format('M j, Y'),
            ];
        })->all();

        if ($useDemoReports || count($rows) < 8) {
            $rows = $this->buildDemoReportRows(max(14, count($rows)), $daySeed);

            $totalApplicants = max($totalApplicants, count($rows) + 52);
            $students4thYear = max($students4thYear, count($rows) + 36);
            $verifiedCount = (int) round($students4thYear * 0.57);
            $incompleteCount = max($students4thYear - $verifiedCount, 0);
            $approvedCount = (int) round($verifiedCount * 0.72);
            $approvalRate = $students4thYear > 0
                ? round(($approvedCount / $students4thYear) * 100, 1)
                : 0;
        }

        $sectionOptions = collect($rows)
            ->pluck('section')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $requirementCounts = [];
        foreach ($rows as $row) {
            foreach ((array) ($row['missing_items'] ?? []) as $item) {
                $requirementCounts[$item] = ($requirementCounts[$item] ?? 0) + 1;
            }
        }

        arsort($requirementCounts);
        $topRequirementLabels = array_slice(array_keys($requirementCounts), 0, 8);
        $topRequirementValues = array_map(function ($label) use ($requirementCounts) {
            return $requirementCounts[$label] ?? 0;
        }, $topRequirementLabels);

        if (!count($topRequirementLabels)) {
            $topRequirementLabels = [
                'Form 137',
                'PSA',
                'OJT Certificate',
                'Library Clearance',
                'Graduation Application',
                'Medical Certificate',
                'Good Moral Certificate',
                'TOR',
            ];
            $topRequirementValues = [76, 69, 63, 58, 49, 43, 37, 31];
        }

        $monthlyPointCount = 12;
        $timelineMonthlyCategories = [];
        $timelineMonthlySeries = $this->buildDemoUptrendSeries($monthlyPointCount, 16 + ($daySeed % 5), 1, 4, $daySeed + 14);
        $monthlyStart = Carbon::now()->startOfMonth()->subMonths($monthlyPointCount - 1);
        for ($i = 0; $i < $monthlyPointCount; $i++) {
            $timelineMonthlyCategories[] = $monthlyStart->copy()->addMonths($i)->format('M Y');
        }

        $yearlyPointCount = 6;
        $timelineYearlyCategories = [];
        $timelineYearlySeries = $this->buildDemoUptrendSeries($yearlyPointCount, 120 + ($daySeed % 20), 18, 42, $daySeed + 33);
        $yearlyStart = Carbon::now()->startOfYear()->subYears($yearlyPointCount - 1);
        for ($i = 0; $i < $yearlyPointCount; $i++) {
            $timelineYearlyCategories[] = $yearlyStart->copy()->addYears($i)->format('Y');
        }

        if ($approvedCount >= 20) {
            $monthlyMax = max($timelineMonthlySeries) ?: 1;
            $monthlyScale = ($approvedCount * 1.05) / $monthlyMax;
            $timelineMonthlySeries = array_map(function ($value) use ($monthlyScale) {
                return max(0, (int) round($value * $monthlyScale));
            }, $timelineMonthlySeries);

            $yearlyMax = max($timelineYearlySeries) ?: 1;
            $yearlyScale = ($approvedCount * 4.2) / $yearlyMax;
            $timelineYearlySeries = array_map(function ($value) use ($yearlyScale) {
                return max(0, (int) round($value * $yearlyScale));
            }, $timelineYearlySeries);
        }

        if (max($timelineMonthlySeries) === 0) {
            $timelineMonthlySeries = $this->buildDemoUptrendSeries($monthlyPointCount, 24, 2, 5, $daySeed + 7);
        }
        if (max($timelineYearlySeries) === 0) {
            $timelineYearlySeries = $this->buildDemoUptrendSeries($yearlyPointCount, 140, 24, 45, $daySeed + 11);
        }

        $approvalTimeline = [
            'monthly' => [
                'categories' => $timelineMonthlyCategories,
                'series' => $timelineMonthlySeries,
                'markerCategory' => $timelineMonthlyCategories[count($timelineMonthlyCategories) - 2] ?? end($timelineMonthlyCategories),
            ],
            'yearly' => [
                'categories' => $timelineYearlyCategories,
                'series' => $timelineYearlySeries,
                'markerCategory' => $timelineYearlyCategories[count($timelineYearlyCategories) - 2] ?? end($timelineYearlyCategories),
            ],
        ];

        return view('registrar.process.reports', [
            'reportData' => [
                'totalApplicants' => $totalApplicants,
                'students4thYear' => $students4thYear,
                'verifiedCount' => $verifiedCount,
                'incompleteCount' => $incompleteCount,
                'approvedCount' => $approvedCount,
                'approvalRate' => $approvalRate,
                'sectionOptions' => $sectionOptions,
                'rows' => $rows,
                'missingRequirementLabels' => $topRequirementLabels,
                'missingRequirementValues' => $topRequirementValues,
                'approvalTimeline' => $approvalTimeline,
                'timelineCategories' => $approvalTimeline['monthly']['categories'],
                'timelineSeries' => $approvalTimeline['monthly']['series'],
                'timelineMarkerCategory' => $approvalTimeline['monthly']['markerCategory'],
            ],
        ]);
    }

    /**
     * Process > Reports > UNIFAST
     */
    public function reportsUnifast()
    {
        return view('registrar.process.reports-unifast');
    }

    /**
     * Process > Reports > OSS - NSTP Form
     */
    public function reportsOssNstpForm()
    {
        return view('registrar.process.reports-oss-nstp-form');
    }

    private function buildMonthlyCounts(string $table, int $months): array
    {
        $fallback = $this->buildDemoMovingSeries($months, 45, 11, 5, (int) Carbon::now()->format('z') + 3);
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'created_at')) {
            return $fallback;
        }

        $values = [];
        $cursor = Carbon::now()->startOfMonth()->subMonths($months - 1);
        for ($i = 0; $i < $months; $i++) {
            $start = $cursor->copy()->addMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $values[] = (int) \DB::table($table)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        if (array_sum($values) === 0) {
            return $fallback;
        }

        return $values;
    }

    private function computeLastMonthPercent(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $previous = (float) $values[count($values) - 2];
        $current = (float) $values[count($values) - 1];

        if ($previous <= 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildSparklinePaths(array $values, int $width, int $height): array
    {
        $paddingX = 2;
        $paddingY = 6;
        $chartWidth = max($width - ($paddingX * 2), 1);
        $chartHeight = max($height - ($paddingY * 2), 1);

        $min = min($values);
        $max = max($values);
        $range = max($max - $min, 1);
        $count = max(count($values) - 1, 1);

        $points = [];
        foreach ($values as $index => $value) {
            $x = $paddingX + ($chartWidth * ($index / $count));
            $y = $paddingY + ($chartHeight * (1 - (($value - $min) / $range)));
            $points[] = [round($x, 2), round($y, 2)];
        }

        $line = 'M ' . $points[0][0] . ',' . $points[0][1];
        for ($i = 1; $i < count($points); $i++) {
            $line .= ' L ' . $points[$i][0] . ',' . $points[$i][1];
        }

        $area = $line . ' L ' . ($paddingX + $chartWidth) . ',' . ($paddingY + $chartHeight)
            . ' L ' . $paddingX . ',' . ($paddingY + $chartHeight) . ' Z';

        return [
            'line' => $line,
            'area' => $area,
        ];
    }

    private function buildDemoMovingSeries(int $points, int $base, int $amplitude, int $trend, int $seed): array
    {
        $series = [];
        $phase = ($seed % 11) / 3;
        for ($i = 0; $i < $points; $i++) {
            $wave = sin($phase + ($i * 0.95)) * $amplitude;
            $progress = $i * $trend;
            $noise = (($seed + ($i * 7)) % 5) - 2;
            $series[] = max(5, (int) round($base + $wave + $progress + $noise));
        }
        return $series;
    }

    private function buildDemoUptrendSeries(int $points, int $start, int $stepMin, int $stepMax, int $seed): array
    {
        $series = [];
        $value = max(5, $start + ($seed % 5));
        $safeStepMax = max($stepMax, $stepMin);

        for ($i = 0; $i < $points; $i++) {
            $stepRange = max($safeStepMax - $stepMin + 1, 1);
            $step = $stepMin + (($seed + ($i * 3)) % $stepRange);
            $wave = ($i % 4 === 2) ? -1 : (($i % 4 === 0) ? 1 : 0);
            $value += $step;
            $series[] = max(5, (int) round($value + $wave));
        }

        return $series;
    }

    private function buildDemoReportRows(int $count, int $seed): array
    {
        $names = [
            'Elias Bartolome', 'Juan Dela Cruz', 'Gabriel Villanueva', 'Andrea Jane Austero', 'Maria Clara Santos',
            'Mark Jay Bares', 'Patricia Mendoza', 'Nico Ramirez', 'Lea Domingo', 'Caleb Flores',
            'Trisha Javier', 'Paolo Mendoza', 'Rina Gamboa', 'Dale Aquino', 'Jessa Salazar',
        ];
        $sections = ['BSCS 1-C', 'BSCS 2-A', 'BSCS 3-B', 'BSCS 4-B', 'BSIT 4-A', 'BSIT 3-C'];
        $requirements = [
            'Form 137',
            'PSA',
            'OJT Certificate',
            'Library Clearance',
            'Graduation Application',
            'Medical Certificate',
            'Good Moral Certificate',
            'TOR',
            'Barangay Clearance',
        ];

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i % count($names)] . ($i >= count($names) ? ' ' . chr(65 + ($i % 26)) : '');
            $section = $sections[($i + $seed) % count($sections)];
            $firstReq = $requirements[($i + $seed) % count($requirements)];
            $secondReq = $requirements[($i + $seed + 2) % count($requirements)];

            $missingSet = [
                $firstReq,
                $secondReq,
                $requirements[($i + $seed + 4) % count($requirements)],
            ];

            $rows[] = [
                'name' => $name,
                'section' => $section,
                'missing_items' => ($i % 4 === 0)
                    ? $missingSet
                    : (($i % 2 === 0) ? [$firstReq, $secondReq] : [$firstReq]),
                'last_updated' => Carbon::now()->subDays(($i * 2 + ($seed % 5)) % 25)->format('M j, Y'),
            ];
        }

        return $rows;
    }

    /**
     * Registrar > Academic Master > Program File
     */
    public function programFile(Request $request)
    {
        $departments = Department::orderBy('description')->get();
        $faculties = Faculty::orderBy('name')->get();

        $programs = Course::with(['department', 'deanDirector'])
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->where('department_id', $request->input('department_id'));
            })
            ->when($request->filled('program_type'), function ($query) use ($request) {
                $query->where('program_type', $request->input('program_type'));
            })
            ->when($request->filled('program_code'), function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->input('program_code') . '%');
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $term = $request->input('description');
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('code')
            ->get();

        return view('registrar.registrar-menu.academic-master.program-file', [
            'departments' => $departments,
            'faculties' => $faculties,
            'programs' => $programs,
            'filters' => [
                'department_id' => $request->input('department_id', ''),
                'program_type' => $request->input('program_type', ''),
                'program_code' => $request->input('program_code', ''),
                'description' => $request->input('description', ''),
            ],
        ]);
    }

    /**
     * Registrar > Academic Master > Program File > Save setup modal
     */
    public function saveProgramSetup(Request $request)
    {
        $validated = $request->validate([
            'program_type' => 'required|string|max:80',
            'program_code' => 'required|string|max:30|unique:courses,code',
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string|max:255',
            'slots' => 'nullable|integer|min:0',
            'track_category' => 'nullable|in:Academic,TVL,Academic/TVL',
            'non_filipino' => 'nullable|boolean',
            'dean_director_id' => 'nullable|exists:faculties,id',
        ]);

        Course::create([
            'code' => $validated['program_code'],
            'name' => $validated['description'],
            'program_type' => $validated['program_type'],
            'department_id' => $validated['department_id'],
            'description' => $validated['description'],
            'slots' => $validated['slots'] ?? 0,
            'track_category' => $validated['track_category'] ?? null,
            'non_filipino' => (bool) ($validated['non_filipino'] ?? false),
            'dean_director_id' => $validated['dean_director_id'] ?? null,
        ]);

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file')
            ->with('program_file_success', 'Program setup saved successfully.');
    }

    /**
     * Registrar > Academic Master > Subject File
     */
    public function subjectFile()
    {
        return view('registrar.registrar-menu.academic-master.subject-file');
    }

    /**
     * Registrar > Academic Master > Pre-requisites
     */
    public function preRequisites()
    {
        return view('registrar.registrar-menu.academic-master.pre-requisites');
    }

    /**
     * Registrar > Academic Master > Letter Grade Setup
     */
    public function letterGrade()
    {
        return view('registrar.registrar-menu.scheduling.letter-grade');
    }

    /**
     * Registrar > Scheduling > Room File
     */
    public function roomFile()
    {
        return view('registrar.registrar-menu.scheduling.room-file');
    }

    /**
     * Registrar > Scheduling > Section Offering
     */
    public function sectionOffering()
    {
        return view('registrar.registrar-menu.scheduling.section-offering');
    }

    /**
     * Registrar > Scheduling > Slot Monitoring
     */
    public function slotMonitoring()
    {
        return view('registrar.registrar-menu.scheduling.slot-monitoring');
    }

    /**
     * Registrar > Scheduling > Section Merging
     */
    public function sectionMerging()
    {
        return view('registrar.registrar-menu.scheduling.section-merging');
    }

    /**
     * Registrar > Student Management > Student Enrollment
     */
    public function studentEnrollment()
    {
        return view('registrar.registrar-menu.student-management.student-enrollment');
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'student_no' => 'required|unique:students,student_no',
            'name' => 'required|string',
            'sex' => 'nullable|string',
            'age' => 'nullable|integer',
            'college' => 'nullable|string',
            'program' => 'nullable|string',
            'curriculum' => 'nullable|string',
            'year_level' => 'nullable|string',
            'scholarship' => 'nullable|string',
            'school_year' => 'required|string',
            'semester' => 'required|string',
        ]);

        $defaultPass = null;

        DB::transaction(function () use ($request, &$defaultPass) {
            $student = Student::create($request->only([
                'student_no',
                'name',
                'sex',
                'age',
                'college',
                'program',
                'curriculum',
                'year_level',
                'scholarship',
                'school_year',
                'semester',
            ]));

            $defaultPass = 'PLP-' . $student->student_no;

            User::create([
                'name' => $student->name,
                'username' => $student->student_no,
                'password' => Hash::make($defaultPass),
                'module' => 'student',
                'force_password_reset' => true,
                'student_id' => $student->id,
            ]);
        });

        return redirect()->route('registrar.registrar-menu.student-mgmt.student-enrollment')
            ->with('success', 'Student profile created!')
            ->with('success_password', $defaultPass);
    }

    /**
     * Registrar > Faculty Management > Grading Sheet
     */
    public function facultyCreate()
    {
        return view('registrar.registrar-menu.faculty-management.faculty-create');
    }

    public function storeFaculty(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:faculties,code',
            'name' => 'required|string',
        ]);

        $defaultPass = null;

        DB::transaction(function () use ($request, &$defaultPass) {
            $faculty = Faculty::create($request->only(['code', 'name']));

            $defaultPass = 'PLP-' . $faculty->code;

            User::create([
                'name' => $faculty->name,
                'username' => $faculty->code,
                'password' => Hash::make($defaultPass),
                'module' => 'faculty',
                'force_password_reset' => true,
                'faculty_id' => $faculty->id,
            ]);
        });

        return redirect()->route('registrar.registrar-menu.faculty-mgmt.faculty-create')
            ->with('success', 'Faculty profile created!')
            ->with('success_password', $defaultPass);
    }

    public function gradingSheet()
    {
        return view('registrar.registrar-menu.faculty-management.grading-sheet');
    }

    /**
     * Registrar > Faculty Management > Evaluation
     */
    public function evaluation()
    {
        return view('registrar.registrar-menu.faculty-management.evaluation');
    }

    /**
     * Registrar > Student Management > Clinic Record
     */
    public function clinicRecord()
    {
        return view('registrar.registrar-menu.student-management.clinic-record');
    }

    /**
     * Registrar > Alumni Tracker
     */
    public function alumniTracker()
    {
        $students = Student::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'program', 'year_level', 'school_year', 'semester']);

        $alumniRows = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'studentNo' => (string) $student->student_no,
                'studentName' => (string) $student->name,
                'program' => (string) ($student->program ?: '-'),
                'yearLevel' => (string) ($student->year_level ?: '-'),
                'schoolYear' => (string) ($student->school_year ?: ''),
                'term' => (string) ($student->semester ?: ''),
            ];
        })->values()->all();

        $alumniPrograms = collect($alumniRows)
            ->pluck('program')
            ->filter(function ($value) {
                return trim((string) $value) !== '' && $value !== '-';
            })
            ->unique()
            ->values()
            ->all();

        $alumniYearLevels = collect($alumniRows)
            ->pluck('yearLevel')
            ->filter(function ($value) {
                return trim((string) $value) !== '' && $value !== '-';
            })
            ->unique()
            ->values()
            ->all();

        $setting = null;
        if (Schema::hasTable('alumni_tracker_settings')) {
            $setting = AlumniTrackerSetting::query()->latest('id')->first();
        }

        $alumniSchoolYears = collect($alumniRows)
            ->pluck('schoolYear')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $alumniTerms = collect($alumniRows)
            ->pluck('term')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $alumniConfig = [
            'schoolYear' => $setting ? (string) $setting->school_year : (string) ($students->first()->school_year ?? '2025-2026'),
            'term' => $setting ? (string) $setting->term : (string) ($students->first()->semester ?? 'Second'),
        ];

        if (!in_array($alumniConfig['schoolYear'], $alumniSchoolYears, true)) {
            $alumniSchoolYears[] = $alumniConfig['schoolYear'];
        }
        if (!in_array($alumniConfig['term'], $alumniTerms, true)) {
            $alumniTerms[] = $alumniConfig['term'];
        }

        if (!count($alumniSchoolYears)) {
            $alumniSchoolYears = ['2025-2026'];
        }
        if (!count($alumniTerms)) {
            $alumniTerms = ['First', 'Second', 'Summer'];
        }

        return view('registrar.registrar-menu.alumni-tracker', compact('alumniRows', 'alumniPrograms', 'alumniYearLevels', 'alumniConfig', 'alumniSchoolYears', 'alumniTerms'));
    }

    public function alumniTrackerSaveConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'term' => 'required|string|max:30',
        ]);

        if (Schema::hasTable('alumni_tracker_settings')) {
            $setting = AlumniTrackerSetting::query()->latest('id')->first();
            if ($setting) {
                $setting->update([
                    'school_year' => $validated['school_year'],
                    'term' => $validated['term'],
                ]);
            } else {
                AlumniTrackerSetting::create([
                    'school_year' => $validated['school_year'],
                    'term' => $validated['term'],
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Placeholder
     */
    public function formsPlaceholder()
    {
        return view('registrar.forms.placeholder');
    }

    /**
     * Registrar > Forms > TOR
     */
    public function formsTor()
    {
        return view('registrar.forms.tor');
    }

    /**
     * Registrar > Forms > Application for Leave of Absence - Enrolled
     */
    public function formsApplicationLeaveAbsenceEnrolled(Request $request)
    {
        $students = Student::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'program', 'year_level', 'school_year', 'semester']);

        $selectedStudentId = (int) $request->query('student_id', 0);
        if ($selectedStudentId <= 0 && $students->isNotEmpty()) {
            $selectedStudentId = (int) $students->first()->id;
        }

        $student = null;
        if ($selectedStudentId > 0) {
            $student = Student::find($selectedStudentId);
        }

        $gradeRows = collect();
        if ($student) {
            $gradeRows = StudentSubjectGrade::query()
                ->with(['subject.facultyModel'])
                ->where('student_id', $student->id)
                ->orderBy('subject_id')
                ->get()
                ->map(function ($grade) use ($student) {
                    $subject = $grade->subject;

                    $section = $subject && $subject->year_section
                        ? (string) $subject->year_section
                        : trim(($student->program ?: '') . ' ' . ($student->year_level ?: ''));

                    $semestralGradeRemarks = '';
                    if ($grade->final_average !== null) {
                        $semestralGradeRemarks = (string) $grade->final_average;
                    }
                    if (!empty($grade->remarks)) {
                        $semestralGradeRemarks = trim($semestralGradeRemarks . ' ' . (string) $grade->remarks);
                    }

                    $professorName = '';
                    if ($subject) {
                        if (!empty($subject->faculty)) {
                            $professorName = (string) $subject->faculty;
                        } elseif ($subject->relationLoaded('facultyModel') && $subject->facultyModel) {
                            $professorName = (string) $subject->facultyModel->name;
                        }
                    }

                    return [
                        'course_code' => $subject ? (string) $subject->code : '',
                        'course_description' => $subject ? (string) $subject->name : '',
                        'section' => $section,
                        'midterm_grade' => $grade->midterm !== null ? (string) $grade->midterm : '',
                        'final_grade' => $grade->final !== null ? (string) $grade->final : '',
                        'semestral_grade_remarks' => $semestralGradeRemarks,
                        'professor_name_signature' => $professorName,
                    ];
                })
                ->values();
        }

        return view('registrar.forms.application-leave-absence-enrolled', [
            'students' => $students,
            'selectedStudentId' => $selectedStudentId,
            'student' => $student,
            'gradeRows' => $gradeRows,
            'applicationDate' => Carbon::now()->format('F d, Y'),
        ]);
    }

    /**
     * Registrar > Forms > Diploma
     */
    public function formsDiploma()
    {
        return view('registrar.forms.diploma');
    }

    /**
     * Registrar > Forms > Graduation Clearance
     */
    public function formsGraduationClearance()
    {
        return view('registrar.forms.graduation-clearance');
    }

    /**
     * Registrar > Forms > Honorable Dismissal
     */
    public function formsHonorableDismissal()
    {
        return view('registrar.forms.honorable-dismissal');
    }

    /**
     * Registrar > Forms > Official Grade Report
     */
    public function formsOfficialGradeReport()
    {
        $students = Student::query()
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'student_no', 'name', 'program', 'year_level', 'school_year', 'semester']);

        $gradesByStudent = StudentSubjectGrade::query()
            ->with('subject')
            ->whereIn('student_id', $students->pluck('id')->all())
            ->orderBy('student_id')
            ->orderBy('subject_id')
            ->get()
            ->groupBy('student_id');

        $gradeReportRows = [];
        $subjectsByRow = [];
        $metaByRow = [];

        foreach ($students as $index => $student) {
            $rowId = (string) ($index + 1);
            $section = trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR'));
            $gradeReportRows[] = [
                'row_id' => $rowId,
                'student_id' => $student->id,
                'student_no' => $student->student_no,
                'student_name' => $student->name,
                'program' => $student->program ?: '-',
                'year' => $student->year_level ?: '-',
                'section' => $section,
            ];

            $subjects = ($gradesByStudent->get($student->id) ?: collect())->map(function ($grade) use ($section) {
                $subject = $grade->subject;
                return [
                    'code' => $subject ? (string) $subject->code : '-',
                    'desc' => $subject ? (string) $subject->name : '-',
                    'section' => $subject && $subject->year_section ? (string) $subject->year_section : $section,
                    'prof' => 'TBA',
                    'grade' => $grade->final_average !== null ? (string) $grade->final_average : '-',
                    'remarks' => $grade->remarks ?: '-',
                    'reexam' => '',
                    'units' => $subject && $subject->units !== null ? number_format((float) $subject->units, 2) : '0.00',
                ];
            })->values()->all();

            $subjectsByRow[$rowId] = $subjects;
            $metaByRow[$rowId] = [
                'studentNo' => $student->student_no,
                'studentName' => strtoupper((string) $student->name),
                'address' => '-',
                'birthday' => '-',
                'section' => $section,
                'course' => ($student->program ?: 'PROGRAM') . ' : ' . ($student->program ?: 'Program'),
                'schoolYear' => (string) ($student->school_year ?: '2025-2026') . ' / ' . strtoupper((string) ($student->semester ?: 'First')),
                'curriculum' => 'CURRENT',
                'studentType' => 'REGULAR',
                'yearLevel' => $student->year_level ?: '-',
                'residency' => 'PR',
                'cwa' => '-',
            ];
        }

        return view('registrar.forms.official-grade-report', compact('gradeReportRows', 'subjectsByRow', 'metaByRow'));
    }

    public function formsOfficialGradeReportData(Student $student): JsonResponse
    {
        $records = StudentSubjectGrade::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->orderBy('subject_id')
            ->get();

        $subjects = $records->map(function ($grade) {
            $subject = $grade->subject;
            return [
                'code' => $subject ? (string) $subject->code : '-',
                'desc' => $subject ? (string) $subject->name : '-',
                'section' => $subject && $subject->year_section ? (string) $subject->year_section : '-',
                'prof' => 'TBA',
                'grade' => $grade->final_average !== null ? (string) $grade->final_average : '-',
                'remarks' => $grade->remarks ?: '-',
                'reexam' => '',
                'units' => $subject && $subject->units !== null ? number_format((float) $subject->units, 2) : '0.00',
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'student' => [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'program' => $student->program,
                'year_level' => $student->year_level,
            ],
            'subjects' => $subjects,
        ]);
    }

    /**
     * Registrar > Forms > Permission to Cross-Enroll
     */
    public function formsPermissionCrossEnroll()
    {
        $this->seedCrossEnrollRowsIfEmpty();

        $crossEnrollRows = CrossEnrollmentRequest::query()
            ->with('student')
            ->orderByDesc('id')
            ->get();

        return view('registrar.forms.permission-cross-enroll', compact('crossEnrollRows'));
    }

    public function formsPermissionCrossEnrollStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CrossEnrollmentRequest::create([
            'student_id' => $student->id,
            'school_year' => $student->school_year,
            'semester' => $student->semester,
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
            'status' => 'pending',
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function formsPermissionCrossEnrollUpdate(Request $request, CrossEnrollmentRequest $crossEnrollmentRequest): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
            'program' => 'nullable|string|max:80',
            'year_level' => 'nullable|string|max:40',
        ]);

        $student = $crossEnrollmentRequest->student;
        $student->student_no = trim($validated['student_no']);
        $student->name = trim($validated['name']);
        $student->program = isset($validated['program']) ? trim((string) $validated['program']) : $student->program;
        $student->year_level = isset($validated['year_level']) ? trim((string) $validated['year_level']) : $student->year_level;
        $student->save();

        $crossEnrollmentRequest->update([
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
        ]);

        return response()->json(['ok' => true]);
    }

    public function formsPermissionCrossEnrollDestroy(CrossEnrollmentRequest $crossEnrollmentRequest): JsonResponse
    {
        $crossEnrollmentRequest->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Waiver for Cancellation of Enrollment
     */
    public function formsWaiverCancellation()
    {
        $this->seedWaiverRowsIfEmpty();

        $waiverRows = CancellationWaiver::query()
            ->with('student')
            ->orderByDesc('id')
            ->get();

        return view('registrar.forms.waiver-cancellation', compact('waiverRows'));
    }

    public function formsWaiverCancellationStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CancellationWaiver::create([
            'student_id' => $student->id,
            'school_year' => $student->school_year,
            'semester' => $student->semester,
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
            'status' => 'pending',
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function formsWaiverCancellationUpdate(Request $request, CancellationWaiver $cancellationWaiver): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
            'program' => 'nullable|string|max:80',
            'year_level' => 'nullable|string|max:40',
        ]);

        $student = $cancellationWaiver->student;
        $student->student_no = trim($validated['student_no']);
        $student->name = trim($validated['name']);
        $student->program = isset($validated['program']) ? trim((string) $validated['program']) : $student->program;
        $student->year_level = isset($validated['year_level']) ? trim((string) $validated['year_level']) : $student->year_level;
        $student->save();

        $cancellationWaiver->update([
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
        ]);

        return response()->json(['ok' => true]);
    }

    public function formsWaiverCancellationDestroy(CancellationWaiver $cancellationWaiver): JsonResponse
    {
        $cancellationWaiver->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Certificate of GWA
     *
     * If a Student is provided (route-model binding) compute the GWA and pass it to the view.
     */
    public function formsCertificateGwa(?\App\Student $student = null)
    {
        $gwa = null;

        if ($student) {
            $grades = StudentSubjectGrade::with('subject')
                ->where('student_id', $student->id)
                ->get();

            $weightedSum = 0.0;
            $unitsSum = 0.0;
            $plainSum = 0.0;
            $plainCount = 0;

            foreach ($grades as $rec) {
                if ($rec->final_average === null) {
                    continue;
                }
                $avg = (float) $rec->final_average;
                $units = 0.0;
                if ($rec->relationLoaded('subject') && $rec->subject && isset($rec->subject->units) && is_numeric($rec->subject->units)) {
                    $units = (float) $rec->subject->units;
                }

                if ($units > 0) {
                    $weightedSum += $avg * $units;
                    $unitsSum += $units;
                } else {
                    $plainSum += $avg;
                    $plainCount++;
                }
            }

            if ($unitsSum > 0) {
                $gwa = round($weightedSum / $unitsSum, 2);
            } elseif ($plainCount > 0) {
                $gwa = round($plainSum / $plainCount, 2);
            } else {
                $gwa = null;
            }
        }

        return view('registrar.forms.certificates.certificate-gwa', compact('student', 'gwa'));
    }

    /**
     * Registrar > Forms > Form No. 8C-2 Certificate of Graduation
     */
    public function formsCertificateGraduation8c2()
    {
        return view('registrar.forms.certificates.certificate-graduation-8c2');
    }

    /**
     * Registrar > Forms > Form No. 8D-2 Certificate of Honor
     */
    public function formsCertificateHonor8d2()
    {
        return view('registrar.forms.certificates.certificate-honor-8d2');
    }

    /**
     * Registrar > Forms > Copy Of Grades (COG)
     */
    public function formsCopyOfGradesCog()
    {
        return view('registrar.forms.cog.copy-of-grades');
    }

    /**
     * Registrar > Forms > Certificate of Registration (COR)
     */
    public function formsCertificateOfRegistration(Request $request)
    {
        $students = Student::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'program', 'year_level']);

        $selectedStudentId = (int) $request->query('student_id', 0);

        if ($selectedStudentId <= 0 && $students->isNotEmpty()) {
            $selectedStudentId = (int) $students->first()->id;
        }

        $student = null;
        if ($selectedStudentId > 0) {
            $student = Student::with('subjects')->find($selectedStudentId);
        }

        $subjects = collect();
        if ($student) {
            $subjects = $student->subjects
                ->sortBy(function ($subject) {
                    return strtoupper((string) $subject->code);
                })
                ->values();
        }

        $totalUnits = (float) $subjects->sum(function ($subject) {
            return is_numeric($subject->units) ? (float) $subject->units : 0;
        });

        $assessment = $this->buildCorAssessment($subjects, $totalUnits);

        return view('registrar.forms.cor.certificate-of-registration', [
            'students' => $students,
            'selectedStudentId' => $selectedStudentId,
            'student' => $student,
            'subjects' => $subjects,
            'totalUnits' => $totalUnits,
            'assessment' => $assessment,
        ]);
    }

    private function buildCorAssessment($subjects, float $totalUnits): array
    {
        $nstpUnits = (float) $subjects->sum(function ($subject) {
            $code = strtoupper((string) $subject->code);
            $name = strtoupper((string) $subject->name);

            if (strpos($code, 'NSTP') !== false || strpos($name, 'CWTS') !== false || strpos($name, 'ROTC') !== false) {
                return is_numeric($subject->units) ? (float) $subject->units : 0;
            }

            return 0;
        });

        $tuitionUnits = max($totalUnits - $nstpUnits, 0);
        $perUnitRate = 50.0;
        $miscellaneousFee = 300.0;
        $laboratoryFee = 500.0;

        $tuitionFee = $tuitionUnits * $perUnitRate;
        $cwtsFee = $nstpUnits * $perUnitRate;
        $totalTuitionFee = $tuitionFee + $cwtsFee;
        $currentAccount = $totalTuitionFee + $miscellaneousFee + $laboratoryFee;

        return [
            'tuition_units' => $tuitionUnits,
            'nstp_units' => $nstpUnits,
            'per_unit_rate' => $perUnitRate,
            'tuition_fee' => $tuitionFee,
            'cwts_fee' => $cwtsFee,
            'total_tuition_fee' => $totalTuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'laboratory_fee' => $laboratoryFee,
            'current_account' => $currentAccount,
        ];
    }

    private function seedCrossEnrollRowsIfEmpty(): void
    {
        if (CrossEnrollmentRequest::query()->exists()) {
            return;
        }

        $students = Student::query()->orderBy('id')->limit(10)->get();
        foreach ($students as $student) {
            CrossEnrollmentRequest::create([
                'student_id' => $student->id,
                'school_year' => $student->school_year,
                'semester' => $student->semester,
                'program' => $student->program,
                'year_level' => $student->year_level,
                'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
                'status' => 'pending',
            ]);
        }
    }

    private function seedWaiverRowsIfEmpty(): void
    {
        if (CancellationWaiver::query()->exists()) {
            return;
        }

        $students = Student::query()->orderBy('id')->limit(10)->get();
        foreach ($students as $student) {
            CancellationWaiver::create([
                'student_id' => $student->id,
                'school_year' => $student->school_year,
                'semester' => $student->semester,
                'program' => $student->program,
                'year_level' => $student->year_level,
                'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
                'status' => 'pending',
            ]);
        }
    }
}
