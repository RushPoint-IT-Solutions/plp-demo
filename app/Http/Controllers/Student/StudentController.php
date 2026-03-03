<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Student;
use App\Subject;
use App\Semester;
use App\Course;
use App\YearBlock;

class StudentController extends Controller
{
    /**
     * Show the Section Offering / COR page.
     */
    public function sectionOffering()
    {
        $student    = Student::with('subjects')->first();
        $subjects   = $student ? $student->subjects : collect();
        $semesters  = Semester::all();
        $courses    = Course::all();
        $yearBlocks = YearBlock::all();

        return view('student.section-offering', compact(
            'student', 'subjects', 'semesters', 'courses', 'yearBlocks'
        ));
    }

    /**
     * Show the Grades page.
     */
    public function grades()
    {
        return view('student.grades');
    }

    /**
     * Show the Schedule page.
     */
    public function schedule()
    {
        $student  = Student::with('subjects')->first();
        $subjects = $student ? $student->subjects : collect();

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
            foreach (array_map('trim', explode(',', $subject->days ?? '')) as $abbr) {
                if (isset($dayMap[$abbr])) {
                    $weekly[$dayMap[$abbr]][] = $subject;
                }
            }
        }

        return view('student.schedule', compact('student', 'subjects', 'weekly', 'days'));
    }

    /**
     * Show the Events page.
     */
    public function events()
    {
        return view('student.events');
    }

    /**
     * Show the Profile page.
     */
    public function profile()
    {
        return view('student.profile');
    }
}
