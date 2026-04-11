<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\AcademicCalendarEvent;
use App\MasterFacultyFile;
use App\FacultyEvaluation;
use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\Subject;
use App\StudentSubjectGrade;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FacultyController extends Controller
{
    private function interpretationFromMean($score)
    {
        $value = (float) $score;

        if ($value >= 4.5) {
            return 'Outstanding';
        }

        if ($value >= 4.0) {
            return 'Very Satisfactory';
        }

        if ($value >= 3.0) {
            return 'Satisfactory';
        }

        return 'Needs Improvement';
    }

    private function sampleCommentsForScore($score)
    {
        $value = (float) $score;

        if ($value >= 4.5) {
            return [
                'Clearly an expert in this field; the lectures make complex concepts easy to understand.',
                'Class sessions are always organized and motivating.',
                'Excellent pacing and examples during discussions.',
                'Approachable and responsive to student questions.',
                'Strong connection of lessons to practical applications.',
            ];
        }

        if ($value >= 4.0) {
            return [
                'Well-prepared and consistent in discussing lessons.',
                'The class flow is clear and easy to follow.',
                'Provides useful examples for difficult topics.',
                'Gives timely feedback on activities.',
                'Encourages participation during class discussions.',
            ];
        }

        if ($value >= 3.0) {
            return [
                'Discussions are understandable most of the time.',
                'Instructional materials are helpful for review.',
                'Could improve pacing on some topics.',
                'Responds to questions during sessions.',
                'Class expectations are generally clear.',
            ];
        }

        return [
            'Needs clearer examples for major concepts.',
            'Pacing can be improved for better understanding.',
            'More consistent feedback would be helpful.',
            'Classroom engagement could be increased.',
            'Additional review sessions are recommended.',
        ];
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $this->syncFacultyNotificationsForUser($user);

            view()->share('facultyNotifications', $this->activeFacultyNotifications($user));
            view()->share('facultyUnreadNotificationCount', $this->facultyUnreadNotificationCount($user));

            return $next($request);
        });
    }

    private function syncFacultyNotificationsForUser($user)
    {
        $this->syncEvaluationNotificationsForFaculty($user);
        $this->syncCalendarNotificationsForFaculty($user);
    }

    private function activeFacultyNotifications($user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user || $user->module !== 'faculty') {
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

    private function facultyUnreadNotificationCount($user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user || $user->module !== 'faculty') {
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

    private function syncEvaluationNotificationsForFaculty($user)
    {
        if (!$user || $user->module !== 'faculty') {
            return;
        }

        if (!Schema::hasTable('faculty_evaluations')
            || !Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')
            || !Schema::hasTable('notification_deliveries')
        ) {
            return;
        }

        $faculty = $this->currentFaculty();
        if (!$faculty) {
            return;
        }

        $subjects = $this->subjectsForFaculty($faculty)
            ->get(['id', 'code', 'name'])
            ->keyBy('id');

        if ($subjects->isEmpty()) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'EVALUATION_RESULT_SHARED'],
            ['name' => 'Evaluation Result Shared']
        );

        $evaluations = FacultyEvaluation::query()
            ->whereIn('subject_id', $subjects->keys()->all())
            ->orderBy('id')
            ->get(['id', 'subject_id', 'section', 'mean_score', 'created_at']);

        foreach ($evaluations as $evaluation) {
            $subject = $subjects->get((int) $evaluation->subject_id);
            if (!$subject) {
                continue;
            }

            $sourceReference = 'faculty_evaluation:' . $evaluation->id;
            $sectionLabel = trim((string) $evaluation->section);
            $titleSubject = trim((string) ($subject->code ?: $subject->name));

            $title = 'New Evaluation Posted: ' . ($titleSubject !== '' ? $titleSubject : 'Subject');
            $message = 'A new evaluation result is available';
            if ($sectionLabel !== '') {
                $message .= ' for section ' . $sectionLabel;
            }
            if ($evaluation->mean_score !== null) {
                $message .= ' (mean score: ' . number_format((float) $evaluation->mean_score, 1) . ')';
            }
            $message .= '.';

            $notification = PortalNotification::query()->firstOrCreate(
                [
                    'source_module' => 'faculty_evaluation',
                    'source_reference' => $sourceReference,
                ],
                [
                    'notification_type_id' => $type->id,
                    'title' => $title,
                    'message' => $message,
                    'source_url' => route('faculty.evaluation', ['subject_id' => $evaluation->subject_id], false),
                    'created_by_user_id' => null,
                ]
            );

            $localSourceUrl = (string) $notification->local_source_url;
            if ($localSourceUrl !== '' && (string) $notification->source_url !== $localSourceUrl) {
                $notification->source_url = $localSourceUrl;
                $notification->save();
            }

            NotificationDelivery::query()->firstOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => $user->id,
                ],
                [
                    'delivered_at' => $evaluation->created_at ?: now(),
                ]
            );
        }
    }

    private function syncCalendarNotificationsForFaculty($user)
    {
        if (!$user || $user->module !== 'faculty') {
            return;
        }

        if (!Schema::hasTable('academic_calendar_events')
            || !Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')
            || !Schema::hasTable('notification_deliveries')
        ) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'ACADEMIC_CALENDAR_EVENT_SET'],
            ['name' => 'Academic Calendar Event Set']
        );

        $today = now()->startOfDay();
        $events = AcademicCalendarEvent::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'event_date', 'title', 'venue', 'created_at', 'post_until']);

        foreach ($events as $event) {
            if (!empty($event->post_until) && $event->post_until->lt($today)) {
                continue;
            }

            $eventTitle = trim((string) $event->title);
            $eventDateLabel = optional($event->event_date)->format('M d, Y');
            $eventVenue = trim((string) ($event->venue ?: ''));

            $title = $eventTitle !== ''
                ? 'New Calendar Event: ' . $eventTitle
                : 'New Calendar Event Posted';

            $message = 'A new academic calendar event has been posted';
            if (!empty($eventDateLabel)) {
                $message .= ' for ' . $eventDateLabel;
            }
            if ($eventVenue !== '') {
                $message .= ' at ' . $eventVenue;
            }
            $message .= '.';

            $notification = PortalNotification::query()->firstOrCreate(
                [
                    'source_module' => 'academic_calendar_event',
                    'source_reference' => 'academic_calendar_event:' . $event->id,
                ],
                [
                    'notification_type_id' => $type->id,
                    'title' => $title,
                    'message' => $message,
                    'source_url' => route('faculty.calendar', [], false),
                    'created_by_user_id' => null,
                ]
            );

            $localSourceUrl = (string) $notification->local_source_url;
            if ($localSourceUrl !== '' && (string) $notification->source_url !== $localSourceUrl) {
                $notification->source_url = $localSourceUrl;
                $notification->save();
            }

            NotificationDelivery::query()->firstOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => $user->id,
                ],
                [
                    'delivered_at' => $event->created_at ?: now(),
                ]
            );
        }
    }

    private function currentFaculty()
    {
        $user = auth()->user();
        if ($user && $user->faculty_id) {
            return \App\Faculty::find($user->faculty_id);
        }
        return null;
    }

    private function subjectsForFaculty($faculty)
    {
        if (!$faculty) {
            return Subject::whereRaw('1 = 0');
        }

        return Subject::where(function ($query) use ($faculty) {
            $query->where('faculty_id', $faculty->id);

            if (Schema::hasColumn('subjects', 'faculty')) {
                $query->orWhere('faculty', $faculty->name);
            }
        });
    }

    private function defaultFacultyProfileSections()
    {
        return [
            'education' => [],
            'registration' => [],
            'organization' => [],
            'work' => [],
            'training' => [],
        ];
    }

    private function mergeFacultyProfileSections($stored)
    {
        $sections = $this->defaultFacultyProfileSections();

        if (!is_array($stored)) {
            return $sections;
        }

        foreach ($sections as $key => $defaultRows) {
            if (isset($stored[$key]) && is_array($stored[$key])) {
                $sections[$key] = $stored[$key];
            }
        }

        return $sections;
    }

    private function resolveFacultyProfileRow($faculty)
    {
        if (!$faculty || !Schema::hasTable('master_faculty_files')) {
            return null;
        }

        $code = (string) ($faculty->code ?: '');
        $name = (string) ($faculty->name ?: '');

        $row = null;
        if ($code !== '') {
            $row = MasterFacultyFile::where('code', $code)->first();
        }

        if (!$row && $name !== '') {
            $row = MasterFacultyFile::where('name', $name)->first();
        }

        if (!$row) {
            $row = MasterFacultyFile::create([
                'code' => $code !== '' ? $code : ('FAC-' . str_pad((string) $faculty->id, 4, '0', STR_PAD_LEFT)),
                'name' => $name !== '' ? $name : 'Faculty Member',
                'department' => 'Computer Studies',
                'status' => 'Active',
            ]);
        }

        return $row;
    }

    private function isFacultyProfileComplete($row)
    {
        if (!$row || !is_array($row->config_payload)) {
            return false;
        }

        $formState = isset($row->config_payload['form_state']) && is_array($row->config_payload['form_state'])
            ? $row->config_payload['form_state']
            : [];

        return !empty($formState['profile_completed']);
    }

    /**
     * Faculty Load – assigned subjects/schedule.
     */
    public function facultyLoad()
    {
        $faculty = $this->currentFaculty();
        $subjects = $this->subjectsForFaculty($faculty)->get();
        return view('faculty.faculty-load', compact('subjects', 'faculty'));
    }

    /**
     * Download faculty load/schedule as CSV.
     */
    public function downloadLoad()
    {
        $faculty = $this->currentFaculty();
        $subjects = $this->subjectsForFaculty($faculty)->orderBy('code')->get();

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Subject Code', 'Subject Description', 'Units', 'Days', 'Time', 'Room No.', 'Year & Section']);

        foreach ($subjects as $subject) {
            fputcsv($handle, [
                (string) $subject->code,
                (string) $subject->name,
                number_format((float) $subject->units, 1),
                str_replace(',', ', ', (string) $subject->days),
                (string) $subject->formatted_time,
                (string) $subject->room,
                (string) $subject->year_section,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $stamp = now()->format('Ymd_His');
        $filename = 'faculty-load-' . $stamp . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Class List – subjects with enrolled students.
     */
    public function classList()
    {
        $faculty = $this->currentFaculty();
        $subjects = $this->subjectsForFaculty($faculty)
            ->with('students')
            ->get();

        $subjectStudents = $subjects->keyBy('id')->map(function ($s) {
            return $s->students->map(function ($st) {
                return [
                    'student_no' => $st->student_no,
                    'name'       => $st->name,
                    'program_block' => trim(($st->program ?: '-') . ' ' . ($st->year_level ?: '-')),
                    'status'     => 'Enrolled',
                ];
            })->values();
        });

        return view('faculty.class-list', compact('subjects', 'subjectStudents'));
    }

    /**
     * Calendar – university events calendar (JS-driven).
     */
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

        return view('faculty.calendar', compact('calendarEvents'));
    }

    /**
     * Grading Sheet – subjects with grading status.
     */
    public function gradingSheet()
    {
        $faculty = $this->currentFaculty();
        $subjects = $this->subjectsForFaculty($faculty)
            ->with(['students', 'studentGrades'])
            ->get();

        $gradingSubjects = $subjects->map(function ($subject) {
            $gradeMap = $subject->studentGrades->keyBy('student_id');

            $students = $subject->students->values()->map(function ($student) use ($gradeMap) {
                $grade = $gradeMap->get($student->id);

                return [
                    'id' => $student->id,
                    'student_no' => $student->student_no,
                    'name' => $student->name,
                    'prelim' => $grade ? number_format((float) $grade->prelim, 2) : '',
                    'midterm' => $grade ? number_format((float) $grade->midterm, 2) : '',
                    'final' => $grade ? number_format((float) $grade->final, 2) : '',
                    'final_average' => $grade ? number_format((float) $grade->final_average, 2) : '',
                    'remarks' => $grade ? $grade->remarks : '',
                ];
            });

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'units' => number_format((float) $subject->units, 1),
                'days' => str_replace(',', ', ', (string) $subject->days),
                'section' => trim(($subject->course ?: '') . ' ' . ($subject->year_section ?: '')),
                'semester' => (string) ($subject->semester ?: ''),
                'school_year' => (string) ($subject->school_year ?: ''),
                'status' => $subject->grading_status,
                'students' => $students,
            ];
        })->values();

        return view('faculty.grading-sheet', compact('subjects', 'gradingSubjects'));
    }

    /**
     * Persist faculty-submitted grades for a selected subject.
     */
    public function updateGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array',
        ]);

        $subject = $this->subjectsForFaculty($this->currentFaculty())
            ->with('students')
            ->findOrFail($request->input('subject_id'));
        $gradesInput = $request->input('grades', []);

        foreach ($subject->students as $student) {
            $row = $gradesInput[$student->id] ?? null;
            if (!$row) {
                continue;
            }

            $prelim = isset($row['prelim']) && $row['prelim'] !== '' ? (float) $row['prelim'] : null;
            $midterm = isset($row['midterm']) && $row['midterm'] !== '' ? (float) $row['midterm'] : null;
            $final = isset($row['final']) && $row['final'] !== '' ? (float) $row['final'] : null;

            if ($prelim === null || $midterm === null || $final === null) {
                continue;
            }

            if ($prelim < 1 || $prelim > 5 || $midterm < 1 || $midterm > 5 || $final < 1 || $final > 5) {
                continue;
            }

            $average = round(($prelim + $midterm + $final) / 3, 2);
            $remarks = $average <= 3.00 ? 'Passed' : 'Failed';

            StudentSubjectGrade::updateOrCreate(
                ['subject_id' => $subject->id, 'student_id' => $student->id],
                [
                    'prelim' => $prelim,
                    'midterm' => $midterm,
                    'final' => $final,
                    'final_average' => $average,
                    'remarks' => $remarks,
                ]
            );
        }

        $subject->grading_status = 'Submitted';
        $subject->save();

        return redirect()->route('faculty.grading-sheet')->with('success', 'Grades updated successfully.');
    }

    /**
     * Faculty Evaluation – subjects with mean scores.
     */
    public function evaluation(Request $request)
    {
        $selectedSubjectId = (int) $request->query('subject_id', 0);

        $subjects = $this->subjectsForFaculty($this->currentFaculty())
            ->with(['evaluations', 'students'])
            ->orderBy('name')
            ->get();

        $evaluationLibrary = [];
        $evaluationPanels = [];

        foreach ($subjects as $subject) {
            $evaluations = $subject->evaluations->sortBy('section')->values();
            $scoreGroups = [];
            $commentGroups = [];
            $criteriaTitles = [
                'A. Commitment',
                'B. Knowledge of Subject Matter',
                'C. Management of Learning',
                'D. Class Engagement',
            ];

            $baseMean = $evaluations->avg(function ($eval) {
                return (float) $eval->mean_score;
            });

            if (!$baseMean) {
                $baseMean = 4.1;
            }

            foreach ($criteriaTitles as $title) {
                $seed = crc32((string) $subject->id . '|' . $title);
                $offset = (($seed % 31) - 15) / 100;
                $derived = max(3.4, min(4.8, round(((float) $baseMean) + $offset, 2)));

                $scoreGroups[] = [
                    'title' => $title,
                    'mean_score' => $derived,
                    'interpretation' => $this->interpretationFromMean($derived),
                ];

                $derivedComments = $this->sampleCommentsForScore($derived);
            }

            // Comments view groups by section (not criteria), per latest UX direction.
            foreach ($evaluations as $eval) {
                $mean = (float) $eval->mean_score;
                $section = trim((string) ($eval->section ?: $subject->year_section ?: 'Section'));
                $sectionComments = $this->sampleCommentsForScore($mean ?: $baseMean);

                $commentGroups[] = [
                    'section' => $section,
                    'count' => count($sectionComments),
                    'comments' => $sectionComments,
                ];
            }

            if (!count($commentGroups)) {
                $fallbackSections = ['BSIT 4-A', 'BSCS 4-B', 'BSA 3-A'];
                foreach ($fallbackSections as $fallbackSection) {
                    $seed = crc32((string) $subject->id . '|comment|' . $fallbackSection);
                    $offset = (($seed % 31) - 15) / 100;
                    $derived = max(3.4, min(4.8, round(((float) $baseMean) + $offset, 2)));
                    $sectionComments = $this->sampleCommentsForScore($derived);

                    $commentGroups[] = [
                        'section' => $fallbackSection,
                        'count' => count($sectionComments),
                        'comments' => $sectionComments,
                    ];
                }
            }

            $responseCount = (int) $subject->students->count();
            $hasResults = count($scoreGroups) > 0;
            $status = 'Closed';

            if ($hasResults && $responseCount > 0) {
                $status = 'Published';
            } elseif ($hasResults) {
                $status = 'Draft';
            }

            $evaluationLibrary[] = [
                'id' => (int) $subject->id,
                'code' => (string) ($subject->code ?: strtoupper(substr((string) $subject->name, 0, 10))),
                'name' => (string) $subject->name,
                'status' => $status,
                'responses' => $responseCount,
                'has_results' => $hasResults,
            ];

            $evaluationPanels[$subject->id] = [
                'subject_id' => (int) $subject->id,
                'subject_name' => (string) $subject->name,
                'subject_code' => (string) ($subject->code ?: ''),
                'scores' => $scoreGroups,
                'comments' => $commentGroups,
            ];
        }

        if ($selectedSubjectId <= 0 && count($evaluationLibrary) > 0) {
            $selectedSubjectId = (int) $evaluationLibrary[0]['id'];
        }

        return view('faculty.evaluation', compact('evaluationLibrary', 'evaluationPanels', 'selectedSubjectId'));
    }

    /**
     * Faculty Profile summary view.
     */
    public function profile()
    {
        $faculty = $this->currentFaculty();

        if (!$faculty) {
            return redirect()->route('module.login', ['module' => 'faculty']);
        }

        $profileRow = $this->resolveFacultyProfileRow($faculty);
        if (!$this->isFacultyProfileComplete($profileRow)) {
            return redirect()->route('faculty.profile.edit');
        }

        $payload = is_array($profileRow->config_payload) ? $profileRow->config_payload : [];
        $formState = isset($payload['form_state']) && is_array($payload['form_state']) ? $payload['form_state'] : [];
        $detailRows = $this->mergeFacultyProfileSections(isset($payload['sections']) ? $payload['sections'] : []);

        return view('faculty.profile-view', compact('faculty', 'profileRow', 'formState', 'detailRows'));
    }

    /**
     * Faculty Profile step wizard.
     */
    public function editProfile()
    {
        $faculty = $this->currentFaculty();

        if (!$faculty) {
            return redirect()->route('module.login', ['module' => 'faculty']);
        }

        $profileRow = $this->resolveFacultyProfileRow($faculty);
        $payload = is_array($profileRow->config_payload) ? $profileRow->config_payload : [];
        $formState = isset($payload['form_state']) && is_array($payload['form_state']) ? $payload['form_state'] : [];
        $detailRows = $this->mergeFacultyProfileSections(isset($payload['sections']) ? $payload['sections'] : []);

        return view('faculty.profile', compact('faculty', 'profileRow', 'formState', 'detailRows'));
    }

    /**
     * Save Faculty Profile wizard payload.
     */
    public function updateProfile(Request $request)
    {
        $faculty = $this->currentFaculty();

        if (!$faculty) {
            return redirect()->route('module.login', ['module' => 'faculty']);
        }

        $profileRow = $this->resolveFacultyProfileRow($faculty);
        if (!$profileRow) {
            return redirect()->route('faculty.load')->withErrors(['profile' => 'Faculty profile storage is not available.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:190',
            'department' => 'required|string|max:190',
            'status' => 'required|string|in:Active,Inactive',
            'sections_json' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        ]);

        $sections = $this->defaultFacultyProfileSections();
        $sectionsRaw = $request->input('sections_json');
        if (!empty($sectionsRaw)) {
            $decoded = json_decode($sectionsRaw, true);
            if (is_array($decoded)) {
                $sections = $this->mergeFacultyProfileSections($decoded);
            }
        }

        $excludedKeys = ['_token', 'sections_json', 'profile_photo'];
        $formState = $request->except($excludedKeys);
        $formState['profile_completed'] = 1;

        if ($request->hasFile('profile_photo')) {
            $formState['profile_photo_path'] = $request->file('profile_photo')->store('faculty/photos', 'public');
        } elseif (!empty($profileRow->config_payload['form_state']['profile_photo_path'])) {
            $formState['profile_photo_path'] = (string) $profileRow->config_payload['form_state']['profile_photo_path'];
        }

        $profileRow->update([
            'name' => $validated['name'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'config_payload' => [
                'form_state' => $formState,
                'sections' => $sections,
            ],
        ]);

        $faculty->name = $validated['name'];
        $faculty->save();

        User::where('faculty_id', $faculty->id)->update(['name' => $validated['name']]);

        return redirect()->route('faculty.profile')->with('success', 'Faculty profile saved successfully.');
    }

    /**
     * Faculty Messaging
     */
    public function messaging()
    {
        return view('faculty.messaging');
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

        $this->syncFacultyNotificationsForUser($user);

        $notifications = $this->activeFacultyNotifications($user)
            ->map(function ($delivery) {
                $notification = $delivery->notification;

                return [
                    'delivery_id' => (int) $delivery->id,
                    'title' => $notification ? (string) $notification->title : 'New notification',
                    'source_url' => $notification ? (string) $notification->local_source_url : '',
                    'is_read' => !empty($delivery->read_at),
                    'dismiss_url' => route('faculty.notifications.dismiss', ['notificationDelivery' => $delivery->id], false),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'unread_count' => $this->facultyUnreadNotificationCount($user),
            'notifications' => $notifications,
        ]);
    }
}
