<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\AcademicCalendarEvent;
use App\FacultyEvaluation;
use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\Subject;
use App\StudentSubjectGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FacultyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $this->syncEvaluationNotificationsForFaculty($user);

            view()->share('facultyNotifications', $this->activeFacultyNotifications($user));
            view()->share('facultyUnreadNotificationCount', $this->facultyUnreadNotificationCount($user));

            return $next($request);
        });
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

        $subjectQuery = $this->subjectsForFaculty($this->currentFaculty());
        if ($selectedSubjectId > 0) {
            $subjectQuery->where('id', $selectedSubjectId);
        }

        $subjects = $subjectQuery
            ->with('evaluations')
            ->get();

        $evaluationDetails = [];
        $hasEvaluationRows = false;
        foreach ($subjects as $subject) {
            foreach ($subject->evaluations as $eval) {
                $hasEvaluationRows = true;
                $base = (float) $eval->mean_score;
                $criteria = [
                    ['label' => 'A. Commitment', 'score' => max(1, min(5, round($base - 0.1, 2)))],
                    ['label' => 'B. Knowledge of Subject Matter', 'score' => max(1, min(5, round($base + 0.1, 2)))],
                    ['label' => 'C. Knowledge of Subject Matter', 'score' => max(1, min(5, round($base - 0.2, 2)))],
                    ['label' => 'D. Management of Learning', 'score' => max(1, min(5, round($base, 2)))],
                ];

                $overall = round(collect($criteria)->avg('score'), 2);
                $interpretation = $overall >= 4.5 ? 'Outstanding' : ($overall >= 4.0 ? 'Very Satisfactory' : ($overall >= 3.0 ? 'Satisfactory' : 'Needs Improvement'));

                $evaluationDetails[$eval->id] = [
                    'subject' => $subject->name,
                    'section' => $eval->section,
                    'criteria' => $criteria,
                    'overall' => $overall,
                    'interpretation' => $interpretation,
                ];
            }
        }

        $subjectOptions = $this->subjectsForFaculty($this->currentFaculty())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('faculty.evaluation', compact('subjects', 'evaluationDetails', 'subjectOptions', 'selectedSubjectId', 'hasEvaluationRows'));
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

        $this->syncEvaluationNotificationsForFaculty($user);

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
