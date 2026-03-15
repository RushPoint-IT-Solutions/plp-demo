<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;

class ReportsAdminController extends Controller
{
    public function academicReports()
    {
        return view('registrar.services.reports-admin.academic-reports');
    }

    public function guidanceReports()
    {
        return view('registrar.services.reports-admin.guidance-reports');
    }

    public function certifications()
    {
        return view('registrar.services.reports-admin.certifications');
    }

    public function taggingOfGraduates()
    {
        return view('registrar.services.reports-admin.tagging-of-graduates');
    }
}
