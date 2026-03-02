<?php

namespace App\Http\Controllers;

use App\Student;
use App\Subject;
use App\Semester;
use App\Course;
use App\YearBlock;

class StudentController extends Controller
{
    // -------------------------------------------------------
    //  /section-offering
    //  Shows the COR / section offering page for student id=1
    // -------------------------------------------------------
    public function sectionOffering()
    {
        $student    = Student::with('subjects')->firstOrFail();
        $subjects   = $student->subjects;
        $semesters  = Semester::all();
        $courses    = Course::all();
        $yearBlocks = YearBlock::all();

        return view('student.section-offering', compact(
            'student', 'subjects', 'semesters', 'courses', 'yearBlocks'
        ));
    }

    // -------------------------------------------------------
    //  /schedule
    //  Shows the subject list table + weekly grid
    // -------------------------------------------------------
    public function schedule()
    {
        $student  = Student::with('subjects')->firstOrFail();
        $subjects = $student->subjects;

        // Map abbreviated day codes to full day names
        $dayMap = [
            'Sun' => 'Sunday',
            'M'   => 'Monday',   'Mon' => 'Monday',
            'T'   => 'Tuesday',  'Tue' => 'Tuesday',
            'W'   => 'Wednesday','Wed' => 'Wednesday',
            'Th'  => 'Thursday', 'Thu' => 'Thursday',
            'F'   => 'Friday',   'Fri' => 'Friday',
            'Sat' => 'Saturday',
        ];

        $days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $weekly = array_fill_keys($days, []);

        foreach ($subjects as $subject) {
            foreach (array_map('trim', explode(',', $subject->days)) as $abbr) {
                if (isset($dayMap[$abbr])) {
                    $weekly[$dayMap[$abbr]][] = $subject;
                }
            }
        }

        return view('student.schedule', compact('student', 'subjects', 'weekly', 'days'));
    }
}
