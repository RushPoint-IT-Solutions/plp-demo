<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegistrarController extends Controller
{
    /**
     * Registrar Dashboard
     */
    public function dashboard()
    {
        return view('registrar.dashboard');
    }

    /**
     * Process > Application Process
     */
    public function applicationProcess()
    {
        return view('registrar.application-process');
    }

    /**
     * Process > Citizenship
     */
    public function citizenship()
    {
        return view('registrar.citizenship');
    }

    /**
     * Process > Religion
     */
    public function religion()
    {
        return view('registrar.religion');
    }

    /**
     * Process > Approval Status
     */
    public function approvalStatus()
    {
        return view('registrar.approval-status');
    }

    /**
     * Process > Batch Upload Image
     */
    public function batchUpload()
    {
        return view('registrar.batch-upload');
    }

    /**
     * Process > Document List
     */
    public function documentList()
    {
        return view('registrar.document-list');
    }

    /**
     * Process > Admission Report
     */
    public function admissionReport()
    {
        return view('registrar.admission-report');
    }

    /**
     * Process > Schedule of Exam
     */
    public function scheduleExam()
    {
        return view('registrar.schedule-exam');
    }
}
