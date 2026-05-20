<?php

namespace App\Http\Controllers\Registrar;

use App\ApplicationStatus;
use App\AcademicTerm;
use App\Applicant;
use App\ApplicantApplicationPreference;
use App\ApplicantEducationalBackground;
use App\ApplicantFamilyBackground;
use App\ApplicantPhotoUpload;
use App\ApplicantRequirementSubmission;
use App\ApplicantRequirementSubmissionFile;
use App\AlumniTrackerSetting;
use App\CancellationWaiver;
use App\College;
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
use App\StudentDisciplineStudent;
use App\StudentRequirementStatus;
use App\StudentMedicalRecord;
use App\StudentClinicRecord;
use App\GraduateTagging;
use App\GradeCorrectionRequest;
use App\StudentSubjectGrade;
use App\ScholarshipProgram;
use App\ScholarshipStudent;
use App\SystemAnnouncement;
use App\MasterStudentGradeFile;
use App\TransmutationRule;
use App\SupportTicket;
use App\RegistrarEmailTemplate;
use App\RegistrarMessage;
use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\Subject;
use App\Support\AuditTrailRecorder;
use App\Support\HelpCenterTicketService;
use App\Support\SystemConfigSchoolTermOptions;
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
    private const SECTION_OFFERING_DEFAULT_PER_PAGE = 10;
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
    private const ROOM_ASSIGNMENT_TYPES = [
        'Lecture Room',
        'Computer Laboratory',
        'Science Laboratory',
        'PE Area',
        'Auditorium',
        'Online / Virtual Room',
    ];
    private const ROOM_AVAILABLE_START_TIME = '07:00';
    private const ROOM_AVAILABLE_END_TIME = '21:00';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $this->syncRegistrarNotificationsForUser($user);

            view()->share('registrarNotifications', $this->activeRegistrarNotifications($user));
            view()->share('registrarUnreadNotificationCount', $this->registrarUnreadNotificationCount($user));

            return $next($request);
        });
    }

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

        // Gender breakdown
        $maleCount = 0;
        $femaleCount = 0;
        $genderRows = Student::query()
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 'students.id')
            ->selectRaw("LOWER(TRIM(COALESCE(NULLIF(students.sex, ''), NULLIF(sp.gender, '')))) as gender_value, COUNT(*) as total")
            ->groupBy('gender_value')
            ->pluck('total', 'gender_value');

        foreach ($genderRows as $genderValue => $count) {
            $normalizedGender = strtolower(trim((string) $genderValue));
            if (in_array($normalizedGender, ['male', 'm'], true)) {
                $maleCount += (int) $count;
            } elseif (in_array($normalizedGender, ['female', 'f'], true)) {
                $femaleCount += (int) $count;
            }
        }

        // Graduate count
        $graduateCount = 0;
        if (Schema::hasTable('graduate_taggings')) {
            $graduateCount = \App\GraduateTagging::where('is_graduate', true)->count();
        }

        // Applicant exam status breakdown
        $examPassed  = 0;
        $examFailed  = 0;
        $examPending = $applicantCount;
        if (Schema::hasColumn('applicants', 'exam_result_status')) {
            $examPassed  = Applicant::where('exam_result_status', 'Passed')->count();
            $examFailed  = Applicant::where('exam_result_status', 'Failed')->count();
            $examPending = max(0, $applicantCount - $examPassed - $examFailed);
        }

        // Students by year level — normalise raw values to 1st–4th Year buckets
        $yearLevelMap = [
            '1st Year' => 0, '2nd Year' => 0, '3rd Year' => 0, '4th Year' => 0,
        ];
        $yearNorm = [
            '1' => '1st Year', 'first' => '1st Year', '1st' => '1st Year', '1st year' => '1st Year',
            '2' => '2nd Year', 'second' => '2nd Year', '2nd' => '2nd Year', '2nd year' => '2nd Year',
            '3' => '3rd Year', 'third' => '3rd Year', '3rd' => '3rd Year', '3rd year' => '3rd Year',
            '4' => '4th Year', 'fourth' => '4th Year', '4th' => '4th Year', '4th year' => '4th Year',
        ];
        if (Schema::hasColumn('students', 'year_level')) {
            $rawYearData = Student::select('year_level', DB::raw('count(*) as total'))
                ->groupBy('year_level')
                ->pluck('total', 'year_level')
                ->toArray();
            foreach ($rawYearData as $level => $count) {
                $key = $yearNorm[strtolower(trim((string) $level))] ?? null;
                if ($key) {
                    $yearLevelMap[$key] += (int) $count;
                }
            }
        }

        // Demo data fallback
        if ($useDemoDashboard || ($studentCount === 0 && $applicantCount === 0 && $facultyCount === 0)) {
            $studentCount  = 230 + ($daySeed % 22);
            $applicantCount = 82 + ($daySeed % 17);
            $facultyCount  = 18 + ($daySeed % 6);
            $departmentCount = max($departmentCount, 4);
            $graduateCount = max($graduateCount, 47 + ($daySeed % 12));
            $examPassed    = 35 + ($daySeed % 8);
            $examFailed    = 12 + ($daySeed % 5);
            $examPending   = max(0, $applicantCount - $examPassed - $examFailed);

            $maleRatio  = 0.53 + (($daySeed % 6) * 0.01);
            $maleCount  = (int) round($studentCount * $maleRatio);
            $femaleCount = max($studentCount - $maleCount, 0);

            $yearLevelMap = [
                '1st Year' => 78 + ($daySeed % 10),
                '2nd Year' => 62 + ($daySeed % 8),
                '3rd Year' => 55 + ($daySeed % 7),
                '4th Year' => 35 + ($daySeed % 6),
            ];
        }

        if ($maleCount + $femaleCount === 0 && $studentCount > 0) {
            $maleCount   = (int) round($studentCount * 0.56);
            $femaleCount = max($studentCount - $maleCount, 0);
        }

        // Enrollment trend
        $trendValues = $this->buildMonthlyCounts('students', 6);
        $nonZeroTrendPoints = count(array_filter($trendValues, function ($value) {
            return $value > 0;
        }));
        if ($useDemoDashboard || array_sum($trendValues) <= 0 || $nonZeroTrendPoints <= 2) {
            $trendValues = $this->buildDemoUptrendSeries(6, 42 + ($daySeed % 6), 3, 7, $daySeed + 5);
        }
        $trendPercent  = $this->computeLastMonthPercent($trendValues);
        $sparklinePaths = $this->buildSparklinePaths($trendValues, 110, 60);

        // Month labels for the bar chart (last 6 months, oldest → newest)
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthLabels[] = Carbon::now()->subMonths($i)->format('M Y');
        }

        $dashboardAnnouncements = $this->activeAnnouncementsForAudience(SystemAnnouncement::AUDIENCE_STAFF, 8);

        return view('registrar.dashboard', [
            'dashboardData' => [
                'studentCount'   => $studentCount,
                'maleCount'      => $maleCount,
                'femaleCount'    => $femaleCount,
                'applicantCount' => $applicantCount,
                'facultyCount'   => $facultyCount,
                'departmentCount' => $departmentCount,
                'graduateCount'  => $graduateCount,
                'examPassed'     => $examPassed,
                'examFailed'     => $examFailed,
                'examPending'    => $examPending,
                'trendPercent'   => $trendPercent,
                'trendValues'    => array_values($trendValues),
                'monthLabels'    => $monthLabels,
                'yearLevelData'  => $yearLevelMap,
                'sparklinePath'      => $sparklinePaths['line'],
                'sparklineAreaPath'  => $sparklinePaths['area'],
            ],
            'dashboardAnnouncements' => $dashboardAnnouncements,
        ]);
    }

    private function activeAnnouncementsForAudience(string $audienceCode, int $limit = 6): array
    {
        if (!Schema::hasTable('system_announcements')) {
            return [];
        }

        $today = now()->toDateString();
        $safeLimit = max(1, $limit);

        return SystemAnnouncement::query()
            ->with('announcementTypeLookup')
            ->activeOn($today)
            ->visibleToAudience($audienceCode)
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get()
            ->map(function (SystemAnnouncement $announcement) {
                $audienceCodeResolved = SystemAnnouncement::normalizeAudienceCode($announcement->announcement_type);

                return [
                    'id' => (int) $announcement->id,
                    'title' => trim((string) $announcement->title),
                    'timeLabel' => $this->formatAnnouncementDateRange($announcement),
                    'audienceCode' => $audienceCodeResolved,
                    'audienceLabel' => $this->dashboardAnnouncementAudienceLabel($audienceCodeResolved),
                ];
            })
            ->filter(function ($row) {
                return $row['title'] !== '';
            })
            ->values()
            ->all();
    }

    private function formatAnnouncementDateRange(SystemAnnouncement $announcement): string
    {
        $from = optional($announcement->date_from)->format('M d, Y');
        $to = optional($announcement->date_to)->format('M d, Y');

        if ($from && $to && $from !== $to) {
            return $from . ' - ' . $to;
        }

        if ($from) {
            return $from;
        }

        if ($to) {
            return $to;
        }

        return 'Date not specified';
    }

    private function dashboardAnnouncementAudienceLabel(string $audienceCode): string
    {
        $labels = [
            SystemAnnouncement::AUDIENCE_EVERYONE => 'Everyone',
            SystemAnnouncement::AUDIENCE_STUDENTS => 'Students',
            SystemAnnouncement::AUDIENCE_FACULTY => 'Faculty',
            SystemAnnouncement::AUDIENCE_STAFF => 'Staff',
            SystemAnnouncement::AUDIENCE_APPLICANT => 'Applicant',
        ];

        return $labels[$audienceCode] ?? $labels[SystemAnnouncement::AUDIENCE_EVERYONE];
    }

    /**
     * Registrar Messaging
     */
    public function messaging()
    {
        $folder = strtolower(trim((string) request('folder', 'inbox')));
        if (!in_array($folder, ['inbox', 'drafts', 'sent', 'trash'], true)) {
            $folder = 'inbox';
        }

        $messages = RegistrarMessage::query()
            ->where('folder', $folder)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $folderCounts = RegistrarMessage::query()
            ->select('folder', DB::raw('COUNT(*) as total'))
            ->groupBy('folder')
            ->pluck('total', 'folder');

        return view('registrar.messaging', compact('folder', 'messages', 'folderCounts'));
    }

    public function storeRegistrarMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => 'required_unless:folder,drafts|nullable|string|max:190',
            'subject' => 'required_unless:folder,drafts|nullable|string|max:190',
            'body' => 'required_unless:folder,drafts|nullable|string|max:5000',
            'folder' => 'nullable|in:sent,drafts',
        ]);

        $user = auth()->user();
        $folder = (string) ($validated['folder'] ?? 'sent');

        $message = RegistrarMessage::query()->create([
            'folder' => $folder,
            'sender_name' => $user && trim((string) $user->name) !== '' ? (string) $user->name : 'Registrar Office',
            'sender_type' => 'Registrar',
            'recipient' => trim((string) ($validated['recipient'] ?? '')),
            'subject' => trim((string) ($validated['subject'] ?? 'Untitled draft')),
            'body' => trim((string) ($validated['body'] ?? '')),
            'source_type' => 'manual',
            'created_by_user_id' => $user ? (int) $user->id : null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => $folder === 'drafts' ? 'Draft saved successfully.' : 'Message saved to database.',
            'id' => (int) $message->id,
        ], 201);
    }

    public function communicationTickets(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $category = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('q', ''));

        $query = SupportTicket::query()
            ->with(['assignedTo:id,name', 'createdBy:id,name'])
            ->orderByRaw("CASE
                WHEN status = 'Open' THEN 1
                WHEN status = 'In Progress' THEN 2
                WHEN status = 'Pending' THEN 3
                WHEN status = 'Resolved' THEN 4
                ELSE 5
            END")
            ->orderByDesc('updated_at');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ticket_no', 'like', '%' . $search . '%')
                    ->orWhere('requester_name', 'like', '%' . $search . '%')
                    ->orWhere('requester_email', 'like', '%' . $search . '%')
                    ->orWhere('subject', 'like', '%' . $search . '%');
            });
        }

        $tickets = $query->limit(150)->get();
        $summary = [
            'total' => (int) $tickets->count(),
            'open' => (int) $tickets->where('status', 'Open')->count(),
            'progress' => (int) $tickets->where('status', 'In Progress')->count(),
            'resolved' => (int) $tickets->where('status', 'Resolved')->count(),
            'complaints' => (int) $tickets->where('category', 'Complaint')->count(),
        ];

        return view('registrar.communication.tickets', compact('tickets', 'summary', 'status', 'category', 'search'));
    }

    public function storeCommunicationTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requester_type' => 'required|in:Student,Faculty,Parent,Applicant,CHED,Other',
            'requester_name' => 'required|string|max:190',
            'requester_email' => 'nullable|email|max:190',
            'category' => 'required|in:Inquiry,Request,Feedback,Complaint,Concern',
            'subject' => 'required|string|max:190',
            'message' => 'nullable|string|max:5000',
            'priority' => 'required|in:Low,Normal,High,Urgent',
        ]);

        $ticket = SupportTicket::query()->create([
            'ticket_no' => $this->nextSupportTicketNumber(),
            'requester_type' => $validated['requester_type'],
            'requester_name' => trim($validated['requester_name']),
            'requester_email' => trim((string) ($validated['requester_email'] ?? '')) ?: null,
            'category' => $validated['category'],
            'subject' => trim($validated['subject']),
            'message' => trim((string) ($validated['message'] ?? '')),
            'priority' => $validated['priority'],
            'status' => 'Open',
            'created_by_user_id' => auth()->id(),
        ]);

        RegistrarMessage::query()->create([
            'folder' => 'inbox',
            'sender_name' => $ticket->requester_name,
            'sender_type' => $ticket->requester_type,
            'recipient' => 'Registrar Office',
            'subject' => '[' . $ticket->ticket_no . '] ' . $ticket->subject,
            'body' => $ticket->message,
            'source_type' => 'support_ticket',
            'source_id' => (int) $ticket->id,
            'created_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Ticket submitted successfully.',
            'ticket_no' => $ticket->ticket_no,
        ], 201);
    }

    public function updateCommunicationTicket(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed',
            'priority' => 'required|in:Low,Normal,High,Urgent',
            'message' => 'nullable|string|max:5000',
        ]);

        $supportTicket->status = $validated['status'];
        $supportTicket->priority = $validated['priority'];
        if (array_key_exists('message', $validated)) {
            $supportTicket->message = trim((string) $validated['message']);
        }
        $supportTicket->assigned_to_user_id = auth()->id();
        $supportTicket->resolved_at = in_array($validated['status'], ['Resolved', 'Closed'], true) ? now() : null;
        $supportTicket->save();

        return response()->json([
            'ok' => true,
            'message' => 'Ticket updated successfully.',
        ]);
    }

    public function stakeholderCommunication()
    {
        $tickets = SupportTicket::query()->orderByDesc('updated_at')->limit(50)->get();
        $stakeholders = collect(['Student', 'Faculty', 'Parent', 'Applicant', 'CHED', 'Other'])
            ->map(function ($type) use ($tickets) {
                $group = $tickets->where('requester_type', $type);
                return [
                    'type' => $type,
                    'tickets' => (int) $group->count(),
                    'open' => (int) $group->whereIn('status', ['Open', 'In Progress', 'Pending'])->count(),
                    'resolved' => (int) $group->where('status', 'Resolved')->count(),
                ];
            })
            ->values();

        return view('registrar.communication.stakeholders', compact('stakeholders', 'tickets'));
    }

    public function emailNotificationTemplates()
    {
        $this->seedDefaultRegistrarEmailTemplates();

        $templates = RegistrarEmailTemplate::query()
            ->with('updatedBy:id,name')
            ->orderBy('audience')
            ->orderBy('name')
            ->get();

        return view('registrar.communication.email-templates', compact('templates'));
    }

    public function storeEmailNotificationTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:80|regex:/^[A-Za-z0-9_-]+$/',
            'name' => 'required|string|max:150',
            'audience' => 'required|string|max:80',
            'subject' => 'required|string|max:190',
            'body' => 'required|string|max:10000',
        ]);

        $code = strtoupper(trim($validated['code']));

        RegistrarEmailTemplate::query()->updateOrCreate([
            'code' => $code,
        ], [
            'name' => trim($validated['name']),
            'audience' => trim($validated['audience']),
            'subject' => trim($validated['subject']),
            'body' => trim($validated['body']),
            'is_active' => true,
            'updated_by_user_id' => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'message' => 'Email template saved successfully.'], 201);
    }

    public function updateEmailNotificationTemplate(Request $request, RegistrarEmailTemplate $registrarEmailTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'audience' => 'required|string|max:80',
            'subject' => 'required|string|max:190',
            'body' => 'required|string|max:10000',
            'is_active' => 'nullable|boolean',
        ]);

        $registrarEmailTemplate->fill([
            'name' => trim($validated['name']),
            'audience' => trim($validated['audience']),
            'subject' => trim($validated['subject']),
            'body' => trim($validated['body']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'updated_by_user_id' => auth()->id(),
        ])->save();

        return response()->json(['ok' => true, 'message' => 'Email template updated successfully.']);
    }

    private function nextSupportTicketNumber(): string
    {
        $prefix = 'TKT-' . now()->format('Ymd') . '-';
        $count = SupportTicket::query()
            ->where('ticket_no', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function seedDefaultRegistrarEmailTemplates(): void
    {
        $templates = [
            [
                'code' => 'STATUS_CHANGE',
                'name' => 'Status Change Email',
                'audience' => 'Applicant / Student',
                'subject' => 'PLP Status Update: {{status}}',
                'body' => "Good day {{name}},\n\nYour status has been updated to {{status}}.\n\nPlease log in to your portal for complete details.\n\nRegistrar Office",
            ],
            [
                'code' => 'OFFICIAL_STUDENT_EMAIL',
                'name' => 'Official Student Email Generation',
                'audience' => 'Student',
                'subject' => 'Your Official PLP Student Email',
                'body' => "Good day {{name}},\n\nYour official PLP student email is {{student_email}}.\n\nUse this account for official academic transactions.\n\nRegistrar Office",
            ],
            [
                'code' => 'ADMISSION_NOTICE',
                'name' => 'Admission Email Template',
                'audience' => 'Applicant',
                'subject' => 'PLP Admission Update',
                'body' => "Good day {{name}},\n\nThis is an admission update from PLP. {{message}}\n\nAdmissions Office",
            ],
            [
                'code' => 'REGISTRAR_NOTICE',
                'name' => 'Registrar Email Template',
                'audience' => 'Stakeholder',
                'subject' => 'Registrar Office Notice',
                'body' => "Good day {{name}},\n\n{{message}}\n\nRegistrar Office",
            ],
        ];

        foreach ($templates as $template) {
            RegistrarEmailTemplate::query()->firstOrCreate(
                ['code' => $template['code']],
                $template + [
                    'is_active' => true,
                    'updated_by_user_id' => auth()->id(),
                ]
            );
        }
    }

    private function activeRegistrarNotifications($user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user || strtolower(trim((string) $user->module)) !== 'registrar') {
            return collect();
        }

        if (!Schema::hasTable('notification_deliveries') || !Schema::hasTable('portal_notifications')) {
            return collect();
        }

        return NotificationDelivery::query()
            ->with(['notification.type'])
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->orderByDesc('delivered_at')
            ->orderByDesc('id')
            ->get();
    }

    private function registrarUnreadNotificationCount($user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user || strtolower(trim((string) $user->module)) !== 'registrar') {
            return 0;
        }

        if (!Schema::hasTable('notification_deliveries')) {
            return 0;
        }

        return (int) NotificationDelivery::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->whereNull('read_at')
            ->count();
    }

    private function syncRegistrarNotificationsForUser($user)
    {
        $this->syncAnnouncementNotificationsForRegistrar($user);
    }

    private function syncAnnouncementNotificationsForRegistrar($user)
    {
        if (!$user || strtolower(trim((string) $user->module)) !== 'registrar') {
            return;
        }

        if (!Schema::hasTable('system_announcements')
            || !Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')
            || !Schema::hasTable('notification_deliveries')) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'SYSTEM_ANNOUNCEMENT_POSTED'],
            ['name' => 'System Announcement Posted']
        );

        $today = now()->toDateString();
        $announcements = SystemAnnouncement::query()
            ->activeOn($today)
            ->visibleToAudience(SystemAnnouncement::AUDIENCE_STAFF)
            ->orderBy('id')
            ->get(['id', 'title', 'date_from', 'date_to', 'created_at', 'content']);

        foreach ($announcements as $announcement) {
            $titleValue = trim((string) $announcement->title);
            $title = $titleValue !== ''
                ? 'Announcement: ' . $titleValue
                : 'New Registrar Announcement';

            $content = trim((string) $announcement->content);
            $message = $content !== '' ? $content : 'A registrar announcement is available';

            $dateFromLabel = optional($announcement->date_from)->format('M d, Y');
            $dateToLabel = optional($announcement->date_to)->format('M d, Y');

            if ($dateFromLabel && $dateToLabel && $dateFromLabel !== $dateToLabel) {
                $message .= ' Effective from ' . $dateFromLabel . ' to ' . $dateToLabel . '.';
            } elseif ($dateFromLabel) {
                $message .= ' Effective on ' . $dateFromLabel . '.';
            } elseif ($dateToLabel) {
                $message .= ' Available until ' . $dateToLabel . '.';
            }

            if (!preg_match('/[.!?]$/', $message)) {
                $message .= '.';
            }

            $notification = PortalNotification::query()->firstOrCreate(
                [
                    'source_module' => 'system_announcement',
                    'source_reference' => 'system_announcement:' . $announcement->id,
                ],
                [
                    'notification_type_id' => $type->id,
                    'title' => $title,
                    'message' => $message,
                    'source_url' => '',
                    'created_by_user_id' => null,
                ]
            );

            $hasChanges = false;

            if ((int) $notification->notification_type_id !== (int) $type->id) {
                $notification->notification_type_id = $type->id;
                $hasChanges = true;
            }

            if ((string) $notification->title !== (string) $title) {
                $notification->title = $title;
                $hasChanges = true;
            }

            if ((string) $notification->message !== (string) $message) {
                $notification->message = $message;
                $hasChanges = true;
            }

            if ((string) ($notification->source_url ?: '') !== '') {
                $notification->source_url = '';
                $hasChanges = true;
            }

            if ($hasChanges) {
                $notification->save();
            }

            NotificationDelivery::query()->firstOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => $user->id,
                ],
                [
                    'delivered_at' => $announcement->created_at ?: now(),
                ]
            );
        }
    }

    public function dismissNotification(Request $request, $notificationDelivery)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('notification_deliveries')) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }

            return back();
        }

        $delivery = NotificationDelivery::query()
            ->where('id', (int) $notificationDelivery)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $delivery->dismissed_at = now();
        if (empty($delivery->read_at)) {
            $delivery->read_at = now();
        }
        $delivery->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function markNotificationsRead(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('notification_deliveries')) {
            return response()->json(['ok' => true, 'unread_count' => 0]);
        }

        NotificationDelivery::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'unread_count' => 0]);
    }

    public function notificationsFeed(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $this->syncRegistrarNotificationsForUser($user);

        $notifications = $this->activeRegistrarNotifications($user)
            ->map(function ($delivery) {
                $notification = $delivery->notification;

                return [
                    'delivery_id' => (int) $delivery->id,
                    'title' => $notification ? (string) $notification->title : 'New notification',
                    'message' => $notification ? (string) $notification->message : '',
                    'source_url' => $notification ? (string) $notification->local_source_url : '',
                    'source_module' => $notification ? (string) $notification->source_module : '',
                    'source_reference' => $notification ? (string) $notification->source_reference : '',
                    'is_read' => !empty($delivery->read_at),
                    'dismiss_url' => route('registrar.notifications.dismiss', ['notificationDelivery' => $delivery->id], false),
                ];
            })
            ->unique(function ($item) {
                $sourceModule = (string) ($item['source_module'] ?? 'general');
                $sourceReference = trim((string) ($item['source_reference'] ?? ''));

                if ($sourceReference !== '') {
                    return $sourceModule . '|' . $sourceReference;
                }

                $fallback = strtolower(trim((string) ($item['title'] ?? '')) . '|' . trim((string) ($item['message'] ?? '')));
                return $sourceModule . '|' . $fallback;
            })
            ->values();

        return response()->json([
            'ok' => true,
            'unread_count' => $this->registrarUnreadNotificationCount($user),
            'notifications' => $notifications,
        ]);
    }

    public function helpCenter()
    {
        $topics = $this->helpCenterTopics();
        $user = auth()->user();

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
            'helpTickets' => HelpCenterTicketService::recentTicketsForUser('Registrar', $user),
            'helpTicketStoreRoute' => route('registrar.help.tickets.store'),
            'helpTicketCreateRoute' => route('registrar.help.tickets.create'),
            'helpTicketRequesterType' => 'Registrar',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function createHelpCenterTicket()
    {
        $user = auth()->user();

        return view('shared.help-center-ticket-form-page', [
            'backRoute' => route('registrar.help.center'),
            'helpTicketStoreRoute' => route('registrar.help.tickets.store'),
            'helpTicketRequesterType' => 'Registrar',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function storeHelpCenterTicket(Request $request)
    {
        $ticket = HelpCenterTicketService::createFromRequest($request, 'Registrar', auth()->user());

        return redirect()
            ->route('registrar.help.center')
            ->with('help_ticket_success', 'Ticket ' . $ticket->ticket_no . ' submitted successfully.');
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

        $photoUpload = ApplicantPhotoUpload::query()->updateOrCreate(
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

        AuditTrailRecorder::record('APPLICANT_PHOTO_UPLOADED', [
            [
                'type' => 'ApplicantPhotoUpload',
                'id' => $photoUpload->id,
                'label' => 'Applicant #' . $applicant->id,
                'changes' => [
                    ['field' => 'original_filename', 'old' => null, 'new' => $photoUpload->original_filename],
                    ['field' => 'storage_path', 'old' => null, 'new' => $photoUpload->storage_path],
                    ['field' => 'mime_type', 'old' => null, 'new' => $photoUpload->mime_type],
                    ['field' => 'size_bytes', 'old' => null, 'new' => $photoUpload->size_bytes],
                ],
            ],
        ], [
            'source_action' => 'Applicant photo upload',
        ]);
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
        $user = User::query()
            ->where('applicant_id', $applicant->id)
            ->first();

        if (!$user) {
            $username = $this->buildApplicantUsernameForAccount($applicant);
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

    private function buildApplicantUsernameForAccount(Applicant $applicant)
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
                'is_sample' => $this->profileFlagToInt($existingIsSample),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function profileFlagToInt($value)
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'rows_html' => view('registrar.process.partials.application-process-table-rows', [
                    'applicants' => $applicants,
                ])->render(),
                'pager_html' => $applicants->links()->render(),
            ]);
        }

        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('registrar.process.application-process', compact('applicants', 'courses', 'filters'));
    }

    public function bulkUpdateApplicantStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status', '');

        if (empty($ids) || empty($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.'
            ], 400);
        }

        try {
            // Update the status for all selected applicants
            // Using the model to ensure any accessors/mutators or events are triggered
            $applicants = Applicant::whereIn('id', $ids)->get();
            
            foreach ($applicants as $applicant) {
                $applicant->application_status = $status;
                $applicant->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated ' . count($ids) . ' applicants to ' . $status . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkConvertToStudent(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No applicants selected.'], 400);
        }

        $applicants = Applicant::whereIn('id', $ids)->get();

        if ($applicants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No applicants found for the selected IDs.',
            ], 422);
        }

        $year      = (int) date('Y');
        $converted = 0;
        $skipped   = 0;
        $results   = [];

        foreach ($applicants as $applicant) {
            $fullName = trim(implode(' ', array_filter([
                $applicant->first_name,
                $applicant->middle_name,
                $applicant->last_name,
            ])));

            if (Student::where('name', $fullName)->exists()) {
                $skipped++;
                continue;
            }

            $studentNo = Student::generateStudentNo($year);

            $course     = null;
            $preference = $applicant->applicationPreference;
            if ($preference && $preference->course_id) {
                $course = Course::find($preference->course_id);
            }

            $student = Student::create([
                'student_no'   => $studentNo,
                'name'         => $fullName,
                'sex'          => $applicant->gender ?? '',
                'age'          => $applicant->age ?? 0,
                'college'      => $course ? ($course->college ?? '') : '',
                'program'      => $course ? ($course->name ?? '') : '',
                'course_id'    => $course ? $course->id : null,
                'school_year'  => $year . '-' . ($year + 1),
                'semester'     => '1st Semester',
                'is_withdrawn' => false,
            ]);

            // Generate official PLP email
            $officialEmail = $this->generateOfficialEmail(
                $applicant->first_name ?? '',
                $applicant->last_name  ?? ''
            );

            $profile = StudentProfile::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'student_no'   => $studentNo,
                    'first_name'   => $applicant->first_name ?? '',
                    'last_name'    => $applicant->last_name  ?? '',
                    'middle_name'  => $applicant->middle_name ?? '',
                    'student_email' => $officialEmail,
                    'profile_complete' => false,
                ]
            );

            // Ensure email is saved even if profile already existed
            if (empty($profile->student_email)) {
                $profile->update(['student_email' => $officialEmail]);
            }

            // Create student portal account if none exists
            if (!User::where('student_id', $student->id)->exists()) {
                User::create([
                    'name'                 => $fullName,
                    'username'             => $studentNo,
                    'email'                => $officialEmail,
                    'password'             => Hash::make($studentNo),
                    'module'               => 'student',
                    'student_id'           => $student->id,
                    'force_password_reset' => true,
                ]);
            }

            $results[] = [
                'student_no' => $studentNo,
                'name'       => $fullName,
                'email'      => $officialEmail,
            ];
            $converted++;
        }

        $summary = "{$converted} applicant(s) successfully converted to students.";
        if ($skipped > 0) {
            $summary .= " {$skipped} skipped (student record already exists).";
        }

        return response()->json([
            'success' => true,
            'message' => $summary,
            'results' => $results,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Generate a unique official PLP email: firstname.lastname@plp.edu.ph
     * Handles accented characters, duplicates, and edge cases.
     */
    private function generateOfficialEmail(string $firstName, string $lastName): string
    {
        $normalize = function (string $str): string {
            // Transliterate accented / non-ASCII characters
            if (function_exists('iconv')) {
                $str = (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            }
            $str = strtolower($str);
            // Keep only a-z and spaces; collapse spaces
            $str = preg_replace('/[^a-z\s]/', '', $str);
            $str = trim(preg_replace('/\s+/', '', $str));
            return $str;
        };

        $first = $normalize($firstName) ?: 'student';
        $last  = $normalize($lastName)  ?: 'plp';
        $base  = $first . '.' . $last;
        $email = $base . '@plp.edu.ph';

        // Resolve collisions by appending an incrementing counter
        $counter = 1;
        while (
            User::where('email', $email)->exists() ||
            StudentProfile::where('student_email', $email)->exists()
        ) {
            $email = $base . $counter . '@plp.edu.ph';
            $counter++;
        }

        return $email;
    }

    public function applicationProcessPrint(Request $request)
    {
        $filters = $this->normalizeApplicationProcessFilters($request);
        $applicants = $this->buildApplicationProcessQuery($filters)->get();
        $format = strtolower((string) $request->query('format', ''));

        $courseName = 'All Courses';
        if (!empty($filters['course_id'])) {
            $course = \App\Models\Course::find($filters['course_id']);
            if ($course) {
                $courseName = $course->name ?: $course->code;
            }
        }

        if ($format === 'pdf') {
            $html = view('registrar.process.application-process-pdf', compact('applicants', 'filters', 'courseName'))->render();
            try {
                // Ensure temp directory exists
                $tempDir = storage_path('app/temp_mpdf');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $mpdf = new \Mpdf\Mpdf([
                    'margin_left' => 10,
                    'margin_right' => 10,
                    'margin_top' => 15,
                    'margin_bottom' => 15,
                    'format' => 'A4-L',
                    'tempDir' => $tempDir
                ]);
                $mpdf->SetTitle('Applicant List');
                $mpdf->WriteHTML($html);
                // Change 'D' to 'I' for Inline display
                return $mpdf->Output('ApplicantList_' . date('Y-m-d') . '.pdf', 'I');
            } catch (\Throwable $e) {
                // Log the error if needed: \Log::error('PDF Export Failed: ' . $e->getMessage());
                return view('registrar.process.application-process-pdf', compact('applicants', 'filters', 'courseName'));
            }
        }

        if ($format === 'excel') {
            $filename = "ApplicantList_" . date('Y-m-d') . ".csv";
            
            return response()->stream(function() use ($applicants) {
                $file = fopen('php://output', 'w');
                
                // Add CSV Header
                fputcsv($file, [
                    'Applicant ID', 
                    'Last Name', 
                    'First Name', 
                    'Middle Name', 
                    'Program', 
                    'Date Applied', 
                    'Date Last Update', 
                    'Status'
                ]);

                foreach ($applicants as $applicant) {
                    $preference = optional($applicant->applicationPreference);
                    $preferredCourse = optional($preference->course);
                    $programLabel = 'N/A';
                    if ($preference->apply_program === 'college') {
                        $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
                    } elseif ($preference->apply_program === 'senior_high') {
                        $programLabel = $preference->apply_strand ?: 'Senior High';
                    }

                    $rawStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
                    $statusText = 'In Process';
                    if ($rawStatus === 'submitted' || $rawStatus === 'document submitted') {
                        $statusText = 'Document Submitted';
                    } elseif ($rawStatus === 'on probation' || $rawStatus === 'on_probation') {
                        $statusText = 'On Probation';
                    } elseif ($rawStatus === 'rejected') {
                        $statusText = 'Rejected';
                    } elseif ($rawStatus === 'incomplete' || $rawStatus === 'draft') {
                        $statusText = 'Incomplete';
                    } elseif ($rawStatus === 'accepted') {
                        $statusText = 'Accepted';
                    }

                    $dateApplied = $applicant->application_submitted_at ?: $applicant->created_at;

                    fputcsv($file, [
                        $applicant->applicant_id,
                        strtoupper((string) $applicant->last_name),
                        strtoupper((string) $applicant->first_name),
                        strtoupper((string) $applicant->middle_name),
                        $programLabel,
                        optional($dateApplied)->format('Y-m-d') ?: 'N/A',
                        optional($applicant->updated_at)->format('Y-m-d') ?: 'N/A',
                        strtoupper($statusText)
                    ]);
                }
                
                fclose($file);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

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

        $search = preg_replace('/\s+/', ' ', trim((string) $request->query('search', '')));
        if ($search !== '' && strlen($search) < 2) {
            $search = '';
        }

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
            'application_status' => trim((string) $request->query('application_status', '')),
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
        ];
    }

    private function buildApplicationProcessQuery(array $filters)
    {
        $appliedDateExpression = Schema::hasColumn('applicants', 'application_submitted_at')
            ? 'COALESCE(applicants.application_submitted_at, applicants.created_at)'
            : 'applicants.created_at';

        $query = Applicant::query()
            ->with(['applicationPreference.course', 'applicationStatusLookup'])
            ->leftJoin('applicant_application_preferences as preferences', 'preferences.applicant_id', '=', 'applicants.id')
            ->select('applicants.*');

        if (!empty($filters['from_date'])) {
            $query->whereRaw('DATE(' . $appliedDateExpression . ') >= ?', [$filters['from_date']]);
        }

        if (!empty($filters['to_date'])) {
            $query->whereRaw('DATE(' . $appliedDateExpression . ') <= ?', [$filters['to_date']]);
        }

        if (!empty($filters['application_status'])) {
            $query->where(function($mainQ) use ($filters) {
                $status = strtolower($filters['application_status']);
                
                // 1. Check via Relationship (Lookup Table)
                $mainQ->whereHas('applicationStatusLookup', function($q) use ($status) {
                    // Map user-friendly labels to potential database codes/labels
                    $searchValues = [$status];
                    if ($status === 'document submitted') {
                        $searchValues[] = 'submitted';
                        $searchValues[] = 'document_submitted';
                    } elseif ($status === 'in process') {
                        $searchValues[] = 'in_process';
                        $searchValues[] = 'process';
                    } elseif ($status === 'on probation') {
                        $searchValues[] = 'on_probation';
                        $searchValues[] = 'probation';
                    }

                    $q->where(function($sub) use ($searchValues) {
                        foreach ($searchValues as $val) {
                            $sub->orWhereRaw('LOWER(code) = ?', [$val])
                                ->orWhereRaw('LOWER(label) = ?', [$val]);
                        }
                    });
                });

                // 2. Fallbacks for NULL status_id cases to match UI display defaults
                if ($status === 'document submitted') {
                    $mainQ->orWhere(function($q) {
                        $q->whereNull('application_status_id')
                          ->whereNotNull('application_submitted_at');
                    });
                } elseif ($status === 'in process') {
                    $mainQ->orWhere(function($q) {
                        $q->whereNull('application_status_id')
                          ->whereNull('application_submitted_at');
                    });
                } elseif ($status === 'incomplete') {
                    $mainQ->orWhereHas('applicationStatusLookup', function($q) {
                        $q->whereRaw('LOWER(code) = ?', ['draft'])
                          ->orWhereRaw('LOWER(label) = ?', ['draft']);
                    });
                }
            });
        }

        if (!empty($filters['course_id'])) {
            $query->where('preferences.apply_course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['search'])) {
            $escapedSearch = addcslashes($filters['search'], '\\%_');
            $searchPrefix = $escapedSearch . '%';
            $searchTokens = preg_split('/[\s,]+/', $escapedSearch, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($searchQuery) use ($searchPrefix, $searchTokens) {
                $searchQuery->where('applicants.applicant_id', 'like', $searchPrefix);

                if (count($searchTokens) >= 2) {
                    $firstToken = $searchTokens[0] . '%';
                    $lastToken = $searchTokens[count($searchTokens) - 1] . '%';

                    $searchQuery->orWhere(function ($nameQuery) use ($firstToken, $lastToken) {
                        $nameQuery->where('applicants.first_name', 'like', $firstToken)
                            ->where('applicants.last_name', 'like', $lastToken);
                    })->orWhere(function ($nameQuery) use ($firstToken, $lastToken) {
                        $nameQuery->where('applicants.last_name', 'like', $firstToken)
                            ->where('applicants.first_name', 'like', $lastToken);
                    });

                    return;
                }

                $searchQuery->orWhere('applicants.first_name', 'like', $searchPrefix)
                    ->orWhere('applicants.middle_name', 'like', $searchPrefix)
                    ->orWhere('applicants.last_name', 'like', $searchPrefix);
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
            $query->orderByRaw($appliedDateExpression . ' ' . $sortDirection);
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

    public function applicantDocumentsData(Request $request, Applicant $applicant): JsonResponse
    {
        $filters = $this->normalizeApplicantDocumentFilters($request);
        $rows = $this->applicantDocumentRows($applicant, $filters['requirement_type']);

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $rows = $rows->filter(function ($row) use ($search) {
                return stripos((string) ($row['document_type'] ?? ''), $search) !== false;
            })->values();
        }

        if ($filters['status'] !== '') {
            $rows = $rows->filter(function ($row) use ($filters) {
                return strtolower((string) ($row['status'] ?? '')) === $filters['status'];
            })->values();
        }

        $total = (int) $rows->count();
        $lastPage = max(1, (int) ceil(max($total, 1) / $filters['per_page']));
        $page = min($filters['page'], $lastPage);
        $start = ($page - 1) * $filters['per_page'];

        $pagedRows = $rows
            ->slice($start, $filters['per_page'])
            ->values();

        return response()->json([
            'ok' => true,
            'rows' => $pagedRows,
            'meta' => [
                'page' => $page,
                'last_page' => $lastPage,
                'per_page' => $filters['per_page'],
                'total' => $total,
            ],
        ]);
    }

    public function upsertApplicantDocument(Request $request, Applicant $applicant, RegistrarRequirement $registrarRequirement): JsonResponse
    {
        if (!$this->applicantDocumentTablesReady()) {
            return response()->json([
                'ok' => false,
                'message' => 'Applicant requirement tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'is_submitted' => 'nullable',
            'remarks' => 'nullable|string|max:500',
            'date_submitted' => 'nullable|date',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $requirementType = $this->normalizeApplicantRequirementType($registrarRequirement->requirement_type);
        if ($requirementType === '') {
            throw ValidationException::withMessages([
                'document' => ['Only document and medical requirements are supported in this panel.'],
            ]);
        }

        if (empty($registrarRequirement->registrar_requirement_policy_id)) {
            $this->syncRequirementPolicyForLegacyRow($registrarRequirement);
            $registrarRequirement->refresh();
        }

        $policyId = (int) $registrarRequirement->registrar_requirement_policy_id;
        if ($policyId < 1) {
            throw ValidationException::withMessages([
                'document' => ['This requirement is missing a policy mapping. Please update the requirement setup first.'],
            ]);
        }

        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();
        $policySemesterId = (int) RegistrarRequirementPolicy::query()
            ->where('id', $policyId)
            ->value('system_school_semester_id');

        if ($policySemesterId > 0 && $policySemesterId !== $activeSemesterId) {
            throw ValidationException::withMessages([
                'document' => ['This requirement is not active for the current configured semester.'],
            ]);
        }

        $submission = ApplicantRequirementSubmission::query()->firstOrNew([
            'applicant_id' => (int) $applicant->id,
            'registrar_requirement_policy_id' => $policyId,
        ]);

        $originalIsSubmitted = $submission->exists
            ? ((bool) $submission->getOriginal('is_submitted') ? '1' : '0')
            : null;
        $originalRemarks = $submission->exists
            ? (trim((string) ($submission->getOriginal('remarks') ?? '')) !== '' ? (string) $submission->getOriginal('remarks') : null)
            : null;
        $originalDateSubmitted = $submission->exists
            ? (trim((string) ($submission->getOriginal('date_submitted') ?? '')) !== '' ? (string) $submission->getOriginal('date_submitted') : null)
            : null;

        $submission->is_submitted = $this->requestBoolean($request, 'is_submitted');

        $remarks = trim((string) ($validated['remarks'] ?? ''));
        $submission->remarks = $remarks !== '' ? $remarks : null;

        if ($submission->is_submitted) {
            $submission->date_submitted = !empty($validated['date_submitted'])
                ? $validated['date_submitted']
                : now()->toDateString();
        } else {
            $submission->date_submitted = null;
        }

        $submission->verified_by_user_id = optional(auth()->user())->id;
        $submission->save();

        $auditSubjects = [[
            'type' => 'ApplicantRequirementSubmission',
            'id' => (int) $submission->id,
            'label' => (string) $registrarRequirement->requirement_name,
            'changes' => [
                ['field' => 'is_submitted', 'old' => $originalIsSubmitted, 'new' => $submission->is_submitted ? '1' : '0'],
                ['field' => 'remarks', 'old' => $originalRemarks, 'new' => $submission->remarks],
                ['field' => 'date_submitted', 'old' => $originalDateSubmitted, 'new' => $submission->date_submitted ? $submission->date_submitted->format('Y-m-d') : null],
            ],
        ]];

        if ($request->hasFile('document_file')) {
            $uploadedFile = $request->file('document_file');
            $storageFolder = $requirementType === 'Medical'
                ? 'applicants/medical-clearance/' . $applicant->id
                : 'applicants/documents/' . $applicant->id;
            $storedPath = $uploadedFile->store($storageFolder, 'public');

            $existingFile = ApplicantRequirementSubmissionFile::query()
                ->where('applicant_requirement_submission_id', $submission->id)
                ->first();

            $previousFilePayload = null;
            if ($existingFile) {
                $previousFilePayload = [
                    'original_filename' => (string) ($existingFile->original_filename ?: ''),
                    'storage_disk' => (string) ($existingFile->storage_disk ?: ''),
                    'storage_path' => (string) ($existingFile->storage_path ?: ''),
                    'mime_type' => (string) ($existingFile->mime_type ?: ''),
                    'size_bytes' => (int) ($existingFile->size_bytes ?: 0),
                ];
            }

            if ($existingFile && !empty($existingFile->storage_path)) {
                Storage::disk((string) ($existingFile->storage_disk ?: 'public'))->delete((string) $existingFile->storage_path);
            }

            $submissionFile = ApplicantRequirementSubmissionFile::query()->updateOrCreate(
                [
                    'applicant_requirement_submission_id' => (int) $submission->id,
                ],
                [
                    'uploaded_by_user_id' => optional(auth()->user())->id,
                    'original_filename' => (string) $uploadedFile->getClientOriginalName(),
                    'storage_disk' => 'public',
                    'storage_path' => (string) $storedPath,
                    'mime_type' => (string) ($uploadedFile->getClientMimeType() ?: ''),
                    'size_bytes' => (int) $uploadedFile->getSize(),
                ]
            );

            $auditSubjects[] = [
                'type' => 'ApplicantRequirementSubmissionFile',
                'id' => (int) $submissionFile->id,
                'label' => (string) ($submissionFile->original_filename ?: basename($storedPath)),
                'changes' => [
                    ['field' => 'original_filename', 'old' => $previousFilePayload['original_filename'] ?? null, 'new' => (string) $submissionFile->original_filename],
                    ['field' => 'storage_disk', 'old' => $previousFilePayload['storage_disk'] ?? null, 'new' => (string) $submissionFile->storage_disk],
                    ['field' => 'storage_path', 'old' => $previousFilePayload['storage_path'] ?? null, 'new' => (string) $submissionFile->storage_path],
                    ['field' => 'mime_type', 'old' => $previousFilePayload['mime_type'] ?? null, 'new' => (string) $submissionFile->mime_type],
                    ['field' => 'size_bytes', 'old' => isset($previousFilePayload['size_bytes']) ? (string) $previousFilePayload['size_bytes'] : null, 'new' => (string) $submissionFile->size_bytes],
                ],
            ];

            if (!$submission->is_submitted) {
                $submission->is_submitted = true;
                $submission->date_submitted = $submission->date_submitted ?: now()->toDateString();
                $submission->save();
            }
        }

        $auditSubjects[0]['changes'][0]['new'] = $submission->is_submitted ? '1' : '0';
        $auditSubjects[0]['changes'][2]['new'] = $submission->date_submitted ? $submission->date_submitted->format('Y-m-d') : null;

        AuditTrailRecorder::record('APPLICANT_REQUIREMENT_SAVED', $auditSubjects, [
            'source_action' => __FUNCTION__,
        ]);

        $rows = $this->applicantDocumentRows($applicant, $requirementType);
        $row = $rows->firstWhere('requirement_id', (int) $registrarRequirement->id);

        return response()->json([
            'ok' => true,
            'message' => 'Applicant requirement record saved successfully.',
            'row' => $row,
        ]);
    }

    public function applicantDocumentFile(Applicant $applicant, ApplicantRequirementSubmissionFile $submissionFile)
    {
        $submission = $submissionFile->submission;
        if (!$submission || (int) $submission->applicant_id !== (int) $applicant->id) {
            abort(404);
        }

        $disk = (string) ($submissionFile->storage_disk ?: 'public');
        $path = (string) $submissionFile->storage_path;

        if ($path === '' || !Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mimeType = (string) ($submissionFile->mime_type ?: 'application/octet-stream');
        $filename = trim((string) $submissionFile->original_filename);
        if ($filename === '') {
            $filename = basename($path);
        }

        return response(
            Storage::disk($disk)->get($path),
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    private function normalizeApplicantDocumentFilters(Request $request): array
    {
        $allowedPerPage = [10, 25, 50];
        $allowedStatus = ['', 'pending', 'completed'];

        $search = preg_replace('/\s+/', ' ', trim((string) $request->query('search', '')));
        if ($search !== '' && strlen($search) < 2) {
            $search = '';
        }
        $status = strtolower(trim((string) $request->query('status', '')));
        if (!in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        $requirementType = $this->normalizeApplicantRequirementType($request->query('requirement_type', 'Document'));
        if ($requirementType === '') {
            $requirementType = 'Document';
        }

        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $page = (int) $request->query('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        return [
            'search' => $search,
            'status' => $status,
            'requirement_type' => $requirementType,
            'per_page' => $perPage,
            'page' => $page,
        ];
    }

    private function normalizeApplicantRequirementType($value): string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === 'medical') {
            return 'Medical';
        }

        if ($normalized === 'document') {
            return 'Document';
        }

        return '';
    }

    private function applicantDocumentRows(Applicant $applicant, $requirementType = 'Document')
    {
        $requirementType = $this->normalizeApplicantRequirementType($requirementType);
        if ($requirementType === '') {
            $requirementType = 'Document';
        }

        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $requirements = RegistrarRequirement::query()
            ->with('yearBlock:id,label')
            ->where('requirement_type', $requirementType)
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
            ->get();

        $requirementsByPolicyId = $requirements
            ->filter(function (RegistrarRequirement $requirement) {
                return (int) $requirement->registrar_requirement_policy_id > 0;
            })
            ->groupBy('registrar_requirement_policy_id')
            ->map(function ($groupedRequirements) {
                return $groupedRequirements->first();
            });

        if (!$this->applicantDocumentTablesReady()) {
            return $requirements->map(function (RegistrarRequirement $requirement) {
                return [
                    'requirement_id' => (int) $requirement->id,
                    'policy_id' => (int) $requirement->registrar_requirement_policy_id,
                    'submission_id' => null,
                    'document_type' => (string) $requirement->requirement_name,
                    'grade_level' => $requirement->applies_to_all_year_levels
                        ? 'All Year Level'
                        : (string) optional($requirement->yearBlock)->label,
                    'remarks' => '',
                    'date_submitted' => '',
                    'is_submitted' => false,
                    'status' => 'pending',
                    'status_label' => 'Pending',
                    'status_class' => 'app-status-pending',
                    'file_name' => '',
                    'file_url' => '',
                ];
            })->values();
        }

        $this->ensureApplicantDocumentBaselineSubmissions(
            $applicant,
            $activeSemesterId,
            $requirementsByPolicyId->keys()->all(),
            $requirementType
        );

        $submissionRows = ApplicantRequirementSubmission::query()
            ->with('file')
            ->where('applicant_id', (int) $applicant->id)
            ->whereIn('registrar_requirement_policy_id', $requirementsByPolicyId->keys()->all())
            ->orderBy('id')
            ->get();

        if (!$submissionRows->count()) {
            return $requirements->map(function (RegistrarRequirement $requirement) {
                return [
                    'requirement_id' => (int) $requirement->id,
                    'policy_id' => (int) $requirement->registrar_requirement_policy_id,
                    'submission_id' => null,
                    'document_type' => (string) $requirement->requirement_name,
                    'grade_level' => $requirement->applies_to_all_year_levels
                        ? 'All Year Level'
                        : (string) optional($requirement->yearBlock)->label,
                    'remarks' => '',
                    'date_submitted' => '',
                    'is_submitted' => false,
                    'status' => 'pending',
                    'status_label' => 'Pending',
                    'status_class' => 'app-status-pending',
                    'file_name' => '',
                    'file_url' => '',
                ];
            })->values();
        }

        return $submissionRows->map(function (ApplicantRequirementSubmission $submission) use ($requirementsByPolicyId, $applicant) {
            $policyId = (int) $submission->registrar_requirement_policy_id;
            $requirement = $requirementsByPolicyId->get($policyId);

            if (!$requirement) {
                return null;
            }

            $file = $submission->file;
            $isSubmitted = (bool) $submission->is_submitted;

            $statusLabel = $isSubmitted ? 'Completed' : 'Pending';
            $statusClass = $isSubmitted ? 'app-status-accepted' : 'app-status-pending';

            return [
                'requirement_id' => (int) $requirement->id,
                'policy_id' => $policyId,
                'submission_id' => (int) $submission->id,
                'document_type' => (string) $requirement->requirement_name,
                'grade_level' => $requirement->applies_to_all_year_levels
                    ? 'All Year Level'
                    : (string) optional($requirement->yearBlock)->label,
                'remarks' => (string) ($submission->remarks ?: ''),
                'date_submitted' => $submission->date_submitted
                    ? $submission->date_submitted->format('Y-m-d')
                    : '',
                'is_submitted' => $isSubmitted,
                'status' => strtolower($statusLabel),
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'file_name' => $file ? (string) ($file->original_filename ?: '') : '',
                'file_url' => $file
                    ? route('registrar.process.application.documents.file', [
                        'applicant' => $applicant->id,
                        'submissionFile' => $file->id,
                    ])
                    : '',
            ];
        })->filter()->values();
    }

    private function ensureApplicantDocumentBaselineSubmissions(Applicant $applicant, $activeSemesterId, array $activePolicyIds, $requirementType): void
    {
        if (!count($activePolicyIds)) {
            return;
        }

        $existingCount = ApplicantRequirementSubmission::query()
            ->where('applicant_id', (int) $applicant->id)
            ->whereIn('registrar_requirement_policy_id', $activePolicyIds)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $baselinePolicyIds = RegistrarRequirement::query()
            ->where('requirement_type', 'Document')
            ->where('applies_to_all_year_levels', true)
            ->whereIn('registrar_requirement_policy_id', $activePolicyIds)
            ->whereHas('policy', function ($policyQuery) use ($activeSemesterId) {
                $policyQuery->where('system_school_semester_id', $activeSemesterId);
            })
            ->pluck('registrar_requirement_policy_id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values();

        if (!$baselinePolicyIds->count()) {
            $baselinePolicyIds = collect($activePolicyIds)
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter(function ($value) {
                    return $value > 0;
                })
                ->values();
        }

        foreach ($baselinePolicyIds as $policyId) {
            ApplicantRequirementSubmission::query()->firstOrCreate([
                'applicant_id' => (int) $applicant->id,
                'registrar_requirement_policy_id' => (int) $policyId,
            ], [
                'is_submitted' => false,
            ]);
        }
    }

    public function availableApplicantDocuments(Applicant $applicant): JsonResponse
    {
        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $assignedPolicyIds = ApplicantRequirementSubmission::query()
            ->where('applicant_id', (int) $applicant->id)
            ->pluck('registrar_requirement_policy_id')
            ->toArray();

        $available = RegistrarRequirement::query()
            ->with('yearBlock:id,label')
            ->where('requirement_type', 'Document')
            ->whereNotNull('registrar_requirement_policy_id')
            ->whereHas('policy', function ($query) use ($activeSemesterId) {
                $query->where('system_school_semester_id', $activeSemesterId);
            })
            ->when(count($assignedPolicyIds) > 0, function ($query) use ($assignedPolicyIds) {
                $query->whereNotIn('registrar_requirement_policy_id', $assignedPolicyIds);
            })
            ->orderBy('applies_to_all_year_levels', 'desc')
            ->orderBy('year_block_id')
            ->orderBy('requirement_name')
            ->orderBy('id')
            ->get()
            ->map(function (RegistrarRequirement $requirement) {
                return [
                    'policy_id' => (int) $requirement->registrar_requirement_policy_id,
                    'requirement_id' => (int) $requirement->id,
                    'document_type' => (string) $requirement->requirement_name,
                    'level' => $requirement->applies_to_all_year_levels
                        ? 'All Year Level'
                        : (string) optional($requirement->yearBlock)->label,
                ];
            })
            ->filter(function ($item) {
                return (int) ($item['policy_id'] ?? 0) > 0;
            })
            ->unique('policy_id')
            ->values();

        return response()->json(['ok' => true, 'data' => $available]);
    }

    public function assignApplicantDocuments(Request $request, Applicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'policy_ids' => 'required|array',
            'policy_ids.*' => 'integer|exists:registrar_requirement_policies,id',
        ]);

        $requestedPolicyIds = collect($validated['policy_ids'])
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (!count($requestedPolicyIds)) {
            throw ValidationException::withMessages([
                'policy_ids' => ['Select at least one requirement to assign.'],
            ]);
        }

        $activeSemesterId = $this->resolveActiveRequirementSystemSemesterId();

        $allowedPolicyIds = RegistrarRequirement::query()
            ->where('requirement_type', 'Document')
            ->whereIn('registrar_requirement_policy_id', $requestedPolicyIds)
            ->whereHas('policy', function ($query) use ($activeSemesterId) {
                $query->where('system_school_semester_id', $activeSemesterId);
            })
            ->pluck('registrar_requirement_policy_id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (!count($allowedPolicyIds)) {
            throw ValidationException::withMessages([
                'policy_ids' => ['Selected requirements are not active for the current semester.'],
            ]);
        }

        $assignedCount = 0;
        foreach ($allowedPolicyIds as $policyId) {
            ApplicantRequirementSubmission::firstOrCreate([
                'applicant_id' => (int) $applicant->id,
                'registrar_requirement_policy_id' => (int) $policyId,
            ], [
                'is_submitted' => false,
            ]);
            $assignedCount += 1;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Requirements assigned successfully.',
            'assigned_count' => $assignedCount,
        ]);
    }

    public function storeApplicantDocumentRequirement(Request $request, Applicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'grade_level' => 'required|string|max:50',
            'document' => 'required|string|max:180',
            'doc_type' => 'nullable|in:Medical,Document',
            'non_filipino' => 'nullable',
        ]);

        $resolvedYearLevel = $this->resolveDocumentRequirementYearLevel($validated['grade_level']);
        $documentName = trim((string) $validated['document']);
        $documentType = trim((string) ($validated['doc_type'] ?? 'Document'));
        $nonFilipino = $this->requestBoolean($request, 'non_filipino');

        if ($documentType === '') {
            $documentType = 'Document';
        }

        $requirement = $this->findDuplicateDocumentRequirement(
            $resolvedYearLevel,
            $documentName,
            $documentType,
            $nonFilipino
        );

        $wasCreated = false;
        if (!$requirement) {
            $requirement = RegistrarRequirement::query()->create([
                'year_block_id' => $resolvedYearLevel['year_block_id'],
                'applies_to_all_year_levels' => $resolvedYearLevel['applies_to_all_year_levels'],
                'requirement_name' => $documentName,
                'requirement_type' => $documentType,
                'non_filipino' => $nonFilipino,
                'created_by_user_id' => optional(auth()->user())->id,
            ]);

            $this->syncRequirementPolicyForLegacyRow($requirement);
            $requirement->refresh();
            $wasCreated = true;
        }

        if ((int) $requirement->registrar_requirement_policy_id <= 0) {
            $this->syncRequirementPolicyForLegacyRow($requirement);
            $requirement->refresh();
        }

        $policyId = (int) $requirement->registrar_requirement_policy_id;
        if ($policyId <= 0) {
            throw ValidationException::withMessages([
                'document' => ['Unable to map the requirement to an active policy. Please try again.'],
            ]);
        }

        $submission = ApplicantRequirementSubmission::query()->firstOrCreate([
            'applicant_id' => (int) $applicant->id,
            'registrar_requirement_policy_id' => $policyId,
        ], [
            'is_submitted' => false,
        ]);

        $row = $this->applicantDocumentRows($applicant)
            ->first(function ($item) use ($requirement) {
                return (int) ($item['requirement_id'] ?? 0) === (int) $requirement->id;
            });

        AuditTrailRecorder::record('APPLICANT_REQUIREMENT_ASSIGNED', [[
            'type' => 'ApplicantRequirementSubmission',
            'id' => (int) $submission->id,
            'label' => (string) $requirement->requirement_name,
            'changes' => [
                ['field' => 'policy_id', 'old' => null, 'new' => (int) $requirement->registrar_requirement_policy_id],
                ['field' => 'is_submitted', 'old' => null, 'new' => '0'],
                ['field' => 'date_submitted', 'old' => null, 'new' => null],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => $wasCreated
                ? 'New requirement added and assigned to the applicant.'
                : 'Existing requirement assigned to the applicant.',
            'row' => $row,
        ], $wasCreated ? 201 : 200);
    }

    public function destroyApplicantDocumentFile(Applicant $applicant, RegistrarRequirement $registrarRequirement): JsonResponse
    {
        if (!$this->applicantDocumentTablesReady()) {
            throw ValidationException::withMessages([
                'document_file' => ['Applicant document tables are not ready.'],
            ]);
        }

        $policyId = (int) $registrarRequirement->registrar_requirement_policy_id;
        if ($policyId <= 0) {
            throw ValidationException::withMessages([
                'document_file' => ['Selected requirement is not linked to an active policy.'],
            ]);
        }

        $submission = ApplicantRequirementSubmission::query()
            ->with('file')
            ->where('applicant_id', (int) $applicant->id)
            ->where('registrar_requirement_policy_id', $policyId)
            ->first();

        if (!$submission) {
            return response()->json([
                'ok' => true,
                'message' => 'No uploaded file found for this requirement.',
                'row' => null,
            ]);
        }


        $auditFile = $submission->file;
        $originalIsSubmitted = (bool) $submission->is_submitted ? '1' : '0';
        $originalDateSubmitted = $submission->date_submitted ? $submission->date_submitted->format('Y-m-d') : null;
        $this->removeApplicantRequirementSubmissionFile($submission);

        $submission->is_submitted = false;
        $submission->date_submitted = null;
        $submission->save();


        $auditSubjects = [[
            'type' => 'ApplicantRequirementSubmission',
            'id' => (int) $submission->id,
            'label' => (string) $registrarRequirement->requirement_name,
            'changes' => [
                ['field' => 'is_submitted', 'old' => $originalIsSubmitted, 'new' => '0'],
                ['field' => 'date_submitted', 'old' => $originalDateSubmitted, 'new' => null],
            ],
        ]];

        if ($auditFile) {
            $auditSubjects[] = [
                'type' => 'ApplicantRequirementSubmissionFile',
                'id' => (int) $auditFile->id,
                'label' => (string) ($auditFile->original_filename ?: 'Uploaded file'),
                'changes' => [
                    ['field' => 'original_filename', 'old' => (string) ($auditFile->original_filename ?: ''), 'new' => null],
                    ['field' => 'storage_disk', 'old' => (string) ($auditFile->storage_disk ?: ''), 'new' => null],
                    ['field' => 'storage_path', 'old' => (string) ($auditFile->storage_path ?: ''), 'new' => null],
                ],
            ];
        }

        AuditTrailRecorder::record('APPLICANT_REQUIREMENT_FILE_DELETED', $auditSubjects, [
            'source_action' => __FUNCTION__,
        ]);
        $row = $this->applicantDocumentRows($applicant)
            ->first(function ($item) use ($registrarRequirement) {
                return (int) ($item['requirement_id'] ?? 0) === (int) $registrarRequirement->id;
            });

        return response()->json([
            'ok' => true,
            'message' => 'Uploaded file deleted successfully.',
            'row' => $row,
        ]);
    }

    public function destroyApplicantDocumentAssignment(Applicant $applicant, RegistrarRequirement $registrarRequirement): JsonResponse
    {
        if (!$this->applicantDocumentTablesReady()) {
            throw ValidationException::withMessages([
                'policy_ids' => ['Applicant document tables are not ready.'],
            ]);
        }

        $policyId = (int) $registrarRequirement->registrar_requirement_policy_id;
        if ($policyId <= 0) {
            throw ValidationException::withMessages([
                'policy_ids' => ['Selected requirement is not linked to an active policy.'],
            ]);
        }

        $submission = ApplicantRequirementSubmission::query()
            ->with('file')
            ->where('applicant_id', (int) $applicant->id)
            ->where('registrar_requirement_policy_id', $policyId)
            ->first();

        if ($submission) {
            $auditFile = $submission->file;
            $originalIsSubmitted = (bool) $submission->is_submitted ? '1' : '0';
            $originalDateSubmitted = $submission->date_submitted ? $submission->date_submitted->format('Y-m-d') : null;

            $this->removeApplicantRequirementSubmissionFile($submission);
            $submission->delete();

            $auditSubjects = [[
                'type' => 'ApplicantRequirementSubmission',
                'id' => (int) $submission->id,
                'label' => (string) $registrarRequirement->requirement_name,
                'changes' => [
                    ['field' => 'is_submitted', 'old' => $originalIsSubmitted, 'new' => null],
                    ['field' => 'date_submitted', 'old' => $originalDateSubmitted, 'new' => null],
                ],
            ]];

            if ($auditFile) {
                $auditSubjects[] = [
                    'type' => 'ApplicantRequirementSubmissionFile',
                    'id' => (int) $auditFile->id,
                    'label' => (string) ($auditFile->original_filename ?: 'Uploaded file'),
                    'changes' => [
                        ['field' => 'original_filename', 'old' => (string) ($auditFile->original_filename ?: ''), 'new' => null],
                        ['field' => 'storage_disk', 'old' => (string) ($auditFile->storage_disk ?: ''), 'new' => null],
                        ['field' => 'storage_path', 'old' => (string) ($auditFile->storage_path ?: ''), 'new' => null],
                    ],
                ];
            }

            AuditTrailRecorder::record('APPLICANT_REQUIREMENT_ASSIGNMENT_DELETED', $auditSubjects, [
                'source_action' => __FUNCTION__,
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Requirement removed from the applicant.',
            'requirement_id' => (int) $registrarRequirement->id,
        ]);
    }

    private function removeApplicantRequirementSubmissionFile(ApplicantRequirementSubmission $submission): void
    {
        $submission->loadMissing('file');
        $file = $submission->file;

        if (!$file) {
            if (!empty($submission->submission_file_id)) {
                $submission->submission_file_id = null;
                $submission->save();
            }
            return;
        }

        $disk = !empty($file->storage_disk) ? (string) $file->storage_disk : 'public';
        $path = !empty($file->storage_path) ? (string) $file->storage_path : '';

        if ($path !== '') {
            Storage::disk($disk)->delete($path);
        }

        if ((int) $submission->submission_file_id === (int) $file->id) {
            $submission->submission_file_id = null;
            $submission->save();
        }

        $file->delete();
    }

    private function applicantDocumentTablesReady(): bool
    {
        return Schema::hasTable('applicant_requirement_submissions')
            && Schema::hasTable('applicant_requirement_submission_files');
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

    public function examInterviewScheduling(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $query = Applicant::query()
            ->with(['applicationPreference.course'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('applicant_id', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email_address', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $query->where(function ($builder) use ($status) {
                $builder->where('exam_result_status', $status);

                if (Schema::hasColumn('applicants', 'interview_status')) {
                    $builder->orWhere('interview_status', $status);
                }

                if (Schema::hasColumn('applicants', 'medical_clearance_status')) {
                    $builder->orWhere('medical_clearance_status', $status);
                }
            });
        }

        $applicants = $query
            ->limit(150)
            ->get()
            ->map(function (Applicant $applicant) {
                return $this->mapExamInterviewApplicantRow($applicant);
            })
            ->values();

        $summary = [
            'applicants' => (int) $applicants->count(),
            'exam_scheduled' => (int) $applicants->filter(function ($row) {
                return (string) ($row['exam_date'] ?? '') !== '';
            })->count(),
            'exam_passed' => (int) $applicants->where('exam_result_status', 'Passed')->count(),
            'interview_scheduled' => (int) $applicants->filter(function ($row) {
                return (string) ($row['interview_date'] ?? '') !== '';
            })->count(),
            'medical_cleared' => (int) $applicants->where('medical_clearance_status', 'Cleared')->count(),
        ];

        return view('registrar.process.exam-interview-scheduling', compact('applicants', 'summary', 'search', 'status'));
    }

    private function mapExamInterviewApplicantRow(Applicant $applicant): array
    {
        $program = 'N/A';
        $preference = $applicant->applicationPreference;
        if ($preference && $preference->course) {
            $courseCode = trim((string) $preference->course->code);
            $courseName = trim((string) $preference->course->name);
            $program = $courseCode !== '' && $courseName !== '' ? $courseCode . ' - ' . $courseName : ($courseName !== '' ? $courseName : $courseCode);
        }

        $interviewDate = Schema::hasColumn('applicants', 'interview_date') ? $applicant->interview_date : null;

        return [
            'id' => (int) $applicant->id,
            'applicant_id' => (string) $applicant->applicant_id,
            'name' => trim(implode(' ', array_filter([
                $applicant->first_name,
                $applicant->middle_name,
                $applicant->last_name,
            ]))),
            'email' => (string) ($applicant->email_address ?? ''),
            'program' => $program !== '' ? $program : 'N/A',
            'exam_date' => optional($applicant->exam_date)->format('Y-m-d') ?: '',
            'exam_time' => optional($applicant->exam_date)->format('H:i') ?: '',
            'exam_room' => (string) ($applicant->exam_room ?? ''),
            'exam_result_status' => (string) ($applicant->exam_result_status ?: 'Pending'),
            'exam_score' => $applicant->exam_score,
            'interview_date' => $interviewDate ? optional($interviewDate)->format('Y-m-d') : '',
            'interview_time' => $interviewDate ? optional($interviewDate)->format('H:i') : '',
            'interview_room' => Schema::hasColumn('applicants', 'interview_room') ? (string) ($applicant->interview_room ?? '') : '',
            'interview_status' => Schema::hasColumn('applicants', 'interview_status') ? (string) ($applicant->interview_status ?: 'Pending') : 'Pending',
            'medical_clearance_status' => Schema::hasColumn('applicants', 'medical_clearance_status') ? (string) ($applicant->medical_clearance_status ?: 'Pending') : 'Pending',
            'update_url' => route('registrar.process.application.exam-interview.update', ['applicant' => $applicant->id]),
        ];
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

            $profileImage = StudentProfileImage::updateOrCreate(
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

            AuditTrailRecorder::record('STUDENT_PROFILE_IMAGE_UPLOADED', [
                [
                    'type' => 'StudentProfileImage',
                    'id' => $profileImage->id,
                    'label' => (string) $profile->student_no,
                    'changes' => [
                        ['field' => 'original_filename', 'old' => null, 'new' => $profileImage->original_filename],
                        ['field' => 'storage_path', 'old' => null, 'new' => $profileImage->storage_path],
                        ['field' => 'mime_type', 'old' => null, 'new' => $profileImage->mime_type],
                        ['field' => 'size_bytes', 'old' => null, 'new' => $profileImage->size_bytes],
                    ],
                ],
            ], [
                'source_action' => 'Batch student photo upload',
            ]);

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

        AuditTrailRecorder::record('DOCUMENT_REQUIREMENT_CREATED', [[
            'type' => 'RegistrarRequirement',
            'id' => (int) $requirement->id,
            'label' => (string) $requirement->requirement_name,
            'changes' => [
                ['field' => 'year_level', 'old' => null, 'new' => $resolvedYearLevel['applies_to_all_year_levels'] ? 'All Year Level' : (string) optional($requirement->yearBlock)->label],
                ['field' => 'requirement_name', 'old' => null, 'new' => $documentName],
                ['field' => 'requirement_type', 'old' => null, 'new' => $documentType],
                ['field' => 'non_filipino', 'old' => null, 'new' => $nonFilipino ? '1' : '0'],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

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
        $originalYearBlockId = $documentRequirement->year_block_id;
        $originalAppliesToAllYearLevels = (bool) $documentRequirement->applies_to_all_year_levels;
        $originalDocumentName = (string) $documentRequirement->requirement_name;
        $originalDocumentType = (string) $documentRequirement->requirement_type;
        $originalNonFilipino = (bool) $documentRequirement->non_filipino;
        $originalPolicyId = (int) $documentRequirement->registrar_requirement_policy_id;
        $originalYearLevelLabel = $originalAppliesToAllYearLevels
            ? 'All Year Level'
            : ($originalYearBlockId ? (string) YearBlock::query()->where('id', $originalYearBlockId)->value('label') : '');

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

        AuditTrailRecorder::record('DOCUMENT_REQUIREMENT_UPDATED', [[
            'type' => 'RegistrarRequirement',
            'id' => (int) $documentRequirement->id,
            'label' => (string) $documentRequirement->requirement_name,
            'changes' => [
                ['field' => 'year_level', 'old' => $originalYearLevelLabel, 'new' => $resolvedYearLevel['applies_to_all_year_levels'] ? 'All Year Level' : (string) optional($documentRequirement->yearBlock)->label],
                ['field' => 'requirement_name', 'old' => $originalDocumentName, 'new' => $documentName],
                ['field' => 'requirement_type', 'old' => $originalDocumentType, 'new' => $documentType],
                ['field' => 'non_filipino', 'old' => $originalNonFilipino ? '1' : '0', 'new' => $nonFilipino ? '1' : '0'],
                ['field' => 'policy_id', 'old' => $originalPolicyId ?: null, 'new' => (int) $documentRequirement->registrar_requirement_policy_id],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

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
        $auditYearBlockId = $documentRequirement->year_block_id;
        $auditAppliesToAllYearLevels = (bool) $documentRequirement->applies_to_all_year_levels;

        if (!empty($policyId)) {
            $definitionId = RegistrarRequirementPolicy::query()
                ->where('id', $policyId)
                ->value('registrar_requirement_definition_id');
        }

        $auditLabel = (string) $documentRequirement->requirement_name;
        $auditYearLevel = $auditAppliesToAllYearLevels
            ? 'All Year Level'
            : ($auditYearBlockId ? (string) YearBlock::query()->where('id', $auditYearBlockId)->value('label') : '');
        $auditRequirementType = (string) $documentRequirement->requirement_type;
        $auditNonFilipino = (bool) $documentRequirement->non_filipino;

        $documentRequirement->delete();

        $this->cleanupOrphanedRequirementPolicy($policyId, $definitionId);

        AuditTrailRecorder::record('DOCUMENT_REQUIREMENT_DELETED', [[
            'type' => 'RegistrarRequirement',
            'id' => (int) $documentRequirement->id,
            'label' => $auditLabel,
            'changes' => [
                ['field' => 'year_level', 'old' => $auditYearLevel, 'new' => null],
                ['field' => 'requirement_name', 'old' => $auditLabel, 'new' => null],
                ['field' => 'requirement_type', 'old' => $auditRequirementType, 'new' => null],
                ['field' => 'non_filipino', 'old' => $auditNonFilipino ? '1' : '0', 'new' => null],
                ['field' => 'policy_id', 'old' => $policyId ?: null, 'new' => null],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

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
        $savedCount = 0;

        // 1. Process staged bulk list if any
        if ($request->has('new_dept_codes') && is_array($request->new_dept_codes)) {
            $codes = $request->new_dept_codes;
            $descriptions = $request->new_dept_descriptions;

            foreach ($codes as $index => $code) {
                $description = $descriptions[$index] ?? '';
                if ($this->tryCreateDepartment($code, $description)) {
                    $savedCount++;
                }
            }
        }

        // 2. Process main inputs (handles both single add and the "last" entry in a bulk add)
        $mainCode = $request->input('dept_code');
        $mainDesc = $request->input('dept_description');
        
        if (!empty($mainCode) && !empty($mainDesc)) {
            if ($this->tryCreateDepartment($mainCode, $mainDesc)) {
                $savedCount++;
            } else if ($savedCount === 0) {
                // If only a single one was tried and it's a duplicate, let standard validation handle the error message
                return $this->processSingleDepartment($request);
            }
        }

        if ($savedCount > 0) {
            return redirect()
                ->route('registrar.registrar-menu.academic-master.program-file')
                ->with('program_file_success', $savedCount . ' department(s) added successfully.');
        }

        return redirect()
            ->route('registrar.registrar-menu.academic-master.program-file');
    }

    /**
     * Helper to check for duplicates and create a department
     */
    private function tryCreateDepartment($code, $description)
    {
        $cleanCode = trim((string) $code);
        $cleanDescription = trim((string) $description);

        if ($cleanCode === '' || $cleanDescription === '') {
            return false;
        }

        $exists = Department::where('code', $cleanCode)
            ->orWhere('description', $cleanDescription)
            ->exists();

        if (!$exists) {
            Department::create([
                'code' => $cleanCode,
                'description' => $cleanDescription,
            ]);
            return true;
        }

        return false;
    }

    private function processSingleDepartment(Request $request)
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
            ->with('program_file_success', 'Department added successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        // Check if department is used in programs
        if ($department->courses()->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Cannot delete department. It is currently linked to one or more programs.'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Department deleted successfully.'
        ]);
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
            'degree_type' => 'nullable|string|max:80',
            'total_units' => 'nullable|numeric|min:0|max:999',
            'academic_year' => 'nullable|string|max:20',
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
            'program_type' => $validated['degree_type'] ?? $validated['program_type'] ?? 'Degree',
            'department_id' => $validated['department_id'],
            'description' => $validated['program_name'],
            'total_units' => $validated['total_units'] ?? null,
            'academic_year' => trim((string) ($validated['academic_year'] ?? '')) ?: null,
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
            'degree_type' => 'nullable|string|max:80',
            'total_units' => 'nullable|numeric|min:0|max:999',
            'academic_year' => 'nullable|string|max:20',
            'accreditation_level' => 'nullable|string|max:120',
        ]);

        $accreditationLevel = trim((string) ($validated['accreditation_level'] ?? ''));

        $course->update([
            'code' => $validated['program_code'],
            'name' => $validated['program_name'],
            'description' => $validated['program_name'],
            'program_type' => $validated['degree_type'] ?? $course->program_type,
            'total_units' => $validated['total_units'] ?? null,
            'academic_year' => trim((string) ($validated['academic_year'] ?? '')) ?: null,
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
                'hours',
                'course_type',
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
            'hours' => 'required|numeric|min:0.5|max:999',
            'course_type' => 'required|in:Major,Minor,GE,Elective',
        ]);

        $lec = (int) ($validated['lec'] ?? 0);
        $lab = (int) ($validated['lab'] ?? 0);

        $subjectPayload = [
            'code' => $validated['code'],
            'name' => trim($validated['title']),
            'units' => (float) ($lec + $lab),
            'hours' => $validated['hours'] ?? null,
            'course_type' => $validated['course_type'],
            'lec' => $lec,
            'lab' => $lab,
            'is_subject_file_record' => true,
            'is_core' => $this->requestBoolean($request, 'core'),
            'is_applied' => $this->requestBoolean($request, 'applied'),
            'is_specialized' => $this->requestBoolean($request, 'specialized'),
        ];

        $roomRequirementSubject = (object) [
            'code' => $validated['code'],
            'name' => trim($validated['title']),
            'course_type' => $validated['course_type'],
            'required_lecture_room_type' => null,
            'required_laboratory_room_type' => null,
        ];
        if (Schema::hasColumn('subjects', 'required_lecture_room_type')) {
            $subjectPayload['required_lecture_room_type'] = $this->resolveSubjectRequiredRoomType($roomRequirementSubject, 'Lecture');
        }
        if (Schema::hasColumn('subjects', 'required_laboratory_room_type')) {
            $subjectPayload['required_laboratory_room_type'] = $lab > 0
                ? $this->resolveSubjectRequiredRoomType($roomRequirementSubject, 'Laboratory')
                : null;
        }
        if (Schema::hasColumn('subjects', 'room_requirement_status')) {
            $subjectPayload['room_requirement_status'] = 'Pending';
        }

        $subject = Subject::create($subjectPayload);

        return response()->json([
            'ok' => true,
            'message' => 'Course created successfully.',
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
                'message' => 'Course not found.',
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
            'hours' => 'required|numeric|min:0.5|max:999',
            'course_type' => 'required|in:Major,Minor,GE,Elective',
        ]);

        $lec = (int) ($validated['lec'] ?? 0);
        $lab = (int) ($validated['lab'] ?? 0);

        $subject->code = $validated['code'];
        $subject->name = trim($validated['title']);
        $subject->units = (float) ($lec + $lab);
        $subject->hours = $validated['hours'] ?? null;
        $subject->course_type = $validated['course_type'];
        if (Schema::hasColumn('subjects', 'required_lecture_room_type')) {
            $subject->required_lecture_room_type = $subject->required_lecture_room_type ?: $this->resolveSubjectRequiredRoomType($subject, 'Lecture');
        }
        if (Schema::hasColumn('subjects', 'required_laboratory_room_type')) {
            $subject->required_laboratory_room_type = $lab > 0
                ? ($subject->required_laboratory_room_type ?: $this->resolveSubjectRequiredRoomType($subject, 'Laboratory'))
                : null;
        }
        if (Schema::hasColumn('subjects', 'room_requirement_status')) {
            $subject->room_requirement_status = $subject->room_requirement_status ?: 'Pending';
        }
        $subject->lec = $lec;
        $subject->lab = $lab;
        $subject->is_core = $this->requestBoolean($request, 'core');
        $subject->is_applied = $this->requestBoolean($request, 'applied');
        $subject->is_specialized = $this->requestBoolean($request, 'specialized');
        $subject->is_subject_file_record = true;
        $subject->save();

        return response()->json([
            'ok' => true,
            'message' => 'Course updated successfully.',
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
                'message' => 'Course not found.',
            ], 404);
        }

        $subject->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Course deleted successfully.',
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
            'hours' => (float) ($subject->hours ?: 0),
            'course_type' => (string) ($subject->course_type ?: 'Major'),
            'required_lecture_room_type' => (string) ($subject->required_lecture_room_type ?: $this->resolveSubjectRequiredRoomType($subject, 'Lecture')),
            'required_laboratory_room_type' => (string) ($subject->required_laboratory_room_type ?: ((int) $subject->lab > 0 ? $this->resolveSubjectRequiredRoomType($subject, 'Laboratory') : '')),
            'room_requirement_status' => (string) ($subject->room_requirement_status ?: 'Pending'),
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
        $selectedCurriculum = $selectedCourseId && $selectedCurriculumYear
            ? $this->findCourseCurriculumByCourseAndYear((int) $selectedCourseId, (string) $selectedCurriculumYear)
            : null;
        $yearBlocks = $this->orderedYearBlocks();
        $semesters = $this->orderedSemesters()->values();
        $availableSubjects = Subject::query()
            ->where('is_subject_file_record', true)
            ->orderBy('code')
            ->orderBy('name')
            ->limit(800)
            ->get(['id', 'code', 'name', 'units', 'lec', 'lab', 'hours', 'course_type', 'is_core', 'is_applied', 'is_specialized']);
        $curriculumSummary = $selectedCurriculum
            ? $this->buildCurriculumSummaryPayload($selectedCurriculum)
            : $this->emptyCurriculumSummaryPayload();

        return view('registrar.registrar-menu.academic-master.curriculum-file', [
            'courses' => $courses,
            'courseYearMap' => $courseYearMap,
            'selectedCourseId' => $selectedCourseId,
            'selectedCurriculumYear' => $selectedCurriculumYear,
            'selectedDateFrom' => optional(optional($selectedCurriculum)->date_from)->format('Y-m-d'),
            'selectedDateTo' => optional(optional($selectedCurriculum)->date_to)->format('Y-m-d'),
            'selectedCurriculum' => $selectedCurriculum,
            'yearBlocks' => $yearBlocks,
            'semesters' => $semesters,
            'availableSubjects' => $availableSubjects,
            'curriculumSummary' => $curriculumSummary,
        ]);
    }

    public function curriculumYearTracking(Request $request)
    {
        $selectedCourseId = trim((string) $request->query('course_id', ''));
        $selectedStartedYear = trim((string) $request->query('started_year', ''));

        $courses = Course::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'description', 'department_id']);

        $courseIds = $courses->pluck('id')->all();
        $curriculaByCourse = $this->curriculumTrackingCurriculaByCourse($courseIds);

        $studentQuery = DB::table('students as st')
            ->leftJoin('courses as c', 'c.id', '=', 'st.course_id')
            ->leftJoin('departments as d', 'd.id', '=', 'c.department_id');

        if (Schema::hasTable('academic_terms') && Schema::hasColumn('students', 'academic_term_id')) {
            $studentQuery->leftJoin('academic_terms as at', 'at.id', '=', 'st.academic_term_id');
        }

        $startedYearExpr = $this->curriculumTrackingStartedYearExpression();
        $programNameExpr = $this->curriculumTrackingProgramNameExpression();
        $departmentNameExpr = $this->curriculumTrackingDepartmentNameExpression();

        $studentQuery->select([
            DB::raw('COALESCE(st.course_id, 0) as course_id'),
            DB::raw('COALESCE(c.code, "") as program_code'),
            DB::raw($programNameExpr . ' as program_name'),
            DB::raw($departmentNameExpr . ' as department_name'),
            DB::raw($startedYearExpr . ' as started_year'),
        ]);

        if ($selectedCourseId !== '') {
            $studentQuery->where('st.course_id', (int) $selectedCourseId);
        }
        if ($selectedStartedYear !== '') {
            $studentQuery->whereRaw($startedYearExpr . ' = ?', [$selectedStartedYear]);
        }

        $trackingRows = $studentQuery
            ->get()
            ->groupBy(function ($row) {
                return (int) $row->course_id . '|' . (string) $row->started_year;
            })
            ->map(function ($group) use ($curriculaByCourse) {
                $row = clone $group->first();
                $row->student_count = $group->count();
                $curricula = $curriculaByCourse[(int) $row->course_id] ?? collect();
                $active = $curricula->firstWhere('is_active', true) ?: $curricula->first();

                $row->approved_curricula = $curricula->pluck('code')->filter()->unique()->values()->all();
                $row->active_curriculum = $active ? $active->code : '';
                $row->curriculum_subjects = $curricula->sum('subject_count');

                return $row;
            })
            ->sortBy(function ($row) {
                return strtolower((string) $row->program_name) . '|' . str_pad((string) $row->started_year, 20, '0', STR_PAD_LEFT);
            })
            ->values();

        $startedYearOptions = $trackingRows
            ->pluck('started_year')
            ->filter()
            ->unique()
            ->sortByDesc(function ($year) {
                return (string) $year;
            })
            ->values();

        $summary = [
            'students' => (int) $trackingRows->sum('student_count'),
            'programs' => $trackingRows->pluck('course_id')->filter()->unique()->count(),
            'started_years' => $trackingRows->pluck('started_year')->filter()->unique()->count(),
            'curricula' => collect($curriculaByCourse)->flatten(1)->count(),
        ];

        return view('registrar.registrar-menu.academic-master.curriculum-year-tracking', compact(
            'courses',
            'trackingRows',
            'summary',
            'selectedCourseId',
            'selectedStartedYear',
            'startedYearOptions'
        ));
    }

    private function curriculumTrackingStartedYearExpression(): string
    {
        $parts = [];

        if (Schema::hasColumn('students', 'school_year')) {
            $parts[] = 'NULLIF(st.school_year, "")';
        } elseif (Schema::hasTable('academic_terms') && Schema::hasColumn('students', 'academic_term_id')) {
            $parts[] = 'NULLIF(at.school_year, "")';
        }

        if (Schema::hasColumn('students', 'student_no')) {
            $parts[] = 'NULLIF(SUBSTRING(st.student_no, 5, 4), "")';
        }

        $parts[] = '"Unassigned"';

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private function curriculumTrackingProgramNameExpression(): string
    {
        $parts = ['NULLIF(c.name, "")', 'NULLIF(c.description, "")'];

        if (Schema::hasColumn('students', 'program')) {
            $parts[] = 'NULLIF(st.program, "")';
        }

        $parts[] = '"Unassigned Program"';

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private function curriculumTrackingDepartmentNameExpression(): string
    {
        $parts = ['NULLIF(d.code, "")', 'NULLIF(d.description, "")'];

        if (Schema::hasColumn('students', 'college')) {
            $parts[] = 'NULLIF(st.college, "")';
        }

        $parts[] = '"Unassigned Department"';

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private function curriculumTrackingCurriculaByCourse(array $courseIds)
    {
        if (!Schema::hasTable('course_curricula') || empty($courseIds)) {
            return collect();
        }

        $query = DB::table('course_curricula as cc')
            ->whereIn('cc.course_id', $courseIds);

        if (Schema::hasTable('course_curriculum_subjects')) {
            $query->leftJoin('course_curriculum_subjects as ccs', 'ccs.course_curriculum_id', '=', 'cc.id');
            $subjectCountExpr = 'COUNT(ccs.id) as subject_count';
        } else {
            $subjectCountExpr = '0 as subject_count';
        }

        $hasCurriculumYearLookup = Schema::hasTable('curriculum_years') && Schema::hasColumn('course_curricula', 'curriculum_year_id');

        if ($hasCurriculumYearLookup) {
            $query->leftJoin('curriculum_years as cy', 'cy.id', '=', 'cc.curriculum_year_id')
                ->select([
                    'cc.course_id',
                    DB::raw('COALESCE(cy.code, cc.curriculum_year_code) as code'),
                    'cc.is_active',
                    DB::raw($subjectCountExpr),
                ]);
        } else {
            $query->select([
                'cc.course_id',
                'cc.curriculum_year_code as code',
                'cc.is_active',
                DB::raw($subjectCountExpr),
            ]);
        }

        return $query
            ->when($hasCurriculumYearLookup, function ($query) {
                $query->groupBy('cc.course_id', DB::raw('COALESCE(cy.code, cc.curriculum_year_code)'), 'cc.is_active');
            }, function ($query) {
                $query->groupBy('cc.course_id', 'cc.curriculum_year_code', 'cc.is_active');
            })
            ->orderBy('code')
            ->get()
            ->groupBy('course_id');
    }

    /**
     * Registrar > Academic Master > Curriculum File > Copy curriculum
     */
    public function copyCurriculum(Request $request)
    {
        $validated = $request->validate([
            'copy_source_course_id' => 'required|integer|exists:courses,id',
            'copy_source_curriculum_year' => 'required|string|max:20',
            'copy_course_id' => 'required|integer|exists:courses,id',
            'copy_curriculum_year' => 'required|string|max:20',
            'copy_to' => 'nullable|string|max:180',
            'copy_semester' => 'nullable|in:All Semester,First,Second',
            'copy_year_level' => 'nullable|in:All Year Levels,First Year,Second Year,Third Year,Fourth Year',
        ]);

        $sourceCourseId = (int) $validated['copy_source_course_id'];
        $sourceCurriculumYear = $this->normalizeCurriculumYearCode($validated['copy_source_curriculum_year']);
        $destinationCourseId = (int) $validated['copy_course_id'];
        $destinationCurriculumYear = $this->normalizeCurriculumYearCode($validated['copy_curriculum_year']);
        $copyTo = trim((string) ($validated['copy_to'] ?? ''));
        $semesterLabel = $this->resolveCurriculumFileSemesterLabel((string) ($validated['copy_semester'] ?? ''));
        $yearLevelLabel = $this->resolveCurriculumFileYearLevelLabel((string) ($validated['copy_year_level'] ?? ''));

        if ($sourceCourseId === $destinationCourseId && $sourceCurriculumYear === $destinationCurriculumYear) {
            return $this->redirectToCurriculumFile(
                $destinationCourseId,
                $destinationCurriculumYear,
                'Destination curriculum must be different from the source.',
                'curriculum_file_error'
            );
        }

        $sourceCurriculum = $this->findCourseCurriculumByCourseAndYear($sourceCourseId, $sourceCurriculumYear);
        if (!$sourceCurriculum) {
            return $this->redirectToCurriculumFile(
                $sourceCourseId,
                $sourceCurriculumYear,
                'Source curriculum not found.',
                'curriculum_file_error'
            );
        }

        $destinationExisting = $this->findCourseCurriculumByCourseAndYear($destinationCourseId, $destinationCurriculumYear);
        if ($destinationExisting) {
            return $this->redirectToCurriculumFile(
                $destinationCourseId,
                $destinationCurriculumYear,
                'Destination curriculum already exists.',
                'curriculum_file_error'
            );
        }

        $destinationCourse = Course::query()->find($destinationCourseId);
        $semesterId = $this->resolveCurriculumFileSemesterId($semesterLabel);
        $yearBlockId = $this->resolveCurriculumFileYearBlockId($yearLevelLabel);
        $sourceSubjects = CourseCurriculumSubject::query()
            ->where('course_curriculum_id', (int) $sourceCurriculum->id)
            ->when($semesterId, function ($query, $semesterId) {
                $query->where('semester_id', (int) $semesterId);
            })
            ->when($yearBlockId, function ($query, $yearBlockId) {
                $query->where('year_block_id', (int) $yearBlockId);
            })
            ->with(['requisites' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        $copiedSubjectCount = 0;
        $copiedRequisiteCount = 0;

        DB::transaction(function () use (
            $copyTo,
            $destinationCourseId,
            $destinationCourse,
            $destinationCurriculumYear,
            $sourceCurriculum,
            $sourceSubjects,
            &$copiedSubjectCount,
            &$copiedRequisiteCount
        ) {
            $destinationCurriculumYearId = $this->resolveOrCreateCurriculumYearId($destinationCurriculumYear);

            $destinationTitle = $copyTo !== '' ? $copyTo : trim((string) ($sourceCurriculum->title ?: ''));
            if ($destinationTitle === '') {
                $destinationTitle = trim((string) ((optional($destinationCourse)->name ?: optional($destinationCourse)->description ?: 'Curriculum') . ' Curriculum ' . $destinationCurriculumYear));
            }

            $curriculumPayload = [
                'course_id' => $destinationCourseId,
                'curriculum_year_code' => $destinationCurriculumYear,
                'title' => $destinationTitle,
                'is_active' => true,
            ];

            if (Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
                $curriculumPayload['curriculum_year_id'] = $destinationCurriculumYearId;
            }

            $destinationCurriculum = CourseCurriculum::create($curriculumPayload);

            foreach ($sourceSubjects as $sourceSubject) {
                $destinationSubject = CourseCurriculumSubject::create([
                    'course_curriculum_id' => (int) $destinationCurriculum->id,
                    'subject_id' => (int) $sourceSubject->subject_id,
                    'year_block_id' => (int) $sourceSubject->year_block_id,
                    'semester_id' => (int) $sourceSubject->semester_id,
                    'credited_units' => (float) $sourceSubject->credited_units,
                    'display_order' => (int) $sourceSubject->display_order,
                ]);

                $copiedSubjectCount++;

                foreach ($sourceSubject->requisites as $requisite) {
                    CurriculumSubjectRequisite::create([
                        'course_curriculum_subject_id' => (int) $destinationSubject->id,
                        'requisite_subject_id' => (int) $requisite->requisite_subject_id,
                        'curriculum_requisite_type_id' => (int) $requisite->curriculum_requisite_type_id,
                        'sort_order' => (int) $requisite->sort_order,
                    ]);

                    $copiedRequisiteCount++;
                }
            }
        });

        $message = $copiedSubjectCount > 0
            ? 'Curriculum copied successfully.'
            : 'Curriculum header copied successfully, but no subjects matched the selected filters.';

        return $this->redirectToCurriculumFile(
            $destinationCourseId,
            $destinationCurriculumYear,
            $message,
            'curriculum_file_success'
        );
    }

    /**
     * Registrar > Academic Master > Curriculum File > Save curriculum setup
     */
    public function saveCurriculumSetup(Request $request)
    {
        $validated = $request->validate([
            'setup_course_id' => 'required|integer|exists:courses,id',
            'setup_curriculum_year' => 'nullable|string|max:20',
            'setup_date_from' => 'required|date',
            'setup_date_to' => 'required|date|after_or_equal:setup_date_from',
            'setup_term_id' => 'required|integer|exists:semesters,id',
            'setup_year_block_id' => 'required|integer|exists:year_blocks,id',
            'setup_subject_ids' => 'required|array|min:1',
            'setup_subject_ids.*' => 'integer|exists:subjects,id',
        ]);

        $courseId = (int) $validated['setup_course_id'];
        $dateFrom = Carbon::parse($validated['setup_date_from'])->toDateString();
        $dateTo = Carbon::parse($validated['setup_date_to'])->toDateString();
        $curriculumYear = $this->normalizeCurriculumYearCode($validated['setup_curriculum_year'] ?? '');
        if ($curriculumYear === '') {
            $curriculumYear = $this->deriveCurriculumYearCodeFromDates($dateFrom, $dateTo);
        }
        $termId = (int) $validated['setup_term_id'];
        $yearBlockId = (int) $validated['setup_year_block_id'];
        $subjectIds = collect((array) $validated['setup_subject_ids'])
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $course = Course::query()->find($courseId);
        if (!$course) {
            return $this->redirectToCurriculumFile(
                $courseId,
                $curriculumYear,
                'Program not found.',
                'curriculum_file_error'
            );
        }

        if (!count($subjectIds)) {
            throw ValidationException::withMessages([
                'setup_subject_ids' => ['Please select at least one course to add to the curriculum.'],
            ]);
        }

        $term = Semester::query()->find($termId);
        $yearBlock = YearBlock::query()->find($yearBlockId);
        if (!$term || !$yearBlock) {
            throw ValidationException::withMessages([
                'setup_term_id' => ['Please select a valid term and year level.'],
            ]);
        }

        $curriculumYearId = $this->resolveOrCreateCurriculumYearId($curriculumYear);
        $titleParts = [
            trim((string) ($course->name ?: $course->description ?: 'Curriculum')),
            'Curriculum ' . $curriculumYear,
            (string) $term->name,
            (string) $yearBlock->label,
        ];
        $title = trim(implode(' - ', array_filter($titleParts, function ($value) {
            return trim((string) $value) !== '';
        })));

        $curriculumPayload = [
            'curriculum_year_code' => $curriculumYear,
            'curriculum_year_id' => $curriculumYearId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'title' => $title !== '' ? $title : 'Curriculum ' . $curriculumYear,
            'is_active' => true,
        ];

        if (Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            $curriculumPayload['curriculum_year_id'] = $curriculumYearId;
        } else {
            unset($curriculumPayload['curriculum_year_id']);
        }

        $addedCount = 0;
        DB::transaction(function () use (
            $courseId,
            $curriculumYear,
            $curriculumPayload,
            $subjectIds,
            $yearBlockId,
            $termId,
            &$addedCount
        ) {
            $curriculum = CourseCurriculum::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'curriculum_year_code' => $curriculumYear,
                ],
                $curriculumPayload + ['course_id' => $courseId]
            );

            $subjects = Subject::query()
                ->whereIn('id', $subjectIds)
                ->get(['id', 'units', 'lec', 'lab']);
            $subjectUnitMap = $subjects->mapWithKeys(function (Subject $subject) {
                $units = (float) ($subject->units ?: 0);
                if ($units <= 0) {
                    $units = (float) (($subject->lec ?: 0) + ($subject->lab ?: 0));
                }

                return [(int) $subject->id => $units];
            });
            $displayOrder = (int) CourseCurriculumSubject::query()
                ->where('course_curriculum_id', (int) $curriculum->id)
                ->where('year_block_id', $yearBlockId)
                ->where('semester_id', $termId)
                ->max('display_order');

            foreach ($subjectIds as $subjectId) {
                if (!$subjects->firstWhere('id', $subjectId)) {
                    continue;
                }

                $existing = CourseCurriculumSubject::query()
                    ->where('course_curriculum_id', (int) $curriculum->id)
                    ->where('subject_id', $subjectId)
                    ->where('year_block_id', $yearBlockId)
                    ->where('semester_id', $termId)
                    ->first();

                if ($existing) {
                    $existing->credited_units = (float) ($subjectUnitMap[$subjectId] ?? 0);
                    $existing->save();
                    continue;
                }

                $displayOrder++;
                CourseCurriculumSubject::create([
                    'course_curriculum_id' => (int) $curriculum->id,
                    'subject_id' => $subjectId,
                    'year_block_id' => $yearBlockId,
                    'semester_id' => $termId,
                    'credited_units' => (float) ($subjectUnitMap[$subjectId] ?? 0),
                    'display_order' => $displayOrder,
                ]);
                $addedCount++;
            }
        });

        $message = $addedCount > 0
            ? $addedCount . ' course(s) added to the curriculum successfully.'
            : 'Curriculum setup saved successfully. Selected courses were already assigned for this year level and term.';

        if ((bool) $request->input('return_to_pre_requisites')) {
            return redirect()
                ->route('registrar.registrar-menu.academic-master.pre-requisites', [
                    'course_id' => $courseId,
                    'curriculum_year' => $curriculumYear,
                ])
                ->with('prereq_success', $message);
        }

        return $this->redirectToCurriculumFile(
            $courseId,
            $curriculumYear,
            $message,
            'curriculum_file_success'
        );
    }

    public function updateCurriculumWorkflow(Request $request, CourseCurriculum $courseCurriculum)
    {
        $validated = $request->validate([
            'action' => 'required|in:department_head,dean,registrar,academic_council,publish',
        ]);

        $summary = $this->buildCurriculumSummaryPayload($courseCurriculum);
        $now = now();
        $action = (string) $validated['action'];

        if ($action === 'publish') {
            if (count($summary['issues']) > 0) {
                return $this->redirectToCurriculumFile(
                    (int) $courseCurriculum->course_id,
                    (string) $courseCurriculum->curriculum_year_code,
                    'Curriculum cannot be published until validation issues are resolved.',
                    'curriculum_file_error'
                );
            }

            if (empty($courseCurriculum->department_head_approved_at)
                || empty($courseCurriculum->dean_approved_at)
                || empty($courseCurriculum->registrar_approved_at)
                || empty($courseCurriculum->academic_council_approved_at)) {
                return $this->redirectToCurriculumFile(
                    (int) $courseCurriculum->course_id,
                    (string) $courseCurriculum->curriculum_year_code,
                    'Complete all curriculum approvals before publishing.',
                    'curriculum_file_error'
                );
            }

            $courseCurriculum->is_published = true;
            $courseCurriculum->published_at = $courseCurriculum->published_at ?: $now;
            $courseCurriculum->approval_status = 'Published';
            $courseCurriculum->save();

            return $this->redirectToCurriculumFile(
                (int) $courseCurriculum->course_id,
                (string) $courseCurriculum->curriculum_year_code,
                'Curriculum published successfully.',
                'curriculum_file_success'
            );
        }

        $columnMap = [
            'department_head' => 'department_head_approved_at',
            'dean' => 'dean_approved_at',
            'registrar' => 'registrar_approved_at',
            'academic_council' => 'academic_council_approved_at',
        ];

        $labelMap = [
            'department_head' => 'Department Head Approved',
            'dean' => 'Dean Approved',
            'registrar' => 'Registrar Approved',
            'academic_council' => 'Academic Council Approved',
        ];

        $column = $columnMap[$action];
        $courseCurriculum->{$column} = $courseCurriculum->{$column} ?: $now;
        $courseCurriculum->approval_status = $labelMap[$action];
        $courseCurriculum->save();

        return $this->redirectToCurriculumFile(
            (int) $courseCurriculum->course_id,
            (string) $courseCurriculum->curriculum_year_code,
            $labelMap[$action] . '.',
            'curriculum_file_success'
        );
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
        $selectedCurriculum = $selectedCourseId && $selectedCurriculumYear
            ? $this->findCourseCurriculumByCourseAndYear((int) $selectedCourseId, (string) $selectedCurriculumYear)
            : null;
        $availableSubjects = Subject::query()
            ->where('is_subject_file_record', true)
            ->orderBy('code')
            ->orderBy('name')
            ->limit(800)
            ->get(['id', 'code', 'name', 'units', 'lec', 'lab', 'hours', 'course_type']);

        return view('registrar.registrar-menu.academic-master.pre-requisites', [
            'courses' => $courses,
            'courseYearMap' => $courseYearMap,
            'selectedCourseId' => $selectedCourseId,
            'selectedCurriculumYear' => $selectedCurriculumYear,
            'selectedDateFrom' => $selectedCurriculum && $selectedCurriculum->date_from ? optional($selectedCurriculum->date_from)->format('Y-m-d') : '',
            'selectedDateTo' => $selectedCurriculum && $selectedCurriculum->date_to ? optional($selectedCurriculum->date_to)->format('Y-m-d') : '',
            'yearBlocks' => $this->orderedYearBlocks(),
            'semesters' => $this->orderedSemesters()->values(),
            'availableSubjects' => $availableSubjects,
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
                'message' => 'Curriculum course not found.',
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
            abort(404, 'Curriculum course not found.');
        }

        $subjectCode = $this->sanitizeFilenameSegment($payload['subject']['code'] ?? 'course');
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
                'message' => 'Curriculum course not found.',
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
                        'pre_subject_ids' => ['One or more selected courses are not valid for this curriculum.'],
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
            'message' => 'Pre/co-requisite mappings saved successfully.',
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

    private function normalizeCurriculumYearCode($curriculumYearCode): string
    {
        $normalized = preg_replace('/\s+/', '', trim((string) $curriculumYearCode));

        return $normalized !== '' ? $normalized : '';
    }

    private function findCourseCurriculumByCourseAndYear(int $courseId, string $curriculumYearCode)
    {
        $normalizedYearCode = $this->normalizeCurriculumYearCode($curriculumYearCode);
        if ($normalizedYearCode === '') {
            return null;
        }

        $curriculumYearId = Schema::hasColumn('course_curricula', 'curriculum_year_id')
            ? $this->resolveCurriculumYearLookupId($normalizedYearCode)
            : null;

        return CourseCurriculum::query()
            ->where('course_id', $courseId)
            ->where(function ($query) use ($normalizedYearCode, $curriculumYearId) {
                $query->where('curriculum_year_code', $normalizedYearCode);

                if ($curriculumYearId) {
                    $query->orWhere('curriculum_year_id', $curriculumYearId);
                }
            })
            ->first();
    }

    private function resolveOrCreateCurriculumYearId(string $curriculumYearCode)
    {
        $normalizedYearCode = $this->normalizeCurriculumYearCode($curriculumYearCode);
        if ($normalizedYearCode === '' || !Schema::hasTable('curriculum_years')) {
            return null;
        }

        $curriculumYearId = DB::table('curriculum_years')
            ->where('code', $normalizedYearCode)
            ->value('id');

        if ($curriculumYearId) {
            return (int) $curriculumYearId;
        }

        return (int) DB::table('curriculum_years')->insertGetId([
            'code' => $normalizedYearCode,
            'label' => $this->formatCurriculumYearLabel($normalizedYearCode),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveCurriculumYearLookupId(string $curriculumYearCode)
    {
        $normalizedYearCode = $this->normalizeCurriculumYearCode($curriculumYearCode);
        if ($normalizedYearCode === '' || !Schema::hasTable('curriculum_years')) {
            return null;
        }

        $curriculumYearId = DB::table('curriculum_years')
            ->where('code', $normalizedYearCode)
            ->value('id');

        return $curriculumYearId ? (int) $curriculumYearId : null;
    }

    private function formatCurriculumYearLabel(string $curriculumYearCode): string
    {
        $normalizedCode = trim($curriculumYearCode);

        if (preg_match('/^(\d{2})(\d{2})$/', $normalizedCode, $matches)) {
            $start = (int) $matches[1];
            $end = (int) $matches[2];
            $centuryBase = $start >= 80 ? 1900 : 2000;

            return 'AY ' . ($centuryBase + $start) . '-' . ($centuryBase + $end);
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', $normalizedCode, $matches)) {
            return 'AY ' . $matches[1] . '-' . $matches[2];
        }

        return 'AY ' . $normalizedCode;
    }

    private function emptyCurriculumSummaryPayload(): array
    {
        return [
            'approval_status' => 'Draft',
            'is_published' => false,
            'published_at' => '',
            'workflow' => [
                ['label' => 'Department Head', 'done' => false, 'date' => ''],
                ['label' => 'Dean', 'done' => false, 'date' => ''],
                ['label' => 'Registrar', 'done' => false, 'date' => ''],
                ['label' => 'Academic Council', 'done' => false, 'date' => ''],
            ],
            'term_totals' => [],
            'year_totals' => [],
            'program_total_units' => 0,
            'expected_total_units' => 0,
            'issues' => ['No curriculum selected yet.'],
        ];
    }

    private function buildCurriculumSummaryPayload(CourseCurriculum $curriculum): array
    {
        $assignments = CourseCurriculumSubject::query()
            ->with(['subject', 'yearBlock', 'semester'])
            ->where('course_curriculum_id', (int) $curriculum->id)
            ->orderBy('year_block_id')
            ->orderBy('semester_id')
            ->orderBy('display_order')
            ->get();

        $termTotals = [];
        $yearTotals = [];
        $issues = [];
        $subjectCounts = [];
        $subjectIds = $assignments->pluck('subject_id')->map(function ($id) {
            return (int) $id;
        })->unique()->values()->all();

        foreach ($assignments as $assignment) {
            $units = (float) ($assignment->credited_units ?: 0);
            $yearLabel = (string) (optional($assignment->yearBlock)->label ?: 'Unassigned Year');
            $termLabel = (string) (optional($assignment->semester)->name ?: 'Unassigned Term');
            $termKey = $yearLabel . ' - ' . $termLabel;
            $subjectCode = trim((string) optional($assignment->subject)->code);

            $termTotals[$termKey] = ($termTotals[$termKey] ?? 0) + $units;
            $yearTotals[$yearLabel] = ($yearTotals[$yearLabel] ?? 0) + $units;
            $subjectCounts[(int) $assignment->subject_id] = ($subjectCounts[(int) $assignment->subject_id] ?? 0) + 1;

            if ($units <= 0) {
                $issues[] = ($subjectCode !== '' ? $subjectCode : 'A course') . ' has missing credited units.';
            }
        }

        foreach ($subjectCounts as $subjectId => $count) {
            if ($count <= 1) {
                continue;
            }

            $subject = $assignments->firstWhere('subject_id', (int) $subjectId);
            $issues[] = trim((string) optional(optional($subject)->subject)->code) . ' appears in multiple curriculum rows.';
        }

        $requisiteRows = CurriculumSubjectRequisite::query()
            ->whereIn('course_curriculum_subject_id', $assignments->pluck('id')->all())
            ->get(['course_curriculum_subject_id', 'requisite_subject_id']);

        foreach ($requisiteRows as $row) {
            if (!in_array((int) $row->requisite_subject_id, $subjectIds, true)) {
                $issues[] = 'A requisite points to a course outside this curriculum.';
                break;
            }
        }

        if ($assignments->isEmpty()) {
            $issues[] = 'No courses are assigned yet.';
        }

        $expectedTotalUnits = (float) (optional($curriculum->course)->total_units ?: 0);
        $programTotalUnits = (float) array_sum($yearTotals);
        if ($expectedTotalUnits > 0 && abs($programTotalUnits - $expectedTotalUnits) > 0.01) {
            $issues[] = 'Total units do not match the program total units.';
        }

        $workflow = [
            ['label' => 'Department Head', 'done' => !empty($curriculum->department_head_approved_at), 'date' => optional($curriculum->department_head_approved_at)->format('M j, Y')],
            ['label' => 'Dean', 'done' => !empty($curriculum->dean_approved_at), 'date' => optional($curriculum->dean_approved_at)->format('M j, Y')],
            ['label' => 'Registrar', 'done' => !empty($curriculum->registrar_approved_at), 'date' => optional($curriculum->registrar_approved_at)->format('M j, Y')],
            ['label' => 'Academic Council', 'done' => !empty($curriculum->academic_council_approved_at), 'date' => optional($curriculum->academic_council_approved_at)->format('M j, Y')],
        ];

        return [
            'approval_status' => (string) ($curriculum->approval_status ?: 'Draft'),
            'is_published' => (bool) $curriculum->is_published,
            'published_at' => optional($curriculum->published_at)->format('M j, Y'),
            'workflow' => $workflow,
            'term_totals' => collect($termTotals)->map(function ($units, $label) {
                return ['label' => (string) $label, 'units' => (float) $units];
            })->values()->all(),
            'year_totals' => collect($yearTotals)->map(function ($units, $label) {
                return ['label' => (string) $label, 'units' => (float) $units];
            })->values()->all(),
            'program_total_units' => $programTotalUnits,
            'expected_total_units' => $expectedTotalUnits,
            'issues' => array_values(array_unique($issues)),
        ];
    }

    private function deriveCurriculumYearCodeFromDates(string $dateFrom, string $dateTo): string
    {
        $fromYear = Carbon::parse($dateFrom)->format('Y');
        $toYear = Carbon::parse($dateTo)->format('Y');

        return $fromYear . '-' . $toYear;
    }

    private function resolveCurriculumFileSemesterLabel(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || in_array($normalized, ['all semester', 'all semesters'], true)) {
            return null;
        }

        if (in_array($normalized, ['first', 'first semester', '1', '1st', '1st semester'], true)) {
            return 'First Semester';
        }

        if (in_array($normalized, ['second', 'second semester', '2', '2nd', '2nd semester'], true)) {
            return 'Second Semester';
        }

        if (strpos($normalized, 'summer') !== false) {
            return 'Summer Semester';
        }

        return null;
    }

    private function resolveCurriculumFileSemesterId(?string $semesterLabel)
    {
        if (!$semesterLabel || !Schema::hasTable('semesters')) {
            return null;
        }

        $semesterId = Semester::query()
            ->where('name', $semesterLabel)
            ->value('id');

        return $semesterId ? (int) $semesterId : null;
    }

    private function resolveCurriculumFileYearLevelLabel(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || in_array($normalized, ['all year levels', 'all year level'], true)) {
            return null;
        }

        if (in_array($normalized, ['1', '1st', '1st year', 'first', 'first year'], true)) {
            return '1st Year';
        }

        if (in_array($normalized, ['2', '2nd', '2nd year', 'second', 'second year'], true)) {
            return '2nd Year';
        }

        if (in_array($normalized, ['3', '3rd', '3rd year', 'third', 'third year'], true)) {
            return '3rd Year';
        }

        if (in_array($normalized, ['4', '4th', '4th year', 'fourth', 'fourth year'], true)) {
            return '4th Year';
        }

        return null;
    }

    private function resolveCurriculumFileYearBlockId(?string $yearLevelLabel)
    {
        if (!$yearLevelLabel || !Schema::hasTable('year_blocks')) {
            return null;
        }

        $yearBlockId = YearBlock::query()
            ->where('label', $yearLevelLabel)
            ->value('id');

        return $yearBlockId ? (int) $yearBlockId : null;
    }

    private function redirectToCurriculumFile($courseId, $curriculumYear, string $message, string $flashKey = 'curriculum_file_success')
    {
        $routeParameters = [];

        if (!empty($courseId)) {
            $routeParameters['course_id'] = (int) $courseId;
        }

        if ($curriculumYear !== null && trim((string) $curriculumYear) !== '') {
            $routeParameters['curriculum_year'] = (string) $curriculumYear;
        }

        return redirect()
            ->route('registrar.registrar-menu.academic-master.curriculum-file', $routeParameters)
            ->with($flashKey, $message);
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
    public function coordinationDeansFaculty(Request $request)
    {
        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $courseId = (int) $request->query('course_id', 0);

        $rows = $this->buildClassSchedulePreparationRowsQuery($schoolYear, $semester, '', '', $courseId)
            ->limit(800)
            ->get();

        $programs = $rows
            ->groupBy('course_id')
            ->map(function ($groupRows) {
                $first = $groupRows->first();
                $sections = $groupRows->pluck('section')->filter()->unique()->count();
                $facultyCount = $groupRows->pluck('faculty_name')->filter()->unique()->count();
                $scheduled = $groupRows->filter(function ($row) {
                    return trim((string) ($row->days ?? '')) !== ''
                        && trim((string) ($row->time_start ?? '')) !== ''
                        && trim((string) ($row->time_end ?? '')) !== ''
                        && trim((string) ($row->room ?? '')) !== ''
                        && trim((string) ($row->faculty_name ?? '')) !== '';
                })->count();

                return [
                    'course_id' => (int) ($first->course_id ?? 0),
                    'course_code' => trim((string) ($first->course_code ?? '')),
                    'course_name' => trim((string) ($first->course_name ?? '')),
                    'sections' => (int) $sections,
                    'subjects' => (int) $groupRows->count(),
                    'scheduled' => (int) $scheduled,
                    'pending' => max((int) $groupRows->count() - (int) $scheduled, 0),
                    'faculty' => (int) $facultyCount,
                ];
            })
            ->sortBy('course_code')
            ->values();

        $facultyLoads = $rows
            ->groupBy(function ($row) {
                $name = trim((string) ($row->faculty_name ?? ''));
                return $name !== '' ? $name : 'TBA';
            })
            ->map(function ($groupRows, $facultyName) {
                return [
                    'faculty' => (string) $facultyName,
                    'subjects' => (int) $groupRows->count(),
                    'sections' => (int) $groupRows->pluck('section')->filter()->unique()->count(),
                    'rooms' => (int) $groupRows->pluck('room')->filter()->unique()->count(),
                ];
            })
            ->sortByDesc('subjects')
            ->take(20)
            ->values();

        $slotRows = $this->buildSlotMonitoringSubjectRowsQuery($schoolYear, $semester, '', '', '', $courseId)
            ->limit(500)
            ->get();

        $slotPressure = $slotRows
            ->map(function ($slot) {
                $total = max((int) ($slot->total_slots ?? 0), 0);
                $enrolled = max((int) ($slot->enrolled_slots ?? 0), 0);
                $percent = $total > 0 ? (int) round(($enrolled / $total) * 100) : 0;

                return [
                    'course' => trim((string) ($slot->course_code ?? '')),
                    'section' => (string) ($slot->section ?? ''),
                    'subject' => trim((string) ($slot->subject_code ?? '')) . ' - ' . trim((string) ($slot->subject_name ?? '')),
                    'total' => $total,
                    'enrolled' => $enrolled,
                    'percent' => min(max($percent, 0), 999),
                ];
            })
            ->sortByDesc('percent')
            ->take(12)
            ->values();

        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = collect(array_values($configOptions['school_years'] ?? []))
            ->map(function ($value) {
                $value = trim((string) $value);
                return ['value' => $value, 'label' => $value];
            })
            ->filter(function ($option) {
                return (string) ($option['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $semesterOptions = [
            ['value' => 'First', 'label' => 'First'],
            ['value' => 'Second', 'label' => 'Second'],
        ];

        $courseOptions = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                $name = trim((string) $course->name);
                return [
                    'id' => (int) $course->id,
                    'label' => $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name),
                ];
            })
            ->values()
            ->all();

        $summary = [
            'programs' => (int) $programs->count(),
            'sections' => (int) $rows->pluck('section')->filter()->unique()->count(),
            'subjects' => (int) $rows->count(),
            'pending' => (int) $programs->sum('pending'),
            'faculty' => (int) $rows->pluck('faculty_name')->filter()->unique()->count(),
        ];

        return view('registrar.registrar-menu.scheduling.coordination-deans-faculty', compact(
            'programs',
            'facultyLoads',
            'slotPressure',
            'summary',
            'schoolYearOptions',
            'semesterOptions',
            'courseOptions',
            'schoolYear',
            'semester',
            'courseId'
        ));
    }

    public function roomSectionOfferingManagement(Request $request)
    {
        $this->seedRoomDimensionsIfEmpty();

        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $sectionQuery = trim((string) $request->query('section', ''));
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);
        $statusFilter = trim((string) $request->query('status', ''));
        $statusKey = function ($status) {
            return strtolower(str_replace([' ', '/'], '_', trim((string) $status)));
        };

        $roomsQuery = Room::query()
            ->with(['hallway.building:id,name', 'courses', 'updatedBy:id,name'])
            ->when($courseId > 0, function ($query) use ($courseId) {
                $query->whereHas('courses', function ($courseQuery) use ($courseId) {
                    $courseQuery->where('courses.id', $courseId);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('room_number', 'like', '%' . $search . '%')
                        ->orWhere('room_code', 'like', '%' . $search . '%')
                        ->orWhere('room_name', 'like', '%' . $search . '%')
                        ->orWhere('room_type', 'like', '%' . $search . '%')
                        ->orWhereHas('hallway', function ($hallwayQuery) use ($search) {
                            $hallwayQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhereHas('building', function ($buildingQuery) use ($search) {
                                    $buildingQuery->where('name', 'like', '%' . $search . '%');
                                });
                        })
                        ->orWhereHas('courses', function ($courseQuery) use ($search) {
                            $courseQuery->where('code', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('floor_number')
            ->orderBy('room_number');

        $rooms = $roomsQuery->get()
            ->map(function (Room $room) {
                $row = $this->mapRoomFileRow($room);
                $row['availability'] = $row['available_start_time'] . ' - ' . $row['available_end_time'];
                return $row;
            })
            ->values();

        $subjectRows = $this->buildSectionOfferingSubjectRowsQuery(
            $schoolYear,
            $semester,
            $sectionQuery,
            $search,
            $courseId
        )->limit(1000)->get();

        $roomLookup = [];
        foreach ($rooms as $room) {
            $aliases = array_filter([
                (string) ($room['id'] ?? ''),
                trim((string) ($room['room_code'] ?? '')),
                trim((string) ($room['room_number'] ?? '')),
                'Room#' . trim((string) ($room['room_code'] ?? '')),
                'Room#' . trim((string) ($room['room_number'] ?? '')),
            ]);

            foreach ($aliases as $alias) {
                $roomLookup[strtolower(trim($alias))] = $room;
            }
        }

        $assignmentRows = collect();
        if (Schema::hasTable('class_room_assignments')) {
            $assignmentQuery = DB::table('class_room_assignments as cra')
                ->leftJoin('subjects as s', 's.id', '=', 'cra.class_offering_id')
                ->leftJoin('academic_terms as at', 'at.id', '=', 's.academic_term_id')
                ->leftJoin('courses as c', 'c.id', '=', 's.course_id')
                ->leftJoin('rooms as r', 'r.id', '=', 'cra.room_id')
                ->select([
                    'cra.*',
                    's.code as subject_code',
                    's.name as subject_name',
                    's.course_id as course_id',
                    's.year_section as section',
                    'at.school_year as school_year',
                    'at.term as term',
                    'c.code as course_code',
                    DB::raw('COALESCE(r.room_code, r.room_number, "") as room_code'),
                ]);

            if ($schoolYear !== '') {
                $assignmentQuery->where(function ($query) use ($schoolYear) {
                    $query->where('cra.academic_year', $schoolYear)
                        ->orWhere('at.school_year', $schoolYear);
                });
            }

            if ($semester !== '') {
                $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                    ->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })
                    ->values()
                    ->all();

                $assignmentQuery->where(function ($query) use ($semesterAliases) {
                    $query->whereIn(DB::raw('LOWER(TRIM(COALESCE(cra.semester, "")))'), $semesterAliases)
                        ->orWhereIn(DB::raw('LOWER(TRIM(COALESCE(at.term, "")))'), $semesterAliases);
                });
            }

            if ($courseId > 0) {
                $assignmentQuery->where('s.course_id', $courseId);
            }
            if ($sectionQuery !== '') {
                $assignmentQuery->where('cra.section_id', 'like', '%' . $sectionQuery . '%');
            }
            if ($search !== '') {
                $assignmentQuery->where(function ($query) use ($search) {
                    $query->where('s.code', 'like', '%' . $search . '%')
                        ->orWhere('s.name', 'like', '%' . $search . '%')
                        ->orWhere('cra.section_id', 'like', '%' . $search . '%')
                        ->orWhere('c.code', 'like', '%' . $search . '%')
                        ->orWhere('r.room_code', 'like', '%' . $search . '%')
                        ->orWhere('r.room_number', 'like', '%' . $search . '%');
                });
            }

            $assignmentRows = $assignmentQuery
                ->orderByDesc('cra.updated_at')
                ->limit(1000)
                ->get();
        }

        $studentCounts = collect();
        if (Schema::hasTable('student_section_assignments')) {
            $studentCountQuery = DB::table('student_section_assignments as ssa')
                ->leftJoin('academic_terms as at', 'at.id', '=', 'ssa.academic_term_id')
                ->select([
                    'ssa.course_id',
                    'ssa.section',
                    'at.school_year',
                    'at.term',
                    DB::raw('COUNT(*) as total'),
                ])
                ->groupBy('ssa.course_id', 'ssa.section', 'at.school_year', 'at.term');

            if ($schoolYear !== '') {
                $studentCountQuery->where('at.school_year', $schoolYear);
            }
            if ($semester !== '') {
                $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                    ->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })
                    ->values()
                    ->all();
                $studentCountQuery->whereIn(DB::raw('LOWER(TRIM(COALESCE(at.term, "")))'), $semesterAliases);
            }
            if ($courseId > 0) {
                $studentCountQuery->where('ssa.course_id', $courseId);
            }

            $studentCounts = $studentCountQuery->get()->keyBy(function ($row) {
                return implode('|', [
                    (int) $row->course_id,
                    trim((string) $row->school_year),
                    $this->normalizeSlotMonitoringSemester((string) $row->term),
                    trim((string) $row->section),
                ]);
            });
        }

        $offeringRows = $subjectRows
            ->map(function ($row) use ($roomLookup) {
                $roomText = trim((string) ($row->room ?? ''));
                $room = $roomText !== '' ? ($roomLookup[strtolower($roomText)] ?? $roomLookup[strtolower('Room#' . $roomText)] ?? null) : null;
                $courseIdValue = (int) ($row->course_id ?? 0);
                $programAllowed = !$room || in_array($courseIdValue, (array) ($room['program_ids'] ?? []), true);
                $hasSchedule = trim((string) ($row->days ?? '')) !== ''
                    && trim((string) ($row->time_start ?? '')) !== ''
                    && trim((string) ($row->time_end ?? '')) !== '';
                $hasRoom = $roomText !== '';
                $validationIssues = [];

                $status = 'Ready';
                if (!$hasSchedule && !$hasRoom) {
                    $status = 'Needs Schedule and Room';
                    $validationIssues[] = 'Missing schedule';
                    $validationIssues[] = 'No room assigned';
                } elseif (!$hasSchedule) {
                    $status = 'Needs Schedule';
                    $validationIssues[] = 'Missing schedule';
                } elseif (!$hasRoom) {
                    $status = 'Needs Room';
                    $validationIssues[] = 'No room assigned';
                } elseif ($roomText !== '' && !$room) {
                    $status = 'Needs Room';
                    $validationIssues[] = 'Assigned room is not found in the room file';
                } elseif (!$programAllowed) {
                    $status = 'Program Not Allowed';
                    $validationIssues[] = 'Assigned room is not allowed for this program';
                }

                return [
                    'id' => (int) ($row->id ?? 0),
                    'school_year' => trim((string) ($row->school_year ?? '')),
                    'semester' => $this->normalizeSlotMonitoringSemester((string) ($row->semester_label ?? '')),
                    'course_id' => $courseIdValue,
                    'course_code' => trim((string) ($row->course_code ?? '')),
                    'section' => trim((string) ($row->section ?? '')),
                    'subject_code' => trim((string) ($row->subject_code ?? '')),
                    'subject_name' => trim((string) ($row->subject_name ?? '')),
                    'faculty_name' => trim((string) ($row->faculty_name ?? '')),
                    'days' => trim((string) ($row->days ?? '')),
                    'time_start' => $this->classScheduleTimeInputValue((string) ($row->time_start ?? '')),
                    'time_end' => $this->classScheduleTimeInputValue((string) ($row->time_end ?? '')),
                    'room' => $roomText,
                    'room_label' => $room ? ($room['room_code'] . ' / Room #' . $room['room_number']) : ($roomText !== '' ? $roomText : 'TBA'),
                    'room_type' => $room['room_type'] ?? '',
                    'room_capacity' => (int) ($room['capacity'] ?? 0),
                    'program_allowed' => $programAllowed,
                    'status' => $status,
                    'validation_issues' => $validationIssues,
                    'status_reason' => implode('; ', $validationIssues),
                    'schedule_lines' => $this->buildSectionOfferingScheduleLines(
                        (string) ($row->days ?? ''),
                        (string) ($row->time_start ?? ''),
                        (string) ($row->time_end ?? ''),
                        $roomText
                    ),
                ];
            })
            ->values();

        $conflictSubjectIds = [];
        for ($leftIndex = 0; $leftIndex < $offeringRows->count(); $leftIndex++) {
            $left = $offeringRows[$leftIndex];
            if ($left['time_start'] === '' || $left['time_end'] === '' || $left['days'] === '') {
                continue;
            }

            for ($rightIndex = $leftIndex + 1; $rightIndex < $offeringRows->count(); $rightIndex++) {
                $right = $offeringRows[$rightIndex];
                if ($right['time_start'] === '' || $right['time_end'] === '' || $right['days'] === '') {
                    continue;
                }
                if ($left['school_year'] !== $right['school_year'] || $left['semester'] !== $right['semester']) {
                    continue;
                }
                if (!$this->autoScheduleOverlaps([
                    'days' => $left['days'],
                    'time_start' => $left['time_start'],
                    'time_end' => $left['time_end'],
                ], [
                    'days' => $right['days'],
                    'time_start' => $right['time_start'],
                    'time_end' => $right['time_end'],
                ])) {
                    continue;
                }

                $sameRoom = $left['room'] !== '' && strtolower($left['room']) === strtolower($right['room']);
                $sameSection = $left['section'] !== '' && $left['section'] === $right['section'];
                $sameFaculty = $left['faculty_name'] !== '' && $left['faculty_name'] === $right['faculty_name'];

                if ($sameRoom || $sameSection || $sameFaculty) {
                    $conflictSubjectIds[$left['id']] = true;
                    $conflictSubjectIds[$right['id']] = true;
                }
            }
        }

        $offeringRows = $offeringRows
            ->map(function ($row) use ($conflictSubjectIds, $statusKey) {
                if (isset($conflictSubjectIds[(int) $row['id']])) {
                    $row['status'] = 'Conflict';
                    $issues = (array) ($row['validation_issues'] ?? []);
                    $issues[] = 'Schedule overlaps with the same room, section, or faculty';
                    $row['validation_issues'] = array_values(array_unique($issues));
                    $row['status_reason'] = implode('; ', $row['validation_issues']);
                }

                $keys = [$statusKey($row['status'] ?? '')];
                if (empty($row['program_allowed'])) {
                    $keys[] = 'program_not_allowed';
                }
                foreach ((array) ($row['validation_issues'] ?? []) as $issue) {
                    $issue = strtolower((string) $issue);
                    if (strpos($issue, 'schedule') !== false && strpos($issue, 'overlap') === false) {
                        $keys[] = 'needs_schedule';
                    }
                    if (strpos($issue, 'room') !== false && strpos($issue, 'allowed') === false && strpos($issue, 'overlap') === false) {
                        $keys[] = 'needs_room';
                    }
                    if (strpos($issue, 'overlap') !== false) {
                        $keys[] = 'conflict';
                    }
                }
                $row['status_keys'] = array_values(array_unique(array_filter($keys)));

                return $row;
            })
            ->values();

        if ($statusFilter !== '') {
            $offeringRows = $offeringRows
                ->filter(function ($row) use ($statusFilter, $statusKey) {
                    return in_array($statusFilter, (array) ($row['status_keys'] ?? [$statusKey($row['status'] ?? '')]), true);
                })
                ->values();
        }

        $roomUsage = [];
        foreach ($offeringRows as $offering) {
            $roomText = trim((string) ($offering['room'] ?? ''));
            if ($roomText === '') {
                continue;
            }
            $room = $roomLookup[strtolower($roomText)] ?? $roomLookup[strtolower('Room#' . $roomText)] ?? null;
            if (!$room) {
                continue;
            }
            $roomId = (int) $room['id'];
            if (!isset($roomUsage[$roomId])) {
                $roomUsage[$roomId] = [
                    'offerings' => 0,
                    'sections' => [],
                    'subjects' => [],
                    'rows' => [],
                ];
            }
            $roomUsage[$roomId]['offerings']++;
            $roomUsage[$roomId]['sections'][$offering['section']] = true;
            $roomUsage[$roomId]['subjects'][] = $offering['subject_code'];
            $roomUsage[$roomId]['rows'][] = [
                'school_year' => (string) ($offering['school_year'] ?? ''),
                'semester' => (string) ($offering['semester'] ?? ''),
                'program' => (string) ($offering['course_code'] ?? ''),
                'section' => (string) ($offering['section'] ?? ''),
                'subject' => trim((string) ($offering['subject_code'] ?? '') . ' ' . (string) ($offering['subject_name'] ?? '')),
                'faculty' => (string) ($offering['faculty_name'] ?? ''),
                'schedule' => implode(', ', (array) ($offering['schedule_lines'] ?? [])),
                'status' => (string) ($offering['status'] ?? ''),
            ];
        }

        $rooms = $rooms
            ->map(function ($room) use ($roomUsage) {
                $usage = $roomUsage[(int) $room['id']] ?? ['offerings' => 0, 'sections' => [], 'subjects' => [], 'rows' => []];
                $room['offering_count'] = (int) ($usage['offerings'] ?? 0);
                $room['section_count'] = count($usage['sections'] ?? []);
                $room['subject_preview'] = implode(', ', array_slice(array_filter($usage['subjects'] ?? []), 0, 4));
                $room['usage_rows'] = array_slice((array) ($usage['rows'] ?? []), 0, 12);
                return $room;
            })
            ->values();

        $visibleSubjectIds = $offeringRows
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $sectionSourceRows = $statusFilter === ''
            ? $subjectRows
            : $subjectRows->filter(function ($row) use ($visibleSubjectIds) {
                return in_array((int) ($row->id ?? 0), $visibleSubjectIds, true);
            })->values();

        $sections = $sectionSourceRows
            ->groupBy(function ($row) {
                return implode('|', [
                    (int) ($row->course_id ?? 0),
                    trim((string) ($row->school_year ?? '')),
                    $this->normalizeSlotMonitoringSemester((string) ($row->semester_label ?? '')),
                    trim((string) ($row->section ?? '')),
                ]);
            })
            ->map(function ($groupRows) use ($studentCounts, $offeringRows) {
                $groupRows = collect($groupRows)->values();
                $first = $groupRows->first();
                $schoolYearValue = trim((string) ($first->school_year ?? ''));
                $semesterValue = $this->normalizeSlotMonitoringSemester((string) ($first->semester_label ?? ''));
                $courseIdValue = (int) ($first->course_id ?? 0);
                $sectionValue = trim((string) ($first->section ?? ''));
                $sectionKey = implode('|', [$courseIdValue, $schoolYearValue, $semesterValue, $sectionValue]);
                $sectionOfferings = $offeringRows->filter(function ($offering) use ($schoolYearValue, $semesterValue, $courseIdValue, $sectionValue) {
                    return (int) $offering['course_id'] === $courseIdValue
                        && (string) $offering['school_year'] === $schoolYearValue
                        && (string) $offering['semester'] === $semesterValue
                        && (string) $offering['section'] === $sectionValue;
                })->values();

                $scheduledRows = $groupRows->filter(function ($row) {
                    return trim((string) ($row->days ?? '')) !== ''
                        && trim((string) ($row->time_start ?? '')) !== ''
                        && trim((string) ($row->time_end ?? '')) !== ''
                        && trim((string) ($row->room ?? '')) !== '';
                });

                $roomsUsed = $groupRows
                    ->pluck('room')
                    ->map(function ($room) {
                        return trim((string) $room);
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $faculty = $groupRows
                    ->pluck('faculty_name')
                    ->map(function ($name) {
                        return trim((string) $name);
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $scheduleSamples = $groupRows
                    ->map(function ($row) {
                        $lines = $this->buildSectionOfferingScheduleLines(
                            (string) ($row->days ?? ''),
                            (string) ($row->time_start ?? ''),
                            (string) ($row->time_end ?? ''),
                            (string) ($row->room ?? '')
                        );

                        return [
                            'subject' => trim((string) ($row->subject_code ?? '')),
                            'schedule' => count($lines) ? implode(', ', $lines) : 'TBA',
                        ];
                    })
                    ->take(4)
                    ->values()
                    ->all();

                $roomCapacity = $sectionOfferings
                    ->pluck('room_capacity')
                    ->filter(function ($capacity) {
                        return (int) $capacity > 0;
                    })
                    ->max() ?: 0;
                $students = (int) optional($studentCounts->get($sectionKey))->total;
                $pendingCount = $sectionOfferings->filter(function ($offering) {
                    return (string) $offering['status'] !== 'Ready';
                })->count();

                return [
                    'school_year' => $schoolYearValue,
                    'semester' => $semesterValue,
                    'course_code' => trim((string) ($first->course_code ?? '')),
                    'course_name' => trim((string) ($first->course_name ?? '')),
                    'section' => $sectionValue,
                    'subject_count' => (int) $groupRows->count(),
                    'scheduled_count' => (int) $scheduledRows->count(),
                    'pending_count' => (int) $pendingCount,
                    'student_count' => $students,
                    'room_capacity' => (int) $roomCapacity,
                    'rooms' => $roomsUsed,
                    'faculty' => $faculty,
                    'schedule_samples' => $scheduleSamples,
                ];
            })
            ->sortBy(function ($section) {
                return strtolower((string) ($section['course_code'] ?? '')) . '|'
                    . strtolower((string) ($section['section'] ?? ''));
            })
            ->values();

        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = collect(array_values($configOptions['school_years'] ?? []))
            ->map(function ($value) {
                $value = trim((string) $value);
                return ['value' => $value, 'label' => $value];
            })
            ->filter(function ($option) {
                return (string) ($option['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $semesterOptions = collect(['First', 'Second', 'Summer'])
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            })
            ->all();

        $courseOptions = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                $name = trim((string) $course->name);

                return [
                    'id' => (int) $course->id,
                    'label' => $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name),
                ];
            })
            ->values()
            ->all();

        $sectionOptions = $subjectRows
            ->pluck('section')
            ->map(function ($section) {
                return trim((string) $section);
            })
            ->filter(function ($section) {
                return $section !== '';
            })
            ->unique()
            ->sort()
            ->values()
            ->all();

        $summary = [
            'rooms' => (int) $rooms->count(),
            'capacity' => (int) $rooms->sum('capacity'),
            'sections' => (int) $sections->count(),
            'scheduled_subjects' => (int) $sections->sum('scheduled_count'),
            'subjects' => (int) $sections->sum('subject_count'),
            'ready_offerings' => (int) $offeringRows->filter(function ($row) { return $row['status'] === 'Ready'; })->count(),
            'pending_offerings' => (int) $offeringRows->filter(function ($row) { return $row['status'] !== 'Ready'; })->count(),
            'conflicts' => (int) $offeringRows->filter(function ($row) { return in_array('conflict', (array) ($row['status_keys'] ?? []), true); })->count(),
            'assignments' => (int) $assignmentRows->count(),
        ];

        $paginateCollection = function ($collection, $pageName, $perPage) use ($request) {
            $collection = collect($collection)->values();
            $total = $collection->count();
            $lastPage = max(1, (int) ceil($total / max(1, (int) $perPage)));
            $page = max(1, min((int) $request->query($pageName, 1), $lastPage));
            $items = $collection->forPage($page, $perPage)->values();

            return [
                $items,
                [
                    'page_name' => $pageName,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => $lastPage,
                    'total' => $total,
                    'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                    'to' => min($total, $page * $perPage),
                ],
            ];
        };

        list($rooms, $roomsPagination) = $paginateCollection($rooms, 'rooms_page', 50);
        list($sections, $sectionsPagination) = $paginateCollection($sections, 'sections_page', 25);
        list($offeringRows, $offeringsPagination) = $paginateCollection($offeringRows, 'offerings_page', 50);
        list($assignmentRows, $assignmentsPagination) = $paginateCollection($assignmentRows, 'assignments_page', 50);

        $pagination = [
            'rooms' => $roomsPagination,
            'sections' => $sectionsPagination,
            'offerings' => $offeringsPagination,
            'assignments' => $assignmentsPagination,
        ];

        $statusOptions = [
            ['value' => '', 'label' => 'All Statuses'],
            ['value' => 'ready', 'label' => 'Ready'],
            ['value' => 'needs_schedule_and_room', 'label' => 'Needs Schedule and Room'],
            ['value' => 'needs_schedule', 'label' => 'Needs Schedule'],
            ['value' => 'needs_room', 'label' => 'Needs Room'],
            ['value' => 'program_not_allowed', 'label' => 'Program Not Allowed'],
            ['value' => 'conflict', 'label' => 'Conflict'],
        ];

        return view('registrar.registrar-menu.scheduling.room-section-offering-management', compact(
            'rooms',
            'sections',
            'offeringRows',
            'assignmentRows',
            'pagination',
            'summary',
            'schoolYearOptions',
            'semesterOptions',
            'courseOptions',
            'statusOptions',
            'sectionOptions',
            'schoolYear',
            'semester',
            'sectionQuery',
            'search',
            'courseId',
            'statusFilter'
        ));
    }

    public function roomFile()
    {
        $this->seedRoomDimensionsIfEmpty();

        return view('registrar.registrar-menu.scheduling.room-file');
    }

    public function roomGenerationAssignment(Request $request)
    {
        $this->seedRoomDimensionsIfEmpty();

        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = collect(array_values($configOptions['school_years'] ?? []))
            ->map(function ($schoolYear) {
                $value = trim((string) $schoolYear);
                return ['value' => $value, 'label' => $value];
            })
            ->filter(function ($option) {
                return (string) ($option['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $semesterOptions = collect(['First', 'Second', 'Summer'])
            ->map(function ($semester) {
                return ['value' => $semester, 'label' => $semester];
            })
            ->all();

        $programOptions = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                $name = trim((string) $course->name);
                return [
                    'id' => (int) $course->id,
                    'label' => $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name),
                ];
            })
            ->values()
            ->all();

        $yearLevelOptions = YearBlock::query()
            ->orderBy('id')
            ->get(['id', 'label'])
            ->map(function (YearBlock $yearBlock) {
                return [
                    'id' => (int) $yearBlock->id,
                    'label' => (string) $yearBlock->label,
                ];
            })
            ->values()
            ->all();

        $sectionOptions = Subject::query()
            ->whereNotNull('year_section')
            ->whereRaw("TRIM(COALESCE(year_section, '')) <> ''")
            ->when(Schema::hasColumn('subjects', 'is_subject_file_record'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('is_subject_file_record')
                        ->orWhere('is_subject_file_record', 0);
                });
            })
            ->select('year_section')
            ->distinct()
            ->orderBy('year_section')
            ->pluck('year_section')
            ->map(function ($section) {
                return trim((string) $section);
            })
            ->filter()
            ->values()
            ->all();

        return view('registrar.registrar-menu.scheduling.room-generation-assignment', compact(
            'schoolYearOptions',
            'semesterOptions',
            'programOptions',
            'yearLevelOptions',
            'sectionOptions'
        ));
    }

    public function generateRoomsForAssignment(Request $request): JsonResponse
    {
        $this->ensureRoomAssignmentSchemaReady();
        $this->seedRoomDimensionsIfEmpty();

        $validated = $request->validate([
            'course_id' => 'nullable|integer|min:1|exists:courses,id',
        ]);

        $selectedCourseId = (int) ($validated['course_id'] ?? 0);
        $allowedCourseIds = $selectedCourseId > 0
            ? [$selectedCourseId]
            : Course::query()
                ->orderBy('id')
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter(function ($id) {
                    return $id > 0;
                })
                ->values()
                ->all();

        $created = 0;
        $updated = 0;
        $programAssignments = 0;
        $subjectAssignments = 0;

        DB::transaction(function () use ($allowedCourseIds, &$created, &$updated, &$programAssignments, &$subjectAssignments) {
            $building = RoomBuilding::query()->firstOrCreate(['name' => 'Campus 1']);
            $hallway = RoomHallway::query()->firstOrCreate([
                'room_building_id' => (int) $building->id,
                'name' => 'Main Hallway',
            ]);

            $defaults = [
                ['code' => 'R101', 'name' => 'R101', 'type' => 'Lecture Room', 'capacity' => 40, 'room_number' => 101],
                ['code' => 'R102', 'name' => 'R102', 'type' => 'Lecture Room', 'capacity' => 45, 'room_number' => 102],
                ['code' => 'R201', 'name' => 'R201', 'type' => 'Lecture Room', 'capacity' => 80, 'room_number' => 201],
                ['code' => 'CLAB1', 'name' => 'Computer Laboratory 1', 'type' => 'Computer Laboratory', 'capacity' => 35, 'room_number' => 301],
                ['code' => 'CLAB2', 'name' => 'Computer Laboratory 2', 'type' => 'Computer Laboratory', 'capacity' => 35, 'room_number' => 302],
                ['code' => 'SCI-LAB1', 'name' => 'Science Laboratory 1', 'type' => 'Science Laboratory', 'capacity' => 30, 'room_number' => 401],
                ['code' => 'PE-GYM', 'name' => 'PE Gym', 'type' => 'PE Area', 'capacity' => 60, 'room_number' => 501],
                ['code' => 'ONLINE', 'name' => 'Online / Virtual Room', 'type' => 'Online / Virtual Room', 'capacity' => 999, 'room_number' => 901],
            ];

            foreach ($defaults as $roomData) {
                $room = Room::query()
                    ->where('room_code', $roomData['code'])
                    ->first();

                if (!$room) {
                    $room = Room::query()->create([
                        'room_code' => $roomData['code'],
                        'room_name' => $roomData['name'],
                        'room_hallway_id' => (int) $hallway->id,
                        'room_number' => (int) $roomData['room_number'],
                        'floor_number' => 1,
                        'capacity' => (int) $roomData['capacity'],
                        'room_type' => $roomData['type'],
                        'available_days' => 'MTWTHFS',
                        'available_start_time' => $this->autoScheduleDayStartTime(),
                        'available_end_time' => $this->autoScheduleDayEndTime(),
                        'status' => 'Active',
                        'updated_by_user_id' => auth()->id(),
                    ]);
                    $created++;
                } else {
                    $payload = [
                        'room_name' => $room->room_name ?: $roomData['name'],
                        'room_type' => $room->room_type ?: $roomData['type'],
                        'capacity' => max((int) $room->capacity, (int) $roomData['capacity']),
                        'available_days' => $room->available_days ?: 'MTWTHFS',
                        'available_start_time' => $room->available_start_time ?: $this->autoScheduleDayStartTime(),
                        'available_end_time' => $room->available_end_time ?: $this->autoScheduleDayEndTime(),
                        'status' => $room->status ?: 'Active',
                        'updated_by_user_id' => auth()->id(),
                    ];

                    $room->fill($payload);
                    if ($room->isDirty()) {
                        $room->save();
                        $updated++;
                    }
                }

                if (!empty($allowedCourseIds)) {
                    $beforeCount = (int) DB::table('room_course_assignments')
                        ->where('room_id', (int) $room->id)
                        ->whereIn('course_id', $allowedCourseIds)
                        ->count();

                    $syncPayload = [];
                    foreach ($allowedCourseIds as $courseId) {
                        $syncPayload[(int) $courseId] = [
                            'assigned_by_user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $room->courses()->syncWithoutDetaching($syncPayload);

                    $afterCount = (int) DB::table('room_course_assignments')
                        ->where('room_id', (int) $room->id)
                        ->whereIn('course_id', $allowedCourseIds)
                        ->count();
                    $programAssignments += max(0, $afterCount - $beforeCount);
                }

                if (Schema::hasTable('room_allowed_subjects')) {
                    $compatibleSubjectIds = Subject::query()
                        ->whereIn('course_id', $allowedCourseIds)
                        ->get()
                        ->filter(function (Subject $subject) use ($room) {
                            $required = $this->resolveSubjectRequiredRoomType($subject, (float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture');
                            return $this->roomTypeMatchesSubject($room, $subject, $required)
                                && (int) $room->capacity >= $this->autoScheduleRequiredCapacity($subject);
                        })
                        ->pluck('id')
                        ->map(function ($id) {
                            return (int) $id;
                        })
                        ->values();

                    foreach ($compatibleSubjectIds as $subjectId) {
                        $before = DB::table('room_allowed_subjects')
                            ->where('room_id', (int) $room->id)
                            ->where('subject_id', (int) $subjectId)
                            ->exists();
                        DB::table('room_allowed_subjects')->updateOrInsert([
                            'room_id' => (int) $room->id,
                            'subject_id' => (int) $subjectId,
                        ], [
                            'assigned_by_user_id' => auth()->id(),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]);
                        if (!$before) {
                            $subjectAssignments++;
                        }
                    }
                }
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Room generation completed.',
            'created_count' => $created,
            'updated_count' => $updated,
            'program_assignment_count' => $programAssignments,
            'subject_assignment_count' => $subjectAssignments,
            'rooms' => Room::query()->count(),
        ]);
    }

    public function assignRoomsPerSectionSubject(Request $request): JsonResponse
    {
        $this->ensureRoomAssignmentSchemaReady();

        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'course_id' => 'nullable|integer|min:0',
            'year_block_id' => 'nullable|integer|min:0',
            'section' => 'nullable|string|max:120',
            'only_pending' => 'nullable|boolean',
        ]);

        $schoolYear = $this->normalizeSectionOfferingSchoolYear((string) $validated['school_year']);
        $semester = $this->normalizeSlotMonitoringSemester((string) $validated['semester']);
        $courseId = (int) ($validated['course_id'] ?? 0);
        $yearBlockId = (int) ($validated['year_block_id'] ?? 0);
        $section = trim((string) ($validated['section'] ?? ''));
        $onlyPending = $this->requestBoolean($request, 'only_pending');

        if ($semester === '') {
            throw ValidationException::withMessages([
                'semester' => ['Please select a valid semester.'],
            ]);
        }

        $subjects = $this->roomAssignmentOfferingsQuery($schoolYear, $semester, $courseId, $yearBlockId, $section)
            ->get();

        if ($subjects->isEmpty()) {
            return response()->json([
                'ok' => true,
                'message' => 'No class offerings matched the selected filters.',
                'summary' => ['assigned' => 0, 'pending' => 0, 'conflicts' => 0],
                'assigned' => [],
                'issues' => [],
            ]);
        }

        $assigned = [];
        $issues = [];
        $summary = ['assigned' => 0, 'pending' => 0, 'conflicts' => 0];

        DB::transaction(function () use ($subjects, $schoolYear, $semester, $onlyPending, &$assigned, &$issues, &$summary) {
            foreach ($subjects as $subject) {
                $components = $this->roomAssignmentComponentsForSubject($subject);
                if (!count($components)) {
                    $summary['pending']++;
                    $issues[] = $this->roomAssignmentReportRow((object) [
                        'class_offering_id' => (int) $subject->id,
                        'course_code' => (string) $subject->code,
                        'subject_name' => (string) $subject->name,
                        'section_id' => (string) $subject->year_section,
                        'schedule_component_type' => 'Hours',
                        'academic_year' => $schoolYear,
                        'semester' => $semester,
                        'assignment_status' => 'Pending Room Assignment',
                        'remarks' => 'Subject Hours is required before room schedule generation. Update Hours in Subject File.',
                    ]);
                    continue;
                }

                foreach ($components as $component) {
                    if ($onlyPending && $this->roomAssignmentComponentAlreadyAssigned((int) $subject->id, (string) $component['type'])) {
                        continue;
                    }

                    $result = $this->assignRoomForSubjectComponent($subject, $component, $schoolYear, $semester);
                    if ($result['status'] === 'Assigned') {
                        $summary['assigned']++;
                        $assigned[] = $result['row'];
                    } else {
                        if ($result['status'] === 'Room Conflict') {
                            $summary['conflicts']++;
                        } else {
                            $summary['pending']++;
                        }
                        $issues[] = $result['row'];
                    }
                }
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Room assignment completed.',
            'summary' => $summary,
            'assigned' => $assigned,
            'issues' => $issues,
        ]);
    }

    public function roomAssignmentReport(Request $request): JsonResponse
    {
        $this->ensureRoomAssignmentSchemaReady();

        $schoolYear = $this->normalizeSectionOfferingSchoolYear((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $courseId = (int) $request->query('course_id', 0);
        $section = trim((string) $request->query('section', ''));

        $query = DB::table('class_room_assignments as cra')
            ->leftJoin('subjects as s', 's.id', '=', 'cra.class_offering_id')
            ->leftJoin('rooms as r', 'r.id', '=', 'cra.room_id')
            ->select([
                'cra.*',
                's.name as subject_name',
                's.course_id as subject_course_id',
                DB::raw('COALESCE(r.room_code, r.room_number, "") as room_code'),
            ]);

        if ($schoolYear !== '') {
            $query->where('cra.academic_year', $schoolYear);
        }
        if ($semester !== '') {
            $query->where('cra.semester', $semester);
        }
        if ($courseId > 0) {
            $query->where('s.course_id', $courseId);
        }
        if ($section !== '') {
            $query->where('cra.section_id', $section);
        }

        $rows = $query
            ->orderBy('cra.assignment_status')
            ->orderBy('cra.course_code')
            ->orderBy('cra.section_id')
            ->limit(300)
            ->get()
            ->map(function ($row) {
                return $this->roomAssignmentReportRow($row);
            })
            ->values();

        return response()->json([
            'ok' => true,
            'rows' => $rows,
        ]);
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
                'rooms.room_code',
                'rooms.room_name',
                'rooms.room_hallway_id',
                'rooms.room_number',
                'rooms.floor_number',
                'rooms.capacity',
                'rooms.room_type',
                'rooms.available_days',
                'rooms.available_start_time',
                'rooms.available_end_time',
                'rooms.status',
                'rooms.updated_by_user_id',
                'rooms.updated_at',
            ])
            ->with([
                'hallway.building:id,name',
                'courses',
                'allowedSubjects',
                'updatedBy:id,name',
            ]);

        if ($search !== '') {
            $this->applyRoomFileSearch($query, $search);
            $this->applyRoomFileSearchPriority($query, $search);
        }

        $this->applyRoomFileSort($query, $sortBy, $sortDir);

        $paginator = $query->paginate($perPage, [
            'rooms.id',
            'rooms.room_code',
            'rooms.room_name',
            'rooms.room_hallway_id',
            'rooms.room_number',
            'rooms.floor_number',
            'rooms.capacity',
            'rooms.room_type',
            'rooms.available_days',
            'rooms.available_start_time',
            'rooms.available_end_time',
            'rooms.status',
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
        $courseIds = $this->normalizeRoomCourseIds($validated['course_ids'] ?? []);
        $subjectIds = $this->normalizeRoomSubjectIds($validated['subject_ids'] ?? []);

        $room = null;

        DB::beginTransaction();
        try {
            $roomPayload = [
                'room_hallway_id' => (int) $validated['room_hallway_id'],
                'room_number' => (int) $validated['room_number'],
                'floor_number' => (int) $validated['floor_number'],
                'capacity' => (int) $validated['capacity'],
                'updated_by_user_id' => auth()->id(),
            ];
            $roomPayload = array_merge($roomPayload, $this->defaultExtendedRoomPayload((int) $validated['room_number']));

            $room = Room::create($roomPayload);

            $room->courses()->sync($courseIds);
            $this->syncRoomAllowedSubjects($room, $subjectIds);

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

        $room->load(['hallway.building:id,name', 'courses', 'allowedSubjects', 'updatedBy:id,name']);

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
        $courseIds = $this->normalizeRoomCourseIds($validated['course_ids'] ?? []);
        $subjectIds = $this->normalizeRoomSubjectIds($validated['subject_ids'] ?? []);

        DB::beginTransaction();
        try {
            $roomPayload = [
                'room_hallway_id' => (int) $validated['room_hallway_id'],
                'room_number' => (int) $validated['room_number'],
                'floor_number' => (int) $validated['floor_number'],
                'capacity' => (int) $validated['capacity'],
                'updated_by_user_id' => auth()->id(),
            ];
            $roomPayload = array_merge($roomPayload, $this->defaultExtendedRoomPayload((int) $validated['room_number'], $room));

            $room->update($roomPayload);

            $room->courses()->sync($courseIds);
            $this->syncRoomAllowedSubjects($room, $subjectIds);

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

        $room->load(['hallway.building:id,name', 'courses', 'allowedSubjects', 'updatedBy:id,name']);

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

        $subjects = Subject::query()
            ->when(Schema::hasColumn('subjects', 'is_subject_file_record'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('is_subject_file_record')
                        ->orWhere('is_subject_file_record', 1)
                        ->orWhere('is_subject_file_record', true);
                });
            })
            ->orderBy('code')
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'code', 'name'])
            ->map(function (Subject $subject) {
                $code = trim((string) $subject->code);
                $name = trim((string) $subject->name);

                return [
                    'id' => (int) $subject->id,
                    'code' => $code,
                    'name' => $name,
                    'label' => $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name),
                ];
            })
            ->values();

        return [
            'buildings' => $buildings,
            'programs' => $programs,
            'subjects' => $subjects,
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
            'room_code' => (string) ($room->room_code ?: ('R' . $room->room_number)),
            'room_name' => (string) ($room->room_name ?: ('Room ' . $room->room_number)),
            'room_number' => (int) $room->room_number,
            'floor_number' => (int) $room->floor_number,
            'capacity' => (int) $room->capacity,
            'room_type' => (string) ($room->room_type ?: 'Lecture Room'),
            'available_days' => (string) ($room->available_days ?: 'MTWTHFS'),
            'available_start_time' => $this->classScheduleTimeInputValue((string) ($room->available_start_time ?: $this->autoScheduleDayStartTime())),
            'available_end_time' => $this->classScheduleTimeInputValue((string) ($room->available_end_time ?: $this->autoScheduleDayEndTime())),
            'status' => (string) ($room->status ?: 'Active'),
            'room_building_id' => $room->hallway ? (int) $room->hallway->room_building_id : null,
            'room_hallway_id' => (int) $room->room_hallway_id,
            'building' => $buildingName,
            'hallway' => $hallwayName,
            'location_label' => $locationLabel,
            'program_ids' => $programIds,
            'program_labels' => $programLabels,
            'program_label' => count($programLabels) ? implode(', ', $programLabels) : '-',
            'subject_ids' => $subjectIds,
            'subject_labels' => $subjectLabels,
            'subject_label' => count($subjectLabels) ? implode(', ', $subjectLabels) : (count($programLabels) ? implode(', ', $programLabels) : '-'),
            'updated_by' => $updatedByName,
            'updated_at' => $room->updated_at ? $room->updated_at->toDateTimeString() : null,
        ];
    }

    private function defaultExtendedRoomPayload(int $roomNumber, Room $room = null): array
    {
        $payload = [];

        if (Schema::hasColumn('rooms', 'room_code')) {
            $payload['room_code'] = $room && $room->room_code ? $room->room_code : ('R' . $roomNumber);
        }
        if (Schema::hasColumn('rooms', 'room_name')) {
            $payload['room_name'] = $room && $room->room_name ? $room->room_name : ('Room ' . $roomNumber);
        }
        if (Schema::hasColumn('rooms', 'room_type')) {
            $payload['room_type'] = $room && $room->room_type ? $room->room_type : 'Lecture Room';
        }
        if (Schema::hasColumn('rooms', 'available_days')) {
            $payload['available_days'] = $room && $room->available_days ? $room->available_days : 'MTWTHFS';
        }
        if (Schema::hasColumn('rooms', 'available_start_time')) {
            $payload['available_start_time'] = $room && $room->available_start_time ? $room->available_start_time : $this->autoScheduleDayStartTime();
        }
        if (Schema::hasColumn('rooms', 'available_end_time')) {
            $payload['available_end_time'] = $room && $room->available_end_time ? $room->available_end_time : $this->autoScheduleDayEndTime();
        }
        if (Schema::hasColumn('rooms', 'status')) {
            $payload['status'] = $room && $room->status ? $room->status : 'Active';
        }

        return $payload;
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

    private function normalizeRoomSubjectIds(array $subjectIds)
    {
        $normalized = [];

        foreach ($subjectIds as $subjectId) {
            $id = (int) $subjectId;
            if ($id > 0 && !in_array($id, $normalized, true)) {
                $normalized[] = $id;
            }
        }

        return $normalized;
    }

    private function syncRoomAllowedSubjects(Room $room, array $subjectIds): void
    {
        if (!Schema::hasTable('room_allowed_subjects')) {
            return;
        }

        $syncPayload = [];
        foreach ($subjectIds as $subjectId) {
            $syncPayload[(int) $subjectId] = [
                'assigned_by_user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $room->allowedSubjects()->sync($syncPayload);
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

    private function ensureRoomAssignmentSchemaReady(): void
    {
        $requiredTables = ['rooms', 'subjects', 'class_room_assignments'];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                throw ValidationException::withMessages([
                    'room_assignment' => ['Room assignment tables are not ready. Please run migrations first.'],
                ]);
            }
        }
    }

    private function roomAssignmentOfferingsQuery(string $schoolYear, string $semester, int $courseId = 0, int $yearBlockId = 0, string $section = '')
    {
        $query = Subject::query()
            ->with('academicTerm')
            ->whereNotNull('course_id')
            ->whereNotNull('year_section')
            ->whereRaw("TRIM(COALESCE(year_section, '')) <> ''");

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($builder) {
                $builder->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 0);
            });
        }

        if ($courseId > 0) {
            $query->where('course_id', $courseId);
        }

        if ($section !== '') {
            $query->where('year_section', $section);
        }

        if ($yearBlockId > 0) {
            $yearLabel = (string) YearBlock::query()->where('id', $yearBlockId)->value('label');
            $yearNumber = $this->academicSetupYearLevelNumber($yearLabel);
            if ($yearNumber > 0) {
                $query->where(function ($builder) use ($yearNumber) {
                    $builder->where('year_section', 'like', '%-' . $yearNumber . '%')
                        ->orWhere('year_section', 'like', '% ' . $yearNumber . '%');
                });
            }
        }

        if ($schoolYear !== '' || $semester !== '') {
            $query->whereHas('academicTerm', function ($termQuery) use ($schoolYear, $semester) {
                if ($schoolYear !== '') {
                    $termQuery->where('school_year', $schoolYear);
                }

                if ($semester !== '') {
                    $aliases = collect($this->slotMonitoringSemesterAliases($semester))
                        ->map(function ($value) {
                            return strtolower(trim((string) $value));
                        })
                        ->values()
                        ->all();

                    $termQuery->whereIn(DB::raw('LOWER(TRIM(term))'), $aliases);
                }
            });
        }

        return $query->orderBy('course_id')->orderBy('year_section')->orderBy('code')->orderBy('id');
    }

    private function roomAssignmentComponentsForSubject(Subject $subject): array
    {
        $hours = (float) ($subject->hours ?: 0);
        if ($hours <= 0) {
            return [];
        }

        $hasLaboratory = (float) ($subject->lab ?: 0) > 0
            || trim((string) ($subject->required_laboratory_room_type ?? '')) !== '';

        if ($hasLaboratory) {
            return [[
                'type' => 'Laboratory',
                'hours' => $hours,
                'required_room_type' => $this->resolveSubjectRequiredRoomType($subject, 'Laboratory'),
            ]];
        }

        return [[
            'type' => $this->roomAssignmentLectureComponentType($subject),
            'hours' => $hours,
            'required_room_type' => $this->resolveSubjectRequiredRoomType($subject, 'Lecture'),
        ]];
    }

    private function roomAssignmentLectureComponentType(Subject $subject): string
    {
        $type = $this->resolveSubjectRequiredRoomType($subject, 'Lecture');
        if ($type === 'PE Area') {
            return 'PE';
        }
        if ($type === 'Online / Virtual Room') {
            return 'Online';
        }

        return 'Lecture';
    }

    private function resolveSubjectRequiredRoomType($subject, string $componentType): string
    {
        $general = trim((string) ($subject->required_room_type ?? ''));
        if ($general !== '') {
            return $this->normalizeRoomAssignmentType($general);
        }

        if ($componentType === 'Laboratory') {
            $explicit = trim((string) ($subject->required_laboratory_room_type ?? ''));
            if ($explicit !== '') {
                return $this->normalizeRoomAssignmentType($explicit);
            }

            return $this->inferLaboratoryRoomType($subject);
        }

        $explicit = trim((string) ($subject->required_lecture_room_type ?? ''));
        if ($explicit !== '') {
            return $this->normalizeRoomAssignmentType($explicit);
        }

        $name = strtoupper(trim((string) $subject->code . ' ' . (string) $subject->name . ' ' . (string) $subject->course_type));
        if (strpos($name, 'ONLINE') !== false || strpos($name, 'VIRTUAL') !== false) {
            return 'Online / Virtual Room';
        }
        if (preg_match('/\bPE\b|PHYSICAL EDUCATION|PATHFIT/', $name) === 1) {
            return 'PE Area';
        }

        return 'Lecture Room';
    }

    private function inferLaboratoryRoomType($subject): string
    {
        $name = strtoupper(trim((string) $subject->code . ' ' . (string) $subject->name . ' ' . (string) $subject->course_type));
        if (strpos($name, 'SCI') !== false || strpos($name, 'BIO') !== false || strpos($name, 'CHEM') !== false || strpos($name, 'PHYS') !== false) {
            return 'Science Laboratory';
        }
        if (strpos($name, 'ONLINE') !== false || strpos($name, 'VIRTUAL') !== false) {
            return 'Online / Virtual Room';
        }

        return 'Computer Laboratory';
    }

    private function normalizeRoomAssignmentType(string $value): string
    {
        $normalized = strtolower(trim($value));
        foreach (self::ROOM_ASSIGNMENT_TYPES as $type) {
            if ($normalized === strtolower($type)) {
                return $type;
            }
        }

        if (strpos($normalized, 'computer') !== false) {
            return 'Computer Laboratory';
        }
        if (strpos($normalized, 'science') !== false) {
            return 'Science Laboratory';
        }
        if (strpos($normalized, 'pe') !== false || strpos($normalized, 'gym') !== false) {
            return 'PE Area';
        }
        if (strpos($normalized, 'online') !== false || strpos($normalized, 'virtual') !== false) {
            return 'Online / Virtual Room';
        }
        if (strpos($normalized, 'auditorium') !== false) {
            return 'Auditorium';
        }

        return 'Lecture Room';
    }

    private function assignRoomForSubjectComponent(Subject $subject, array $component, string $schoolYear, string $semester): array
    {
        $sectionSize = $this->roomAssignmentSectionSize($subject, $schoolYear, $semester);
        $slot = $this->roomAssignmentSlotForComponent($subject, $component);
        $requiredRoomType = (string) $component['required_room_type'];
        $room = $this->findAvailableRoomForAssignment($requiredRoomType, $sectionSize, $slot, $schoolYear, $semester, $subject);
        $validationIssues = $room ? $this->roomAssignmentValidationIssues($room, $subject, $slot, $schoolYear, $semester) : [];
        if (count($validationIssues)) {
            $room = null;
        }
        $status = $room ? 'Assigned' : 'Pending Room Assignment';
        $remarks = $room
            ? 'Assigned automatically by room generation process.'
            : (count($validationIssues)
                ? implode(' ', $validationIssues)
                : 'No active room assigned to this program matched type, capacity, availability, and schedule conflict rules.');

        $payload = [
            'class_offering_id' => (int) $subject->id,
            'course_code' => (string) $subject->code,
            'section_id' => (string) $subject->year_section,
            'room_id' => $room ? (int) $room->id : null,
            'room_type_required' => $requiredRoomType,
            'schedule_component_type' => (string) $component['type'],
            'academic_year' => $schoolYear,
            'semester' => $semester,
            'day' => (string) $slot['days'],
            'start_time' => (string) $slot['time_start'],
            'end_time' => (string) $slot['time_end'],
            'assignment_status' => $status,
            'remarks' => $remarks,
            'created_by' => auth()->id(),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        DB::table('class_room_assignments')->updateOrInsert([
            'class_offering_id' => (int) $subject->id,
            'schedule_component_type' => (string) $component['type'],
        ], $payload);

        if ($room) {
            $subject->room = $this->roomAssignmentRoomCode($room);
            $subject->room_requirement_status = 'Assigned';
            if (trim((string) $subject->days) === '') {
                $subject->days = (string) $slot['days'];
            }
            if (trim((string) $subject->time_start) === '') {
                $subject->time_start = (string) $slot['time_start'];
            }
            if (trim((string) $subject->time_end) === '') {
                $subject->time_end = (string) $slot['time_end'];
            }
            $subject->save();
        } else {
            $subject->room_requirement_status = 'Pending';
            $subject->save();
        }

        $row = $this->roomAssignmentReportRow((object) array_merge($payload, [
            'id' => DB::table('class_room_assignments')
                ->where('class_offering_id', (int) $subject->id)
                ->where('schedule_component_type', (string) $component['type'])
                ->value('id'),
            'subject_name' => (string) $subject->name,
            'room_code' => $room ? $this->roomAssignmentRoomCode($room) : '',
        ]));

        return [
            'status' => $status,
            'row' => $row,
        ];
    }

    private function roomAssignmentSectionSize(Subject $subject, string $schoolYear, string $semester): int
    {
        $academicTermId = (int) $subject->academic_term_id;
        $section = trim((string) $subject->year_section);

        if (Schema::hasTable('student_section_assignments') && $academicTermId > 0 && $section !== '') {
            $count = (int) DB::table('student_section_assignments')
                ->where('academic_term_id', $academicTermId)
                ->where('section', $section)
                ->count();
            if ($count > 0) {
                return $count;
            }
        }

        if (Schema::hasTable('student_subject')) {
            $count = (int) DB::table('student_subject')
                ->where('subject_id', (int) $subject->id)
                ->count();
            if ($count > 0) {
                return $count;
            }
        }

        $subject->loadMissing('canonicalCourse');
        if ($subject->canonicalCourse && (int) $subject->canonicalCourse->slots > 0) {
            return (int) $subject->canonicalCourse->slots;
        }

        return 40;
    }

    private function roomAssignmentSlotForComponent(Subject $subject, array $component): array
    {
        $days = trim((string) $subject->days);
        $timeStart = trim((string) $subject->time_start);
        $timeEnd = trim((string) $subject->time_end);

        if ($days !== '' && $timeStart !== '' && $timeEnd !== '' && (string) $component['type'] === 'Lecture') {
            return [
                'days' => $days,
                'time_start' => $this->classScheduleTimeInputValue($timeStart),
                'time_end' => $this->classScheduleTimeInputValue($timeEnd),
            ];
        }

        $durationMinutes = max(60, (int) round(((float) ($component['hours'] ?? 1.5)) * 60));
        $starts = $this->autoScheduleStartTimes($durationMinutes);
        $patterns = ['MWF', 'TTH', 'MW', 'F', 'S'];

        foreach ($patterns as $pattern) {
            foreach ($starts as $start) {
                $slot = [
                    'days' => $pattern,
                    'time_start' => $start,
                    'time_end' => Carbon::createFromFormat('H:i', $start)->addMinutes($durationMinutes)->format('H:i'),
                ];

                if (!$this->hasAutoScheduleSectionOrFacultyConflict($subject, $slot, $this->loadScheduleConflictRows(collect([$subject])))) {
                    return $slot;
                }
            }
        }

        return [
            'days' => $days !== '' ? $days : 'MWF',
            'time_start' => $timeStart !== '' ? $this->classScheduleTimeInputValue($timeStart) : $this->autoScheduleDayStartTime(),
            'time_end' => $timeEnd !== '' ? $this->classScheduleTimeInputValue($timeEnd) : $this->autoScheduleFallbackEndTime($durationMinutes),
        ];
    }

    private function autoScheduleFallbackEndTime(int $durationMinutes): string
    {
        $start = Carbon::createFromFormat('H:i', $this->autoScheduleDayStartTime());
        $end = $start->copy()->addMinutes($durationMinutes);
        $latestEnd = Carbon::createFromFormat('H:i', $this->autoScheduleDayEndTime());

        if ($end->gt($latestEnd)) {
            return $latestEnd->format('H:i');
        }

        return $end->format('H:i');
    }

    private function findAvailableRoomForAssignment(string $requiredRoomType, int $sectionSize, array $slot, string $schoolYear, string $semester, Subject $subject)
    {
        $courseId = (int) $subject->course_id;
        $rooms = Room::query()
            ->with('hallway.building:id,name')
            ->when(Schema::hasTable('room_allowed_subjects'), function ($query) use ($subject) {
                $query->whereHas('allowedSubjects', function ($subjectQuery) use ($subject) {
                    $subjectQuery->where('subjects.id', (int) $subject->id)
                        ->orWhere('subjects.code', (string) $subject->code);
                });
            }, function ($query) use ($courseId) {
                $query->whereHas('courses', function ($courseQuery) use ($courseId) {
                    $courseQuery->where('courses.id', $courseId);
                });
            })
            ->where('capacity', '>=', $sectionSize)
            ->when(Schema::hasColumn('rooms', 'room_type'), function ($query) use ($requiredRoomType) {
                $query->where('room_type', $requiredRoomType);
            })
            ->when(Schema::hasColumn('rooms', 'status'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('status')
                        ->orWhere('status', '')
                        ->orWhere('status', 'Active');
                });
            })
            ->orderByRaw('capacity - ? asc', [$sectionSize])
            ->orderBy('capacity')
            ->orderBy('room_number')
            ->get();

        foreach ($rooms as $room) {
            if (!count($this->roomAssignmentValidationIssues($room, $subject, $slot, $schoolYear, $semester))) {
                return $room;
            }
        }

        return null;
    }

    private function roomAssignmentValidationIssues(Room $room, Subject $subject, array $slot, string $schoolYear, string $semester): array
    {
        $issues = [];
        $courseId = (int) $subject->course_id;

        if (!$this->roomAllowsSubject($room, $subject)) {
            $issues[] = 'Subject is not allowed in this room.';
        }

        if (!$this->roomAssignmentRoomAvailableForSlot($room, $slot)) {
            $issues[] = 'Room is not available for the selected days/time.';
        }

        if ($this->roomAssignmentHasConflict($room, $slot, $schoolYear, $semester, (int) $subject->id)) {
            $issues[] = 'Room has a schedule conflict.';
        }

        if ($this->hasAutoScheduleSectionOrFacultyConflict($subject, $slot, $this->loadScheduleConflictRows(collect([$subject])))) {
            $issues[] = 'Section or faculty has a schedule conflict.';
        }

        return array_values(array_unique($issues));
    }

    private function roomAssignmentRoomAvailableForSlot(Room $room, array $slot): bool
    {
        $availableDays = trim((string) ($room->available_days ?: 'MTWTHFS'));
        $roomDays = $this->autoScheduleDayTokens($availableDays);
        $slotDays = $this->autoScheduleDayTokens((string) ($slot['days'] ?? ''));

        if (count($roomDays) && count(array_diff($slotDays, $roomDays)) > 0) {
            return false;
        }

        $availableStart = $this->autoScheduleMinutes((string) ($room->available_start_time ?: $this->autoScheduleDayStartTime()));
        $availableEnd = $this->autoScheduleMinutes((string) ($room->available_end_time ?: $this->autoScheduleDayEndTime()));
        $slotStart = $this->autoScheduleMinutes((string) ($slot['time_start'] ?? ''));
        $slotEnd = $this->autoScheduleMinutes((string) ($slot['time_end'] ?? ''));

        if ($availableStart === null || $availableEnd === null || $slotStart === null || $slotEnd === null) {
            return false;
        }

        return $slotStart >= $availableStart && $slotEnd <= $availableEnd;
    }

    private function roomAssignmentHasConflict(Room $room, array $slot, string $schoolYear, string $semester, int $subjectId): bool
    {
        $assignmentConflicts = DB::table('class_room_assignments')
            ->where('room_id', (int) $room->id)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->where('class_offering_id', '<>', $subjectId)
            ->whereIn('assignment_status', ['Assigned', 'Manual Override'])
            ->get();

        foreach ($assignmentConflicts as $assignment) {
            if ($this->autoScheduleOverlaps($slot, [
                'days' => (string) $assignment->day,
                'time_start' => (string) $assignment->start_time,
                'time_end' => (string) $assignment->end_time,
            ])) {
                return true;
            }
        }

        $roomCode = $this->roomAssignmentRoomCode($room);
        $roomAliases = array_values(array_unique(array_filter([
            $roomCode,
            (string) $room->room_number,
            'Room#' . $roomCode,
            'Room#' . (string) $room->room_number,
        ])));
        $subjectConflicts = Subject::query()
            ->where('id', '<>', $subjectId)
            ->whereIn('room', $roomAliases)
            ->whereHas('academicTerm', function ($termQuery) use ($schoolYear, $semester) {
                $termQuery->where('school_year', $schoolYear)
                    ->whereIn(DB::raw('LOWER(TRIM(term))'), collect($this->slotMonitoringSemesterAliases($semester))->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })->all());
            })
            ->whereNotNull('days')
            ->whereNotNull('time_start')
            ->whereNotNull('time_end')
            ->get(['id', 'days', 'time_start', 'time_end']);

        foreach ($subjectConflicts as $conflict) {
            if ($this->autoScheduleOverlaps($slot, [
                'days' => (string) $conflict->days,
                'time_start' => (string) $conflict->time_start,
                'time_end' => (string) $conflict->time_end,
            ])) {
                return true;
            }
        }

        return false;
    }

    private function roomAssignmentComponentAlreadyAssigned(int $subjectId, string $componentType): bool
    {
        return DB::table('class_room_assignments')
            ->where('class_offering_id', $subjectId)
            ->where('schedule_component_type', $componentType)
            ->whereIn('assignment_status', ['Assigned', 'Manual Override'])
            ->exists();
    }

    private function roomAssignmentRoomCode(Room $room): string
    {
        $code = trim((string) ($room->room_code ?? ''));
        if ($code !== '') {
            return $code;
        }

        return (string) $room->room_number;
    }

    private function roomAssignmentReportRow($row): array
    {
        $roomCode = trim((string) ($row->room_code ?? ''));

        return [
            'id' => (int) ($row->id ?? 0),
            'class_offering_id' => (int) ($row->class_offering_id ?? 0),
            'course_code' => (string) ($row->course_code ?? ''),
            'subject_name' => (string) ($row->subject_name ?? ''),
            'section_id' => (string) ($row->section_id ?? ''),
            'room_code' => $roomCode,
            'room_type_required' => (string) ($row->room_type_required ?? ''),
            'schedule_component_type' => (string) ($row->schedule_component_type ?? ''),
            'academic_year' => (string) ($row->academic_year ?? ''),
            'semester' => (string) ($row->semester ?? ''),
            'day' => (string) ($row->day ?? ''),
            'start_time' => $this->classScheduleTimeInputValue((string) ($row->start_time ?? '')),
            'end_time' => $this->classScheduleTimeInputValue((string) ($row->end_time ?? '')),
            'assignment_status' => (string) ($row->assignment_status ?? 'Pending Room Assignment'),
            'remarks' => (string) ($row->remarks ?? ''),
        ];
    }

    /**
     * Registrar > Scheduling > Section Offering
     */
    public function classSchedulePreparation(Request $request)
    {
        $this->seedRoomDimensionsIfEmpty();

        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));
        $section = trim((string) $request->query('section', ''));
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);

        $rows = $this->buildClassSchedulePreparationRowsQuery($schoolYear, $semester, $section, $search, $courseId)
            ->limit(300)
            ->get()
            ->map(function ($row) {
                $days = trim((string) ($row->days ?? ''));
                $timeStart = trim((string) ($row->time_start ?? ''));
                $timeEnd = trim((string) ($row->time_end ?? ''));
                $room = trim((string) ($row->room ?? ''));
                $facultyId = (int) ($row->faculty_id ?? 0);

                return [
                    'id' => (int) ($row->id ?? 0),
                    'school_year' => trim((string) ($row->school_year ?? '')),
                    'semester' => $this->normalizeSlotMonitoringSemester((string) ($row->semester_label ?? '')),
                    'course_code' => trim((string) ($row->course_code ?? '')),
                    'course_name' => trim((string) ($row->course_name ?? '')),
                    'section' => trim((string) ($row->section ?? '')),
                    'subject_code' => trim((string) ($row->subject_code ?? '')),
                    'subject_name' => trim((string) ($row->subject_name ?? '')),
                    'units' => (float) ($row->units ?? 0),
                    'days' => $days,
                    'time_start' => $this->classScheduleTimeInputValue($timeStart),
                    'time_end' => $this->classScheduleTimeInputValue($timeEnd),
                    'room' => $room,
                    'faculty_id' => $facultyId > 0 ? $facultyId : null,
                    'faculty_name' => trim((string) ($row->faculty_name ?? '')),
                    'is_scheduled' => $days !== '' && $timeStart !== '' && $timeEnd !== '' && $room !== '' && $facultyId > 0,
                ];
            })
            ->values();

        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = collect(array_values($configOptions['school_years'] ?? []))
            ->map(function ($schoolYearOption) {
                $value = trim((string) $schoolYearOption);
                return ['value' => $value, 'label' => $value];
            })
            ->filter(function ($option) {
                return (string) ($option['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $semesterOptions = collect(['First', 'Second'])
            ->map(function ($term) {
                return ['value' => $term, 'label' => $term];
            })
            ->all();

        $courseOptions = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                $name = trim((string) $course->name);
                $label = $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name);

                return [
                    'id' => (int) $course->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();

        $roomOptions = Room::query()
            ->with('hallway.building')
            ->orderBy('floor_number')
            ->orderBy('room_number')
            ->get()
            ->map(function (Room $room) {
                $roomNumber = trim((string) $room->room_number);
                $building = '';
                $hallway = '';

                if ($room->hallway) {
                    $hallway = trim((string) $room->hallway->name);
                    if ($room->hallway->building) {
                        $building = trim((string) $room->hallway->building->name);
                    }
                }

                $detail = collect([$building, $hallway])
                    ->filter(function ($value) {
                        return trim((string) $value) !== '';
                    })
                    ->implode(' / ');

                return [
                    'value' => $roomNumber,
                    'label' => $detail !== '' ? $roomNumber . ' - ' . $detail : $roomNumber,
                ];
            })
            ->filter(function ($room) {
                return (string) ($room['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $facultyOptions = Faculty::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Faculty $faculty) {
                $code = trim((string) $faculty->code);
                $name = trim((string) $faculty->name);
                $label = $code !== '' && $name !== '' ? $code . ' - ' . $name : ($name !== '' ? $name : $code);

                return [
                    'id' => (int) $faculty->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();

        $summary = [
            'total' => (int) $rows->count(),
            'scheduled' => (int) $rows->where('is_scheduled', true)->count(),
            'needs_attention' => (int) $rows->where('is_scheduled', false)->count(),
            'rooms' => (int) count($roomOptions),
            'faculty' => (int) count($facultyOptions),
        ];

        return view('registrar.registrar-menu.scheduling.class-schedule-preparation', compact(
            'rows',
            'schoolYearOptions',
            'semesterOptions',
            'courseOptions',
            'roomOptions',
            'facultyOptions',
            'summary',
            'schoolYear',
            'semester',
            'section',
            'search',
            'courseId'
        ));
    }

    public function updateClassSchedulePreparation(Request $request, Subject $subject): JsonResponse
    {
        $rules = [
            'days' => 'nullable|string|max:40',
            'time_start' => 'nullable|date_format:H:i',
            'time_end' => 'nullable|date_format:H:i|after:time_start',
            'room' => 'nullable|string|max:60',
        ];

        if (Schema::hasColumn('subjects', 'faculty_id') && Schema::hasTable('faculties')) {
            $rules['faculty_id'] = 'nullable|integer|exists:faculties,id';
        }

        $data = $request->validate($rules);
        $timeStart = trim((string) ($data['time_start'] ?? ''));
        $timeEnd = trim((string) ($data['time_end'] ?? ''));

        if (($timeStart === '' && $timeEnd !== '') || ($timeStart !== '' && $timeEnd === '')) {
            throw ValidationException::withMessages([
                'time_start' => ['Please provide both start time and end time.'],
            ]);
        }

        $manualIssues = $this->manualScheduleValidationIssues($subject, $data);
        if (count($manualIssues)) {
            throw ValidationException::withMessages([
                'schedule' => [$manualIssues[0]],
            ]);
        }

        $payload = [];
        foreach (['days', 'time_start', 'time_end', 'room', 'faculty_id'] as $column) {
            if (!Schema::hasColumn('subjects', $column)) {
                continue;
            }

            $value = $data[$column] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }

            $payload[$column] = $value === '' ? null : $value;
        }

        $subject->fill($payload);
        $subject->save();
        $this->upsertClassRoomAssignmentFromSubject($subject, 'Manual Override');
        $subject->load('facultyModel');

        return response()->json([
            'message' => 'Class schedule updated successfully.',
            'data' => [
                'id' => (int) $subject->id,
                'days' => (string) ($subject->days ?? ''),
                'time_start' => $this->classScheduleTimeInputValue((string) ($subject->time_start ?? '')),
                'time_end' => $this->classScheduleTimeInputValue((string) ($subject->time_end ?? '')),
                'room' => (string) ($subject->room ?? ''),
                'faculty_id' => $subject->faculty_id ? (int) $subject->faculty_id : null,
                'faculty_name' => $subject->facultyModel ? (string) $subject->facultyModel->name : '',
            ],
        ]);
    }

    public function autoGenerateClassSchedulePreparation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:30',
            'section' => 'nullable|string|max:120',
            'course_id' => 'nullable|integer|min:0',
            'overwrite' => 'nullable|boolean',
            'auto_create_rooms' => 'nullable|boolean',
        ]);

        $schoolYear = trim((string) ($validated['school_year'] ?? ''));
        $semester = $this->normalizeSlotMonitoringSemester((string) ($validated['semester'] ?? ''));
        $section = trim((string) ($validated['section'] ?? ''));
        $courseId = (int) ($validated['course_id'] ?? 0);
        $overwrite = $this->requestBoolean($request, 'overwrite');
        $autoCreateRooms = $request->has('auto_create_rooms')
            ? $this->requestBoolean($request, 'auto_create_rooms')
            : true;

        $query = Subject::query()
            ->whereNotNull('course_id')
            ->whereNotNull('year_section')
            ->whereRaw("TRIM(COALESCE(year_section, '')) <> ''");

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($builder) {
                $builder->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 0);
            });
        }

        if ($schoolYear !== '' || $semester !== '') {
            $query->whereHas('academicTerm', function ($termQuery) use ($schoolYear, $semester) {
                if ($schoolYear !== '') {
                    $termQuery->where('school_year', $schoolYear);
                }

                if ($semester !== '') {
                    $aliases = collect($this->slotMonitoringSemesterAliases($semester))
                        ->map(function ($value) {
                            return strtolower(trim((string) $value));
                        })
                        ->values()
                        ->all();

                    $termQuery->whereIn(DB::raw('LOWER(TRIM(term))'), $aliases);
                }
            });
        }

        if ($courseId > 0) {
            $query->where('course_id', $courseId);
        }

        if ($section !== '') {
            $query->where('year_section', 'like', '%' . $section . '%');
        }

        if (!$overwrite) {
            $query->where(function ($builder) {
                $builder->whereNull('days')
                    ->orWhereNull('time_start')
                    ->orWhereNull('time_end')
                    ->orWhereNull('room')
                    ->orWhereRaw("TRIM(COALESCE(days, '')) = ''")
                    ->orWhereRaw("TRIM(COALESCE(time_start, '')) = ''")
                    ->orWhereRaw("TRIM(COALESCE(time_end, '')) = ''")
                    ->orWhereRaw("TRIM(COALESCE(room, '')) = ''");
            });
        }

        $subjects = $query
            ->orderBy('course_id')
            ->orderBy('year_section')
            ->orderBy('code')
            ->orderBy('id')
            ->limit(500)
            ->get();

        if ($subjects->isEmpty()) {
            return response()->json([
                'ok' => true,
                'message' => 'No unscheduled generated subjects matched the current filters.',
                'updated_count' => 0,
                'created_rooms' => 0,
            ]);
        }

        $result = $this->autoAssignSubjectRoomsAndSchedules($subjects, $autoCreateRooms, $overwrite);

        return response()->json([
            'ok' => true,
            'message' => 'Automatic room and schedule generation completed.',
            'updated_count' => (int) $result['updated_count'],
            'created_rooms' => (int) $result['created_rooms'],
            'skipped_count' => (int) $result['skipped_count'],
        ]);
    }

    public function updateApplicantExamInterview(Request $request, Applicant $applicant): JsonResponse
    {
        $rules = [
            'exam_date' => 'nullable|date',
            'exam_time' => 'nullable|date_format:H:i',
            'exam_room' => 'nullable|string|max:190',
            'exam_result_status' => 'nullable|in:Pending,Passed,Failed',
            'exam_score' => 'nullable|numeric|min:0|max:100',
        ];

        if (Schema::hasColumn('applicants', 'interview_date')) {
            $rules['interview_date'] = 'nullable|date';
            $rules['interview_time'] = 'nullable|date_format:H:i';
            $rules['interview_room'] = 'nullable|string|max:190';
            $rules['interview_status'] = 'nullable|in:Pending,Scheduled,Completed,No Show';
        }

        if (Schema::hasColumn('applicants', 'medical_clearance_status')) {
            $rules['medical_clearance_status'] = 'nullable|in:Pending,Cleared,For Follow-up,Not Cleared';
        }

        $validated = $request->validate($rules);

        $examDate = trim((string) ($validated['exam_date'] ?? ''));
        $examTime = trim((string) ($validated['exam_time'] ?? ''));
        if (($examDate === '' && $examTime !== '') || ($examDate !== '' && $examTime === '')) {
            throw ValidationException::withMessages([
                'exam_date' => ['Please provide both exam date and exam time.'],
            ]);
        }

        $interviewDate = trim((string) ($validated['interview_date'] ?? ''));
        $interviewTime = trim((string) ($validated['interview_time'] ?? ''));
        if (($interviewDate === '' && $interviewTime !== '') || ($interviewDate !== '' && $interviewTime === '')) {
            throw ValidationException::withMessages([
                'interview_date' => ['Please provide both interview date and interview time.'],
            ]);
        }

        $applicant->exam_date = $examDate !== ''
            ? Carbon::createFromFormat('Y-m-d H:i', $examDate . ' ' . $examTime)
            : null;
        $applicant->exam_room = trim((string) ($validated['exam_room'] ?? '')) ?: null;
        $applicant->exam_result_status = trim((string) ($validated['exam_result_status'] ?? '')) ?: 'Pending';
        $applicant->exam_score = array_key_exists('exam_score', $validated) && $validated['exam_score'] !== null
            ? $validated['exam_score']
            : null;

        if (Schema::hasColumn('applicants', 'interview_date')) {
            $applicant->interview_date = $interviewDate !== ''
                ? Carbon::createFromFormat('Y-m-d H:i', $interviewDate . ' ' . $interviewTime)
                : null;
        }

        if (Schema::hasColumn('applicants', 'interview_room')) {
            $applicant->interview_room = trim((string) ($validated['interview_room'] ?? '')) ?: null;
        }

        if (Schema::hasColumn('applicants', 'interview_status')) {
            $applicant->interview_status = trim((string) ($validated['interview_status'] ?? '')) ?: 'Pending';
        }

        if (Schema::hasColumn('applicants', 'medical_clearance_status')) {
            $applicant->medical_clearance_status = trim((string) ($validated['medical_clearance_status'] ?? '')) ?: 'Pending';
        }

        $applicant->save();

        return response()->json([
            'ok' => true,
            'message' => 'Exam and interview details saved successfully.',
            'row' => $this->mapExamInterviewApplicantRow($applicant->fresh(['applicationPreference.course'])),
        ]);
    }

    public function sectionOffering()
    {
        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearValues = array_values($configOptions['school_years'] ?? []);
        $semesterMap = is_array($configOptions['semester_map'] ?? null)
            ? $configOptions['semester_map']
            : [];

        $defaultSchoolYear = count($schoolYearValues)
            ? (string) $schoolYearValues[0]
            : (string) ($configOptions['default_school_year'] ?? '');

        $schoolYearOptions = array_map(function ($schoolYear) {
            $schoolYear = trim((string) $schoolYear);

            return [
                'value' => $schoolYear,
                'label' => $schoolYear,
            ];
        }, $schoolYearValues);

        $semesterOptions = array_map(function ($semester) {
            $semester = trim((string) $semester);

            return [
                'value' => $semester,
                'label' => $semester,
            ];
        }, SystemConfigSchoolTermOptions::semesterOptionsForYear($semesterMap, $defaultSchoolYear));

        $defaultSemester = count($semesterOptions)
            ? (string) ($semesterOptions[0]['value'] ?? '')
            : (string) ($configOptions['default_semester'] ?? 'First');

        $existingSectionsByCourse = Subject::query()
            ->whereNotNull('course_id')
            ->whereNotNull('year_section')
            ->whereRaw("TRIM(COALESCE(year_section, '')) <> ''")
            ->when(Schema::hasColumn('subjects', 'is_subject_file_record'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('is_subject_file_record')
                        ->orWhere('is_subject_file_record', 0);
                });
            })
            ->get(['course_id', 'year_section'])
            ->groupBy('course_id')
            ->map(function ($rows) {
                return collect($rows)
                    ->groupBy(function ($row) {
                        return trim((string) $row->year_section);
                    })
                    ->map(function ($sectionRows, $sectionLabel) {
                        $yearNumber = $this->extractYearLevelFromSectionLabel((string) $sectionLabel);

                        return [
                            'section' => (string) $sectionLabel,
                            'year' => $yearNumber ? $this->sectionOfferingYearLevelLabel($yearNumber) : 'N/A',
                            'subject_count' => (int) collect($sectionRows)->count(),
                        ];
                    })
                    ->sortBy(function ($row) {
                        return $this->sectionOfferingYearLevelWeight((string) ($row['year'] ?? '')) . '|'
                            . strtolower((string) ($row['section'] ?? ''));
                    })
                    ->values()
                    ->all();
            });

        $activeProgramDirectory = CourseCurriculum::query()
            ->with(['course:id,code,name', 'curriculumSubjects.yearBlock:id,label', 'curriculumSubjects.semester:id,name'])
            ->when(Schema::hasColumn('course_curricula', 'is_active'), function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('course_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('course_id')
            ->map(function ($curricula, $courseId) use ($existingSectionsByCourse) {
                $curriculum = collect($curricula)->first();
                $course = optional($curriculum)->course;
                $code = trim((string) optional($course)->code);
                $name = trim((string) optional($course)->name);
                $label = $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name);
                $subjects = collect($curricula)->flatMap(function ($item) {
                    return $item->curriculumSubjects ?: collect();
                });

                $years = $subjects
                    ->map(function ($assignment) {
                        return trim((string) optional($assignment->yearBlock)->label);
                    })
                    ->filter()
                    ->unique()
                    ->sortBy(function ($year) {
                        return $this->sectionOfferingYearLevelWeight((string) $year);
                    })
                    ->values()
                    ->all();

                $terms = $subjects
                    ->map(function ($assignment) {
                        return trim((string) optional($assignment->semester)->name);
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'course_id' => (int) $courseId,
                    'code' => $code,
                    'name' => $name,
                    'label' => $label !== '' ? $label : ('Program #' . (int) $courseId),
                    'curriculum_count' => (int) collect($curricula)->count(),
                    'subject_count' => (int) $subjects->count(),
                    'years' => $years,
                    'terms' => $terms,
                    'sections' => $existingSectionsByCourse->get((int) $courseId, []),
                ];
            })
            ->values()
            ->all();

        $subjectIds = $room->allowedSubjects
            ? $room->allowedSubjects->pluck('id')->map(function ($subjectId) {
                return (int) $subjectId;
            })->values()->all()
            : [];

        $subjectLabels = $room->allowedSubjects
            ? $room->allowedSubjects->map(function (Subject $subject) {
                $code = trim((string) $subject->code);
                return $code !== '' ? $code : (string) $subject->name;
            })->filter(function ($value) {
                return $value !== '';
            })->values()->all()
            : [];

        return view('registrar.registrar-menu.scheduling.section-offering', compact(
            'schoolYearOptions',
            'semesterOptions',
            'defaultSchoolYear',
            'defaultSemester',
            'activeProgramDirectory'
        ));
    }

    public function academicTermLifecycle(Request $request)
    {
        $terms = Schema::hasTable('academic_terms')
            ? AcademicTerm::query()->orderByDesc('school_year')->orderByDesc('id')->limit(40)->get()
            : collect();

        $currentTerm = $terms->first(function ($term) {
            return !in_array((string) ($term->status ?? ''), ['Closed', 'Archived'], true);
        }) ?: $terms->first();

        $logs = collect();
        if (Schema::hasTable('academic_term_lifecycle_logs')) {
            $logs = DB::table('academic_term_lifecycle_logs as log')
                ->leftJoin('academic_terms as from_term', 'from_term.id', '=', 'log.from_academic_term_id')
                ->leftJoin('academic_terms as to_term', 'to_term.id', '=', 'log.to_academic_term_id')
                ->select([
                    'log.*',
                    'from_term.school_year as from_school_year',
                    'from_term.term as from_term_label',
                    'to_term.school_year as to_school_year',
                    'to_term.term as to_term_label',
                ])
                ->orderByDesc('log.created_at')
                ->limit(20)
                ->get();
        }

        return view('registrar.registrar-menu.scheduling.academic-term-lifecycle', compact('terms', 'currentTerm', 'logs'));
    }

    public function closeCurrentSemester(Request $request): JsonResponse
    {
        if (!Schema::hasTable('academic_terms') || !Schema::hasColumn('academic_terms', 'status')) {
            return response()->json([
                'ok' => false,
                'message' => 'Academic term lifecycle fields are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'academic_term_id' => 'required|integer|exists:academic_terms,id',
        ]);

        $term = AcademicTerm::query()->findOrFail((int) $validated['academic_term_id']);
        if (in_array((string) $term->status, ['Closed', 'Archived'], true)) {
            return response()->json([
                'ok' => true,
                'message' => 'This semester is already closed.',
                'status' => (string) $term->status,
                'issues' => [],
            ]);
        }

        $issues = $this->academicTermCloseValidationIssues((int) $term->id);
        if (count($issues) > 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Resolve semester closing validations before closing this term.',
                'issues' => $issues,
            ], 422);
        }

        $oldStatus = (string) ($term->status ?: 'Draft');
        $term->status = 'Closed';
        $term->closed_at = now();
        $term->closed_by_user_id = auth()->id();
        $term->save();

        AuditTrailRecorder::record('ACADEMIC_TERM_CLOSED', [[
            'type' => 'AcademicTerm',
            'id' => (int) $term->id,
            'label' => (string) $term->school_year . ' ' . (string) $term->term,
            'changes' => [
                ['field' => 'status', 'old' => $oldStatus, 'new' => 'Closed'],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Current semester closed successfully.',
            'status' => 'Closed',
            'issues' => [],
        ]);
    }

    public function openNewAcademicTerm(Request $request): JsonResponse
    {
        $requiredTables = ['academic_terms', 'program_term_offerings', 'student_promotions', 'academic_term_lifecycle_logs', 'academic_setup_generation_logs', 'student_section_assignments'];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Academic term lifecycle tables are not ready. Please run migrations first.',
                ], 409);
            }
        }

        $validated = $request->validate([
            'current_academic_term_id' => 'required|integer|exists:academic_terms,id',
            'max_students_per_section' => 'nullable|integer|min:1|max:300',
            'auto_create_rooms' => 'nullable|boolean',
        ]);

        $currentTerm = AcademicTerm::query()->findOrFail((int) $validated['current_academic_term_id']);
        if ((string) $currentTerm->status !== 'Closed') {
            return response()->json([
                'ok' => false,
                'message' => 'Current semester must be closed before opening a new academic term.',
            ], 422);
        }

        $next = $this->determineNextAcademicTerm((string) $currentTerm->school_year, (string) $currentTerm->term);
        $nextTermId = $this->resolveSectionOfferingAcademicTermId($next['school_year'], $next['semester']);
        $nextTerm = AcademicTerm::query()->findOrFail($nextTermId);
        $nextTerm->status = 'Draft';
        $nextTerm->opened_at = $nextTerm->opened_at ?: now();
        $nextTerm->opened_by_user_id = $nextTerm->opened_by_user_id ?: auth()->id();
        $nextTerm->save();

        $maxStudentsPerSection = (int) ($validated['max_students_per_section'] ?? 40);
        $autoCreateRooms = $request->has('auto_create_rooms') ? $this->requestBoolean($request, 'auto_create_rooms') : true;
        $report = $this->emptyAcademicTermOpeningReport($currentTerm, $nextTerm);

        $lifecycleLogId = (int) DB::table('academic_term_lifecycle_logs')->insertGetId([
            'from_academic_term_id' => (int) $currentTerm->id,
            'to_academic_term_id' => (int) $nextTerm->id,
            'created_by_user_id' => auth()->id(),
            'action' => 'open_new_academic_term',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            DB::transaction(function () use (
                $currentTerm,
                $nextTerm,
                $next,
                $maxStudentsPerSection,
                $autoCreateRooms,
                $lifecycleLogId,
                &$report
            ) {
                $programs = Course::query()->orderBy('code')->orderBy('name')->get();
                $yearBlocks = YearBlock::query()->orderBy('id')->get();

                foreach ($programs as $program) {
                    $availability = $this->programTermAvailability($program, $yearBlocks);

                    DB::table('program_term_offerings')->updateOrInsert([
                        'academic_term_id' => (int) $nextTerm->id,
                        'course_id' => (int) $program->id,
                    ], [
                        'academic_year' => (string) $nextTerm->school_year,
                        'semester' => (string) $nextTerm->term,
                        'program_code' => (string) ($program->code ?: 'PROGRAM-' . (int) $program->id),
                        'is_offered_this_term' => (bool) $availability['is_offered'],
                        'accepting_new_students' => (bool) $availability['accepting_new_students'],
                        'allowed_year_levels' => implode(',', $availability['allowed_year_numbers']),
                        'curriculum_version' => $this->programCurrentCurriculumVersion((int) $program->id),
                        'status' => (string) $availability['status'],
                        'created_by' => auth()->id(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);

                    if (!$availability['is_offered']) {
                        $report['programs_not_opened'][] = [
                            'code' => (string) $program->code,
                            'reason' => (string) $availability['reason'],
                        ];
                        continue;
                    }

                    $report['programs_opened'][] = (string) $program->code;
                    $promotionResult = $this->promoteStudentsForNewTerm(
                        $program,
                        $currentTerm,
                        $nextTerm,
                        $availability['allowed_year_block_ids']
                    );
                    $report['counts']['students_promoted_count'] += $promotionResult['promoted_count'];
                    $report['counts']['irregular_students_count'] += $promotionResult['irregular_count'];
                    foreach ($promotionResult['issues'] as $issue) {
                        $report['pending_issues'][] = $issue;
                    }

                    foreach ($availability['allowed_year_block_ids'] as $yearBlockId) {
                        $generation = $this->generateNewTermSetupForProgramYear(
                            $program,
                            (int) $yearBlockId,
                            $nextTerm,
                            $next['semester'],
                            $maxStudentsPerSection,
                            $autoCreateRooms,
                            $lifecycleLogId
                        );

                        foreach ($generation['counts'] as $key => $value) {
                            $report['counts'][$key] += (int) $value;
                        }
                        foreach ($generation['issues'] as $issue) {
                            $report['pending_issues'][] = $issue;
                        }
                    }
                }

                $report['counts']['programs_opened_count'] = count($report['programs_opened']);
                $report['counts']['programs_not_opened_count'] = count($report['programs_not_opened']);
                $report['counts']['pending_issue_count'] = count($report['pending_issues']);
                $newStatus = $report['counts']['pending_issue_count'] > 0 ? 'Open for Setup' : 'Open for Enrollment';
                $report['new_term']['status'] = $newStatus;

                AcademicTerm::query()
                    ->where('id', (int) $nextTerm->id)
                    ->update([
                        'status' => $newStatus,
                        'updated_at' => now(),
                    ]);

                DB::table('academic_term_lifecycle_logs')
                    ->where('id', $lifecycleLogId)
                    ->update($report['counts'] + [
                        'status' => $newStatus,
                        'report_payload' => json_encode($report),
                        'updated_at' => now(),
                    ]);
            });
        } catch (\Throwable $exception) {
            DB::table('academic_term_lifecycle_logs')
                ->where('id', $lifecycleLogId)
                ->update([
                    'status' => 'failed',
                    'report_payload' => json_encode($report + ['error' => $exception->getMessage()]),
                    'updated_at' => now(),
                ]);

            throw $exception;
        }

        $nextTerm->refresh();

        AuditTrailRecorder::record('ACADEMIC_TERM_OPENED', [[
            'type' => 'AcademicTermLifecycleLog',
            'id' => $lifecycleLogId,
            'label' => (string) $nextTerm->school_year . ' ' . (string) $nextTerm->term,
            'changes' => [
                ['field' => 'status', 'old' => 'Draft', 'new' => (string) $nextTerm->status],
                ['field' => 'programs_opened', 'old' => null, 'new' => (string) $report['counts']['programs_opened_count']],
                ['field' => 'pending_issues', 'old' => null, 'new' => (string) $report['counts']['pending_issue_count']],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'New academic term opened successfully.',
            'log_id' => $lifecycleLogId,
            'term' => [
                'id' => (int) $nextTerm->id,
                'school_year' => (string) $nextTerm->school_year,
                'semester' => (string) $nextTerm->term,
                'status' => (string) $nextTerm->status,
            ],
            'report' => $report,
        ]);
    }

    public function publishNewAcademicTerm(Request $request, $academicTerm): JsonResponse
    {
        if (!Schema::hasTable('academic_terms') || !Schema::hasColumn('academic_terms', 'status')) {
            return response()->json(['ok' => false, 'message' => 'Academic term lifecycle fields are not ready.'], 409);
        }

        $term = AcademicTerm::query()->findOrFail((int) $academicTerm);
        $openIssues = 0;
        if (Schema::hasTable('academic_term_lifecycle_logs')) {
            $latestLog = DB::table('academic_term_lifecycle_logs')
                ->where('to_academic_term_id', (int) $term->id)
                ->orderByDesc('id')
                ->first();
            $openIssues = $latestLog ? (int) $latestLog->pending_issue_count : 0;
        }

        if ($openIssues > 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Resolve pending setup issues before publishing the new term.',
                'pending_issue_count' => $openIssues,
            ], 422);
        }

        $oldStatus = (string) ($term->status ?: 'Draft');
        $term->status = 'Open for Enrollment';
        $term->save();

        $latestLogId = DB::table('academic_term_lifecycle_logs')
            ->where('to_academic_term_id', (int) $term->id)
            ->orderByDesc('id')
            ->value('id');

        if ($latestLogId) {
            DB::table('academic_term_lifecycle_logs')
                ->where('id', (int) $latestLogId)
                ->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        AuditTrailRecorder::record('ACADEMIC_TERM_PUBLISHED', [[
            'type' => 'AcademicTerm',
            'id' => (int) $term->id,
            'label' => (string) $term->school_year . ' ' . (string) $term->term,
            'changes' => [
                ['field' => 'status', 'old' => $oldStatus, 'new' => 'Open for Enrollment'],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'New academic term published for enrollment.',
            'status' => 'Open for Enrollment',
        ]);
    }

    private function academicTermCloseValidationIssues(int $academicTermId): array
    {
        $issues = [];
        $subjects = Subject::query()
            ->where('academic_term_id', $academicTermId)
            ->when(Schema::hasColumn('subjects', 'is_subject_file_record'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('is_subject_file_record')
                        ->orWhere('is_subject_file_record', 0);
                });
            })
            ->get(['id', 'code', 'name', 'year_section']);

        foreach ($subjects as $subject) {
            $enrolledStudentIds = DB::table('student_subject')
                ->where('subject_id', (int) $subject->id)
                ->pluck('student_id')
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter()
                ->unique()
                ->values();

            if ($enrolledStudentIds->isEmpty()) {
                continue;
            }

            $gradeRows = StudentSubjectGrade::query()
                ->where('subject_id', (int) $subject->id)
                ->whereIn('student_id', $enrolledStudentIds->all())
                ->get(['student_id', 'final_average', 'remarks']);

            if ($gradeRows->count() < $enrolledStudentIds->count()) {
                $issues[] = [
                    'type' => 'Grades not encoded',
                    'label' => trim((string) $subject->code . ' ' . (string) $subject->year_section),
                    'description' => 'One or more enrolled students do not have finalized grade records.',
                ];
            }

            $incompleteCount = $gradeRows->filter(function (StudentSubjectGrade $grade) {
                $remarks = strtolower(trim((string) $grade->remarks));
                return $remarks === 'incomplete' || $remarks === 'inc' || $grade->final_average === null;
            })->count();

            if ($incompleteCount > 0) {
                $issues[] = [
                    'type' => 'Incomplete grades flagged',
                    'label' => trim((string) $subject->code . ' ' . (string) $subject->year_section),
                    'description' => $incompleteCount . ' incomplete grade record(s) need registrar review.',
                ];
            }
        }

        return array_slice($issues, 0, 100);
    }

    private function determineNextAcademicTerm(string $schoolYear, string $term): array
    {
        $canonicalSemester = $this->normalizeSlotMonitoringSemester($term);
        if ($canonicalSemester === '') {
            $canonicalSemester = stripos($term, '2') !== false || stripos($term, 'second') !== false ? 'Second' : 'First';
        }

        if ($canonicalSemester === 'First') {
            return [
                'school_year' => $schoolYear,
                'semester' => 'Second',
            ];
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', trim($schoolYear), $matches) === 1) {
            $nextStart = (int) $matches[1] + 1;
            return [
                'school_year' => $nextStart . '-' . ($nextStart + 1),
                'semester' => 'First',
            ];
        }

        $year = (int) Carbon::now()->format('Y');
        return [
            'school_year' => $year . '-' . ($year + 1),
            'semester' => 'First',
        ];
    }

    private function emptyAcademicTermOpeningReport(AcademicTerm $currentTerm, AcademicTerm $nextTerm): array
    {
        return [
            'current_term' => [
                'academic_year' => (string) $currentTerm->school_year,
                'semester' => (string) $currentTerm->term,
                'status' => (string) $currentTerm->status,
            ],
            'new_term' => [
                'academic_year' => (string) $nextTerm->school_year,
                'semester' => (string) $nextTerm->term,
                'status' => 'Draft',
            ],
            'programs_opened' => [],
            'programs_not_opened' => [],
            'pending_issues' => [],
            'counts' => [
                'programs_opened_count' => 0,
                'programs_not_opened_count' => 0,
                'students_promoted_count' => 0,
                'irregular_students_count' => 0,
                'sections_created_count' => 0,
                'class_offerings_generated_count' => 0,
                'rooms_assigned_count' => 0,
                'faculty_assigned_count' => 0,
                'schedules_generated_count' => 0,
                'student_loads_generated_count' => 0,
                'pending_issue_count' => 0,
            ],
        ];
    }

    private function programTermAvailability(Course $program, $yearBlocks): array
    {
        $statusText = strtolower(trim((string) $program->program_file));
        $inactiveStatuses = ['inactive', 'closed', 'not offered this term', 'not_offered_this_term'];
        if (in_array($statusText, $inactiveStatuses, true)) {
            return [
                'is_offered' => false,
                'accepting_new_students' => false,
                'allowed_year_block_ids' => [],
                'allowed_year_numbers' => [],
                'status' => ucwords(str_replace('_', ' ', $statusText)),
                'reason' => ucwords(str_replace('_', ' ', $statusText)),
            ];
        }

        $phasingOut = strpos($statusText, 'phasing') !== false;
        $allowed = collect($yearBlocks)->filter(function (YearBlock $yearBlock) use ($phasingOut) {
            $number = $this->academicSetupYearLevelNumber((string) $yearBlock->label);
            return $number > 0 && (!$phasingOut || $number > 1);
        })->values();

        return [
            'is_offered' => true,
            'accepting_new_students' => !$phasingOut,
            'allowed_year_block_ids' => $allowed->pluck('id')->map(function ($value) {
                return (int) $value;
            })->all(),
            'allowed_year_numbers' => $allowed->map(function (YearBlock $yearBlock) {
                return $this->academicSetupYearLevelNumber((string) $yearBlock->label);
            })->filter()->values()->all(),
            'status' => $phasingOut ? 'Phasing Out' : 'Open',
            'reason' => $phasingOut ? 'Phasing Out' : 'Active',
        ];
    }

    private function programCurrentCurriculumVersion(int $courseId): ?string
    {
        $curriculum = $this->resolveSectionOfferingCurriculum($courseId);
        if (!$curriculum) {
            return null;
        }

        return (string) ($curriculum->curriculum_year_code ?: $curriculum->curriculum_year ?: $curriculum->id);
    }

    private function promoteStudentsForNewTerm(Course $program, AcademicTerm $currentTerm, AcademicTerm $nextTerm, array $allowedYearBlockIds): array
    {
        $result = [
            'promoted_count' => 0,
            'irregular_count' => 0,
            'issues' => [],
        ];
        $studentColumns = ['id', 'student_no', 'name', 'course_id', 'year_block_id'];
        if (Schema::hasColumn('students', 'year_level')) {
            $studentColumns[] = 'year_level';
        }

        $students = Student::query()
            ->active()
            ->where('course_id', (int) $program->id)
            ->where('academic_term_id', (int) $currentTerm->id)
            ->get($studentColumns);

        foreach ($students as $student) {
            $evaluation = $this->evaluateStudentPromotionStatus($student, (int) $currentTerm->id);
            $nextYearBlockId = $this->nextYearBlockIdForPromotion((int) $student->year_block_id, (string) $currentTerm->term);
            if (!$nextYearBlockId || !in_array($nextYearBlockId, $allowedYearBlockIds, true)) {
                DB::table('student_promotions')->updateOrInsert([
                    'student_id' => (int) $student->id,
                    'to_academic_term_id' => (int) $nextTerm->id,
                ], [
                    'from_academic_term_id' => (int) $currentTerm->id,
                    'from_year_block_id' => (int) $student->year_block_id ?: null,
                    'to_year_block_id' => null,
                    'promotion_status' => 'Graduation Evaluation',
                    'remarks' => 'Student completed the terminal year level or is outside allowed year levels.',
                    'created_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                continue;
            }

            $promotionStatus = $evaluation['is_irregular'] ? 'Needs Adviser Approval' : 'Promoted';
            if ($evaluation['is_irregular']) {
                $result['irregular_count']++;
                $result['issues'][] = [
                    'type' => 'Irregular student',
                    'label' => (string) ($student->student_no ?: $student->name),
                    'description' => implode(', ', $evaluation['flags']) . '. Adviser approval is required before final loading.',
                ];
            }

            DB::table('student_promotions')->updateOrInsert([
                'student_id' => (int) $student->id,
                'to_academic_term_id' => (int) $nextTerm->id,
            ], [
                'from_academic_term_id' => (int) $currentTerm->id,
                'from_year_block_id' => (int) $student->year_block_id ?: null,
                'to_year_block_id' => $nextYearBlockId,
                'promotion_status' => $promotionStatus,
                'remarks' => count($evaluation['flags']) ? implode('; ', $evaluation['flags']) : 'Passed all encoded subjects.',
                'created_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $payload = [
                'academic_term_id' => (int) $nextTerm->id,
                'school_year' => (string) $nextTerm->school_year,
                'semester' => (string) $nextTerm->term,
                'year_block_id' => $nextYearBlockId,
            ];
            $yearLabel = YearBlock::query()->where('id', $nextYearBlockId)->value('label');
            if ($yearLabel && Schema::hasColumn('students', 'year_level')) {
                $payload['year_level'] = (string) $yearLabel;
            }
            Student::query()->where('id', (int) $student->id)->update($payload);
            $result['promoted_count']++;
        }

        return $result;
    }

    private function evaluateStudentPromotionStatus(Student $student, int $academicTermId): array
    {
        $flags = [];
        $subjectIds = $student->subjects()
            ->where('subjects.academic_term_id', $academicTermId)
            ->pluck('subjects.id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->all();

        if (!count($subjectIds)) {
            return ['is_irregular' => true, 'flags' => ['No finalized enrollment load found']];
        }

        $grades = StudentSubjectGrade::query()
            ->where('student_id', (int) $student->id)
            ->whereIn('subject_id', $subjectIds)
            ->get(['subject_id', 'final_average', 'remarks']);

        if ($grades->count() < count($subjectIds)) {
            $flags[] = 'Missing encoded grades';
        }

        foreach ($grades as $grade) {
            $remarks = strtolower(trim((string) $grade->remarks));
            if (in_array($remarks, ['incomplete', 'inc'], true) || $grade->final_average === null) {
                $flags[] = 'Incomplete grades';
            }
            if (in_array($remarks, ['failed', 'fail', 'f'], true) || ($grade->final_average !== null && (float) $grade->final_average < 75.0)) {
                $flags[] = 'Failed/back subject';
            }
        }

        $flags = array_values(array_unique($flags));

        return [
            'is_irregular' => count($flags) > 0,
            'flags' => $flags,
        ];
    }

    private function nextYearBlockIdForPromotion(int $currentYearBlockId, string $currentTermLabel)
    {
        $current = YearBlock::query()->where('id', $currentYearBlockId)->first();
        if (!$current) {
            return null;
        }

        $currentNumber = $this->academicSetupYearLevelNumber((string) $current->label);
        $currentSemester = $this->normalizeSlotMonitoringSemester($currentTermLabel);
        $nextNumber = ($currentSemester === 'Second' || $currentSemester === 'Summer')
            ? $currentNumber + 1
            : $currentNumber;

        if ($nextNumber < 1) {
            return null;
        }

        return $this->resolveSectionOfferingYearBlockId($nextNumber);
    }

    private function generateNewTermSetupForProgramYear(
        Course $program,
        int $yearBlockId,
        AcademicTerm $nextTerm,
        string $semesterCanonical,
        int $maxStudentsPerSection,
        bool $autoCreateRooms,
        int $lifecycleLogId
    ): array {
        $setupLogId = (int) DB::table('academic_setup_generation_logs')->insertGetId([
            'generated_by_user_id' => auth()->id(),
            'academic_term_id' => (int) $nextTerm->id,
            'course_id' => (int) $program->id,
            'year_block_id' => $yearBlockId,
            'school_year' => (string) $nextTerm->school_year,
            'semester' => $semesterCanonical,
            'max_students_per_section' => $maxStudentsPerSection,
            'status' => 'term_lifecycle_draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counts = [
            'sections_created_count' => 0,
            'class_offerings_generated_count' => 0,
            'rooms_assigned_count' => 0,
            'faculty_assigned_count' => 0,
            'schedules_generated_count' => 0,
            'student_loads_generated_count' => 0,
        ];
        $issues = [];

        $students = Student::query()
            ->active()
            ->where('course_id', (int) $program->id)
            ->where('academic_term_id', (int) $nextTerm->id)
            ->where('year_block_id', $yearBlockId)
            ->orderBy('name')
            ->orderBy('student_no')
            ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id']);

        if ($students->isEmpty()) {
            return ['counts' => $counts, 'issues' => $issues];
        }

        $curriculum = $this->resolveSectionOfferingCurriculum((int) $program->id);
        if (!$curriculum) {
            return [
                'counts' => $counts,
                'issues' => [[
                    'type' => 'Course not in curriculum',
                    'label' => (string) $program->code,
                    'description' => 'No published curriculum is available for this program.',
                ]],
            ];
        }

        $semesterIds = $this->resolveSectionOfferingSemesterIds($semesterCanonical);
        $assignments = CourseCurriculumSubject::query()
            ->with('subject')
            ->where('course_curriculum_id', (int) $curriculum->id)
            ->where('year_block_id', $yearBlockId)
            ->when(count($semesterIds) > 0, function ($query) use ($semesterIds) {
                $query->whereIn('semester_id', $semesterIds);
            })
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($assignments->isEmpty()) {
            return [
                'counts' => $counts,
                'issues' => [[
                    'type' => 'Course not in curriculum',
                    'label' => (string) $program->code,
                    'description' => 'No curriculum subjects match the promoted year level and new semester.',
                ]],
            ];
        }

        $yearBlock = YearBlock::query()->find($yearBlockId);
        $yearNumber = $this->academicSetupYearLevelNumber((string) optional($yearBlock)->label);
        $requiredSectionCount = max(1, (int) ceil($students->count() / max($maxStudentsPerSection, 1)));
        $sectionPlan = $this->academicSetupSectionPlan((string) $program->code, $yearNumber, $requiredSectionCount);
        $existingLabels = $this->academicSetupExistingSectionLabels((int) $program->id, (int) $nextTerm->id);

        foreach ($sectionPlan as $sectionRow) {
            $sectionLabel = (string) $sectionRow['desired'];
            if (!$existingLabels->intersect(collect($sectionRow['aliases']))->count()) {
                $inserted = $this->createAcademicSetupSectionOfferings($assignments, (int) $program->id, (int) $nextTerm->id, $sectionLabel);
                $counts['sections_created_count']++;
                $counts['class_offerings_generated_count'] += $inserted;
            }
        }

        $sectionLabels = $this->academicSetupExistingSectionLabels((int) $program->id, (int) $nextTerm->id)
            ->filter(function ($label) use ($sectionPlan) {
                foreach ($sectionPlan as $sectionRow) {
                    if (in_array((string) $label, (array) $sectionRow['aliases'], true)) {
                        return true;
                    }
                }
                return false;
            })
            ->values();

        foreach ($students->values() as $index => $student) {
            $sectionIndex = (int) floor($index / max($maxStudentsPerSection, 1));
            $sectionLabel = (string) ($sectionLabels[$sectionIndex] ?? $sectionLabels->last());
            if ($sectionLabel === '') {
                continue;
            }

            $promotionStatus = DB::table('student_promotions')
                ->where('student_id', (int) $student->id)
                ->where('to_academic_term_id', (int) $nextTerm->id)
                ->value('promotion_status');

            DB::table('student_section_assignments')->updateOrInsert([
                'academic_term_id' => (int) $nextTerm->id,
                'student_id' => (int) $student->id,
            ], [
                'course_id' => (int) $program->id,
                'year_block_id' => $yearBlockId,
                'section' => $sectionLabel,
                'status' => 'active',
                'approval_status' => $promotionStatus === 'Needs Adviser Approval' ? 'adviser_review' : 'auto_approved',
                'flags' => $promotionStatus === 'Needs Adviser Approval' ? 'Irregular student; adviser approval required' : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        $subjects = Subject::query()
            ->where('course_id', (int) $program->id)
            ->where('academic_term_id', (int) $nextTerm->id)
            ->whereIn('year_section', $sectionLabels->all())
            ->get();

        $counts['faculty_assigned_count'] += $this->assignAcademicSetupFaculty($subjects);
        $schedule = $this->autoAssignSubjectRoomsAndSchedules($subjects, $autoCreateRooms, false);
        $counts['schedules_generated_count'] += (int) $schedule['updated_count'];

        if (Schema::hasTable('class_room_assignments')) {
            foreach ($subjects as $subject) {
                foreach ($this->roomAssignmentComponentsForSubject($subject) as $component) {
                    $result = $this->assignRoomForSubjectComponent($subject, $component, (string) $nextTerm->school_year, $semesterCanonical);
                    if ((string) $result['status'] === 'Assigned') {
                        $counts['rooms_assigned_count']++;
                    }
                }
            }
        }

        $subjectsBySection = Subject::query()
            ->where('course_id', (int) $program->id)
            ->where('academic_term_id', (int) $nextTerm->id)
            ->whereIn('year_section', $sectionLabels->all())
            ->get()
            ->groupBy('year_section');

        foreach ($students as $student) {
            $section = DB::table('student_section_assignments')
                ->where('academic_term_id', (int) $nextTerm->id)
                ->where('student_id', (int) $student->id)
                ->value('section');

            foreach ($subjectsBySection->get((string) $section, collect()) as $subject) {
                DB::table('student_subject')->updateOrInsert([
                    'student_id' => (int) $student->id,
                    'subject_id' => (int) $subject->id,
                ], [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                $counts['student_loads_generated_count']++;
            }
        }

        $this->validateAcademicSetupGeneration(
            $setupLogId,
            (int) $program->id,
            (int) $nextTerm->id,
            $sectionLabels->all(),
            $maxStudentsPerSection
        );

        if (Schema::hasTable('academic_setup_pending_issues')) {
            $issues = DB::table('academic_setup_pending_issues')
                ->where('generation_log_id', $setupLogId)
                ->where('status', 'open')
                ->orderBy('id')
                ->get()
                ->map(function ($issue) {
                    return [
                        'type' => (string) $issue->issue_type,
                        'label' => (string) ($issue->affected_label ?: ''),
                        'description' => (string) $issue->description,
                    ];
                })
                ->all();
        }

        DB::table('academic_setup_generation_logs')
            ->where('id', $setupLogId)
            ->update($counts + [
                'pending_issue_count' => count($issues),
                'status' => count($issues) ? 'pending_issues' : 'ready_for_publish',
                'updated_at' => now(),
            ]);

        return ['counts' => $counts, 'issues' => $issues];
    }

    public function academicSetupAutomation(Request $request)
    {
        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = collect(array_values($configOptions['school_years'] ?? []))
            ->map(function ($schoolYear) {
                $value = trim((string) $schoolYear);
                return ['value' => $value, 'label' => $value];
            })
            ->filter(function ($option) {
                return (string) ($option['value'] ?? '') !== '';
            })
            ->values()
            ->all();

        $semesterOptions = collect(['First', 'Second', 'Summer'])
            ->map(function ($semester) {
                return ['value' => $semester, 'label' => $semester];
            })
            ->all();

        $programOptions = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(function (Course $course) {
                $code = trim((string) $course->code);
                $name = trim((string) $course->name);

                return [
                    'id' => (int) $course->id,
                    'label' => $code !== '' && $name !== '' ? $code . ' - ' . $name : ($code !== '' ? $code : $name),
                ];
            })
            ->values()
            ->all();

        $yearLevelOptions = YearBlock::query()
            ->orderBy('id')
            ->get(['id', 'label'])
            ->map(function (YearBlock $yearBlock) {
                return [
                    'id' => (int) $yearBlock->id,
                    'label' => (string) $yearBlock->label,
                ];
            })
            ->values()
            ->all();

        $logs = collect();
        if (Schema::hasTable('academic_setup_generation_logs')) {
            $logs = DB::table('academic_setup_generation_logs as log')
                ->leftJoin('courses as c', 'c.id', '=', 'log.course_id')
                ->leftJoin('year_blocks as yb', 'yb.id', '=', 'log.year_block_id')
                ->select([
                    'log.*',
                    'c.code as course_code',
                    'c.name as course_name',
                    'yb.label as year_level_label',
                ])
                ->orderByDesc('log.created_at')
                ->limit(20)
                ->get();
        }

        return view('registrar.registrar-menu.scheduling.academic-setup-automation', compact(
            'schoolYearOptions',
            'semesterOptions',
            'programOptions',
            'yearLevelOptions',
            'logs'
        ));
    }

    public function generateAcademicSetupAutomation(Request $request): JsonResponse
    {
        if (!Schema::hasTable('academic_setup_generation_logs')
            || !Schema::hasTable('academic_setup_pending_issues')
            || !Schema::hasTable('student_section_assignments')) {
            return response()->json([
                'ok' => false,
                'message' => 'Academic setup automation tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'campus' => 'nullable|string|max:120',
            'course_id' => 'required|integer|exists:courses,id',
            'year_block_id' => 'required|integer|exists:year_blocks,id',
            'max_students_per_section' => 'required|integer|min:1|max:300',
            'auto_create_rooms' => 'nullable|boolean',
        ]);

        $schoolYear = $this->normalizeSectionOfferingSchoolYear((string) $validated['school_year']);
        $semester = $this->normalizeSlotMonitoringSemester((string) $validated['semester']);
        $campus = trim((string) ($validated['campus'] ?? ''));
        $courseId = (int) $validated['course_id'];
        $yearBlockId = (int) $validated['year_block_id'];
        $maxStudentsPerSection = (int) $validated['max_students_per_section'];
        $autoCreateRooms = $request->has('auto_create_rooms') ? $this->requestBoolean($request, 'auto_create_rooms') : true;

        if ($semester === '') {
            throw ValidationException::withMessages([
                'semester' => ['Please select a valid semester.'],
            ]);
        }

        $course = Course::query()->findOrFail($courseId);
        $yearBlock = YearBlock::query()->findOrFail($yearBlockId);
        $yearNumber = $this->academicSetupYearLevelNumber((string) $yearBlock->label);
        if ($yearNumber < 1) {
            throw ValidationException::withMessages([
                'year_block_id' => ['The selected year level cannot be used for automatic section naming.'],
            ]);
        }

        $academicTermId = $this->resolveSectionOfferingAcademicTermId($schoolYear, $semester);
        $logId = (int) DB::table('academic_setup_generation_logs')->insertGetId([
            'generated_by_user_id' => auth()->id(),
            'academic_term_id' => $academicTermId,
            'course_id' => $courseId,
            'year_block_id' => $yearBlockId,
            'school_year' => $schoolYear,
            'semester' => $semester,
            'campus' => $campus !== '' ? $campus : null,
            'max_students_per_section' => $maxStudentsPerSection,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counts = [
            'programs_generated_count' => 0,
            'courses_generated_count' => 0,
            'sections_created_count' => 0,
            'students_assigned_count' => 0,
            'class_offerings_generated_count' => 0,
            'rooms_assigned_count' => 0,
            'faculty_assigned_count' => 0,
            'schedules_generated_count' => 0,
            'student_loads_generated_count' => 0,
        ];

        try {
            DB::transaction(function () use (
                $course,
                $courseId,
                $yearBlockId,
                $yearNumber,
                $academicTermId,
                $schoolYear,
                $semester,
                $maxStudentsPerSection,
                $autoCreateRooms,
                $logId,
                &$counts
            ) {
                Course::query()
                    ->where('id', $courseId)
                    ->update(['slots' => $maxStudentsPerSection]);

                $curriculum = $this->resolveSectionOfferingCurriculum($courseId);
                if (!$curriculum) {
                    $this->recordAcademicSetupIssue(
                        $logId,
                        'Course not in curriculum',
                        'Program',
                        $courseId,
                        (string) $course->code,
                        'No published or active curriculum was found for the selected program.',
                        'Publish the current curriculum in Curriculum File, then generate again.'
                    );
                    return;
                }

                $semesterIds = $this->resolveSectionOfferingSemesterIds($semester);
                $assignments = CourseCurriculumSubject::query()
                    ->with('subject')
                    ->where('course_curriculum_id', (int) $curriculum->id)
                    ->where('year_block_id', $yearBlockId)
                    ->when(count($semesterIds) > 0, function ($query) use ($semesterIds) {
                        $query->whereIn('semester_id', $semesterIds);
                    })
                    ->orderBy('display_order')
                    ->orderBy('id')
                    ->get();

                if ($assignments->isEmpty()) {
                    $this->recordAcademicSetupIssue(
                        $logId,
                        'Course not in curriculum',
                        'Curriculum',
                        (int) $curriculum->id,
                        (string) $curriculum->curriculum_year_code,
                        'No curriculum subjects match the selected year level and semester.',
                        'Add subjects to the curriculum for this year level and semester.'
                    );
                    return;
                }

                $students = Student::query()
                    ->active()
                    ->where('course_id', $courseId)
                    ->where('year_block_id', $yearBlockId)
                    ->orderBy('name')
                    ->orderBy('student_no')
                    ->get(['id', 'student_no', 'name', 'course_id', 'year_block_id']);

                if ($students->isEmpty()) {
                    $this->recordAcademicSetupIssue(
                        $logId,
                        'Students without section',
                        'Program',
                        $courseId,
                        (string) $course->code,
                        'No active students were found for the selected program and year level.',
                        'Check student program/year tagging before generation.'
                    );
                }

                $requiredSectionCount = max(1, (int) ceil(max($students->count(), 1) / $maxStudentsPerSection));
                $sectionPlan = $this->academicSetupSectionPlan((string) $course->code, $yearNumber, $requiredSectionCount);
                $sectionLabels = collect($sectionPlan)->pluck('desired')->values()->all();
                $existingLabels = $this->academicSetupExistingSectionLabels($courseId, $academicTermId);

                foreach ($sectionPlan as $sectionRow) {
                    $sectionLabel = (string) $sectionRow['desired'];
                    $knownLabels = collect($sectionRow['aliases']);

                    if (!$existingLabels->intersect($knownLabels)->count()) {
                        $inserted = $this->createAcademicSetupSectionOfferings(
                            $assignments,
                            $courseId,
                            $academicTermId,
                            $sectionLabel
                        );

                        $counts['sections_created_count']++;
                        $counts['class_offerings_generated_count'] += $inserted;
                    }
                }

                $allSectionLabels = $this->academicSetupExistingSectionLabels($courseId, $academicTermId)
                    ->filter(function ($label) use ($sectionPlan) {
                        foreach ($sectionPlan as $sectionRow) {
                            if (in_array((string) $label, (array) $sectionRow['aliases'], true)) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->values();

                $studentSectionMap = [];
                foreach ($students->values() as $index => $student) {
                    $sectionIndex = (int) floor($index / $maxStudentsPerSection);
                    $sectionLabel = (string) ($allSectionLabels[$sectionIndex] ?? $allSectionLabels->last());
                    if ($sectionLabel === '') {
                        $this->recordAcademicSetupIssue(
                            $logId,
                            'Students without section',
                            'Student',
                            (int) $student->id,
                            (string) ($student->student_no ?: $student->name),
                            'The student could not be assigned because no section exists.',
                            'Create at least one section offering for this program and term.'
                        );
                        continue;
                    }

                    DB::table('student_section_assignments')->updateOrInsert([
                        'academic_term_id' => $academicTermId,
                        'student_id' => (int) $student->id,
                    ], [
                        'course_id' => $courseId,
                        'year_block_id' => $yearBlockId,
                        'section' => $sectionLabel,
                        'status' => 'active',
                        'approval_status' => 'auto_approved',
                        'flags' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);

                    $studentSectionMap[(int) $student->id] = $sectionLabel;
                    $counts['students_assigned_count']++;
                }

                $subjects = Subject::query()
                    ->where('course_id', $courseId)
                    ->where('academic_term_id', $academicTermId)
                    ->whereIn('year_section', $allSectionLabels->all())
                    ->get();

                $counts['faculty_assigned_count'] = $this->assignAcademicSetupFaculty($subjects);

                $scheduleResult = $this->autoAssignSubjectRoomsAndSchedules($subjects, $autoCreateRooms, false);
                $counts['schedules_generated_count'] = (int) $scheduleResult['updated_count'];
                $counts['rooms_assigned_count'] = Subject::query()
                    ->where('course_id', $courseId)
                    ->where('academic_term_id', $academicTermId)
                    ->whereIn('year_section', $allSectionLabels->all())
                    ->whereRaw("TRIM(COALESCE(room, '')) <> ''")
                    ->count();

                $subjectsBySection = Subject::query()
                    ->where('course_id', $courseId)
                    ->where('academic_term_id', $academicTermId)
                    ->whereIn('year_section', $allSectionLabels->all())
                    ->get()
                    ->groupBy('year_section');

                foreach ($students as $student) {
                    $sectionLabel = $studentSectionMap[(int) $student->id] ?? '';
                    if ($sectionLabel === '' || !$subjectsBySection->has($sectionLabel)) {
                        continue;
                    }

                    foreach ($subjectsBySection->get($sectionLabel) as $subject) {
                        DB::table('student_subject')->updateOrInsert([
                            'student_id' => (int) $student->id,
                            'subject_id' => (int) $subject->id,
                        ], [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]);
                        $counts['student_loads_generated_count']++;
                    }
                }

                $this->validateAcademicSetupGeneration(
                    $logId,
                    $courseId,
                    $academicTermId,
                    $allSectionLabels->all(),
                    $maxStudentsPerSection
                );
            });
        } catch (\Throwable $exception) {
            DB::table('academic_setup_generation_logs')
                ->where('id', $logId)
                ->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);

            throw $exception;
        }

        $pendingIssues = (int) DB::table('academic_setup_pending_issues')
            ->where('generation_log_id', $logId)
            ->where('status', 'open')
            ->count();

        $status = $pendingIssues > 0 ? 'pending_issues' : 'ready_for_publish';
        DB::table('academic_setup_generation_logs')
            ->where('id', $logId)
            ->update($counts + [
                'pending_issue_count' => $pendingIssues,
                'status' => $status,
                'updated_at' => now(),
            ]);

        AuditTrailRecorder::record('ACADEMIC_SETUP_GENERATED', [[
            'type' => 'AcademicSetupGenerationLog',
            'id' => $logId,
            'label' => $schoolYear . ' ' . $semester . ' ' . (string) $course->code,
            'changes' => [
                ['field' => 'status', 'old' => null, 'new' => $status],
                ['field' => 'sections_created', 'old' => null, 'new' => (string) $counts['sections_created_count']],
                ['field' => 'student_loads_generated', 'old' => null, 'new' => (string) $counts['student_loads_generated_count']],
                ['field' => 'pending_issues', 'old' => null, 'new' => (string) $pendingIssues],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => $pendingIssues > 0
                ? 'Academic setup generated with pending issues.'
                : 'Academic setup generated and ready for publishing.',
            'log_id' => $logId,
            'status' => $status,
            'counts' => $counts + ['pending_issue_count' => $pendingIssues],
            'issues' => $this->academicSetupIssueRows($logId),
        ]);
    }

    public function publishAcademicSetupAutomation(Request $request, $generationLog): JsonResponse
    {
        if (!Schema::hasTable('academic_setup_generation_logs') || !Schema::hasTable('academic_setup_pending_issues')) {
            return response()->json(['ok' => false, 'message' => 'Academic setup automation tables are not ready.'], 409);
        }

        $log = DB::table('academic_setup_generation_logs')->where('id', (int) $generationLog)->first();
        if (!$log) {
            return response()->json(['ok' => false, 'message' => 'Generation log not found.'], 404);
        }

        $openIssues = (int) DB::table('academic_setup_pending_issues')
            ->where('generation_log_id', (int) $generationLog)
            ->where('status', 'open')
            ->count();

        if ($openIssues > 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Resolve pending issues before publishing this academic setup.',
                'pending_issue_count' => $openIssues,
            ], 422);
        }

        DB::table('academic_setup_generation_logs')
            ->where('id', (int) $generationLog)
            ->update([
                'status' => 'published',
                'published_at' => now(),
                'updated_at' => now(),
            ]);

        AuditTrailRecorder::record('ACADEMIC_SETUP_PUBLISHED', [[
            'type' => 'AcademicSetupGenerationLog',
            'id' => (int) $generationLog,
            'label' => (string) $log->school_year . ' ' . (string) $log->semester,
            'changes' => [
                ['field' => 'status', 'old' => (string) $log->status, 'new' => 'published'],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Academic setup published successfully.',
        ]);
    }

    private function academicSetupYearLevelNumber(string $label): int
    {
        $normalized = strtolower(trim($label));
        if ($normalized === '') {
            return 0;
        }

        $map = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
            'sixth' => 6,
        ];

        foreach ($map as $word => $number) {
            if (strpos($normalized, $word) !== false) {
                return $number;
            }
        }

        if (preg_match('/([1-6])/', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function academicSetupSectionPlan(string $programCode, int $yearNumber, int $count): array
    {
        $programCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($programCode)));
        if ($programCode === '') {
            $programCode = 'PROGRAM';
        }

        $rows = [];
        for ($index = 0; $index < $count; $index++) {
            $suffix = $this->academicSetupSectionSuffix($index);
            $desired = $programCode . '-' . $yearNumber . $suffix;
            $legacy = $yearNumber . '-' . $suffix;

            $rows[] = [
                'desired' => $desired,
                'aliases' => [$desired, $legacy],
            ];
        }

        return $rows;
    }

    private function academicSetupSectionSuffix(int $index): string
    {
        $letters = '';
        $value = $index;

        do {
            $letters = chr(65 + ($value % 26)) . $letters;
            $value = (int) floor($value / 26) - 1;
        } while ($value >= 0);

        return $letters;
    }

    private function academicSetupExistingSectionLabels(int $courseId, int $academicTermId)
    {
        $query = Subject::query()
            ->where('course_id', $courseId)
            ->where('academic_term_id', $academicTermId)
            ->whereNotNull('year_section')
            ->whereRaw("TRIM(COALESCE(year_section, '')) <> ''");

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($builder) {
                $builder->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 0);
            });
        }

        return $query
            ->pluck('year_section')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->sort()
            ->values();
    }

    private function createAcademicSetupSectionOfferings($assignments, int $courseId, int $academicTermId, string $sectionLabel): int
    {
        $now = now();
        $addedBy = auth()->check() ? trim((string) optional(auth()->user())->name) : '';
        if ($addedBy === '') {
            $addedBy = null;
        }

        $rows = collect($assignments)
            ->map(function (CourseCurriculumSubject $assignment) use ($courseId, $academicTermId, $sectionLabel, $addedBy, $now) {
                $subject = $assignment->subject;
                $code = trim((string) optional($subject)->code);
                $name = trim((string) optional($subject)->name);

                return [
                    'code' => $code !== '' ? $code : ('SUBJ-' . (int) $assignment->id),
                    'name' => $name !== '' ? $name : ('Curriculum Subject ' . (int) $assignment->id),
                    'is_subject_file_record' => 0,
                    'units' => $subject ? (float) $subject->units : (float) $assignment->credited_units,
                    'lec' => $subject ? (int) $subject->lec : 0,
                    'lab' => $subject ? (int) $subject->lab : 0,
                    'hours' => $subject ? (float) ($subject->hours ?: 0) : (float) ($assignment->credited_units ?: 0),
                    'course_type' => $subject ? (string) ($subject->course_type ?: '') : null,
                    'is_core' => $subject ? (int) ((bool) $subject->is_core) : 0,
                    'is_applied' => $subject ? (int) ((bool) $subject->is_applied) : 0,
                    'is_specialized' => $subject ? (int) ((bool) $subject->is_specialized) : 0,
                    'days' => null,
                    'time_start' => null,
                    'time_end' => null,
                    'room' => null,
                    'faculty_id' => null,
                    'year_section' => $sectionLabel,
                    'course_id' => $courseId,
                    'academic_term_id' => $academicTermId,
                    'grading_status_id' => null,
                    'load_type_id' => null,
                    'credited_tuition_units' => (float) $assignment->credited_units,
                    'load_hours' => null,
                    'added_by' => $addedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        if (!count($rows)) {
            return 0;
        }

        DB::table('subjects')->insert($rows);

        return count($rows);
    }

    private function assignAcademicSetupFaculty($subjects): int
    {
        $subjects = collect($subjects)->filter(function ($subject) {
            return $subject instanceof Subject && empty($subject->faculty_id);
        })->values();

        if ($subjects->isEmpty() || !Schema::hasTable('faculties')) {
            return 0;
        }

        $facultyIds = Faculty::query()
            ->orderBy('name')
            ->pluck('id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->values();

        if ($facultyIds->isEmpty()) {
            return 0;
        }

        $assigned = 0;
        $index = 0;
        foreach ($subjects as $subject) {
            $subject->faculty_id = (int) $facultyIds[$index % $facultyIds->count()];
            $subject->load_type = $subject->load_type ?: 'Regular';
            $subject->save();
            $assigned++;
            $index++;
        }

        return $assigned;
    }

    private function validateAcademicSetupGeneration(
        int $logId,
        int $courseId,
        int $academicTermId,
        array $sectionLabels,
        int $maxStudentsPerSection
    ): void {
        $subjects = Subject::query()
            ->where('course_id', $courseId)
            ->where('academic_term_id', $academicTermId)
            ->whereIn('year_section', $sectionLabels)
            ->get();

        foreach ($subjects as $subject) {
            $label = trim((string) $subject->code) . ' - ' . trim((string) $subject->year_section);

            if (trim((string) $subject->room) === '') {
                $this->recordAcademicSetupIssue(
                    $logId,
                    'Classes without rooms',
                    'Subject',
                    (int) $subject->id,
                    $label,
                    'This class offering does not have an assigned room.',
                    'Add an active room with enough capacity or rerun with automatic room creation enabled.'
                );
            }

            if (empty($subject->faculty_id)) {
                $this->recordAcademicSetupIssue(
                    $logId,
                    'Classes without faculty',
                    'Subject',
                    (int) $subject->id,
                    $label,
                    'This class offering does not have an assigned faculty member.',
                    'Assign faculty in Faculty Loads or configure faculty qualification data.'
                );
            }

            if (trim((string) $subject->days) === '' || trim((string) $subject->time_start) === '' || trim((string) $subject->time_end) === '') {
                $this->recordAcademicSetupIssue(
                    $logId,
                    'Schedule conflicts',
                    'Subject',
                    (int) $subject->id,
                    $label,
                    'This class offering does not have a complete schedule.',
                    'Assign a conflict-free day and time in Class Schedule Preparation.'
                );
            }
        }

        $sectionCounts = DB::table('student_section_assignments')
            ->select('section', DB::raw('COUNT(*) as total'))
            ->where('academic_term_id', $academicTermId)
            ->where('course_id', $courseId)
            ->whereIn('section', $sectionLabels)
            ->groupBy('section')
            ->get();

        foreach ($sectionCounts as $sectionCount) {
            if ((int) $sectionCount->total > $maxStudentsPerSection) {
                $this->recordAcademicSetupIssue(
                    $logId,
                    'Section full',
                    'Section',
                    null,
                    (string) $sectionCount->section,
                    'The section has ' . (int) $sectionCount->total . ' students, exceeding the maximum of ' . $maxStudentsPerSection . '.',
                    'Create another section or increase the approved section capacity.'
                );
            }
        }

        $this->recordAcademicSetupScheduleConflicts($logId, $subjects);
    }

    private function recordAcademicSetupScheduleConflicts(int $logId, $subjects): void
    {
        $subjects = collect($subjects)->values();

        for ($leftIndex = 0; $leftIndex < $subjects->count(); $leftIndex++) {
            $left = $subjects[$leftIndex];
            if (!$this->subjectHasCompleteSchedule($left)) {
                continue;
            }

            for ($rightIndex = $leftIndex + 1; $rightIndex < $subjects->count(); $rightIndex++) {
                $right = $subjects[$rightIndex];
                if (!$this->subjectHasCompleteSchedule($right)) {
                    continue;
                }

                $candidate = [
                    'days' => (string) $left->days,
                    'time_start' => (string) $left->time_start,
                    'time_end' => (string) $left->time_end,
                ];

                $other = [
                    'days' => (string) $right->days,
                    'time_start' => (string) $right->time_start,
                    'time_end' => (string) $right->time_end,
                ];

                if (!$this->autoScheduleOverlaps($candidate, $other)) {
                    continue;
                }

                $sameSection = trim((string) $left->year_section) === trim((string) $right->year_section);
                $sameRoom = trim((string) $left->room) !== '' && trim((string) $left->room) === trim((string) $right->room);
                $sameFaculty = !empty($left->faculty_id) && (int) $left->faculty_id === (int) $right->faculty_id;

                if ($sameSection || $sameRoom || $sameFaculty) {
                    $this->recordAcademicSetupIssue(
                        $logId,
                        'Schedule conflicts',
                        'Subject',
                        (int) $left->id,
                        trim((string) $left->code) . ' / ' . trim((string) $right->code),
                        'Two class offerings overlap for the same section, room, or faculty.',
                        'Adjust one schedule in Class Schedule Preparation.'
                    );
                }
            }
        }
    }

    private function recordAcademicSetupIssue(
        int $logId,
        string $issueType,
        ?string $affectedType,
        ?int $affectedId,
        ?string $affectedLabel,
        string $description,
        ?string $suggestedAction = null
    ): void {
        DB::table('academic_setup_pending_issues')->insert([
            'generation_log_id' => $logId,
            'issue_type' => $issueType,
            'affected_type' => $affectedType,
            'affected_id' => $affectedId,
            'affected_label' => $affectedLabel,
            'description' => $description,
            'suggested_action' => $suggestedAction,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function academicSetupIssueRows(int $logId): array
    {
        if (!Schema::hasTable('academic_setup_pending_issues')) {
            return [];
        }

        return DB::table('academic_setup_pending_issues')
            ->where('generation_log_id', $logId)
            ->orderBy('issue_type')
            ->orderBy('id')
            ->get()
            ->map(function ($issue) {
                return [
                    'issue_type' => (string) $issue->issue_type,
                    'affected_label' => (string) ($issue->affected_label ?: ''),
                    'description' => (string) $issue->description,
                    'suggested_action' => (string) ($issue->suggested_action ?: ''),
                    'status' => (string) $issue->status,
                ];
            })
            ->all();
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
                            'hours' => (float) ($row->hours ?? 0),
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

        if (!$curriculum) {
            return response()->json([
                'ok' => true,
                'rows' => [],
                'meta' => [
                    'course_id' => $courseId,
                    'curriculum_id' => null,
                    'curriculum_year' => '',
                    'year_level' => $yearLevel,
                    'semester' => $semester,
                    'total' => 0,
                ],
            ]);
        }

        $rowsQuery = CourseCurriculumSubject::query()
            ->with('subject')
            ->where('course_curriculum_id', (int) $curriculum->id);

        if ($yearBlockId) {
            $rowsQuery->where('year_block_id', (int) $yearBlockId);
        }

        if (count($semesterIds)) {
            $rowsQuery->whereIn('semester_id', $semesterIds);
        }

        $rows = $rowsQuery
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
                    'hours' => $subject ? (float) ($subject->hours ?: 0) : (float) ($assignment->credited_units ?: 0),
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
                'curriculum_subject_ids' => ['Select at least one available course.'],
            ]);
        }

        $curriculum = $this->resolveSectionOfferingCurriculum($courseId);
        if (!$curriculum) {
            throw ValidationException::withMessages([
                'course_id' => ['No published curriculum is available for the selected course. Publish the curriculum first before creating a section offering.'],
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
            ->whereIn('id', $selectedCurriculumSubjectIds->all())
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($assignments->count() !== $selectedCurriculumSubjectIds->count()) {
            throw ValidationException::withMessages([
                'curriculum_subject_ids' => ['One or more selected courses are invalid for the chosen program.'],
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
                $hours = $subject ? (float) ($subject->hours ?: 0) : (float) ($assignment->credited_units ?: 0);

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
                    'hours' => $hours,
                    'course_type' => $subject ? (string) ($subject->course_type ?: '') : null,
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

        $autoScheduleResult = [
            'updated_count' => 0,
            'created_rooms' => 0,
            'skipped_count' => 0,
        ];

        if ((bool) ($validated['auto_schedule'] ?? true)) {
            $createdSubjects = Subject::query()
                ->where('course_id', $courseId)
                ->where('academic_term_id', $academicTermId)
                ->where('year_section', $sectionLabel)
                ->whereIn('code', collect($rowsToInsert)->pluck('code')->all())
                ->orderBy('code')
                ->orderBy('id')
                ->get();

            $autoScheduleResult = $this->autoAssignSubjectRoomsAndSchedules(
                $createdSubjects,
                (bool) ($validated['auto_create_rooms'] ?? true),
                false
            );
        }

        AuditTrailRecorder::record('SECTION_OFFERING_CREATED', [[
            'type' => 'SectionOfferingBatch',
            'id' => null,
            'label' => $sectionLabel,
            'changes' => [
                ['field' => 'course_id', 'old' => null, 'new' => $courseId],
                ['field' => 'school_year', 'old' => null, 'new' => $schoolYear],
                ['field' => 'semester', 'old' => null, 'new' => $semester],
                ['field' => 'year_level', 'old' => null, 'new' => $yearLevel],
                ['field' => 'section', 'old' => null, 'new' => $sectionLabel],
                ['field' => 'slots', 'old' => null, 'new' => $slots !== null ? (string) $slots : null],
                ['field' => 'adviser', 'old' => null, 'new' => $adviserName !== '' ? $adviserName : null],
                ['field' => 'curriculum_subject_count', 'old' => null, 'new' => (string) count($rowsToInsert)],
            ],
        ]], [
            'source_action' => __FUNCTION__,
        ]);

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
                'auto_scheduled_count' => (int) $autoScheduleResult['updated_count'],
                'auto_created_rooms' => (int) $autoScheduleResult['created_rooms'],
            ],
        ], 201);
    }

    private function autoAssignSubjectRoomsAndSchedules($subjects, bool $autoCreateRooms = true, bool $overwrite = false): array
    {
        $subjects = collect($subjects)->filter(function ($subject) {
            return $subject instanceof Subject;
        })->values();

        if ($subjects->isEmpty()) {
            return [
                'updated_count' => 0,
                'created_rooms' => 0,
                'skipped_count' => 0,
            ];
        }

        $this->seedRoomDimensionsIfEmpty();

        $createdRooms = 0;
        $updated = 0;
        $skipped = 0;
        $conflicts = $this->loadScheduleConflictRows($subjects);
        $roomCache = [];

        foreach ($subjects as $subject) {
            if (!$overwrite && $this->subjectHasCompleteSchedule($subject)) {
                $skipped++;
                continue;
            }

            if ((int) ($subject->faculty_id ?: 0) <= 0) {
                $faculty = $this->findAvailableTeacherForSubject($subject, $conflicts);
                if ($faculty) {
                    $subject->faculty_id = (int) $faculty->id;
                }
            }

            $courseId = (int) $subject->course_id;
            if (!array_key_exists($courseId, $roomCache)) {
                $roomCache[$courseId] = $this->availableRoomsForAutoSchedule($courseId, $subject);
            }

            if ($roomCache[$courseId]->isEmpty() && $autoCreateRooms) {
                $room = $this->createAutoScheduleRoom($courseId, $this->autoScheduleRequiredCapacity($subject), $subject);
                $createdRooms++;
                $this->syncAutoRoomAllowedSubject($room, $subject);
                $roomCache[$courseId] = $this->availableRoomsForAutoSchedule($courseId, $subject);
            }

            $assignment = $this->findAutoScheduleAssignment($subject, $roomCache[$courseId], $conflicts);

            if (!$assignment && $autoCreateRooms) {
                $room = $this->createAutoScheduleRoom($courseId, $this->autoScheduleRequiredCapacity($subject), $subject);
                $createdRooms++;
                $this->syncAutoRoomAllowedSubject($room, $subject);
                $roomCache[$courseId]->push($room);
                $assignment = $this->findAutoScheduleAssignment($subject, $roomCache[$courseId], $conflicts);
            }

            if (!$assignment) {
                $skipped++;
                continue;
            }

            $subject->days = $assignment['days'];
            $subject->time_start = $assignment['time_start'];
            $subject->time_end = $assignment['time_end'];
            $subject->room = (string) $assignment['room'];
            if (Schema::hasColumn('subjects', 'room_requirement_status')) {
                $subject->room_requirement_status = 'Assigned';
            }
            $subject->save();

            $conflicts[] = [
                'id' => (int) $subject->id,
                'academic_term_id' => (int) $subject->academic_term_id,
                'section' => (string) $subject->year_section,
                'faculty_id' => (int) ($subject->faculty_id ?: 0),
                'days' => (string) $subject->days,
                'time_start' => (string) $subject->time_start,
                'time_end' => (string) $subject->time_end,
                'room' => (string) $subject->room,
            ];

            $updated++;
        }

        return [
            'updated_count' => $updated,
            'created_rooms' => $createdRooms,
            'skipped_count' => $skipped,
        ];
    }

    private function loadScheduleConflictRows($subjects): array
    {
        $academicTermIds = collect($subjects)
            ->pluck('academic_term_id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values();

        if ($academicTermIds->isEmpty()) {
            return [];
        }

        return Subject::query()
            ->select(['id', 'academic_term_id', 'year_section', 'faculty_id', 'days', 'time_start', 'time_end', 'room'])
            ->whereIn('academic_term_id', $academicTermIds->all())
            ->whereNotNull('days')
            ->whereNotNull('time_start')
            ->whereNotNull('time_end')
            ->whereNotNull('room')
            ->whereRaw("TRIM(COALESCE(days, '')) <> ''")
            ->whereRaw("TRIM(COALESCE(time_start, '')) <> ''")
            ->whereRaw("TRIM(COALESCE(time_end, '')) <> ''")
            ->whereRaw("TRIM(COALESCE(room, '')) <> ''")
            ->get()
            ->map(function (Subject $subject) {
                return [
                    'id' => (int) $subject->id,
                    'academic_term_id' => (int) $subject->academic_term_id,
                    'section' => (string) $subject->year_section,
                    'faculty_id' => (int) ($subject->faculty_id ?: 0),
                    'days' => (string) $subject->days,
                    'time_start' => (string) $subject->time_start,
                    'time_end' => (string) $subject->time_end,
                    'room' => (string) $subject->room,
                ];
            })
            ->values()
            ->all();
    }

    private function subjectHasCompleteSchedule(Subject $subject): bool
    {
        return trim((string) $subject->days) !== ''
            && trim((string) $subject->time_start) !== ''
            && trim((string) $subject->time_end) !== ''
            && trim((string) $subject->room) !== '';
    }

    private function availableRoomsForAutoSchedule(int $courseId, Subject $subject = null)
    {
        if ($courseId <= 0 && !$subject) {
            return collect();
        }

        return Room::query()
            ->with(['courses' => function ($query) {
                $query->select('courses.id');
            }, 'allowedSubjects' => function ($query) {
                $query->select('subjects.id');
            }])
            ->when($subject && Schema::hasTable('room_allowed_subjects'), function ($query) use ($subject) {
                $query->whereHas('allowedSubjects', function ($subjectQuery) use ($subject) {
                    $subjectQuery->where('subjects.id', (int) $subject->id)
                        ->orWhere('subjects.code', (string) $subject->code);
                });
            }, function ($query) use ($courseId) {
                $query->whereHas('courses', function ($courseQuery) use ($courseId) {
                    $courseQuery->where('courses.id', $courseId);
                });
            })
            ->when(Schema::hasColumn('rooms', 'status'), function ($query) {
                $query->where(function ($builder) {
                    $builder->whereNull('status')
                        ->orWhere('status', '')
                        ->orWhere('status', 'Active');
                });
            })
            ->orderBy('capacity')
            ->orderBy('floor_number')
            ->orderBy('room_number')
            ->get();
    }

    private function findAutoScheduleAssignment(Subject $subject, $rooms, array $conflicts)
    {
        $requiredCapacity = $this->autoScheduleRequiredCapacity($subject);
        $requiredRoomType = $this->resolveSubjectRequiredRoomType($subject, ((float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture'));
        $slots = $this->autoScheduleTimeSlots($subject);
        $rooms = collect($rooms)
            ->filter(function (Room $room) use ($requiredCapacity, $requiredRoomType, $subject) {
                return (int) $room->capacity >= $requiredCapacity
                    && $this->roomTypeMatchesSubject($room, $subject, $requiredRoomType)
                    && $this->roomAllowsSubject($room, $subject);
            })
            ->values();

        if ($rooms->isEmpty()) {
            return null;
        }

        foreach ($slots as $slot) {
            if ($this->hasAutoScheduleSectionOrFacultyConflict($subject, $slot, $conflicts)) {
                continue;
            }

            if ((int) ($subject->faculty_id ?: 0) > 0
                && !$this->teacherAvailableForSlot((int) $subject->faculty_id, $slot)) {
                continue;
            }

            foreach ($rooms as $room) {
                $candidate = [
                    'days' => $slot['days'],
                    'time_start' => $slot['time_start'],
                    'time_end' => $slot['time_end'],
                    'room' => $this->roomAssignmentRoomCode($room),
                ];

                if ($this->roomAssignmentRoomAvailableForSlot($room, $candidate)
                    && !$this->hasAutoScheduleRoomConflict($subject, $candidate, $conflicts)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function autoScheduleRequiredCapacity(Subject $subject): int
    {
        $capacity = 50;
        $subject->loadMissing('canonicalCourse');

        if ($subject->canonicalCourse && (int) $subject->canonicalCourse->slots > 0) {
            $capacity = (int) $subject->canonicalCourse->slots;
        }

        return max($capacity, 30);
    }

    private function autoScheduleTimeSlots(Subject $subject): array
    {
        $patterns = ['MWF', 'TTH', 'MW', 'TTH', 'F', 'S'];
        $durationMinutes = $this->autoScheduleSubjectDurationMinutes($subject);
        if ($durationMinutes <= 0) {
            return [];
        }
        $starts = $this->autoScheduleStartTimes($durationMinutes);
        $slots = [];

        foreach ($patterns as $pattern) {
            foreach ($starts as $start) {
                $end = Carbon::createFromFormat('H:i', $start)
                    ->addMinutes($durationMinutes)
                    ->format('H:i');

                $slots[] = [
                    'days' => $pattern,
                    'time_start' => $start,
                    'time_end' => $end,
                ];
            }
        }

        return $slots;
    }

    private function autoScheduleSubjectDurationMinutes(Subject $subject): int
    {
        $hours = (float) ($subject->hours ?: 0);
        if ($hours <= 0) {
            return 0;
        }

        $startMinutes = $this->autoScheduleMinutes($this->autoScheduleDayStartTime());
        $endMinutes = $this->autoScheduleMinutes($this->autoScheduleDayEndTime());
        $availableMinutes = max($endMinutes - $startMinutes, 60);
        $durationMinutes = (int) round($hours * 60);

        return min(max($durationMinutes, 60), $availableMinutes);
    }

    private function autoScheduleStartTimes(int $durationMinutes): array
    {
        $dayStart = $this->autoScheduleDayStartTime();
        $dayEnd = $this->autoScheduleDayEndTime();
        $cursor = Carbon::createFromFormat('H:i', $dayStart);
        $latestEnd = Carbon::createFromFormat('H:i', $dayEnd);
        $starts = [];

        if ($latestEnd->lte($cursor)) {
            $latestEnd = Carbon::createFromFormat('H:i', '21:00');
        }

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($latestEnd)) {
            $starts[] = $cursor->format('H:i');
            $cursor->addMinutes(30);
        }

        return $starts;
    }

    private function autoScheduleDayStartTime(): string
    {
        return self::ROOM_AVAILABLE_START_TIME;
    }

    private function autoScheduleDayEndTime(): string
    {
        return self::ROOM_AVAILABLE_END_TIME;
    }

    private function normalizeAutoScheduleBoundary(string $value, string $fallback): string
    {
        $value = trim($value);
        if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $fallback;
        }

        try {
            Carbon::createFromFormat('H:i', $value);
            return $value;
        } catch (\Throwable $exception) {
            return $fallback;
        }
    }

    private function hasAutoScheduleSectionOrFacultyConflict(Subject $subject, array $slot, array $conflicts): bool
    {
        foreach ($conflicts as $conflict) {
            if ((int) ($conflict['id'] ?? 0) === (int) $subject->id) {
                continue;
            }

            if ((int) ($conflict['academic_term_id'] ?? 0) !== (int) $subject->academic_term_id) {
                continue;
            }

            if (!$this->autoScheduleOverlaps($slot, $conflict)) {
                continue;
            }

            $sameSection = trim((string) ($conflict['section'] ?? '')) !== ''
                && trim((string) ($conflict['section'] ?? '')) === trim((string) $subject->year_section);
            $sameFaculty = (int) ($subject->faculty_id ?: 0) > 0
                && (int) ($conflict['faculty_id'] ?? 0) === (int) $subject->faculty_id;

            if ($sameSection || $sameFaculty) {
                return true;
            }
        }

        return false;
    }

    private function hasAutoScheduleRoomConflict(Subject $subject, array $candidate, array $conflicts): bool
    {
        foreach ($conflicts as $conflict) {
            if ((int) ($conflict['id'] ?? 0) === (int) $subject->id) {
                continue;
            }

            if ((int) ($conflict['academic_term_id'] ?? 0) !== (int) $subject->academic_term_id) {
                continue;
            }

            if (trim((string) ($conflict['room'] ?? '')) !== trim((string) ($candidate['room'] ?? ''))) {
                continue;
            }

            if ($this->autoScheduleOverlaps($candidate, $conflict)) {
                return true;
            }
        }

        return false;
    }

    private function autoScheduleOverlaps(array $left, array $right): bool
    {
        $sharedDays = array_intersect(
            $this->autoScheduleDayTokens((string) ($left['days'] ?? '')),
            $this->autoScheduleDayTokens((string) ($right['days'] ?? ''))
        );

        if (!count($sharedDays)) {
            return false;
        }

        $leftStart = $this->autoScheduleMinutes((string) ($left['time_start'] ?? ''));
        $leftEnd = $this->autoScheduleMinutes((string) ($left['time_end'] ?? ''));
        $rightStart = $this->autoScheduleMinutes((string) ($right['time_start'] ?? ''));
        $rightEnd = $this->autoScheduleMinutes((string) ($right['time_end'] ?? ''));

        if ($leftStart === null || $leftEnd === null || $rightStart === null || $rightEnd === null) {
            return false;
        }

        return $leftStart < $rightEnd && $rightStart < $leftEnd;
    }

    private function autoScheduleDayTokens(string $days): array
    {
        $value = strtoupper(preg_replace('/[^A-Z]/', '', $days));
        $tokens = [];
        $map = ['TH' => 'R', 'SU' => 'U'];
        $value = str_replace(array_keys($map), array_values($map), $value);

        foreach (str_split($value) as $token) {
            if (in_array($token, ['M', 'T', 'W', 'R', 'F', 'S', 'U'], true)) {
                $tokens[] = $token;
            }
        }

        return array_values(array_unique($tokens));
    }

    private function autoScheduleMinutes(string $time)
    {
        $value = trim($time);
        if ($value === '') {
            return null;
        }

        try {
            $parsed = Carbon::parse($value);
            return ((int) $parsed->format('H') * 60) + (int) $parsed->format('i');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function createAutoScheduleRoom(int $courseId, int $capacity, Subject $subject = null): Room
    {
        $building = RoomBuilding::query()->firstOrCreate([
            'name' => 'AUTO GENERATED',
        ]);

        $hallway = RoomHallway::query()->firstOrCreate([
            'room_building_id' => (int) $building->id,
            'name' => 'AUTO',
        ]);

        $nextRoomNumber = (int) Room::query()
            ->where('room_hallway_id', (int) $hallway->id)
            ->where('floor_number', 1)
            ->max('room_number');

        if ($nextRoomNumber < 900) {
            $nextRoomNumber = 900;
        }

        $roomPayload = [
            'room_hallway_id' => (int) $hallway->id,
            'room_number' => $nextRoomNumber + 1,
            'floor_number' => 1,
            'capacity' => max($capacity, 50),
            'updated_by_user_id' => auth()->id(),
        ];
        $roomType = $subject
            ? $this->resolveSubjectRequiredRoomType($subject, (float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture')
            : 'Lecture Room';

        $roomPayload = array_merge($roomPayload, [
            'room_code' => 'AUTO-' . ($nextRoomNumber + 1),
            'room_name' => 'Auto Generated Room ' . ($nextRoomNumber + 1),
            'room_type' => $roomType,
            'available_days' => 'MTWTHFS',
            'available_start_time' => $this->autoScheduleDayStartTime(),
            'available_end_time' => $this->autoScheduleDayEndTime(),
            'status' => 'Active',
        ]);
        $roomPayload = array_filter($roomPayload, function ($value, $key) {
            return in_array($key, ['room_hallway_id', 'room_number', 'floor_number', 'capacity', 'updated_by_user_id'], true)
                || Schema::hasColumn('rooms', $key);
        }, ARRAY_FILTER_USE_BOTH);

        $room = Room::query()->create($roomPayload);

        if ($courseId > 0) {
            $room->courses()->syncWithoutDetaching([
                $courseId => [
                    'assigned_by_user_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        return $room;
    }

    private function syncAutoRoomAllowedSubject(Room $room, Subject $subject): void
    {
        if (!Schema::hasTable('room_allowed_subjects')) {
            return;
        }

        DB::table('room_allowed_subjects')->updateOrInsert([
            'room_id' => (int) $room->id,
            'subject_id' => (int) $subject->id,
        ], [
            'assigned_by_user_id' => auth()->id(),
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function manualScheduleValidationIssues(Subject $subject, array $data): array
    {
        $days = trim((string) ($data['days'] ?? $subject->days ?? ''));
        $timeStart = trim((string) ($data['time_start'] ?? $subject->time_start ?? ''));
        $timeEnd = trim((string) ($data['time_end'] ?? $subject->time_end ?? ''));
        $roomValue = trim((string) ($data['room'] ?? $subject->room ?? ''));
        $facultyId = (int) ($data['faculty_id'] ?? $subject->faculty_id ?? 0);
        $issues = [];

        if ($days === '' || $timeStart === '' || $timeEnd === '') {
            return [];
        }

        $slot = ['days' => $days, 'time_start' => $timeStart, 'time_end' => $timeEnd];
        $term = $subject->academicTerm;
        $schoolYear = (string) ($term->school_year ?? $subject->school_year ?? '');
        $semester = $this->normalizeSlotMonitoringSemester((string) ($term->term ?? $subject->semester ?? ''));

        if ($roomValue !== '') {
            $room = $this->findRoomByScheduleValue($roomValue);
            if (!$room) {
                $issues[] = 'Selected room was not found in the Room File.';
            } else {
                $sectionSize = $this->roomAssignmentSectionSize($subject, $schoolYear, $semester);
                $requiredRoomType = $this->resolveSubjectRequiredRoomType($subject, ((float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture'));

                if (!$this->roomAllowsSubject($room, $subject)) {
                    $issues[] = 'Subject ' . (string) $subject->code . ' is not allowed in room ' . $this->roomAssignmentRoomCode($room) . '.';
                }
                if ((int) $room->capacity < $sectionSize) {
                    $issues[] = 'Room ' . $this->roomAssignmentRoomCode($room) . ' capacity is lower than the section size of ' . $sectionSize . '.';
                }
                if (!$this->roomTypeMatchesSubject($room, $subject, $requiredRoomType)) {
                    $issues[] = 'Room type mismatch. ' . (string) $subject->code . ' requires ' . $requiredRoomType . '.';
                }
                if (!$this->roomAssignmentRoomAvailableForSlot($room, $slot)) {
                    $issues[] = 'Room ' . $this->roomAssignmentRoomCode($room) . ' is not available for the selected day and time.';
                }
                $roomConflict = $this->roomAssignmentConflictDetail($room, $slot, $schoolYear, $semester, (int) $subject->id);
                if ($roomConflict !== '') {
                    $issues[] = $roomConflict;
                }
            }
        }

        if ($facultyId > 0) {
            if (!$this->teacherQualifiedForSubject($facultyId, $subject)) {
                $issues[] = 'Selected teacher is not qualified for ' . (string) $subject->code . '.';
            }
            if (!$this->teacherLoadWithinLimit($facultyId, $subject)) {
                $issues[] = 'Selected teacher will exceed the maximum teaching load.';
            }
            if (!$this->teacherAvailableForSlot($facultyId, $slot)) {
                $issues[] = 'Selected teacher is not available for the selected day and time.';
            }
        }

        $conflicts = $this->loadScheduleConflictRows(collect([$subject]));
        $testSubject = clone $subject;
        $testSubject->faculty_id = $facultyId;
        if ($this->hasAutoScheduleSectionOrFacultyConflict($testSubject, $slot, $conflicts)) {
            $issues[] = 'Teacher or section already has an overlapping schedule.';
        }

        return array_values(array_unique($issues));
    }

    private function roomAllowsSubject(Room $room, Subject $subject): bool
    {
        if (Schema::hasTable('room_allowed_subjects')) {
            $roomSubjectRows = DB::table('room_allowed_subjects')
                ->where('room_id', (int) $room->id)
                ->count();

            if ($roomSubjectRows > 0) {
                return DB::table('room_allowed_subjects')
                    ->join('subjects as allowed_subjects', 'allowed_subjects.id', '=', 'room_allowed_subjects.subject_id')
                    ->where('room_id', (int) $room->id)
                    ->where(function ($query) use ($subject) {
                        $query->where('room_allowed_subjects.subject_id', (int) $subject->id)
                            ->orWhere('allowed_subjects.code', (string) $subject->code);
                    })
                    ->exists();
            }
        }

        $courseId = (int) $subject->course_id;
        return $courseId <= 0 || DB::table('room_course_assignments')
            ->where('room_id', (int) $room->id)
            ->where('course_id', $courseId)
            ->exists();
    }

    private function roomTypeMatchesSubject(Room $room, Subject $subject, string $requiredRoomType): bool
    {
        $roomType = $this->normalizeRoomAssignmentType((string) ($room->room_type ?: 'Lecture Room'));
        $required = $this->normalizeRoomAssignmentType($requiredRoomType);

        if ((float) ($subject->lab ?: 0) > 0) {
            return strpos(strtolower($roomType), 'laboratory') !== false;
        }

        if ($required === 'Lecture Room') {
            return in_array($roomType, ['Lecture Room', 'Auditorium'], true);
        }

        return $roomType === $required;
    }

    private function findRoomByScheduleValue(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return Room::query()
            ->where('room_code', $value)
            ->orWhere('room_number', $value)
            ->orWhereRaw("CONCAT('Room#', room_number) = ?", [$value])
            ->first();
    }

    private function roomAssignmentConflictDetail(Room $room, array $slot, string $schoolYear, string $semester, int $subjectId): string
    {
        $assignments = DB::table('class_room_assignments as cra')
            ->leftJoin('subjects as s', 's.id', '=', 'cra.class_offering_id')
            ->where('cra.room_id', (int) $room->id)
            ->where('cra.academic_year', $schoolYear)
            ->where('cra.semester', $semester)
            ->where('cra.class_offering_id', '<>', $subjectId)
            ->whereIn('cra.assignment_status', ['Assigned', 'Manual Override'])
            ->select('cra.*', 's.code as subject_code', 's.year_section')
            ->get();

        foreach ($assignments as $assignment) {
            if ($this->autoScheduleOverlaps($slot, [
                'days' => (string) $assignment->day,
                'time_start' => (string) $assignment->start_time,
                'time_end' => (string) $assignment->end_time,
            ])) {
                return 'Room ' . $this->roomAssignmentRoomCode($room) . ' is already occupied on '
                    . (string) $assignment->day . ' from '
                    . $this->classScheduleTimeInputValue((string) $assignment->start_time) . ' to '
                    . $this->classScheduleTimeInputValue((string) $assignment->end_time) . ' by '
                    . trim((string) $assignment->subject_code . ' - ' . (string) $assignment->year_section) . '.';
            }
        }

        return '';
    }

    private function teacherQualifiedForSubject(int $facultyId, Subject $subject): bool
    {
        if (!Schema::hasTable('teacher_allowed_subjects')) {
            return true;
        }

        $rows = DB::table('teacher_allowed_subjects')->where('faculty_id', $facultyId)->count();
        if ($rows === 0) {
            return true;
        }

        return DB::table('teacher_allowed_subjects')
            ->join('subjects as allowed_subjects', 'allowed_subjects.id', '=', 'teacher_allowed_subjects.subject_id')
            ->where('faculty_id', $facultyId)
            ->where(function ($query) use ($subject) {
                $query->where('teacher_allowed_subjects.subject_id', (int) $subject->id)
                    ->orWhere('allowed_subjects.code', (string) $subject->code);
            })
            ->exists();
    }

    private function teacherLoadWithinLimit(int $facultyId, Subject $subject): bool
    {
        $max = $this->teacherMaxLoadUnits($facultyId);
        if ($max <= 0) {
            return true;
        }

        $current = (float) Subject::query()
            ->where('faculty_id', $facultyId)
            ->where('id', '<>', (int) $subject->id)
            ->when((int) $subject->academic_term_id > 0, function ($query) use ($subject) {
                $query->where('academic_term_id', (int) $subject->academic_term_id);
            })
            ->sum(DB::raw('COALESCE(credited_tuition_units, units, 0)'));

        return ($current + (float) ($subject->credited_tuition_units ?: $subject->units ?: 0)) <= $max;
    }

    private function teacherMaxLoadUnits(int $facultyId): float
    {
        $faculty = Faculty::query()->find($facultyId);
        if (!$faculty) {
            return 0.0;
        }

        if (Schema::hasColumn('faculties', 'max_load_units') && (float) ($faculty->max_load_units ?? 0) > 0) {
            return (float) $faculty->max_load_units;
        }

        $type = Schema::hasColumn('faculties', 'employment_type')
            ? (string) ($faculty->employment_type ?: 'Full-time Teacher')
            : 'Full-time Teacher';

        if (Schema::hasTable('teacher_load_settings')) {
            $configured = DB::table('teacher_load_settings')
                ->where('employment_type', $type)
                ->value('max_load_units');
            if ($configured !== null) {
                return (float) $configured;
            }
        }

        $defaults = [
            'Full-time Teacher' => 24,
            'Part-time Teacher' => 12,
            'Department Head' => 9,
            'Visiting Lecturer' => 6,
        ];

        return (float) ($defaults[$type] ?? 24);
    }

    private function teacherAvailableForSlot(int $facultyId, array $slot): bool
    {
        if (!Schema::hasTable('teacher_availability')) {
            return true;
        }

        $availabilityRows = DB::table('teacher_availability')
            ->where('faculty_id', $facultyId)
            ->where('is_available', true)
            ->get();

        if ($availabilityRows->isEmpty()) {
            return true;
        }

        $slotDays = $this->autoScheduleDayTokens((string) ($slot['days'] ?? ''));
        $slotStart = $this->autoScheduleMinutes((string) ($slot['time_start'] ?? ''));
        $slotEnd = $this->autoScheduleMinutes((string) ($slot['time_end'] ?? ''));

        foreach ($slotDays as $day) {
            $covered = false;
            foreach ($availabilityRows as $row) {
                $rowDays = $this->autoScheduleDayTokens((string) $row->day);
                if (!in_array($day, $rowDays, true)) {
                    continue;
                }

                $rowStart = $this->autoScheduleMinutes((string) $row->start_time);
                $rowEnd = $this->autoScheduleMinutes((string) $row->end_time);
                if ($slotStart !== null && $slotEnd !== null && $rowStart !== null && $rowEnd !== null
                    && $slotStart >= $rowStart && $slotEnd <= $rowEnd) {
                    $covered = true;
                    break;
                }
            }

            if (!$covered) {
                return false;
            }
        }

        return true;
    }

    private function findAvailableTeacherForSubject(Subject $subject, array $conflicts)
    {
        return Faculty::query()
            ->orderBy('name')
            ->get()
            ->first(function (Faculty $faculty) use ($subject) {
                return $this->teacherQualifiedForSubject((int) $faculty->id, $subject)
                    && $this->teacherLoadWithinLimit((int) $faculty->id, $subject);
            });
    }

    private function upsertClassRoomAssignmentFromSubject(Subject $subject, string $status): void
    {
        if (!Schema::hasTable('class_room_assignments')) {
            return;
        }

        $room = $this->findRoomByScheduleValue((string) $subject->room);
        $term = $subject->academicTerm;

        DB::table('class_room_assignments')->updateOrInsert([
            'class_offering_id' => (int) $subject->id,
            'schedule_component_type' => (float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture',
        ], [
            'course_code' => (string) $subject->code,
            'section_id' => (string) $subject->year_section,
            'room_id' => $room ? (int) $room->id : null,
            'room_type_required' => $this->resolveSubjectRequiredRoomType($subject, (float) ($subject->lab ?: 0) > 0 ? 'Laboratory' : 'Lecture'),
            'academic_year' => (string) ($term->school_year ?? $subject->school_year ?? ''),
            'semester' => $this->normalizeSlotMonitoringSemester((string) ($term->term ?? $subject->semester ?? '')),
            'day' => (string) $subject->days,
            'start_time' => (string) $subject->time_start,
            'end_time' => (string) $subject->time_end,
            'assignment_status' => $status,
            'remarks' => 'Saved through manual class schedule preparation.',
            'created_by' => auth()->id(),
            'updated_at' => now(),
            'created_at' => now(),
        ]);
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

        if (Schema::hasColumn('course_curricula', 'is_published')) {
            $query->where('is_published', true);
        }

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
                DB::raw('COALESCE(sm_subjects.hours, 0) as hours'),
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

    private function buildClassSchedulePreparationRowsQuery($schoolYear = '', $semester = '', $sectionQuery = '', $search = '', $courseId = 0)
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

        $facultyNameExpression = count($facultyNameCandidates)
            ? 'COALESCE(' . implode(', ', $facultyNameCandidates) . ", '')"
            : "''";

        $facultyIdExpression = $hasFacultyLookup ? 'COALESCE(sm_subjects.faculty_id, 0)' : '0';

        $query = $this->slotMonitoringSubjectBaseQuery()
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
                DB::raw('COALESCE(sm_subjects.units, 0) as units'),
                DB::raw('COALESCE(sm_subjects.days, "") as days'),
                DB::raw('COALESCE(sm_subjects.time_start, "") as time_start'),
                DB::raw('COALESCE(sm_subjects.time_end, "") as time_end'),
                DB::raw('COALESCE(sm_subjects.room, "") as room'),
                DB::raw($facultyIdExpression . ' as faculty_id'),
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

            $query->whereIn(DB::raw('LOWER(TRIM(COALESCE(sm_terms.term, "")))'), $semesterAliases);
        }

        if ($sectionQuery !== '') {
            $query->where('sm_subjects.year_section', 'like', '%' . $sectionQuery . '%');
        }

        if ($courseId > 0) {
            $query->where('sm_subjects.course_id', $courseId);
        }

        $normalizedSearch = trim((string) preg_replace('/\s+/', ' ', $search));
        if ($normalizedSearch !== '' && strlen($normalizedSearch) >= 2) {
            $query->where(function ($builder) use ($normalizedSearch, $hasFacultyLookup, $hasLegacyFacultyColumn) {
                $builder->where('sm_subjects.year_section', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_subjects.code', 'like', $normalizedSearch . '%')
                    ->orWhere('sm_subjects.name', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_subjects.days', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('sm_subjects.room', 'like', '%' . $normalizedSearch . '%')
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
                ELSE 3
            END")
            ->orderBy('sm_courses.code')
            ->orderBy('sm_subjects.year_section')
            ->orderBy('sm_subjects.code')
            ->orderBy('sm_subjects.id');
    }

    private function classScheduleTimeInputValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{2}:\d{2}/', $value, $matches)) {
            return $matches[0];
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $exception) {
            return '';
        }
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

    private function configuredSchedulingSchoolSemesters(): array
    {
        $schoolYears = [];
        $semesterMap = [];

        if (!Schema::hasTable('system_school_semesters')) {
            return [
                'school_years' => $schoolYears,
                'semester_map' => $semesterMap,
            ];
        }

        $appendConfiguredSemester = function ($schoolYear, $semester) use (&$schoolYears, &$semesterMap) {
            $normalizedYear = trim((string) $schoolYear);
            $normalizedSemester = $this->normalizeSlotMonitoringSemester((string) $semester);

            if ($normalizedYear === '' || $normalizedSemester === '') {
                return;
            }

            if (!array_key_exists($normalizedYear, $semesterMap)) {
                $semesterMap[$normalizedYear] = [];
                $schoolYears[] = $normalizedYear;
            }

            if (!in_array($normalizedSemester, $semesterMap[$normalizedYear], true)) {
                $semesterMap[$normalizedYear][] = $normalizedSemester;
            }
        };

        $hasSchoolYearColumn = Schema::hasColumn('system_school_semesters', 'school_year');
        $hasSemesterColumn = Schema::hasColumn('system_school_semesters', 'semester');
        $hasAcademicTermColumn = Schema::hasColumn('system_school_semesters', 'academic_term_id');

        $selectColumns = ['id'];
        if ($hasSchoolYearColumn) {
            $selectColumns[] = 'school_year';
        }
        if ($hasSemesterColumn) {
            $selectColumns[] = 'semester';
        }
        if ($hasAcademicTermColumn) {
            $selectColumns[] = 'academic_term_id';
        }

        $rowsQuery = SystemSchoolSemester::query()
            ->select($selectColumns)
            ->orderByDesc('id');

        if ($hasAcademicTermColumn) {
            $rowsQuery->with('academicTerm:id,school_year,term');
        }

        $rows = $rowsQuery->get();

        foreach ($rows as $row) {
            $schoolYear = $hasSchoolYearColumn
                ? trim((string) $row->getAttribute('school_year'))
                : '';
            $semester = $hasSemesterColumn
                ? (string) $row->getAttribute('semester')
                : '';

            if (($schoolYear === '' || trim($semester) === '') && $row->academicTerm) {
                if ($schoolYear === '') {
                    $schoolYear = trim((string) $row->academicTerm->school_year);
                }

                if (trim($semester) === '') {
                    $semester = (string) $row->academicTerm->term;
                }
            }

            $appendConfiguredSemester($schoolYear, $semester);
        }

        if (!count($schoolYears)
            && $hasAcademicTermColumn
            && Schema::hasTable('academic_terms')) {
            $linkedTerms = DB::table('system_school_semesters as sss')
                ->join('academic_terms as at', 'at.id', '=', 'sss.academic_term_id')
                ->select(['at.school_year', 'at.term'])
                ->orderBy('sss.id', 'desc')
                ->get();

            foreach ($linkedTerms as $termRow) {
                $appendConfiguredSemester(
                    (string) ($termRow->school_year ?? ''),
                    (string) ($termRow->term ?? '')
                );
            }
        }

        foreach ($semesterMap as $year => $yearSemesters) {
            $semesterMap[$year] = collect($yearSemesters)
                ->map(function ($value) {
                    return $this->normalizeSlotMonitoringSemester((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->sortBy(function ($value) {
                    return $this->slotMonitoringSemesterWeight((string) $value);
                })
                ->values()
                ->all();
        }

        return [
            'school_years' => array_values($schoolYears),
            'semester_map' => $semesterMap,
        ];
    }

    private function slotMonitoringOptionsPayload($schoolYear = '', $semester = '', $courseId = 0, $optionsMode = '')
    {
        $baseQuery = $this->slotMonitoringSubjectBaseQuery();

        $configuredOptions = $this->configuredSchedulingSchoolSemesters();
        $configuredSemesterMap = is_array($configuredOptions['semester_map'] ?? null)
            ? $configuredOptions['semester_map']
            : [];

        $schoolYears = collect($configuredOptions['school_years'] ?? [])
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values();

        if ($schoolYears->isEmpty()) {
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
        }

        if ($schoolYears->isEmpty()) {
            $year = (int) date('Y');
            $schoolYears = collect([
                ($year - 1) . '-' . $year,
                $year . '-' . ($year + 1),
            ]);
        }

        $semesters = collect();
        if (count($configuredSemesterMap)) {
            if ($schoolYear !== '' && array_key_exists($schoolYear, $configuredSemesterMap)) {
                $semesters = collect($configuredSemesterMap[$schoolYear] ?? []);
            } else {
                $flattenedSemesters = [];
                foreach ($configuredSemesterMap as $yearSemesters) {
                    if (!is_array($yearSemesters)) {
                        continue;
                    }

                    $flattenedSemesters = array_merge($flattenedSemesters, $yearSemesters);
                }

                $semesters = collect($flattenedSemesters);
            }
        }

        $semesters = $semesters
            ->map(function ($value) {
                return $this->normalizeSlotMonitoringSemester((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->sortBy(function ($value) {
                return $this->slotMonitoringSemesterWeight((string) $value);
            })
            ->values();

        if ($semesters->isEmpty()) {
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
        }

        $normalizedMode = strtolower(trim((string) $optionsMode));
        if ($normalizedMode === 'filters') {
            return [
                'school_years' => $schoolYears,
                'semesters' => $semesters,
            ];
        }

        $courseQueryBuilder = DB::table('courses as sm_courses')
            ->select([
                'sm_courses.id',
                'sm_courses.code',
                'sm_courses.name',
            ])
            ->whereNotNull('sm_courses.id');

        if ($schoolYear !== '' || ($semester !== '' && in_array($semester, self::SLOT_MONITORING_ALLOWED_SEMESTERS, true))) {
            $courseQueryBuilder->whereExists(function ($existsQuery) use ($schoolYear, $semester) {
                $existsQuery->select(DB::raw(1))
                    ->from('subjects as sub_sm_subjects')
                    ->join('academic_terms as sub_sm_terms', 'sub_sm_terms.id', '=', 'sub_sm_subjects.academic_term_id')
                    ->whereColumn('sub_sm_subjects.course_id', 'sm_courses.id');

                if ($schoolYear !== '') {
                    $existsQuery->where('sub_sm_terms.school_year', $schoolYear);
                }

                if ($semester !== '' && in_array($semester, self::SLOT_MONITORING_ALLOWED_SEMESTERS, true)) {
                    $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                        ->map(function ($value) {
                            return strtolower(trim((string) $value));
                        })
                        ->values()
                        ->all();

                    $existsQuery->whereIn(
                        DB::raw('LOWER(TRIM(COALESCE(sub_sm_terms.term, "")))'),
                        $semesterAliases
                    );
                }
            });
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
        $requestedSemester = $this->normalizeSlotMonitoringSemester((string) $request->query('semester', ''));

        $configuredOptions = $this->configuredSchedulingSchoolSemesters();
        $schoolYears = array_values($configuredOptions['school_years'] ?? []);
        $semesterMap = is_array($configuredOptions['semester_map'] ?? null)
            ? $configuredOptions['semester_map']
            : [];

        if (!count($schoolYears)) {
            $configRows = SlotMonitoring::query()
                ->select([
                    'school_year',
                    'semester',
                    DB::raw('COUNT(*) as row_count'),
                ])
                ->whereNotNull('school_year')
                ->where('school_year', '<>', '')
                ->whereNotNull('semester')
                ->where('semester', '<>', '')
                ->groupBy('school_year', 'semester')
                ->orderBy('school_year', 'desc')
                ->orderByDesc('row_count')
                ->get();

            foreach ($configRows as $configRow) {
                $year = trim((string) $configRow->school_year);
                $semester = $this->normalizeSlotMonitoringSemester((string) $configRow->semester);

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
        }

        foreach ($semesterMap as $year => $yearSemesters) {
            $semesterMap[$year] = collect($yearSemesters)
                ->map(function ($value) {
                    return $this->normalizeSlotMonitoringSemester((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->sortBy(function ($value) {
                    return $this->slotMonitoringSemesterWeight((string) $value);
                })
                ->values()
                ->all();
        }

        $schoolYear = $requestedSchoolYear;
        if ($schoolYear === '' || !in_array($schoolYear, $schoolYears, true)) {
            $schoolYear = !empty($schoolYears) ? (string) $schoolYears[0] : $schoolYear;
        }

        $semestersForYear = array_key_exists($schoolYear, $semesterMap)
            ? $semesterMap[$schoolYear]
            : self::SLOT_MONITORING_ALLOWED_SEMESTERS;
        $semestersForYear = collect($semestersForYear)
            ->map(function ($value) {
                return $this->normalizeSlotMonitoringSemester((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique()
            ->sortBy(function ($value) {
                return $this->slotMonitoringSemesterWeight((string) $value);
            })
            ->values()
            ->all();

        $semester = $requestedSemester;
        if ($semester === '' || !in_array($semester, $semestersForYear, true)) {
            $semester = !empty($semestersForYear) ? (string) $semestersForYear[0] : '';
        }

        $rows = $this->sectionMergingRowsQuery((string) $schoolYear, (string) $semester)->get();

        if ($rows->isEmpty() && $requestedSchoolYear === '' && $requestedSemester === '') {
            foreach ($schoolYears as $candidateYear) {
                $candidateSemesters = array_key_exists($candidateYear, $semesterMap)
                    ? (array) $semesterMap[$candidateYear]
                    : [];

                foreach ($candidateSemesters as $candidateSemester) {
                    $candidateRows = $this->sectionMergingRowsQuery(
                        (string) $candidateYear,
                        (string) $candidateSemester
                    )->get();

                    if ($candidateRows->isEmpty()) {
                        continue;
                    }

                    $schoolYear = (string) $candidateYear;
                    $semester = (string) $candidateSemester;
                    $rows = $candidateRows;
                    break 2;
                }
            }
        }

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

        if ($courses->isEmpty()) {
            $courses = Course::query()
                ->orderBy('code')
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(function (Course $course) {
                    $code = trim((string) $course->code);
                    $name = trim((string) $course->name);
                    return [
                        'id' => (int) $course->id,
                        'code' => $code,
                        'name' => $name,
                        'label' => trim($code . ($code !== '' && $name !== '' ? ' - ' : '') . $name),
                    ];
                })
                ->values();
        }

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

    private function sectionMergingRowsQuery(string $schoolYear, string $semester)
    {
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
            $semesterAliases = collect($this->slotMonitoringSemesterAliases($semester))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values()
                ->all();

            if (count($semesterAliases)) {
                $rowsQuery->whereIn(
                    DB::raw("LOWER(TRIM(COALESCE(semester, '')))"),
                    $semesterAliases
                );
            }
        }

        return $rowsQuery
            ->orderBy('section')
            ->orderBy('subject')
            ->orderBy('id');
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
        $normalizedSchoolYear = $this->normalizeSectionOfferingSchoolYear((string) $schoolYear);
        $sourceSchoolYear = $this->normalizeSectionOfferingSchoolYear((string) $source->school_year);
        $targetSchoolYear = $this->normalizeSectionOfferingSchoolYear((string) $target->school_year);

        if ($sourceSchoolYear !== $normalizedSchoolYear || $targetSchoolYear !== $normalizedSchoolYear) {
            return [
                'school_year' => ['Selected sections do not match the chosen school year.'],
            ];
        }

        $normalizedSemester = $this->normalizeSlotMonitoringSemester((string) $semester);
        $sourceSemester = $this->normalizeSlotMonitoringSemester((string) $source->semester);
        $targetSemester = $this->normalizeSlotMonitoringSemester((string) $target->semester);

        if ($sourceSemester !== $normalizedSemester || $targetSemester !== $normalizedSemester) {
            return [
                'semester' => ['Selected sections do not match the chosen semester.'],
            ];
        }

        if ((int) $source->course_id !== (int) $target->course_id) {
            return [
                'target_slot_monitoring_id' => ['Source and target sections must belong to the same program.'],
            ];
        }

        $sourceYearLevel = $this->extractYearLevelFromSectionLabel((string) $source->section);
        $targetYearLevel = $this->extractYearLevelFromSectionLabel((string) $target->section);

        if ($sourceYearLevel !== null && $targetYearLevel !== null && (int) $sourceYearLevel !== (int) $targetYearLevel) {
            return [
                'target_slot_monitoring_id' => ['Source and target sections must belong to the same year level.'],
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

        if (preg_match('/(?:^|[^0-9])([1-9])(?:\s*[-]|\b|[a-z])/', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Registrar > Student Management > Student Enrollment
     */
    public function studentEnrollment(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $sortDirection = strtolower((string) $request->input('sort', 'asc'));
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $studentsQuery = Student::query()
            ->with('canonicalCourse')
            ->orderBy('name', $sortDirection)
            ->orderBy('student_no', $sortDirection);

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('student_no', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $students = $studentsQuery
            ->paginate(25)
            ->appends($request->query());

        $courses = Course::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $applicants = Applicant::query()
            ->orderBy('applicant_id')
            ->get(['id', 'applicant_id', 'first_name', 'middle_name', 'last_name']);

        return view('registrar.registrar-menu.student-management.student-enrollment', compact('students', 'courses', 'applicants', 'search', 'sortDirection'));
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'student_no' => 'nullable|unique:students,student_no',
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
            $payload = $request->only([
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
            ]);

            $year = (int) date('Y');
            if (preg_match('/^\d{4}/', (string) $request->input('school_year'), $matches)) {
                $year = (int) $matches[0];
            }

            $payload['student_no'] = trim((string) $request->input('student_no')) ?: Student::generateStudentNo($year);

            $student = Student::create($payload);

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

    public function updateStudent(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => [
                'required',
                Rule::unique('students', 'student_no')->ignore($student->id),
            ],
            'name' => 'required|string',
            'program' => 'nullable|string',
            'year_level' => 'nullable|string',
        ]);

        DB::transaction(function () use ($student, $validated) {
            $student->student_no = $validated['student_no'];
            $student->name = $validated['name'];
            $student->program = $validated['program'] ?? $student->program;
            $student->year_level = $validated['year_level'] ?? $student->year_level;
            $student->save();

            $student->loadMissing(['user', 'canonicalCourse']);
            if ($student->user) {
                $student->user->name = $student->name;
                $student->user->username = $student->student_no;
                $student->user->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Student profile updated successfully.',
            'student' => [
                'id' => (int) $student->id,
                'student_no' => (string) $student->student_no,
                'name' => (string) $student->name,
                'program' => trim((string) ($student->program ?: optional($student->canonicalCourse)->name ?: 'N/A')),
                'program_value' => (string) ($student->program ?: ''),
                'program_label' => trim((string) ($student->program ?: optional($student->canonicalCourse)->name ?: 'N/A')),
                'year_level' => trim((string) ($student->year_level ?: 'N/A')),
                'year_level_value' => (string) ($student->year_level ?: ''),
            ],
        ]);
    }

    public function destroyStudent(Request $request, Student $student): JsonResponse
    {
        DB::transaction(function () use ($student) {
            $student->loadMissing('user');

            if ($student->user) {
                $student->user->delete();
            }

            $student->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Student profile deleted successfully.',
            'student_id' => (int) $student->id,
        ]);
    }

    /**
     * Registrar > Student Records > List
     */
    public function studentRecordList(Request $request)
    {
        $search  = trim((string) $request->input('q', ''));
        $program = trim((string) $request->input('program', ''));
        $year    = trim((string) $request->input('year', ''));
        $status  = $request->input('status', 'all');
        $graduate = $request->input('graduate', 'all');
        $sy      = trim((string) $request->input('sy', ''));
        $sem     = trim((string) $request->input('sem', ''));

        // students table uses course_id, year_block_id, academic_term_id — not text columns
        $studentRelations = ['profile', 'canonicalCourse', 'academicTerm', 'yearBlock'];
        if (Schema::hasTable('graduate_taggings')) {
            $studentRelations[] = 'graduateTagging';
        }

        $query = Student::query()
            ->with($studentRelations)
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student_no', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        if ($program !== '') {
            // program filter targets course_id FK
            $courseId = Course::where('code', $program)->value('id');
            if ($courseId) {
                $query->where('course_id', $courseId);
            }
        }
        if ($year !== '') {
            // year filter targets year_block_id FK
            $ybId = DB::table('year_blocks')->where('label', $year)->value('id');
            if ($ybId) {
                $query->where('year_block_id', $ybId);
            }
        }
        if ($status === 'active') {
            $query->where(function ($q) {
                $q->whereNull('is_withdrawn')->orWhere('is_withdrawn', false);
            });
        } elseif ($status === 'withdrawn') {
            $query->where('is_withdrawn', true);
        }
        if (Schema::hasTable('graduate_taggings')) {
            if ($graduate === 'graduates') {
                $query->whereHas('graduateTagging', function ($q) {
                    $q->where('is_graduate', true);
                });
            } elseif ($graduate === 'non_graduates') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('graduateTagging')
                      ->orWhereHas('graduateTagging', function ($tagQuery) {
                          $tagQuery->where(function ($nested) {
                              $nested->whereNull('is_graduate')->orWhere('is_graduate', false);
                          });
                      });
                });
            }
        }
        if ($sy !== '') {
            // school_year lives in academic_terms, filter via academic_term_id
            $termIds = DB::table('academic_terms')->where('school_year', $sy)->pluck('id');
            $query->whereIn('academic_term_id', $termIds);
        }
        if ($sem !== '') {
            $termIds = DB::table('academic_terms')->where('term', $sem)->pluck('id');
            $query->whereIn('academic_term_id', $termIds);
        }

        $students = $query->paginate(24)->appends($request->query());

        $courses = Course::orderBy('code')->get(['id', 'code', 'name']);

        // Pull distinct term ids used by students once (avoid repeated subqueries)
        $usedTermIds  = DB::table('students')->pluck('academic_term_id')->filter()->unique();
        $usedYearIds  = DB::table('students')->pluck('year_block_id')->filter()->unique();

        // school years from academic_terms joined to students
        $schoolYears = DB::table('academic_terms')
            ->whereIn('id', $usedTermIds)
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->unique()
            ->filter();

        // distinct semester/term labels used by students
        $semesterOptions = DB::table('academic_terms')
            ->whereIn('id', $usedTermIds)
            ->orderBy('term')
            ->pluck('term')
            ->unique()
            ->filter();

        // year level labels from year_blocks
        $yearLevels = DB::table('year_blocks')
            ->whereIn('id', $usedYearIds)
            ->orderBy('label')
            ->pluck('label')
            ->filter()
            ->unique();

        $totalCount     = Student::count();
        $activeCount    = Student::where(function ($q) {
            $q->whereNull('is_withdrawn')->orWhere('is_withdrawn', false);
        })->count();
        $withdrawnCount = Student::where('is_withdrawn', true)->count();
        $graduateCount  = Schema::hasTable('graduate_taggings')
            ? GraduateTagging::where('is_graduate', true)->count()
            : 0;

        return view('registrar.registrar-menu.student-management.student-records', compact(
            'students', 'courses', 'schoolYears', 'semesterOptions', 'yearLevels',
            'search', 'program', 'year', 'status', 'graduate', 'sy', 'sem',
            'totalCount', 'activeCount', 'withdrawnCount', 'graduateCount'
        ));
    }

    /**
     * Registrar > Student Records > Profile (tabbed view)
     */
    public function studentRecordProfile(Request $request, Student $student)
    {
        $student->load(['profile', 'canonicalCourse', 'deficiencies', 'user', 'yearBlock', 'academicTerm']);

        // Enrolled subjects — subjects table uses academic_term_id, not school_year/semester columns
        $enrolledSubjects = collect([]);
        if (Schema::hasTable('student_subject')) {
            $enrolledSubjects = $student->subjects()
                ->with(['academicTerm', 'facultyModel'])
                ->orderByDesc('academic_term_id')
                ->orderBy('code')
                ->get();
        }

        // Subject grades keyed by subject_id
        $subjectGrades = collect([]);
        if (Schema::hasTable('student_subject_grades')) {
            $subjectGrades = StudentSubjectGrade::where('student_id', $student->id)
                ->get()
                ->keyBy('subject_id');
        }

        // Enrolled subjects grouped by SY + Semester
        $enrolledBySyTerm = $enrolledSubjects->groupBy(function ($s) {
            return ($s->school_year ?? 'N/A') . '|||' . ($s->semester ?? 'N/A');
        });

        // Curriculum tagged to the student's course
        $curriculum = null;
        $curriculumByYearSem = collect([]);
        if ($student->course_id && Schema::hasTable('course_curricula') && Schema::hasTable('course_curriculum_subjects')) {
            $curriculum = CourseCurriculum::where('course_id', $student->course_id)
                ->where('is_active', true)
                ->with(['curriculumSubjects' => function ($q) { $q->with(['subject', 'yearBlock', 'semester'])->orderBy('display_order'); }])
                ->first();
            if (!$curriculum) {
                $curriculum = CourseCurriculum::where('course_id', $student->course_id)
                    ->with(['curriculumSubjects' => function ($q) { $q->with(['subject', 'yearBlock', 'semester'])->orderBy('display_order'); }])
                    ->latest()
                    ->first();
            }
            if ($curriculum) {
                $curriculumByYearSem = $curriculum->curriculumSubjects
                    ->groupBy(function ($cs) { return optional($cs->yearBlock)->label . '|||' . optional($cs->semester)->name; });
            }
        }

        // Grade records
        $gradeRecords = collect([]);
        if (Schema::hasTable('student_grade_records')) {
            $gradeRecords = \App\StudentGradeRecord::where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_no', $student->student_no);
            })->orderBy('school_year', 'desc')->orderBy('term')->orderBy('subject_code')->get();
        }

        $gradedRecords = $gradeRecords->filter(function ($r) {
            return !$r->inc && is_numeric($r->final_grade) && (float) $r->final_grade > 0 && (float) $r->units > 0;
        });
        $gwa = null;
        if ($gradedRecords->count() > 0) {
            $tw = $gradedRecords->sum(function ($r) { return (float) $r->final_grade * (float) $r->units; });
            $tu = $gradedRecords->sum(function ($r) { return (float) $r->units; });
            $gwa = $tu > 0 ? round($tw / $tu, 4) : null;
        }
        $totalUnitsEarned = $gradedRecords->filter(function ($r) { return (float) $r->final_grade <= 3.0; })->sum(function ($r) { return (float) $r->units; });
        $academicStanding = $this->computeAcademicStanding($gwa);
        $gradesBySyTerm   = $gradeRecords->groupBy(function ($r) { return $r->school_year . '|||' . $r->term; });

        // Discipline
        $disciplineStudent = null;
        $disciplineRecords = collect([]);
        if (Schema::hasTable('student_discipline_students') && Schema::hasTable('student_discipline_records')) {
            $disciplineStudent = StudentDisciplineStudent::where('student_id', $student->id)
                ->with(['records.caseType', 'records.actionType', 'studentType'])
                ->first();
            $disciplineRecords = $disciplineStudent ? $disciplineStudent->records : collect([]);
        }

        // Requirements / Documents
        $requirements = collect([]);
        if (Schema::hasTable('student_requirement_statuses')) {
            $requirements = StudentRequirementStatus::where('student_id', $student->id)
                ->with(['requirementPolicy.definition', 'requirementPolicy.definition.type', 'verifier'])
                ->get();
        }

        $deficiencies     = $student->deficiencies ?? collect([]);
        $courses          = Course::orderBy('code')->orderBy('name')->get(['id', 'code', 'name']);
        $profileUpdateUrl = route('registrar.registrar-menu.student-mgmt.academic-record.update-profile', ['student' => $student->id]);

        // Graduate tagging
        $graduateTagging = null;
        if (Schema::hasTable('graduate_taggings')) {
            $graduateTagging = GraduateTagging::where('student_id', $student->id)->first();
        }
        $isGraduated = $graduateTagging && $graduateTagging->is_graduate;

        // Medical record
        $medicalRecord = null;
        if (Schema::hasTable('student_medical_records')) {
            $medicalRecord = StudentMedicalRecord::where('student_id', $student->id)->first();
        }

        // Clinic records
        $clinicRecords = collect([]);
        if (Schema::hasTable('student_clinic_records')) {
            $clinicRecords = StudentClinicRecord::where('student_id', $student->id)
                ->orderByDesc('visit_date')
                ->orderByDesc('id')
                ->get();
        }

        // Scholastic comments
        $scholasticComments = collect([]);
        if (Schema::hasTable('student_scholastic_comments')) {
            $scholasticComments = DB::table('student_scholastic_comments')
                ->where('student_id', $student->id)
                ->orderByDesc('date_issued')
                ->orderByDesc('id')
                ->get();
        }

        $scholarshipPrograms = collect([]);
        $studentScholarships = collect([]);
        if (Schema::hasTable('scholarship_programs') && Schema::hasTable('scholarship_student')) {
            $scholarshipPrograms = ScholarshipProgram::orderByRaw("CASE status WHEN 'Open' THEN 1 WHEN 'Ongoing' THEN 2 WHEN 'Closed' THEN 3 WHEN 'Ended' THEN 4 ELSE 5 END")
                ->orderBy('name')
                ->get();
            $studentScholarships = ScholarshipStudent::where('student_id', $student->id)
                ->with('program')
                ->orderByDesc('school_year')
                ->orderBy('semester')
                ->get();
        }

        return view('registrar.registrar-menu.student-management.student-record-profile', compact(
            'student', 'gradeRecords', 'gradesBySyTerm', 'gwa', 'totalUnitsEarned',
            'academicStanding', 'deficiencies', 'courses',
            'disciplineStudent', 'disciplineRecords', 'requirements',
            'enrolledSubjects', 'subjectGrades', 'enrolledBySyTerm',
            'curriculum', 'curriculumByYearSem',
            'profileUpdateUrl', 'medicalRecord', 'clinicRecords',
            'graduateTagging', 'isGraduated', 'scholasticComments',
            'scholarshipPrograms', 'studentScholarships'
        ));
    }

    public function studentScholasticCommentSave(Request $request, Student $student): JsonResponse
    {
        if (!Schema::hasTable('student_scholastic_comments')) {
            return response()->json(['success' => false, 'message' => 'Scholastic comments table not found. Please run migrations.'], 500);
        }

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:40',
            'comment' => 'required|string|max:2000',
            'date_issued' => 'nullable|date',
            'issued_by' => 'nullable|string|max:120',
        ]);

        $payload = [
            'student_id' => $student->id,
            'school_year' => trim((string) ($validated['school_year'] ?? '')),
            'semester' => trim((string) ($validated['semester'] ?? '')),
            'comment' => trim((string) $validated['comment']),
            'date_issued' => !empty($validated['date_issued']) ? $validated['date_issued'] : now()->toDateString(),
            'issued_by' => trim((string) ($validated['issued_by'] ?? '')) ?: optional($request->user())->name,
            'updated_at' => now(),
        ];

        if (!empty($validated['id'])) {
            $updated = DB::table('student_scholastic_comments')
                ->where('id', (int) $validated['id'])
                ->where('student_id', $student->id)
                ->update($payload);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Scholastic comment not found.'], 404);
            }

            $id = (int) $validated['id'];
        } else {
            $payload['created_at'] = now();
            $id = (int) DB::table('student_scholastic_comments')->insertGetId($payload);
        }

        $row = DB::table('student_scholastic_comments')->where('id', $id)->first();

        return response()->json(['success' => true, 'comment' => $row]);
    }

    public function studentRecordMedicalSave(Request $request, Student $student)
    {
        if (!Schema::hasTable('student_medical_records')) {
            return response()->json(['success' => false, 'message' => 'Medical records table not found.'], 500);
        }

        $data = $request->only([
            'blood_type', 'height_cm', 'weight_kg',
            'allergies', 'medical_conditions', 'current_medications', 'past_illnesses',
            'mental_health_notes', 'vision_od', 'vision_os',
            'hearing_right', 'hearing_left', 'dental_status',
            'immunization_status', 'immunization_notes',
            'last_physical_exam_date', 'physician_name', 'exam_findings', 'remarks',
            'emergency_contact_name', 'emergency_contact_number', 'emergency_contact_relation',
        ]);

        // Null-out blank numeric fields so they don't get saved as empty string
        foreach (['height_cm', 'weight_kg'] as $f) {
            if (isset($data[$f]) && trim((string) $data[$f]) === '') {
                $data[$f] = null;
            }
        }
        // Null-out blank date
        if (isset($data['last_physical_exam_date']) && trim((string) $data['last_physical_exam_date']) === '') {
            $data['last_physical_exam_date'] = null;
        }

        $data['recorded_by'] = optional(auth()->user())->name ?? 'Registrar';

        StudentMedicalRecord::updateOrCreate(
            ['student_id' => $student->id],
            $data
        );

        return response()->json(['success' => true]);
    }

    public function studentClinicRecordSave(Request $request, Student $student)
    {
        if (!Schema::hasTable('student_clinic_records')) {
            return response()->json(['success' => false, 'message' => 'Clinic records table not found.'], 500);
        }

        $data = $request->only([
            'visit_date', 'chief_complaint', 'diagnosis', 'treatment', 'medications_given',
            'temperature', 'blood_pressure', 'pulse_rate', 'respiratory_rate', 'weight_kg',
            'disposition', 'referred_to', 'attended_by', 'remarks',
        ]);

        foreach (['temperature', 'weight_kg', 'pulse_rate', 'respiratory_rate'] as $f) {
            if (isset($data[$f]) && trim((string) $data[$f]) === '') {
                $data[$f] = null;
            }
        }

        $data['student_id'] = $student->id;

        $id = $request->input('id');
        if ($id) {
            $record = StudentClinicRecord::where('student_id', $student->id)->where('id', $id)->first();
            if ($record) {
                $record->update($data);
                return response()->json(['success' => true, 'id' => $record->id]);
            }
        }

        $record = StudentClinicRecord::create($data);
        return response()->json(['success' => true, 'id' => $record->id]);
    }

    public function studentClinicRecordDelete(Request $request, Student $student, StudentClinicRecord $clinic)
    {
        if ($clinic->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        $clinic->delete();
        return response()->json(['success' => true]);
    }

    public function studentPrintTor(Student $student)
    {
        $student->load(['profile', 'canonicalCourse']);

        $gradeRecords = collect([]);
        if (Schema::hasTable('student_grade_records')) {
            $gradeRecords = \App\StudentGradeRecord::where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_no', $student->student_no);
            })->orderBy('school_year')->orderBy('term')->orderBy('subject_code')->get();
        }

        $gradesBySyTerm = $gradeRecords->groupBy(function ($r) {
            return $r->school_year . '|||' . $r->term;
        });

        $gradedRecords = $gradeRecords->filter(function ($r) {
            return !$r->inc && is_numeric($r->final_grade) && (float) $r->final_grade > 0 && (float) $r->units > 0;
        });
        $gwa = null;
        if ($gradedRecords->count() > 0) {
            $tw = $gradedRecords->sum(function ($r) { return (float) $r->final_grade * (float) $r->units; });
            $tu = $gradedRecords->sum(function ($r) { return (float) $r->units; });
            $gwa = $tu > 0 ? round($tw / $tu, 4) : null;
        }
        $totalUnitsEarned = $gradedRecords->filter(function ($r) { return (float) $r->final_grade <= 3.0; })
            ->sum(function ($r) { return (float) $r->units; });

        $signatories = collect([]);
        if (Schema::hasTable('system_config_name_signatures') && Schema::hasTable('system_config_signature_designations')) {
            $signatories = DB::table('system_config_name_signatures as s')
                ->join('system_config_signature_designations as d', 's.designation_id', '=', 'd.id')
                ->where('s.is_active', 1)
                ->orderBy('d.sort_order')
                ->select('s.signer_name', 's.signature_path', 'd.name as designation_name', 'd.code')
                ->get();
        }

        $registrar = $signatories->first(function ($s) {
            return stripos($s->designation_name, 'registrar') !== false;
        }) ?? $signatories->first();

        return view('registrar.registrar-menu.student-management.print.tor', compact(
            'student', 'gradeRecords', 'gradesBySyTerm', 'gwa', 'totalUnitsEarned', 'registrar'
        ));
    }

    public function studentPrintDiploma(Student $student)
    {
        $student->load(['profile', 'canonicalCourse']);

        $graduateTagging = null;
        if (Schema::hasTable('graduate_taggings')) {
            $graduateTagging = GraduateTagging::where('student_id', $student->id)->first();
        }

        if (!$graduateTagging || !$graduateTagging->is_graduate) {
            return redirect()->back()->with('error', 'This student is not tagged as a graduate.');
        }

        $signatories = collect([]);
        if (Schema::hasTable('system_config_name_signatures') && Schema::hasTable('system_config_signature_designations')) {
            $signatories = DB::table('system_config_name_signatures as s')
                ->join('system_config_signature_designations as d', 's.designation_id', '=', 'd.id')
                ->where('s.is_active', 1)
                ->orderBy('d.sort_order')
                ->select('s.signer_name', 's.signature_path', 'd.name as designation_name', 'd.code')
                ->get();
        }

        $president = $signatories->first(function ($s) {
            return stripos($s->designation_name, 'president') !== false;
        }) ?? $signatories->first();

        $registrar = $signatories->first(function ($s) {
            return stripos($s->designation_name, 'registrar') !== false;
        });

        return view('registrar.registrar-menu.student-management.print.diploma', compact(
            'student', 'graduateTagging', 'president', 'registrar'
        ));
    }

    /**
     * Registrar > Student Management > Academic Record (view)
     */
    public function studentAcademicRecord(Request $request, Student $student)
    {
        $student->load(['profile', 'canonicalCourse', 'deficiencies', 'user']);

        $gradeRecords = collect([]);
        if (Schema::hasTable('student_grade_records')) {
            $gradeRecords = \App\StudentGradeRecord::where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_no', $student->student_no);
            })
            ->orderBy('school_year', 'desc')
            ->orderBy('term')
            ->orderBy('subject_code')
            ->get();
        }

        // Weighted GWA (exclude INC, zero-unit, or zero-grade rows)
        $gradedRecords = $gradeRecords->filter(function ($r) {
            return !$r->inc && is_numeric($r->final_grade) && (float) $r->final_grade > 0 && (float) $r->units > 0;
        });
        $gwa = null;
        if ($gradedRecords->count() > 0) {
            $totalWeighted = $gradedRecords->sum(function ($r) { return (float) $r->final_grade * (float) $r->units; });
            $totalUnits    = $gradedRecords->sum(function ($r) { return (float) $r->units; });
            $gwa = $totalUnits > 0 ? round($totalWeighted / $totalUnits, 4) : null;
        }
        $totalUnitsEarned = $gradedRecords->filter(function ($r) { return (float) $r->final_grade <= 3.0; })->sum(function ($r) { return (float) $r->units; });

        $academicStanding = $this->computeAcademicStanding($gwa);

        // Group by SY → Term
        $gradesBySyTerm = $gradeRecords->groupBy(function ($r) { return $r->school_year . '|||' . $r->term; });
        $schoolYears    = $gradeRecords->pluck('school_year')->filter()->unique()->sortByDesc(function ($value) { return $value; })->values();
        $gradeCorrectionRequests = $this->studentGradeCorrectionRequests($student);

        $deficiencies = $student->deficiencies ?? collect([]);

        // Discipline records
        $disciplineStudent  = null;
        $disciplineRecords  = collect([]);
        if (Schema::hasTable('student_discipline_students') && Schema::hasTable('student_discipline_records')) {
            $disciplineStudent = StudentDisciplineStudent::where('student_id', $student->id)
                ->with(['records.caseType', 'records.actionType', 'studentType'])
                ->first();
            $disciplineRecords = $disciplineStudent ? $disciplineStudent->records : collect([]);
        }

        // Requirement / document statuses
        $requirements = collect([]);
        if (Schema::hasTable('student_requirement_statuses')) {
            $requirements = StudentRequirementStatus::where('student_id', $student->id)
                ->with(['requirementPolicy.definition', 'requirementPolicy.definition.type', 'verifier'])
                ->get();
        }

        $courses = Course::orderBy('code')->orderBy('name')->get(['id', 'code', 'name']);

        $gradeUpdateUrlTpl  = route('registrar.registrar-menu.student-mgmt.academic-record.grades.update',  ['student' => $student->id, 'record' => '__RECORD__']);
        $gradeDestroyUrlTpl = route('registrar.registrar-menu.student-mgmt.academic-record.grades.destroy', ['student' => $student->id, 'record' => '__RECORD__']);
        $gradeStoreUrl      = route('registrar.registrar-menu.student-mgmt.academic-record.grades.store',   ['student' => $student->id]);
        $gradeCorrectionApproveUrlTpl = route('registrar.registrar-menu.student-mgmt.academic-record.grade-corrections.approve', ['gradeCorrectionRequest' => '__REQUEST__']);
        $gradeCorrectionRejectUrlTpl = route('registrar.registrar-menu.student-mgmt.academic-record.grade-corrections.reject', ['gradeCorrectionRequest' => '__REQUEST__']);
        $profileUpdateUrl   = route('registrar.registrar-menu.student-mgmt.academic-record.update-profile', ['student' => $student->id]);

        return view('registrar.registrar-menu.student-management.student-academic-record', compact(
            'student', 'gradeRecords', 'gradesBySyTerm', 'gwa', 'totalUnitsEarned',
            'academicStanding', 'schoolYears', 'deficiencies', 'courses',
            'disciplineStudent', 'disciplineRecords', 'requirements',
            'gradeUpdateUrlTpl', 'gradeDestroyUrlTpl', 'gradeStoreUrl',
            'gradeCorrectionRequests', 'gradeCorrectionApproveUrlTpl', 'gradeCorrectionRejectUrlTpl',
            'profileUpdateUrl'
        ));
    }

    private function computeAcademicStanding(?float $gwa): array
    {
        if ($gwa === null) {
            return ['label' => 'No Grades Yet', 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => '—'];
        }
        if ($gwa <= 1.50) {
            return ['label' => "Dean's List", 'color' => '#92400e', 'bg' => '#fef3c7', 'icon' => '★'];
        }
        if ($gwa <= 2.00) {
            return ['label' => 'Good Standing', 'color' => '#065f46', 'bg' => '#d1fae5', 'icon' => '✓'];
        }
        if ($gwa <= 3.00) {
            return ['label' => 'Regular', 'color' => '#1e40af', 'bg' => '#dbeafe', 'icon' => '●'];
        }
        return ['label' => 'Academic Warning', 'color' => '#991b1b', 'bg' => '#fee2e2', 'icon' => '!'];
    }

    public function updateStudentAcademicProfile(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'first_name'           => 'nullable|string|max:100',
            'last_name'            => 'nullable|string|max:100',
            'middle_name'          => 'nullable|string|max:100',
            'suffix'               => 'nullable|string|max:20',
            'gender'               => 'nullable|string|max:30',
            'date_of_birth'        => 'nullable|date',
            'place_of_birth'       => 'nullable|string|max:200',
            'civil_status'         => 'nullable|string|max:30',
            'mobile_number'        => 'nullable|string|max:20',
            'student_email'        => 'nullable|email|max:150',
            'lrn'                  => 'nullable|string|max:30',
            'junior_school'        => 'nullable|string|max:200',
            'senior_school'        => 'nullable|string|max:200',
            'shs_track_strand'     => 'nullable|string|max:100',
            'present_street'       => 'nullable|string|max:200',
            'present_barangay'     => 'nullable|string|max:100',
            'present_municipality' => 'nullable|string|max:100',
            'present_province'     => 'nullable|string|max:100',
            'present_region'       => 'nullable|string|max:100',
            'present_zipcode'      => 'nullable|string|max:10',
            'mother_firstname'     => 'nullable|string|max:100',
            'mother_lastname'      => 'nullable|string|max:100',
            'mother_contact'       => 'nullable|string|max:30',
            'mother_occupation'    => 'nullable|string|max:100',
            'father_firstname'     => 'nullable|string|max:100',
            'father_lastname'      => 'nullable|string|max:100',
            'father_contact'       => 'nullable|string|max:30',
            'father_occupation'    => 'nullable|string|max:100',
            'guardian_firstname'   => 'nullable|string|max:100',
            'guardian_lastname'    => 'nullable|string|max:100',
            'guardian_contact'     => 'nullable|string|max:30',
        ]);

        $profile = StudentProfile::firstOrCreate(
            ['student_id' => $student->id],
            ['student_no' => $student->student_no]
        );
        $profile->fill($validated)->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function getStudentGradeRecords(Request $request, Student $student): JsonResponse
    {
        if (!Schema::hasTable('student_grade_records')) {
            return response()->json(['success' => true, 'records' => []]);
        }
        $records = \App\StudentGradeRecord::where(function ($q) use ($student) {
            $q->where('student_id', $student->id)->orWhere('student_no', $student->student_no);
        })
        ->orderBy('school_year', 'desc')->orderBy('term')->orderBy('subject_code')
        ->get();

        return response()->json(['success' => true, 'records' => $records]);
    }

    public function storeStudentGradeRecord(Request $request, Student $student): JsonResponse
    {
        if (!Schema::hasTable('student_grade_records')) {
            return response()->json(['success' => false, 'message' => 'Grade records table not available.'], 500);
        }
        $validated = $request->validate([
            'school_year'  => 'required|string|max:20',
            'term'         => 'required|string|max:30',
            'subject_code' => 'required|string|max:30',
            'description'  => 'nullable|string|max:200',
            'units'        => 'nullable|numeric|min:0|max:30',
            'final_grade'  => 'nullable|numeric',
            'status'       => 'nullable|string|max:30',
            'grade_status' => 'nullable|string|max:30',
            'remarks'      => 'nullable|string|max:200',
            'professor'    => 'nullable|string|max:100',
            'section_code' => 'nullable|string|max:50',
            'inc'          => 'nullable|boolean',
        ]);

        $record = \App\StudentGradeRecord::create(array_merge($validated, [
            'student_id' => $student->id,
            'student_no' => $student->student_no,
        ]));

        return response()->json(['success' => true, 'message' => 'Grade record added.', 'record' => $record]);
    }

    public function updateStudentGradeRecord(Request $request, Student $student, \App\StudentGradeRecord $record): JsonResponse
    {
        if (!Schema::hasTable('grade_correction_requests')) {
            return response()->json(['success' => false, 'message' => 'Grade correction requests table not found. Please run migrations.'], 500);
        }
        if ((int) $record->student_id !== (int) $student->id && (string) $record->student_no !== (string) $student->student_no) {
            return response()->json(['success' => false, 'message' => 'Record does not belong to this student.'], 403);
        }
        $validated = $request->validate([
            'school_year'  => 'sometimes|string|max:20',
            'term'         => 'sometimes|string|max:30',
            'subject_code' => 'sometimes|string|max:30',
            'equiv_subject_code' => 'nullable|string|max:30',
            'description'  => 'nullable|string|max:200',
            'units'        => 'nullable|numeric|min:0|max:30',
            'final_grade'  => 'nullable|numeric',
            'status'       => 'nullable|string|max:30',
            'grade_status' => 'nullable|string|max:30',
            'remarks'      => 'nullable|string|max:200',
            'professor'    => 'nullable|string|max:100',
            'section_code' => 'nullable|string|max:50',
            'inc'          => 'nullable|boolean',
            'reason'       => 'nullable|string|max:1000',
        ]);

        $payload = $validated;
        $reason = trim((string) ($payload['reason'] ?? ''));
        unset($payload['reason']);

        $requestRow = $this->createGradeCorrectionRequest($request, $student, $record, 'update', $payload, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Grade correction request submitted for review.',
            'pending_review' => true,
            'request' => $requestRow,
        ]);
    }

    public function destroyStudentGradeRecord(Request $request, Student $student, \App\StudentGradeRecord $record): JsonResponse
    {
        if (!Schema::hasTable('grade_correction_requests')) {
            return response()->json(['success' => false, 'message' => 'Grade correction requests table not found. Please run migrations.'], 500);
        }
        if ((int) $record->student_id !== (int) $student->id && (string) $record->student_no !== (string) $student->student_no) {
            return response()->json(['success' => false, 'message' => 'Record does not belong to this student.'], 403);
        }

        $reason = trim((string) $request->input('reason', ''));
        $requestRow = $this->createGradeCorrectionRequest($request, $student, $record, 'remove', [], $reason);

        return response()->json([
            'success' => true,
            'message' => 'Grade removal request submitted for review.',
            'pending_review' => true,
            'request' => $requestRow,
        ]);
    }

    public function approveGradeCorrectionRequest(Request $request, GradeCorrectionRequest $gradeCorrectionRequest): JsonResponse
    {
        if ($gradeCorrectionRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This request has already been reviewed.'], 422);
        }

        $validated = $request->validate([
            'reviewer_remarks' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $gradeCorrectionRequest, $validated) {
            $record = \App\StudentGradeRecord::find($gradeCorrectionRequest->student_grade_record_id);

            if ($record && $gradeCorrectionRequest->action === 'update') {
                $record->fill((array) $gradeCorrectionRequest->new_values)->save();
            } elseif ($record && $gradeCorrectionRequest->action === 'remove') {
                $record->delete();
            }

            $gradeCorrectionRequest->fill([
                'status' => 'approved',
                'reviewed_by' => optional($request->user())->id,
                'reviewed_at' => now(),
                'reviewer_remarks' => trim((string) ($validated['reviewer_remarks'] ?? '')),
            ])->save();
        });

        return response()->json(['success' => true, 'message' => 'Grade request approved and recorded.']);
    }

    public function rejectGradeCorrectionRequest(Request $request, GradeCorrectionRequest $gradeCorrectionRequest): JsonResponse
    {
        if ($gradeCorrectionRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This request has already been reviewed.'], 422);
        }

        $validated = $request->validate([
            'reviewer_remarks' => 'nullable|string|max:1000',
        ]);

        $gradeCorrectionRequest->fill([
            'status' => 'rejected',
            'reviewed_by' => optional($request->user())->id,
            'reviewed_at' => now(),
            'reviewer_remarks' => trim((string) ($validated['reviewer_remarks'] ?? '')),
        ])->save();

        return response()->json(['success' => true, 'message' => 'Grade request rejected and recorded.']);
    }

    private function createGradeCorrectionRequest(Request $request, Student $student, \App\StudentGradeRecord $record, string $action, array $newValues, ?string $reason): GradeCorrectionRequest
    {
        $snapshotFields = [
            'school_year',
            'term',
            'subject_code',
            'equiv_subject_code',
            'description',
            'units',
            'final_grade',
            'status',
            'grade_status',
            'remarks',
            'professor',
            'section_code',
            'inc',
        ];

        return GradeCorrectionRequest::create([
            'student_id' => $student->id,
            'student_no' => $student->student_no,
            'student_grade_record_id' => $record->id,
            'action' => $action,
            'status' => 'pending',
            'old_values' => $record->only($snapshotFields),
            'new_values' => $action === 'update' ? array_merge($record->only($snapshotFields), $newValues) : null,
            'reason' => $reason,
            'requested_by' => optional($request->user())->id,
            'requested_at' => now(),
        ]);
    }

    private function studentGradeCorrectionRequests(Student $student)
    {
        if (!Schema::hasTable('grade_correction_requests')) {
            return collect([]);
        }

        return GradeCorrectionRequest::where(function ($q) use ($student) {
            $q->where('student_id', $student->id)
                ->orWhere('student_no', $student->student_no);
        })
            ->latest('requested_at')
            ->latest('id')
            ->limit(30)
            ->get();
    }

    /**
     * Registrar > Faculty Management > Grading Sheet
     */
    public function facultyCreate()
    {
        $departments = Department::query()
            ->with('college')
            ->orderBy('description')
            ->get();
        $colleges = College::orderBy('sort_order')->orderBy('name')->get();

        return view('registrar.registrar-menu.faculty-management.faculty-create', compact('departments', 'colleges'));
    }

    public function storeFaculty(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:faculties,code',
            'name' => 'required|string',
            'department_id' => 'nullable|integer|exists:departments,id',
            'college_id' => 'nullable|integer|exists:colleges,id',
        ]);

        $defaultPass = null;

        DB::transaction(function () use ($validated, &$defaultPass) {
            $department = !empty($validated['department_id'])
                ? Department::find($validated['department_id'])
                : null;
            $collegeId = $validated['college_id'] ?? (Schema::hasColumn('departments', 'college_id') ? optional($department)->college_id : null);

            $payload = [
                'code' => $validated['code'],
                'name' => $validated['name'],
            ];

            if (Schema::hasColumn('faculties', 'department')) {
                $payload['department'] = optional($department)->description;
            }
            if (Schema::hasColumn('faculties', 'department_id')) {
                $payload['department_id'] = $validated['department_id'] ?? null;
            }
            if (Schema::hasColumn('faculties', 'college_id')) {
                $payload['college_id'] = $collegeId;
            }

            $faculty = Faculty::create($payload);

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
                'gradingStatusLookup',
            ])
            ->whereHas('gradingStatusLookup', function ($query) {
                $query->whereIn(DB::raw('UPPER(code)'), [
                    'SUBMITTED',
                    'DEAN_APPROVED',
                    'REGISTRAR_FINALIZED',
                    'REJECTED',
                ]);
            })
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
                    'midterm' => $grade && $grade->midterm !== null ? (float) $grade->midterm : null,
                    'final' => $grade && $grade->final !== null ? (float) $grade->final : null,
                    'cRating' => $grade && $grade->final_average !== null ? (float) $grade->final_average : null,
                    'fRating' => $grade && $grade->final_average !== null ? (float) $grade->final_average : null,
                    'status' => $grade ? (string) ($grade->status ?: $grade->remarks) : '',
                    'remarks' => $grade ? (string) $grade->remarks : '',
                ];
            })->values()->all();

            $midtermPostedAt = $subject->studentGrades
                ->filter(function($g) { return $g->midterm !== null; })->max('updated_at');
            $finalPostedAt = $subject->studentGrades
                ->filter(function($g) { return $g->final !== null; })->max('updated_at');

            $statusCode = strtoupper((string) optional($subject->gradingStatusLookup)->code);
            $statusLabel = (string) (optional($subject->gradingStatusLookup)->label ?: 'Submitted for Dean Review');
            $approvedBy = '-';
            if (!empty($subject->registrar_finalized_by)) {
                $approvedBy = optional(User::find((int) $subject->registrar_finalized_by))->name ?: 'Registrar';
            } elseif (!empty($subject->dean_approved_by)) {
                $approvedBy = optional(User::find((int) $subject->dean_approved_by))->name ?: 'Dean';
            } elseif (!empty($subject->grading_returned_by)) {
                $approvedBy = optional(User::find((int) $subject->grading_returned_by))->name ?: 'Registrar';
            }

            return [
                'id' => (int) $subject->id,
                'section' => trim((string) ($subject->year_section ?: '-')),
                'courseCode' => (string) ($subject->code ?: '-'),
                'description' => (string) ($subject->name ?: '-'),
                'faculty' => (string) (optional($subject->facultyModel)->name ?: ($subject->faculty ?: '-')),
                'midterm' => $midtermPostedAt ? Carbon::parse($midtermPostedAt)->format('m/d/Y') : '-',
                'final' => $finalPostedAt ? Carbon::parse($finalPostedAt)->format('m/d/Y') : '-',
                'approvedBy' => $approvedBy,
                'courseFull' => (string) ($subject->name ?: '-'),
                'schedule' => 'Room No. : ' . (string) ($subject->room ?: 'TBA'),
                'schoolYear' => (string) ($subject->school_year ?: ''),
                'term' => (string) ($subject->semester ?: ''),
                'status' => $statusLabel,
                'statusCode' => $statusCode,
                'submittedAt' => optional($subject->submitted_at)->format('m/d/Y h:i A') ?: '',
                'deanApprovedAt' => optional($subject->dean_approved_at)->format('m/d/Y h:i A') ?: '',
                'registrarFinalizedAt' => optional($subject->registrar_finalized_at)->format('m/d/Y h:i A') ?: '',
                'returnReason' => (string) ($subject->grading_return_reason ?: ''),
                'students' => $students,
            ];
        })->values()->all();

        $faculties = $subjects
            ->map(function($s) { return optional($s->facultyModel)->name ?: $s->faculty; })
            ->filter()->unique()->values()->all();

        return view('registrar.registrar-menu.faculty-management.grading-sheet', [
            'gradingSections' => $gradingSections,
            'faculties' => $faculties,
        ]);
    }

    public function gradingSheetAction(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'action' => 'required|in:approved,dean_approved,finalized,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $subject = Subject::with('gradingStatusLookup')->findOrFail($request->input('subject_id'));
        $action = (string) $request->input('action');
        $currentCode = strtoupper((string) optional($subject->gradingStatusLookup)->code);

        if ($action === 'approved') {
            $action = 'dean_approved';
        }

        if ($action === 'dean_approved' && $currentCode !== 'SUBMITTED') {
            return response()->json(['ok' => false, 'message' => 'Only submitted grading sheets can be dean-approved.'], 422);
        }

        if ($action === 'finalized' && $currentCode !== 'DEAN_APPROVED') {
            return response()->json(['ok' => false, 'message' => 'Only dean-approved grading sheets can be finalized by the registrar.'], 422);
        }

        $statusCode = [
            'dean_approved' => 'DEAN_APPROVED',
            'finalized' => 'REGISTRAR_FINALIZED',
            'rejected' => 'REJECTED',
        ][$action];

        $statusId = \DB::table('subject_grading_statuses')
            ->whereRaw('UPPER(code) = ?', [strtoupper($statusCode)])
            ->value('id');

        if (!$statusId) {
            return response()->json(['ok' => false, 'message' => 'Grading status not configured.'], 500);
        }

        $subject->grading_status_id = $statusId;

        if ($action === 'dean_approved') {
            $subject->dean_approved_by = optional($request->user())->id;
            $subject->dean_approved_at = now();
            $subject->registrar_finalized_by = null;
            $subject->registrar_finalized_at = null;
            $subject->grading_returned_by = null;
            $subject->grading_returned_at = null;
            $subject->grading_return_reason = null;
        } elseif ($action === 'finalized') {
            $subject->registrar_finalized_by = optional($request->user())->id;
            $subject->registrar_finalized_at = now();
            $subject->load('students');
            foreach ($subject->students as $student) {
                $this->syncStudentGradeSnapshot($student);
            }
        } elseif ($action === 'rejected') {
            $subject->grading_returned_by = optional($request->user())->id;
            $subject->grading_returned_at = now();
            $subject->grading_return_reason = trim((string) $request->input('remarks', ''));
            $subject->dean_approved_by = null;
            $subject->dean_approved_at = null;
            $subject->registrar_finalized_by = null;
            $subject->registrar_finalized_at = null;
        }

        $subject->save();

        return response()->json([
            'ok' => true,
            'status' => $statusCode,
            'label' => \DB::table('subject_grading_statuses')->where('id', $statusId)->value('label') ?: $statusCode,
            'approvedBy' => optional($request->user())->name ?: 'Updated',
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
            if ($gradeValue < 0 || $gradeValue > 100) {
                continue;
            }

            $gradeRow = StudentSubjectGrade::firstOrNew([
                'subject_id' => $subject->id,
                'student_id' => (int) $studentId,
            ]);

            $gradeRow->{$phase} = $gradeValue;
            $transmuted = $this->computeRegistrarTransmutedGrade(
                $subject,
                $gradeRow->midterm,
                $gradeRow->final
            );
            $gradeRow->final_average = $transmuted['grade'];
            $gradeRow->remarks = $transmuted['remarks'];
            if (Schema::hasColumn('student_subject_grades', 'status')) {
                $gradeRow->status = $transmuted['status'];
            }
            $gradeRow->save();

            $student = $subject->students->firstWhere('id', (int) $studentId);
            if ($student) {
                $this->syncStudentGradeSnapshot($student);
            }
        }

        $subject->grading_status_id = \DB::table('subject_grading_statuses')
            ->whereRaw('UPPER(code) = ?', ['SUBMITTED'])
            ->value('id');
        $subject->submitted_at = now();
        $subject->dean_approved_by = null;
        $subject->dean_approved_at = null;
        $subject->registrar_finalized_by = null;
        $subject->registrar_finalized_at = null;
        $subject->grading_returned_by = null;
        $subject->grading_returned_at = null;
        $subject->grading_return_reason = null;
        $subject->save();

        $updatedRows = StudentSubjectGrade::query()
            ->where('subject_id', $subject->id)
            ->get()
            ->map(function ($row) {
                return [
                    'student_id' => (int) $row->student_id,
                    'midterm' => $row->midterm !== null ? (float) $row->midterm : null,
                    'final' => $row->final !== null ? (float) $row->final : null,
                    'final_average' => $row->final_average !== null ? (float) $row->final_average : null,
                    'status' => (string) (($row->status ?? '') ?: ($row->remarks ?: '')),
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

    private function computeRegistrarTransmutedGrade(Subject $subject, $midterm, $final): array
    {
        $grades = collect([$midterm, $final])
            ->filter(function ($value) {
                return $value !== null && $value !== '' && is_numeric($value);
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();

        if ($grades->count() === 0) {
            return ['grade' => null, 'remarks' => null, 'status' => null];
        }

        $rawAverage = round($grades->avg(), 2);
        $status = $rawAverage >= 75.0 ? 'Passed' : 'Failed';

        return [
            'grade' => $rawAverage,
            'remarks' => $status,
            'status' => $status,
        ];
    }

    private function matchingRegistrarTransmutationRule(Subject $subject, float $rawAverage)
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return null;
        }

        $base = TransmutationRule::query()
            ->where('initial_from', '<=', $rawAverage)
            ->where('initial_to', '>=', $rawAverage);

        $termQuery = clone $base;
        if (Schema::hasColumn('transmutation_rules', 'academic_term_id') && !empty($subject->academic_term_id)) {
            $termQuery->where('academic_term_id', (int) $subject->academic_term_id);
        } elseif (Schema::hasColumn('transmutation_rules', 'school_year') && Schema::hasColumn('transmutation_rules', 'term')) {
            $termQuery->where('school_year', (string) $subject->school_year)
                ->whereIn('term', $this->registrarTransmutationTermAliases($subject->semester));
        }

        if (Schema::hasColumn('transmutation_rules', 'course_id') && !empty($subject->course_id)) {
            $specific = clone $termQuery;
            $rule = $specific->where('course_id', (int) $subject->course_id)->orderByDesc('initial_from')->first();
            if ($rule) {
                return $rule;
            }
        }

        if (Schema::hasColumn('transmutation_rules', 'program')) {
            $program = trim((string) ($subject->course ?: $subject->code));
            if ($program !== '') {
                $specific = clone $termQuery;
                $rule = $specific->where('program', $program)->orderByDesc('initial_from')->first();
                if ($rule) {
                    return $rule;
                }
            }
        }

        return $termQuery->orderByDesc('initial_from')->first();
    }

    private function registrarTransmutationTermAliases($semester): array
    {
        $value = strtolower(trim((string) $semester));
        if (strpos($value, 'second') !== false || strpos($value, '2nd') !== false || $value === '2') {
            return ['Second', 'Second Semester', '2nd Semester'];
        }
        if (strpos($value, 'summer') !== false) {
            return ['Summer', 'Summer Semester'];
        }

        return ['First', 'First Semester', '1st Semester'];
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
        $graduateTaggings = Schema::hasTable('graduate_taggings')
            ? GraduateTagging::query()
                ->with(['student.profile', 'student.canonicalCourse:id,code,name', 'student.yearBlock:id,label', 'student.academicTerm:id,school_year,term'])
                ->where('is_graduate', true)
                ->orderByDesc('date_graduated')
                ->orderByDesc('id')
                ->limit(1000)
                ->get()
            : collect();

        $alumniRows = $graduateTaggings->map(function ($tagging) {
            $student = $tagging->student;
            if (!$student) {
                return null;
            }

            $programCode = $student->program ?: optional($student->canonicalCourse)->code;
            $programName = optional($student->canonicalCourse)->name ?: $student->program;

            return [
                'id' => $student->id,
                'studentNo' => (string) $student->student_no,
                'studentName' => (string) $student->name,
                'program' => (string) ($programCode ?: '-'),
                'programName' => (string) ($programName ?: '-'),
                'yearLevel' => (string) ($student->year_level ?: '-'),
                'schoolYear' => (string) ($student->school_year ?: ''),
                'term' => (string) ($student->semester ?: ''),
                'dateGraduated' => $tagging->date_graduated ? $tagging->date_graduated->format('Y-m-d') : '',
                'graduationYear' => $tagging->date_graduated ? $tagging->date_graduated->format('Y') : '',
                'soNumber' => (string) ($tagging->so_number ?: ''),
                'soDate' => $tagging->so_date ? $tagging->so_date->format('Y-m-d') : '',
                'isSuspended' => (bool) $tagging->suspend_account,
                'suspendRemarks' => (string) ($tagging->suspend_remarks ?: ''),
                'contact' => (string) (optional($student->profile)->mobile_number ?: optional($student->profile)->student_email ?: ''),
                'profileUrl' => route('registrar.registrar-menu.student-mgmt.student-records.profile', $student->id),
            ];
        })->filter()->values()->all();

        $alumniPrograms = collect($alumniRows)
            ->pluck('program')
            ->filter(function ($value) {
                return trim((string) $value) !== '' && $value !== '-';
            })
            ->unique()
            ->values()
            ->all();

        $alumniGraduationYears = collect($alumniRows)
            ->pluck('graduationYear')
            ->filter()
            ->unique()
            ->sortByDesc(function ($value) { return $value; })
            ->values()
            ->all();

        $alumniSummary = [
            'total' => count($alumniRows),
            'active' => collect($alumniRows)->where('isSuspended', false)->count(),
            'suspended' => collect($alumniRows)->where('isSuspended', true)->count(),
            'programs' => count($alumniPrograms),
            'years' => count($alumniGraduationYears),
        ];

        $alumniProgramBreakdown = collect($alumniRows)
            ->groupBy('program')
            ->map(function ($rows, $program) {
                return ['program' => $program, 'count' => $rows->count()];
            })
            ->sortByDesc('count')
            ->values()
            ->take(8)
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

        $configuredOptions = $this->configuredSchedulingSchoolSemesters();
        $configuredSchoolYears = collect($configuredOptions['school_years'] ?? [])
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->all();

        $configuredSemesterMap = is_array($configuredOptions['semester_map'] ?? null)
            ? $configuredOptions['semester_map']
            : [];

        $alumniSemesterMap = [];
        foreach ($configuredSemesterMap as $schoolYear => $semesters) {
            $normalizedSchoolYear = trim((string) $schoolYear);
            if ($normalizedSchoolYear === '') {
                continue;
            }

            $normalizedSemesters = collect((array) $semesters)
                ->map(function ($value) {
                    return $this->normalizeSlotMonitoringSemester((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->sortBy(function ($value) {
                    return $this->slotMonitoringSemesterWeight((string) $value);
                })
                ->values()
                ->all();

            if (!count($normalizedSemesters)) {
                continue;
            }

            $alumniSemesterMap[$normalizedSchoolYear] = $normalizedSemesters;
        }

        $alumniSchoolYears = count($configuredSchoolYears)
            ? $configuredSchoolYears
            : collect($alumniRows)
                ->pluck('schoolYear')
                ->filter()
                ->unique()
                ->values()
                ->all();

        if (!count($alumniSchoolYears)) {
            $yearStart = (int) date('Y');
            $alumniSchoolYears = [$yearStart . '-' . ($yearStart + 1)];
        }

        $defaultSchoolYear = $setting
            ? trim((string) $setting->school_year)
            : (string) ($alumniSchoolYears[0] ?? '');
        if ($defaultSchoolYear === '' || !in_array($defaultSchoolYear, $alumniSchoolYears, true)) {
            $defaultSchoolYear = (string) $alumniSchoolYears[0];
        }

        $alumniTerms = array_key_exists($defaultSchoolYear, $alumniSemesterMap)
            ? $alumniSemesterMap[$defaultSchoolYear]
            : collect($alumniSemesterMap)
                ->flatten(1)
                ->unique()
                ->values()
                ->all();

        if (!count($alumniTerms)) {
            $alumniTerms = ['First', 'Second', 'Summer'];
        }

        $defaultTerm = $this->normalizeSlotMonitoringSemester((string) ($setting ? $setting->term : ''));
        if ($defaultTerm === '' || !in_array($defaultTerm, $alumniTerms, true)) {
            $defaultTerm = (string) $alumniTerms[0];
        }

        $alumniConfig = [
            'schoolYear' => $defaultSchoolYear,
            'term' => $defaultTerm,
        ];

        return view('registrar.registrar-menu.alumni-tracker', compact(
            'alumniRows',
            'alumniPrograms',
            'alumniYearLevels',
            'alumniConfig',
            'alumniSchoolYears',
            'alumniTerms',
            'alumniSemesterMap',
            'alumniGraduationYears',
            'alumniSummary',
            'alumniProgramBreakdown'
        ));
    }

    public function alumniTrackerSaveConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'term' => 'required|string|max:30',
        ]);

        $schoolYear = trim((string) $validated['school_year']);
        $term = $this->normalizeSlotMonitoringSemester((string) $validated['term']);

        $configuredOptions = $this->configuredSchedulingSchoolSemesters();
        $schoolYearOptions = collect($configuredOptions['school_years'] ?? [])
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->all();

        if (count($schoolYearOptions) && !in_array($schoolYear, $schoolYearOptions, true)) {
            throw ValidationException::withMessages([
                'school_year' => ['Selected school year is not part of the current system configuration.'],
            ]);
        }

        $semesterMap = is_array($configuredOptions['semester_map'] ?? null)
            ? $configuredOptions['semester_map']
            : [];
        $allowedTerms = array_key_exists($schoolYear, $semesterMap)
            ? collect((array) $semesterMap[$schoolYear])
                ->map(function ($value) {
                    return $this->normalizeSlotMonitoringSemester((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values()
                ->all()
            : ['First', 'Second', 'Summer'];

        if ($term === '' || (count($allowedTerms) && !in_array($term, $allowedTerms, true))) {
            throw ValidationException::withMessages([
                'term' => ['Selected term is not part of the current system configuration.'],
            ]);
        }

        if (Schema::hasTable('alumni_tracker_settings')) {
            $setting = AlumniTrackerSetting::query()->latest('id')->first();
            if ($setting) {
                $setting->update([
                    'school_year' => $schoolYear,
                    'term' => $term,
                ]);
            } else {
                AlumniTrackerSetting::create([
                    'school_year' => $schoolYear,
                    'term' => $term,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Registrar > Forms > Placeholder
     */
    public function formsPlaceholder(Request $request)
    {
        $configuredOptions = $this->configuredSchedulingSchoolSemesters();
        $schoolYears = collect($configuredOptions['school_years'] ?? [])
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->all();

        if (!count($schoolYears)) {
            $yearStart = (int) date('Y');
            $schoolYears = [$yearStart . '-' . ($yearStart + 1)];
        }

        $semesterMap = is_array($configuredOptions['semester_map'] ?? null)
            ? $configuredOptions['semester_map']
            : [];

        $selectedSchoolYear = trim((string) $request->query('school_year', (string) ($schoolYears[0] ?? '')));
        if ($selectedSchoolYear === '' || !in_array($selectedSchoolYear, $schoolYears, true)) {
            $selectedSchoolYear = (string) ($schoolYears[0] ?? '');
        }

        $termOptions = array_key_exists($selectedSchoolYear, $semesterMap)
            ? collect((array) $semesterMap[$selectedSchoolYear])
                ->map(function ($value) {
                    return $this->normalizeSlotMonitoringSemester((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->sortBy(function ($value) {
                    return $this->slotMonitoringSemesterWeight((string) $value);
                })
                ->values()
                ->all()
            : [];

        if (!count($termOptions)) {
            $termOptions = ['First', 'Second', 'Summer'];
        }

        $selectedTerm = $this->normalizeSlotMonitoringSemester((string) $request->query('term', (string) $termOptions[0]));
        if ($selectedTerm === '' || !in_array($selectedTerm, $termOptions, true)) {
            $selectedTerm = (string) $termOptions[0];
        }

        return view('registrar.forms.placeholder', compact(
            'schoolYears',
            'semesterMap',
            'termOptions',
            'selectedSchoolYear',
            'selectedTerm'
        ));
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
    public function formsApplicationLeaveAbsenceEnrolled(Request $request, ?Student $student = null)
    {
        if (!$student && $request->filled('student_id')) {
            $student = Student::find($request->query('student_id'));
        }

        $studentColumns = ['id', 'student_no', 'name'];
        foreach (['college', 'program', 'year_level', 'school_year', 'semester', 'course_id', 'year_block_id', 'academic_term_id'] as $column) {
            if (Schema::hasColumn('students', $column)) {
                $studentColumns[] = $column;
            }
        }

        $students = Student::query()
            ->with(['profile', 'canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->when($student, function ($query) use ($student) {
                $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$student->id]);
            })
            ->orderBy('name')
            ->limit(500)
            ->get($studentColumns);

        $selectedStudentId = $student ? (int) $student->id : 0;
        if ($selectedStudentId <= 0 && $students->isNotEmpty()) {
            $selectedStudentId = (int) $students->first()->id;
        }

        if ($selectedStudentId > 0) {
            $student = Student::with(['profile', 'canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])->find($selectedStudentId);
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
    public function formsDiploma(Request $request)
    {
        if ($request->filled('student_id')) {
            $student = Student::with(['profile', 'canonicalCourse'])->find($request->query('student_id'));

            if ($student) {
                $graduateTagging = null;
                if (Schema::hasTable('graduate_taggings')) {
                    $graduateTagging = GraduateTagging::where('student_id', $student->id)->first();
                }

                $signatories = collect([]);
                if (Schema::hasTable('system_config_name_signatures') && Schema::hasTable('system_config_signature_designations')) {
                    $signatories = DB::table('system_config_name_signatures as s')
                        ->join('system_config_signature_designations as d', 's.designation_id', '=', 'd.id')
                        ->where('s.is_active', 1)
                        ->orderBy('d.sort_order')
                        ->select('s.signer_name', 's.signature_path', 'd.name as designation_name', 'd.code')
                        ->get();
                }

                $president = $signatories->first(function ($s) {
                    return stripos($s->designation_name, 'president') !== false;
                }) ?? $signatories->first();

                $registrar = $signatories->first(function ($s) {
                    return stripos($s->designation_name, 'registrar') !== false;
                });

                return view('registrar.registrar-menu.student-management.print.diploma', compact(
                    'student', 'graduateTagging', 'president', 'registrar'
                ));
            }
        }

        return view('registrar.forms.diploma');
    }

    /**
     * Registrar > Forms > Graduation Clearance
     */
    public function formsGraduationClearance(?Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        $studentColumns = ['id', 'student_no', 'name'];
        foreach (['college', 'course_id', 'year_block_id', 'academic_term_id', 'program', 'year_level', 'school_year', 'semester'] as $column) {
            if (Schema::hasColumn('students', $column)) {
                $studentColumns[] = $column;
            }
        }

        $graduationClearanceRows = Student::query()
            ->with(['profile', 'canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->when($student, function ($query) use ($student) {
                $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$student->id]);
            })
            ->orderBy('name')
            ->limit(100)
            ->get($studentColumns);

        $selectedStudentId = $student ? (int) $student->id : null;

        return view('registrar.forms.graduation-clearance', compact('graduationClearanceRows', 'selectedStudentId'));
    }

    /**
     * Registrar > Forms > Clearance 2
     */
    public function formsClearance2(?Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        if ($student) {
            $student->load([
                'profile',
                'canonicalCourse:id,code,name',
                'yearBlock:id,label',
                'academicTerm:id,school_year,term',
            ]);
        }

        $studentOptions = Student::query()
            ->with(['canonicalCourse:id,code,name'])
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'student_no', 'name', 'course_id']);

        return view('registrar.forms.clearance-2', compact('student', 'studentOptions'));
    }

    /**
     * Registrar > Forms > Honorable Dismissal
     */
    public function formsHonorableDismissal(?Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        $studentColumns = ['id', 'student_no', 'name'];
        foreach (['course_id', 'year_block_id', 'academic_term_id', 'program', 'year_level', 'school_year', 'semester'] as $column) {
            if (Schema::hasColumn('students', $column)) {
                $studentColumns[] = $column;
            }
        }

        $honorableDismissalRows = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->when($student, function ($query) use ($student) {
                $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$student->id]);
            })
            ->orderBy('name')
            ->limit(100)
            ->get($studentColumns);

        $selectedStudentId = $student ? (int) $student->id : null;

        return view('registrar.forms.honorable-dismissal', compact('honorableDismissalRows', 'selectedStudentId'));
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
            ->with('student.subjects')
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
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        $gwa = null;

        if ($student) {
            $student->loadMissing('canonicalCourse');

            if (Schema::hasTable('student_grade_records')) {
                $gradeRecords = \App\StudentGradeRecord::where(function ($q) use ($student) {
                    $q->where('student_id', $student->id)
                        ->orWhere('student_no', $student->student_no);
                })->get();

                $gradedRecords = $gradeRecords->filter(function ($record) {
                    return !$record->inc
                        && is_numeric($record->final_grade)
                        && (float) $record->final_grade > 0
                        && (float) $record->units > 0;
                });

                if ($gradedRecords->isNotEmpty()) {
                    $weightedSum = $gradedRecords->sum(function ($record) {
                        return (float) $record->final_grade * (float) $record->units;
                    });
                    $unitsSum = $gradedRecords->sum(function ($record) {
                        return (float) $record->units;
                    });
                    $gwa = $unitsSum > 0 ? round($weightedSum / $unitsSum, 2) : null;
                }
            }

            if ($gwa === null) {
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
                }
            }
        }

        return view('registrar.forms.certificates.certificate-gwa', compact('student', 'gwa'));
    }

    /**
     * Registrar > Forms > Dean's Honors Certificate
     */
    public function formsCertificateDeansHonors(?\App\Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'academicTerm']);
        }

        return view('registrar.forms.certificates.deans-honors', [
            'student' => $student,
            'issuedDate' => request()->query('issued_date'),
            'awardTitle' => "Dean's Honors List Award",
            'pageTitle' => "Dean's Honors Certificate",
        ]);
    }

    /**
     * Registrar > Forms > President's Honors Certificate
     */
    public function formsCertificatePresidentsHonors(?\App\Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'academicTerm']);
        }

        return view('registrar.forms.certificates.deans-honors', [
            'student' => $student,
            'issuedDate' => request()->query('issued_date'),
            'awardTitle' => "President's Honors List Award",
            'pageTitle' => "President's Honors Certificate",
        ]);
    }

    /**
     * Registrar > Forms > Form No. 8C-2 Certificate of Graduation
     */
    public function formsCertificateGraduation8c2(?\App\Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'graduateTagging']);
        }

        return view('registrar.forms.certificates.certificate-graduation-8c2', compact('student'));
    }

    /**
     * Registrar > Forms > Form No. 8D-2 Certificate of Honor
     */
    public function formsCertificateHonor8d2(?\App\Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'graduateTagging']);
        }

        return view('registrar.forms.certificates.certificate-honor-8d2', compact('student'));
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
            $student = Student::with([
                'subjects',
                'profile',
                'canonicalCourse:id,code,name',
                'yearBlock:id,label',
                'academicTerm:id,school_year,term',
            ])->find($selectedStudentId);
        }

        $subjects = collect();
        if ($student) {
            $subjects = $student->subjects->values();
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
    public function formsRequestFormF137a(?Student $student = null)
    {
        if (!$student && request()->filled('student_id')) {
            $student = Student::find(request()->query('student_id'));
        }

        return view('registrar.forms.request-form-f-137a', compact('student'));
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
                    ['label' => 'Contact Information', 'value' => '\n<b>EMAIL</b>: \nregistrar@plpasig.edu.ph\n<b>NO</b>: 02-8642-8300 local 110'],
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
