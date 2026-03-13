<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Subject;
use App\FacultyEvaluation;
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
                    'program'    => $st->program,
                    'year_level' => $st->year_level,
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
        $subjects = Subject::where('faculty', self::FACULTY)->get();
        return view('faculty.grading-sheet', compact('subjects'));
    }

    /**
     * Faculty Evaluation – subjects with mean scores.
     */
    public function evaluation()
    {
        $subjects = Subject::where('faculty', self::FACULTY)
            ->with('evaluations')
            ->get();
        return view('faculty.evaluation', compact('subjects'));
    }
}
