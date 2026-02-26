<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    /**
     * Show the Section Offering page.
     */
    public function sectionOffering()
    {
        return view('student.section-offering');
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
        return view('student.schedule');
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
