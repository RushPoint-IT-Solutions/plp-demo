<?php

namespace App\Http\Controllers\Applicant;

use App\Applicant;
use App\ApplicantApplicationPreference;
use App\ApplicantEducationalBackground;
use App\ApplicantFamilyBackground;
use App\ApplicantPhotoUpload;
use App\AcademicCalendarEvent;
use App\Http\Controllers\Concerns\PortalNotifications;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\SystemAnnouncement;
use App\Support\HelpCenterTicketService;

class ApplicantController extends Controller
{
    use PortalNotifications;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            $this->syncPortalNotificationsForUser($user);

            view()->share('applicantNotifications', $this->portalNotificationPayloads($user));
            view()->share('applicantUnreadNotificationCount', $this->portalUnreadNotificationCount($user));

            return $next($request);
        });
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

    protected function portalNotificationModule(): string
    {
        return 'applicant';
    }

    protected function portalNotificationAudience(): string
    {
        return SystemAnnouncement::AUDIENCE_APPLICANT;
    }

    protected function portalNotificationRoutePrefix(): string
    {
        return 'applicant';
    }

    protected function portalNotificationFallbackTitle(): string
    {
        return 'New Applicant Notification';
    }

    protected function portalNotificationFallbackMessage(): string
    {
        return 'A new applicant announcement is available';
    }

    public function notificationsFeed(Request $request)
    {
        return $this->portalNotificationsFeed($request);
    }

    public function markNotificationsRead(Request $request)
    {
        return $this->markPortalNotificationsRead($request);
    }

    public function messaging()
    {
        $applicant = $this->getApplicant();

        if ($applicant->application_status !== 'submitted') {
            return redirect()->route('applicant.application-form')
                ->with('error', 'Messaging is only available after submitting your application.');
        }

        return view('applicant.messaging', compact('applicant'));
    }

    public function dismissNotification(Request $request, $notificationDelivery)
    {
        return $this->dismissPortalNotification($request, $notificationDelivery);
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
            $user = User::query()
                ->where('applicant_id', $applicant->id)
                ->first();
        }

        if (!$user) {
            $username = $this->buildApplicantUsername($applicant);
            $defaultPasswordSeed = trim((string) $applicant->last_name);
            if ($defaultPasswordSeed === '') {
                $defaultPasswordSeed = $username;
            }

            $user = new User();
            $user->username = $username;
            $user->password = Hash::make(strtoupper($defaultPasswordSeed));
            $user->force_password_reset = true;
        }

        $fullName = trim(implode(' ', array_filter([
            $applicant->first_name,
            $applicant->middle_name,
            $applicant->last_name,
        ])));
        if ($fullName === '') {
            $fullName = (string) $user->username;
        }

        $user->name = $fullName;
        $user->module = 'applicant';
        $user->applicant_id = $applicant->id;

        if (!empty($applicant->email_address)) {
            $emailTaken = User::query()
                ->where('email', $applicant->email_address)
                ->where('id', '<>', $user->id ?: 0)
                ->exists();

            if (!$emailTaken) {
                $user->email = $applicant->email_address;
            }
        }

        $user->save();
        $this->syncApplicantUserAccountProfile($user);
    }

    private function buildApplicantUsername(Applicant $applicant)
    {
        $baseUsername = trim((string) $applicant->applicant_id);
        if ($baseUsername === '') {
            $baseUsername = 'APP' . str_pad((string) $applicant->id, 6, '0', STR_PAD_LEFT);
        }

        $candidate = $baseUsername;
        $counter = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = $baseUsername . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function syncApplicantUserAccountProfile(User $user)
    {
        if (!Schema::hasTable('user_account_profiles')
            || !Schema::hasTable('user_account_types')
            || !Schema::hasTable('user_account_states')) {
            return;
        }

        $now = now();

        DB::table('user_account_types')->updateOrInsert(
            ['code' => 'applicant'],
            [
                'name' => 'Applicant',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'active'],
            [
                'name' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'inactive'],
            [
                'name' => 'Inactive',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $typeId = DB::table('user_account_types')->where('code', 'applicant')->value('id');
        $activeStateId = DB::table('user_account_states')->where('code', 'active')->value('id');

        if (!$typeId || !$activeStateId) {
            return;
        }

        $existingIsSample = DB::table('user_account_profiles')
            ->where('user_id', $user->id)
            ->value('is_sample');

        DB::table('user_account_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'user_account_type_id' => (int) $typeId,
                'user_account_state_id' => (int) $activeStateId,
                'is_sample' => $this->toBooleanInt($existingIsSample),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function toBooleanInt($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
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

            $photo = $request->file('photo');
            $storedPath = $photo->store('applicants/photos', 'public');

            $validated['photo'] = $storedPath;
            $this->syncApplicantPhotoUploadRecord($applicant, $photo, $storedPath, $request->user());
        }

        $applicant->fill($validated);
    }

    private function syncApplicantPhotoUploadRecord(Applicant $applicant, $photo, $storedPath, User $actor = null)
    {
        if (!Schema::hasTable('applicant_photo_uploads')) {
            return;
        }

        ApplicantPhotoUpload::query()->updateOrCreate(
            [
                'applicant_id' => (int) $applicant->id,
            ],
            [
                'uploaded_by_user_id' => optional($actor)->id,
                'original_filename' => (string) $photo->getClientOriginalName(),
                'storage_disk' => 'public',
                'storage_path' => (string) $storedPath,
                'mime_type' => (string) ($photo->getClientMimeType() ?: ''),
                'size_bytes' => (int) $photo->getSize(),
            ]
        );
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
        $payload['apply_program'] = 'college';
        $payload['apply_strand'] = null;

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
        $validated['apply_program'] = 'college';
        $validated['apply_strand'] = null;

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
                'apply_program' => 'college',
                'apply_strand' => null,
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
            $today = now()->toDateString();

            $calendarEvents = AcademicCalendarEvent::query()
                ->where('is_active', true)
                ->where(function ($query) use ($today) {
                    $query->whereNull('post_until')
                        ->orWhereDate('post_until', '>=', $today);
                })
                ->visibleToAudience('applicant')
                ->orderBy('event_date')
                ->orderBy('time_from')
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
     * Medical Clearance – shows medical records.
     */
    public function medicalClearance()
    {
        $applicant = $this->getApplicant();
        return view('applicant.medical-clearance', compact('applicant'));
    }

    /**
     * Documents Submitted – shows application requirements.
     */
    public function documentsSubmitted()
    {
        $applicant = $this->getApplicant();
        return view('applicant.documents-submitted', compact('applicant'));
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

    public function helpCenter()
    {
        $topics = $this->helpCenterTopics();
        $user = Auth::user();

        $popularQuestions = [
            [
                'question' => 'How do I complete my application faster?',
                'answer' => 'Prepare your personal details and required documents first, then complete each section in one session to avoid delays.',
            ],
            [
                'question' => 'Where can I monitor my application updates?',
                'answer' => 'Check your Application Status and Correspondence pages regularly for registrar updates and notices.',
            ],
            [
                'question' => 'What file format should I upload?',
                'answer' => 'Use clear JPG, PNG, or PDF files and make sure each file is within the allowed size.',
            ],
        ];

        return view('applicant.help-center.index', [
            'topics' => $topics,
            'popularQuestions' => $popularQuestions,
            'helpTickets' => HelpCenterTicketService::recentTicketsForUser('Applicant', $user),
            'helpTicketStoreRoute' => route('applicant.help.tickets.store'),
            'helpTicketCreateRoute' => route('applicant.help.tickets.create'),
            'helpTicketRequesterType' => 'Applicant',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function createHelpCenterTicket()
    {
        $user = Auth::user();

        return view('shared.help-center-ticket-form-page', [
            'backRoute' => route('applicant.help.center'),
            'helpTicketStoreRoute' => route('applicant.help.tickets.store'),
            'helpTicketRequesterType' => 'Applicant',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function storeHelpCenterTicket(Request $request)
    {
        $ticket = HelpCenterTicketService::createFromRequest($request, 'Applicant', Auth::user());

        return redirect()
            ->route('applicant.help.center')
            ->with('help_ticket_success', 'Ticket ' . $ticket->ticket_no . ' submitted successfully.');
    }

    public function helpCenterTopic($topic)
    {
        $topics = $this->helpCenterTopics();
        if (!isset($topics[$topic])) {
            abort(404);
        }

        return view('applicant.help-center.topic', [
            'topic' => $topics[$topic],
        ]);
    }

    public function helpCenterLiveChat()
    {
        return view('applicant.help-center.live-chat');
    }

    private function helpCenterTopics()
    {
        return [
            'account-issues' => [
                'slug' => 'account-issues',
                'title' => 'Account Issues',
                'subtitle' => 'Problems signing in, password reset concerns, and account access issues.',
                'icon' => 'account',
                'steps' => [
                    ['title' => 'Click "Forgot Password"', 'description' => 'Use the reset option on the applicant login page.'],
                    ['title' => 'Enter your registered email', 'description' => 'Use the same email address linked to your applicant account.'],
                    ['title' => 'Open the reset link in your email', 'description' => 'If missing, check spam or junk folder.'],
                    ['title' => 'Set a new secure password', 'description' => 'Use at least 8 characters with letters and numbers.'],
                ],
                'faqs' => [
                    ['q' => 'Why can not I log in?', 'a' => 'Most cases are due to wrong password, unverified email, or temporary lock after repeated attempts.'],
                    ['q' => 'How do I recover my account?', 'a' => 'Use Forgot Password and follow the email verification steps.'],
                    ['q' => 'Can I update my email account?', 'a' => 'Yes. Contact support so your account details can be validated and updated securely.'],
                ],
            ],
            'application-process' => [
                'slug' => 'application-process',
                'title' => 'Application Process',
                'subtitle' => 'Step-by-step guide for completing and submitting your application.',
                'icon' => 'application',
                'steps' => [
                    ['title' => 'Complete all required form fields', 'description' => 'Review each section and make sure required items are filled.'],
                    ['title' => 'Upload complete requirements', 'description' => 'Submit clear copies of required documents before final submission.'],
                    ['title' => 'Submit your application', 'description' => 'Finalize your form once all details are reviewed.'],
                    ['title' => 'Track status updates', 'description' => 'Use your portal pages to monitor examiner and registrar updates.'],
                ],
                'faqs' => [
                    ['q' => 'Can I edit my application after submitting?', 'a' => 'Some sections may be locked after submission. Contact registrar for correction requests.'],
                    ['q' => 'How will I know if my application passed review?', 'a' => 'Status changes are posted in your applicant portal and may also be sent to your email.'],
                    ['q' => 'Where can I track application progress?', 'a' => 'Open Application Status and Correspondence in your applicant account.'],
                ],
            ],
            'technical-problems' => [
                'slug' => 'technical-problems',
                'title' => 'Technical Problems',
                'subtitle' => 'Quick troubleshooting steps for loading and system issues.',
                'icon' => 'technical',
                'steps' => [
                    ['title' => 'Refresh the page', 'description' => 'A reload often resolves temporary display or submission issues.'],
                    ['title' => 'Use Chrome or Edge browser', 'description' => 'These browsers are fully supported by the portal.'],
                    ['title' => 'Clear browser cache', 'description' => 'Cached files can cause outdated data or UI glitches.'],
                    ['title' => 'Check internet stability', 'description' => 'An unstable connection can interrupt uploads and saves.'],
                ],
                'faqs' => [
                    ['q' => 'Why is the page not loading?', 'a' => 'This can happen due to weak internet, expired session, or browser cache conflicts.'],
                    ['q' => 'What should I do when I see an error?', 'a' => 'Refresh, try again, then capture a screenshot and contact support if the error persists.'],
                ],
            ],
            'forms-and-uploads' => [
                'slug' => 'forms-and-uploads',
                'title' => 'Forms and Uploads',
                'subtitle' => 'Best practices for uploading files and completing online forms.',
                'icon' => 'forms',
                'steps' => [
                    ['title' => 'Click "Upload File"', 'description' => 'Locate the upload button in the form section.'],
                    ['title' => 'Select your document', 'description' => 'Choose the correct file from your device.'],
                    ['title' => 'Ensure correct format', 'description' => 'Only accepted formats like PDF, JPG, or PNG are allowed.'],
                    ['title' => 'Click "Submit"', 'description' => 'Wait until upload is complete before proceeding.'],
                ],
                'faqs' => [
                    ['q' => 'What documents are required?', 'a' => 'Required documents depend on your program and are listed in your applicant form requirements.'],
                    ['q' => 'Why is my upload failing?', 'a' => 'Common reasons include unsupported format, file too large, or unstable connection.'],
                ],
            ],
            'applicant-module' => [
                'slug' => 'applicant-module',
                'title' => 'Applicant Module',
                'subtitle' => 'Overview of applicant portal pages and how to use them effectively.',
                'icon' => 'module',
                'steps' => [
                    ['title' => 'Click "Continue" after application', 'description' => 'This button appears after completing all required steps.'],
                    ['title' => 'Log in using your credentials', 'description' => 'Use the email and password provided during registration.'],
                    ['title' => 'Redirect to Applicant Module', 'description' => 'You will be automatically directed to the applicant module.'],
                ],
                'faqs' => [
                    ['q' => 'Where can I track my application?', 'a' => 'Use Application Status and Correspondence pages in your account.'],
                    ['q' => 'Can I edit my information?', 'a' => 'You may edit allowed sections before final submission or request registrar assistance.'],
                ],
            ],
            'security-and-privacy' => [
                'slug' => 'security-and-privacy',
                'title' => 'Security and Privacy',
                'subtitle' => 'Data protection and account safety reminders for applicants.',
                'icon' => 'security',
                'steps' => [
                    ['title' => 'Do not share your password', 'description' => 'Keep your login credentials private at all times.'],
                    ['title' => 'Always log out after use', 'description' => 'Especially when using public or shared devices.'],
                    ['title' => 'Use a strong password', 'description' => 'Combine letters, numbers, and symbols for better security.'],
                    ['title' => 'Report suspicious activity', 'description' => 'Contact support immediately if you notice unusual activity.'],
                ],
                'faqs' => [
                    ['q' => 'Is my data safe?', 'a' => 'Your data is secured with controlled access and protected processing standards.'],
                    ['q' => 'Can I delete my account?', 'a' => 'Account-related requests can be coordinated with registrar support and follow policy checks.'],
                ],
            ],
        ];
    }
}
