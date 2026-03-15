<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;

class GradingAcademicController extends Controller
{
    public function gradingSystem()
    {
        return view('registrar.services.grading-academic.grading-system');
    }

    public function gradingPeriods()
    {
        return view('registrar.services.grading-academic.grading-periods');
    }

    public function gradingComponents()
    {
        return view('registrar.services.grading-academic.grading-components');
    }

    public function transmutation()
    {
        return view('registrar.services.grading-academic.transmutation');
    }

    public function deficiency()
    {
        return view('registrar.services.grading-academic.deficiency');
    }
}
