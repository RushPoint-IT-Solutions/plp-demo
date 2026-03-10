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
        return view('registrar.process.application-process');
    }

    /**
     * Process > Requirements
     */
    public function requirements()
    {
        return view('registrar.process.requirements');
    }

    /**
     * Process > Citizenship
     */
    public function citizenship()
    {
        return view('registrar.process.citizenship');
    }

    /**
     * Process > Religion
     */
    public function religion()
    {
        return view('registrar.process.religion');
    }

    /**
     * Process > Approval Status
     */
    public function approvalStatus()
    {
        return view('registrar.process.approval-status');
    }

    /**
     * Process > Batch Upload Image
     */
    public function batchUpload()
    {
        return view('registrar.process.batch-upload');
    }

    /**
     * Process > Document List
     */
    public function documentList()
    {
        return view('registrar.process.document-list');
    }

    /**
     * Process > Reports
     */
    public function reports()
    {
        return view('registrar.process.reports');
    }

    /**
     * Registrar > Academic Master > Program File
     */
    public function programFile()
    {
        return view('registrar.registrar-menu.program-file');
    }

    /**
     * Registrar > Academic Master > Subject File
     */
    public function subjectFile()
    {
        return view('registrar.registrar-menu.subject-file');
    }

    /**
     * Registrar > Academic Master > Pre-requisites
     */
    public function preRequisites()
    {
        return view('registrar.registrar-menu.pre-requisites');
    }

    /**
     * Registrar > Academic Master > Letter Grade Setup
     */
    public function letterGrade()
    {
        return view('registrar.registrar-menu.letter-grade');
    }
}
