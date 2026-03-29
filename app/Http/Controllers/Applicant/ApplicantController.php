<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Applicant;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $validated = $request->validate([
            'lrn' => 'nullable|string|max:40',
            'last_name' => 'required|string|max:120',
            'first_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'suffix' => 'nullable|string|max:40',
            'nickname' => 'nullable|string|max:120',
            'gender' => 'nullable|in:Male,Female',
            'nationality' => 'nullable|string|max:120',
            'religion' => 'nullable|string|max:120',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:190',
            'age' => 'nullable|integer|min:0|max:120',
            'civil_status' => 'nullable|string|max:60',
            'mobile_number' => 'nullable|string|max:30',
            'email_address' => 'nullable|email|max:190',
            'present_street' => 'nullable|string|max:190',
            'present_barangay' => 'nullable|string|max:190',
            'present_zipcode' => 'nullable|string|max:20',
            'present_municipality' => 'nullable|string|max:190',
            'present_province' => 'nullable|string|max:190',
            'present_region' => 'nullable|string|max:190',
            'same_as_present' => 'nullable|boolean',
            'permanent_street' => 'nullable|string|max:190',
            'permanent_barangay' => 'nullable|string|max:190',
            'permanent_zipcode' => 'nullable|string|max:20',
            'permanent_municipality' => 'nullable|string|max:190',
            'permanent_province' => 'nullable|string|max:190',
            'permanent_region' => 'nullable|string|max:190',
            'photo' => 'nullable|image|max:3072',
        ]);

        $validated['same_as_present'] = $request->boolean('same_as_present');

        if (!empty($validated['date_of_birth']) && empty($validated['age'])) {
            $validated['age'] = Carbon::parse($validated['date_of_birth'])->age;
        }

        if ($validated['same_as_present']) {
            $validated['permanent_street'] = $validated['present_street'] ?? null;
            $validated['permanent_barangay'] = $validated['present_barangay'] ?? null;
            $validated['permanent_zipcode'] = $validated['present_zipcode'] ?? null;
            $validated['permanent_municipality'] = $validated['present_municipality'] ?? null;
            $validated['permanent_province'] = $validated['present_province'] ?? null;
            $validated['permanent_region'] = $validated['present_region'] ?? null;
        }

        if ($request->hasFile('photo')) {
            if (!empty($applicant->photo)) {
                Storage::disk('public')->delete($applicant->photo);
            }
            $validated['photo'] = $request->file('photo')->store('applicants/photos', 'public');
        }

        $applicant->fill($validated);
        $applicant->save();

        $user = $request->user();
        if ($user) {
            $user->name = trim(implode(' ', array_filter([
                $applicant->first_name,
                $applicant->middle_name,
                $applicant->last_name,
            ])));

            if (!empty($applicant->email_address)) {
                $emailTaken = User::query()
                    ->where('email', $applicant->email_address)
                    ->where('id', '<>', $user->id)
                    ->exists();

                if (!$emailTaken) {
                    $user->email = $applicant->email_address;
                }
            }

            $user->save();
        }

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
