<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantController extends Controller
{
    private function getApplicant()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if (is_null($user->applicant_id) && !empty($user->username)) {
            $legacyApplicant = Applicant::where('applicant_id', $user->username)->first();
            if ($legacyApplicant) {
                $user->applicant_id = $legacyApplicant->id;
                $user->save();
            }
        }

        if (is_null($user->applicant_id)) {
            abort(403, 'Applicant account is not linked yet.');
        }

        return Applicant::findOrFail($user->applicant_id);
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
