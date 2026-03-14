<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Subject;
use App\FacultyEvaluation;
use App\StudentSubjectGrade;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    // Demo: faculty name used to filter subjects
    private const FACULTY = 'Abejo, M.';

    /**
     * Faculty Load – assigned subjects/schedule.
     */
    public function facultyLoad()
    {
        $subjects = Subject::where('faculty', self::FACULTY)->get();
        return view('faculty.faculty-load', compact('subjects'));
    }

    /**
     * Class List – subjects with enrolled students.
     */
    public function classList()
    {
        $subjects = Subject::where('faculty', self::FACULTY)
            ->with('students')
            ->get();

        $subjectStudents = $subjects->keyBy('id')->map(function ($s) {
            return $s->students->map(function ($st) {
                return [
                    'student_no' => $st->student_no,
                    'name'       => $st->name,
                    'program_block' => trim(($st->program ?: '-') . ' ' . ($st->year_level ?: '-')),
                    'status'     => 'Enrolled',
                ];
            })->values();
        });

        return view('faculty.class-list', compact('subjects', 'subjectStudents'));
    }

    /**
     * Calendar – university events calendar (JS-driven).
     */
    public function calendar()
    {
        return view('faculty.calendar');
    }

    /**
     * Grading Sheet – subjects with grading status.
     */
    public function gradingSheet()
    {
        $subjects = Subject::where('faculty', self::FACULTY)
            ->with(['students', 'studentGrades'])
            ->get();

        $gradingSubjects = $subjects->map(function ($subject) {
            $gradeMap = $subject->studentGrades->keyBy('student_id');

            $students = $subject->students->values()->map(function ($student) use ($gradeMap) {
                $grade = $gradeMap->get($student->id);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'prelim' => $grade ? number_format((float) $grade->prelim, 2) : '',
                    'midterm' => $grade ? number_format((float) $grade->midterm, 2) : '',
                    'final' => $grade ? number_format((float) $grade->final, 2) : '',
                    'final_average' => $grade ? number_format((float) $grade->final_average, 2) : '',
                    'remarks' => $grade ? $grade->remarks : '',
                ];
            });

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'units' => number_format((float) $subject->units, 1),
                'days' => str_replace(',', ', ', (string) $subject->days),
                'section' => trim(($subject->course ?: '') . ' ' . ($subject->year_section ?: '')),
                'status' => $subject->grading_status,
                'students' => $students,
            ];
        })->values();

        return view('faculty.grading-sheet', compact('subjects', 'gradingSubjects'));
    }

    /**
     * Persist faculty-submitted grades for a selected subject.
     */
    public function updateGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array',
        ]);

        $subject = Subject::with('students')->findOrFail($request->input('subject_id'));
        $gradesInput = $request->input('grades', []);

        foreach ($subject->students as $student) {
            $row = $gradesInput[$student->id] ?? null;
            if (!$row) {
                continue;
            }

            $prelim = isset($row['prelim']) && $row['prelim'] !== '' ? (float) $row['prelim'] : null;
            $midterm = isset($row['midterm']) && $row['midterm'] !== '' ? (float) $row['midterm'] : null;
            $final = isset($row['final']) && $row['final'] !== '' ? (float) $row['final'] : null;

            if ($prelim === null || $midterm === null || $final === null) {
                continue;
            }

            if ($prelim < 1 || $prelim > 5 || $midterm < 1 || $midterm > 5 || $final < 1 || $final > 5) {
                continue;
            }

            $average = round(($prelim + $midterm + $final) / 3, 2);
            $remarks = $average <= 3.00 ? 'Passed' : 'Failed';

            StudentSubjectGrade::updateOrCreate(
                ['subject_id' => $subject->id, 'student_id' => $student->id],
                [
                    'prelim' => $prelim,
                    'midterm' => $midterm,
                    'final' => $final,
                    'final_average' => $average,
                    'remarks' => $remarks,
                ]
            );
        }

        $subject->grading_status = 'Submitted';
        $subject->save();

        return redirect()->route('faculty.grading-sheet')->with('success', 'Grades updated successfully.');
    }

    /**
     * Faculty Evaluation – subjects with mean scores.
     */
    public function evaluation()
    {
        $subjects = Subject::where('faculty', self::FACULTY)
            ->with('evaluations')
            ->get();

        $evaluationDetails = [];
        foreach ($subjects as $subject) {
            foreach ($subject->evaluations as $eval) {
                $base = (float) $eval->mean_score;
                $criteria = [
                    ['label' => 'A. Commitment', 'score' => max(1, min(5, round($base - 0.1, 2)))],
                    ['label' => 'B. Knowledge of Subject Matter', 'score' => max(1, min(5, round($base + 0.1, 2)))],
                    ['label' => 'C. Knowledge of Subject Matter', 'score' => max(1, min(5, round($base - 0.2, 2)))],
                    ['label' => 'D. Management of Learning', 'score' => max(1, min(5, round($base, 2)))],
                ];

                $overall = round(collect($criteria)->avg('score'), 2);
                $interpretation = $overall >= 4.5 ? 'Outstanding' : ($overall >= 4.0 ? 'Very Satisfactory' : ($overall >= 3.0 ? 'Satisfactory' : 'Needs Improvement'));

                $evaluationDetails[$eval->id] = [
                    'subject' => $subject->name,
                    'section' => $eval->section,
                    'criteria' => $criteria,
                    'overall' => $overall,
                    'interpretation' => $interpretation,
                ];
            }
        }

        return view('faculty.evaluation', compact('subjects', 'evaluationDetails'));
    }
}
