<?php

namespace App\Http\Controllers\Registrar\Services;

use App\CertificateIssued;
use App\GraduateTagging;
use App\Http\Controllers\Controller;
use App\Student;
use App\StudentSubjectGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsAdminController extends Controller
{
    public function academicReports()
    {
        $totalStudents = Student::query()->count();
        $failingStudents = StudentSubjectGrade::query()
            ->whereNotNull('final_average')
            ->where('final_average', '>=', 3.0)
            ->distinct()
            ->count('student_id');
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'student_no', 'name']);

        $summary = [
            'total_students' => $totalStudents,
            'failing_students' => $failingStudents,
            'passing_students' => max($totalStudents - $failingStudents, 0),
        ];

        return view('registrar.services.reports-admin.academic-reports', compact('summary', 'students'));
    }

    public function guidanceReports()
    {
        return view('registrar.services.reports-admin.guidance-reports');
    }

    public function certifications()
    {
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'student_no', 'name']);
        $recentCertificates = CertificateIssued::query()
            ->with('student')
            ->orderByDesc('date_issued')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('registrar.services.reports-admin.certifications', compact('students', 'recentCertificates'));
    }

    public function taggingOfGraduates()
    {
        $students = Student::query()
            ->orderBy('name')
            ->paginate(10);

        $studentIds = [];
        foreach ($students->items() as $student) {
            $studentIds[] = $student->id;
        }

        $taggings = GraduateTagging::query()
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        return view('registrar.services.reports-admin.tagging-of-graduates', compact('students', 'taggings'));
    }

    public function issueAcademicReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'report_type' => 'required|string|max:120',
            'purpose' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CertificateIssued::create([
            'student_id' => $student->id,
            'certificate_type' => trim($validated['report_type']),
            'purpose' => isset($validated['purpose']) ? trim((string) $validated['purpose']) : null,
            'date_issued' => $validated['date_issued'] ?? now()->toDateString(),
            'issued_by' => optional(auth()->user())->name ?: 'Registrar',
            'school_year' => $student->school_year,
            'semester' => $student->semester,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function issueCertification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|string|max:120',
            'purpose' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CertificateIssued::create([
            'student_id' => $student->id,
            'certificate_type' => trim($validated['certificate_type']),
            'purpose' => isset($validated['purpose']) ? trim((string) $validated['purpose']) : null,
            'date_issued' => $validated['date_issued'] ?? now()->toDateString(),
            'issued_by' => optional(auth()->user())->name ?: 'Registrar',
            'school_year' => $student->school_year,
            'semester' => $student->semester,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function taggingOfGraduatesUpdate(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'is_graduate' => 'nullable|boolean',
            'date_graduated' => 'nullable|date',
            'so_number' => 'nullable|string|max:80',
            'so_date' => 'nullable|date',
            'suspend_account' => 'nullable|boolean',
        ]);

        GraduateTagging::updateOrCreate(
            ['student_id' => $student->id],
            [
                'is_graduate' => (bool) ($validated['is_graduate'] ?? false),
                'date_graduated' => $validated['date_graduated'] ?? null,
                'so_number' => isset($validated['so_number']) ? trim((string) $validated['so_number']) : null,
                'so_date' => $validated['so_date'] ?? null,
                'suspend_account' => (bool) ($validated['suspend_account'] ?? false),
            ]
        );

        return response()->json(['ok' => true]);
    }
}
