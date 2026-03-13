<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    // Demo: fixed applicant used throughout the portal
    private const APPLICANT_ID = '2526B0177';

    private function getApplicant()
    {
        return Applicant::where('applicant_id', self::APPLICANT_ID)->firstOrFail();
    }

    /**
     * Application Form – personal + residence information.
     */
    public function applicationForm()
    {
        $applicant = $this->getApplicant();
        return view('applicant.application-form', compact('applicant'));
    }

    /**
     * Save Application Form (POST).
     */
    public function saveApplicationForm(Request $request)
    {
        $applicant = $this->getApplicant();

        $applicant->update($request->except(['_token', '_method']));

        return redirect()->route('applicant.application-form')
            ->with('success', 'Application form saved successfully.');
    }

    /**
     * Schedule of Exam – shows exam permit + reminders.
     */
    public function scheduleOfExam()
    {
        $applicant = $this->getApplicant();
        return view('applicant.schedule-of-exam', compact('applicant'));
    }

    /**
     * Exam Result – shows pass/fail result.
     */
    public function examResult()
    {
        $applicant = $this->getApplicant();
        return view('applicant.exam-result', compact('applicant'));
    }
}
