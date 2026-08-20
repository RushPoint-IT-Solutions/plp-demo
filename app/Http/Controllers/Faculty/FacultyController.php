<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\AcademicCalendarEvent;
use App\MasterFacultyFile;
use App\FacultyEvaluation;
use App\GradeRule;
use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\SystemAnnouncement;
use App\Subject;
use App\StudentSubjectGrade;
use App\SubjectGradingStatus;
use App\TransmutationRule;
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
        $this->syncAnnouncementNotificationsForFaculty($user);
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
            ->where(function ($query) use ($today) {
                $query->whereNull('post_until')
                    ->orWhereDate('post_until', '>=', $today->toDateString());
            })
            ->visibleToAudience('faculty')
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

    private function syncAnnouncementNotificationsForFaculty($user)
    {
        if (!$user || $user->module !== 'faculty') {
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
            ->visibleToAudience(SystemAnnouncement::AUDIENCE_FACULTY)
            ->orderBy('id')
            ->get(['id', 'title', 'date_from', 'date_to', 'created_at', 'announcement_type_id', 'content']);

        foreach ($announcements as $announcement) {
            $titleValue = trim((string) $announcement->title);
            $title = $titleValue !== ''
                ? 'Announcement: ' . $titleValue
                : 'New Registrar Announcement';

            $dateFromLabel = optional($announcement->date_from)->format('M d, Y');
            $dateToLabel = optional($announcement->date_to)->format('M d, Y');

            $content = trim((string) $announcement->content);
            $message = $content !== '' ? $content : 'A registrar announcement is available';
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
            $today = now()->toDateString();

            $calendarEvents = AcademicCalendarEvent::query()
                ->where('is_active', true)
                ->where(function ($query) use ($today) {
                    $query->whereNull('post_until')
                        ->orWhereDate('post_until', '>=', $today);
                })
                ->visibleToAudience('faculty')
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

        return view('faculty.calendar', compact('calendarEvents'));
    }

    /**
     * Grading Sheet – subjects with grading status.
     */
    public function gradingSheet()
    {
        $faculty = $this->currentFaculty();
        $subjects = $this->subjectsForFaculty($faculty)
            ->with(['students', 'studentGrades.gradeRule'])
            ->get();
        $gradeRules = GradeRule::query()
            ->whereIn('code', ['OD', 'UD', 'INC', 'F', 'P'])
            ->orderByRaw("FIELD(code, 'OD', 'UD', 'INC', 'F', 'P')")
            ->get()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'code' => $rule->code,
                    'grade' => $rule->grade,
                    'remarks' => $rule->remarks,
                ];
            })
            ->values();

        $hasMidtermPostedColumn = Schema::hasColumn('student_subject_grades', 'midterm_posted_at');
        $hasFinalPostedColumn = Schema::hasColumn('student_subject_grades', 'final_posted_at');

        $gradingSubjects = $subjects->map(function ($subject) use ($faculty, $hasMidtermPostedColumn, $hasFinalPostedColumn) {
            $gradeMap = $subject->studentGrades->keyBy('student_id');

            $students = $subject->students->values()->map(function ($student) use ($gradeMap, $hasMidtermPostedColumn, $hasFinalPostedColumn) {
                $grade = $gradeMap->get($student->id);

                return [
                    'id' => $student->id,
                    'student_no' => $student->student_no,
                    'name' => $student->name,
                    'midterm' => ($grade && $grade->midterm !== null) ? number_format((float) $grade->midterm, 2) : '',
                    'final' => ($grade && $grade->final !== null) ? number_format((float) $grade->final, 2) : '',
                    'final_average' => ($grade && $grade->final_average !== null) ? number_format((float) $grade->final_average, 2) : '',
                    'grade_rule_id' => $grade ? $grade->grade_rule_id : '',
                    'grade_rule_code' => ($grade && $grade->gradeRule) ? $grade->gradeRule->code : '',
                    'status' => $grade ? (string) ($grade->status ?: $grade->remarks) : '',
                    'remarks' => $grade ? $grade->remarks : '',
                    'midterm_posted' => $hasMidtermPostedColumn && $grade && $grade->midterm_posted_at !== null,
                    'final_posted' => $hasFinalPostedColumn && $grade && $grade->final_posted_at !== null,
                    'midterm_posted_at' => ($hasMidtermPostedColumn && $grade && $grade->midterm_posted_at) ? (string) $grade->midterm_posted_at : '',
                    'final_posted_at' => ($hasFinalPostedColumn && $grade && $grade->final_posted_at) ? (string) $grade->final_posted_at : '',
                ];
            });

            $studentCount = $students->count();
            $midtermPostedCount = $students->filter(function ($student) {
                return !empty($student['midterm_posted']);
            })->count();
            $finalPostedCount = $students->filter(function ($student) {
                return !empty($student['final_posted']);
            })->count();
            $midtermDraftedCount = $students->filter(function ($student) {
                return isset($student['midterm']) && $student['midterm'] !== '' && empty($student['midterm_posted']);
            })->count();

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'units' => number_format((float) $subject->units, 1),
                'days' => str_replace(',', ', ', (string) $subject->days),
                'schedule' => trim(str_replace(',', ', ', (string) $subject->days) . ' ' . (string) $subject->formatted_time),
                'room' => (string) ($subject->room ?: ''),
                'professor' => (string) ($faculty->name ?: $subject->faculty ?: ''),
                'section' => trim(($subject->course ?: '') . ' ' . ($subject->year_section ?: '')),
                'semester' => (string) ($subject->semester ?: ''),
                'school_year' => (string) ($subject->school_year ?: ''),
                'status' => optional($subject->gradingStatusLookup)->label ?? 'Open For Encoding',
                'midterm_posted' => $studentCount > 0 && $midtermPostedCount === $studentCount,
                'midterm_drafted' => $midtermDraftedCount > 0,
                'final_posted' => $studentCount > 0 && $finalPostedCount === $studentCount,
                'submitted_at' => $subject->submitted_at ? $subject->submitted_at->format('Y-m-d H:i:s') : '',
                'dean_approved_at' => $subject->dean_approved_at ? $subject->dean_approved_at->format('Y-m-d H:i:s') : '',
                'registrar_finalized_at' => $subject->registrar_finalized_at ? $subject->registrar_finalized_at->format('Y-m-d H:i:s') : '',
                'transmutation_rules' => $this->transmutationRulesForSubject($subject),
                'students' => $students,
            ];
        })->values();

        return view('faculty.grading-sheet', compact('subjects', 'gradingSubjects', 'gradeRules'));
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

            $midterm = isset($row['midterm']) && $row['midterm'] !== '' ? (float) $row['midterm'] : null;
            $final = isset($row['final']) && $row['final'] !== '' ? (float) $row['final'] : null;
            $remarksInput = isset($row['remarks']) ? trim((string) $row['remarks']) : '';

            if ($midterm === null || $final === null) {
                continue;
            }

            if (!$this->isValidEncodedGrade($midterm) || !$this->isValidEncodedGrade($final)) {
                continue;
            }

            $average = $this->finalResultFromMidtermFinal($midterm, $final);
            $transmuted = $this->transmutedSemestralGrade($subject, [$midterm, $final]);
            $equivalentGrade = $transmuted['grade'] ?: $average;
            $status = $transmuted['remarks'] ?: $this->statusForFinalResult($equivalentGrade);
            $remarks = $remarksInput !== '' ? $remarksInput : $status;
            $gradeRule = $this->passFailGradeRuleForFinalResult($average);

            $payload = [
                'prelim' => null,
                'midterm' => $midterm,
                'final' => $final,
                'final_average' => $average,
                'grade_rule_id' => $gradeRule ? $gradeRule->id : null,
                'remarks' => $remarks,
            ];
            if (Schema::hasColumn('student_subject_grades', 'status')) {
                $payload['status'] = $status;
            }

            StudentSubjectGrade::updateOrCreate(
                ['subject_id' => $subject->id, 'student_id' => $student->id],
                $payload
            );
        }

        $subject->grading_status_id = SubjectGradingStatus::where('code', 'SUBMITTED')->value('id');
        $subject->submitted_at = now();
        $subject->dean_approved_by = null;
        $subject->dean_approved_at = null;
        $subject->registrar_finalized_by = null;
        $subject->registrar_finalized_at = null;
        $subject->grading_returned_by = null;
        $subject->grading_returned_at = null;
        $subject->grading_return_reason = null;
        $subject->save();

        return redirect()->route('faculty.grading-sheet')->with('success', 'Grades updated successfully.');
    }

    public function updateGradeRow(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required',
            'grade_rule_id' => 'nullable|exists:grade_rules,id',
            'midterm' => 'nullable|numeric|min:50|max:100',
            'final' => 'nullable|numeric|min:50|max:100',
            'remarks' => 'nullable|string|max:500',
            'draft' => 'nullable|boolean',
        ]);

        $subject = $this->subjectsForFaculty($this->currentFaculty())
            ->findOrFail($request->input('subject_id'));

        $studentId = $request->input('student_id');
        $gradeRule = $request->filled('grade_rule_id')
            ? GradeRule::find($request->input('grade_rule_id'))
            : null;
        $midterm   = $request->filled('midterm') ? (float) $request->input('midterm') : null;
        $final     = $request->input('final') !== null ? (float) $request->input('final') : null;
        $remarks = trim((string) $request->input('remarks', ''));
        $gradeRuleCode = $gradeRule ? strtoupper(trim((string) $gradeRule->code)) : '';
        $canSkipNumericGrades = in_array($gradeRuleCode, ['OD', 'UD', 'INC'], true);
        $existingGrade = StudentSubjectGrade::where('subject_id', $subject->id)
            ->where('student_id', $studentId)
            ->first();
        $subjectStatusLabel = strtolower(trim((string) (optional($subject->gradingStatusLookup)->label ?: $subject->grading_status)));
        $isReturnedForRevision = $subjectStatusLabel === 'returned for revision'
            || strpos($subjectStatusLabel, 'returned') !== false;

        $isDraft = in_array(strtolower((string) $request->input('draft', '')), ['1', 'true', 'on', 'yes'], true);

        if ($existingGrade && Schema::hasColumn('student_subject_grades', 'midterm_posted_at')
            && $existingGrade->midterm_posted_at !== null && $midterm !== null
            && (float) $existingGrade->midterm !== (float) $midterm && !$isReturnedForRevision) {
            return response()->json([
                'ok' => false,
                'message' => 'Midterm grade is already posted and cannot be changed.',
            ], 422);
        }
        if ($existingGrade && Schema::hasColumn('student_subject_grades', 'midterm_posted_at')
            && $existingGrade->midterm_posted_at !== null && !$isReturnedForRevision) {
            $midterm = $existingGrade->midterm !== null ? (float) $existingGrade->midterm : null;
        }

        if ($existingGrade && Schema::hasColumn('student_subject_grades', 'final_posted_at')
            && $existingGrade->final_posted_at !== null && $final !== null
            && (float) $existingGrade->final !== (float) $final && !$isReturnedForRevision) {
            return response()->json([
                'ok' => false,
                'message' => 'Final grade is already posted and cannot be changed.',
            ], 422);
        }
        if ($existingGrade && Schema::hasColumn('student_subject_grades', 'final_posted_at')
            && $existingGrade->final_posted_at !== null && !$isReturnedForRevision) {
            $final = $existingGrade->final !== null ? (float) $existingGrade->final : null;
        }

        if ($canSkipNumericGrades) {
            $midterm = null;
            $final = null;
        } elseif (!$isDraft && $midterm === null && $final === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Please enter at least one grade before saving.',
            ], 422);
        }

        $average = null;
        $status = null;
        if ($midterm !== null && $final !== null) {
            $average = $this->finalResultFromMidtermFinal($midterm, $final);
            $transmuted = $this->transmutedSemestralGrade($subject, [$midterm, $final]);
            $equivalentGrade = $transmuted['grade'] ?: $average;
            $status = $transmuted['remarks'] ?: $this->statusForFinalResult($equivalentGrade);
            if (!$gradeRule) {
                $gradeRule = $this->passFailGradeRuleForFinalResult($average);
            }
        }

        if ($remarks === '' && $status !== null) {
            $remarks = $status;
        }

        $payload = [
            'prelim'        => null,
            'midterm'       => $midterm,
            'final'         => $final,
            'final_average' => $average,
            'grade_rule_id' => $gradeRule ? $gradeRule->id : null,
            'remarks'       => $remarks !== '' ? $remarks : null,
            'draft_saved_at' => now(),
        ];
        if ($isReturnedForRevision && $existingGrade && Schema::hasColumn('student_subject_grades', 'midterm_posted_at')
            && $existingGrade->midterm_posted_at !== null && $midterm !== null
            && (float) $existingGrade->midterm !== (float) $midterm) {
            $payload['midterm_posted_at'] = null;
        }
        if ($isReturnedForRevision && $existingGrade && Schema::hasColumn('student_subject_grades', 'final_posted_at')
            && $existingGrade->final_posted_at !== null && $final !== null
            && (float) $existingGrade->final !== (float) $final) {
            $payload['final_posted_at'] = null;
        }
        if (Schema::hasColumn('student_subject_grades', 'status')) {
            $payload['status'] = $status;
        }

        $gradeRow = StudentSubjectGrade::updateOrCreate(
            ['subject_id' => $subject->id, 'student_id' => $studentId],
            $payload
        );

        return response()->json([
            'ok'            => true,
            'midterm'       => $midterm !== null ? number_format($midterm, 2) : '',
            'final'         => $final !== null ? number_format($final, 2) : '',
            'final_average' => $average !== null ? number_format($average, 2) : '',
            'grade_rule_id' => $gradeRule ? $gradeRule->id : '',
            'status'        => $status ?: '',
            'remarks'       => $remarks,
            'draft_saved_at' => now()->format('Y-m-d H:i:s'),
            'midterm_posted' => Schema::hasColumn('student_subject_grades', 'midterm_posted_at') && $gradeRow->midterm_posted_at !== null,
            'final_posted' => Schema::hasColumn('student_subject_grades', 'final_posted_at') && $gradeRow->final_posted_at !== null,
            'midterm_posted_at' => Schema::hasColumn('student_subject_grades', 'midterm_posted_at') && $gradeRow->midterm_posted_at ? (string) $gradeRow->midterm_posted_at : '',
            'final_posted_at' => Schema::hasColumn('student_subject_grades', 'final_posted_at') && $gradeRow->final_posted_at ? (string) $gradeRow->final_posted_at : '',
        ]);
    }

    private function isValidEncodedGrade($grade): bool
    {
        return is_numeric($grade) && (float) $grade >= 50 && (float) $grade <= 100;
    }

    private function finalResultFromMidtermFinal($midterm, $final): ?float
    {
        if (!is_numeric($midterm) || !is_numeric($final)) {
            return null;
        }

        return round((((float) $midterm + (float) $final) / 2), 2);
    }

    private function statusForFinalResult($grade): ?string
    {
        if (!is_numeric($grade)) {
            return null;
        }

        if ((float) $grade <= 5.0) {
            return (float) $grade <= 3.0 ? 'Passed' : 'Failed';
        }

        return (float) $grade >= 75.0 ? 'Passed' : 'Failed';
    }

    private function passFailGradeRuleForFinalResult($grade)
    {
        if (!is_numeric($grade)) {
            return null;
        }

        $passed = (float) $grade <= 5.0
            ? (float) $grade <= 3.0
            : (float) $grade >= 75.0;

        return GradeRule::where('code', $passed ? 'P' : 'F')->first();
    }

    private function transmutedSemestralGrade(Subject $subject, array $grades): array
    {
        $validGrades = collect($grades)
            ->filter(function ($value) {
                return $value !== null && $value !== '' && is_numeric($value);
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();

        if ($validGrades->count() === 0) {
            return ['grade' => null, 'remarks' => null];
        }

        $rawAverage = round($validGrades->avg(), 2);
        if ($rawAverage <= 5) {
            return [
                'grade' => $rawAverage,
                'remarks' => $rawAverage <= 3.00 ? 'Passed' : 'Failed',
            ];
        }

        $rule = $this->matchingTransmutationRule($subject, $rawAverage);
        if (!$rule) {
            $defaultRule = $this->defaultTransmutationRuleForRawAverage($rawAverage);

            return [
                'grade' => $defaultRule ? $defaultRule['grade'] : $rawAverage,
                'remarks' => $defaultRule ? $defaultRule['remarks'] : null,
            ];
        }

        $grade = round((float) $rule->transmuted_grade, 2);

        return [
            'grade' => $grade,
            'remarks' => trim((string) $rule->remarks) ?: ($grade <= 3.00 ? 'Passed' : 'Failed'),
        ];
    }

    private function matchingTransmutationRule(Subject $subject, float $rawAverage)
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
                ->whereIn('term', $this->transmutationTermAliases($subject->semester));
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

        $global = clone $base;
        if (Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
            $global->whereNull('academic_term_id');
        }
        if (Schema::hasColumn('transmutation_rules', 'course_id')) {
            $global->whereNull('course_id');
        }
        if (Schema::hasColumn('transmutation_rules', 'school_year')) {
            $global->whereNull('school_year');
        }
        if (Schema::hasColumn('transmutation_rules', 'term')) {
            $global->whereNull('term');
        }
        if (Schema::hasColumn('transmutation_rules', 'program')) {
            $global->whereNull('program');
        }

        return $global->orderByDesc('initial_from')->first();
    }

    private function defaultTransmutationBands(): array
    {
        return [
            ['from' => 97.50, 'to' => 100.00, 'grade' => 1.00, 'remarks' => 'Passed'],
            ['from' => 94.50, 'to' => 97.49, 'grade' => 1.25, 'remarks' => 'Passed'],
            ['from' => 91.50, 'to' => 94.49, 'grade' => 1.50, 'remarks' => 'Passed'],
            ['from' => 88.50, 'to' => 91.49, 'grade' => 1.75, 'remarks' => 'Passed'],
            ['from' => 85.50, 'to' => 88.49, 'grade' => 2.00, 'remarks' => 'Passed'],
            ['from' => 82.50, 'to' => 85.49, 'grade' => 2.25, 'remarks' => 'Passed'],
            ['from' => 79.50, 'to' => 82.49, 'grade' => 2.50, 'remarks' => 'Passed'],
            ['from' => 76.50, 'to' => 79.49, 'grade' => 2.75, 'remarks' => 'Passed'],
            ['from' => 74.50, 'to' => 76.49, 'grade' => 3.00, 'remarks' => 'Passed'],
            ['from' => 0.00, 'to' => 74.49, 'grade' => 5.00, 'remarks' => 'Failed'],
        ];
    }

    private function defaultTransmutationRuleForRawAverage(float $rawAverage): ?array
    {
        foreach ($this->defaultTransmutationBands() as $band) {
            if ($rawAverage >= $band['from'] && $rawAverage <= $band['to']) {
                return $band;
            }
        }

        return null;
    }

    private function transmutationRulesForSubject(Subject $subject): array
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return $this->defaultTransmutationBands();
        }

        $base = TransmutationRule::query();

        if (Schema::hasColumn('transmutation_rules', 'academic_term_id') && !empty($subject->academic_term_id)) {
            $base->where('academic_term_id', (int) $subject->academic_term_id);
        } elseif (Schema::hasColumn('transmutation_rules', 'school_year') && Schema::hasColumn('transmutation_rules', 'term')) {
            $base->where('school_year', (string) $subject->school_year)
                ->whereIn('term', $this->transmutationTermAliases($subject->semester));
        }

        if (Schema::hasColumn('transmutation_rules', 'course_id') && !empty($subject->course_id)) {
            $courseRules = (clone $base)
                ->where('course_id', (int) $subject->course_id)
                ->orderByDesc('initial_from')
                ->get();

            if ($courseRules->isNotEmpty()) {
                return $this->formatTransmutationRules($courseRules);
            }
        }

        if (Schema::hasColumn('transmutation_rules', 'program')) {
            $program = trim((string) ($subject->course ?: $subject->code));
            if ($program !== '') {
                $programRules = (clone $base)
                    ->where('program', $program)
                    ->orderByDesc('initial_from')
                    ->get();

                if ($programRules->isNotEmpty()) {
                    return $this->formatTransmutationRules($programRules);
                }
            }
        }

        $global = TransmutationRule::query();
        if (Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
            $global->whereNull('academic_term_id');
        }
        if (Schema::hasColumn('transmutation_rules', 'course_id')) {
            $global->whereNull('course_id');
        }
        if (Schema::hasColumn('transmutation_rules', 'school_year')) {
            $global->whereNull('school_year');
        }
        if (Schema::hasColumn('transmutation_rules', 'term')) {
            $global->whereNull('term');
        }
        if (Schema::hasColumn('transmutation_rules', 'program')) {
            $global->whereNull('program');
        }

        $globalRules = $global->orderByDesc('initial_from')->get();

        if ($globalRules->isEmpty()) {
            return $this->defaultTransmutationBands();
        }

        return $this->formatTransmutationRules($globalRules);
    }

    private function formatTransmutationRules($rules): array
    {
        return $rules->map(function ($rule) {
            return [
                'from' => (float) $rule->initial_from,
                'to' => (float) $rule->initial_to,
                'grade' => (float) $rule->transmuted_grade,
                'remarks' => trim((string) $rule->remarks),
            ];
        })->values()->all();
    }

    private function transmutationTermAliases($semester): array
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


    public function submitGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'phase' => 'nullable|in:midterm,final',
        ]);

        $phase = $request->input('phase', 'final');

        $subject = $this->subjectsForFaculty($this->currentFaculty())
            ->with(['students', 'studentGrades.gradeRule'])
            ->findOrFail($request->input('subject_id'));

        $gradeMap = $subject->studentGrades->keyBy('student_id');
        $missing = [];

        foreach ($subject->students as $student) {
            $grade = $gradeMap->get($student->id);

            $gradeRuleCode = ($grade && $grade->gradeRule) ? strtoupper(trim((string) $grade->gradeRule->code)) : '';
            $canSkipNumericGrades = in_array($gradeRuleCode, ['OD', 'UD', 'INC'], true);
            $hasMidterm = $grade && $grade->midterm !== null && $grade->midterm !== '';
            $hasFinal   = $grade && $grade->final !== null && $grade->final !== '';
            $midtermPosted = $grade && Schema::hasColumn('student_subject_grades', 'midterm_posted_at')
                ? $grade->midterm_posted_at !== null
                : false;

            if ($phase === 'midterm' && !$canSkipNumericGrades && !$hasMidterm) {
                $missing[] = $student->name . ' (missing: Midterm)';
            }

            if ($phase === 'final' && !$canSkipNumericGrades && !$midtermPosted) {
                $missing[] = $student->name . ' (midterm not yet posted)';
                continue;
            }

            if ($phase === 'final' && !$canSkipNumericGrades && (!$hasMidterm || !$hasFinal)) {
                $missing[] = $student->name . ' (missing: '
                    . (!$hasMidterm ? 'Midterm ' : '')
                    . (!$hasFinal ? 'Final' : '')
                    . ')';
            }
        }

        if (!empty($missing)) {
            return response()->json([
                'ok'      => false,
                'message' => $phase === 'midterm'
                    ? 'Some students have missing midterm grades. Please complete them before posting.'
                    : 'Some students have missing final grades. Please complete midterm and final grades before posting.',
                'missing' => $missing,
            ], 422);
        }

        $postedAt = now();
        $postedColumn = $phase === 'midterm' ? 'midterm_posted_at' : 'final_posted_at';

        if (Schema::hasColumn('student_subject_grades', $postedColumn)) {
            foreach ($subject->students as $student) {
                $grade = $gradeMap->get($student->id);
                if (!$grade) {
                    continue;
                }

                $payload = [$postedColumn => $postedAt];

                if ($phase === 'final' && $grade->midterm !== null && $grade->final !== null) {
                    $average = $this->finalResultFromMidtermFinal($grade->midterm, $grade->final);
                    $transmuted = $this->transmutedSemestralGrade($subject, [$grade->midterm, $grade->final]);
                    $equivalentGrade = $transmuted['grade'] ?: $average;
                    $payload['final_average'] = $average;

                    if (Schema::hasColumn('student_subject_grades', 'status')) {
                        $payload['status'] = $transmuted['remarks'] ?: $this->statusForFinalResult($equivalentGrade);
                    }
                }

                $grade->fill($payload);
                $grade->save();
            }
        }

        if ($phase === 'final') {
            $subject->grading_status_id = \DB::table('subject_grading_statuses')
                ->whereRaw('UPPER(code) = ?', ['SUBMITTED'])
                ->value('id');
            $subject->submitted_at = $postedAt;
            $subject->dean_approved_by = null;
            $subject->dean_approved_at = null;
            $subject->registrar_finalized_by = null;
            $subject->registrar_finalized_at = null;
            $subject->grading_returned_by = null;
            $subject->grading_returned_at = null;
            $subject->grading_return_reason = null;
            $subject->save();
        }

        return response()->json([
            'ok'      => true,
            'phase' => $phase,
            'posted_at' => $postedAt->format('Y-m-d H:i:s'),
            'message' => $phase === 'midterm'
                ? 'Midterm grades posted successfully.'
                : 'Final grades posted successfully.',
        ]);
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
                    'message' => $notification ? (string) $notification->message : '',
                    'source_url' => $notification ? (string) $notification->local_source_url : '',
                    'source_module' => $notification ? (string) $notification->source_module : '',
                    'source_reference' => $notification ? (string) $notification->source_reference : '',
                    'is_read' => !empty($delivery->read_at),
                    'dismiss_url' => route('faculty.notifications.dismiss', ['notificationDelivery' => $delivery->id], false),
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
            'unread_count' => $this->facultyUnreadNotificationCount($user),
            'notifications' => $notifications,
        ]);
    }
}
