<?php

namespace App\Http\Controllers\Registrar;

use App\Course;
use App\Department;
use App\Faculty;
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
     * Registrar Messaging
     */
    public function messaging()
    {
        return view('registrar.messaging');
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
    public function programFile(Request $request)
    {
        $departments = Department::orderBy('description')->get();
        $faculties = Faculty::orderBy('name')->get();

        $programs = Course::with(['department', 'deanDirector'])
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->where('department_id', $request->input('department_id'));
            })
            ->when($request->filled('program_type'), function ($query) use ($request) {
                $query->where('program_type', $request->input('program_type'));
            })
            ->when($request->filled('program_code'), function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->input('program_code') . '%');
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $term = $request->input('description');
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('code')
            ->get();

        return view('registrar.registrar-menu.academic-master.program-file', [
            'departments' => $departments,
            'faculties' => $faculties,
            'programs' => $programs,
            'filters' => [
                'department_id' => $request->input('department_id', ''),
                'program_type' => $request->input('program_type', ''),
                'program_code' => $request->input('program_code', ''),
                'description' => $request->input('description', ''),
            ],
        ]);
    }

    /**
     * Registrar > Academic Master > Program File > Save setup modal
     */
    public function saveProgramSetup(Request $request)
    {
        $validated = $request->validate([
            'program_type' => 'required|string|max:80',
            'program_code' => 'required|string|max:30|unique:courses,code',
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string|max:255',
            'slots' => 'nullable|integer|min:0',
            'track_category' => 'nullable|in:Academic,TVL,Academic/TVL',
            'non_filipino' => 'nullable|boolean',
            'dean_director_id' => 'nullable|exists:faculties,id',
        ]);

        Course::create([
            'code' => $validated['program_code'],
            'name' => $validated['description'],
            'program_type' => $validated['program_type'],
            'department_id' => $validated['department_id'],
            'description' => $validated['description'],
            'slots' => $validated['slots'] ?? 0,
            'track_category' => $validated['track_category'] ?? null,
            'non_filipino' => (bool) ($validated['non_filipino'] ?? false),
            'dean_director_id' => $validated['dean_director_id'] ?? null,
        ]);

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file')
            ->with('program_file_success', 'Program setup saved successfully.');
    }

    /**
     * Registrar > Academic Master > Subject File
     */
    public function subjectFile()
    {
        return view('registrar.registrar-menu.academic-master.subject-file');
    }

    /**
     * Registrar > Academic Master > Pre-requisites
     */
    public function preRequisites()
    {
        return view('registrar.registrar-menu.academic-master.pre-requisites');
    }

    /**
     * Registrar > Academic Master > Letter Grade Setup
     */
    public function letterGrade()
    {
        return view('registrar.registrar-menu.scheduling.letter-grade');
    }

    /**
     * Registrar > Scheduling > Room File
     */
    public function roomFile()
    {
        return view('registrar.registrar-menu.scheduling.room-file');
    }

    /**
     * Registrar > Scheduling > Section Offering
     */
    public function sectionOffering()
    {
        return view('registrar.registrar-menu.scheduling.section-offering');
    }

    /**
     * Registrar > Scheduling > Slot Monitoring
     */
    public function slotMonitoring()
    {
        return view('registrar.registrar-menu.scheduling.slot-monitoring');
    }

    /**
     * Registrar > Scheduling > Section Merging
     */
    public function sectionMerging()
    {
        return view('registrar.registrar-menu.scheduling.section-merging');
    }

    /**
     * Registrar > Student Management > Student Enrollment
     */
    public function studentEnrollment()
    {
        return view('registrar.registrar-menu.student-management.student-enrollment');
    }

    /**
     * Registrar > Faculty Management > Grading Sheet
     */
    public function gradingSheet()
    {
        return view('registrar.registrar-menu.faculty-management.grading-sheet');
    }

    /**
     * Registrar > Faculty Management > Evaluation
     */
    public function evaluation()
    {
        return view('registrar.registrar-menu.faculty-management.evaluation');
    }

    /**
     * Registrar > Student Management > Clinic Record
     */
    public function clinicRecord()
    {
        return view('registrar.registrar-menu.student-management.clinic-record');
    }

    /**
     * Registrar > Alumni Tracker
     */
    public function alumniTracker()
    {
        return view('registrar.registrar-menu.alumni-tracker');
    }
}
