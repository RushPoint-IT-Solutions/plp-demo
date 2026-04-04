<?php

namespace App\Http\Controllers\Applicant;

use App\Applicant;
use App\ApplicantApplicationPreference;
use App\ApplicantEducationalBackground;
use App\ApplicantFamilyBackground;
use App\AcademicCalendarEvent;
use App\Course;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveApplicantStep1Request;
use App\Http\Requests\SaveApplicantStep2Request;
use App\Http\Requests\SaveApplicantStep3Request;
use App\Http\Requests\SaveApplicantStep4Request;
use App\Http\Requests\SubmitApplicantApplicationRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ApplicantController extends Controller
{
    private function requestBoolean(Request $request, $key)
    {
        $value = $request->input($key);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
        }

        return false;
    }

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

    private function markDraftProgress(Applicant $applicant, $step)
    {
        $currentStep = (int) $applicant->application_draft_step;
        $applicant->application_status = 'draft';
        $applicant->application_draft_step = max($currentStep, (int) $step);
        $applicant->application_submitted_at = null;
        $applicant->application_portal_stage = 0;
    }

    private function hasCompletedStep1(Applicant $applicant)
    {
        $requiredFields = [
            'last_name',
            'first_name',
            'gender',
            'date_of_birth',
            'mobile_number',
            'email_address',
            'present_street',
            'present_barangay',
            'present_zipcode',
            'present_municipality',
            'present_province',
            'present_region',
        ];

        foreach ($requiredFields as $field) {
            $value = $applicant->{$field};
            if (is_null($value) || trim((string) $value) === '') {
                return false;
            }
        }

        return true;
    }

    private function hasCompletedStep2(Applicant $applicant)
    {
        if (!$applicant->relationLoaded('educationalBackground')) {
            $applicant->load('educationalBackground');
        }

        $record = $applicant->educationalBackground;
        if (!$record) {
            return false;
        }

        if (trim((string) $record->junior_school) === '') {
            return false;
        }

        if (!$record->no_k12 && trim((string) $record->senior_school) === '') {
            return false;
        }

        if (trim((string) $record->shs_track_strand) === '') {
            return false;
        }

        if (trim((string) $record->learner_reference_number) === '') {
            return false;
        }

        return true;
    }

    private function hasCompletedStep3(Applicant $applicant)
    {
        return (int) $applicant->application_draft_step >= 3;
    }

    private function stepBlockedResponse(Request $request, $step, $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->route('applicant.application-form')
            ->withErrors(['step' => $message])
            ->withInput(['active_step' => $step]);
    }

    private function enforceStepPrerequisite(Request $request, Applicant $applicant, $targetStep)
    {
        if ((int) $targetStep === 2 && !$this->hasCompletedStep1($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                1,
                'Complete and save Step 1 before proceeding to Step 2.'
            );
        }

        if ((int) $targetStep === 3 && !$this->hasCompletedStep2($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                2,
                'Complete and save Step 2 before proceeding to Step 3.'
            );
        }

        if ((int) $targetStep === 4 && !$this->hasCompletedStep3($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                3,
                'Complete and save Step 3 before proceeding to Step 4.'
            );
        }

        return null;
    }

    private function syncUserFromApplicant($user, Applicant $applicant)
    {
        if (!$user) {
            return;
        }

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

    private function applyStep1Payload(Applicant $applicant, array $validated, Request $request)
    {
        $validated['same_as_present'] = $this->requestBoolean($request, 'same_as_present');

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
    }

    private function upsertEducationalBackground(Applicant $applicant, array $payload)
    {
        $record = $applicant->educationalBackground;
        if (!$record) {
            $record = new ApplicantEducationalBackground();
            $record->applicant_id = $applicant->id;
        }

        $record->fill($payload);
        $record->save();
    }

    private function upsertFamilyBackground(Applicant $applicant, array $payload)
    {
        $record = $applicant->familyBackground;
        if (!$record) {
            $record = new ApplicantFamilyBackground();
            $record->applicant_id = $applicant->id;
        }

        $record->fill($payload);
        $record->save();
    }

    private function upsertApplicationPreference(Applicant $applicant, array $payload)
    {
        if (($payload['apply_program'] ?? null) === 'senior_high') {
            $payload['apply_course_id'] = null;
        }

        if (($payload['apply_program'] ?? null) === 'college') {
            $payload['apply_strand'] = null;
        }

        $record = $applicant->applicationPreference;
        if (!$record) {
            $record = new ApplicantApplicationPreference();
            $record->applicant_id = $applicant->id;
        }

        $record->fill($payload);
        $record->save();
    }

    /**
     * Application Form – personal + residence information.
     */
    public function applicationForm()
    {
        $applicant = $this->getApplicant()->load([
            'educationalBackground',
            'familyBackground',
            'applicationPreference',
        ]);

        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'program_type', 'track_category']);

        $collegeCourses = $courses->filter(function ($course) {
            return strtolower((string) $course->program_type) === 'college';
        })->values();

        $strandOptions = $courses->pluck('track_category')
            ->filter(function ($value) {
                return !empty($value);
            })
            ->unique()
            ->values();

        return view('applicant.application-form', compact('applicant', 'courses', 'collegeCourses', 'strandOptions'));
    }

    public function saveApplicationFormStep1(SaveApplicantStep1Request $request)
    {
        $applicant = $this->getApplicant();
        $validated = $request->validated();

        $this->applyStep1Payload($applicant, $validated, $request);
        $this->markDraftProgress($applicant, 1);
        $applicant->save();

        $this->syncUserFromApplicant($request->user(), $applicant);

        return response()->json([
            'success' => true,
            'message' => 'Step 1 saved successfully.',
        ]);
    }

    public function saveApplicationFormStep2(SaveApplicantStep2Request $request)
    {
        $applicant = $this->getApplicant()->load('educationalBackground');

        $guardResponse = $this->enforceStepPrerequisite($request, $applicant, 2);
        if ($guardResponse) {
            return $guardResponse;
        }

        $validated = $request->validated();
        $validated['no_k12'] = $this->requestBoolean($request, 'no_k12');

        if ($validated['no_k12'] && empty($validated['senior_school'])) {
            $validated['senior_school'] = $validated['junior_school'];
        }

        $this->upsertEducationalBackground($applicant, $validated);
        $applicant->lrn = $validated['learner_reference_number'];
        $this->markDraftProgress($applicant, 2);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 2 saved successfully.',
        ]);
    }

    public function saveApplicationFormStep3(SaveApplicantStep3Request $request)
    {
        $applicant = $this->getApplicant()->load('familyBackground');

        $guardResponse = $this->enforceStepPrerequisite($request, $applicant, 3);
        if ($guardResponse) {
            return $guardResponse;
        }

        $validated = $request->validated();

        $this->upsertFamilyBackground($applicant, $validated);
        $this->markDraftProgress($applicant, 3);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 3 saved successfully.',
        ]);
    }

    public function saveApplicationFormStep4(SaveApplicantStep4Request $request)
    {
        $applicant = $this->getApplicant()->load('applicationPreference');

        $guardResponse = $this->enforceStepPrerequisite($request, $applicant, 4);
        if ($guardResponse) {
            return $guardResponse;
        }

        $validated = $request->validated();

        $this->upsertApplicationPreference($applicant, $validated);
        $this->markDraftProgress($applicant, 4);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 4 draft saved successfully.',
        ]);
    }

    /**
     * Save Application Form (POST).
     */
    public function saveApplicationForm(SubmitApplicantApplicationRequest $request)
    {
        $applicant = $this->getApplicant()->load([
            'educationalBackground',
            'familyBackground',
            'applicationPreference',
        ]);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $applicant, $validated) {
            $step1Fields = [
                'last_name',
                'first_name',
                'middle_name',
                'suffix',
                'nickname',
                'gender',
                'nationality',
                'religion',
                'date_of_birth',
                'place_of_birth',
                'age',
                'civil_status',
                'mobile_number',
                'email_address',
                'present_street',
                'present_barangay',
                'present_zipcode',
                'present_municipality',
                'present_province',
                'present_region',
                'same_as_present',
                'permanent_street',
                'permanent_barangay',
                'permanent_zipcode',
                'permanent_municipality',
                'permanent_province',
                'permanent_region',
            ];

            $step1Payload = [];
            foreach ($step1Fields as $field) {
                if (array_key_exists($field, $validated)) {
                    $step1Payload[$field] = $validated[$field];
                }
            }

            $this->applyStep1Payload($applicant, $step1Payload, $request);
            $applicant->lrn = $validated['learner_reference_number'];
            $applicant->application_status = 'submitted';
            $applicant->application_draft_step = 4;
            $applicant->application_submitted_at = Carbon::now();
            $applicant->application_portal_stage = 0;
            $applicant->save();

            $step2Payload = [
                'junior_school' => $validated['junior_school'],
                'senior_school' => $validated['senior_school'] ?? null,
                'shs_track_strand' => $validated['shs_track_strand'],
                'no_k12' => $this->requestBoolean($request, 'no_k12'),
                'learner_reference_number' => $validated['learner_reference_number'],
            ];

            if ($step2Payload['no_k12'] && empty($step2Payload['senior_school'])) {
                $step2Payload['senior_school'] = $step2Payload['junior_school'];
            }

            $this->upsertEducationalBackground($applicant, $step2Payload);

            $step3Fields = [
                'mother_last_name',
                'mother_first_name',
                'mother_middle_name',
                'mother_nationality',
                'mother_religion',
                'mother_date_of_birth',
                'mother_mobile_number',
                'mother_occupation',
                'mother_company_address',
                'mother_estimated_monthly_income',
                'mother_residence_address',
                'mother_email_address',
                'father_last_name',
                'father_first_name',
                'father_middle_name',
                'father_nationality',
                'father_religion',
                'father_date_of_birth',
                'father_mobile_number',
                'father_occupation',
                'father_company_address',
                'father_estimated_monthly_income',
                'father_residence_address',
                'father_email_address',
            ];

            $step3Payload = [];
            foreach ($step3Fields as $field) {
                if (array_key_exists($field, $validated)) {
                    $step3Payload[$field] = $validated[$field];
                }
            }

            $this->upsertFamilyBackground($applicant, $step3Payload);

            $step4Payload = [
                'apply_program' => $validated['apply_program'],
                'apply_strand' => $validated['apply_strand'] ?? null,
                'apply_course_id' => $validated['apply_course_id'] ?? null,
                'entry_classification' => $validated['entry_classification'],
                'year_level' => $validated['year_level'],
                'semester' => $validated['semester'],
                'school_year' => $validated['school_year'],
                'application_date' => $validated['application_date'],
                'campus' => $validated['campus'],
            ];

            $this->upsertApplicationPreference($applicant, $step4Payload);

            $this->syncUserFromApplicant($request->user(), $applicant);
        });

        return redirect()->route('applicant.application-form')
            ->with('success', 'Application form submitted successfully.');
    }

    public function continueApplicationForm()
    {
        $applicant = $this->getApplicant();

        if ($applicant->application_status !== 'submitted') {
            return redirect()->route('applicant.application-form');
        }

        if ((int) $applicant->application_portal_stage < 1) {
            $applicant->application_portal_stage = 1;
            $applicant->save();
        }

        return redirect()->route('applicant.application-form');
    }

    public function resetApplicationFormProgress()
    {
        if (!app()->environment('local') && !config('app.debug')) {
            abort(403, 'Reset is only available in local/debug mode.');
        }

        $applicant = $this->getApplicant();

        DB::transaction(function () use ($applicant) {
            ApplicantEducationalBackground::query()
                ->where('applicant_id', $applicant->id)
                ->delete();

            ApplicantFamilyBackground::query()
                ->where('applicant_id', $applicant->id)
                ->delete();

            ApplicantApplicationPreference::query()
                ->where('applicant_id', $applicant->id)
                ->delete();

            $applicant->application_status = 'draft';
            $applicant->application_draft_step = 1;
            $applicant->application_submitted_at = null;
            $applicant->application_portal_stage = 0;
            $applicant->exam_date = null;
            $applicant->exam_room = null;
            $applicant->exam_result_status = 'Pending';
            $applicant->exam_score = null;
            $applicant->save();
        });

        return redirect()->route('applicant.application-form')
            ->with('success', 'Temporary reset complete. You can start from Step 1.');
    }

    /**
     * Schedule of Exam – shows exam permit + reminders.
     */
    public function calendar()
    {
        $applicant = $this->getApplicant();
        $calendarEvents = [];

        if (Schema::hasTable('academic_calendar_events')) {
            $calendarEvents = AcademicCalendarEvent::query()
                ->where('is_active', true)
                ->orderBy('event_date')
                ->get()
                ->map(function ($event) {
                    return [
                        'date' => optional($event->event_date)->format('Y-m-d'),
                        'type' => strtolower((string) $event->event_type) === 'holiday' ? 'holiday' : 'event',
                        'label' => (string) $event->title,
                    ];
                })
                ->filter(function ($event) {
                    return !empty($event['date']) && !empty($event['label']);
                })
                ->values()
                ->all();
        }

        return view('applicant.calendar', compact('applicant', 'calendarEvents'));
    }

    /**
     * Correspondence – shows acceptance/review notice.
     */
    public function correspondence()
    {
        $applicant = $this->getApplicant()->load('applicationPreference.course');
        return view('applicant.correspondence', compact('applicant'));
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
