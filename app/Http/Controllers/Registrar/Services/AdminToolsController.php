<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\StudentProfile;

class AdminToolsController extends Controller
{
    public function configuration()
    {
        return view('registrar.admin-tools.system-config.configuration');
    }

    public function admissionConfig()
    {
        return view('registrar.admin-tools.system-config.admission-config');
    }

    public function academicCalendar()
    {
        return view('registrar.admin-tools.system-config.academic-calendar');
    }

    public function announcement()
    {
        return view('registrar.admin-tools.system-config.announcement');
    }

    // Access Management
    public function userAccounts()
    {
        return view('registrar.admin-tools.access-management.user-accounts');
    }

    public function reportAccess()
    {
        return view('registrar.admin-tools.access-management.report-access');
    }

    // Master Files
    public function facultyFile()
    {
        return view('registrar.admin-tools.master-files.faculty-file');
    }

    public function studentProfile()
    {
        $studentNo = request('student_id');
        $previewProfile = null;

        if (!empty($studentNo)) {
            $previewProfile = StudentProfile::where('student_no', $studentNo)->first();
        }

        if (!$previewProfile) {
            $previewProfile = StudentProfile::orderBy('id')->first();
        }

        return view('registrar.admin-tools.master-files.student-profile', compact('previewProfile'));
    }

    public function studentGradeFile()
    {
        return view('registrar.admin-tools.master-files.student-grade-file');
    }

    // Student Maintenance
    public function bedStudentStatus()
    {
        return view('registrar.admin-tools.student-maintenance.bed-student-status');
    }

    public function bedDays()
    {
        return view('registrar.admin-tools.student-maintenance.bed-days');
    }

    public function studentUpdate()
    {
        return view('registrar.admin-tools.student-maintenance.student-update');
    }
}
