<?php

namespace App\Http\Controllers\Registrar;

use App\ApplicationStatus;
use App\Applicant;
use App\ApplicantApplicationPreference;
use App\ApplicantEducationalBackground;
use App\ApplicantFamilyBackground;
use App\AlumniTrackerSetting;
use App\CancellationWaiver;
use App\Course;
use App\CourseCurriculum;
use App\CourseCurriculumSubject;
use App\CrossEnrollmentRequest;
use App\CurriculumRequisiteType;
use App\CurriculumSubjectRequisite;
use App\Department;
use App\Faculty;
use App\Http\Requests\StoreRoomBuildingRequest;
use App\Http\Requests\StoreRoomHallwayRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\StoreSectionOfferingRequest;
use App\Http\Requests\StoreSectionMergingRequest;
use App\Http\Requests\StoreSlotMonitoringRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Requests\UpdateSlotMonitoringRequest;
use App\Http\Requests\SaveApplicantStep1Request;
use App\Http\Requests\SaveApplicantStep2Request;
use App\Http\Requests\SaveApplicantStep3Request;
use App\Http\Requests\SaveApplicantStep4Request;
use App\Http\Requests\SubmitApplicantApplicationRequest;
use App\RegistrarRequirement;
use App\RegistrarRequirementDefinition;
use App\RegistrarRequirementPolicy;
use App\RegistrarRequirementType;
use App\Room;
use App\RoomBuilding;
use App\RoomHallway;
use App\Semester;
use App\SectionMergingOperation;
use App\SlotMonitoring;
use App\Student;
use App\StudentProfile;
use App\StudentProfileImage;
use App\StudentSubjectGrade;
use App\MasterStudentGradeFile;
use App\Subject;
use App\SystemSchoolSemester;
use App\YearBlock;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class RegistrarController extends Controller
{
    private const ROOM_FILE_DEFAULT_PER_PAGE = 25;
    private const ROOM_FILE_MIN_PER_PAGE = 10;
    private const ROOM_FILE_MAX_PER_PAGE = 100;
    private const ROOM_FILE_DEFAULT_SORT_BY = 'floor_number';
    private const ROOM_FILE_DEFAULT_SORT_DIR = 'asc';
    private const ROOM_FILE_ALLOWED_SORT_COLUMNS = [
        'room_number',
        'floor_number',
        'building',
        'capacity',
        'program',
        'updated_by',
    ];

    private const SLOT_MONITORING_DEFAULT_PER_PAGE = 25;
    private const SLOT_MONITORING_MIN_PER_PAGE = 10;
    private const SLOT_MONITORING_MAX_PER_PAGE = 100;
    private const SECTION_OFFERING_DEFAULT_PER_PAGE = 25;
    private const SECTION_OFFERING_MIN_PER_PAGE = 10;
    private const SECTION_OFFERING_MAX_PER_PAGE = 100;
    private const SLOT_MONITORING_ALLOWED_SEMESTERS = ['First', 'Second', 'Summer'];
    private const SLOT_MONITORING_ALLOWED_COLLEGE_NAMES = [
        'COLLEGE OF NURSING',
        'COLLEGE OF ARTS AND SCIENCES',
        'COLLEGE OF COMPUTER STUDIES',
        'COLLEGE OF ENGINEERING',
        'COLLEGE OF INTERNATIONAL HOSPITALITY MANAGEMENT',
        'COLLEGE OF BUSINESS AND ACCOUNTANCY',
        'COLLEGE OF EDUCATION',
    ];
    private const SLOT_MONITORING_ALLOWED_DEPARTMENT_NAMES = [
        'COLLEGE OF NURSING',
        'COLLEGE OF ARTS AND SCIENCES',
        'COLLEGE OF COMPUTER STUDIES',
        'COLLEGE OF ENGINEERING',
        'COLLEGE OF INTERNATIONAL HOSPITALITY MANAGEMENT',
        'COLLEGE OF BUSINESS AND ACCOUNTANCY',
        'COLLEGE OF BUSINESS ADMINISTRATION',
        'COLLEGE OF EDUCATION',
    ];

    /**
     * Registrar Dashboard
     */
    public function dashboard()
    {
        $studentCount = Student::count();
        $applicantCount = Applicant::count();
        $facultyCount = Faculty::count();
        $departmentCount = Department::count();
        $daySeed = (int) Carbon::now()->format('z') + 1;
        $useDemoDashboard = ($studentCount <= 20);

        $maleCount = 0;
        $femaleCount = 0;
        if (Schema::hasColumn('students', 'sex')) {
            $maleCount = Student::whereRaw('LOWER(sex) = ?', ['male'])->count();
            $femaleCount = Student::whereRaw('LOWER(sex) = ?', ['female'])->count();
        }

        if ($useDemoDashboard || ($studentCount === 0 && $applicantCount === 0 && $facultyCount === 0)) {
            $studentCount = 230 + ($daySeed % 22);
            $applicantCount = 82 + ($daySeed % 17);
            $facultyCount = 18 + ($daySeed % 6);
            $departmentCount = max($departmentCount, 4);

            $maleRatio = 0.53 + (($daySeed % 6) * 0.01);
            $maleCount = (int) round($studentCount * $maleRatio);
            $femaleCount = max($studentCount - $maleCount, 0);
        }

        if ($maleCount + $femaleCount === 0 && $studentCount > 0) {
            $maleCount = (int) round($studentCount * 0.56);
            $femaleCount = max($studentCount - $maleCount, 0);
        }

        $trendValues = $this->buildMonthlyCounts('students', 6);
        $nonZeroTrendPoints = count(array_filter($trendValues, function ($value) {
            return $value > 0;
        }));
        if ($useDemoDashboard || array_sum($trendValues) <= 0 || $nonZeroTrendPoints <= 2) {
            $trendValues = $this->buildDemoUptrendSeries(6, 42 + ($daySeed % 6), 3, 7, $daySeed + 5);
        }
        $trendPercent = $this->computeLastMonthPercent($trendValues);
        $sparklinePaths = $this->buildSparklinePaths($trendValues, 110, 60);

        return view('registrar.dashboard', [
            'dashboardData' => [
                'studentCount' => $studentCount,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'applicantCount' => $applicantCount,
                'facultyCount' => $facultyCount,
                'departmentCount' => $departmentCount,
                'trendPercent' => $trendPercent,
                'sparklinePath' => $sparklinePaths['line'],
                'sparklineAreaPath' => $sparklinePaths['area'],
            ],
        ]);
    }

    /**
     * Registrar Messaging
     */
    public function messaging()
    {
        return view('registrar.messaging');
    }

    public function helpCenter()
    {
        $topics = $this->helpCenterTopics();

        $popularQuestions = [
            [
                'question' => 'What documents are required for application?',
                'answer' => 'You usually need a valid school ID, recent grades/records, and government-issued identification. Check your application step page for the exact list.',
            ],
            [
                'question' => 'How can I track my application status?',
                'answer' => 'Go to Application Process in your account and open your submitted application. Status updates are posted there in real-time by the registrar.',
            ],
            [
                'question' => 'How do I apply using the system?',
                'answer' => 'Open Application Process, complete all required fields, review your entries, then submit. You can use the Help Center topic pages for step-by-step guidance.',
            ],
        ];

        return view('registrar.help-center.index', [
            'topics' => $topics,
            'popularQuestions' => $popularQuestions,
        ]);
    }

    public function helpCenterTopic($topic)
    {
        $topics = $this->helpCenterTopics();
        if (!isset($topics[$topic])) {
            abort(404);
        }

        return view('registrar.help-center.topic', [
            'topic' => $topics[$topic],
        ]);
    }

    public function helpCenterLiveChat()
    {
        return view('registrar.help-center.live-chat');
    }

    private function helpCenterTopics()
    {
        return [
            'account-issues' => [
                'slug' => 'account-issues',
                'title' => 'Account Issues',
                'subtitle' => 'Common problems: Forgot password, Cannot log in, Did not receive a verification email, and Account is locked.',
                'icon' => 'account',
                'steps' => [
                    ['title' => 'Click "Forgot Password"', 'description' => 'This option is available on the login page below the password field.'],
                    ['title' => 'Enter your registered email', 'description' => 'Use the same email address used in your registration.'],
                    ['title' => 'Check your email for reset link', 'description' => 'If not found, check your spam or junk folder.'],
                    ['title' => 'Create a new password', 'description' => 'Use at least 8 characters with a mix of letters and numbers.'],
                ],
                'faqs' => [
                    ['q' => 'Why can not I log in?', 'a' => 'This usually happens because of a wrong password, inactive account, or pending verification. Try resetting your password first.'],
                    ['q' => 'How do I reset my password?', 'a' => 'Use the Forgot Password option on login, then follow the instructions sent to your email.'],
                    ['q' => 'Can I change my email?', 'a' => 'Yes, submit an account update request to registrar support for validation.'],
                ],
            ],
            'application-process' => [
                'slug' => 'application-process',
                'title' => 'Application Process',
                'subtitle' => 'Here is the step-by-step guide on how to apply using the system.',
                'icon' => 'application',
                'steps' => [
                    ['title' => 'Complete the Application Form', 'description' => 'Fill out all required fields in the application form.'],
                    ['title' => 'Submit your Application', 'description' => 'Review your information and click "Submit".'],
                    ['title' => 'Log in to your Applicant Account', 'description' => 'Use your registered email and password to track your application status.'],
                ],
                'faqs' => [
                    ['q' => 'Can I edit my application after submitting it?', 'a' => 'Minor updates may be requested from registrar, but major fields are usually locked after submission.'],
                    ['q' => 'How will I know if my application is successful?', 'a' => 'You will receive updates in your portal status and registered email notifications.'],
                    ['q' => 'Where can I track my application status?', 'a' => 'Open Application Process from your dashboard and view your current status timeline.'],
                ],
            ],
            'technical-problems' => [
                'slug' => 'technical-problems',
                'title' => 'Technical Problems',
                'subtitle' => 'Try these troubleshooting steps before contacting support.',
                'icon' => 'technical',
                'steps' => [
                    ['title' => 'Refresh the page', 'description' => 'This helps reload the system and fix minor issues.'],
                    ['title' => 'Clear browser cache', 'description' => 'Old cache files may cause loading problems.'],
                    ['title' => 'Use Google Chrome or Edge', 'description' => 'These browsers are fully supported by the system.'],
                    ['title' => 'Check your internet connection', 'description' => 'Ensure you have a stable and strong connection.'],
                ],
                'faqs' => [
                    ['q' => 'Why is the system slow?', 'a' => 'Slow speed can be caused by network instability, browser cache, or peak-hour traffic.'],
                    ['q' => 'What should I do if I get an error?', 'a' => 'Refresh first, then try again. If it persists, capture a screenshot and contact support.'],
                ],
            ],
            'forms-and-uploads' => [
                'slug' => 'forms-and-uploads',
                'title' => 'Forms and Uploads',
                'subtitle' => 'Guidelines for uploading files and submitting forms correctly.',
                'icon' => 'forms',
                'steps' => [
                    ['title' => 'Prepare clear document scans', 'description' => 'Use readable images or PDF files only.'],
                    ['title' => 'Check file size limits', 'description' => 'Large files may fail upload due to system limits.'],
                    ['title' => 'Upload one required file at a time', 'description' => 'Wait for success confirmation before uploading next file.'],
                ],
                'faqs' => [
                    ['q' => 'What file types are accepted?', 'a' => 'Commonly accepted formats are JPG, PNG, and PDF.'],
                    ['q' => 'Why does upload fail?', 'a' => 'Upload may fail due to unsupported type, large size, or unstable internet connection.'],
                ],
            ],
            'applicant-module' => [
                'slug' => 'applicant-module',
                'title' => 'Applicant Module',
                'subtitle' => 'Quick guide for using applicant portal features.',
                'icon' => 'module',
                'steps' => [
                    ['title' => 'Log in with your registered account', 'description' => 'Use the same credentials from your application registration.'],
                    ['title' => 'Complete required sections', 'description' => 'Finish profile and requirement steps to avoid delays.'],
                    ['title' => 'Monitor status updates', 'description' => 'Check your dashboard regularly for new notices.'],
                ],
                'faqs' => [
                    ['q' => 'Can I use one account for multiple applications?', 'a' => 'Use one account per applicant profile to avoid data conflicts.'],
                    ['q' => 'Where do I see my application number?', 'a' => 'Your application number appears in your profile summary and status page.'],
                ],
            ],
            'security-and-privacy' => [
                'slug' => 'security-and-privacy',
                'title' => 'Security and Privacy',
                'subtitle' => 'Best practices to keep your account and personal data safe.',
                'icon' => 'security',
                'steps' => [
                    ['title' => 'Use a strong password', 'description' => 'Do not reuse passwords from other websites.'],
                    ['title' => 'Never share your login credentials', 'description' => 'Registrar staff will never ask for your password.'],
                    ['title' => 'Log out on shared devices', 'description' => 'Always end your session after using public computers.'],
                ],
                'faqs' => [
                    ['q' => 'How is my data protected?', 'a' => 'Your account data is protected with access controls and secure processing policies.'],
                    ['q' => 'What if I suspect unauthorized access?', 'a' => 'Reset your password immediately and report the incident to support.'],
                ],
            ],
        ];
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

    private function stepBlockedResponse(Request $request, Applicant $applicant, $step, $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()
            ->route('registrar.process.application.form.edit', ['applicant' => $applicant->id])
            ->withErrors(['step' => $message])
            ->withInput(['active_step' => $step]);
    }

    private function enforceStepPrerequisite(Request $request, Applicant $applicant, $targetStep)
    {
        if ((int) $targetStep === 2 && !$this->hasCompletedStep1($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                $applicant,
                1,
                'Complete and save Step 1 before proceeding to Step 2.'
            );
        }

        if ((int) $targetStep === 3 && !$this->hasCompletedStep2($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                $applicant,
                2,
                'Complete and save Step 2 before proceeding to Step 3.'
            );
        }

        if ((int) $targetStep === 4 && !$this->hasCompletedStep3($applicant)) {
            return $this->stepBlockedResponse(
                $request,
                $applicant,
                3,
                'Complete and save Step 3 before proceeding to Step 4.'
            );
        }

        return null;
    }

    private function syncLinkedUserFromApplicant(Applicant $applicant)
    {
        $user = User::query()->where('applicant_id', $applicant->id)->first();
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

    public function applicantFormEditor(Applicant $applicant)
    {
        $applicant->load([
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

        $forceEditable = true;
        $showResetButton = false;
        $formRouteNames = [
            'save' => 'registrar.process.application.form.save',
            'step1' => 'registrar.process.application.form.step-1.save',
            'step2' => 'registrar.process.application.form.step-2.save',
            'step3' => 'registrar.process.application.form.step-3.save',
            'step4' => 'registrar.process.application.form.step-4.save',
            'continue' => null,
            'reset' => null,
        ];

        return view('applicant.application-form', [
            'applicant' => $applicant,
            'courses' => $courses,
            'collegeCourses' => $collegeCourses,
            'strandOptions' => $strandOptions,
            'forceEditable' => $forceEditable,
            'showResetButton' => $showResetButton,
            'formRouteNames' => $formRouteNames,
            'formRouteParams' => ['applicant' => $applicant->id],
            'applicationFormEmbedded' => true,
        ]);
    }

    public function saveApplicantFormStep1FromRegistrar(SaveApplicantStep1Request $request, Applicant $applicant)
    {
        $validated = $request->validated();

        $this->applyStep1Payload($applicant, $validated, $request);
        $applicant->application_draft_step = max((int) $applicant->application_draft_step, 1);
        $applicant->save();

        $this->syncLinkedUserFromApplicant($applicant);

        return response()->json([
            'success' => true,
            'message' => 'Step 1 saved successfully.',
        ]);
    }

    public function saveApplicantFormStep2FromRegistrar(SaveApplicantStep2Request $request, Applicant $applicant)
    {
        $applicant->load('educationalBackground');

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
        $applicant->application_draft_step = max((int) $applicant->application_draft_step, 2);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 2 saved successfully.',
        ]);
    }

    public function saveApplicantFormStep3FromRegistrar(SaveApplicantStep3Request $request, Applicant $applicant)
    {
        $applicant->load('familyBackground');

        $guardResponse = $this->enforceStepPrerequisite($request, $applicant, 3);
        if ($guardResponse) {
            return $guardResponse;
        }

        $validated = $request->validated();

        $this->upsertFamilyBackground($applicant, $validated);
        $applicant->application_draft_step = max((int) $applicant->application_draft_step, 3);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 3 saved successfully.',
        ]);
    }

    public function saveApplicantFormStep4FromRegistrar(SaveApplicantStep4Request $request, Applicant $applicant)
    {
        $applicant->load('applicationPreference');

        $guardResponse = $this->enforceStepPrerequisite($request, $applicant, 4);
        if ($guardResponse) {
            return $guardResponse;
        }

        $validated = $request->validated();
        $validated['apply_program'] = 'college';
        $validated['apply_strand'] = null;

        $this->upsertApplicationPreference($applicant, $validated);
        $applicant->application_draft_step = max((int) $applicant->application_draft_step, 4);
        $applicant->save();

        return response()->json([
            'success' => true,
            'message' => 'Step 4 draft saved successfully.',
        ]);
    }

    public function saveApplicantFormFromRegistrar(SubmitApplicantApplicationRequest $request, Applicant $applicant)
    {
        $applicant->load([
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
            $applicant->application_draft_step = max((int) $applicant->application_draft_step, 4);
            if (empty($applicant->application_status)) {
                $applicant->application_status = 'draft';
            }
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
            $this->syncLinkedUserFromApplicant($applicant);
        });

        return redirect()
            ->route('registrar.process.application.form.edit', ['applicant' => $applicant->id])
            ->with('success', 'Applicant form details were updated successfully.');
    }

    /**
     * Process > Application Process
     */
    public function applicationProcess(Request $request)
    {
        $filters = $this->normalizeApplicationProcessFilters($request);

        $applicants = $this->buildApplicationProcessQuery($filters)
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('registrar.process.application-process', compact('applicants', 'courses', 'filters'));
    }

    public function applicationProcessPrint(Request $request)
    {
        $filters = $this->normalizeApplicationProcessFilters($request);

        $applicants = $this->buildApplicationProcessQuery($filters)->get();

        return view('registrar.process.application-process-print', compact('applicants', 'filters'));
    }

    private function normalizeApplicationProcessFilters(Request $request): array
    {
        $allowedPerPage = [10, 25, 50, 100];
        $allowedSortBy = ['applicant_id', 'applicant_name', 'date_applied', 'date_updated'];
        $allowedSortDirection = ['asc', 'desc'];

        $fromDate = trim((string) $request->query('from_date', ''));
        $toDate = trim((string) $request->query('to_date', ''));

        $normalizedFromDate = null;
        if ($fromDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            try {
                $normalizedFromDate = Carbon::parse($fromDate)->format('Y-m-d');
            } catch (\Throwable $exception) {
                $normalizedFromDate = null;
            }
        }

        $normalizedToDate = null;
        if ($toDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            try {
                $normalizedToDate = Carbon::parse($toDate)->format('Y-m-d');
            } catch (\Throwable $exception) {
                $normalizedToDate = null;
            }
        }

        if (!empty($normalizedFromDate) && !empty($normalizedToDate) && $normalizedFromDate > $normalizedToDate) {
            $tempDate = $normalizedFromDate;
            $normalizedFromDate = $normalizedToDate;
            $normalizedToDate = $tempDate;
        }

        $courseId = (int) $request->query('course_id', 0);
        if ($courseId < 1) {
            $courseId = 0;
        }

        $search = trim((string) $request->query('search', ''));

        $sortBy = strtolower(trim((string) $request->query('sort_by', 'date_updated')));
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'date_updated';
        }

        $sortDirection = strtolower(trim((string) $request->query('sort_direction', 'desc')));
        if (!in_array($sortDirection, $allowedSortDirection, true)) {
            $sortDirection = 'desc';
        }

        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        return [
            'from_date' => $normalizedFromDate,
            'to_date' => $normalizedToDate,
            'course_id' => $courseId,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
        ];
    }

    private function buildApplicationProcessQuery(array $filters)
    {
        $query = Applicant::query()
            ->with('applicationPreference.course')
            ->leftJoin('applicant_application_preferences as preferences', 'preferences.applicant_id', '=', 'applicants.id')
            ->select('applicants.*');

        if (!empty($filters['from_date'])) {
            $query->whereDate('applicants.created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('applicants.created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('preferences.apply_course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['search'])) {
            $escapedSearch = addcslashes($filters['search'], '\\%_');
            $likeValue = '%' . $escapedSearch . '%';

            $query->where(function ($searchQuery) use ($likeValue) {
                $searchQuery->where('applicants.applicant_id', 'like', $likeValue)
                    ->orWhere('applicants.first_name', 'like', $likeValue)
                    ->orWhere('applicants.middle_name', 'like', $likeValue)
                    ->orWhere('applicants.last_name', 'like', $likeValue)
                    ->orWhereRaw(
                        "CONCAT(COALESCE(applicants.first_name, ''), ' ', COALESCE(applicants.last_name, '')) LIKE ?",
                        [$likeValue]
                    )
                    ->orWhereRaw(
                        "CONCAT(COALESCE(applicants.last_name, ''), ', ', COALESCE(applicants.first_name, '')) LIKE ?",
                        [$likeValue]
                    );
            });
        }

        $sortDirection = $filters['sort_direction'] === 'asc' ? 'asc' : 'desc';
        if ($filters['sort_by'] === 'applicant_name') {
            $query->orderBy('applicants.last_name', $sortDirection)
                ->orderBy('applicants.first_name', $sortDirection)
                ->orderBy('applicants.middle_name', $sortDirection);
        } elseif ($filters['sort_by'] === 'applicant_id') {
            $query->orderBy('applicants.applicant_id', $sortDirection);
        } elseif ($filters['sort_by'] === 'date_applied') {
            $query->orderBy('applicants.created_at', $sortDirection);
        } else {
            $query->orderBy('applicants.updated_at', $sortDirection);
        }

        $query->orderBy('applicants.id', 'desc');

        return $query;
    }

    public function updateApplicantExamSchedule(Request $request, Applicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'exam_date' => 'required|date',
            'exam_time' => 'required|date_format:H:i',
            'exam_room' => 'required|string|max:190',
        ]);

        $examDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['exam_date'] . ' ' . $validated['exam_time']
        );

        $applicant->exam_date = $examDateTime;
        $applicant->exam_room = $validated['exam_room'];
        if (empty($applicant->exam_result_status)) {
            $applicant->exam_result_status = 'Pending';
        }
        $applicant->save();

        return response()->json([
            'ok' => true,
            'message' => 'Exam schedule saved successfully.',
            'row' => [
                'id' => $applicant->id,
                'applicant_id' => $applicant->applicant_id,
                'exam_date' => optional($applicant->exam_date)->format('Y-m-d'),
                'exam_time' => optional($applicant->exam_date)->format('H:i'),
                'exam_room' => (string) ($applicant->exam_room ?? ''),
                'exam_result_status' => (string) ($applicant->exam_result_status ?? 'Pending'),
            ],
        ]);
    }

    public function updateApplicantExamResult(Request $request, Applicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'exam_result_status' => 'required|in:Pending,Passed,Failed',
            'exam_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $applicant->exam_result_status = $validated['exam_result_status'];
        $applicant->exam_score = array_key_exists('exam_score', $validated)
            ? $validated['exam_score']
            : null;
        $applicant->save();

        return response()->json([
            'ok' => true,
            'message' => 'Exam result saved successfully.',
            'row' => [
                'id' => $applicant->id,
                'applicant_id' => $applicant->applicant_id,
                'exam_result_status' => (string) ($applicant->exam_result_status ?? 'Pending'),
                'exam_score' => $applicant->exam_score,
            ],
        ]);
    }

    private function mapApprovalStatusLabelToDbValue($statusLabel): string
    {
        $normalized = strtolower(trim((string) $statusLabel));

        if ($normalized === '') {
            return 'in_process';
        }

        if ($normalized === 'document submitted' || $normalized === 'submitted') {
            return 'submitted';
        }

        if ($normalized === 'on probation' || $normalized === 'on_probation') {
            return 'on_probation';
        }

        if ($normalized === 'in process' || $normalized === 'in_process') {
            return 'in_process';
        }

        if ($normalized === 'rejected') {
            return 'rejected';
        }

        if ($normalized === 'incomplete' || $normalized === 'draft') {
            return 'draft';
        }

        if ($normalized === 'accepted') {
            return 'accepted';
        }

        return str_replace(' ', '_', $normalized);
    }

    private function mapApprovalStatusDbValueToLabel($statusValue): string
    {
        $normalized = strtolower(trim((string) $statusValue));

        if ($normalized === '') {
            return 'In Process';
        }

        if ($normalized === 'submitted' || $normalized === 'document submitted') {
            return 'Document Submitted';
        }

        if ($normalized === 'on_probation' || $normalized === 'on probation') {
            return 'On Probation';
        }

        if ($normalized === 'in_process' || $normalized === 'in process') {
            return 'In Process';
        }

        if ($normalized === 'rejected') {
            return 'Rejected';
        }

        if ($normalized === 'draft' || $normalized === 'incomplete') {
            return 'Incomplete';
        }

        if ($normalized === 'accepted') {
            return 'Accepted';
        }

        return ucwords(str_replace('_', ' ', $normalized));
    }

    public function updateApplicantApprovalStatus(Request $request, Applicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'application_status' => 'required|string|max:120',
        ]);

        $applicant->application_status = $this->mapApprovalStatusLabelToDbValue($validated['application_status']);
        $applicant->save();

        return response()->json([
            'ok' => true,
            'message' => 'Application status updated successfully.',
            'row' => [
                'id' => $applicant->id,
                'applicant_id' => $applicant->applicant_id,
                'application_status' => $this->mapApprovalStatusDbValueToLabel($applicant->application_status),
            ],
        ]);
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

    public function approvalStatusData(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = ApplicationStatus::query()->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('status_code', 'like', '%' . $search . '%')
                    ->orWhere('status_name', 'like', '%' . $search . '%')
                    ->orWhere('status_message', 'like', '%' . $search . '%');
            });
        }

        $rows = $query->orderBy('status_code')
            ->orderBy('id')
            ->get()
            ->map(function (ApplicationStatus $status) {
                return $this->approvalStatusRowPayload($status);
            })
            ->values();

        return response()->json([
            'rows' => $rows,
        ]);
    }

    public function storeApprovalStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status_code' => 'required|string|max:10|regex:/^[A-Za-z0-9_-]+$/|unique:application_statuses,status_code',
            'status' => 'required|string|max:120|unique:application_statuses,status_name',
            'message' => 'nullable|string|max:2000',
        ]);

        $status = ApplicationStatus::create([
            'status_code' => strtoupper(trim($validated['status_code'])),
            'status_name' => trim($validated['status']),
            'status_message' => trim((string) ($validated['message'] ?? '')),
            'is_active' => true,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Approval status created successfully.',
            'row' => $this->approvalStatusRowPayload($status),
        ], 201);
    }

    public function updateApprovalStatus(Request $request, ApplicationStatus $applicationStatus): JsonResponse
    {
        $validated = $request->validate([
            'status_code' => 'required|string|max:10|regex:/^[A-Za-z0-9_-]+$/|unique:application_statuses,status_code,' . $applicationStatus->id,
            'status' => 'required|string|max:120|unique:application_statuses,status_name,' . $applicationStatus->id,
            'message' => 'nullable|string|max:2000',
        ]);

        $applicationStatus->status_code = strtoupper(trim($validated['status_code']));
        $applicationStatus->status_name = trim($validated['status']);
        $applicationStatus->status_message = trim((string) ($validated['message'] ?? ''));
        $applicationStatus->is_active = true;
        $applicationStatus->save();

        return response()->json([
            'ok' => true,
            'message' => 'Approval status updated successfully.',
            'row' => $this->approvalStatusRowPayload($applicationStatus),
        ]);
    }

    public function destroyApprovalStatus(ApplicationStatus $applicationStatus): JsonResponse
    {
        $applicationStatus->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Approval status deleted successfully.',
        ]);
    }

    private function approvalStatusRowPayload(ApplicationStatus $status): array
    {
        return [
            'id' => $status->id,
            'code' => (string) $status->status_code,
            'status' => (string) $status->status_name,
            'message' => (string) ($status->status_message ?: ''),
        ];
    }

    /**
     * Process > Exam Category
     */
    public function examCategory()
    {
        return view('registrar.process.exam-category');
    }

    /**
     * Process > Exam List
     */
    public function examList()
    {
        return view('registrar.process.exam-list');
    }

    /**
     * Process > Batch Upload Image
     */
    public function batchUpload()
    {
        $recentUploads = collect();

        if (Schema::hasTable('student_profile_images')) {
            $recentUploads = StudentProfileImage::query()
                ->with('studentProfile')
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get()
                ->map(function ($image) {
                    $profile = $image->studentProfile;
                    $studentNo = $profile ? (string) $profile->student_no : 'N/A';
                    $studentName = $profile ? $this->formatStudentProfileName($profile) : 'Unknown Student';
                    $imageUrl = '';

                    if (!empty($image->storage_path)) {
                        $imageUrl = route('registrar.process.batch-upload.image', [
                            'studentProfileImage' => $image->id,
                        ]);
                    }

                    return [
                        'id' => (int) $image->id,
                        'student_no' => $studentNo,
                        'student_name' => $studentName,
                        'original_filename' => (string) $image->original_filename,
                        'storage_path' => (string) $image->storage_path,
                        'image_url' => $imageUrl,
                        'size_kb' => round(((int) $image->size_bytes) / 1024, 2),
                        'uploaded_at' => optional($image->updated_at)->format('M d, Y h:i A') ?: '-',
                    ];
                })
                ->values();
        }

        return view('registrar.process.batch-upload', [
            'recentUploads' => $recentUploads,
        ]);
    }

    public function batchUploadImage(StudentProfileImage $studentProfileImage)
    {
        $disk = !empty($studentProfileImage->storage_disk) ? (string) $studentProfileImage->storage_disk : 'public';
        $path = (string) $studentProfileImage->storage_path;

        if (empty($path) || !Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mimeType = !empty($studentProfileImage->mime_type)
            ? (string) $studentProfileImage->mime_type
            : 'image/jpeg';

        return response(
            Storage::disk($disk)->get($path),
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]
        );
    }

    public function storeBatchUpload(Request $request)
    {
        $validated = $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpg,jpeg|max:1024',
        ]);

        if (!Schema::hasTable('student_profile_images')) {
            return redirect()
                ->route('registrar.process.batch-upload')
                ->withErrors(['images' => 'Upload table is not available. Please run migrations first.']);
        }

        $uploadedRows = [];
        $skippedRows = [];
        $files = $validated['images'];

        foreach ($files as $file) {
            $originalFilename = (string) $file->getClientOriginalName();
            $studentNo = trim((string) pathinfo($originalFilename, PATHINFO_FILENAME));

            if ($studentNo === '') {
                $skippedRows[] = [
                    'filename' => $originalFilename,
                    'reason' => 'Filename is empty. Use student number as filename.',
                ];
                continue;
            }

            $student = Student::where('student_no', $studentNo)->first();
            if (!$student) {
                $skippedRows[] = [
                    'filename' => $originalFilename,
                    'reason' => 'No matching student ID found for ' . $studentNo . '.',
                ];
                continue;
            }

            $profile = StudentProfile::where('student_no', $studentNo)->first();

            if (!$profile) {
                $profile = StudentProfile::create([
                    'student_id' => $student->id,
                    'student_no' => $student->student_no,
                    'first_name' => $student->name,
                    'profile_complete' => false,
                ]);
            }

            if (!$profile) {
                $skippedRows[] = [
                    'filename' => $originalFilename,
                    'reason' => 'Student profile could not be created for ' . $studentNo . '.',
                ];
                continue;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            if ($extension === '') {
                $extension = 'jpg';
            }

            $storedFilename = $studentNo . '.' . $extension;
            $storedPath = $file->storeAs('students/profile-photos', $storedFilename, 'public');

            if (empty($storedPath)) {
                $skippedRows[] = [
                    'filename' => $originalFilename,
                    'reason' => 'File could not be stored. Please try again.',
                ];
                continue;
            }

            $existingImage = StudentProfileImage::where('student_profile_id', $profile->id)->first();
            if ($existingImage && !empty($existingImage->storage_path) && $existingImage->storage_path !== $storedPath) {
                Storage::disk($existingImage->storage_disk ?: 'public')->delete($existingImage->storage_path);
            }

            StudentProfileImage::updateOrCreate(
                ['student_profile_id' => $profile->id],
                [
                    'uploaded_by_user_id' => optional(auth()->user())->id,
                    'original_filename' => $originalFilename,
                    'storage_disk' => 'public',
                    'storage_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => (int) $file->getSize(),
                ]
            );

            // Keep existing profile photo field synchronized for legacy pages.
            $profile->profile_photo_path = $storedPath;
            $profile->save();

            $uploadedRows[] = [
                'filename' => $originalFilename,
                'student_no' => (string) $profile->student_no,
            ];
        }

        return redirect()
            ->route('registrar.process.batch-upload')
            ->with('batchUploadReport', [
                'total' => count($files),
                'uploaded' => $uploadedRows,
                'skipped' => $skippedRows,
            ]);
    }

    private function formatStudentProfileName(StudentProfile $profile)
    {
        $parts = [
            (string) $profile->first_name,
            (string) $profile->middle_name,
            (string) $profile->last_name,
            (string) $profile->suffix,
        ];

        $parts = array_values(array_filter($parts, function ($value) {
            return trim((string) $value) !== '';
        }));

        if (!empty($parts)) {
            return trim(implode(' ', $parts));
        }

        return !empty($profile->student_no) ? (string) $profile->student_no : 'Unknown Student';
    }

    /**
     * Process > Document List
     */
    public function documentList()
    {
        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $yearLevelOptions = YearBlock::query()
            ->orderBy('id')
            ->pluck('label')
            ->values()
            ->all();

        if (!count($yearLevelOptions)) {
            $yearLevelOptions = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        }

        array_unshift($yearLevelOptions, 'All Year Level');

        $rows = RegistrarRequirement::query()
            ->with('yearBlock:id,label')
            ->where(function ($query) use ($activeSemesterId) {
                $query->whereNull('registrar_requirement_policy_id')
                    ->orWhereHas('policy', function ($policyQuery) use ($activeSemesterId) {
                        $policyQuery->where('system_school_semester_id', $activeSemesterId);
                    });
            })
            ->orderBy('applies_to_all_year_levels', 'desc')
            ->orderBy('year_block_id')
            ->orderBy('requirement_name')
            ->orderBy('id')
            ->get()
            ->map(function (RegistrarRequirement $requirement) {
                return $this->documentRequirementRowPayload($requirement);
            })
            ->values()
            ->all();

        return view('registrar.process.document-list', [
            'requirements' => $rows,
            'yearLevelOptions' => $yearLevelOptions,
        ]);
    }

    public function storeDocumentRequirement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grade_level' => 'required|string|max:50',
            'document' => 'required|string|max:180',
            'doc_type' => 'required|in:Medical,Document',
            'non_filipino' => 'nullable',
        ]);

        $resolvedYearLevel = $this->resolveDocumentRequirementYearLevel($validated['grade_level']);
        $documentName = trim((string) $validated['document']);
        $documentType = trim((string) $validated['doc_type']);
        $nonFilipino = $this->requestBoolean($request, 'non_filipino');

        $duplicate = $this->findDuplicateDocumentRequirement(
            $resolvedYearLevel,
            $documentName,
            $documentType,
            $nonFilipino
        );

        if ($duplicate) {
            throw ValidationException::withMessages([
                'document' => ['This requirement already exists for the selected year level and type.'],
            ]);
        }

        $requirement = RegistrarRequirement::create([
            'year_block_id' => $resolvedYearLevel['year_block_id'],
            'applies_to_all_year_levels' => $resolvedYearLevel['applies_to_all_year_levels'],
            'requirement_name' => $documentName,
            'requirement_type' => $documentType,
            'non_filipino' => $nonFilipino,
            'created_by_user_id' => optional(auth()->user())->id,
        ]);

        $this->syncRequirementPolicyForLegacyRow($requirement);

        $requirement->load('yearBlock:id,label');

        return response()->json([
            'ok' => true,
            'message' => 'Requirement added successfully.',
            'row' => $this->documentRequirementRowPayload($requirement),
        ], 201);
    }

    public function updateDocumentRequirement(Request $request, RegistrarRequirement $documentRequirement): JsonResponse
    {
        $validated = $request->validate([
            'grade_level' => 'required|string|max:50',
            'document' => 'required|string|max:180',
            'doc_type' => 'required|in:Medical,Document',
            'non_filipino' => 'nullable',
        ]);

        $resolvedYearLevel = $this->resolveDocumentRequirementYearLevel($validated['grade_level']);
        $documentName = trim((string) $validated['document']);
        $documentType = trim((string) $validated['doc_type']);
        $nonFilipino = $this->requestBoolean($request, 'non_filipino');

        $duplicate = $this->findDuplicateDocumentRequirement(
            $resolvedYearLevel,
            $documentName,
            $documentType,
            $nonFilipino,
            $documentRequirement->id
        );

        if ($duplicate) {
            throw ValidationException::withMessages([
                'document' => ['This requirement already exists for the selected year level and type.'],
            ]);
        }

        $documentRequirement->year_block_id = $resolvedYearLevel['year_block_id'];
        $documentRequirement->applies_to_all_year_levels = $resolvedYearLevel['applies_to_all_year_levels'];
        $documentRequirement->requirement_name = $documentName;
        $documentRequirement->requirement_type = $documentType;
        $documentRequirement->non_filipino = $nonFilipino;
        $documentRequirement->save();

        $this->syncRequirementPolicyForLegacyRow($documentRequirement);

        $documentRequirement->load('yearBlock:id,label');

        return response()->json([
            'ok' => true,
            'message' => 'Requirement updated successfully.',
            'row' => $this->documentRequirementRowPayload($documentRequirement),
        ]);
    }

    public function destroyDocumentRequirement(RegistrarRequirement $documentRequirement): JsonResponse
    {
        $policyId = $documentRequirement->registrar_requirement_policy_id;
        $definitionId = null;

        if (!empty($policyId)) {
            $definitionId = RegistrarRequirementPolicy::query()
                ->where('id', $policyId)
                ->value('registrar_requirement_definition_id');
        }

        $documentRequirement->delete();

        $this->cleanupOrphanedRequirementPolicy($policyId, $definitionId);

        return response()->json([
            'ok' => true,
            'message' => 'Requirement deleted successfully.',
        ]);
    }

    private function resolveDocumentRequirementYearLevel($gradeLevel): array
    {
        $normalized = trim((string) $gradeLevel);

        if ($normalized === '' || strcasecmp($normalized, 'All Year Level') === 0) {
            return [
                'year_block_id' => null,
                'applies_to_all_year_levels' => true,
            ];
        }

        $yearBlock = YearBlock::query()
            ->where('label', $normalized)
            ->first();

        if (!$yearBlock) {
            throw ValidationException::withMessages([
                'grade_level' => ['Selected year level is invalid.'],
            ]);
        }

        return [
            'year_block_id' => $yearBlock->id,
            'applies_to_all_year_levels' => false,
        ];
    }

    private function syncRequirementPolicyForLegacyRow(RegistrarRequirement $requirement)
    {
        $typeCode = $this->normalizeRequirementTypeCode($requirement->requirement_type);

        $type = RegistrarRequirementType::query()->firstOrCreate(
            ['code' => $typeCode],
            ['name' => $this->resolveRequirementTypeLabel($typeCode)]
        );

        $definition = RegistrarRequirementDefinition::query()->firstOrCreate(
            [
                'requirement_name' => (string) $requirement->requirement_name,
                'registrar_requirement_type_id' => $type->id,
                'non_filipino_only' => (bool) $requirement->non_filipino,
            ],
            [
                'created_by_user_id' => $requirement->created_by_user_id,
            ]
        );

        $systemSchoolSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $policyQuery = RegistrarRequirementPolicy::query()
            ->where('registrar_requirement_definition_id', $definition->id)
            ->where('system_school_semester_id', $systemSchoolSemesterId);

        if ($requirement->applies_to_all_year_levels || empty($requirement->year_block_id)) {
            $policyQuery->whereNull('year_block_id');
        } else {
            $policyQuery->where('year_block_id', $requirement->year_block_id);
        }

        $policy = $policyQuery->first();

        if (!$policy) {
            $policy = RegistrarRequirementPolicy::query()->create([
                'registrar_requirement_definition_id' => $definition->id,
                'system_school_semester_id' => $systemSchoolSemesterId,
                'year_block_id' => ($requirement->applies_to_all_year_levels || empty($requirement->year_block_id))
                    ? null
                    : $requirement->year_block_id,
                'created_by_user_id' => $requirement->created_by_user_id,
            ]);
        }

        if ((int) $requirement->registrar_requirement_policy_id !== (int) $policy->id) {
            $requirement->registrar_requirement_policy_id = $policy->id;
            $requirement->save();
        }
    }

    private function resolveActiveRequirementSystemSemesterId()
    {
        $latestSemester = SystemSchoolSemester::query()
            ->orderByDesc('id')
            ->first();

        if ($latestSemester) {
            return (int) $latestSemester->id;
        }

        $yearStart = (int) Carbon::now()->format('Y');

        $createdSemester = SystemSchoolSemester::query()->create([
            'school_year' => $yearStart . '-' . ($yearStart + 1),
            'semester' => 'First Semester',
        ]);

        return (int) $createdSemester->id;
    }

    private function normalizeRequirementTypeCode($type)
    {
        $normalized = strtoupper(trim((string) $type));

        if ($normalized === 'MEDICAL') {
            return 'MEDICAL';
        }

        return 'DOCUMENT';
    }

    private function resolveRequirementTypeLabel($typeCode)
    {
        if ($typeCode === 'MEDICAL') {
            return 'Medical';
        }

        return 'Document';
    }

    private function cleanupOrphanedRequirementPolicy($policyId, $definitionId = null)
    {
        if (empty($policyId)) {
            return;
        }

        $hasLegacyRows = RegistrarRequirement::query()
            ->where('registrar_requirement_policy_id', $policyId)
            ->exists();

        if ($hasLegacyRows) {
            return;
        }

        $hasStudentStatusRows = DB::table('student_requirement_statuses')
            ->where('registrar_requirement_policy_id', $policyId)
            ->exists();

        if ($hasStudentStatusRows) {
            return;
        }

        $policy = RegistrarRequirementPolicy::query()->find($policyId);

        if (!$policy) {
            return;
        }

        if (empty($definitionId)) {
            $definitionId = $policy->registrar_requirement_definition_id;
        }

        $policy->delete();

        if (empty($definitionId)) {
            return;
        }

        $hasOtherPolicies = RegistrarRequirementPolicy::query()
            ->where('registrar_requirement_definition_id', $definitionId)
            ->exists();

        if (!$hasOtherPolicies) {
            RegistrarRequirementDefinition::query()
                ->where('id', $definitionId)
                ->delete();
        }
    }

    private function findDuplicateDocumentRequirement(
        array $resolvedYearLevel,
        $documentName,
        $documentType,
        $nonFilipino,
        $ignoreId = null
    ) {
        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $query = RegistrarRequirement::query()
            ->where('requirement_name', $documentName)
            ->where('requirement_type', $documentType)
            ->where('non_filipino', $nonFilipino)
            ->where('applies_to_all_year_levels', $resolvedYearLevel['applies_to_all_year_levels'])
            ->where(function ($scopeQuery) use ($activeSemesterId) {
                $scopeQuery->whereNull('registrar_requirement_policy_id')
                    ->orWhereHas('policy', function ($policyQuery) use ($activeSemesterId) {
                        $policyQuery->where('system_school_semester_id', $activeSemesterId);
                    });
            });

        if (!empty($resolvedYearLevel['year_block_id'])) {
            $query->where('year_block_id', $resolvedYearLevel['year_block_id']);
        } else {
            $query->whereNull('year_block_id');
        }

        if (!empty($ignoreId)) {
            $query->where('id', '<>', $ignoreId);
        }

        return $query->first();
    }

    private function documentRequirementRowPayload(RegistrarRequirement $requirement): array
    {
        $yearLevel = 'All Year Level';

        if (!$requirement->applies_to_all_year_levels && $requirement->yearBlock) {
            $yearLevel = (string) $requirement->yearBlock->label;
        }

        return [
            'id' => (int) $requirement->id,
            'year_level' => $yearLevel,
            'document' => (string) $requirement->requirement_name,
            'type' => (string) $requirement->requirement_type,
            'non_filipino' => (bool) $requirement->non_filipino,
        ];
    }

    /**
     * Process > Reports
     */
    public function reports()
    {
        $totalApplicants = Applicant::count();
        $students4thYear = Student::query()
            ->whereHas('yearBlock', function ($yearBlockQuery) {
                $yearBlockQuery->where('label', 'like', '4%');
            })
            ->count();
        $daySeed = (int) Carbon::now()->format('z') + 1;

        $useDemoReports = ($totalApplicants <= 15 || $students4thYear <= 15);

        if ($useDemoReports) {
            $totalApplicants = 238 + ($daySeed % 24);
            $students4thYear = 172 + ($daySeed % 18);
        }

        $verifiedCount = (int) round($students4thYear * 0.56);
        $incompleteCount = max($students4thYear - $verifiedCount, 0);
        $approvedCount = (int) round($verifiedCount * 0.69);
        $approvalRate = $students4thYear > 0
            ? round(($approvedCount / $students4thYear) * 100, 1)
            : 0;

        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['name', 'course_id', 'year_block_id', 'updated_at']);

        $requirementPool = [
            'Form 137',
            'PSA',
            'OJT Certificate',
            'Library Clearance',
            'Graduation Application',
            'Medical Certificate',
            'Good Moral Certificate',
            'TOR',
            'Barangay Clearance',
        ];

        $rows = $students->values()->map(function ($student, $index) use ($requirementPool) {
            $firstReq = $requirementPool[$index % count($requirementPool)];
            $secondReq = $requirementPool[($index + 1) % count($requirementPool)];
            $thirdReq = $requirementPool[($index + 3) % count($requirementPool)];
            $missing = ($index % 5 === 0)
                ? [$firstReq, $secondReq, $thirdReq]
                : (($index % 2 === 0) ? [$firstReq, $secondReq] : [$firstReq]);

            return [
                'name' => $student->name ?: 'Unknown Student',
                'section' => trim(($student->program ?: 'N/A') . ' ' . ($student->year_level ?: '')),
                'missing_items' => $missing,
                'last_updated' => optional($student->updated_at)->format('M j, Y') ?: Carbon::now()->format('M j, Y'),
            ];
        })->all();

        if ($useDemoReports || count($rows) < 8) {
            $rows = $this->buildDemoReportRows(max(14, count($rows)), $daySeed);

            $totalApplicants = max($totalApplicants, count($rows) + 52);
            $students4thYear = max($students4thYear, count($rows) + 36);
            $verifiedCount = (int) round($students4thYear * 0.57);
            $incompleteCount = max($students4thYear - $verifiedCount, 0);
            $approvedCount = (int) round($verifiedCount * 0.72);
            $approvalRate = $students4thYear > 0
                ? round(($approvedCount / $students4thYear) * 100, 1)
                : 0;
        }

        $sectionOptions = collect($rows)
            ->pluck('section')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $requirementCounts = [];
        foreach ($rows as $row) {
            foreach ((array) ($row['missing_items'] ?? []) as $item) {
                $requirementCounts[$item] = ($requirementCounts[$item] ?? 0) + 1;
            }
        }

        arsort($requirementCounts);
        $topRequirementLabels = array_slice(array_keys($requirementCounts), 0, 8);
        $topRequirementValues = array_map(function ($label) use ($requirementCounts) {
            return $requirementCounts[$label] ?? 0;
        }, $topRequirementLabels);

        if (!count($topRequirementLabels)) {
            $topRequirementLabels = [
                'Form 137',
                'PSA',
                'OJT Certificate',
                'Library Clearance',
                'Graduation Application',
                'Medical Certificate',
                'Good Moral Certificate',
                'TOR',
            ];
            $topRequirementValues = [76, 69, 63, 58, 49, 43, 37, 31];
        }

        $monthlyPointCount = 12;
        $timelineMonthlyCategories = [];
        $timelineMonthlySeries = $this->buildDemoUptrendSeries($monthlyPointCount, 16 + ($daySeed % 5), 1, 4, $daySeed + 14);
        $monthlyStart = Carbon::now()->startOfMonth()->subMonths($monthlyPointCount - 1);
        for ($i = 0; $i < $monthlyPointCount; $i++) {
            $timelineMonthlyCategories[] = $monthlyStart->copy()->addMonths($i)->format('M Y');
        }

        $yearlyPointCount = 6;
        $timelineYearlyCategories = [];
        $timelineYearlySeries = $this->buildDemoUptrendSeries($yearlyPointCount, 120 + ($daySeed % 20), 18, 42, $daySeed + 33);
        $yearlyStart = Carbon::now()->startOfYear()->subYears($yearlyPointCount - 1);
        for ($i = 0; $i < $yearlyPointCount; $i++) {
            $timelineYearlyCategories[] = $yearlyStart->copy()->addYears($i)->format('Y');
        }

        if ($approvedCount >= 20) {
            $monthlyMax = max($timelineMonthlySeries) ?: 1;
            $monthlyScale = ($approvedCount * 1.05) / $monthlyMax;
            $timelineMonthlySeries = array_map(function ($value) use ($monthlyScale) {
                return max(0, (int) round($value * $monthlyScale));
            }, $timelineMonthlySeries);

            $yearlyMax = max($timelineYearlySeries) ?: 1;
            $yearlyScale = ($approvedCount * 4.2) / $yearlyMax;
            $timelineYearlySeries = array_map(function ($value) use ($yearlyScale) {
                return max(0, (int) round($value * $yearlyScale));
            }, $timelineYearlySeries);
        }

        if (max($timelineMonthlySeries) === 0) {
            $timelineMonthlySeries = $this->buildDemoUptrendSeries($monthlyPointCount, 24, 2, 5, $daySeed + 7);
        }
        if (max($timelineYearlySeries) === 0) {
            $timelineYearlySeries = $this->buildDemoUptrendSeries($yearlyPointCount, 140, 24, 45, $daySeed + 11);
        }

        $approvalTimeline = [
            'monthly' => [
                'categories' => $timelineMonthlyCategories,
                'series' => $timelineMonthlySeries,
                'markerCategory' => $timelineMonthlyCategories[count($timelineMonthlyCategories) - 2] ?? end($timelineMonthlyCategories),
            ],
            'yearly' => [
                'categories' => $timelineYearlyCategories,
                'series' => $timelineYearlySeries,
                'markerCategory' => $timelineYearlyCategories[count($timelineYearlyCategories) - 2] ?? end($timelineYearlyCategories),
            ],
        ];

        return view('registrar.process.reports', [
            'reportData' => [
                'totalApplicants' => $totalApplicants,
                'students4thYear' => $students4thYear,
                'verifiedCount' => $verifiedCount,
                'incompleteCount' => $incompleteCount,
                'approvedCount' => $approvedCount,
                'approvalRate' => $approvalRate,
                'sectionOptions' => $sectionOptions,
                'rows' => $rows,
                'missingRequirementLabels' => $topRequirementLabels,
                'missingRequirementValues' => $topRequirementValues,
                'approvalTimeline' => $approvalTimeline,
                'timelineCategories' => $approvalTimeline['monthly']['categories'],
                'timelineSeries' => $approvalTimeline['monthly']['series'],
                'timelineMarkerCategory' => $approvalTimeline['monthly']['markerCategory'],
            ],
        ]);
    }

    /**
     * Process > Reports > UNIFAST
     */
    public function reportsUnifast()
    {
        return view('registrar.process.reports-unifast');
    }

    /**
     * Process > Reports > OSS - NSTP Form
     */
    public function reportsOssNstpForm()
    {
        return view('registrar.process.reports-oss-nstp-form');
    }

    private function buildMonthlyCounts(string $table, int $months): array
    {
        $fallback = $this->buildDemoMovingSeries($months, 45, 11, 5, (int) Carbon::now()->format('z') + 3);
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'created_at')) {
            return $fallback;
        }

        $values = [];
        $cursor = Carbon::now()->startOfMonth()->subMonths($months - 1);
        for ($i = 0; $i < $months; $i++) {
            $start = $cursor->copy()->addMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $values[] = (int) \DB::table($table)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        if (array_sum($values) === 0) {
            return $fallback;
        }

        return $values;
    }

    private function computeLastMonthPercent(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $previous = (float) $values[count($values) - 2];
        $current = (float) $values[count($values) - 1];

        if ($previous <= 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildSparklinePaths(array $values, int $width, int $height): array
    {
        $paddingX = 2;
        $paddingY = 6;
        $chartWidth = max($width - ($paddingX * 2), 1);
        $chartHeight = max($height - ($paddingY * 2), 1);

        $min = min($values);
        $max = max($values);
        $range = max($max - $min, 1);
        $count = max(count($values) - 1, 1);

        $points = [];
        foreach ($values as $index => $value) {
            $x = $paddingX + ($chartWidth * ($index / $count));
            $y = $paddingY + ($chartHeight * (1 - (($value - $min) / $range)));
            $points[] = [round($x, 2), round($y, 2)];
        }

        $line = 'M ' . $points[0][0] . ',' . $points[0][1];
        for ($i = 1; $i < count($points); $i++) {
            $line .= ' L ' . $points[$i][0] . ',' . $points[$i][1];
        }

        $area = $line . ' L ' . ($paddingX + $chartWidth) . ',' . ($paddingY + $chartHeight)
            . ' L ' . $paddingX . ',' . ($paddingY + $chartHeight) . ' Z';

        return [
            'line' => $line,
            'area' => $area,
        ];
    }

    private function buildDemoMovingSeries(int $points, int $base, int $amplitude, int $trend, int $seed): array
    {
        $series = [];
        $phase = ($seed % 11) / 3;
        for ($i = 0; $i < $points; $i++) {
            $wave = sin($phase + ($i * 0.95)) * $amplitude;
            $progress = $i * $trend;
            $noise = (($seed + ($i * 7)) % 5) - 2;
            $series[] = max(5, (int) round($base + $wave + $progress + $noise));
        }
        return $series;
    }

    private function buildDemoUptrendSeries(int $points, int $start, int $stepMin, int $stepMax, int $seed): array
    {
        $series = [];
        $value = max(5, $start + ($seed % 5));
        $safeStepMax = max($stepMax, $stepMin);

        for ($i = 0; $i < $points; $i++) {
            $stepRange = max($safeStepMax - $stepMin + 1, 1);
            $step = $stepMin + (($seed + ($i * 3)) % $stepRange);
            $wave = ($i % 4 === 2) ? -1 : (($i % 4 === 0) ? 1 : 0);
            $value += $step;
            $series[] = max(5, (int) round($value + $wave));
        }

        return $series;
    }

    private function buildDemoReportRows(int $count, int $seed): array
    {
        $names = [
            'Elias Bartolome', 'Juan Dela Cruz', 'Gabriel Villanueva', 'Andrea Jane Austero', 'Maria Clara Santos',
            'Mark Jay Bares', 'Patricia Mendoza', 'Nico Ramirez', 'Lea Domingo', 'Caleb Flores',
            'Trisha Javier', 'Paolo Mendoza', 'Rina Gamboa', 'Dale Aquino', 'Jessa Salazar',
        ];
        $sections = ['BSCS 1-C', 'BSCS 2-A', 'BSCS 3-B', 'BSCS 4-B', 'BSIT 4-A', 'BSIT 3-C'];
        $requirements = [
            'Form 137',
            'PSA',
            'OJT Certificate',
            'Library Clearance',
            'Graduation Application',
            'Medical Certificate',
            'Good Moral Certificate',
            'TOR',
            'Barangay Clearance',
        ];

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i % count($names)] . ($i >= count($names) ? ' ' . chr(65 + ($i % 26)) : '');
            $section = $sections[($i + $seed) % count($sections)];
            $firstReq = $requirements[($i + $seed) % count($requirements)];
            $secondReq = $requirements[($i + $seed + 2) % count($requirements)];

            $missingSet = [
                $firstReq,
                $secondReq,
                $requirements[($i + $seed + 4) % count($requirements)],
            ];

            $rows[] = [
                'name' => $name,
                'section' => $section,
                'missing_items' => ($i % 4 === 0)
                    ? $missingSet
                    : (($i % 2 === 0) ? [$firstReq, $secondReq] : [$firstReq]),
                'last_updated' => Carbon::now()->subDays(($i * 2 + ($seed % 5)) % 25)->format('M j, Y'),
            ];
        }

        return $rows;
    }

    /**
     * Registrar > Academic Master > Program File
     */
    public function programFile(Request $request)
    {
        $departmentId = (int) $request->input('department_id', 0);
        $programType = trim((string) $request->input('program_type', ''));
        $programCode = trim((string) $request->input('program_code', ''));
        $description = trim((string) $request->input('description', ''));
        $perPage = 10;

        $departments = Department::orderBy('description')->get();
        $faculties = Faculty::orderBy('name')->get();

        $programsQuery = Course::with(['department', 'deanDirector'])
            ->when($departmentId > 0, function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($programType !== '', function ($query) use ($programType) {
                if ($programType === 'Pending Review') {
                    $query->where(function ($pendingQuery) {
                        $pendingQuery->whereNull('program_file')
                            ->orWhere('program_file', '')
                            ->orWhere('program_file', 'Pending Review');
                    });
                    return;
                }

                $query->where('program_file', $programType);
            })
            ->when($programCode !== '', function ($query) use ($programCode) {
                $query->where('code', 'like', '%' . $programCode . '%');
            })
            ->when($description !== '', function ($query) use ($description) {
                $query->where(function ($subQuery) use ($description) {
                    $departmentTable = (new Department)->getTable();

                    $subQuery->where('name', 'like', '%' . $description . '%')
                        ->orWhere('description', 'like', '%' . $description . '%')
                        ->orWhere('code', 'like', '%' . $description . '%')
                        ->orWhereHas('department', function ($departmentQuery) use ($description, $departmentTable) {
                            $departmentQuery->where($departmentTable . '.description', 'like', '%' . $description . '%')
                                ->orWhere($departmentTable . '.code', 'like', '%' . $description . '%');
                        });
                });
            })
            ->orderBy('code');

        $programs = $programsQuery
            ->paginate($perPage)
            ->appends([
                'department_id' => $departmentId > 0 ? $departmentId : null,
                'program_type' => $programType,
                'program_code' => $programCode,
                'description' => $description,
                'per_page' => $perPage,
            ]);

        return view('registrar.registrar-menu.academic-master.program-file', [
            'departments' => $departments,
            'faculties' => $faculties,
            'programs' => $programs,
            'filters' => [
                'department_id' => $departmentId > 0 ? (string) $departmentId : '',
                'program_type' => $programType,
                'program_code' => $programCode,
                'description' => $description,
                'per_page' => (string) $perPage,
            ],
        ]);
    }

    /**
     * Registrar > Academic Master > Program File > Save setup modal
     */
    public function saveDepartmentSetup(Request $request)
    {
        $validated = $request->validate([
            'dept_code' => 'required|string|max:30|unique:departments,code',
            'dept_description' => 'required|string|max:255|unique:departments,description',
        ]);

        Department::create([
            'code' => $validated['dept_code'],
            'description' => $validated['dept_description'],
        ]);

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file')
            ->with('program_file_success', 'Department setup saved successfully.');
    }

    /**
     * Registrar > Academic Master > Program File > Save add-program modal
     */
    public function saveProgramSetup(Request $request)
    {
        $validated = $request->validate([
            'program_type' => 'nullable|string|max:80',
            'program_code' => 'required|string|max:30|unique:courses,code',
            'department_id' => 'required|exists:departments,id',
            'program_name' => 'required|string|max:255',
            'accreditation_level' => 'nullable|string|max:120',
            'slots' => 'nullable|integer|min:0',
            'track_category' => 'nullable|in:Academic,TVL,Academic/TVL',
            'non_filipino' => 'nullable|boolean',
            'dean_director_id' => 'nullable|exists:faculties,id',
        ]);

        $accreditationLevel = trim((string) ($validated['accreditation_level'] ?? ''));

        Course::create([
            'code' => $validated['program_code'],
            'name' => $validated['program_name'],
            'program_type' => $validated['program_type'] ?? 'Degree',
            'department_id' => $validated['department_id'],
            'description' => $validated['program_name'],
            'program_file' => $accreditationLevel !== '' ? $accreditationLevel : 'Pending Review',
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
     * Registrar > Academic Master > Program File > Update setup modal
     */
    public function updateProgramSetup(Request $request, Course $course)
    {
        $validated = $request->validate([
            'program_code' => 'required|string|max:30|unique:courses,code,' . $course->id,
            'department_id' => 'required|exists:departments,id',
            'program_name' => 'required|string|max:255',
            'accreditation_level' => 'nullable|string|max:120',
        ]);

        $accreditationLevel = trim((string) ($validated['accreditation_level'] ?? ''));

        $course->update([
            'code' => $validated['program_code'],
            'name' => $validated['program_name'],
            'description' => $validated['program_name'],
            'department_id' => $validated['department_id'],
            'program_file' => $accreditationLevel !== '' ? $accreditationLevel : 'Pending Review',
        ]);

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file')
            ->with('program_file_success', 'Program updated successfully.');
    }

    /**
     * Registrar > Academic Master > Program File > Delete setup row
     */
    public function destroyProgramSetup(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file')
            ->with('program_file_success', 'Program deleted successfully.');
    }

    /**
     * Registrar > Academic Master > Subject File
     */
    public function subjectFile()
    {
        return view('registrar.registrar-menu.academic-master.subject-file');
    }

    public function subjectFileData(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $sort = strtolower((string) $request->query('sort', 'asc'));
        if (!in_array($sort, ['asc', 'desc'], true)) {
            $sort = 'asc';
        }

        $perPage = (int) $request->query('per_page', 25);
        if ($perPage < 10) {
            $perPage = 10;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $page = (int) $request->query('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $query = Subject::query()
            ->select([
                'id',
                'code',
                'name',
                'lec',
                'lab',
                'is_core',
                'is_applied',
                'is_specialized',
            ])
            ->where('is_subject_file_record', true);

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('code', 'like', $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $paginator = $query->orderBy('code', $sort)
            ->orderBy('id', $sort)
            ->paginate($perPage, ['*'], 'page', $page);

        $rows = collect($paginator->items())
            ->map(function (Subject $subject) {
                return $this->subjectFileRowPayload($subject);
            })
            ->values();

        return response()->json([
            'rows' => $rows,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'sort' => $sort,
                'search' => $search,
            ],
        ]);
    }

    public function storeSubjectFile(Request $request): JsonResponse
    {
        $normalizedCode = strtoupper(trim((string) $request->input('code', '')));
        $request->merge([
            'code' => $normalizedCode,
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('subjects', 'code')->where(function ($query) {
                    $query->where('is_subject_file_record', true);
                }),
            ],
            'title' => 'required|string|max:255',
            'lec' => 'nullable|integer|min:0|max:99',
            'lab' => 'nullable|integer|min:0|max:99',
        ]);

        $lec = (int) ($validated['lec'] ?? 0);
        $lab = (int) ($validated['lab'] ?? 0);

        $subject = Subject::create([
            'code' => $validated['code'],
            'name' => trim($validated['title']),
            'units' => (float) ($lec + $lab),
            'lec' => $lec,
            'lab' => $lab,
            'is_subject_file_record' => true,
            'is_core' => $this->requestBoolean($request, 'core'),
            'is_applied' => $this->requestBoolean($request, 'applied'),
            'is_specialized' => $this->requestBoolean($request, 'specialized'),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Subject created successfully.',
            'row' => $this->subjectFileRowPayload($subject),
        ], 201);
    }

    public function updateSubjectFile(Request $request, $subjectId): JsonResponse
    {
        $subject = Subject::query()
            ->where('id', (int) $subjectId)
            ->where('is_subject_file_record', true)
            ->first();

        if (!$subject) {
            return response()->json([
                'ok' => false,
                'message' => 'Subject not found.',
            ], 404);
        }

        $normalizedCode = strtoupper(trim((string) $request->input('code', '')));
        $request->merge([
            'code' => $normalizedCode,
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('subjects', 'code')
                    ->where(function ($query) {
                        $query->where('is_subject_file_record', true);
                    })
                        ->ignore((int) $subject->id),
            ],
            'title' => 'required|string|max:255',
            'lec' => 'nullable|integer|min:0|max:99',
            'lab' => 'nullable|integer|min:0|max:99',
        ]);

        $lec = (int) ($validated['lec'] ?? 0);
        $lab = (int) ($validated['lab'] ?? 0);

        $subject->code = $validated['code'];
        $subject->name = trim($validated['title']);
        $subject->units = (float) ($lec + $lab);
        $subject->lec = $lec;
        $subject->lab = $lab;
        $subject->is_core = $this->requestBoolean($request, 'core');
        $subject->is_applied = $this->requestBoolean($request, 'applied');
        $subject->is_specialized = $this->requestBoolean($request, 'specialized');
        $subject->is_subject_file_record = true;
        $subject->save();

        return response()->json([
            'ok' => true,
            'message' => 'Subject updated successfully.',
            'row' => $this->subjectFileRowPayload($subject),
        ]);
    }

    public function destroySubjectFile($subjectId): JsonResponse
    {
        $subject = Subject::query()
            ->where('id', (int) $subjectId)
            ->where('is_subject_file_record', true)
            ->first();

        if (!$subject) {
            return response()->json([
                'ok' => false,
                'message' => 'Subject not found.',
            ], 404);
        }

        $subject->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Subject deleted successfully.',
        ]);
    }

    private function subjectFileRowPayload(Subject $subject): array
    {
        return [
            'id' => $subject->id,
            'code' => (string) $subject->code,
            'title' => (string) $subject->name,
            'lec' => (float) ($subject->lec ?: 0),
            'lab' => (float) ($subject->lab ?: 0),
            'core' => (bool) $subject->is_core,
            'applied' => (bool) $subject->is_applied,
            'specialized' => (bool) $subject->is_specialized,
        ];
    }

    /**
     * Registrar > Academic Master > Curriculum File
     */
    public function curriculumFile(Request $request)
    {
        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'description']);

        $courseYearMap = $this->buildCourseCurriculumYearMap($courses);
        $selectedCourseId = $this->normalizeSelectedCourseId($request->query('course_id'), $courses, $courseYearMap);
        $selectedCurriculumYear = $this->normalizeSelectedCurriculumYear(
            $selectedCourseId,
            $request->query('curriculum_year'),
            $courseYearMap
        );

        return view('registrar.registrar-menu.academic-master.curriculum-file', [
            'courses' => $courses,
            'courseYearMap' => $courseYearMap,
            'selectedCourseId' => $selectedCourseId,
            'selectedCurriculumYear' => $selectedCurriculumYear,
        ]);
    }

    /**
     * Registrar > Academic Master > Pre-requisites
     */
    public function preRequisites(Request $request)
    {
        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'description']);

        $courseYearMap = $this->buildCourseCurriculumYearMap($courses);
        $selectedCourseId = $this->normalizeSelectedCourseId($request->query('course_id'), $courses, $courseYearMap);
        $selectedCurriculumYear = $this->normalizeSelectedCurriculumYear(
            $selectedCourseId,
            $request->query('curriculum_year'),
            $courseYearMap
        );

        return view('registrar.registrar-menu.academic-master.pre-requisites', [
            'courses' => $courses,
            'courseYearMap' => $courseYearMap,
            'selectedCourseId' => $selectedCourseId,
            'selectedCurriculumYear' => $selectedCurriculumYear,
        ]);
    }

    public function preRequisitesData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'curriculum_year' => 'required|string|max:20',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $courseId = (int) $validated['course_id'];
        $curriculumYear = trim((string) $validated['curriculum_year']);
        $page = isset($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 100;

        $payload = $this->buildPreRequisitesListPayload($courseId, $curriculumYear, $page, $perPage);

        return response()->json(array_merge(['ok' => true], $payload));
    }

    public function preRequisitesSubjectDetail($courseCurriculumSubjectId): JsonResponse
    {
        $payload = $this->buildPreRequisitesSubjectPayload((int) $courseCurriculumSubjectId);
        if (!$payload) {
            return response()->json([
                'ok' => false,
                'message' => 'Curriculum subject not found.',
            ], 404);
        }

        return response()->json(array_merge(['ok' => true], $payload));
    }

    public function downloadPreRequisitesPdf(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'curriculum_year' => 'required|string|max:20',
        ]);

        $courseId = (int) $validated['course_id'];
        $curriculumYear = trim((string) $validated['curriculum_year']);

        $payload = $this->buildPreRequisitesListPayload($courseId, $curriculumYear, 1, 5000);

        $fileSafeCode = $this->sanitizeFilenameSegment($payload['course']['code'] ?? 'course');
        $fileSafeYear = $this->sanitizeFilenameSegment($payload['curriculum_year'] ?? $curriculumYear);
        $filename = 'pre-requisites-' . $fileSafeCode . '-' . $fileSafeYear . '-' . now()->format('Ymd_His') . '.pdf';

        $html = view('registrar.registrar-menu.academic-master.pdf.pre-requisites', [
            'payload' => $payload,
            'generatedAt' => now(),
        ])->render();

        return $this->makePdfDownloadResponse($html, $filename, 'L');
    }

    public function downloadPreRequisitesSubjectPdf($courseCurriculumSubjectId)
    {
        $payload = $this->buildPreRequisitesSubjectPayload((int) $courseCurriculumSubjectId);
        if (!$payload) {
            abort(404, 'Curriculum subject not found.');
        }

        $subjectCode = $this->sanitizeFilenameSegment($payload['subject']['code'] ?? 'subject');
        $curriculumYear = $this->sanitizeFilenameSegment($payload['curriculum_year'] ?? 'curriculum');
        $filename = 'subject-config-' . $subjectCode . '-' . $curriculumYear . '-' . now()->format('Ymd_His') . '.pdf';

        $html = view('registrar.registrar-menu.academic-master.pdf.pre-requisites-subject', [
            'payload' => $payload,
            'generatedAt' => now(),
        ])->render();

        return $this->makePdfDownloadResponse($html, $filename, 'P');
    }

    public function updatePreRequisitesSubjectDetail(Request $request, $courseCurriculumSubjectId): JsonResponse
    {
        $curriculumSubjectId = (int) $courseCurriculumSubjectId;

        $assignment = CourseCurriculumSubject::query()->find($curriculumSubjectId);
        if (!$assignment) {
            return response()->json([
                'ok' => false,
                'message' => 'Curriculum subject not found.',
            ], 404);
        }

        $validated = $request->validate([
            'pre_subject_ids' => 'nullable|array',
            'pre_subject_ids.*' => 'integer|exists:subjects,id',
            'co_subject_ids' => 'nullable|array',
            'co_subject_ids.*' => 'integer|exists:subjects,id',
            'equivalent_subject_ids' => 'nullable|array',
            'equivalent_subject_ids.*' => 'integer|exists:subjects,id',
        ]);

        $allowedSubjectIds = CourseCurriculumSubject::query()
            ->where('course_curriculum_id', (int) $assignment->course_curriculum_id)
            ->where('id', '<>', (int) $assignment->id)
            ->pluck('subject_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $preIds = collect((array) ($validated['pre_subject_ids'] ?? []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $coIds = collect((array) ($validated['co_subject_ids'] ?? []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $equivalentIds = collect((array) ($validated['equivalent_subject_ids'] ?? []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        foreach ([$preIds, $coIds, $equivalentIds] as $ids) {
            foreach ($ids as $subjectId) {
                if (!in_array((int) $subjectId, $allowedSubjectIds, true)) {
                    throw ValidationException::withMessages([
                        'pre_subject_ids' => ['One or more selected subjects are not valid for this curriculum.'],
                    ]);
                }
            }
        }

        $typeIds = [];
        foreach ([
            'pre' => 'Pre-requisite',
            'co' => 'Co-requisite',
            'equivalent' => 'Equivalent Subject',
        ] as $code => $name) {
            $typeIds[$code] = (int) CurriculumRequisiteType::query()
                ->firstOrCreate(['code' => $code], ['name' => $name])
                ->id;
        }

        DB::transaction(function () use ($assignment, $typeIds, $preIds, $coIds, $equivalentIds) {
            CurriculumSubjectRequisite::query()
                ->where('course_curriculum_subject_id', (int) $assignment->id)
                ->delete();

            $rows = [];
            $groupMap = [
                'pre' => $preIds,
                'co' => $coIds,
                'equivalent' => $equivalentIds,
            ];

            foreach ($groupMap as $typeCode => $ids) {
                $sortOrder = 0;

                foreach ($ids as $subjectId) {
                    $rows[] = [
                        'course_curriculum_subject_id' => (int) $assignment->id,
                        'requisite_subject_id' => (int) $subjectId,
                        'curriculum_requisite_type_id' => (int) $typeIds[$typeCode],
                        'sort_order' => $sortOrder,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $sortOrder++;
                }
            }

            if (count($rows)) {
                DB::table('curriculum_subject_requisites')->insert($rows);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Pre-requisite mappings saved successfully.',
        ]);
    }

    private function buildPreRequisitesListPayload($courseId, $curriculumYear, $page = 1, $perPage = 100): array
    {
        $course = Course::query()->find((int) $courseId);
        if (!$course) {
            throw ValidationException::withMessages([
                'course_id' => ['Course not found.'],
            ]);
        }

        $safePage = max((int) $page, 1);
        $safePerPage = min(max((int) $perPage, 1), 5000);

        $hasCurriculumYearLookup = Schema::hasTable('curriculum_years');
        $hasCurriculumYearForeign = Schema::hasColumn('course_curricula', 'curriculum_year_id');
        $curriculumYearId = null;
        if ($hasCurriculumYearLookup) {
            $curriculumYearId = DB::table('curriculum_years')
                ->where('code', (string) $curriculumYear)
                ->value('id');
        }

        $curriculum = CourseCurriculum::query()
            ->where('course_id', (int) $courseId)
            ->where(function ($query) use ($curriculumYear, $curriculumYearId, $hasCurriculumYearForeign) {
                $query->where('curriculum_year_code', (string) $curriculumYear);

                if ($hasCurriculumYearForeign && !empty($curriculumYearId)) {
                    $query->orWhere('curriculum_year_id', (int) $curriculumYearId);
                }
            })
            ->first();

        if (!$curriculum) {
            return [
                'course' => [
                    'id' => (int) $course->id,
                    'code' => (string) $course->code,
                    'name' => (string) ($course->name ?: $course->description),
                ],
                'curriculum_year' => (string) $curriculumYear,
                'program_title' => strtoupper((string) ($course->name ?: $course->description)),
                'years' => [],
                'meta' => [
                    'page' => 1,
                    'per_page' => $safePerPage,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ];
        }

        $assignmentQuery = CourseCurriculumSubject::query()
            ->with(['subject', 'yearBlock', 'semester'])
            ->where('course_curriculum_id', (int) $curriculum->id)
            ->orderBy('display_order')
            ->orderBy('id');

        $totalAssignments = (int) (clone $assignmentQuery)->count();
        $lastPage = max((int) ceil($totalAssignments / $safePerPage), 1);
        if ($safePage > $lastPage) {
            $safePage = $lastPage;
        }

        $assignments = $assignmentQuery
            ->forPage($safePage, $safePerPage)
            ->get();

        $assignmentIds = $assignments->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $requisiteMap = [];
        if (count($assignmentIds) > 0) {
            $requisiteRows = DB::table('curriculum_subject_requisites as csr')
                ->join('curriculum_requisite_types as crt', 'crt.id', '=', 'csr.curriculum_requisite_type_id')
                ->join('subjects as rs', 'rs.id', '=', 'csr.requisite_subject_id')
                ->whereIn('csr.course_curriculum_subject_id', $assignmentIds)
                ->orderBy('csr.sort_order')
                ->get([
                    'csr.course_curriculum_subject_id',
                    'crt.code as requisite_type_code',
                    'rs.code as requisite_subject_code',
                ]);

            foreach ($requisiteRows as $row) {
                $assignmentId = (int) $row->course_curriculum_subject_id;
                $typeCode = strtolower(trim((string) $row->requisite_type_code));

                if (!isset($requisiteMap[$assignmentId])) {
                    $requisiteMap[$assignmentId] = [
                        'pre' => [],
                        'co' => [],
                        'equivalent' => [],
                    ];
                }

                if (!array_key_exists($typeCode, $requisiteMap[$assignmentId])) {
                    continue;
                }

                $requisiteMap[$assignmentId][$typeCode][] = trim((string) $row->requisite_subject_code);
            }
        }

        $assignmentBuckets = [];
        foreach ($assignments as $assignment) {
            $assignmentId = (int) $assignment->id;
            $yearBlockId = (int) $assignment->year_block_id;
            $semesterId = (int) $assignment->semester_id;

            if (!isset($assignmentBuckets[$yearBlockId])) {
                $assignmentBuckets[$yearBlockId] = [];
            }

            if (!isset($assignmentBuckets[$yearBlockId][$semesterId])) {
                $assignmentBuckets[$yearBlockId][$semesterId] = [];
            }

            $types = $requisiteMap[$assignmentId] ?? [
                'pre' => [],
                'co' => [],
                'equivalent' => [],
            ];

            $assignmentBuckets[$yearBlockId][$semesterId][] = [
                'curriculum_subject_id' => $assignmentId,
                'subject_id' => (int) $assignment->subject_id,
                'code' => (string) optional($assignment->subject)->code,
                'description' => (string) optional($assignment->subject)->name,
                'credited_units' => (float) $assignment->credited_units,
                'pre_requisite_text' => count($types['pre']) ? implode(', ', $types['pre']) : 'None',
                'co_requisite_text' => count($types['co']) ? implode(', ', $types['co']) : 'None',
                'equivalent_subject_text' => count($types['equivalent']) ? implode(', ', $types['equivalent']) : 'None',
            ];
        }

        $yearsPayload = [];
        $yearBlocks = $this->orderedYearBlocks();
        $semesters = $this->orderedSemesters();

        foreach ($yearBlocks as $yearBlock) {
            $semesterPayload = [];

            foreach ($semesters as $semester) {
                $semesterPayload[] = [
                    'id' => (int) $semester->id,
                    'label' => (string) $semester->name,
                    'subjects' => $assignmentBuckets[(int) $yearBlock->id][(int) $semester->id] ?? [],
                ];
            }

            $yearsPayload[] = [
                'id' => (int) $yearBlock->id,
                'label' => (string) $yearBlock->label,
                'semesters' => $semesterPayload,
            ];
        }

        $resolvedCurriculumYearCode = trim((string) $curriculum->curriculum_year_code);
        if ($resolvedCurriculumYearCode === '' && $hasCurriculumYearLookup && !empty($curriculum->curriculum_year_id)) {
            $lookupCode = DB::table('curriculum_years')
                ->where('id', (int) $curriculum->curriculum_year_id)
                ->value('code');

            if (!empty($lookupCode)) {
                $resolvedCurriculumYearCode = (string) $lookupCode;
            }
        }

        if ($resolvedCurriculumYearCode === '') {
            $resolvedCurriculumYearCode = (string) $curriculumYear;
        }

        return [
            'course' => [
                'id' => (int) $course->id,
                'code' => (string) $course->code,
                'name' => (string) ($course->name ?: $course->description),
            ],
            'curriculum_year' => $resolvedCurriculumYearCode,
            'program_title' => strtoupper((string) ($course->name ?: $course->description)),
            'years' => $yearsPayload,
            'meta' => [
                'page' => $safePage,
                'per_page' => $safePerPage,
                'total' => $totalAssignments,
                'last_page' => $lastPage,
            ],
        ];
    }

    private function buildPreRequisitesSubjectPayload($curriculumSubjectId)
    {
        $assignment = CourseCurriculumSubject::query()
            ->with(['subject', 'curriculum.course'])
            ->find((int) $curriculumSubjectId);

        if (!$assignment) {
            return null;
        }

        $availableSubjects = CourseCurriculumSubject::query()
            ->with('subject')
            ->where('course_curriculum_id', (int) $assignment->course_curriculum_id)
            ->where('id', '<>', (int) $assignment->id)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->map(function (CourseCurriculumSubject $item) {
                return [
                    'subject_id' => (int) $item->subject_id,
                    'code' => (string) optional($item->subject)->code,
                    'description' => (string) optional($item->subject)->name,
                ];
            })
            ->unique('subject_id')
            ->values();

        $selectedRows = CurriculumSubjectRequisite::query()
            ->with(['requisiteType', 'requisiteSubject'])
            ->where('course_curriculum_subject_id', (int) $assignment->id)
            ->orderBy('sort_order')
            ->get();

        $selected = [
            'pre' => [],
            'co' => [],
            'equivalent' => [],
        ];

        foreach ($selectedRows as $row) {
            $typeCode = strtolower((string) optional($row->requisiteType)->code);
            if (!array_key_exists($typeCode, $selected)) {
                continue;
            }

            $selected[$typeCode][] = [
                'subject_id' => (int) $row->requisite_subject_id,
                'code' => (string) optional($row->requisiteSubject)->code,
                'description' => (string) optional($row->requisiteSubject)->name,
            ];
        }

        $curriculumYearCode = trim((string) optional($assignment->curriculum)->curriculum_year_code);
        if ($curriculumYearCode === ''
            && Schema::hasTable('curriculum_years')
            && !empty(optional($assignment->curriculum)->curriculum_year_id)) {
            $lookupCode = DB::table('curriculum_years')
                ->where('id', (int) optional($assignment->curriculum)->curriculum_year_id)
                ->value('code');

            if (!empty($lookupCode)) {
                $curriculumYearCode = (string) $lookupCode;
            }
        }

        return [
            'subject' => [
                'curriculum_subject_id' => (int) $assignment->id,
                'subject_id' => (int) $assignment->subject_id,
                'code' => (string) optional($assignment->subject)->code,
                'description' => (string) optional($assignment->subject)->name,
                'credited_units' => (float) $assignment->credited_units,
            ],
            'available_subjects' => $availableSubjects,
            'selected' => $selected,
            'course' => [
                'id' => (int) optional(optional($assignment->curriculum)->course)->id,
                'code' => (string) optional(optional($assignment->curriculum)->course)->code,
                'name' => (string) (optional(optional($assignment->curriculum)->course)->name ?: optional(optional($assignment->curriculum)->course)->description),
            ],
            'curriculum_year' => $curriculumYearCode,
        ];
    }

    private function makePdfDownloadResponse($html, $filename, $orientation = 'P')
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $format = strtoupper((string) $orientation) === 'L' ? 'A4-L' : 'A4';

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'tempDir' => $tempDir,
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
        ]);

        $pdf->WriteHTML((string) $html);
        $binary = $pdf->Output((string) $filename, Destination::STRING_RETURN);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function sanitizeFilenameSegment($value)
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);
        $normalized = trim((string) $normalized, '-');

        return $normalized !== '' ? $normalized : 'file';
    }

    private function buildCourseCurriculumYearMap($courses): array
    {
        $courseIds = collect($courses)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        if (!count($courseIds)) {
            return [];
        }

        $rowsQuery = DB::table('course_curricula as cc')
            ->whereIn('cc.course_id', $courseIds)
            ->orderBy('cc.curriculum_year_code');

        if (Schema::hasTable('curriculum_years') && Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            $rowsQuery
                ->leftJoin('curriculum_years as cy', 'cy.id', '=', 'cc.curriculum_year_id')
                ->select([
                    'cc.course_id',
                    DB::raw('COALESCE(cy.code, cc.curriculum_year_code) as curriculum_year_code'),
                ]);
        } else {
            $rowsQuery->select(['cc.course_id', 'cc.curriculum_year_code']);
        }

        $rows = $rowsQuery->get();

        $map = [];
        foreach ($courseIds as $courseId) {
            $map[$courseId] = [];
        }

        foreach ($rows as $row) {
            $courseId = (int) $row->course_id;
            $yearCode = trim((string) $row->curriculum_year_code);

            if ($yearCode === '') {
                continue;
            }

            if (!isset($map[$courseId])) {
                $map[$courseId] = [];
            }

            if (!in_array($yearCode, $map[$courseId], true)) {
                $map[$courseId][] = $yearCode;
            }
        }

        return $map;
    }

    private function normalizeSelectedCourseId($requestedCourseId, $courses, array $courseYearMap)
    {
        $requestedId = (int) $requestedCourseId;
        $availableIds = collect($courses)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        if ($requestedId > 0 && in_array($requestedId, $availableIds, true)) {
            return $requestedId;
        }

        foreach ($availableIds as $courseId) {
            if (count($courseYearMap[$courseId] ?? [])) {
                return $courseId;
            }
        }

        return count($availableIds) ? (int) $availableIds[0] : null;
    }

    private function normalizeSelectedCurriculumYear($selectedCourseId, $requestedYear, array $courseYearMap)
    {
        if (!$selectedCourseId) {
            return '';
        }

        $years = $courseYearMap[(int) $selectedCourseId] ?? [];
        $requested = trim((string) $requestedYear);

        if ($requested !== '' && in_array($requested, $years, true)) {
            return $requested;
        }

        return count($years) ? (string) $years[0] : '';
    }

    private function orderedYearBlocks()
    {
        return YearBlock::query()
            ->whereIn('label', ['1st Year', '2nd Year', '3rd Year', '4th Year'])
            ->orderByRaw("CASE label WHEN '1st Year' THEN 1 WHEN '2nd Year' THEN 2 WHEN '3rd Year' THEN 3 WHEN '4th Year' THEN 4 ELSE 99 END")
            ->orderBy('label')
            ->get(['id', 'label']);
    }

    private function orderedSemesters()
    {
        return Semester::query()
            ->whereIn('name', ['First Semester', 'Second Semester', 'Summer Semester'])
            ->orderByRaw("CASE name WHEN 'First Semester' THEN 1 WHEN 'Second Semester' THEN 2 WHEN 'Summer Semester' THEN 3 ELSE 99 END")
            ->orderBy('name')
            ->get(['id', 'name']);
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
        $this->seedRoomDimensionsIfEmpty();

        return view('registrar.registrar-menu.scheduling.room-file');
    }

    public function roomFileData(Request $request): JsonResponse
    {
        $this->seedRoomDimensionsIfEmpty();

        $search = trim((string) $request->input('search', ''));
        $page = $this->resolveRoomFilePage($request->input('page', 1));
        $perPage = $this->resolveRoomFilePerPage($request->input('per_page', self::ROOM_FILE_DEFAULT_PER_PAGE));
        $sortBy = $this->resolveRoomFileSortBy($request->input('sort_by', self::ROOM_FILE_DEFAULT_SORT_BY));
        $sortDir = $this->resolveRoomFileSortDirection($request->input('sort_dir', self::ROOM_FILE_DEFAULT_SORT_DIR));
        $includeOptions = $this->requestBoolean($request, 'include_options');

        $query = Room::query()
            ->select([
                'rooms.id',
                'rooms.room_hallway_id',
                'rooms.room_number',
                'rooms.floor_number',
                'rooms.capacity',
                'rooms.updated_by_user_id',
                'rooms.updated_at',
            ])
            ->with([
                'hallway.building:id,name',
                'courses',
                'updatedBy:id,name',
            ]);

        if ($search !== '') {
            $this->applyRoomFileSearch($query, $search);
            $this->applyRoomFileSearchPriority($query, $search);
        }

        $this->applyRoomFileSort($query, $sortBy, $sortDir);

        $paginator = $query->paginate($perPage, [
            'rooms.id',
            'rooms.room_hallway_id',
            'rooms.room_number',
            'rooms.floor_number',
            'rooms.capacity',
            'rooms.updated_by_user_id',
            'rooms.updated_at',
        ], 'page', $page);

        $rows = $paginator->getCollection()
            ->map(function (Room $room) {
                return $this->mapRoomFileRow($room);
            })
            ->values();

        $options = null;
        if ($includeOptions) {
            $options = $this->roomFileOptionsPayload();
        }

        return response()->json([
            'ok' => true,
            'rows' => $rows,
            'options' => $options,
            'meta' => [
                'page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'per_page' => (int) $paginator->perPage(),
                'total' => (int) $paginator->total(),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function storeRoomFile(StoreRoomRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $courseIds = $this->normalizeRoomCourseIds($validated['course_ids']);

        $room = null;

        DB::beginTransaction();
        try {
            $room = Room::create([
                'room_hallway_id' => (int) $validated['room_hallway_id'],
                'room_number' => (int) $validated['room_number'],
                'floor_number' => (int) $validated['floor_number'],
                'capacity' => (int) $validated['capacity'],
                'updated_by_user_id' => auth()->id(),
            ]);

            $room->courses()->sync($courseIds);

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicateRoomConstraint($exception)) {
                return response()->json([
                    'message' => 'Duplicate room location entry.',
                    'errors' => [
                        'room_number' => ['The room number already exists for the selected floor and hallway.'],
                    ],
                ], 422);
            }

            throw $exception;
        }

        $room->load(['hallway.building:id,name', 'courses', 'updatedBy:id,name']);

        return response()->json([
            'ok' => true,
            'id' => $room->id,
            'row' => $this->mapRoomFileRow($room),
        ]);
    }

    public function storeRoomBuilding(StoreRoomBuildingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $name = $this->normalizeRoomBuildingName($validated['name']);

        if ($name === '') {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['Building name is required.'],
                ],
            ], 422);
        }

        $existing = RoomBuilding::query()
            ->with([
                'hallways' => function ($hallwayQuery) {
                    $hallwayQuery
                        ->select('id', 'room_building_id', 'name')
                        ->orderBy('name');
                },
            ])
            ->where('name', $name)
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => true,
                'existing' => true,
                'building' => $this->mapRoomBuildingOption($existing),
            ]);
        }

        try {
            $building = RoomBuilding::query()->create([
                'name' => $name,
            ]);

            $building->setRelation('hallways', collect());
        } catch (QueryException $exception) {
            if ($this->isDuplicateRoomBuildingConstraint($exception)) {
                $existing = RoomBuilding::query()
                    ->with([
                        'hallways' => function ($hallwayQuery) {
                            $hallwayQuery
                                ->select('id', 'room_building_id', 'name')
                                ->orderBy('name');
                        },
                    ])
                    ->where('name', $name)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'ok' => true,
                        'existing' => true,
                        'building' => $this->mapRoomBuildingOption($existing),
                    ]);
                }
            }

            throw $exception;
        }

        return response()->json([
            'ok' => true,
            'existing' => false,
            'building' => $this->mapRoomBuildingOption($building),
        ]);
    }

    public function storeRoomHallway(StoreRoomHallwayRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $buildingId = (int) $validated['room_building_id'];
        $name = $this->normalizeRoomHallwayName($validated['name']);

        if ($name === '') {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['Hallway name is required.'],
                ],
            ], 422);
        }

        $existing = RoomHallway::query()
            ->where('room_building_id', $buildingId)
            ->where('name', $name)
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => true,
                'existing' => true,
                'hallway' => [
                    'id' => (int) $existing->id,
                    'name' => (string) $existing->name,
                    'room_building_id' => (int) $existing->room_building_id,
                ],
            ]);
        }

        try {
            $hallway = RoomHallway::query()->create([
                'room_building_id' => $buildingId,
                'name' => $name,
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateRoomHallwayConstraint($exception)) {
                $existing = RoomHallway::query()
                    ->where('room_building_id', $buildingId)
                    ->where('name', $name)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'ok' => true,
                        'existing' => true,
                        'hallway' => [
                            'id' => (int) $existing->id,
                            'name' => (string) $existing->name,
                            'room_building_id' => (int) $existing->room_building_id,
                        ],
                    ]);
                }
            }

            throw $exception;
        }

        return response()->json([
            'ok' => true,
            'existing' => false,
            'hallway' => [
                'id' => (int) $hallway->id,
                'name' => (string) $hallway->name,
                'room_building_id' => (int) $hallway->room_building_id,
            ],
        ]);
    }

    public function updateRoomFile(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $validated = $request->validated();
        $courseIds = $this->normalizeRoomCourseIds($validated['course_ids']);

        DB::beginTransaction();
        try {
            $room->update([
                'room_hallway_id' => (int) $validated['room_hallway_id'],
                'room_number' => (int) $validated['room_number'],
                'floor_number' => (int) $validated['floor_number'],
                'capacity' => (int) $validated['capacity'],
                'updated_by_user_id' => auth()->id(),
            ]);

            $room->courses()->sync($courseIds);

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicateRoomConstraint($exception)) {
                return response()->json([
                    'message' => 'Duplicate room location entry.',
                    'errors' => [
                        'room_number' => ['The room number already exists for the selected floor and hallway.'],
                    ],
                ], 422);
            }

            throw $exception;
        }

        $room->load(['hallway.building:id,name', 'courses', 'updatedBy:id,name']);

        return response()->json([
            'ok' => true,
            'row' => $this->mapRoomFileRow($room),
        ]);
    }

    public function destroyRoomFile(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(['ok' => true]);
    }

    private function applyRoomFileSearch($query, $search)
    {
        $term = '%' . $search . '%';

        $query->where(function ($builder) use ($term) {
            $builder->where('room_number', 'like', $term)
                ->orWhere('floor_number', 'like', $term)
                ->orWhere('capacity', 'like', $term)
                ->orWhereHas('hallway', function ($hallwayQuery) use ($term) {
                    $hallwayQuery->where('name', 'like', $term)
                        ->orWhereHas('building', function ($buildingQuery) use ($term) {
                            $buildingQuery->where('name', 'like', $term);
                        });
                })
                ->orWhereHas('courses', function ($courseQuery) use ($term) {
                    $courseQuery->where('code', 'like', $term)
                        ->orWhere('name', 'like', $term);
                })
                ->orWhereHas('updatedBy', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', $term);
                });
        });
    }

    private function applyRoomFileSearchPriority($query, $search)
    {
        $needle = trim((string) $search);
        if ($needle === '') {
            return;
        }

        $startsWith = $needle . '%';
        $contains = '%' . $needle . '%';

        $query->orderByRaw(
            "CASE WHEN CAST(rooms.room_number AS CHAR) = ? THEN 0 WHEN CAST(rooms.room_number AS CHAR) LIKE ? THEN 1 WHEN CAST(rooms.room_number AS CHAR) LIKE ? THEN 2 ELSE 3 END",
            [$needle, $startsWith, $contains]
        );
    }

    private function applyRoomFileSort($query, $sortBy, $sortDir)
    {
        switch ($sortBy) {
            case 'room_number':
                $query->orderBy('rooms.room_number', $sortDir)
                    ->orderBy('rooms.floor_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;

            case 'building':
                $query->leftJoin('room_hallways as rf_sort_h', 'rf_sort_h.id', '=', 'rooms.room_hallway_id')
                    ->leftJoin('room_buildings as rf_sort_b', 'rf_sort_b.id', '=', 'rf_sort_h.room_building_id')
                    ->orderBy('rf_sort_b.name', $sortDir)
                    ->orderBy('rf_sort_h.name', $sortDir)
                    ->orderBy('rooms.floor_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.room_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;

            case 'capacity':
                $query->orderBy('rooms.capacity', $sortDir)
                    ->orderBy('rooms.floor_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.room_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;

            case 'program':
                $query->orderByRaw("(SELECT MIN(COALESCE(NULLIF(TRIM(c.code), ''), c.name)) FROM room_course_assignments rca INNER JOIN courses c ON c.id = rca.course_id WHERE rca.room_id = rooms.id) {$sortDir}")
                    ->orderBy('rooms.floor_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.room_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;

            case 'updated_by':
                $query->leftJoin('users as rf_sort_u', 'rf_sort_u.id', '=', 'rooms.updated_by_user_id')
                    ->orderByRaw("COALESCE(rf_sort_u.name, '') {$sortDir}")
                    ->orderBy('rooms.floor_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.room_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;

            case 'floor_number':
            default:
                $query->orderBy('rooms.floor_number', $sortDir)
                    ->orderBy('rooms.room_number', self::ROOM_FILE_DEFAULT_SORT_DIR)
                    ->orderBy('rooms.id', self::ROOM_FILE_DEFAULT_SORT_DIR);
                return;
        }
    }

    private function roomFileOptionsPayload()
    {
        $buildings = RoomBuilding::query()
            ->with([
                'hallways' => function ($hallwayQuery) {
                    $hallwayQuery
                        ->select('id', 'room_building_id', 'name')
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (RoomBuilding $building) {
                return $this->mapRoomBuildingOption($building);
            })
            ->values();

        $programs = Course::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $label = trim((string) $course->code);
                if ($label === '') {
                    $label = (string) $course->name;
                }

                return [
                    'id' => (int) $course->id,
                    'code' => (string) $course->code,
                    'name' => (string) $course->name,
                    'label' => $label,
                ];
            })
            ->values();

        return [
            'buildings' => $buildings,
            'programs' => $programs,
        ];
    }

    private function mapRoomFileRow(Room $room)
    {
        $buildingName = '';
        $hallwayName = '';

        if ($room->hallway) {
            $hallwayName = (string) $room->hallway->name;
            if ($room->hallway->building) {
                $buildingName = (string) $room->hallway->building->name;
            }
        }

        $programIds = $room->courses
            ->pluck('id')
            ->map(function ($courseId) {
                return (int) $courseId;
            })
            ->values()
            ->all();

        $programLabels = $room->courses
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                return $code !== '' ? $code : (string) $course->name;
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->all();

        $updatedByName = 'System';
        if ($room->updatedBy && trim((string) $room->updatedBy->name) !== '') {
            $updatedByName = (string) $room->updatedBy->name;
        }

        $locationLabel = trim($buildingName . ' / ' . $hallwayName, ' /');

        return [
            'id' => (int) $room->id,
            'room_number' => (int) $room->room_number,
            'floor_number' => (int) $room->floor_number,
            'capacity' => (int) $room->capacity,
            'room_building_id' => $room->hallway ? (int) $room->hallway->room_building_id : null,
            'room_hallway_id' => (int) $room->room_hallway_id,
            'building' => $buildingName,
            'hallway' => $hallwayName,
            'location_label' => $locationLabel,
            'program_ids' => $programIds,
            'program_labels' => $programLabels,
            'program_label' => count($programLabels) ? implode(', ', $programLabels) : '-',
            'updated_by' => $updatedByName,
            'updated_at' => $room->updated_at ? $room->updated_at->toDateTimeString() : null,
        ];
    }

    private function normalizeRoomCourseIds(array $courseIds)
    {
        $normalized = [];

        foreach ($courseIds as $courseId) {
            $id = (int) $courseId;
            if ($id > 0 && !in_array($id, $normalized, true)) {
                $normalized[] = $id;
            }
        }

        return $normalized;
    }

    private function normalizeRoomHallwayName($name)
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $name));

        return $normalized === null ? '' : $normalized;
    }

    private function normalizeRoomBuildingName($name)
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $name));

        return $normalized === null ? '' : $normalized;
    }

    private function resolveRoomFilePage($page)
    {
        $safePage = (int) $page;

        if ($safePage < 1) {
            return 1;
        }

        return $safePage;
    }

    private function resolveRoomFilePerPage($perPage)
    {
        $safePerPage = (int) $perPage;

        if ($safePerPage < self::ROOM_FILE_MIN_PER_PAGE) {
            return self::ROOM_FILE_MIN_PER_PAGE;
        }

        if ($safePerPage > self::ROOM_FILE_MAX_PER_PAGE) {
            return self::ROOM_FILE_MAX_PER_PAGE;
        }

        return $safePerPage;
    }

    private function resolveRoomFileSortBy($sortBy)
    {
        $candidate = strtolower(trim((string) $sortBy));

        if (in_array($candidate, self::ROOM_FILE_ALLOWED_SORT_COLUMNS, true)) {
            return $candidate;
        }

        return self::ROOM_FILE_DEFAULT_SORT_BY;
    }

    private function resolveRoomFileSortDirection($sortDirection)
    {
        $candidate = strtolower(trim((string) $sortDirection));

        if ($candidate === 'desc') {
            return 'desc';
        }

        return self::ROOM_FILE_DEFAULT_SORT_DIR;
    }

    private function isDuplicateRoomConstraint(QueryException $exception)
    {
        if ((string) $exception->getCode() !== '23000') {
            return false;
        }

        $message = $exception->getMessage();

        return strpos($message, 'rooms_location_unique') !== false
            || strpos($message, 'Duplicate entry') !== false;
    }

    private function isDuplicateRoomHallwayConstraint(QueryException $exception)
    {
        if ((string) $exception->getCode() !== '23000') {
            return false;
        }

        $message = $exception->getMessage();

        return strpos($message, 'room_hallways_building_name_unique') !== false
            || strpos($message, 'Duplicate entry') !== false;
    }

    private function isDuplicateRoomBuildingConstraint(QueryException $exception)
    {
        if ((string) $exception->getCode() !== '23000') {
            return false;
        }

        $message = $exception->getMessage();

        return strpos($message, 'room_buildings_name_unique') !== false
            || strpos($message, 'Duplicate entry') !== false;
    }

    private function mapRoomBuildingOption(RoomBuilding $building)
    {
        $hallways = $building->hallways;
        if (!$hallways) {
            $hallways = collect();
        }

        return [
            'id' => (int) $building->id,
            'name' => (string) $building->name,
            'hallways' => $hallways
                ->map(function (RoomHallway $hallway) {
                    return [
                        'id' => (int) $hallway->id,
                        'name' => (string) $hallway->name,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function seedRoomDimensionsIfEmpty()
    {
        if (!Schema::hasTable('room_buildings') || !Schema::hasTable('room_hallways')) {
            return;
        }

        if (RoomBuilding::query()->exists()) {
            return;
        }

        $defaultBuildings = ['Campus 1', 'Campus 2', 'Campus 3', 'Campus 4'];

        DB::transaction(function () use ($defaultBuildings) {
            foreach ($defaultBuildings as $buildingName) {
                $building = RoomBuilding::query()->create([
                    'name' => $buildingName,
                ]);

                RoomHallway::query()->create([
                    'room_building_id' => $building->id,
                    'name' => 'Main Hallway',
                ]);
            }
        });
    }

    /**
     * Registrar > Scheduling > Section Offering
     */
    public function sectionOffering()
    {
        return view('registrar.registrar-menu.scheduling.section-offering');
    }

    public function sectionOfferingData(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $yearLevel = $this->normalizeSectionOfferingYearLevel((string) $request->query('year_level', ''));
        $sectionQuery = trim((string) $request->query('section', ''));
        $courseId = (int) $request->query('course_id', 0);
        $page = $this->resolveSectionOfferingPage($request->query('page', 1));
        $perPage = $this->resolveSectionOfferingPerPage($request->query('per_page', self::SECTION_OFFERING_DEFAULT_PER_PAGE));

        $rows = $this->buildSectionOfferingSubjectRowsQuery(
            $schoolYear,
            $semester,
            $sectionQuery,
            $search,
            $courseId
        )->get();

        $sectionGroups = $rows->groupBy(function ($row) {
            $courseIdValue = (int) ($row->course_id ?? 0);
            $schoolYearValue = trim((string) ($row->school_year ?? ''));
            $semesterValue = $this->normalizeSlotMonitoringSemester((string) ($row->semester_label ?? ''));
            $sectionValue = trim((string) ($row->section ?? ''));

            return implode('|', [$courseIdValue, $schoolYearValue, $semesterValue, $sectionValue]);
        });

        $sections = $sectionGroups
            ->map(function ($groupRows, $groupKey) {
                $groupRows = collect($groupRows)->values();
                $first = $groupRows->first();

                $sectionLabel = trim((string) ($first->section ?? ''));
                $yearLevelNumeric = $this->extractYearLevelFromSectionLabel($sectionLabel);
                $yearLevelLabel = $yearLevelNumeric ? $this->sectionOfferingYearLevelLabel($yearLevelNumeric) : 'N/A';

                $courseSlots = (int) ($first->course_slots ?? 0);
                if ($courseSlots < 1) {
                    $courseSlots = 50;
                }

                $facultyNames = $groupRows
                    ->map(function ($row) {
                        return trim((string) ($row->faculty_name ?? ''));
                    })
                    ->filter(function ($name) {
                        return $name !== '';
                    })
                    ->unique()
                    ->values();

                $adviser = 'TBA';
                if ($facultyNames->count() === 1) {
                    $adviser = (string) $facultyNames->first();
                } elseif ($facultyNames->count() > 1) {
                    $adviser = 'Multiple Faculty';
                }

                $subjects = $groupRows
                    ->map(function ($row) use ($courseSlots) {
                        $subjectCode = trim((string) ($row->subject_code ?? ''));
                        $subjectName = trim((string) ($row->subject_name ?? ''));
                        $timeStart = trim((string) ($row->time_start ?? ''));
                        $timeEnd = trim((string) ($row->time_end ?? ''));
                        $room = trim((string) ($row->room ?? ''));

                        $tuitionUnits = (float) ($row->units ?? 0);
                        $creditUnits = $row->credited_tuition_units !== null
                            ? (float) $row->credited_tuition_units
                            : $tuitionUnits;

                        $professor = trim((string) ($row->faculty_name ?? ''));
                        if ($professor === '') {
                            $professor = 'TBA';
                        }

                        return [
                            'code' => $subjectCode !== '' ? $subjectCode : 'N/A',
                            'description' => $subjectName !== '' ? $subjectName : 'N/A',
                            'lec' => (int) ($row->lec ?? 0),
                            'lab' => (int) ($row->lab ?? 0),
                            'tuitionUnits' => $tuitionUnits,
                            'creditUnits' => $creditUnits,
                            'room' => $room !== '' ? ('Room#' . $room) : 'TBA',
                            'professor' => $professor,
                            'slots' => $courseSlots,
                            'schedules' => $this->buildSectionOfferingScheduleLines(
                                (string) ($row->days ?? ''),
                                $timeStart,
                                $timeEnd,
                                $room
                            ),
                        ];
                    })
                    ->sortBy('code')
                    ->values();

                return [
                    'id' => 'so-' . substr(md5((string) $groupKey), 0, 16),
                    'program' => trim((string) ($first->course_code ?? '')),
                    'schoolYear' => trim((string) ($first->school_year ?? '')),
                    'semester' => $this->normalizeSlotMonitoringSemester((string) ($first->semester_label ?? '')),
                    'yearLevel' => $yearLevelLabel,
                    'section' => $sectionLabel,
                    'slots' => $courseSlots,
                    'adviser' => $adviser,
                    'description' => '',
                    'subjects' => $subjects->all(),
                    'subjectCount' => (int) $subjects->count(),
                    'courseId' => (int) ($first->course_id ?? 0),
                ];
            })
            ->values();

        if ($yearLevel !== '') {
            $sections = $sections
                ->filter(function ($section) use ($yearLevel) {
                    return (string) ($section['yearLevel'] ?? '') === $yearLevel;
                })
                ->values();
        }

        $sections = $sections
            ->sort(function ($left, $right) {
                $leftSchoolYear = (string) ($left['schoolYear'] ?? '');
                $rightSchoolYear = (string) ($right['schoolYear'] ?? '');
                if ($leftSchoolYear !== $rightSchoolYear) {
                    return strcmp($rightSchoolYear, $leftSchoolYear);
                }

                $leftSemesterWeight = $this->slotMonitoringSemesterWeight((string) ($left['semester'] ?? ''));
                $rightSemesterWeight = $this->slotMonitoringSemesterWeight((string) ($right['semester'] ?? ''));
                if ($leftSemesterWeight !== $rightSemesterWeight) {
                    return $leftSemesterWeight <=> $rightSemesterWeight;
                }

                $leftProgram = (string) ($left['program'] ?? '');
                $rightProgram = (string) ($right['program'] ?? '');
                if ($leftProgram !== $rightProgram) {
                    return strcmp($leftProgram, $rightProgram);
                }

                return strcmp((string) ($left['section'] ?? ''), (string) ($right['section'] ?? ''));
            })
            ->values();

        $allSections = $sections->values();
        $totalSections = (int) $allSections->count();
        $lastPage = max((int) ceil(max($totalSections, 1) / $perPage), 1);
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $sections = $allSections
            ->forPage($page, $perPage)
            ->values();

        $optionsPayload = $this->slotMonitoringOptionsPayload('', '', 0, '');

        $courseOptions = collect($optionsPayload['courses'] ?? [])
            ->map(function ($course) {
                return [
                    'id' => (int) ($course['id'] ?? 0),
                    'code' => trim((string) ($course['code'] ?? '')),
                    'name' => trim((string) ($course['name'] ?? '')),
                    'label' => trim((string) ($course['label'] ?? '')),
                ];
            })
            ->filter(function ($course) {
                return (int) ($course['id'] ?? 0) > 0;
            })
            ->values()
            ->all();

        $yearLevelOptions = $allSections
            ->pluck('yearLevel')
            ->filter(function ($value) {
                $text = trim((string) $value);
                return $text !== '' && $text !== 'N/A';
            })
            ->unique()
            ->sortBy(function ($value) {
                return $this->sectionOfferingYearLevelWeight((string) $value);
            })
            ->values()
            ->all();

        $sectionOptions = $allSections
            ->pluck('section')
            ->filter(function ($value) {
                return trim((string) $value) !== '';
            })
            ->unique()
            ->sort()
            ->values()
            ->all();

        $totalSubjects = (int) $allSections->sum(function ($section) {
            $subjects = $section['subjects'] ?? [];
            return is_array($subjects) ? count($subjects) : 0;
        });

        $visibleSubjects = (int) $sections->sum(function ($section) {
            $subjects = $section['subjects'] ?? [];
            return is_array($subjects) ? count($subjects) : 0;
        });

        return response()->json([
            'ok' => true,
            'sections' => $sections->all(),
            'options' => [
                'school_years' => collect($optionsPayload['school_years'] ?? [])->values()->all(),
                'semesters' => collect($optionsPayload['semesters'] ?? [])->values()->all(),
                'courses' => $courseOptions,
                'year_levels' => $yearLevelOptions,
                'sections' => $sectionOptions,
            ],
            'meta' => [
                'page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total_sections' => $totalSections,
                'total_subjects' => $totalSubjects,
                'visible_sections' => (int) $sections->count(),
                'visible_subjects' => $visibleSubjects,
            ],
        ]);
    }

    public function sectionOfferingCurriculumSubjects(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'semester' => 'required|string|max:30',
            'year_level' => 'required|string|max:20',
        ]);

        $courseId = (int) $validated['course_id'];
        $semester = $this->normalizeSlotMonitoringSemester((string) $validated['semester']);
        $yearLevel = $this->normalizeSectionOfferingYearLevel((string) $validated['year_level']);

        if ($semester === '') {
            throw ValidationException::withMessages([
                'semester' => ['Please select a valid semester.'],
            ]);
        }

        if ($yearLevel === '') {
            throw ValidationException::withMessages([
                'year_level' => ['Please select a valid year level.'],
            ]);
        }

        $yearLevelNumber = $this->sectionOfferingYearLevelWeight($yearLevel);
        $yearBlockId = $this->resolveSectionOfferingYearBlockId($yearLevelNumber);
        $semesterIds = $this->resolveSectionOfferingSemesterIds($semester);
        $curriculum = $this->resolveSectionOfferingCurriculum($courseId);

        if (!$curriculum || !$yearBlockId || !count($semesterIds)) {
            return response()->json([
                'ok' => true,
                'rows' => [],
                'meta' => [
                    'course_id' => $courseId,
                    'curriculum_id' => $curriculum ? (int) $curriculum->id : null,
                    'curriculum_year' => $curriculum ? (string) $curriculum->curriculum_year_code : '',
                    'year_level' => $yearLevel,
                    'semester' => $semester,
                    'total' => 0,
                ],
            ]);
        }

        $rows = CourseCurriculumSubject::query()
            ->with('subject')
            ->where('course_curriculum_id', (int) $curriculum->id)
            ->where('year_block_id', (int) $yearBlockId)
            ->whereIn('semester_id', $semesterIds)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->map(function (CourseCurriculumSubject $assignment) {
                $subject = $assignment->subject;

                return [
                    'id' => (int) $assignment->id,
                    'subject_id' => (int) $assignment->subject_id,
                    'code' => trim((string) optional($subject)->code),
                    'description' => trim((string) optional($subject)->name),
                    'units' => $subject ? (float) $subject->units : (float) $assignment->credited_units,
                    'lec' => $subject ? (int) $subject->lec : 0,
                    'lab' => $subject ? (int) $subject->lab : 0,
                    'credited_units' => (float) $assignment->credited_units,
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'rows' => $rows,
            'meta' => [
                'course_id' => $courseId,
                'curriculum_id' => (int) $curriculum->id,
                'curriculum_year' => (string) $curriculum->curriculum_year_code,
                'year_level' => $yearLevel,
                'semester' => $semester,
                'total' => (int) $rows->count(),
            ],
        ]);
    }

    public function storeSectionOffering(StoreSectionOfferingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $courseId = (int) $validated['course_id'];
        $schoolYear = $this->normalizeSectionOfferingSchoolYear((string) $validated['school_year']);
        $semester = $this->normalizeSlotMonitoringSemester((string) $validated['semester']);
        $yearLevel = $this->normalizeSectionOfferingYearLevel((string) $validated['year_level']);
        $yearLevelNumber = $this->sectionOfferingYearLevelWeight($yearLevel);
        $sectionCode = $this->sanitizeSectionOfferingSectionCode((string) $validated['section']);
        $sectionLabel = $this->composeSectionOfferingLabel($yearLevelNumber, $sectionCode);
        $slots = isset($validated['slots']) ? (int) $validated['slots'] : null;
        $adviserName = trim((string) ($validated['adviser'] ?? ''));

        $selectedCurriculumSubjectIds = collect((array) ($validated['curriculum_subject_ids'] ?? []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values();

        if ($semester === '') {
            throw ValidationException::withMessages([
                'semester' => ['Please select a valid semester.'],
            ]);
        }

        if ($yearLevel === '' || $yearLevelNumber > 6) {
            throw ValidationException::withMessages([
                'year_level' => ['Please select a valid year level.'],
            ]);
        }

        if ($sectionCode === '' || $sectionLabel === '') {
            throw ValidationException::withMessages([
                'section' => ['Please provide a valid section code.'],
            ]);
        }

        if ($selectedCurriculumSubjectIds->isEmpty()) {
            throw ValidationException::withMessages([
                'curriculum_subject_ids' => ['Select at least one curriculum subject.'],
            ]);
        }

        $curriculum = $this->resolveSectionOfferingCurriculum($courseId);
        if (!$curriculum) {
            throw ValidationException::withMessages([
                'course_id' => ['No curriculum is configured for the selected course.'],
            ]);
        }

        $yearBlockId = $this->resolveSectionOfferingYearBlockId($yearLevelNumber);
        if (!$yearBlockId) {
            throw ValidationException::withMessages([
                'year_level' => ['Year level is not configured in curriculum dimensions.'],
            ]);
        }

        $semesterIds = $this->resolveSectionOfferingSemesterIds($semester);
        if (!count($semesterIds)) {
            throw ValidationException::withMessages([
                'semester' => ['Semester is not configured in curriculum dimensions.'],
            ]);
        }

        $assignments = CourseCurriculumSubject::query()
            ->with('subject')
            ->where('course_curriculum_id', (int) $curriculum->id)
            ->where('year_block_id', (int) $yearBlockId)
            ->whereIn('semester_id', $semesterIds)
            ->whereIn('id', $selectedCurriculumSubjectIds->all())
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($assignments->count() !== $selectedCurriculumSubjectIds->count()) {
            throw ValidationException::withMessages([
                'curriculum_subject_ids' => ['One or more selected curriculum subjects are invalid for the chosen course, year level, or semester.'],
            ]);
        }

        $academicTermId = $this->resolveSectionOfferingAcademicTermId($schoolYear, $semester);
        $facultyId = $this->resolveSectionOfferingFacultyId($adviserName);

        $existingSectionQuery = Subject::query()
            ->where('course_id', $courseId)
            ->where('academic_term_id', $academicTermId)
            ->where('year_section', $sectionLabel);

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $existingSectionQuery->where(function ($builder) {
                $builder->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 0);
            });
        }

        if ($existingSectionQuery->exists()) {
            return response()->json([
                'message' => 'Section already exists for the selected school year, semester, and course.',
                'errors' => [
                    'section' => ['Section ' . $sectionLabel . ' already exists.'],
                ],
            ], 422);
        }

        $now = now();
        $addedBy = auth()->check() ? trim((string) optional(auth()->user())->name) : '';
        if ($addedBy === '') {
            $addedBy = null;
        }

        $rowsToInsert = $assignments
            ->map(function (CourseCurriculumSubject $assignment) use ($courseId, $academicTermId, $sectionLabel, $facultyId, $addedBy, $now) {
                $subject = $assignment->subject;

                $code = trim((string) optional($subject)->code);
                if ($code === '') {
                    $code = 'SUBJ-' . (int) $assignment->id;
                }

                $name = trim((string) optional($subject)->name);
                if ($name === '') {
                    $name = 'Curriculum Subject ' . (int) $assignment->id;
                }

                $units = $subject ? (float) $subject->units : (float) $assignment->credited_units;
                $lec = $subject ? (int) $subject->lec : 0;
                $lab = $subject ? (int) $subject->lab : 0;

                $creditedTuitionUnits = $subject && $subject->credited_tuition_units !== null
                    ? (float) $subject->credited_tuition_units
                    : (float) $assignment->credited_units;

                $loadHours = $subject && $subject->load_hours !== null
                    ? (float) $subject->load_hours
                    : null;

                return [
                    'code' => $code,
                    'name' => $name,
                    'is_subject_file_record' => 0,
                    'units' => $units,
                    'lec' => $lec,
                    'lab' => $lab,
                    'is_core' => $subject ? (int) ((bool) $subject->is_core) : 0,
                    'is_applied' => $subject ? (int) ((bool) $subject->is_applied) : 0,
                    'is_specialized' => $subject ? (int) ((bool) $subject->is_specialized) : 0,
                    'days' => null,
                    'time_start' => null,
                    'time_end' => null,
                    'room' => null,
                    'faculty_id' => $facultyId,
                    'year_section' => $sectionLabel,
                    'course_id' => $courseId,
                    'academic_term_id' => $academicTermId,
                    'grading_status_id' => null,
                    'load_type_id' => null,
                    'credited_tuition_units' => $creditedTuitionUnits,
                    'load_hours' => $loadHours,
                    'added_by' => $addedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        if (!count($rowsToInsert)) {
            throw ValidationException::withMessages([
                'curriculum_subject_ids' => ['Selected curriculum subjects could not be mapped to subject definitions.'],
            ]);
        }

        DB::transaction(function () use ($courseId, $slots, $rowsToInsert) {
            if ($slots && $slots > 0) {
                Course::query()
                    ->where('id', $courseId)
                    ->update(['slots' => $slots]);
            }

            DB::table('subjects')->insert($rowsToInsert);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Section created successfully.',
            'section' => [
                'course_id' => $courseId,
                'school_year' => $schoolYear,
                'semester' => $semester,
                'year_level' => $yearLevel,
                'section' => $sectionLabel,
                'subject_count' => count($rowsToInsert),
                'adviser' => $adviserName !== '' ? $adviserName : null,
                'adviser_applied' => $facultyId !== null,
            ],
        ], 201);
    }

    private function resolveSectionOfferingPage($value): int
    {
        $page = (int) $value;

        return $page >= 1 ? $page : 1;
    }

    private function resolveSectionOfferingPerPage($value): int
    {
        $safePerPage = (int) $value;

        if ($safePerPage < self::SECTION_OFFERING_MIN_PER_PAGE) {
            return self::SECTION_OFFERING_MIN_PER_PAGE;
        }

        if ($safePerPage > self::SECTION_OFFERING_MAX_PER_PAGE) {
            return self::SECTION_OFFERING_MAX_PER_PAGE;
        }

        return $safePerPage;
    }

    private function normalizeSectionOfferingSchoolYear(string $value): string
    {
        $normalized = preg_replace('/\s+/', '', trim($value));

        if (preg_match('/^(\d{4})-(\d{4})$/', $normalized, $matches) === 1) {
            return $matches[1] . '-' . $matches[2];
        }

        return $normalized;
    }

    private function resolveSectionOfferingYearBlockId(int $yearLevelNumber)
    {
        $labelMap = [
            1 => '1st Year',
            2 => '2nd Year',
            3 => '3rd Year',
            4 => '4th Year',
            5 => '5th Year',
            6 => '6th Year',
        ];

        $label = $labelMap[$yearLevelNumber] ?? null;
        if (!$label) {
            return null;
        }

        $id = YearBlock::query()
            ->where('label', $label)
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        $fallbackId = YearBlock::query()
            ->where('id', $yearLevelNumber)
            ->value('id');

        return $fallbackId ? (int) $fallbackId : null;
    }

    private function resolveSectionOfferingSemesterIds(string $semesterCanonical): array
    {
        if (!Schema::hasTable('semesters')) {
            return [];
        }

        $aliases = collect($this->slotMonitoringSemesterAliases($semesterCanonical))
            ->push($this->sectionOfferingDatabaseSemesterLabel($semesterCanonical))
            ->map(function ($value) {
                return strtolower(trim((string) $value));
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->values()
            ->all();

        if (!count($aliases)) {
            return [];
        }

        return Semester::query()
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $aliases)
            ->pluck('id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function resolveSectionOfferingCurriculum(int $courseId)
    {
        $query = CourseCurriculum::query()
            ->where('course_id', $courseId);

        if (Schema::hasColumn('course_curricula', 'is_active')) {
            $query->orderByDesc('is_active');
        }

        return $query
            ->orderByDesc('id')
            ->first();
    }

    private function resolveSectionOfferingAcademicTermId(string $schoolYear, string $semesterCanonical): int
    {
        $aliases = collect($this->slotMonitoringSemesterAliases($semesterCanonical))
            ->map(function ($value) {
                return strtolower(trim((string) $value));
            })
            ->values()
            ->all();

        $existingId = DB::table('academic_terms')
            ->where('school_year', $schoolYear)
            ->whereIn(DB::raw('LOWER(TRIM(term))'), $aliases)
            ->orderByDesc('id')
            ->value('id');

        if (!empty($existingId)) {
            return (int) $existingId;
        }

        $termLabel = $this->sectionOfferingDatabaseSemesterLabel($semesterCanonical);
        $canonicalKey = strtolower(trim($schoolYear) . '|' . trim($termLabel));

        try {
            return (int) DB::table('academic_terms')->insertGetId([
                'school_year' => $schoolYear,
                'term' => $termLabel,
                'canonical_key' => $canonicalKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                $id = DB::table('academic_terms')
                    ->where('canonical_key', $canonicalKey)
                    ->value('id');

                if (!empty($id)) {
                    return (int) $id;
                }
            }

            throw $exception;
        }
    }

    private function sectionOfferingDatabaseSemesterLabel(string $semesterCanonical): string
    {
        if ($semesterCanonical === 'First') {
            return 'First Semester';
        }

        if ($semesterCanonical === 'Second') {
            return 'Second Semester';
        }

        if ($semesterCanonical === 'Summer') {
            return 'Summer Semester';
        }

        return trim($semesterCanonical) !== '' ? trim($semesterCanonical) : 'First Semester';
    }

    private function sanitizeSectionOfferingSectionCode(string $value): string
    {
        $normalized = strtoupper(trim($value));
        $normalized = preg_replace('/\s+/', '', $normalized);
        $normalized = preg_replace('/[^A-Z0-9-]/', '', $normalized);

        return trim((string) $normalized, '-');
    }

    private function composeSectionOfferingLabel(int $yearLevelNumber, string $sectionCode): string
    {
        if ($sectionCode === '' || $yearLevelNumber < 1) {
            return '';
        }

        if (preg_match('/^([1-6])-?([A-Z0-9]+)$/', $sectionCode, $matches) === 1) {
            return $matches[1] . '-' . $matches[2];
        }

        return $yearLevelNumber . '-' . ltrim($sectionCode, '-');
    }

    private function resolveSectionOfferingFacultyId(string $adviserName)
    {
        $normalized = strtolower(trim($adviserName));
        if ($normalized === '' || !Schema::hasTable('faculties')) {
            return null;
        }

        $id = Faculty::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function buildSectionOfferingSubjectRowsQuery($schoolYear = '', $semester = '', $sectionQuery = '', $search = '', $courseId = 0)
    {
        $hasFacultyLookup = Schema::hasTable('faculties') && Schema::hasColumn('subjects', 'faculty_id');
        $hasLegacyFacultyColumn = Schema::hasColumn('subjects', 'faculty');

        $facultyNameCandidates = [];
        if ($hasFacultyLookup) {
            $facultyNameCandidates[] = 'sm_faculties.name';
        }
        if ($hasLegacyFacultyColumn) {
            $facultyNameCandidates[] = 'sm_subjects.faculty';
        }

        $facultyNameExpression = "''";
        if (count($facultyNameCandidates)) {
            $facultyNameExpression = 'COALESCE(' . implode(', ', $facultyNameCandidates) . ", '')";
        }

        $query = $this->slotMonitoringSubjectBaseQuery()
            ->select([
                'sm_subjects.id as id',
                DB::raw('COALESCE(sm_terms.school_year, "") as school_year'),
                DB::raw('COALESCE(sm_terms.term, "") as semester_label'),
                DB::raw('COALESCE(sm_subjects.course_id, 0) as course_id'),
                DB::raw('COALESCE(sm_courses.code, "") as course_code'),
                DB::raw('COALESCE(sm_courses.name, "") as course_name'),
                DB::raw('CASE WHEN sm_courses.slots IS NULL OR sm_courses.slots < 1 THEN 50 ELSE sm_courses.slots END as course_slots'),
                DB::raw('COALESCE(sm_subjects.year_section, "") as section'),
                DB::raw('COALESCE(sm_subjects.code, "") as subject_code'),
                DB::raw('COALESCE(sm_subjects.name, "") as subject_name'),
                DB::raw('COALESCE(sm_subjects.units, 0) as units'),
                DB::raw('COALESCE(sm_subjects.lec, 0) as lec'),
                DB::raw('COALESCE(sm_subjects.lab, 0) as lab'),
                DB::raw('sm_subjects.credited_tuition_units as credited_tuition_units'),
                DB::raw('COALESCE(sm_subjects.days, "") as days'),
                DB::raw('COALESCE(sm_subjects.time_start, "") as time_start'),
                DB::raw('COALESCE(sm_subjects.time_end, "") as time_end'),
                DB::raw('COALESCE(sm_subjects.room, "") as room'),
                DB::raw($facultyNameExpression . ' as faculty_name'),
            ])
            ->whereNotNull('sm_subjects.year_section')
            ->whereRaw("TRIM(COALESCE(sm_subjects.year_section, '')) <> ''");

        if ($schoolYear !== '') {
            $query->where('sm_terms.school_year', $schoolYear);
        }

        if ($semester !== '' && in_array($semester, self::SLOT_MONITORING_ALLOWED_SEMESTERS, true)) {
            $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->values()
                ->all();

            $query->whereIn(
                DB::raw('LOWER(TRIM(COALESCE(sm_terms.term, "")))'),
                $semesterAliases
            );
        }

        if ($sectionQuery !== '') {
            $query->where('sm_subjects.year_section', 'like', '%' . $sectionQuery . '%');
        }

        if ($courseId > 0) {
            $query->where('sm_subjects.course_id', $courseId);
        }

        $normalizedSearch = preg_replace('/\s+/', ' ', $search);
        $normalizedSearch = trim((string) $normalizedSearch);
        if ($normalizedSearch !== '' && strlen($normalizedSearch) >= 2) {
            $query->where(function ($builder) use ($normalizedSearch, $hasFacultyLookup, $hasLegacyFacultyColumn) {
                $builder->where('sm_subjects.year_section', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_subjects.code', 'like', $normalizedSearch . '%')
                    ->orWhere('sm_subjects.name', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_courses.code', 'like', $normalizedSearch . '%')
                    ->orWhere('sm_courses.name', 'like', '%' . $normalizedSearch . '%');

                if ($hasFacultyLookup) {
                    $builder->orWhere('sm_faculties.name', 'like', '%' . $normalizedSearch . '%');
                }

                if ($hasLegacyFacultyColumn) {
                    $builder->orWhere('sm_subjects.faculty', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        return $query
            ->orderBy('sm_terms.school_year', 'desc')
            ->orderByRaw("CASE
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('first', '1st semester', 'first semester') THEN 1
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('second', '2nd semester', 'second semester') THEN 2
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('summer', 'summer semester') THEN 3
                ELSE 4
            END")
            ->orderBy('sm_courses.code')
            ->orderBy('sm_subjects.year_section')
            ->orderBy('sm_subjects.code')
            ->orderBy('sm_subjects.id');
    }

    private function normalizeSectionOfferingYearLevel(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'first') !== false || $normalized === '1') {
            return 'First';
        }

        if (strpos($normalized, 'second') !== false || $normalized === '2') {
            return 'Second';
        }

        if (strpos($normalized, 'third') !== false || $normalized === '3') {
            return 'Third';
        }

        if (strpos($normalized, 'fourth') !== false || $normalized === '4') {
            return 'Fourth';
        }

        if (strpos($normalized, 'fifth') !== false || $normalized === '5') {
            return 'Fifth';
        }

        if (strpos($normalized, 'sixth') !== false || $normalized === '6') {
            return 'Sixth';
        }

        return '';
    }

    private function sectionOfferingYearLevelLabel($value): string
    {
        $level = (int) $value;

        if ($level === 1) {
            return 'First';
        }

        if ($level === 2) {
            return 'Second';
        }

        if ($level === 3) {
            return 'Third';
        }

        if ($level === 4) {
            return 'Fourth';
        }

        if ($level === 5) {
            return 'Fifth';
        }

        if ($level === 6) {
            return 'Sixth';
        }

        return 'N/A';
    }

    private function sectionOfferingYearLevelWeight(string $value): int
    {
        $normalized = $this->normalizeSectionOfferingYearLevel($value);

        if ($normalized === 'First') {
            return 1;
        }

        if ($normalized === 'Second') {
            return 2;
        }

        if ($normalized === 'Third') {
            return 3;
        }

        if ($normalized === 'Fourth') {
            return 4;
        }

        if ($normalized === 'Fifth') {
            return 5;
        }

        if ($normalized === 'Sixth') {
            return 6;
        }

        return 99;
    }

    private function buildSectionOfferingScheduleLines($days, $timeStart, $timeEnd, $room)
    {
        $start = trim((string) $timeStart);
        $end = trim((string) $timeEnd);

        $timeRange = 'TBA';
        if ($start !== '' && $end !== '') {
            $timeRange = $start . '-' . $end;
        } elseif ($start !== '' || $end !== '') {
            $timeRange = $start !== '' ? $start : $end;
        }

        $roomLabel = trim((string) $room);
        if ($roomLabel === '') {
            $roomLabel = 'TBA';
        } else {
            $roomLabel = 'Room#' . $roomLabel;
        }

        $dayTokens = $this->extractSectionOfferingDayTokens((string) $days);
        if (empty($dayTokens)) {
            return [$timeRange . ' ' . $roomLabel];
        }

        return collect($dayTokens)
            ->map(function ($dayToken) use ($timeRange, $roomLabel) {
                return $dayToken . ' ' . $timeRange . ' ' . $roomLabel;
            })
            ->values()
            ->all();
    }

    private function extractSectionOfferingDayTokens($days)
    {
        $normalized = strtoupper(trim((string) $days));
        if ($normalized === '') {
            return [];
        }

        $wordMap = [
            'MONDAY' => 'M',
            'MON' => 'M',
            'TUESDAY' => 'T',
            'TUE' => 'T',
            'WEDNESDAY' => 'W',
            'WED' => 'W',
            'THURSDAY' => 'TH',
            'THU' => 'TH',
            'FRIDAY' => 'F',
            'FRI' => 'F',
            'SATURDAY' => 'S',
            'SAT' => 'S',
            'SUNDAY' => 'SU',
            'SUN' => 'SU',
        ];

        foreach ($wordMap as $word => $code) {
            $normalized = str_replace($word, $code, $normalized);
        }

        $normalized = preg_replace('/[^A-Z]/', '', $normalized);
        if ($normalized === '') {
            return [];
        }

        $tokens = [];
        $length = strlen($normalized);

        for ($index = 0; $index < $length; $index++) {
            $pair = substr($normalized, $index, 2);

            if ($pair === 'TH' || $pair === 'SU') {
                $tokens[] = $pair;
                $index++;
                continue;
            }

            $single = $normalized[$index];
            if (in_array($single, ['M', 'T', 'W', 'F', 'S'], true)) {
                $tokens[] = $single;
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Registrar > Scheduling > Slot Monitoring
     */
    public function slotMonitoring()
    {
        $options = $this->slotMonitoringOptionsPayload('', '', 0, 'filters');

        $schoolYearOptions = collect($options['school_years'] ?? [])
            ->map(function ($value) {
                $text = trim((string) $value);

                return [
                    'value' => $text,
                    'label' => $text,
                ];
            })
            ->filter(function ($option) {
                return $option['value'] !== '';
            })
            ->values();

        $semesterOptions = collect($options['semesters'] ?? [])
            ->map(function ($value) {
                $text = trim((string) $value);

                return [
                    'value' => $text,
                    'label' => $text,
                ];
            })
            ->filter(function ($option) {
                return $option['value'] !== '';
            })
            ->values();

        $schoolYearOptions->prepend([
            'value' => '',
            'label' => '- All -',
        ]);

        $semesterOptions->prepend([
            'value' => '',
            'label' => '- All -',
        ]);

        return view('registrar.registrar-menu.scheduling.slot-monitoring', [
            'schoolYearOptions' => $schoolYearOptions,
            'semesterOptions' => $semesterOptions,
        ]);
    }

    public function slotMonitoringData(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $sectionQuery = trim((string) $request->query('section', ''));
        $courseQuery = trim((string) $request->query('course_query', ''));
        $courseId = (int) $request->query('course_id', 0);
        $page = $this->resolveSlotMonitoringPage($request->query('page', 1));
        $perPage = $this->resolveSlotMonitoringPerPage($request->query('per_page', self::SLOT_MONITORING_DEFAULT_PER_PAGE));
        $includeOptions = $this->requestBoolean($request, 'include_options');
        $optionsMode = trim((string) $request->query('options_mode', ''));

        $query = $this->buildSlotMonitoringSubjectRowsQuery(
            $schoolYear,
            $semester,
            $sectionQuery,
            $courseQuery,
            $search,
            $courseId
        );

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $rows = collect($paginator->items())
            ->map(function ($row) {
                return $this->mapSlotMonitoringSubjectRow($row);
            })
            ->values();

        $options = null;
        if ($includeOptions) {
            $options = $this->slotMonitoringOptionsPayload($schoolYear, $semester, $courseId, $optionsMode);
        }

        return response()->json([
            'ok' => true,
            'rows' => $rows,
            'options' => $options,
            'meta' => [
                'page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'per_page' => (int) $paginator->perPage(),
                'total' => (int) $paginator->total(),
            ],
        ]);
    }

    public function slotMonitoringReport(Request $request, $reportType)
    {
        $normalizedReportType = strtolower(trim((string) $reportType));
        if (!in_array($normalizedReportType, ['actual-size', 'under-20', 'dissolved', 'closed'], true)) {
            abort(404);
        }

        $search = trim((string) $request->query('search', ''));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $sectionQuery = trim((string) $request->query('section', ''));
        $courseQuery = trim((string) $request->query('course_query', ''));
        $courseId = (int) $request->query('course_id', 0);

        $query = $this->buildSlotMonitoringSubjectRowsQuery(
            $schoolYear,
            $semester,
            $sectionQuery,
            $courseQuery,
            $search,
            $courseId
        );

        if ($normalizedReportType === 'under-20') {
            $query->whereRaw('COALESCE(sm_enrollment_totals.enrolled_slots, 0) <= 20');
        } elseif ($normalizedReportType === 'dissolved') {
            $query->whereRaw('COALESCE(sm_enrollment_totals.enrolled_slots, 0) = 0');
        } elseif ($normalizedReportType === 'closed') {
            $query->whereRaw('COALESCE(sm_enrollment_totals.enrolled_slots, 0) >= CASE WHEN sm_courses.slots IS NULL OR sm_courses.slots < 1 THEN 50 ELSE sm_courses.slots END');
        }

        $rows = $query->get()
            ->map(function ($row) {
                return $this->mapSlotMonitoringSubjectRow($row);
            })
            ->values();

        return view('registrar.registrar-menu.scheduling.slot-monitoring-report', [
            'reportType' => $normalizedReportType,
            'reportTitle' => $this->slotMonitoringReportTitle($normalizedReportType),
            'selectedSchoolYear' => $schoolYear !== '' ? $schoolYear : 'All',
            'selectedSemester' => $semester !== '' ? $semester : 'All',
            'generatedAt' => Carbon::now(),
            'rows' => $rows,
        ]);
    }

    public function storeSlotMonitoring(StoreSlotMonitoringRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $slotMonitoring = null;

        DB::beginTransaction();
        try {
            $slotMonitoring = SlotMonitoring::query()->create([
                'school_year' => (string) $validated['school_year'],
                'semester' => (string) $validated['semester'],
                'course_id' => (int) $validated['course_id'],
                'section' => (string) $validated['section'],
                'subject' => (string) $validated['subject'],
                'schedule' => (string) $validated['schedule'],
                'total_slots' => (int) $validated['total_slots'],
                'enrolled_slots' => isset($validated['enrolled_slots']) ? (int) $validated['enrolled_slots'] : 0,
                'updated_by_user_id' => auth()->id(),
            ]);

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicateSlotMonitoringConstraint($exception)) {
                return response()->json([
                    'message' => 'Duplicate slot entry.',
                    'errors' => [
                        'subject' => ['A slot entry with the same school year, semester, course, section, subject, and schedule already exists.'],
                    ],
                ], 422);
            }

            throw $exception;
        }

        $slotMonitoring->load(['course:id,code,name', 'updatedBy:id,name']);

        return response()->json([
            'ok' => true,
            'id' => (int) $slotMonitoring->id,
            'row' => $this->mapSlotMonitoringRow($slotMonitoring),
        ]);
    }

    public function updateSlotMonitoring(UpdateSlotMonitoringRequest $request, SlotMonitoring $slotMonitoring): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $slotMonitoring->update([
                'school_year' => (string) $validated['school_year'],
                'semester' => (string) $validated['semester'],
                'course_id' => (int) $validated['course_id'],
                'section' => (string) $validated['section'],
                'subject' => (string) $validated['subject'],
                'schedule' => (string) $validated['schedule'],
                'total_slots' => (int) $validated['total_slots'],
                'enrolled_slots' => isset($validated['enrolled_slots']) ? (int) $validated['enrolled_slots'] : 0,
                'updated_by_user_id' => auth()->id(),
            ]);

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicateSlotMonitoringConstraint($exception)) {
                return response()->json([
                    'message' => 'Duplicate slot entry.',
                    'errors' => [
                        'subject' => ['A slot entry with the same school year, semester, course, section, subject, and schedule already exists.'],
                    ],
                ], 422);
            }

            throw $exception;
        }

        $slotMonitoring->load(['course:id,code,name', 'updatedBy:id,name']);

        return response()->json([
            'ok' => true,
            'row' => $this->mapSlotMonitoringRow($slotMonitoring),
        ]);
    }

    public function destroySlotMonitoring(SlotMonitoring $slotMonitoring): JsonResponse
    {
        $slotMonitoring->delete();

        return response()->json(['ok' => true]);
    }

    private function slotMonitoringOptionsPayload($schoolYear = '', $semester = '', $courseId = 0, $optionsMode = '')
    {
        $baseQuery = $this->slotMonitoringSubjectBaseQuery();

        $schoolYears = (clone $baseQuery)
            ->select('sm_terms.school_year')
            ->whereNotNull('sm_terms.school_year')
            ->whereRaw("TRIM(sm_terms.school_year) <> ''")
            ->distinct()
            ->orderBy('sm_terms.school_year', 'desc')
            ->pluck('sm_terms.school_year')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values();

        if ($schoolYears->isEmpty()) {
            $year = (int) date('Y');
            $schoolYears = collect([
                ($year - 1) . '-' . $year,
                $year . '-' . ($year + 1),
            ]);
        }

        $semesterQuery = clone $baseQuery;
        if ($schoolYear !== '') {
            $semesterQuery->where('sm_terms.school_year', $schoolYear);
        }

        $semesters = collect(self::SLOT_MONITORING_ALLOWED_SEMESTERS)
            ->merge(
                $semesterQuery
                    ->select('sm_terms.term')
                    ->whereNotNull('sm_terms.term')
                    ->whereRaw("TRIM(sm_terms.term) <> ''")
                    ->distinct()
                    ->pluck('sm_terms.term')
                    ->map(function ($value) {
                        return $this->normalizeSlotMonitoringSemester((string) $value);
                    })
            )
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->sortBy(function ($value) {
                return $this->slotMonitoringSemesterWeight((string) $value);
            })
            ->values();

        $normalizedMode = strtolower(trim((string) $optionsMode));
        if ($normalizedMode === 'filters') {
            return [
                'school_years' => $schoolYears,
                'semesters' => $semesters,
            ];
        }

        $courseQueryBuilder = $this->slotMonitoringSubjectBaseQuery()
            ->select([
                'sm_courses.id',
                'sm_courses.code',
                'sm_courses.name',
            ])
            ->whereNotNull('sm_courses.id');

        if ($schoolYear !== '') {
            $courseQueryBuilder->where('sm_terms.school_year', $schoolYear);
        }

        if ($semester !== '' && in_array($semester, self::SLOT_MONITORING_ALLOWED_SEMESTERS, true)) {
            $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->values()
                ->all();

            $courseQueryBuilder->whereIn(
                DB::raw('LOWER(TRIM(COALESCE(sm_terms.term, "")))'),
                $semesterAliases
            );
        }

        $courses = $courseQueryBuilder
            ->distinct()
            ->orderBy('sm_courses.code')
            ->get()
            ->map(function ($course) {
                $label = trim((string) $course->code);
                if ($label === '') {
                    $label = (string) $course->name;
                }

                return [
                    'id' => (int) $course->id,
                    'code' => (string) $course->code,
                    'name' => (string) $course->name,
                    'label' => $label,
                ];
            })
            ->values();

        return [
            'school_years' => $schoolYears,
            'semesters' => $semesters,
            'courses' => $courses,
        ];
    }

    private function buildSlotMonitoringSubjectRowsQuery($schoolYear = '', $semester = '', $sectionQuery = '', $courseQuery = '', $search = '', $courseId = 0)
    {
        $hasFacultyLookup = Schema::hasTable('faculties') && Schema::hasColumn('subjects', 'faculty_id');
        $facultyNameCandidates = [];
        if ($hasFacultyLookup) {
            $facultyNameCandidates[] = 'sm_faculties.name';
        }
        if (Schema::hasColumn('subjects', 'faculty')) {
            $facultyNameCandidates[] = 'sm_subjects.faculty';
        }

        $facultyNameExpression = "''";
        if (count($facultyNameCandidates)) {
            $facultyNameExpression = 'COALESCE(' . implode(', ', $facultyNameCandidates) . ", '')";
        }

        $enrollmentTotals = DB::table('student_subject as sm_student_subject')
            ->select([
                'sm_student_subject.subject_id',
                DB::raw('COUNT(*) as enrolled_slots'),
            ])
            ->groupBy('sm_student_subject.subject_id');

        $query = $this->slotMonitoringSubjectBaseQuery()
            ->leftJoinSub($enrollmentTotals, 'sm_enrollment_totals', function ($join) {
                $join->on('sm_enrollment_totals.subject_id', '=', 'sm_subjects.id');
            })
            ->select([
                'sm_subjects.id as id',
                DB::raw('COALESCE(sm_terms.school_year, "") as school_year'),
                DB::raw('COALESCE(sm_terms.term, "") as semester_label'),
                DB::raw('COALESCE(sm_subjects.course_id, 0) as course_id'),
                DB::raw('COALESCE(sm_courses.code, "") as course_code'),
                DB::raw('COALESCE(sm_courses.name, "") as course_name'),
                DB::raw('COALESCE(sm_subjects.year_section, "") as section'),
                DB::raw('COALESCE(sm_subjects.code, "") as subject_code'),
                DB::raw('COALESCE(sm_subjects.name, "") as subject_name'),
                DB::raw($facultyNameExpression . ' as faculty_name'),
                DB::raw('COALESCE(sm_subjects.days, "") as days'),
                DB::raw('COALESCE(sm_subjects.time_start, "") as time_start'),
                DB::raw('COALESCE(sm_subjects.time_end, "") as time_end'),
                DB::raw('COALESCE(sm_subjects.room, "") as room'),
                DB::raw('CASE WHEN sm_courses.slots IS NULL OR sm_courses.slots < 1 THEN 50 ELSE sm_courses.slots END as total_slots'),
                DB::raw('COALESCE(sm_enrollment_totals.enrolled_slots, 0) as enrolled_slots'),
            ]);

        if ($schoolYear !== '') {
            $query->where('sm_terms.school_year', $schoolYear);
        }

        if ($semester !== '' && in_array($semester, self::SLOT_MONITORING_ALLOWED_SEMESTERS, true)) {
            $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->values()
                ->all();

            $query->whereIn(
                DB::raw('LOWER(TRIM(COALESCE(sm_terms.term, "")))'),
                $semesterAliases
            );
        }

        if ($sectionQuery !== '') {
            $query->where('sm_subjects.year_section', 'like', $sectionQuery . '%');
        }

        if ($courseId > 0) {
            $query->where('sm_subjects.course_id', $courseId);
        }

        if ($courseQuery !== '' && strlen($courseQuery) >= 2) {
            $query->where(function ($builder) use ($courseQuery) {
                $builder->where('sm_courses.code', 'like', $courseQuery . '%')
                    ->orWhere('sm_courses.name', 'like', '%' . $courseQuery . '%');
            });
        }

        $normalizedSearch = preg_replace('/\s+/', ' ', $search);
        $normalizedSearch = trim((string) $normalizedSearch);
        if ($normalizedSearch !== '' && strlen($normalizedSearch) >= 2) {
            $query->where(function ($builder) use ($normalizedSearch) {
                $builder->where('sm_subjects.code', 'like', $normalizedSearch . '%')
                    ->orWhere('sm_subjects.name', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_subjects.year_section', 'like', $normalizedSearch . '%')
                    ->orWhereRaw(
                        "CONCAT_WS(' ', COALESCE(sm_subjects.days, ''), COALESCE(sm_subjects.time_start, ''), COALESCE(sm_subjects.time_end, ''), COALESCE(sm_subjects.room, '')) LIKE ?",
                        ['%' . $normalizedSearch . '%']
                    );
            });
        }

        return $query
            ->orderBy('sm_terms.school_year', 'desc')
            ->orderByRaw("CASE
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('first', '1st semester', 'first semester') THEN 1
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('second', '2nd semester', 'second semester') THEN 2
                WHEN LOWER(TRIM(COALESCE(sm_terms.term, ''))) IN ('summer', 'summer semester') THEN 3
                ELSE 4
            END")
            ->orderBy('sm_subjects.year_section')
            ->orderBy('sm_subjects.code')
            ->orderBy('sm_subjects.id');
    }

    private function slotMonitoringSubjectBaseQuery()
    {
        $query = DB::table('subjects as sm_subjects')
            ->leftJoin('courses as sm_courses', 'sm_courses.id', '=', 'sm_subjects.course_id')
            ->leftJoin('departments as sm_departments', 'sm_departments.id', '=', 'sm_courses.department_id')
            ->leftJoin('academic_terms as sm_terms', 'sm_terms.id', '=', 'sm_subjects.academic_term_id')
            ->whereNotNull('sm_subjects.course_id');

        if (Schema::hasTable('faculties') && Schema::hasColumn('subjects', 'faculty_id')) {
            $query->leftJoin('faculties as sm_faculties', 'sm_faculties.id', '=', 'sm_subjects.faculty_id');
        }

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($builder) {
                $builder->whereNull('sm_subjects.is_subject_file_record')
                    ->orWhere('sm_subjects.is_subject_file_record', 0);
            });
        }

        $hasCollegeDimension = Schema::hasTable('colleges')
            && Schema::hasColumn('courses', 'college_id')
            && Schema::hasColumn('colleges', 'name');

        if ($hasCollegeDimension) {
            $query->leftJoin('colleges as sm_colleges', 'sm_colleges.id', '=', 'sm_courses.college_id');
        }

        $this->applySlotMonitoringCollegeRestriction($query, $hasCollegeDimension);

        return $query;
    }

    private function applySlotMonitoringCollegeRestriction($query, $hasCollegeDimension)
    {
        if ($hasCollegeDimension) {
            $query->where(function ($builder) {
                $builder->whereIn(
                    DB::raw('UPPER(TRIM(COALESCE(sm_colleges.name, "")))'),
                    self::SLOT_MONITORING_ALLOWED_COLLEGE_NAMES
                )->orWhereIn(
                    DB::raw('UPPER(TRIM(COALESCE(sm_departments.description, "")))'),
                    self::SLOT_MONITORING_ALLOWED_DEPARTMENT_NAMES
                );
            });

            return;
        }

        $query->whereIn(
            DB::raw('UPPER(TRIM(COALESCE(sm_departments.description, "")))'),
            self::SLOT_MONITORING_ALLOWED_DEPARTMENT_NAMES
        );
    }

    private function mapSlotMonitoringSubjectRow($row)
    {
        $totalSlots = (int) ($row->total_slots ?? 0);
        if ($totalSlots < 1) {
            $totalSlots = 50;
        }

        $enrolledSlots = (int) ($row->enrolled_slots ?? 0);
        if ($enrolledSlots < 0) {
            $enrolledSlots = 0;
        }

        $utilizationPct = 0;
        if ($totalSlots > 0) {
            $utilizationPct = (int) round(($enrolledSlots / $totalSlots) * 100);
        }

        if ($utilizationPct < 0) {
            $utilizationPct = 0;
        }

        if ($utilizationPct > 100) {
            $utilizationPct = 100;
        }

        $statusLabel = 'Open';
        if ($enrolledSlots <= 0) {
            $statusLabel = 'Dissolved';
        } elseif ($enrolledSlots >= $totalSlots) {
            $statusLabel = 'Closed';
        } elseif ($utilizationPct >= 90) {
            $statusLabel = 'Critical';
        } elseif ($utilizationPct >= 50) {
            $statusLabel = 'Warning';
        }

        $semesterLabel = $this->normalizeSlotMonitoringSemester((string) ($row->semester_label ?? ''));
        if ($semesterLabel === '') {
            $semesterLabel = trim((string) ($row->semester_label ?? ''));
        }

        $subjectCode = trim((string) ($row->subject_code ?? ''));
        $subjectName = trim((string) ($row->subject_name ?? ''));
        $subjectLabel = $subjectCode !== '' ? $subjectCode : $subjectName;
        $facultyName = trim((string) ($row->faculty_name ?? ''));

        return [
            'id' => (int) ($row->id ?? 0),
            'school_year' => trim((string) ($row->school_year ?? '')),
            'semester' => $semesterLabel,
            'course_id' => (int) ($row->course_id ?? 0),
            'course_code' => trim((string) ($row->course_code ?? '')),
            'course_name' => trim((string) ($row->course_name ?? '')),
            'section' => trim((string) ($row->section ?? '')),
            'subject' => $subjectLabel,
            'faculty_name' => $facultyName,
            'schedule' => $this->formatSlotMonitoringSchedule(
                (string) ($row->days ?? ''),
                (string) ($row->time_start ?? ''),
                (string) ($row->time_end ?? ''),
                (string) ($row->room ?? '')
            ),
            'total_slots' => $totalSlots,
            'enrolled_slots' => $enrolledSlots,
            'utilization_pct' => $utilizationPct,
            'status_label' => $statusLabel,
        ];
    }

    private function formatSlotMonitoringSchedule($days, $timeStart, $timeEnd, $room)
    {
        $parts = [];

        $dayLabel = trim((string) $days);
        if ($dayLabel !== '') {
            $parts[] = strtoupper($dayLabel);
        }

        $start = trim((string) $timeStart);
        $end = trim((string) $timeEnd);
        if ($start !== '' || $end !== '') {
            if ($start !== '' && $end !== '') {
                $parts[] = $start . '-' . $end;
            } else {
                $parts[] = $start !== '' ? $start : $end;
            }
        }

        $roomLabel = trim((string) $room);
        if ($roomLabel !== '') {
            $parts[] = 'Room#' . $roomLabel;
        }

        if (empty($parts)) {
            return '-';
        }

        return implode(' | ', $parts);
    }

    private function normalizeSlotMonitoringSemester(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'summer') !== false) {
            return 'Summer';
        }

        if (strpos($normalized, 'second') !== false || strpos($normalized, '2nd') !== false || $normalized === '2') {
            return 'Second';
        }

        if (strpos($normalized, 'first') !== false || strpos($normalized, '1st') !== false || $normalized === '1') {
            return 'First';
        }

        return '';
    }

    private function slotMonitoringSemesterAliases(string $canonicalLabel): array
    {
        if ($canonicalLabel === 'First') {
            return ['First', '1st Semester', 'First Semester'];
        }

        if ($canonicalLabel === 'Second') {
            return ['Second', '2nd Semester', 'Second Semester'];
        }

        if ($canonicalLabel === 'Summer') {
            return ['Summer', 'Summer Semester'];
        }

        return [$canonicalLabel];
    }

    private function slotMonitoringSemesterWeight(string $canonicalLabel): int
    {
        if ($canonicalLabel === 'First') {
            return 1;
        }

        if ($canonicalLabel === 'Second') {
            return 2;
        }

        if ($canonicalLabel === 'Summer') {
            return 3;
        }

        return 4;
    }

    private function slotMonitoringReportTitle(string $reportType): string
    {
        if ($reportType === 'actual-size') {
            return 'ACTUAL SIZE';
        }

        if ($reportType === 'under-20') {
            return 'List of Subject(s) with 20 and below student(s) enrolled';
        }

        if ($reportType === 'dissolved') {
            return 'List of Dissolved Subjects';
        }

        return 'List of Closed Subjects';
    }

    private function mapSlotMonitoringRow(SlotMonitoring $slotMonitoring)
    {
        $totalSlots = (int) $slotMonitoring->total_slots;
        $enrolledSlots = (int) $slotMonitoring->enrolled_slots;
        $utilizationPct = 0;

        if ($totalSlots > 0) {
            $utilizationPct = (int) round(($enrolledSlots / $totalSlots) * 100);
        }

        if ($utilizationPct < 0) {
            $utilizationPct = 0;
        }

        if ($utilizationPct > 100) {
            $utilizationPct = 100;
        }

        $statusLabel = 'Open';
        if ($utilizationPct >= 90) {
            $statusLabel = 'Critical';
        } elseif ($utilizationPct >= 50) {
            $statusLabel = 'Warning';
        }

        return [
            'id' => (int) $slotMonitoring->id,
            'school_year' => (string) $slotMonitoring->school_year,
            'semester' => (string) $slotMonitoring->semester,
            'course_id' => (int) $slotMonitoring->course_id,
            'course_code' => $slotMonitoring->course ? (string) $slotMonitoring->course->code : '',
            'course_name' => $slotMonitoring->course ? (string) $slotMonitoring->course->name : '',
            'section' => (string) $slotMonitoring->section,
            'subject' => (string) $slotMonitoring->subject,
            'schedule' => (string) $slotMonitoring->schedule,
            'total_slots' => $totalSlots,
            'enrolled_slots' => $enrolledSlots,
            'utilization_pct' => $utilizationPct,
            'status_label' => $statusLabel,
            'updated_by' => $slotMonitoring->updatedBy ? (string) $slotMonitoring->updatedBy->name : 'System',
            'updated_at' => $slotMonitoring->updated_at ? $slotMonitoring->updated_at->toDateTimeString() : null,
        ];
    }

    private function resolveSlotMonitoringPage($page)
    {
        $safePage = (int) $page;

        if ($safePage < 1) {
            return 1;
        }

        return $safePage;
    }

    private function resolveSlotMonitoringPerPage($perPage)
    {
        $safePerPage = (int) $perPage;

        if ($safePerPage < self::SLOT_MONITORING_MIN_PER_PAGE) {
            return self::SLOT_MONITORING_MIN_PER_PAGE;
        }

        if ($safePerPage > self::SLOT_MONITORING_MAX_PER_PAGE) {
            return self::SLOT_MONITORING_MAX_PER_PAGE;
        }

        return $safePerPage;
    }

    private function isDuplicateSlotMonitoringConstraint(QueryException $exception)
    {
        if ((string) $exception->getCode() !== '23000') {
            return false;
        }

        $message = $exception->getMessage();

        return strpos($message, 'slot_monitorings_unique') !== false
            || strpos($message, 'Duplicate entry') !== false;
    }

    /**
     * Registrar > Scheduling > Section Merging
     */
    public function sectionMerging()
    {
        return view('registrar.registrar-menu.scheduling.section-merging');
    }

    public function sectionMergingData(Request $request): JsonResponse
    {
        $requestedSchoolYear = trim((string) $request->query('school_year', ''));
        $requestedSemester = trim((string) $request->query('semester', ''));

        $semesterOrderSql = "FIELD(semester, 'First', 'Second', 'Summer')";

        $configRows = SlotMonitoring::query()
            ->select([
                'school_year',
                'semester',
                DB::raw('COUNT(*) as row_count'),
            ])
            ->whereIn('semester', self::SLOT_MONITORING_ALLOWED_SEMESTERS)
            ->whereNotNull('school_year')
            ->where('school_year', '<>', '')
            ->groupBy('school_year', 'semester')
            ->orderBy('school_year', 'desc')
            ->orderByRaw($semesterOrderSql)
            ->get();

        $schoolYears = [];
        $semesterMap = [];

        foreach ($configRows as $configRow) {
            $year = trim((string) $configRow->school_year);
            $semester = trim((string) $configRow->semester);

            if ($year === '' || $semester === '') {
                continue;
            }

            if (!array_key_exists($year, $semesterMap)) {
                $semesterMap[$year] = [];
                $schoolYears[] = $year;
            }

            if (!in_array($semester, $semesterMap[$year], true)) {
                $semesterMap[$year][] = $semester;
            }
        }

        $schoolYear = $requestedSchoolYear;
        if ($schoolYear === '' || !in_array($schoolYear, $schoolYears, true)) {
            $schoolYear = !empty($schoolYears) ? (string) $schoolYears[0] : $schoolYear;
        }

        $semestersForYear = array_key_exists($schoolYear, $semesterMap)
            ? $semesterMap[$schoolYear]
            : self::SLOT_MONITORING_ALLOWED_SEMESTERS;

        $semester = $requestedSemester;
        if ($semester === '' || !in_array($semester, $semestersForYear, true)) {
            $semester = !empty($semestersForYear) ? (string) $semestersForYear[0] : '';
        }

        $rowsQuery = SlotMonitoring::query()
            ->select([
                'id',
                'school_year',
                'semester',
                'course_id',
                'section',
                'subject',
                'schedule',
                'total_slots',
                'enrolled_slots',
            ])
            ->with(['course:id,code,name']);

        if ($schoolYear !== '') {
            $rowsQuery->where('school_year', $schoolYear);
        }

        if ($semester !== '') {
            $rowsQuery->where('semester', $semester);
        }

        $rows = $rowsQuery
            ->orderBy('section')
            ->orderBy('subject')
            ->orderBy('id')
            ->get();

        $mappedRows = $rows
            ->map(function (SlotMonitoring $slotMonitoring) {
                return $this->mapSectionMergingRow($slotMonitoring);
            })
            ->values();

        $courses = $rows
            ->map(function (SlotMonitoring $slotMonitoring) {
                if (!$slotMonitoring->course) {
                    return null;
                }

                $courseCode = trim((string) $slotMonitoring->course->code);
                $courseName = trim((string) $slotMonitoring->course->name);
                $label = $courseCode;

                if ($label === '') {
                    $label = $courseName;
                } elseif ($courseName !== '') {
                    $label = $label . ' - ' . $courseName;
                }

                return [
                    'id' => (int) $slotMonitoring->course->id,
                    'code' => $courseCode,
                    'name' => $courseName,
                    'label' => $label,
                ];
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->unique('id')
            ->sortBy('code')
            ->values();

        $yearLevels = $mappedRows
            ->pluck('year_level')
            ->filter(function ($yearLevel) {
                return is_int($yearLevel) && $yearLevel > 0;
            })
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'ok' => true,
            'rows' => $mappedRows,
            'options' => [
                'courses' => $courses,
                'year_levels' => $yearLevels,
                'config' => [
                    'school_years' => array_values($schoolYears),
                    'semester_map' => $semesterMap,
                    'selected_school_year' => (string) $schoolYear,
                    'selected_semester' => (string) $semester,
                ],
            ],
            'meta' => [
                'total' => (int) $mappedRows->count(),
            ],
        ]);
    }

    public function storeSectionMerging(StoreSectionMergingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $sourceSlotId = (int) $validated['source_slot_monitoring_id'];
        $targetSlotId = (int) $validated['target_slot_monitoring_id'];
        $schoolYear = (string) $validated['school_year'];
        $semester = (string) $validated['semester'];

        $source = null;
        $target = null;
        $operation = null;

        DB::beginTransaction();
        try {
            $source = SlotMonitoring::query()
                ->where('id', $sourceSlotId)
                ->lockForUpdate()
                ->first();

            $target = SlotMonitoring::query()
                ->where('id', $targetSlotId)
                ->lockForUpdate()
                ->first();

            if (!$source || !$target) {
                DB::rollBack();

                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'source_slot_monitoring_id' => ['One of the selected sections no longer exists.'],
                    ],
                ], 422);
            }

            $consistencyError = $this->resolveSectionMergingConsistencyError($source, $target, $schoolYear, $semester);
            if ($consistencyError !== null) {
                DB::rollBack();

                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $consistencyError,
                ], 422);
            }

            $sourceTotalSlots = (int) $source->total_slots;
            $sourceEnrolledSlots = (int) $source->enrolled_slots;
            $targetTotalBefore = (int) $target->total_slots;
            $targetEnrolledBefore = (int) $target->enrolled_slots;

            $targetTotalAfter = $targetTotalBefore + $sourceTotalSlots;
            $targetEnrolledAfter = $targetEnrolledBefore + $sourceEnrolledSlots;

            $target->update([
                'total_slots' => $targetTotalAfter,
                'enrolled_slots' => $targetEnrolledAfter,
                'updated_by_user_id' => auth()->id(),
            ]);

            $source->update([
                'total_slots' => 0,
                'enrolled_slots' => 0,
                'updated_by_user_id' => auth()->id(),
            ]);

            $operation = SectionMergingOperation::query()->create([
                'school_year' => $schoolYear,
                'semester' => $semester,
                'source_slot_monitoring_id' => (int) $source->id,
                'target_slot_monitoring_id' => (int) $target->id,
                'source_course_id' => (int) $source->course_id,
                'target_course_id' => (int) $target->course_id,
                'source_section' => (string) $source->section,
                'target_section' => (string) $target->section,
                'source_subject' => (string) $source->subject,
                'target_subject' => (string) $target->subject,
                'source_total_slots' => $sourceTotalSlots,
                'source_enrolled_slots' => $sourceEnrolledSlots,
                'target_total_slots_before' => $targetTotalBefore,
                'target_enrolled_slots_before' => $targetEnrolledBefore,
                'target_total_slots_after' => $targetTotalAfter,
                'target_enrolled_slots_after' => $targetEnrolledAfter,
                'merge_status' => 'completed',
                'merged_by_user_id' => auth()->id(),
                'merged_at' => Carbon::now(),
            ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $source->load(['course:id,code,name']);
        $target->load(['course:id,code,name']);

        return response()->json([
            'ok' => true,
            'operation_id' => $operation ? (int) $operation->id : null,
            'message' => 'Sections merged successfully.',
            'merged_student_count' => $operation ? (int) $operation->source_enrolled_slots : 0,
            'source_row' => $this->mapSectionMergingRow($source),
            'target_row' => $this->mapSectionMergingRow($target),
        ]);
    }

    private function mapSectionMergingRow(SlotMonitoring $slotMonitoring)
    {
        $courseCode = '';
        $courseName = '';

        if ($slotMonitoring->course) {
            $courseCode = trim((string) $slotMonitoring->course->code);
            $courseName = trim((string) $slotMonitoring->course->name);
        }

        $courseLabel = $courseCode;
        if ($courseLabel === '') {
            $courseLabel = $courseName;
        } elseif ($courseName !== '') {
            $courseLabel = $courseLabel . ' - ' . $courseName;
        }

        $yearLevel = $this->extractYearLevelFromSectionLabel((string) $slotMonitoring->section);

        return [
            'id' => (int) $slotMonitoring->id,
            'school_year' => (string) $slotMonitoring->school_year,
            'semester' => (string) $slotMonitoring->semester,
            'course_id' => (int) $slotMonitoring->course_id,
            'course_code' => $courseCode,
            'course_name' => $courseName,
            'course_label' => $courseLabel,
            'year_level' => $yearLevel,
            'section' => (string) $slotMonitoring->section,
            'subject' => (string) $slotMonitoring->subject,
            'schedule' => (string) $slotMonitoring->schedule,
            'total_slots' => (int) $slotMonitoring->total_slots,
            'enrolled_slots' => (int) $slotMonitoring->enrolled_slots,
            'source_available' => (int) $slotMonitoring->enrolled_slots > 0,
        ];
    }

    private function resolveSectionMergingConsistencyError(SlotMonitoring $source, SlotMonitoring $target, $schoolYear, $semester)
    {
        if ((string) $source->school_year !== $schoolYear || (string) $target->school_year !== $schoolYear) {
            return [
                'school_year' => ['Selected sections do not match the chosen school year.'],
            ];
        }

        if ((string) $source->semester !== $semester || (string) $target->semester !== $semester) {
            return [
                'semester' => ['Selected sections do not match the chosen semester.'],
            ];
        }

        if ((int) $source->course_id !== (int) $target->course_id) {
            return [
                'target_slot_monitoring_id' => ['Source and target sections must belong to the same program.'],
            ];
        }

        if (strcasecmp(trim((string) $source->subject), trim((string) $target->subject)) !== 0) {
            return [
                'target_slot_monitoring_id' => ['Source and target sections must have the same subject before merging.'],
            ];
        }

        if ((int) $source->enrolled_slots <= 0) {
            return [
                'source_slot_monitoring_id' => ['Source section has no enrolled students to merge.'],
            ];
        }

        return null;
    }

    private function extractYearLevelFromSectionLabel($section)
    {
        $normalized = strtolower(trim((string) $section));
        if ($normalized === '') {
            return null;
        }

        $wordToLevel = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
            'sixth' => 6,
        ];

        foreach ($wordToLevel as $word => $value) {
            if (strpos($normalized, $word) !== false) {
                return $value;
            }
        }

        if (preg_match('/(?:^|\s)([1-9])(?:\s*[-]|\b)/', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Registrar > Student Management > Student Enrollment
     */
    public function studentEnrollment()
    {
        return view('registrar.registrar-menu.student-management.student-enrollment');
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'student_no' => 'required|unique:students,student_no',
            'name' => 'required|string',
            'sex' => 'nullable|string',
            'age' => 'nullable|integer',
            'college' => 'nullable|string',
            'program' => 'nullable|string',
            'curriculum' => 'nullable|string',
            'year_level' => 'nullable|string',
            'scholarship' => 'nullable|string',
            'school_year' => 'required|string',
            'semester' => 'required|string',
        ]);

        $defaultPass = null;

        DB::transaction(function () use ($request, &$defaultPass) {
            $student = Student::create($request->only([
                'student_no',
                'name',
                'sex',
                'age',
                'college',
                'program',
                'curriculum',
                'year_level',
                'scholarship',
                'school_year',
                'semester',
            ]));

            $defaultPass = 'PLP-' . $student->student_no;

            User::create([
                'name' => $student->name,
                'username' => $student->student_no,
                'password' => Hash::make($defaultPass),
                'module' => 'student',
                'force_password_reset' => true,
                'student_id' => $student->id,
            ]);
        });

        return redirect()->route('registrar.registrar-menu.student-mgmt.student-enrollment')
            ->with('success', 'Student profile created!')
            ->with('success_password', $defaultPass);
    }

    /**
     * Registrar > Faculty Management > Grading Sheet
     */
    public function facultyCreate()
    {
        return view('registrar.registrar-menu.faculty-management.faculty-create');
    }

    public function storeFaculty(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:faculties,code',
            'name' => 'required|string',
        ]);

        $defaultPass = null;

        DB::transaction(function () use ($request, &$defaultPass) {
            $faculty = Faculty::create($request->only(['code', 'name']));

            $defaultPass = 'PLP-' . $faculty->code;

            User::create([
                'name' => $faculty->name,
                'username' => $faculty->code,
                'password' => Hash::make($defaultPass),
                'module' => 'faculty',
                'force_password_reset' => true,
                'faculty_id' => $faculty->id,
            ]);
        });

        return redirect()->route('registrar.registrar-menu.faculty-mgmt.faculty-create')
            ->with('success', 'Faculty profile created!')
            ->with('success_password', $defaultPass);
    }

    public function gradingSheet()
    {
        $subjects = Subject::query()
            ->with([
                'students' => function ($query) {
                    $query->select(
                        'students.id',
                        'students.student_no',
                        'students.name',
                        'students.course_id',
                        'students.year_block_id'
                    );
                },
                'studentGrades',
                'facultyModel:id,name',
            ])
            ->orderBy('year_section')
            ->orderBy('code')
            ->get();

        $gradingSections = $subjects->map(function ($subject) {
            $gradeMap = $subject->studentGrades->keyBy('student_id');

            $students = $subject->students->map(function ($student) use ($gradeMap) {
                $grade = $gradeMap->get($student->id);

                return [
                    'id' => (int) $student->id,
                    'studentNo' => (string) $student->student_no,
                    'name' => (string) $student->name,
                    'fda' => false,
                    'na' => false,
                    'prelim' => $grade && $grade->prelim !== null ? (float) $grade->prelim : null,
                    'midterm' => $grade && $grade->midterm !== null ? (float) $grade->midterm : null,
                    'final' => $grade && $grade->final !== null ? (float) $grade->final : null,
                    'cRating' => $grade && $grade->final_average !== null ? (float) $grade->final_average : null,
                    'fRating' => $grade && $grade->final_average !== null ? (float) $grade->final_average : null,
                    'remarks' => $grade ? (string) $grade->remarks : '',
                ];
            })->values()->all();

            $midtermPostedAt = $subject->studentGrades
                ->filter(function ($grade) {
                    return $grade->midterm !== null;
                })
                ->max('updated_at');

            $finalPostedAt = $subject->studentGrades
                ->filter(function ($grade) {
                    return $grade->final !== null;
                })
                ->max('updated_at');

            return [
                'id' => (int) $subject->id,
                'section' => trim((string) ($subject->year_section ?: '-')),
                'courseCode' => (string) ($subject->code ?: '-'),
                'description' => (string) ($subject->name ?: '-'),
                'faculty' => (string) (optional($subject->facultyModel)->name ?: ($subject->faculty ?: '-')),
                'midterm' => $midtermPostedAt ? Carbon::parse($midtermPostedAt)->format('m/d/Y') : '-',
                'final' => $finalPostedAt ? Carbon::parse($finalPostedAt)->format('m/d/Y') : '-',
                'approvedBy' => 'Registrar',
                'courseFull' => (string) ($subject->name ?: '-'),
                'schedule' => 'Room No. : ' . (string) ($subject->room ?: 'TBA'),
                'schoolYear' => (string) ($subject->school_year ?: ''),
                'term' => (string) ($subject->semester ?: ''),
                'status' => (string) ($subject->grading_status ?: ''),
                'students' => $students,
            ];
        })->values()->all();

        return view('registrar.registrar-menu.faculty-management.grading-sheet', [
            'gradingSections' => $gradingSections,
        ]);
    }

    public function gradingSheetUpdatePhase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'phase' => ['required', Rule::in(['midterm', 'final'])],
            'grades' => 'required|array',
        ]);

        $phase = (string) $validated['phase'];
        $subject = Subject::query()->with('students')->findOrFail((int) $validated['subject_id']);
        $studentIds = $subject->students->pluck('id')->all();
        $gradesPayload = (array) $validated['grades'];

        foreach ($gradesPayload as $studentId => $rawGrade) {
            if (!in_array((int) $studentId, $studentIds, true)) {
                continue;
            }

            if ($rawGrade === null || $rawGrade === '') {
                continue;
            }

            $gradeValue = (float) $rawGrade;
            if ($gradeValue < 1 || $gradeValue > 5) {
                continue;
            }

            $gradeRow = StudentSubjectGrade::firstOrNew([
                'subject_id' => $subject->id,
                'student_id' => (int) $studentId,
            ]);

            $gradeRow->{$phase} = $gradeValue;
            $gradeRow->final_average = $this->computeRegistrarAverage(
                $gradeRow->prelim,
                $gradeRow->midterm,
                $gradeRow->final
            );
            $gradeRow->remarks = $gradeRow->final_average !== null && (float) $gradeRow->final_average <= 3.00
                ? 'Passed'
                : ($gradeRow->final_average !== null ? 'Failed' : null);
            $gradeRow->save();

            $student = $subject->students->firstWhere('id', (int) $studentId);
            if ($student) {
                $this->syncStudentGradeSnapshot($student);
            }
        }

        $subject->grading_status = 'Submitted';
        $subject->save();

        $updatedRows = StudentSubjectGrade::query()
            ->where('subject_id', $subject->id)
            ->get()
            ->map(function ($row) {
                return [
                    'student_id' => (int) $row->student_id,
                    'prelim' => $row->prelim !== null ? (float) $row->prelim : null,
                    'midterm' => $row->midterm !== null ? (float) $row->midterm : null,
                    'final' => $row->final !== null ? (float) $row->final : null,
                    'final_average' => $row->final_average !== null ? (float) $row->final_average : null,
                    'remarks' => (string) ($row->remarks ?: ''),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'subject_id' => (int) $subject->id,
            'phase' => $phase,
            'posted_at' => Carbon::now()->format('m/d/Y'),
            'updated_rows' => $updatedRows,
        ]);
    }

    private function computeRegistrarAverage($prelim, $midterm, $final)
    {
        $grades = collect([$prelim, $midterm, $final])
            ->filter(function ($value) {
                return $value !== null && $value !== '';
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();

        if ($grades->count() === 0) {
            return null;
        }

        return round($grades->avg(), 2);
    }

    private function syncStudentGradeSnapshot(Student $student): void
    {
        if (!Schema::hasTable('master_student_grade_files')) {
            return;
        }

        MasterStudentGradeFile::updateOrCreate(
            ['student_no' => (string) $student->student_no],
            [
                'source_student_id' => $student->id,
                'student_name' => (string) $student->name,
                'course' => (string) ($student->program ?: ''),
                'year_level' => (string) ($student->year_level ?: ''),
                'snapshot_taken_at' => now(),
                'is_snapshot' => true,
            ]
        );
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
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id', 'academic_term_id']);

        $alumniRows = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'studentNo' => (string) $student->student_no,
                'studentName' => (string) $student->name,
                'program' => (string) ($student->program ?: '-'),
                'yearLevel' => (string) ($student->year_level ?: '-'),
                'schoolYear' => (string) ($student->school_year ?: ''),
                'term' => (string) ($student->semester ?: ''),
            ];
        })->values()->all();

        $alumniPrograms = collect($alumniRows)
            ->pluck('program')
            ->filter(function ($value) {
                return trim((string) $value) !== '' && $value !== '-';
            })
            ->unique()
            ->values()
            ->all();

        $alumniYearLevels = collect($alumniRows)
            ->pluck('yearLevel')
            ->filter(function ($value) {
                return trim((string) $value) !== '' && $value !== '-';
            })
            ->unique()
            ->values()
            ->all();

        $setting = null;
        if (Schema::hasTable('alumni_tracker_settings')) {
            $setting = AlumniTrackerSetting::query()->latest('id')->first();
        }

        $alumniSchoolYears = collect($alumniRows)
            ->pluck('schoolYear')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $alumniTerms = collect($alumniRows)
            ->pluck('term')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $alumniConfig = [
            'schoolYear' => $setting ? (string) $setting->school_year : (string) ($students->first()->school_year ?? '2025-2026'),
            'term' => $setting ? (string) $setting->term : (string) ($students->first()->semester ?? 'Second'),
        ];

        if (!in_array($alumniConfig['schoolYear'], $alumniSchoolYears, true)) {
            $alumniSchoolYears[] = $alumniConfig['schoolYear'];
        }
        if (!in_array($alumniConfig['term'], $alumniTerms, true)) {
            $alumniTerms[] = $alumniConfig['term'];
        }

        if (!count($alumniSchoolYears)) {
            $alumniSchoolYears = ['2025-2026'];
        }
        if (!count($alumniTerms)) {
            $alumniTerms = ['First', 'Second', 'Summer'];
        }

        return view('registrar.registrar-menu.alumni-tracker', compact('alumniRows', 'alumniPrograms', 'alumniYearLevels', 'alumniConfig', 'alumniSchoolYears', 'alumniTerms'));
    }

    public function alumniTrackerSaveConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'term' => 'required|string|max:30',
        ]);

        if (Schema::hasTable('alumni_tracker_settings')) {
            $setting = AlumniTrackerSetting::query()->latest('id')->first();
            if ($setting) {
                $setting->update([
                    'school_year' => $validated['school_year'],
                    'term' => $validated['term'],
                ]);
            } else {
                AlumniTrackerSetting::create([
                    'school_year' => $validated['school_year'],
                    'term' => $validated['term'],
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Placeholder
     */
    public function formsPlaceholder()
    {
        return view('registrar.forms.placeholder');
    }

    /**
     * Registrar > Forms > TOR
     */
    public function formsTor()
    {
        return view('registrar.forms.tor');
    }

    /**
     * Registrar > Forms > Application for Leave of Absence - Enrolled
     */
    public function formsApplicationLeaveAbsenceEnrolled(Request $request)
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id', 'academic_term_id']);

        $selectedStudentId = (int) $request->query('student_id', 0);
        if ($selectedStudentId <= 0 && $students->isNotEmpty()) {
            $selectedStudentId = (int) $students->first()->id;
        }

        $student = null;
        if ($selectedStudentId > 0) {
            $student = Student::find($selectedStudentId);
        }

        $gradeRows = collect();
        if ($student) {
            $gradeRows = StudentSubjectGrade::query()
                ->with(['subject.facultyModel'])
                ->where('student_id', $student->id)
                ->orderBy('subject_id')
                ->get()
                ->map(function ($grade) use ($student) {
                    $subject = $grade->subject;

                    $section = $subject && $subject->year_section
                        ? (string) $subject->year_section
                        : trim(($student->program ?: '') . ' ' . ($student->year_level ?: ''));

                    $semestralGradeRemarks = '';
                    if ($grade->final_average !== null) {
                        $semestralGradeRemarks = (string) $grade->final_average;
                    }
                    if (!empty($grade->remarks)) {
                        $semestralGradeRemarks = trim($semestralGradeRemarks . ' ' . (string) $grade->remarks);
                    }

                    $professorName = '';
                    if ($subject) {
                        if (!empty($subject->faculty)) {
                            $professorName = (string) $subject->faculty;
                        } elseif ($subject->relationLoaded('facultyModel') && $subject->facultyModel) {
                            $professorName = (string) $subject->facultyModel->name;
                        }
                    }

                    return [
                        'course_code' => $subject ? (string) $subject->code : '',
                        'course_description' => $subject ? (string) $subject->name : '',
                        'section' => $section,
                        'midterm_grade' => $grade->midterm !== null ? (string) $grade->midterm : '',
                        'final_grade' => $grade->final !== null ? (string) $grade->final : '',
                        'semestral_grade_remarks' => $semestralGradeRemarks,
                        'professor_name_signature' => $professorName,
                    ];
                })
                ->values();
        }

        return view('registrar.forms.application-leave-absence-enrolled', [
            'students' => $students,
            'selectedStudentId' => $selectedStudentId,
            'student' => $student,
            'gradeRows' => $gradeRows,
            'applicationDate' => Carbon::now()->format('F d, Y'),
        ]);
    }

    /**
     * Registrar > Forms > Diploma
     */
    public function formsDiploma()
    {
        return view('registrar.forms.diploma');
    }

    /**
     * Registrar > Forms > Graduation Clearance
     */
    public function formsGraduationClearance()
    {
        return view('registrar.forms.graduation-clearance');
    }

    /**
     * Registrar > Forms > Honorable Dismissal
     */
    public function formsHonorableDismissal()
    {
        return view('registrar.forms.honorable-dismissal');
    }

    /**
     * Registrar > Forms > Official Grade Report
     */
    public function formsOfficialGradeReport()
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id', 'academic_term_id']);

        $gradesByStudent = StudentSubjectGrade::query()
            ->with('subject')
            ->whereIn('student_id', $students->pluck('id')->all())
            ->orderBy('student_id')
            ->orderBy('subject_id')
            ->get()
            ->groupBy('student_id');

        $gradeReportRows = [];
        $subjectsByRow = [];
        $metaByRow = [];

        foreach ($students as $index => $student) {
            $rowId = (string) ($index + 1);
            $section = trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR'));
            $gradeReportRows[] = [
                'row_id' => $rowId,
                'student_id' => $student->id,
                'student_no' => $student->student_no,
                'student_name' => $student->name,
                'program' => $student->program ?: '-',
                'year' => $student->year_level ?: '-',
                'section' => $section,
            ];

            $subjects = ($gradesByStudent->get($student->id) ?: collect())->map(function ($grade) use ($section) {
                $subject = $grade->subject;
                return [
                    'code' => $subject ? (string) $subject->code : '-',
                    'desc' => $subject ? (string) $subject->name : '-',
                    'section' => $subject && $subject->year_section ? (string) $subject->year_section : $section,
                    'prof' => 'TBA',
                    'grade' => $grade->final_average !== null ? (string) $grade->final_average : '-',
                    'remarks' => $grade->remarks ?: '-',
                    'reexam' => '',
                    'units' => $subject && $subject->units !== null ? number_format((float) $subject->units, 2) : '0.00',
                ];
            })->values()->all();

            $subjectsByRow[$rowId] = $subjects;
            $metaByRow[$rowId] = [
                'studentNo' => $student->student_no,
                'studentName' => strtoupper((string) $student->name),
                'address' => '-',
                'birthday' => '-',
                'section' => $section,
                'course' => ($student->program ?: 'PROGRAM') . ' : ' . ($student->program ?: 'Program'),
                'schoolYear' => (string) ($student->school_year ?: '2025-2026') . ' / ' . strtoupper((string) ($student->semester ?: 'First')),
                'curriculum' => 'CURRENT',
                'studentType' => 'REGULAR',
                'yearLevel' => $student->year_level ?: '-',
                'residency' => 'PR',
                'cwa' => '-',
            ];
        }

        return view('registrar.forms.official-grade-report', compact('gradeReportRows', 'subjectsByRow', 'metaByRow'));
    }

    public function formsOfficialGradeReportData(Student $student): JsonResponse
    {
        $records = StudentSubjectGrade::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->orderBy('subject_id')
            ->get();

        $subjects = $records->map(function ($grade) {
            $subject = $grade->subject;
            return [
                'code' => $subject ? (string) $subject->code : '-',
                'desc' => $subject ? (string) $subject->name : '-',
                'section' => $subject && $subject->year_section ? (string) $subject->year_section : '-',
                'prof' => 'TBA',
                'grade' => $grade->final_average !== null ? (string) $grade->final_average : '-',
                'remarks' => $grade->remarks ?: '-',
                'reexam' => '',
                'units' => $subject && $subject->units !== null ? number_format((float) $subject->units, 2) : '0.00',
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'student' => [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'program' => $student->program,
                'year_level' => $student->year_level,
            ],
            'subjects' => $subjects,
        ]);
    }

    /**
     * Registrar > Forms > Permission to Cross-Enroll
     */
    public function formsPermissionCrossEnroll()
    {
        $this->seedCrossEnrollRowsIfEmpty();

        $crossEnrollRows = CrossEnrollmentRequest::query()
            ->with('student')
            ->orderByDesc('id')
            ->get();

        return view('registrar.forms.permission-cross-enroll', compact('crossEnrollRows'));
    }

    public function formsPermissionCrossEnrollStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CrossEnrollmentRequest::create([
            'student_id' => $student->id,
            'school_year' => $student->school_year,
            'semester' => $student->semester,
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
            'status' => 'pending',
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function formsPermissionCrossEnrollUpdate(Request $request, CrossEnrollmentRequest $crossEnrollmentRequest): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
            'program' => 'nullable|string|max:80',
            'year_level' => 'nullable|string|max:40',
        ]);

        $student = $crossEnrollmentRequest->student;
        $student->student_no = trim($validated['student_no']);
        $student->name = trim($validated['name']);
        $student->program = isset($validated['program']) ? trim((string) $validated['program']) : $student->program;
        $student->year_level = isset($validated['year_level']) ? trim((string) $validated['year_level']) : $student->year_level;
        $student->save();

        $crossEnrollmentRequest->update([
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
        ]);

        return response()->json(['ok' => true]);
    }

    public function formsPermissionCrossEnrollDestroy(CrossEnrollmentRequest $crossEnrollmentRequest): JsonResponse
    {
        $crossEnrollmentRequest->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Waiver for Cancellation of Enrollment
     */
    public function formsWaiverCancellation()
    {
        $this->seedWaiverRowsIfEmpty();

        $waiverRows = CancellationWaiver::query()
            ->with('student')
            ->orderByDesc('id')
            ->get();

        return view('registrar.forms.waiver-cancellation', compact('waiverRows'));
    }

    public function formsWaiverCancellationStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CancellationWaiver::create([
            'student_id' => $student->id,
            'school_year' => $student->school_year,
            'semester' => $student->semester,
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
            'status' => 'pending',
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function formsWaiverCancellationUpdate(Request $request, CancellationWaiver $cancellationWaiver): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
            'program' => 'nullable|string|max:80',
            'year_level' => 'nullable|string|max:40',
        ]);

        $student = $cancellationWaiver->student;
        $student->student_no = trim($validated['student_no']);
        $student->name = trim($validated['name']);
        $student->program = isset($validated['program']) ? trim((string) $validated['program']) : $student->program;
        $student->year_level = isset($validated['year_level']) ? trim((string) $validated['year_level']) : $student->year_level;
        $student->save();

        $cancellationWaiver->update([
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
        ]);

        return response()->json(['ok' => true]);
    }

    public function formsWaiverCancellationDestroy(CancellationWaiver $cancellationWaiver): JsonResponse
    {
        $cancellationWaiver->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Certificate of GWA
     *
     * If a Student is provided (route-model binding) compute the GWA and pass it to the view.
     */
    public function formsCertificateGwa(?\App\Student $student = null)
    {
        $gwa = null;

        if ($student) {
            $grades = StudentSubjectGrade::with('subject')
                ->where('student_id', $student->id)
                ->get();

            $weightedSum = 0.0;
            $unitsSum = 0.0;
            $plainSum = 0.0;
            $plainCount = 0;

            foreach ($grades as $rec) {
                if ($rec->final_average === null) {
                    continue;
                }
                $avg = (float) $rec->final_average;
                $units = 0.0;
                if ($rec->relationLoaded('subject') && $rec->subject && isset($rec->subject->units) && is_numeric($rec->subject->units)) {
                    $units = (float) $rec->subject->units;
                }

                if ($units > 0) {
                    $weightedSum += $avg * $units;
                    $unitsSum += $units;
                } else {
                    $plainSum += $avg;
                    $plainCount++;
                }
            }

            if ($unitsSum > 0) {
                $gwa = round($weightedSum / $unitsSum, 2);
            } elseif ($plainCount > 0) {
                $gwa = round($plainSum / $plainCount, 2);
            } else {
                $gwa = null;
            }
        }

        return view('registrar.forms.certificates.certificate-gwa', compact('student', 'gwa'));
    }

    /**
     * Registrar > Forms > Form No. 8C-2 Certificate of Graduation
     */
    public function formsCertificateGraduation8c2()
    {
        return view('registrar.forms.certificates.certificate-graduation-8c2');
    }

    /**
     * Registrar > Forms > Form No. 8D-2 Certificate of Honor
     */
    public function formsCertificateHonor8d2()
    {
        return view('registrar.forms.certificates.certificate-honor-8d2');
    }

    /**
     * Registrar > Forms > Copy Of Grades (COG)
     */
    public function formsCopyOfGradesCog()
    {
        return view('registrar.forms.cog.copy-of-grades');
    }

    /**
     * Registrar > Forms > Certificate of Registration (COR)
     */
    public function formsCertificateOfRegistration(Request $request)
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label'])
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id']);

        $selectedStudentId = (int) $request->query('student_id', 0);

        if ($selectedStudentId <= 0 && $students->isNotEmpty()) {
            $selectedStudentId = (int) $students->first()->id;
        }

        $student = null;
        if ($selectedStudentId > 0) {
            $student = Student::with('subjects')->find($selectedStudentId);
        }

        $subjects = collect();
        if ($student) {
            $subjects = $student->subjects
                ->sortBy(function ($subject) {
                    return strtoupper((string) $subject->code);
                })
                ->values();
        }

        $totalUnits = (float) $subjects->sum(function ($subject) {
            return is_numeric($subject->units) ? (float) $subject->units : 0;
        });

        $assessment = $this->buildCorAssessment($subjects, $totalUnits);

        return view('registrar.forms.cor.certificate-of-registration', [
            'students' => $students,
            'selectedStudentId' => $selectedStudentId,
            'student' => $student,
            'subjects' => $subjects,
            'totalUnits' => $totalUnits,
            'assessment' => $assessment,
        ]);
    }

    /**
     * Registrar > Forms > Citizen's Charter
     */
    public function formsCitizensCharter()
    {
        return view('registrar.forms.citizens-charter', [
            'coverData' => [
                'institution' => 'PAMANTASAN NG LUNGSOD NG PASIG',
                'institution_sub' => '(University of Pasig City)',
                'document_title' => "CITIZEN'S CHARTER (ENGLISH)",
                'edition' => '2025 EDISYON',
                'office' => 'OFFICE OF THE UNIVERSITY REGISTRAR',
            ],
            'charterPages' => $this->citizensCharterPages(),
        ]);
    }

    /**
     * Registrar > Forms > Request Form for F 137A
     */
    public function formsRequestFormF137a()
    {
        return view('registrar.forms.request-form-f-137a');
    }

    private function citizensCharterPages(): array
    {
        return [
            [
                'type' => 'transaction',
                'title' => 'SUBMISSION OF ENTRANCE CREDENTIALS',
                'lead' => 'Successful admission qualifiers must submit entrance credentials to the Registrar\'s Office to be eligible for registration.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) ADMISSION QUALIFIERS (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => '2 PCS 2X2 PICTURE', 'where' => ''],
                    ['no' => '2', 'requirement' => 'REPORT CARD (GRADE 12) Form 138', 'where' => 'LAST SCHOOL ATTENDED'],
                    ['no' => '3', 'requirement' => 'PSA BIRTH CERTIFICATE (PHOTOCOPY)', 'where' => 'PSA'],
                    ['no' => '4', 'requirement' => '2 VALID ID OF PARENTS (PHOTOCOPY OF ANY OF THE FF:)', 'where' => ''],
                    ['no' => '', 'requirement' => 'DRIVER\'S LICENSE', 'where' => 'LTO'],
                    ['no' => '', 'requirement' => 'PASSPORT', 'where' => 'DFA'],
                    ['no' => '', 'requirement' => 'PRC LICENSE', 'where' => 'PRC'],
                    ['no' => '', 'requirement' => 'SSS ID', 'where' => 'SSS'],
                    ['no' => '', 'requirement' => 'GSIS UMID ID', 'where' => 'GSIS'],
                    ['no' => '', 'requirement' => 'VOTER\'S ID', 'where' => 'COMELEC'],
                    ['no' => '', 'requirement' => 'TAXPAYER\'S ID', 'where' => 'BIR'],
                    ['no' => '', 'requirement' => 'COMPANY ID', 'where' => 'REQUESTING PARTY\'S COMPANY'],
                    ['no' => '', 'requirement' => 'POSTAL ID', 'where' => 'PHILPOST'],
                ],
                'steps' => [
                    [
                        'no' => '1',
                        'client' => 'Student will submit all original docs & present photocopy to serve as receiving copy',
                        'office' => 'Stamp & return the photocopied docs to certify that the office has received the requirements',
                        'fees' => 'None',
                        'time' => '7 minutes',
                        'person' => 'Erran Gerald Pastorfide',
                    ],
                    [
                        'no' => '2',
                        'client' => '',
                        'office' => 'Issuance of Letter Request for Form 137/TCR and Enrolment Slip with Student No.',
                        'fees' => 'None',
                        'time' => '3 minutes',
                        'person' => 'Erran Gerald Pastorfide',
                    ],
                ],
                'totals' => ['fees' => 'None', 'time' => ''],
            ],
            [
                'type' => 'transaction',
                'title' => 'ENROLMENT OF NEW STUDENT',
                'lead' => 'Students have to register the courses they will enroll before the start of every semester to be officially enlisted in classes.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Enrollment slip issued upon submission of entrance credentials', 'where' => "PLP Registrar's Office"],
                ],
                'steps' => [
                    [
                        'no' => '1',
                        'client' => 'Proceed to respective colleges.',
                        'office' => '(1) Tagging of curriculum (2) Advising of subjects to be taken (3) Issuance of assessment slip.',
                        'fees' => 'None',
                        'time' => '5 minutes',
                        'person' => 'College Deans',
                    ],
                    [
                        'no' => '2',
                        'client' => "Proceed to Registrar's Office.",
                        'office' => '(1) Print and issue Certificate of Registration.',
                        'fees' => 'None',
                        'time' => '1 minute',
                        'person' => 'Erran Gerald Pastorfide',
                    ],
                ],
                'totals' => ['fees' => 'None', 'time' => '6 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'ENROLMENT OF OLD STUDENT',
                'lead' => 'Students have to register the courses they will enroll before the start of every semester to be officially enlisted in classes.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Grade Report (Previous Semester)', 'where' => 'Respective Colleges'],
                ],
                'steps' => [
                    [
                        'no' => '1',
                        'client' => 'Proceed to respective colleges.',
                        'office' => '(1) Screen students still eligible for enrollment (2) Advising of courses to be taken (3) Issuance of Assessment Slip.',
                        'fees' => 'None',
                        'time' => '5 minutes',
                        'person' => 'College Deans',
                    ],
                    [
                        'no' => '2',
                        'client' => 'Proceed to Finance Office for clearance (for students with balance only and AB Psychology students only).',
                        'office' => '(1) Collection of fees (2) Tagging of payment in UIS.',
                        'fees' => 'Varies',
                        'time' => '10 minutes',
                        'person' => 'Jenky Estayani',
                    ],
                    [
                        'no' => '3',
                        'client' => "Proceed to Registrar's Office for AB Psychology students.",
                        'office' => '(1) Print and issue Certificate of Registration.',
                        'fees' => 'None',
                        'time' => '1 minute',
                        'person' => 'Erran Gerald Pastorfide',
                    ],
                ],
                'totals' => ['fees' => 'Varies', 'time' => '16 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'REQUEST FOR EXIT CLEARANCE',
                'lead' => 'Students requesting credentials for transfer purposes need to secure exit clearance from key offices to ensure that students have no outstanding obligatios before they are issued credentials.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'School ID', 'where' => 'PLP Multimedia Office'],
                    ['no' => '2', 'requirement' => 'Validated withdrawal of enrollment form (currently enrolled only)', 'where' => "Window 1, Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Proceed to Window 1 to secure Exit Clearance Form.', 'office' => 'Issue Exit Clearance Form.', 'fees' => 'None', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Accomplish form and secure signatures of concerned offices and dean.', 'office' => 'Dean and administrative officers sign if student has no pending obligation.', 'fees' => 'None', 'time' => '30 minutes', 'person' => 'College Deans'],
                    ['no' => '3', 'client' => "Submit form to Office of the Registrar.", 'office' => 'Screen and receive accomplished form.', 'fees' => 'None', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                    ['no' => '4', 'client' => '', 'office' => 'Record and process application for withdrawal in UIS (currently enrolled only).', 'fees' => 'None', 'time' => '2 minutes', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '5', 'client' => 'Receive validated copy of Exit Clearance Form.', 'office' => 'Validate and issue copy of Exit Clearance Form.', 'fees' => 'None', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => 'None', 'time' => '35 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'ADJUSTMENT OF REGISTRATION',
                'lead' => 'Students may add, delete, or change course schedule within the first week from the start of classes.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Latest Certificate of Registration', 'where' => "Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => "Secure Adjustment of Registration Form from Window 1 of Registrar's Office.", 'office' => 'Issue Adjustment of Registration Form.', 'fees' => '0.00', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Accomplish the form and secure signatures of professors and dean.', 'office' => 'Faculty and administrative officers sign the form.', 'fees' => '0.00', 'time' => '30 minutes', 'person' => 'Faculty and Dean'],
                    ['no' => '3', 'client' => "Submit form to Office of the Registrar.", 'office' => 'Screen and receive the accomplished Adjustment of Registration Form.', 'fees' => '0.00', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                    ['no' => '4', 'client' => '', 'office' => 'Record and process application for adjustment of Registration in UIS.', 'fees' => '0.00', 'time' => '1 minute', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '5', 'client' => 'Receive copy of validated Adjustment of Registration Form.', 'office' => 'Issue validated copy of Adjustment of Registration Form.', 'fees' => '0.00', 'time' => '1 minute', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => '0.00', 'time' => '37 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'RETRIEVAL OF SUBMITTED ENTRANCE CREDENTIALS',
                'lead' => "Freshmen who did not report to classes and wish to withdraw from the list of officially enrolled may secure waiver for cancellation of enrollment from the Registrar's Office until two weeks from the start of classes for them to retrieve their submitted enrollment requirements.",
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Receiving copy of submitted documents', 'where' => "Registrar's Office"],
                    ['no' => '2', 'requirement' => 'Original copy of Letter Request for Form 137', 'where' => "Registrar's Office"],
                    ['no' => '3', 'requirement' => 'Validated withdrawal of enrollment form (currently enrolled only)', 'where' => "Registrar's Office"],
                    ['no' => '4', 'requirement' => 'Certificate of Registration (currently enrolled only)', 'where' => "Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Student will request for Cancellation of Enrolment at the Registrar\'s Office', 'office' => 'Records Officer will accomplish Waiver for Cancellation of Enrolment', 'fees' => 'None', 'time' => '5 mins', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '2', 'client' => 'Sign the Waiver and secure the original copy of submitted entrance credentials', 'office' => 'Issue the original copy of submitted entrance credentials and copy of the validated waiver for cancellation of enrollment', 'fees' => 'None', 'time' => '3 mins', 'person' => 'Erran Gerald Pastorfide'],
                ],
                'totals' => ['fees' => 'None', 'time' => '8 mins'],
            ],
            [
                'type' => 'transaction',
                'title' => 'DROPPING OF COURSES',
                'lead' => 'Students who enrolled in courses but failed to attend classes may apply for dropping of courses at least two weeks before the scheduled midterm examination to obtain an OD remark.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Certificate of Registration', 'where' => "PLP Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Secure a dropping form from the Registrar\'s Office', 'office' => 'Issue Dropping Form to students', 'fees' => 'None', 'time' => '5 mins', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Accomplish the form & secure the signature of the respective professor and Dean;', 'office' => 'Professors and Dean will sign the dropping form;', 'fees' => 'None', 'time' => '30 mins', 'person' => 'Faculty and Dean'],
                    ['no' => '3', 'client' => 'Submit the form to Office of the Registrar together with the old COR', 'office' => 'Receive and screen the accomplished form and endorse documents to GPO for processing', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                    ['no' => '4', 'client' => '', 'office' => 'Record and Process Application in UIS', 'fees' => 'None', 'time' => '1 min', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '5', 'client' => 'Receive copy of new Certificate of Registration and Validated Dropping Form', 'office' => 'Print and Issue new Certificate of Registration and Validated Dropping Form', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => 'None', 'time' => '38 min'],
            ],
            [
                'type' => 'transaction',
                'title' => 'COMPLETION OF GRADE',
                'lead' => 'Removal of the "INC" grade must be done two weeks after the submission of semestral grades.
After which the student shall be given a final grade based on his/her overall performance.
Semestral/
grade shall be based on the combined midterm grade and completion/final grade. The INC remarks
will no longer reflect in student\'s scholastic records once completed. Uncompleted INC remarks
will
automatically be equivalent to a final grade of 5.00. The INC remarks will no longer reflect in
student\'s
scholastic records but instead shall be replaced with the computed Semestral grade.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Completion Form', 'where' => 'Attached in issued grade report'],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Submit the completion form (attached in the issued Grade Report) to the faculty concerned upon completion of the requirements for the subject.', 'office' => 'Faculty must sign and provide the semestral grade of the student. Dean will sign the completion form', 'fees' => 'None', 'time' => '15 mins', 'person' => 'Faculty-In-Charge/College Dean'],
                    ['no' => '2', 'client' => 'Submit the accomplished completion form to the Registrar\'s Office', 'office' => 'Stamp and receive the accomplished form & forward to the Grades Processing Officer', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Marilyn Garcia'],
                    ['no' => '3', 'client' => '', 'office' => 'Record and Process Application in UIS', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '4', 'client' => 'Secure new copy of Grade Report', 'office' => 'Print Grade Report and issue to student together with validated completion form', 'fees' => 'None', 'time' => '1 min', 'person' => 'Erran Gerald Pastorfide'],
                ],
                'totals' => ['fees' => 'None', 'time' => '20 mins'],
            ],
            [
                'type' => 'transaction',
                'title' => 'LEAVE OF ABSENCE',
                'lead' => 'A student may apply to withdraw from all courses or not enroll for a specified semester(s) by filing a leave of absence approved by the respective dean. Leave of Absence may be granted to a student only for a maximum of one academic year but may be renewed upon re-application by the student. Each student may be granted a maximum of only two (2) LOAs. A student who is officially under Leave of Absence is not allowed to enroll in any other Higher Educational Institution.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Certificate of Registration of last semester attended', 'where' => "Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Secure Application for Leave of Absence Form', 'office' => 'Issue Leave of Absence Form', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Proceed to College Secretary and present LOA form', 'office' => 'Assessment of grade and students\' case.', 'fees' => 'None', 'time' => '5 min', 'person' => "*Julie Ruth Malabanan Nursing/Hospitality Management\n\n*Annlyn Benito Business Administration\n\n*Elsaflor Silayan Electronics Engineering/Entrepreneurship/Accountancy/Psychology\n\n*Aivee Dela Cruz Elementary/Secondary Education\n\n*Jay Anne Santos Computer Science/Information Technology"],
                    ['no' => '3', 'client' => 'Proceed to the Guidance Office/DSA/Medical Officer to secure signature', 'office' => 'Interview the student and sign the form', 'fees' => 'None', 'time' => '30 min', 'person' => 'Student Success Office'],
                    ['no' => '4', 'client' => 'Secure approval from the Dean', 'office' => 'Sign the student\'s application form', 'fees' => 'None', 'time' => '1 min', 'person' => 'Respective Dean'],
                    ['no' => '5', 'client' => 'Submit accomplished form to Registrar\'s Office', 'office' => 'Stamp and receive the accomplished form; deactivate student account', 'fees' => 'None', 'time' => '5 min', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => 'None', 'time' => '42 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'APPLICATION FOR READMISSION',
                'lead' => 'Returning student must present the approved LOA form upon enrollment. The University has the right to refuse enrollment of students who wish to return but was not able to file his leave prior to his absence. Should his justification be merited, the effectivity of his return will be on the next semester from the period his application for readmission is approved.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Readmission Slip (issued during filing of LOA)', 'where' => "PLP Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Present issued Readmission Slip (issued during filing of LOA) to the Registrar\'s Office', 'office' => 'Activate account of student', 'fees' => 'None', 'time' => '5 mins', 'person' => "Marilyn Garcia\n\n*Julie Ruth Malabanan Nursing/Hospitality Management\n\n*Annlyn Benito Business Administration\n\n*Elsaflor Silayan Electronics Engineering/Entrepreneurship/Accountancy/Psychology\n\n*Aivee Dela Cruz Elementary/Secondary Education\n\n*Jay Anne Santos Computer Science/Information Technology"],
                ],
                'totals' => ['fees' => 'None', 'time' => '5 minutes'],
            ],
            [
                'type' => 'transaction',
                'title' => 'CHANGE OF PERSONAL DATA',
                'lead' => "Students with correction in birth certificate entries or change in address may apply for change of personal data at the Registrar's Office.",
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'University ID', 'where' => ''],
                    ['no' => '2', 'requirement' => 'Certificate of Registration', 'where' => "PLP Registrar's Office"],
                    ['no' => '3', 'requirement' => 'Corrected PSA Birth Certificate (for students changing birth entries)', 'where' => 'PSA Office'],
                    ['no' => '4', 'requirement' => 'Barangay Clearance (for students applying for change of address)', 'where' => 'Respective Barangay'],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Secure a Correction/ Change of Birth Certificate Entries Form', 'office' => 'Issue Correction/ Change of Birth Certificate Entries Form', 'fees' => 'None', 'time' => '5 mins', 'person' => ''],
                    ['no' => '2', 'client' => 'Submit the accomplished form and attach the corrected PSA Birth Certificate/Brgy Clearance', 'office' => 'Validate the documents and have the University Registrar approve the request', 'fees' => 'None', 'time' => '15 mins', 'person' => 'Erran Gerald Pastorfide'],
                    ['no' => '3', 'client' => '', 'office' => 'Record and Process Application in UIS', 'fees' => 'None', 'time' => '2 min', 'person' => ''],
                    ['no' => '4', 'client' => 'Secure copy of the Validated Application Form and New Copy of Certificate of Registration', 'office' => 'Issue copy of the Validated Application Form and New Copy of Certificate of Registration', 'fees' => 'None', 'time' => '1 min', 'person' => ''],
                ],
                'totals' => ['fees' => 'None', 'time' => '23 mins'],
            ],
            [
                'type' => 'transaction',
                'title' => 'CHANGE OF GRADE',
                'lead' => 'A student who has received a passing grade in a given course is not allowed a re-examination for the purpose of improving his grades. Changing of grade may be allowed only after the approval of the Academic Director and must be filed within two weeks from the submission of grade to the Office of the Registrar.',
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Class Record', 'where' => 'Faculty'],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Faculty must secure a Change of Grade Form from the Registrar\'s Office', 'office' => 'Issue Change of Grade Form', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Faculty-in-charge should accomplish the form', 'office' => '', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Faculty'],
                    ['no' => '3', 'client' => 'Seek for the approval of the Dean of Faculty and Dean of student', 'office' => 'Sign the application form', 'fees' => 'None', 'time' => '5 mins', 'person' => 'College Dean'],
                    ['no' => '4', 'client' => 'Submit the approved form to the Registrar\'s Office with the attached class record', 'office' => 'Stamp and receive the accomplished form & forward to the Records Section', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Marilyn Garcia'],
                    ['no' => '5', 'client' => 'Endorse to Grades Processing Officer for recording in UIS', 'office' => '', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Federico Nueva'],
                    ['no' => '6', 'client' => 'Student to secure copy of New Grade Report', 'office' => 'Print new Grade Report of student', 'fees' => 'None', 'time' => '2 mins', 'person' => 'Marilyn Garcia'],
                    ['no' => '7', 'client' => 'Faculty to secure copy of approved Change of Grade form', 'office' => 'Issue approved/disapproved copy of Application for Change of Grade', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => 'None', 'time' => '16 mins'],
            ],
            [
                'type' => 'transaction',
                'title' => 'REQUEST FOR STUDENT RECORDS',
                'lead' => "Students may secure a copy of their credentials from the Registrar's Office.",
                'meta' => [
                    'OFFICE OR DIVISION' => "REGISTRAR'S OFFICE",
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) STUDENT (2) AUTHORIZED REPRESENTATIVE OF THE PARTY CONCERNED',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Lacking Entrance Credentials', 'where' => 'Varies'],
                    ['no' => '2', 'requirement' => 'Authorization Letter and ID (if requested by authorized representative)', 'where' => 'Requesting Student'],
                    ['no' => '3', 'requirement' => 'Validated Clearance (for transferring students)', 'where' => "Registrar's Office"],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Secure a Application for Student Records (Google Form)', 'office' => 'Issued Google Form for Application for Student Records Form', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Proceed to College Secretary', 'office' => 'Evaluate student record and assess the fees to be paid by the student & endorse to the Finance Office', 'fees' => 'None', 'time' => '5 mins', 'person' => "*Julie Ruth Malabanan Nursing/Hospitality Management\n\n*Annlyn Benito Business Administration\n\n*Elsaflor Silayan Electronics Engineering/Entrepreneurship/Accountancy/Psychology\n\n*Aivee Dela Cruz Elementary/Secondary Education\n\n*Jay Anne Santos Computer Science/Information Technology"],
                    ['no' => '3', 'client' => "Pay fees at the Cashier's Office", 'office' => 'Collect fees and issue receipt', 'fees' => '', 'time' => '5 mins', 'person' => 'Jenky Estayani'],
                    ['no' => '', 'client' => 'Transcript of Record', 'office' => '', 'fees' => '100/page', 'time' => '5-7 working days', 'person' => ''],
                    ['no' => '', 'client' => 'Copy of Grades', 'office' => '', 'fees' => '50/page', 'time' => '10 days', 'person' => ''],
                    ['no' => '', 'client' => 'Honorable Dismissal', 'office' => '', 'fees' => '100.00', 'time' => '5 days', 'person' => ''],
                    ['no' => '', 'client' => 'Certificate', 'office' => '', 'fees' => '50.00', 'time' => '5 days', 'person' => ''],
                    ['no' => '', 'client' => 'Permanent Record Authentication or Document', 'office' => '', 'fees' => '100/PG', 'time' => '1 day', 'person' => ''],
                    ['no' => '', 'client' => 'CAV Endorsement', 'office' => '', 'fees' => '80', 'time' => '1 day', 'person' => ''],
                    ['no' => '4', 'client' => "Present receipt to the Registrar's Office and secure claim slip", 'office' => 'Receive the accomplished form and issue claim slip', 'fees' => 'none', 'time' => '5 mins', 'person' => 'Marilyn Garcia'],
                ],
                'totals' => ['fees' => 'Vaires', 'time' => 'Varies'],
            ],
            [
                'type' => 'transaction',
                'title' => 'REQUEST FOR COURSE VALIDATION / COURSE CREDITING',
                'lead' => 'Transferees may request for course validation or course crediting.',
                'meta' => [
                    'OFFICE OR DIVISION' => 'DEAN/OFFICE/REGISTRAR OFFICE',
                    'CLASSIFICATION' => 'SIMPLE',
                    'TYPE OF TRANSACTION' => 'G2C',
                    'WHO MAY AVAIL' => '(1) TRANSFEREE',
                ],
                'checklist' => [
                    ['no' => '1', 'requirement' => 'Validation Permit', 'where' => "Registrar's Office"],
                    ['no' => '2', 'requirement' => 'TOR', 'where' => 'Former School'],
                    ['no' => '3', 'requirement' => 'Course Syllabus', 'where' => 'Former School'],
                    ['no' => '4', 'requirement' => 'Course Description', 'where' => 'Former School'],
                ],
                'steps' => [
                    ['no' => '1', 'client' => 'Secure validation permit from the OUR', 'office' => 'Issue validation permit', 'fees' => 'None', 'time' => '1 min', 'person' => 'Marilyn Garcia'],
                    ['no' => '2', 'client' => 'Submit filled out validation permit and course syllabus/outline', 'office' => 'Validate the submitted form', 'fees' => 'None', 'time' => '1 day', 'person' => 'College Dean'],
                    ['no' => '3', 'client' => 'Submit filled out validation permit and course syllabus/outline duly signed by the Dean', 'office' => 'Record and process the request of the transferee', 'fees' => 'None', 'time' => '1 day', 'person' => 'University Registrar'],
                ],
                'totals' => ['fees' => '0.00', 'time' => 'Varies'],
            ],
            [
                'type' => 'feedback',
                'heading' => 'FEEDBACK AND COMPLAINTS',
                'title' => 'FEEDBACK AND COMPLAINTS MECHANISM',
                'rows' => [
                    ['label' => 'How To Send Feedback', 'value' => "\nFeedbacks and Suggestions are welcomed through our Suggestion Box situated near the Windows of the Registrar's Office or they may send us email at registrar@plpasig.edu.ph\n"],
                    ['label' => 'How feedback is processed', 'value' => '1. Acknowledgement of Feedback and Suggestion\n\n2. Convey feedbacks to concerned personnel\n\n3. Deliberation of Feedbacks and Suggestions that may be adopted/Find possible solution for negative feedbacks\n\n4. Update sender on actions taken to respond to their feedback'],
                    ['label' => 'How to file a complaint', 'value' => "Complaints must be sent in writing to the Registrar's Office either via snail mail, email or personally submitted to the office."],
                    ['label' => 'How complaints are processed', 'value' => '1. Acknowledgement of Written Complaint\n\n2. Validation of Complaint/Investigation\n\n3. Respond with written solution/decision/ action taken within 48 hours from receipt of complaint.'],
                    ['label' => 'Contact Information', 'value' => '\n<b>EMAIL</b>: \nregistrar@plpasig.edu.ph\n<b>NO</b>: (362) 8628-1014 local 110'],
                ],
            ],
        ];
    }

    private function buildCorAssessment($subjects, float $totalUnits): array
    {
        $nstpUnits = (float) $subjects->sum(function ($subject) {
            $code = strtoupper((string) $subject->code);
            $name = strtoupper((string) $subject->name);

            if (strpos($code, 'NSTP') !== false || strpos($name, 'CWTS') !== false || strpos($name, 'ROTC') !== false) {
                return is_numeric($subject->units) ? (float) $subject->units : 0;
            }

            return 0;
        });

        $tuitionUnits = max($totalUnits - $nstpUnits, 0);
        $perUnitRate = 50.0;
        $miscellaneousFee = 300.0;
        $laboratoryFee = 500.0;

        $tuitionFee = $tuitionUnits * $perUnitRate;
        $cwtsFee = $nstpUnits * $perUnitRate;
        $totalTuitionFee = $tuitionFee + $cwtsFee;
        $currentAccount = $totalTuitionFee + $miscellaneousFee + $laboratoryFee;

        return [
            'tuition_units' => $tuitionUnits,
            'nstp_units' => $nstpUnits,
            'per_unit_rate' => $perUnitRate,
            'tuition_fee' => $tuitionFee,
            'cwts_fee' => $cwtsFee,
            'total_tuition_fee' => $totalTuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'laboratory_fee' => $laboratoryFee,
            'current_account' => $currentAccount,
        ];
    }

    private function seedCrossEnrollRowsIfEmpty(): void
    {
        if (CrossEnrollmentRequest::query()->exists()) {
            return;
        }

        $students = Student::query()->orderBy('id')->limit(10)->get();
        foreach ($students as $student) {
            CrossEnrollmentRequest::create([
                'student_id' => $student->id,
                'school_year' => $student->school_year,
                'semester' => $student->semester,
                'program' => $student->program,
                'year_level' => $student->year_level,
                'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
                'status' => 'pending',
            ]);
        }
    }

    private function seedWaiverRowsIfEmpty(): void
    {
        if (CancellationWaiver::query()->exists()) {
            return;
        }

        $students = Student::query()->orderBy('id')->limit(10)->get();
        foreach ($students as $student) {
            CancellationWaiver::create([
                'student_id' => $student->id,
                'school_year' => $student->school_year,
                'semester' => $student->semester,
                'program' => $student->program,
                'year_level' => $student->year_level,
                'section' => trim(($student->program ?: 'PROGRAM') . ' ' . ($student->year_level ?: 'YEAR')),
                'status' => 'pending',
            ]);
        }
    }
}
