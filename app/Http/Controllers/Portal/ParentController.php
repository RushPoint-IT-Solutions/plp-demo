<?php

namespace App\Http\Controllers\Portal;

use App\AcademicCalendarEvent;
use App\Http\Controllers\Concerns\PortalNotifications;
use App\Http\Controllers\Controller;
use App\Student;
use App\StudentDeficiency;
use App\StudentSubjectGrade;
use App\SystemAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
        return view('parent.create-account');
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

        if ($user && $user->student_id) {
            return Student::with('subjects')->find($user->student_id);
        }

        if ($user && !empty($user->username)) {
            return Student::with('subjects')->where('student_no', $user->username)->first();
        }

        return null;
    }

    private function linkedChildren($student)
    {
        if (!$student) {
            return $this->dummyChildren();
        }

        $children = collect([
            [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'course' => $student->program ?: $student->college,
                'year_level' => $student->year_level,
                'birthdate' => null,
                'status' => 'Linked',
            ],
        ]);

        // Keep at least two rows for easier UI configuration/visualization.
        if ($children->count() < 2) {
            $children = $children->concat($this->dummyChildren()->slice(1));
        }

        return $children->values();
    }

    private function dummyChildren()
    {
        return collect([
            [
                'id' => 2,
                'student_no' => '232418412',
                'name' => 'Julius T. Garma',
                'course' => 'BSMT',
                'year_level' => '3',
                'birthdate' => 'February 5, 2001',
                'status' => 'Linked',
            ],
            [
                'id' => 1,
                'student_no' => '232418456',
                'name' => 'Aleya Mae T. Garma',
                'course' => 'BSMT',
                'year_level' => '3',
                'birthdate' => 'July 28, 2004',
                'status' => 'Linked',
            ],
        ]);
    }

    private function dummyTermSections()
    {
        $subjectRows = collect([
            ['code' => 'D-WATCH', 'name' => 'Deck Watchkeeping w/ Bridge Resources Mgmt.', 'units' => 4],
            ['code' => 'GE11', 'name' => 'Arts and Humanities', 'units' => 3],
            ['code' => 'GE13', 'name' => 'Social Sciences and Philosophy', 'units' => 3],
            ['code' => 'GE6', 'name' => 'Arts Appreciation', 'units' => 3],
            ['code' => 'NAV8-MT', 'name' => 'Operation Use of Electronic Chart Display and Information System (ECDIS)', 'units' => 3],
            ['code' => 'NAV7', 'name' => 'Voyage Planning', 'units' => 3],
            ['code' => 'RIZAL221', 'name' => 'Life and Works of Rizal', 'units' => 3],
            ['code' => 'SEAM6-MT', 'name' => 'Advanced Trims, Stability & Stress', 'units' => 3],
        ])->map(function ($item, $index) {
            $row = new \stdClass();
            $row->midterm = null;
            $row->final = null;
            $row->final_average = null;
            $row->remarks = '';

            $subject = new \stdClass();
            $subject->code = $item['code'];
            $subject->name = $item['name'];
            $subject->faculty_name = 'Abela, Manuel';
            $subject->units = $item['units'];
            $subject->section = 'BSMT 3-A';
            $subject->school_year = '2025-2026';
            $subject->semester = 'Second';
            $row->subject = $subject;

            return $row;
        });

        return collect([
            [
                'academic_year' => '2025-2026',
                'term' => 'Second',
                'admission_status' => 'Transferee',
                'academic_status' => 'Regular',
                'program' => 'BSMT',
                'program_description' => 'Bachelor Of Science Marine Transportation',
                'gpa' => 0.00,
                'rows' => $subjectRows,
            ],
            [
                'academic_year' => '2025-2026',
                'term' => 'Second',
                'admission_status' => 'Transferee',
                'academic_status' => 'Regular',
                'program' => 'BSMT',
                'program_description' => 'Bachelor Of Science Marine Transportation',
                'gpa' => 0.00,
                'rows' => $subjectRows,
            ],
        ]);
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
            $default = $children->first(function ($child) {
                $name = strtolower(trim((string) ($child['name'] ?? '')));
                return strpos($name, 'julius') !== false;
            });

            return $default ?: $children->first();
        }

        $matched = $children->first(function ($child) use ($selectedChildId) {
            return (string) ($child['id'] ?? '') === (string) $selectedChildId;
        });

        return $matched ?: $children->first();
    }

    private function shouldUseSampleDeficiency($selectedChild)
    {
        $name = strtolower(trim((string) data_get($selectedChild, 'name')));
        return $name !== '' && strpos($name, 'aleya') !== false;
    }

    public function grades()
    {
        $currentStudent = $this->currentStudent();
        $children = $this->linkedChildren($currentStudent);
        $selectedChildId = request('child');
        $selectedChild = $this->resolveSelectedChild($children, $selectedChildId);

        $student = null;
        if ($selectedChild && !empty($selectedChild['id'])) {
            $student = Student::with('subjects')->find($selectedChild['id']);
        }

        if (!$student && $currentStudent && (!$selectedChild || (string) ($selectedChild['id'] ?? '') === (string) $currentStudent->id)) {
            $student = $currentStudent;
        }

        $gradeRows = collect();
        if ($student) {
            $gradeRows = StudentSubjectGrade::with('subject')
                ->where('student_id', $student->id)
                ->get();
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

        if ($deficiencyItems->isEmpty() && $this->shouldUseSampleDeficiency($selectedChild)) {
            $deficiencyItems = collect([
                'Registrar - Pending Grades in Laboratory Class',
                'Unreturned library book: Maritime Safety Vol. 2',
            ]);
        }

        $hasDeficiencies = $deficiencyItems->isNotEmpty();

        $semesterOptions = $gradeRows
            ->map(function ($row) {
                return optional($row->subject)->school_year . '|' . optional($row->subject)->semester;
            })
            ->filter()
            ->unique()
            ->values();

        $selectedSemester = request('semester');

        if ($selectedSemester) {
            list($selectedSchoolYear, $selectedSem) = array_pad(explode('|', $selectedSemester), 2, null);
            $gradeRows = $gradeRows->filter(function ($row) use ($selectedSchoolYear, $selectedSem) {
                return optional($row->subject)->school_year === $selectedSchoolYear
                    && optional($row->subject)->semester === $selectedSem;
            })->values();
        }

        $termSections = $gradeRows
            ->groupBy(function ($row) {
                return optional($row->subject)->school_year . '|' . optional($row->subject)->semester;
            })
            ->map(function ($rows, $key) {
                list($schoolYear, $semester) = array_pad(explode('|', $key), 2, '');

                $avg = $rows->count() ? round((float) $rows->avg('final_average'), 2) : null;

                return [
                    'academic_year' => $schoolYear ?: 'N/A',
                    'term' => $semester ?: 'N/A',
                    'admission_status' => 'Transferee',
                    'academic_status' => 'Regular',
                    'program' => optional(optional($rows->first())->student)->program ?: 'BSMT',
                    'program_description' => optional(optional($rows->first())->student)->program ?: 'Bachelor Of Science Marine Transportation',
                    'gpa' => $avg,
                    'rows' => $rows->values(),
                ];
            })
            ->values();

        if ($termSections->isEmpty()) {
            $termSections = $this->dummyTermSections();
        }

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

        return view('parent.calendar', compact('calendarEvents'));
    }

    public function messaging()
    {
        return view('parent.messaging');
    }

    public function helpCenter()
    {
        $topics = $this->helpCenterTopics();

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
        ]);
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

    public function studentProfile()
    {
        $currentStudent = $this->currentStudent();
        $children = $this->linkedChildren($currentStudent);
        $selectedChildId = request('child');
        $selectedChild = $this->resolveSelectedChild($children, $selectedChildId);

        $student = null;
        if ($selectedChild && !empty($selectedChild['id'])) {
            $student = Student::with('subjects')->find($selectedChild['id']);
        }

        if (!$student && $currentStudent && (!$selectedChild || (string) ($selectedChild['id'] ?? '') === (string) $currentStudent->id)) {
            $student = $currentStudent;
        }

        $nameParts = $this->splitNameParts($student ? $student->name : optional(Auth::user())->name);

        if (trim($nameParts['first']) === '' && trim($nameParts['middle']) === '' && trim($nameParts['last']) === '') {
            $nameParts = [
                'first' => 'Emelie',
                'middle' => 'Tamayo',
                'last' => 'Garma',
            ];
        }

        return view('parent.student-profile', [
            'user' => Auth::user(),
            'student' => $student,
            'children' => $children,
            'nameParts' => $nameParts,
            'selectedChild' => $selectedChild,
            'selectedChildId' => $selectedChildId,
        ]);
    }

    public function contactUs()
    {
        return view('parent.contact-us');
    }

    public function changePassword()
    {
        return view('parent.change-password');
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
