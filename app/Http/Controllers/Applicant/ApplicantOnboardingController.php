<?php

namespace App\Http\Controllers\Applicant;

use App\Applicant;
use App\ApplicantOnboardingAcknowledgement;
use App\Course;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplicantOnboardingController extends Controller
{
    public function welcome()
    {
        return view('applicant.apply-welcome');
    }

    public function start(Request $request)
    {
        $request->validate([
            'ack_notices' => 'accepted',
            'ack_terms' => 'accepted',
        ], [
            'ack_notices.accepted' => 'Please confirm that you have read and understood the important notices.',
            'ack_terms.accepted' => 'Please agree to the Privacy Policy and Terms of Service to continue.',
        ]);

        $request->session()->put('applicant_onboarding_acknowledgement', [
            'ack_notices' => true,
            'ack_terms' => true,
            'acknowledged_at' => now()->toDateTimeString(),
        ]);

        $applicant = null;
        $user = null;

        try {
            DB::transaction(function () use (&$applicant, &$user) {
                $applicantId = $this->generateApplicantId();

                $applicant = \App\Applicant::create([
                    'applicant_id' => $applicantId,
                    'last_name' => 'APPLICANT',
                    'first_name' => 'NEW',
                    'email_address' => $this->generatePreviewEmailAddress(),
                    'mobile_number' => '09123456789',
                    'nationality' => 'Filipino',
                    'application_status' => 'draft',
                    'application_draft_step' => 1,
                    'application_portal_stage' => 0,
                ]);

                $user = \App\User::create([
                    'name' => 'NEW APPLICANT',
                    'username' => $applicantId,
                    'email' => $applicant->email_address,
                    'password' => \Illuminate\Support\Facades\Hash::make('APPLICANT'),
                    'module' => 'applicant',
                    'force_password_reset' => false,
                    'applicant_id' => $applicant->id,
                ]);

                \App\ApplicantOnboardingAcknowledgement::updateOrCreate(
                    ['applicant_id' => $applicant->id],
                    [
                        'ack_notices' => true,
                        'ack_terms' => true,
                        'acknowledged_at' => now()->toDateTimeString(),
                    ]
                );
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('applicant.apply.welcome')
                ->withErrors([
                    'registration' => 'Unable to start application. Please try again.',
                ]);
        }

        if ($user) {
            Auth::login($user, true);
            $request->session()->regenerate();
        }

        $request->session()->forget('applicant_onboarding_acknowledgement');

        return redirect()
            ->route('applicant.application-form')
            ->with('success', 'Applicant profile created. Please complete the application form.');
    }

    public function basicDetails(Request $request)
    {
        $acknowledgement = (array) $request->session()->get('applicant_onboarding_acknowledgement', []);

        if (empty($acknowledgement['ack_notices']) || empty($acknowledgement['ack_terms'])) {
            return redirect()
                ->route('applicant.apply.welcome')
                ->withErrors([
                    'acknowledgement' => 'Please read and accept the notices and terms before registering.',
                ]);
        }

        return view('applicant.apply-basic-details');
    }

    public function storeBasicDetails(Request $request)
    {
        $acknowledgement = (array) $request->session()->get('applicant_onboarding_acknowledgement', []);

        if (empty($acknowledgement['ack_notices']) || empty($acknowledgement['ack_terms'])) {
            return redirect()
                ->route('applicant.apply.welcome')
                ->withErrors([
                    'acknowledgement' => 'Please read and accept the notices and terms before registering.',
                ]);
        }

        $validated = $request->validate([
            'last_name' => 'nullable|string|max:120|regex:/^(?:[A-Za-z][A-Za-z\s\-\.\x27]*)?$/',
            'first_name' => 'nullable|string|max:120|regex:/^(?:[A-Za-z][A-Za-z\s\-\.\x27]*)?$/',
            'middle_name' => 'nullable|string|max:120|regex:/^(?:[A-Za-z][A-Za-z\s\-\.\x27]*)?$/',
            'has_no_middle_name' => 'nullable|boolean',
            'email_address' => 'nullable|email|max:190',
            'mobile_number' => 'nullable|regex:/^[0-9]{11}$/',
            'application_track' => 'nullable|in:college,senior_high',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'nationality' => 'nullable|in:Filipino,Other',
            'religion' => 'nullable|in:Roman Catholic,Christian,Others,Seventh Day Adventist',
        ], [
            'last_name.regex' => 'Last name may only contain letters, spaces, apostrophes, periods, and hyphens.',
            'first_name.regex' => 'First name may only contain letters, spaces, apostrophes, periods, and hyphens.',
            'middle_name.regex' => 'Middle name may only contain letters, spaces, apostrophes, periods, and hyphens.',
            'nationality.in' => 'Please select a valid nationality option.',
            'religion.in' => 'Please select a valid religion option.',
        ]);

        $validated['last_name'] = trim((string) ($validated['last_name'] ?? ''));
        $validated['first_name'] = trim((string) ($validated['first_name'] ?? ''));
        $validated['middle_name'] = trim((string) ($validated['middle_name'] ?? ''));
        $validated['email_address'] = trim((string) ($validated['email_address'] ?? ''));
        $validated['mobile_number'] = trim((string) ($validated['mobile_number'] ?? ''));
        $validated['nationality'] = trim((string) ($validated['nationality'] ?? ''));
        $validated['religion'] = trim((string) ($validated['religion'] ?? ''));

        if ($validated['last_name'] === '') {
            $validated['last_name'] = 'TESTER';
        }

        if ($validated['first_name'] === '') {
            $validated['first_name'] = 'APPLICANT';
        }

        if ($validated['email_address'] === '' || strtolower($validated['email_address']) === 'preview@plp.test') {
            $validated['email_address'] = $this->generatePreviewEmailAddress();
        }

        if ($validated['mobile_number'] === '') {
            $validated['mobile_number'] = '09123456789';
        }

        if (empty($validated['application_track'])) {
            $validated['application_track'] = 'college';
        }

        if ($validated['nationality'] === '') {
            $validated['nationality'] = 'Filipino';
        }

        if ($validated['religion'] === '') {
            $validated['religion'] = null;
        }

        if ($this->requestBoolean($request, 'has_no_middle_name')) {
            $validated['middle_name'] = null;
        } elseif ($validated['middle_name'] === '') {
            $validated['middle_name'] = null;
        }

        $applicant = null;
        $user = null;

        try {
            DB::transaction(function () use ($validated, $acknowledgement, &$applicant, &$user) {
                $applicantId = $this->generateApplicantId();

                $applicant = Applicant::create([
                    'applicant_id' => $applicantId,
                    'last_name' => trim($validated['last_name']),
                    'first_name' => trim($validated['first_name']),
                    'middle_name' => !empty($validated['middle_name']) ? trim($validated['middle_name']) : null,
                    'email_address' => trim($validated['email_address']),
                    'mobile_number' => trim($validated['mobile_number']),
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'nationality' => $validated['nationality'],
                    'religion' => $validated['religion'],
                    'application_status' => 'draft',
                    'application_draft_step' => 1,
                    'application_portal_stage' => 0,
                ]);

                $user = User::create([
                    'name' => trim($applicant->first_name . ' ' . ($applicant->middle_name ? $applicant->middle_name . ' ' : '') . $applicant->last_name),
                    'username' => $applicantId,
                    'email' => trim($validated['email_address']),
                    'password' => Hash::make(strtoupper($applicant->last_name)),
                    'module' => 'applicant',
                    'force_password_reset' => false,
                    'applicant_id' => $applicant->id,
                ]);

                ApplicantOnboardingAcknowledgement::updateOrCreate(
                    ['applicant_id' => $applicant->id],
                    [
                        'ack_notices' => !empty($acknowledgement['ack_notices']),
                        'ack_terms' => !empty($acknowledgement['ack_terms']),
                        'acknowledged_at' => !empty($acknowledgement['acknowledged_at'])
                            ? $acknowledgement['acknowledged_at']
                            : now()->toDateTimeString(),
                    ]
                );
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('applicant.apply.basic-details')
                ->withInput()
                ->withErrors([
                    'registration' => 'Unable to create your applicant account right now. Please try again.',
                ]);
        }

        if ($user) {
            Auth::login($user, true);
            $request->session()->regenerate();
        }

        $request->session()->forget('applicant_onboarding_acknowledgement');

        return redirect()
            ->route('applicant.application-form')
            ->with('success', 'Applicant profile created. Continue with the full application form.');
    }

    public function formPreview(Request $request)
    {
        $previewApplicant = $this->buildPreviewApplicant($request);
        $isSubmitted = (bool) $request->session()->get('applicant_preview_submitted', false);
        $portalStage = (int) $request->session()->get('applicant_preview_portal_stage', 0);
        $previewPortalUnlocked = $portalStage >= 1;

        $previewApplicant->application_status = $isSubmitted ? 'submitted' : 'draft';
        $previewApplicant->application_portal_stage = $portalStage;
        $previewApplicant->application_draft_step = $isSubmitted ? 4 : 1;

        $courses = collect();
        $collegeCourses = collect();
        $strandOptions = collect();

        try {
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
        } catch (\Throwable $exception) {
            $courses = collect();
            $collegeCourses = collect();
            $strandOptions = collect();
        }

        return view('applicant.application-form', [
            'applicant' => $previewApplicant,
            'courses' => $courses,
            'collegeCourses' => $collegeCourses,
            'strandOptions' => $strandOptions,
            'forceEditable' => !$isSubmitted,
            'showResetButton' => false,
            'formRouteNames' => [
                'save' => 'applicant.apply.form-preview.save',
                'step1' => 'applicant.apply.form-preview.step-1.save',
                'step2' => 'applicant.apply.form-preview.step-2.save',
                'step3' => 'applicant.apply.form-preview.step-3.save',
                'step4' => 'applicant.apply.form-preview.step-4.save',
                'continue' => 'applicant.apply.form-preview.continue',
                'reset' => 'applicant.apply.form-preview.reset-progress',
            ],
            'formRouteParams' => [],
            'applicationFormEmbedded' => false,
            'previewPortalMode' => true,
            'previewPortalUnlocked' => $previewPortalUnlocked,
        ]);
    }

    public function savePreviewStep1()
    {
        return $this->previewStepSavedResponse(1);
    }

    public function savePreviewStep2()
    {
        return $this->previewStepSavedResponse(2);
    }

    public function savePreviewStep3()
    {
        return $this->previewStepSavedResponse(3);
    }

    public function savePreviewStep4()
    {
        return $this->previewStepSavedResponse(4);
    }

    private function previewStepSavedResponse($step)
    {
        return response()->json([
            'success' => true,
            'message' => 'Preview mode: Step ' . $step . ' saved in UI only.',
        ]);
    }

    public function savePreviewForm()
    {
        session()->put('applicant_preview_submitted', true);
        session()->put('applicant_preview_portal_stage', 0);

        return redirect()->route('applicant.apply.form-preview');
    }

    public function continuePreviewForm()
    {
        session()->put('applicant_preview_portal_stage', 1);

        return redirect()->route('applicant.apply.form-preview');
    }

    public function resetPreviewForm(Request $request)
    {
        $request->session()->forget('applicant_preview_basic_details');
        $request->session()->forget('applicant_preview_submitted');
        $request->session()->forget('applicant_preview_portal_stage');
        $request->session()->forget('applicant_preview_applicant_id');

        return redirect()
            ->route('applicant.apply.form-preview')
            ->with('success', 'Preview form reset.');
    }

    public function previewScheduleOfExam(Request $request)
    {
        $guardResponse = $this->ensurePreviewPortalUnlocked($request);
        if ($guardResponse) {
            return $guardResponse;
        }

        return view('applicant.schedule-of-exam', [
            'applicant' => $this->buildPreviewApplicant($request, [
                'exam_date' => now()->addDays(7)->setTime(9, 0),
                'exam_room' => 'Main Building - Room 203',
            ]),
            'applicationFormEmbedded' => false,
            'previewPortalMode' => true,
            'previewPortalUnlocked' => true,
        ]);
    }

    public function previewCalendar(Request $request)
    {
        $guardResponse = $this->ensurePreviewPortalUnlocked($request);
        if ($guardResponse) {
            return $guardResponse;
        }

        return view('applicant.calendar', [
            'applicant' => $this->buildPreviewApplicant($request),
            'calendarEvents' => [],
            'applicationFormEmbedded' => false,
            'previewPortalMode' => true,
            'previewPortalUnlocked' => true,
        ]);
    }

    public function previewExamResult(Request $request)
    {
        $guardResponse = $this->ensurePreviewPortalUnlocked($request);
        if ($guardResponse) {
            return $guardResponse;
        }

        return view('applicant.exam-result', [
            'applicant' => $this->buildPreviewApplicant($request, [
                'exam_date' => now()->subDays(1)->setTime(9, 0),
                'exam_result_status' => 'Pending',
                'exam_score' => null,
            ]),
            'applicationFormEmbedded' => false,
            'previewPortalMode' => true,
            'previewPortalUnlocked' => true,
        ]);
    }

    public function previewCorrespondence(Request $request)
    {
        $guardResponse = $this->ensurePreviewPortalUnlocked($request);
        if ($guardResponse) {
            return $guardResponse;
        }

        return view('applicant.correspondence', [
            'applicant' => $this->buildPreviewApplicant($request, [
                'exam_result_status' => 'Pending',
            ]),
            'applicationFormEmbedded' => false,
            'previewPortalMode' => true,
            'previewPortalUnlocked' => true,
        ]);
    }

    private function ensurePreviewPortalUnlocked(Request $request)
    {
        $portalStage = (int) $request->session()->get('applicant_preview_portal_stage', 0);

        if ($portalStage < 1) {
            return redirect()->route('applicant.apply.form-preview')->withErrors([
                'preview' => 'Please complete and submit the Application Form first, then click Continue to unlock other pages.',
            ]);
        }

        return null;
    }

    private function buildPreviewApplicant(Request $request, array $overrides = [])
    {
        $preview = (array) $request->session()->get('applicant_preview_basic_details', []);
        $storedApplicantId = (string) $request->session()->get('applicant_preview_applicant_id', '');

        if ($storedApplicantId === '' || !preg_match('/^\d{4}B\d{4}$/', $storedApplicantId)) {
            $storedApplicantId = $this->generatePreviewApplicantId();
            $request->session()->put('applicant_preview_applicant_id', $storedApplicantId);
        }

        $applicant = (object) [
            'id' => 0,
            'applicant_id' => $storedApplicantId,
            'last_name' => $preview['last_name'] ?? 'TESTER',
            'first_name' => $preview['first_name'] ?? 'APPLICANT',
            'middle_name' => $preview['middle_name'] ?? null,
            'email_address' => $preview['email_address'] ?? 'preview@plp.test',
            'mobile_number' => $preview['mobile_number'] ?? '09123456789',
            'application_status' => 'draft',
            'application_draft_step' => 1,
            'application_portal_stage' => 0,
            'educationalBackground' => null,
            'familyBackground' => null,
            'applicationPreference' => null,
            'exam_date' => null,
            'exam_room' => null,
            'exam_result_status' => null,
            'exam_score' => null,
            'photo' => null,
        ];

        foreach ($overrides as $key => $value) {
            $applicant->{$key} = $value;
        }

        return $applicant;
    }

    private function generatePreviewApplicantId()
    {
        $currentYear = (int) now()->format('y');
        $nextYear = (int) now()->copy()->addYear()->format('y');

        return sprintf('%02d%02dB%04d', $currentYear, $nextYear, random_int(1, 9999));
    }

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

    private function generatePreviewEmailAddress()
    {
        do {
            $candidate = 'preview+' . now()->format('YmdHis') . random_int(1000, 9999) . '@plp.test';
        } while (User::where('email', $candidate)->exists());

        return $candidate;
    }

    private function generateApplicantId()
    {
        $currentYear = (int) now()->format('y');
        $nextYear = (int) now()->copy()->addYear()->format('y');

        do {
            $candidate = sprintf('%02d%02dA%04d', $currentYear, $nextYear, random_int(0, 9999));
        } while (
            Applicant::where('applicant_id', $candidate)->exists() ||
            User::where('username', $candidate)->exists()
        );

        return $candidate;
    }
}
