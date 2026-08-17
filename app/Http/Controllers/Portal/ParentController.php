<?php

namespace App\Http\Controllers\Portal;

use App\AcademicCalendarEvent;
use App\AcademicCalendarAudienceType;
use App\Http\Controllers\Concerns\PortalNotifications;
use App\Http\Controllers\Controller;
use App\ParentContactRequest;
use App\ParentContactRequestChannel;
use App\ParentContactRequestStatus;
use App\ParentContactRequestTopic;
use App\ParentStudentLink;
use App\Student;
use App\StudentDeficiency;
use App\StudentGradeRecord;
use App\StudentProfile;
use App\StudentSubjectGrade;
use App\SystemAnnouncement;
use App\SystemReportDetailSetting;
use App\Support\HelpCenterTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    use PortalNotifications;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            $this->syncPortalNotificationsForUser($user);

            view()->share('parentNotifications', $this->portalNotificationPayloads($user));
            view()->share('parentUnreadNotificationCount', $this->portalUnreadNotificationCount($user));

            return $next($request);
        });
    }

    public function showCreateAccount()
    {
        return redirect()->route('module.login', [
            'module' => 'parent',
            'open_create_account' => 1,
        ]);
    }

    protected function portalNotificationModule(): string
    {
        return 'parent';
    }

    protected function portalNotificationAudience(): string
    {
        return SystemAnnouncement::AUDIENCE_STUDENTS;
    }

    protected function portalNotificationRoutePrefix(): string
    {
        return 'parent';
    }

    protected function portalNotificationFallbackTitle(): string
    {
        return 'New Parent Notification';
    }

    protected function portalNotificationFallbackMessage(): string
    {
        return 'A student announcement is available';
    }

    public function notificationsFeed(Request $request)
    {
        return $this->portalNotificationsFeed($request);
    }

    public function markNotificationsRead(Request $request)
    {
        return $this->markPortalNotificationsRead($request);
    }

    public function dismissNotification(Request $request, $notificationDelivery)
    {
        return $this->dismissPortalNotification($request, $notificationDelivery);
    }

    private function currentStudent()
    {
        $user = Auth::user();

        if ($user && $user->parent_id && Schema::hasTable('parent_student_links')) {
            $primaryLink = ParentStudentLink::query()
                ->where('parent_id', $user->parent_id)
                ->whereHas('student')
                ->orderByDesc('is_primary_contact')
                ->orderBy('id')
                ->first();

            if ($primaryLink && $primaryLink->student_id) {
                return Student::with(['subjects', 'canonicalCourse', 'yearBlock'])
                    ->find($primaryLink->student_id);
            }
        }

        if ($user && $user->student_id) {
            return Student::with(['subjects', 'canonicalCourse', 'yearBlock'])->find($user->student_id);
        }

        if ($user && !empty($user->username)) {
            return Student::with(['subjects', 'canonicalCourse', 'yearBlock'])
                ->where('student_no', $user->username)
                ->first();
        }

        return null;
    }

    private function linkedChildren($student)
    {
        $user = Auth::user();
        $children = collect();

        if ($user && $user->parent_id && Schema::hasTable('parent_student_links')) {
            $links = ParentStudentLink::query()
                ->where('parent_id', $user->parent_id)
                ->with(['student.canonicalCourse:id,code,name', 'student.yearBlock:id,label'])
                ->whereHas('student')
                ->orderByDesc('is_primary_contact')
                ->orderBy('id')
                ->get();

            $children = $links->map(function ($link) {
                $linkedStudent = $link->student;
                if (!$linkedStudent) {
                    return null;
                }

                $programCode = trim((string) $linkedStudent->program);
                $programName = trim((string) optional($linkedStudent->canonicalCourse)->name);
                $yearLevel = trim((string) $linkedStudent->year_level);

                return [
                    'id' => $linkedStudent->id,
                    'student_no' => $linkedStudent->student_no,
                    'name' => $linkedStudent->name,
                    'course' => $programCode !== '' ? $programCode : ($programName !== '' ? $programName : 'N/A'),
                    'year_level' => $yearLevel !== '' ? $yearLevel : 'N/A',
                    'birthdate' => null,
                    'status' => $link->is_primary_contact ? 'Primary Contact' : 'Linked',
                ];
            })->filter()->values();
        }

        if ($children->isEmpty() && $student) {
            $programCode = trim((string) $student->program);
            $programName = trim((string) optional($student->canonicalCourse)->name);
            $yearLevel = trim((string) $student->year_level);

            $children = collect([[
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'course' => $programCode !== '' ? $programCode : ($programName !== '' ? $programName : 'N/A'),
                'year_level' => $yearLevel !== '' ? $yearLevel : 'N/A',
                'birthdate' => null,
                'status' => 'Linked',
            ]]);
        }

        return $children->values();
    }

    private function splitNameParts($fullName)
    {
        $name = trim((string) $fullName);
        if ($name === '') {
            return [
                'first' => '',
                'middle' => '',
                'last' => '',
            ];
        }

        $parts = preg_split('/\s+/', $name);
        $count = count($parts);

        if ($count === 1) {
            return ['first' => $parts[0], 'middle' => '', 'last' => ''];
        }

        if ($count === 2) {
            return ['first' => $parts[0], 'middle' => '', 'last' => $parts[1]];
        }

        $first = array_shift($parts);
        $last = array_pop($parts);

        return [
            'first' => $first,
            'middle' => implode(' ', $parts),
            'last' => $last,
        ];
    }

    private function resolveSelectedChild($children, $selectedChildId)
    {
        if (!$children || $children->isEmpty()) {
            return null;
        }

        if ($selectedChildId === null || $selectedChildId === '') {
            return $children->first();
        }

        $matched = $children->first(function ($child) use ($selectedChildId) {
            return (string) ($child['id'] ?? '') === (string) $selectedChildId;
        });

        return $matched ?: $children->first();
    }

    private function mapStudentGradeRecords(Student $student)
    {
        if (!Schema::hasTable('student_grade_records')) {
            return collect();
        }

        $studentNo = trim((string) $student->student_no);

        return StudentGradeRecord::query()
            ->where(function ($query) use ($student, $studentNo) {
                $query->where('student_id', $student->id);

                if ($studentNo !== '') {
                    $query->orWhere('student_no', $studentNo);
                }
            })
            ->orderByDesc('school_year')
            ->orderByRaw("CASE WHEN LOWER(term) LIKE '%first%' THEN 1 WHEN LOWER(term) LIKE '%second%' THEN 2 WHEN LOWER(term) LIKE '%summer%' THEN 3 ELSE 4 END")
            ->orderBy('subject_code')
            ->get()
            ->map(function ($record) {
                $row = new \stdClass();
                $row->midterm = null;
                $row->final = null;
                $row->final_average = $record->final_grade;
                $row->remarks = trim((string) ($record->remarks ?: $record->grade_status ?: $record->status));

                $subject = new \stdClass();
                $subject->code = $record->subject_code;
                $subject->name = $record->description;
                $subject->faculty_name = $record->professor;
                $subject->units = $record->units;
                $subject->section = $record->section_code;
                $subject->school_year = $record->school_year;
                $subject->semester = $record->term;

                $row->subject = $subject;

                return $row;
            })
            ->values();
    }

    private function gradeRowSemesterKey($row)
    {
        $subject = isset($row->subject) ? $row->subject : null;

        $schoolYear = trim((string) data_get($subject, 'school_year'));
        $semester = trim((string) data_get($subject, 'semester'));

        if ($semester === '') {
            $semester = trim((string) data_get($subject, 'term'));
        }

        if ($schoolYear === '' && isset($row->school_year)) {
            $schoolYear = trim((string) $row->school_year);
        }

        if ($semester === '' && isset($row->term)) {
            $semester = trim((string) $row->term);
        }

        if ($schoolYear === '' && $semester === '') {
            return '';
        }

        return $schoolYear . '|' . $semester;
    }

    private function schoolYearSortValue($schoolYear)
    {
        if (preg_match('/\d{4}/', (string) $schoolYear, $matches)) {
            return (int) $matches[0];
        }

        return 0;
    }

    private function termSortOrder($term)
    {
        $normalized = strtolower(trim((string) $term));

        if ($normalized === '') {
            return 99;
        }

        if (strpos($normalized, 'first') !== false || preg_match('/(^|[^0-9])1(st)?([^0-9]|$)/', $normalized)) {
            return 1;
        }

        if (strpos($normalized, 'second') !== false || preg_match('/(^|[^0-9])2(nd)?([^0-9]|$)/', $normalized)) {
            return 2;
        }

        if (strpos($normalized, 'summer') !== false) {
            return 3;
        }

        return 4;
    }

    public function grades(Request $request)
    {
        $currentStudent = $this->currentStudent();
        $children = $this->linkedChildren($currentStudent);
        $selectedChildId = $request->query('child');
        $selectedChild = $this->resolveSelectedChild($children, $selectedChildId);

        if ($selectedChild && isset($selectedChild['id'])) {
            $selectedChildId = (string) $selectedChild['id'];
        } else {
            $selectedChildId = null;
        }

        $student = null;
        if ($selectedChild && !empty($selectedChild['id'])) {
            $student = Student::with(['subjects', 'canonicalCourse', 'yearBlock'])->find($selectedChild['id']);
        }

        if (!$student && $currentStudent && (!$selectedChild || (string) ($selectedChild['id'] ?? '') === (string) $currentStudent->id)) {
            $student = $currentStudent;
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'yearBlock']);
        }

        $gradeRows = collect();
        if ($student) {
            $gradeRows = StudentSubjectGrade::query()
                ->with(['subject.academicTerm', 'subject.facultyModel:id,name'])
                ->where('student_id', $student->id)
                ->get();

            if ($gradeRows->isEmpty()) {
                $gradeRows = $this->mapStudentGradeRecords($student);
            }
        }

        $deficiencyItems = collect();
        if ($student) {
            $deficiencyItems = StudentDeficiency::query()
                ->where('student_id', $student->id)
                ->where(function ($query) {
                    $query->whereNull('is_completed')
                        ->orWhere('is_completed', false);
                })
                ->orderByDesc('id')
                ->get()
                ->map(function ($item) {
                    $department = trim((string) $item->department);
                    $remarks = trim((string) $item->remarks);

                    if ($department !== '' && $remarks !== '') {
                        return $department . ' - ' . $remarks;
                    }

                    return $remarks !== '' ? $remarks : ($department !== '' ? $department : 'Unsettled deficiency');
                });
        }

        $hasDeficiencies = $deficiencyItems->isNotEmpty();

        $semesterOptions = $gradeRows
            ->map(function ($row) {
                return $this->gradeRowSemesterKey($row);
            })
            ->filter()
            ->unique()
            ->sort(function ($left, $right) {
                list($leftSchoolYear, $leftTerm) = array_pad(explode('|', (string) $left), 2, '');
                list($rightSchoolYear, $rightTerm) = array_pad(explode('|', (string) $right), 2, '');

                $yearCompare = $this->schoolYearSortValue($rightSchoolYear) <=> $this->schoolYearSortValue($leftSchoolYear);
                if ($yearCompare !== 0) {
                    return $yearCompare;
                }

                $termCompare = $this->termSortOrder($leftTerm) <=> $this->termSortOrder($rightTerm);
                if ($termCompare !== 0) {
                    return $termCompare;
                }

                return strcmp((string) $leftTerm, (string) $rightTerm);
            })
            ->values();

        $selectedSemester = trim((string) $request->query('semester', ''));

        if ($selectedSemester !== '' && !$semesterOptions->contains($selectedSemester)) {
            $selectedSemester = '';
        }

        if ($selectedSemester !== '') {
            $gradeRows = $gradeRows->filter(function ($row) use ($selectedSemester) {
                return $this->gradeRowSemesterKey($row) === $selectedSemester;
            })->values();
        }

        $programCode = $student ? trim((string) $student->program) : '';
        $programName = $student ? trim((string) optional($student->canonicalCourse)->name) : '';
        $programLabel = $programCode !== '' ? $programCode : ($programName !== '' ? $programName : 'N/A');
        $programDescription = $programName !== '' ? $programName : ($programCode !== '' ? $programCode : 'N/A');

        $termSections = $gradeRows
            ->groupBy(function ($row) {
                return $this->gradeRowSemesterKey($row);
            })
            ->map(function ($rows, $key) use ($programLabel, $programDescription) {
                list($schoolYear, $semester) = array_pad(explode('|', $key), 2, '');

                $numericFinalAverages = $rows->filter(function ($row) {
                    return isset($row->final_average) && $row->final_average !== null && is_numeric($row->final_average);
                });

                $avg = $numericFinalAverages->count()
                    ? round((float) $numericFinalAverages->avg('final_average'), 2)
                    : null;

                return [
                    'academic_year' => $schoolYear ?: 'N/A',
                    'term' => $semester ?: 'N/A',
                    'admission_status' => 'N/A',
                    'academic_status' => 'Regular',
                    'program' => $programLabel,
                    'program_description' => $programDescription,
                    'gpa' => $avg,
                    'rows' => $rows->values(),
                ];
            })
            ->sort(function ($left, $right) {
                $yearCompare = $this->schoolYearSortValue($right['academic_year']) <=> $this->schoolYearSortValue($left['academic_year']);
                if ($yearCompare !== 0) {
                    return $yearCompare;
                }

                $termCompare = $this->termSortOrder($left['term']) <=> $this->termSortOrder($right['term']);
                if ($termCompare !== 0) {
                    return $termCompare;
                }

                return strcmp((string) $left['term'], (string) $right['term']);
            })
            ->values();

        return view('parent.grades', compact(
            'student',
            'children',
            'gradeRows',
            'semesterOptions',
            'selectedSemester',
            'termSections',
            'selectedChildId',
            'selectedChild',
            'hasDeficiencies',
            'deficiencyItems'
        ));
    }

    public function calendar()
    {
        $calendarEvents = [];
        $audienceCode = $this->parentCalendarAudienceCode();

        if (Schema::hasTable('academic_calendar_events')) {
            $today = now()->toDateString();

            $calendarEvents = AcademicCalendarEvent::query()
                ->where('is_active', true)
                ->where(function ($query) use ($today) {
                    $query->whereNull('post_until')
                        ->orWhereDate('post_until', '>=', $today);
                })
                ->visibleToAudience($audienceCode)
                ->orderBy('event_date')
                ->orderBy('date_from')
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

        return view('parent.calendar', compact('calendarEvents'));
    }

    private function parentCalendarAudienceCode()
    {
        if (!Schema::hasTable('academic_calendar_audience_types')) {
            return 'student';
        }

        $hasParentAudience = AcademicCalendarAudienceType::query()
            ->whereRaw('LOWER(code) = ?', ['parent'])
            ->exists();

        return $hasParentAudience ? 'parent' : 'student';
    }

    public function messaging()
    {
        return view('parent.messaging');
    }

    public function helpCenter()
    {
        $topics = $this->helpCenterTopics();
        $user = Auth::user();

        $popularQuestions = [
            [
                'question' => 'How do I view the latest grades of my child?',
                'answer' => 'Open Grades in the parent sidebar and select your linked child. Grade updates appear per term once posted.',
            ],
            [
                'question' => 'Why can I not see my child in the student selector?',
                'answer' => 'Child records appear only when the account is correctly linked. Verify the student number and birthdate or contact support.',
            ],
            [
                'question' => 'How can I message the school from the parent portal?',
                'answer' => 'Use the Messaging page in the top header. Compose your concern and include your child student number for faster assistance.',
            ],
        ];

        return view('parent.help-center.index', [
            'topics' => $topics,
            'popularQuestions' => $popularQuestions,
            'helpTickets' => HelpCenterTicketService::recentTicketsForUser('Parent', $user),
            'helpTicketStoreRoute' => route('parent.help.tickets.store'),
            'helpTicketCreateRoute' => route('parent.help.tickets.create'),
            'helpTicketRequesterType' => 'Parent',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function createHelpCenterTicket()
    {
        $user = Auth::user();

        return view('shared.help-center-ticket-form-page', [
            'backRoute' => route('parent.help.center'),
            'helpTicketStoreRoute' => route('parent.help.tickets.store'),
            'helpTicketRequesterType' => 'Parent',
            'helpTicketUserName' => $user ? (string) $user->name : '',
            'helpTicketUserEmail' => $user ? (string) $user->email : '',
        ]);
    }

    public function storeHelpCenterTicket(Request $request)
    {
        $ticket = HelpCenterTicketService::createFromRequest($request, 'Parent', Auth::user());

        return redirect()
            ->route('parent.help.center')
            ->with('help_ticket_success', 'Ticket ' . $ticket->ticket_no . ' submitted successfully.');
    }

    public function helpCenterTopic($topic)
    {
        $topics = $this->helpCenterTopics();
        if (!isset($topics[$topic])) {
            abort(404);
        }

        return view('parent.help-center.topic', [
            'topic' => $topics[$topic],
        ]);
    }

    public function helpCenterLiveChat()
    {
        return view('parent.help-center.live-chat');
    }

    private function helpCenterTopics()
    {
        return [
            'account-issues' => [
                'slug' => 'account-issues',
                'title' => 'Account Issues',
                'subtitle' => 'Parent login concerns, password reset, and account access issues.',
                'icon' => 'account',
                'steps' => [
                    ['title' => 'Open Parent Login', 'description' => 'Go to the parent login page and verify your username entry.'],
                    ['title' => 'Use Forgot Password', 'description' => 'Request a reset link using your registered parent email.'],
                    ['title' => 'Check inbox and spam folder', 'description' => 'Open the reset email and complete the verification steps.'],
                    ['title' => 'Set a strong password', 'description' => 'Use at least 8 characters with letters and numbers.'],
                ],
                'faqs' => [
                    ['q' => 'Why can not I sign in as parent?', 'a' => 'Common causes are incorrect password, inactive account, or unlinked child account.'],
                    ['q' => 'Can I change my parent email?', 'a' => 'Yes. Contact support so account ownership can be validated before update.'],
                    ['q' => 'How many children can be linked?', 'a' => 'Multiple child records can be linked if credentials and child information are valid.'],
                ],
            ],
            'grades-and-records' => [
                'slug' => 'grades-and-records',
                'title' => 'Grades and Records',
                'subtitle' => 'Viewing term grades, GPA details, and student profile information.',
                'icon' => 'application',
                'steps' => [
                    ['title' => 'Open Grades module', 'description' => 'Select Grades from the parent sidebar menu.'],
                    ['title' => 'Select your linked child', 'description' => 'Choose the student from the selector at the top of the page.'],
                    ['title' => 'Review per-term data', 'description' => 'Check the subject table, units, and grade status per term section.'],
                    ['title' => 'Use Student Profile for details', 'description' => 'Open Student Profile to view school and identity records.'],
                ],
                'faqs' => [
                    ['q' => 'Why is a grade blank?', 'a' => 'Blank fields usually mean grades are not yet posted or subject evaluation is still ongoing.'],
                    ['q' => 'Can parents edit grades?', 'a' => 'No. Parent accounts are view-only for grades and records.'],
                    ['q' => 'What does GPA include?', 'a' => 'GPA follows school rules and may exclude NSTP and non-numeric ratings.'],
                ],
            ],
            'technical-problems' => [
                'slug' => 'technical-problems',
                'title' => 'Technical Problems',
                'subtitle' => 'Troubleshooting display issues, slow pages, and loading errors.',
                'icon' => 'technical',
                'steps' => [
                    ['title' => 'Refresh the page', 'description' => 'A full refresh resolves many temporary display issues.'],
                    ['title' => 'Use Chrome or Edge', 'description' => 'These browsers are fully supported by the portal.'],
                    ['title' => 'Clear browser cache', 'description' => 'Outdated cached files can affect current page behavior.'],
                    ['title' => 'Check internet connection', 'description' => 'Unstable connection may interrupt page data loading.'],
                ],
                'faqs' => [
                    ['q' => 'Why is the page layout broken?', 'a' => 'This is often caused by stale cache or incomplete asset loading. Refresh and clear cache.'],
                    ['q' => 'What should I send to support?', 'a' => 'Include screenshot, page URL, browser name, and time of issue for faster diagnosis.'],
                ],
            ],
            'linking-child-account' => [
                'slug' => 'linking-child-account',
                'title' => 'Linking Child Account',
                'subtitle' => 'How to connect one or more student records to your parent account.',
                'icon' => 'forms',
                'steps' => [
                    ['title' => 'Open Parent Profile', 'description' => 'Go to Parent Profile from the top header avatar icon.'],
                    ['title' => 'Click Link another child', 'description' => 'Use student number and birthdate in the modal form.'],
                    ['title' => 'Submit details', 'description' => 'Confirm the information and submit for linking validation.'],
                    ['title' => 'Verify in linked child table', 'description' => 'The student should appear in your linked children list.'],
                ],
                'faqs' => [
                    ['q' => 'Why does linking fail?', 'a' => 'Student number and birthdate may not match records, or the account is already linked elsewhere.'],
                    ['q' => 'Can I unlink a child record?', 'a' => 'Yes, but unlink requests may require verification through support.'],
                ],
            ],
            'parent-portal-module' => [
                'slug' => 'parent-portal-module',
                'title' => 'Parent Portal Module',
                'subtitle' => 'Guide to the Parent sidebar and top header pages.',
                'icon' => 'module',
                'steps' => [
                    ['title' => 'Use sidebar for core modules', 'description' => 'Grades, Student Profile, Calendar, Contact Us, and Change Password are in sidebar.'],
                    ['title' => 'Use header for support tools', 'description' => 'Header icons provide Help Center, Notifications, Messages, and Parent Profile.'],
                    ['title' => 'Review updates regularly', 'description' => 'Check grades, notifications, and calendar for school announcements.'],
                ],
                'faqs' => [
                    ['q' => 'What is the difference between Parent Profile and Student Profile?', 'a' => 'Parent Profile is for account details and linked children. Student Profile shows student information records.'],
                    ['q' => 'Can I access old term grades?', 'a' => 'Yes, available terms appear in the Grades view when records exist.'],
                ],
            ],
            'security-and-privacy' => [
                'slug' => 'security-and-privacy',
                'title' => 'Security and Privacy',
                'subtitle' => 'Parent account safety and student data privacy reminders.',
                'icon' => 'security',
                'steps' => [
                    ['title' => 'Keep credentials private', 'description' => 'Never share your parent username and password.'],
                    ['title' => 'Log out on shared devices', 'description' => 'Always sign out after using public or family-shared computers.'],
                    ['title' => 'Use strong password updates', 'description' => 'Update your password periodically and avoid reused passwords.'],
                    ['title' => 'Report suspicious activity', 'description' => 'Notify support immediately if you notice unknown access attempts.'],
                ],
                'faqs' => [
                    ['q' => 'Who can view my child records?', 'a' => 'Only authorized accounts and school personnel under role permissions can view records.'],
                    ['q' => 'How do I secure my account better?', 'a' => 'Use unique strong passwords and avoid saving credentials on shared browsers.'],
                ],
            ],
        ];
    }

    public function studentProfile(Request $request)
    {
        $currentStudent = $this->currentStudent();
        $children = $this->linkedChildren($currentStudent);
        $selectedChildId = $request->query('child');
        $selectedChild = $this->resolveSelectedChild($children, $selectedChildId);

        $student = null;
        if ($selectedChild && !empty($selectedChild['id'])) {
            $student = Student::with(['canonicalCourse', 'yearBlock', 'user', 'academicTerm'])->find($selectedChild['id']);
        }

        if (!$student && $currentStudent && (!$selectedChild || (string) ($selectedChild['id'] ?? '') === (string) $currentStudent->id)) {
            $student = $currentStudent;
        }

        if ($student) {
            $student->loadMissing(['canonicalCourse', 'yearBlock', 'user', 'academicTerm']);
        }

        if ($selectedChild && isset($selectedChild['id'])) {
            $selectedChildId = (string) $selectedChild['id'];
        } else {
            $selectedChildId = null;
        }

        $studentFieldVisibility = [
            'section' => Schema::hasColumn('students', 'section'),
            'admission_status' => Schema::hasColumn('students', 'admission_status'),
            'admission_year' => Schema::hasColumn('students', 'admission_year'),
            'enrollment_status' => Schema::hasColumn('students', 'enrollment_status'),
            'academic_status' => Schema::hasColumn('students', 'academic_status'),
        ];

        $profile = $this->findStudentProfileRecord($student);
        $profileData = $this->buildStudentProfileData($student, $profile, Auth::user());

        return view('parent.student-profile', [
            'user' => Auth::user(),
            'student' => $student,
            'profile' => $profile,
            'profileData' => $profileData,
            'children' => $children,
            'selectedChild' => $selectedChild,
            'selectedChildId' => $selectedChildId,
            'studentFieldVisibility' => $studentFieldVisibility,
        ]);
    }

    public function contactUs(Request $request)
    {
        $currentStudent = $this->currentStudent();
        $children = $this->linkedChildren($currentStudent);
        $selectedChild = $this->resolveSelectedChild($children, $request->query('child'));
        $selectedChildId = $selectedChild && isset($selectedChild['id'])
            ? (string) $selectedChild['id']
            : '';

        $topics = collect();
        if (Schema::hasTable('parent_contact_request_topics')) {
            $topics = ParentContactRequestTopic::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        $channels = collect();
        if (Schema::hasTable('parent_contact_request_channels')) {
            $channels = ParentContactRequestChannel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        $recentRequests = collect();
        $user = Auth::user();
        if ($user && $user->parent_id && Schema::hasTable('parent_contact_requests')) {
            $recentRequests = ParentContactRequest::query()
                ->with([
                    'topic:id,name',
                    'status:id,name',
                    'channel:id,name',
                    'student:id,student_no,name',
                ])
                ->where('parent_id', $user->parent_id)
                ->orderByDesc('created_at')
                ->paginate(8);
        }

        $supportContacts = $this->supportContacts();

        return view('parent.contact-us', [
            'children' => $children,
            'selectedChildId' => $selectedChildId,
            'topics' => $topics,
            'channels' => $channels,
            'recentRequests' => $recentRequests,
            'supportEmail' => $supportContacts['email'],
            'supportEmailComposeUrl' => 'https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=' . rawurlencode((string) $supportContacts['email']),
            'supportPhone' => $supportContacts['phone'],
            'supportContactDetails' => $supportContacts['contact_details'],
        ]);
    }

    public function changePassword()
    {
        return view('parent.change-password', [
            'user' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
                'different:current_password',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'password.regex' => 'New password must include uppercase, lowercase, and numeric characters.',
        ]);

        if (!Hash::check($validated['current_password'], (string) $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        $user->password = Hash::make($validated['password']);
        $user->force_password_reset = false;
        $user->save();

        return redirect()->route('parent.change-password')
            ->with('status', 'Password updated successfully.');
    }

    public function submitContactUs(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->parent_id) {
            abort(403);
        }

        if (!Schema::hasTable('parent_contact_requests')
            || !Schema::hasTable('parent_contact_request_topics')
            || !Schema::hasTable('parent_contact_request_channels')
            || !Schema::hasTable('parent_contact_request_statuses')) {
            return redirect()->route('parent.contact-us')
                ->withErrors(['contact' => 'Contact request setup is not available yet.']);
        }

        $activeTopicIds = ParentContactRequestTopic::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $activeChannelIds = ParentContactRequestChannel::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $validated = $request->validate([
            'child' => ['nullable', 'integer'],
            'topic_id' => ['required', 'integer', Rule::in($activeTopicIds)],
            'channel_id' => ['required', 'integer', Rule::in($activeChannelIds)],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $children = $this->linkedChildren($this->currentStudent());
        $allowedStudentIds = $children
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->values()
            ->all();

        $studentId = null;
        if (!empty($validated['child'])) {
            $candidateId = (int) $validated['child'];
            if (!in_array($candidateId, $allowedStudentIds, true)) {
                return redirect()->route('parent.contact-us')
                    ->withErrors(['child' => 'Selected child is not linked to your account.'])
                    ->withInput();
            }

            $studentId = $candidateId;
        }

        $statusId = ParentContactRequestStatus::query()->where('code', 'NEW')->value('id');
        if (!$statusId) {
            $statusId = ParentContactRequestStatus::query()->orderBy('sort_order')->value('id');
        }

        if (!$statusId) {
            return redirect()->route('parent.contact-us')
                ->withErrors(['contact' => 'Contact request status setup is missing.'])
                ->withInput();
        }

        $referenceNo = $this->generateContactReferenceNo();
        $parentProfile = $user->parentProfile;

        ParentContactRequest::query()->create([
            'reference_no' => $referenceNo,
            'parent_id' => $user->parent_id,
            'student_id' => $studentId,
            'topic_id' => (int) $validated['topic_id'],
            'status_id' => (int) $statusId,
            'channel_id' => (int) $validated['channel_id'],
            'submitted_by_user_id' => $user->id,
            'subject' => trim((string) $validated['subject']),
            'message' => trim((string) $validated['message']),
            'contact_email_snapshot' => $this->nullableText($user->email ?: optional($parentProfile)->email),
            'contact_mobile_snapshot' => $this->nullableText(optional($parentProfile)->mobile_number),
            'submitted_at' => now(),
        ]);

        return redirect()->route('parent.contact-us')
            ->with('status', 'Your inquiry was submitted successfully. Reference No: ' . $referenceNo);
    }

    private function findStudentProfileRecord($student)
    {
        if (!$student || !Schema::hasTable('student_profiles')) {
            return null;
        }

        $studentId = (int) data_get($student, 'id');
        $studentNo = trim((string) data_get($student, 'student_no'));

        if ($studentId <= 0 && $studentNo === '') {
            return null;
        }

        $relations = ['profileImage'];

        if (Schema::hasTable('ph_regions')
            && Schema::hasColumn('student_profiles', 'present_region_id')
            && Schema::hasColumn('student_profiles', 'permanent_region_id')) {
            $relations[] = 'presentRegion:id,region_name';
            $relations[] = 'permanentRegion:id,region_name';
        }

        if (Schema::hasTable('ph_provinces')
            && Schema::hasColumn('student_profiles', 'present_province_id')
            && Schema::hasColumn('student_profiles', 'permanent_province_id')) {
            $relations[] = 'presentProvince:id,province_name';
            $relations[] = 'permanentProvince:id,province_name';
        }

        if (Schema::hasTable('ph_municipalities')
            && Schema::hasColumn('student_profiles', 'present_municipality_id')
            && Schema::hasColumn('student_profiles', 'permanent_municipality_id')) {
            $relations[] = 'presentMunicipality:id,municipality_name';
            $relations[] = 'permanentMunicipality:id,municipality_name';
        }

        $hasProfileCompleteColumn = Schema::hasColumn('student_profiles', 'profile_complete');

        if ($studentNo !== '') {
            $profileByStudentNoQuery = StudentProfile::query()
                ->with($relations)
                ->where('student_no', $studentNo);

            if ($hasProfileCompleteColumn) {
                $profileByStudentNoQuery->orderByDesc('profile_complete');
            }

            $profileByStudentNo = $profileByStudentNoQuery
                ->orderByDesc('id')
                ->first();

            if ($profileByStudentNo) {
                return $profileByStudentNo;
            }
        }

        if ($studentId > 0) {
            $profileByStudentIdQuery = StudentProfile::query()
                ->with($relations)
                ->where('student_id', $studentId);

            if ($hasProfileCompleteColumn) {
                $profileByStudentIdQuery->orderByDesc('profile_complete');
            }

            return $profileByStudentIdQuery
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }

    private function buildStudentProfileData($student, $profile, $user)
    {
        $studentNo = trim((string) data_get($student, 'student_no'));
        $studentName = trim((string) data_get($student, 'name'));

        if ($studentName === '') {
            $studentName = trim(implode(' ', array_filter([
                trim((string) data_get($profile, 'first_name')),
                trim((string) data_get($profile, 'middle_name')),
                trim((string) data_get($profile, 'last_name')),
                trim((string) data_get($profile, 'suffix')),
            ])));
        }

        $presentRegion = trim((string) optional(data_get($profile, 'presentRegion'))->region_name);
        if ($presentRegion === '') {
            $presentRegion = trim((string) data_get($profile, 'present_region'));
        }

        $presentProvince = trim((string) optional(data_get($profile, 'presentProvince'))->province_name);
        if ($presentProvince === '') {
            $presentProvince = trim((string) data_get($profile, 'present_province'));
        }

        $presentMunicipality = trim((string) optional(data_get($profile, 'presentMunicipality'))->municipality_name);
        if ($presentMunicipality === '') {
            $presentMunicipality = trim((string) data_get($profile, 'present_municipality'));
        }

        $permanentRegion = trim((string) optional(data_get($profile, 'permanentRegion'))->region_name);
        if ($permanentRegion === '') {
            $permanentRegion = trim((string) data_get($profile, 'permanent_region'));
        }

        $permanentProvince = trim((string) optional(data_get($profile, 'permanentProvince'))->province_name);
        if ($permanentProvince === '') {
            $permanentProvince = trim((string) data_get($profile, 'permanent_province'));
        }

        $permanentMunicipality = trim((string) optional(data_get($profile, 'permanentMunicipality'))->municipality_name);
        if ($permanentMunicipality === '') {
            $permanentMunicipality = trim((string) data_get($profile, 'permanent_municipality'));
        }

        $presentAddress = $this->formatAddressLine([
            trim((string) data_get($profile, 'present_street')),
            trim((string) data_get($profile, 'present_barangay')),
            $presentMunicipality,
            $presentProvince,
            $presentRegion,
            trim((string) data_get($profile, 'present_zipcode')),
        ]);

        $permanentAddress = $this->formatAddressLine([
            trim((string) data_get($profile, 'permanent_street')),
            trim((string) data_get($profile, 'permanent_barangay')),
            $permanentMunicipality,
            $permanentProvince,
            $permanentRegion,
            trim((string) data_get($profile, 'permanent_zipcode')),
        ]);

        $programCode = trim((string) data_get($student, 'program'));
        $programName = trim((string) optional(data_get($student, 'canonicalCourse'))->name);
        $program = $programCode;
        if ($program === '') {
            $program = $programName;
        } elseif ($programName !== '' && strcasecmp($programCode, $programName) !== 0) {
            $program = $programCode . ' - ' . $programName;
        }

        $yearLevel = trim((string) data_get($student, 'year_level'));
        if ($yearLevel === '') {
            $yearLevel = trim((string) optional(data_get($student, 'yearBlock'))->label);
        }

        $dateOfBirth = '';
        if ($profile && $profile->date_of_birth) {
            $dateOfBirth = $profile->date_of_birth->format('F d, Y');
        }

        $studentUserEmail = trim((string) data_get($student, 'user.email'));

        return [
            'student_no' => $this->displayText($studentNo),
            'student_name' => $this->displayText($studentName),
            'contact_no' => $this->displayText(
                trim((string) data_get($profile, 'mobile_number')) !== ''
                    ? data_get($profile, 'mobile_number')
                    : data_get($student, 'contact_no')
            ),
            'email' => $this->displayText(
                trim((string) data_get($profile, 'student_email')) !== ''
                    ? data_get($profile, 'student_email')
                    : ($studentUserEmail !== ''
                        ? $studentUserEmail
                        : (data_get($student, 'email') ?: optional($user)->email))
            ),
            'residential_address' => $this->displayText($presentAddress),
            'present_region' => $this->displayText($presentRegion),
            'present_province' => $this->displayText($presentProvince),
            'present_municipality' => $this->displayText($presentMunicipality),
            'permanent_address' => $this->displayText($permanentAddress),
            'date_of_birth' => $this->displayText($dateOfBirth),
            'place_of_birth' => $this->displayText(data_get($profile, 'place_of_birth')),
            'gender' => $this->displayText(
                trim((string) data_get($profile, 'gender')) !== ''
                    ? data_get($profile, 'gender')
                    : data_get($student, 'sex')
            ),
            'religion' => $this->displayText(data_get($profile, 'religion')),
            'citizenship' => $this->displayText(data_get($profile, 'nationality')),
            'civil_status' => $this->displayText(data_get($profile, 'civil_status')),
            'program' => $this->displayText($program),
            'year_level' => $this->displayText($yearLevel),
            'section' => $this->displayText(data_get($student, 'section')),
            'curriculum_year' => $this->displayText(data_get($student, 'curriculum')),
            'admission_status' => $this->displayText(data_get($student, 'admission_status')),
            'admission_year' => $this->displayText(data_get($student, 'admission_year')),
            'enrollment_status' => $this->displayText(data_get($student, 'enrollment_status')),
            'academic_status' => $this->displayText(data_get($student, 'academic_status')),
            'photo_url' => $this->resolveProfilePhotoUrl($profile),
        ];
    }

    private function resolveProfilePhotoUrl($profile)
    {
        if (!$profile) {
            return null;
        }

        $profilePhotoPath = trim((string) data_get($profile, 'profile_photo_path'));
        if ($profilePhotoPath !== '') {
            return asset('storage/' . ltrim($profilePhotoPath, '/'));
        }

        $profileImage = data_get($profile, 'profileImage');
        $storagePath = trim((string) data_get($profileImage, 'storage_path'));
        if ($storagePath === '') {
            return null;
        }

        return asset('storage/' . ltrim($storagePath, '/'));
    }

    private function formatAddressLine(array $segments)
    {
        $values = [];
        foreach ($segments as $segment) {
            $text = trim((string) $segment);
            if ($text === '') {
                continue;
            }

            $values[] = $text;
        }

        if (count($values) === 0) {
            return null;
        }

        return implode(', ', $values);
    }

    private function displayText($value)
    {
        $text = trim((string) $value);
        return $text === '' ? 'N/A' : $text;
    }

    private function nullableText($value)
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function supportContacts()
    {
        $contactDetails = null;

        if (Schema::hasTable('system_report_detail_settings')) {
            $settingsRow = SystemReportDetailSetting::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            if (!$settingsRow) {
                $settingsRow = SystemReportDetailSetting::query()
                    ->orderByDesc('id')
                    ->first();
            }

            $contactDetails = $settingsRow ? trim((string) $settingsRow->contact_details) : null;
        }

        $supportEmail = 'registrar@plpasig.edu.ph';
        $supportPhone = '+63 2 8628 1014';

        $sourceText = trim((string) $contactDetails);
        if ($sourceText !== '') {
            $emailProbe = preg_replace('/(?i)(bcc:|cc:|to:)/', ' ', $sourceText);
            $emailCandidates = preg_split('/[\s,;]+/', (string) $emailProbe);

            foreach ((array) $emailCandidates as $candidate) {
                $token = trim((string) $candidate, "\t\n\r\0\x0B<>()[]{}\"'");
                if ($token === '') {
                    continue;
                }

                if (filter_var($token, FILTER_VALIDATE_EMAIL)) {
                    $supportEmail = strtolower($token);
                    break;
                }
            }

            if (preg_match('/(\+?[0-9][0-9\-\s\(\)]{6,}[0-9])/', $sourceText, $phoneMatches)) {
                $supportPhone = trim((string) $phoneMatches[1]);
            }
        }

        return [
            'email' => $supportEmail,
            'phone' => $supportPhone,
            'contact_details' => $this->nullableText($contactDetails),
        ];
    }

    private function generateContactReferenceNo()
    {
        do {
            $token = strtoupper(substr(md5(uniqid((string) mt_rand(1000, 9999), true)), 0, 6));
            $referenceNo = 'PCR-' . now()->format('YmdHis') . '-' . $token;
        } while (ParentContactRequest::query()->where('reference_no', $referenceNo)->exists());

        return $referenceNo;
    }

    public function profile()
    {
        $user = Auth::user();
        $student = $this->currentStudent();
        $children = $this->linkedChildren($student);

        return view('parent.profile', [
            'user' => $user,
            'student' => $student,
            'children' => $children,
        ]);
    }
}
