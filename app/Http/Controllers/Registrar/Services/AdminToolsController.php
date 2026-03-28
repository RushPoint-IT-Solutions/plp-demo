<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\SystemAnnouncement;
use App\SystemGradePosting;
use App\SystemSchoolSemester;
use App\ReportPermission;
use App\AcademicCalendarEvent;
use App\StudentProfile;
use App\User;
use App\UserAccountStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminToolsController extends Controller
{
    public function configuration()
    {
        $schoolSemRows = [];
        $gradePostingRows = [];

        if (Schema::hasTable('system_school_semesters')) {
            if (SystemSchoolSemester::query()->count() === 0) {
                $this->seedSchoolSemRows();
            }

            $schoolSemRows = SystemSchoolSemester::query()
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'sy' => (string) $row->school_year,
                        'semester' => (string) $row->semester,
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_grade_postings')) {
            if (SystemGradePosting::query()->count() === 0) {
                $this->seedGradePostingRows();
            }

            $gradePostingRows = SystemGradePosting::query()
                ->orderByDesc('date_from')
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'sy' => (string) $row->school_year,
                        'semester' => (string) $row->semester,
                        'period' => (string) $row->period,
                        'dateFrom' => optional($row->date_from)->format('Y-m-d') ?: '',
                    ];
                })
                ->values()
                ->all();
        }

        return view('registrar.admin-tools.system-config.configuration', compact('schoolSemRows', 'gradePostingRows'));
    }

    public function admissionConfig()
    {
        return view('registrar.admin-tools.system-config.admission-config');
    }

    public function academicCalendar()
    {
        $calendarRows = [];

        if (Schema::hasTable('academic_calendar_events')) {
            $calendarRows = AcademicCalendarEvent::query()
                ->orderBy('event_date')
                ->orderBy('time_from')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'date' => optional($event->event_date)->format('Y-m-d'),
                        'timeFrom' => $event->time_from ? substr((string) $event->time_from, 0, 5) : '',
                        'timeTo' => $event->time_to ? substr((string) $event->time_to, 0, 5) : '',
                        'event' => (string) $event->title,
                        'venue' => (string) ($event->venue ?? ''),
                        'inCharge' => (string) ($event->in_charge ?? ''),
                        'postUntil' => optional($event->post_until)->format('Y-m-d') ?: optional($event->event_date)->format('Y-m-d'),
                    ];
                })
                ->values()
                ->all();
        }

        return view('registrar.admin-tools.system-config.academic-calendar', compact('calendarRows'));
    }

    public function academicCalendarStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'timeFrom' => 'required|date_format:H:i',
            'timeTo' => 'required|date_format:H:i',
            'event' => 'required|string|max:190',
            'venue' => 'nullable|string|max:190',
            'inCharge' => 'nullable|string|max:190',
            'postUntil' => 'nullable|date',
        ]);

        $event = AcademicCalendarEvent::create([
            'event_date' => $validated['date'],
            'time_from' => $validated['timeFrom'],
            'time_to' => $validated['timeTo'],
            'title' => $validated['event'],
            'venue' => $validated['venue'] ?? null,
            'in_charge' => $validated['inCharge'] ?? null,
            'post_until' => $validated['postUntil'] ?? $validated['date'],
            'event_type' => 'Registrar Event',
            'is_active' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => $this->mapAcademicCalendarRow($event),
        ]);
    }

    public function academicCalendarUpdate(Request $request, AcademicCalendarEvent $academicCalendarEvent): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'timeFrom' => 'required|date_format:H:i',
            'timeTo' => 'required|date_format:H:i',
            'event' => 'required|string|max:190',
            'venue' => 'nullable|string|max:190',
            'inCharge' => 'nullable|string|max:190',
            'postUntil' => 'nullable|date',
        ]);

        $academicCalendarEvent->update([
            'event_date' => $validated['date'],
            'time_from' => $validated['timeFrom'],
            'time_to' => $validated['timeTo'],
            'title' => $validated['event'],
            'venue' => $validated['venue'] ?? null,
            'in_charge' => $validated['inCharge'] ?? null,
            'post_until' => $validated['postUntil'] ?? $validated['date'],
        ]);

        return response()->json([
            'ok' => true,
            'row' => $this->mapAcademicCalendarRow($academicCalendarEvent),
        ]);
    }

    public function academicCalendarDestroy(AcademicCalendarEvent $academicCalendarEvent): JsonResponse
    {
        $academicCalendarEvent->delete();

        return response()->json(['ok' => true]);
    }

    public function announcement()
    {
        $announcementRows = [];

        if (Schema::hasTable('system_announcements')) {
            if (SystemAnnouncement::query()->count() === 0) {
                $this->seedAnnouncements();
            }

            $announcementRows = SystemAnnouncement::query()
                ->orderByDesc('date_from')
                ->orderByDesc('id')
                ->get()
                ->map(function ($announcement) {
                    return $this->mapAnnouncementRow($announcement);
                })
                ->values()
                ->all();
        }

        return view('registrar.admin-tools.system-config.announcement', compact('announcementRows'));
    }

    public function announcementStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:40',
            'program' => 'nullable|string|max:120',
            'content' => 'required|string',
        ]);

        $announcement = SystemAnnouncement::create([
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $validated['type'] ?? 'Everyone',
            'program' => $validated['program'] ?? 'All Programs',
            'content' => $validated['content'],
        ]);

        return response()->json([
            'ok' => true,
            'row' => $this->mapAnnouncementRow($announcement),
        ]);
    }

    public function announcementUpdate(Request $request, SystemAnnouncement $systemAnnouncement): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:40',
            'program' => 'nullable|string|max:120',
            'content' => 'required|string',
        ]);

        $systemAnnouncement->update([
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $validated['type'] ?? 'Everyone',
            'program' => $validated['program'] ?? 'All Programs',
            'content' => $validated['content'],
        ]);

        return response()->json([
            'ok' => true,
            'row' => $this->mapAnnouncementRow($systemAnnouncement),
        ]);
    }

    public function announcementDestroy(SystemAnnouncement $systemAnnouncement): JsonResponse
    {
        $systemAnnouncement->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationSchoolSemStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
        ]);

        $row = SystemSchoolSemester::create($validated);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'sy' => (string) $row->school_year,
                'semester' => (string) $row->semester,
            ],
        ]);
    }

    public function configurationSchoolSemUpdate(Request $request, SystemSchoolSemester $systemSchoolSemester): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
        ]);

        $systemSchoolSemester->update($validated);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $systemSchoolSemester->id,
                'sy' => (string) $systemSchoolSemester->school_year,
                'semester' => (string) $systemSchoolSemester->semester,
            ],
        ]);
    }

    public function configurationSchoolSemDestroy(SystemSchoolSemester $systemSchoolSemester): JsonResponse
    {
        $systemSchoolSemester->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationGradePostingStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
            'period' => 'required|string|max:30',
            'date_from' => 'required|date',
        ]);

        $row = SystemGradePosting::create($validated);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'sy' => (string) $row->school_year,
                'semester' => (string) $row->semester,
                'period' => (string) $row->period,
                'dateFrom' => optional($row->date_from)->format('Y-m-d') ?: '',
            ],
        ]);
    }

    public function configurationGradePostingUpdate(Request $request, SystemGradePosting $systemGradePosting): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
            'period' => 'required|string|max:30',
            'date_from' => 'required|date',
        ]);

        $systemGradePosting->update($validated);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $systemGradePosting->id,
                'sy' => (string) $systemGradePosting->school_year,
                'semester' => (string) $systemGradePosting->semester,
                'period' => (string) $systemGradePosting->period,
                'dateFrom' => optional($systemGradePosting->date_from)->format('Y-m-d') ?: '',
            ],
        ]);
    }

    public function configurationGradePostingDestroy(SystemGradePosting $systemGradePosting): JsonResponse
    {
        $systemGradePosting->delete();

        return response()->json(['ok' => true]);
    }

    // Access Management
    public function userAccounts()
    {
        $users = User::query()
            ->orderBy('name')
            ->orderBy('username')
            ->limit(400)
            ->get(['id', 'username', 'name', 'module']);

        $statusMap = collect();
        if (Schema::hasTable('user_account_statuses')) {
            $statusMap = UserAccountStatus::query()
                ->whereIn('user_id', $users->pluck('id')->all())
                ->get(['user_id', 'is_inactive'])
                ->pluck('is_inactive', 'user_id');
        }

        $accountUsers = $users->map(function ($user) use ($statusMap) {
            $fullName = trim((string) ($user->name ?: $user->username));
            list($lastName, $firstName) = $this->splitUserName($fullName);

            return [
                'pk' => $user->id,
                'userId' => (string) $user->username,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'fullName' => $fullName,
                'userType' => ucfirst((string) ($user->module ?: 'user')),
                'inactive' => (bool) $statusMap->get($user->id, false),
            ];
        })->values()->all();

        return view('registrar.admin-tools.access-management.user-accounts', compact('accountUsers'));
    }

    public function userAccountsUpdate(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|max:190|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:190',
            'password' => 'nullable|string|min:6|max:190',
            'inactive' => 'nullable|boolean',
        ]);

        $user->username = (string) $validated['user_id'];
        if (!empty($validated['full_name'])) {
            $user->name = (string) $validated['full_name'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make((string) $validated['password']);
            $user->force_password_reset = false;
        }
        $user->save();

        if (Schema::hasTable('user_account_statuses')) {
            UserAccountStatus::updateOrCreate(
                ['user_id' => $user->id],
                ['is_inactive' => (bool) ($validated['inactive'] ?? false)]
            );
        }

        list($lastName, $firstName) = $this->splitUserName((string) ($user->name ?: $user->username));

        return response()->json([
            'ok' => true,
            'row' => [
                'pk' => $user->id,
                'userId' => (string) $user->username,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'fullName' => (string) ($user->name ?: $user->username),
                'userType' => ucfirst((string) ($user->module ?: 'user')),
                'inactive' => (bool) ($validated['inactive'] ?? false),
            ],
        ]);
    }

    public function userAccountsDestroy(Request $request, User $user): JsonResponse
    {
        if ($request->user() && (int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json(['ok' => true]);
    }

    public function reportAccess()
    {
        $users = User::query()
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'username', 'email', 'module']);

        $permissionsByUser = collect();
        if (Schema::hasTable('report_permissions')) {
            $permissionsByUser = ReportPermission::query()
                ->whereIn('user_id', $users->pluck('id')->all())
                ->where('is_allowed', true)
                ->get(['user_id', 'report_key'])
                ->groupBy('user_id')
                ->map(function ($rows) {
                    return $rows->pluck('report_key')->values()->all();
                });
        }

        $reportUsers = $users->map(function ($user) use ($permissionsByUser) {
            $displayName = $user->name ?: $user->username;
            return [
                'id' => $user->id,
                'name' => $displayName,
                'email' => $user->email ?: '-',
                'userType' => ucfirst((string) ($user->module ?: 'user')),
                'reportType' => ucfirst((string) ($user->module ?: 'Academics')) . ' Report',
                'permissions' => $permissionsByUser->get($user->id, []),
            ];
        })->values();

        return view('registrar.admin-tools.access-management.report-access', compact('reportUsers'));
    }

    public function reportAccessUpdate(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'report_keys' => 'nullable|array',
            'report_keys.*' => 'string|max:190',
        ]);

        $keys = collect($validated['report_keys'] ?? [])
            ->map(function ($key) {
                return trim((string) $key);
            })
            ->filter(function ($key) {
                return $key !== '';
            })
            ->unique()
            ->values();

        ReportPermission::query()->where('user_id', $user->id)->delete();

        foreach ($keys as $key) {
            ReportPermission::create([
                'user_id' => $user->id,
                'report_key' => $key,
                'report_type' => null,
                'is_allowed' => true,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    // Master Files
    public function facultyFile()
    {
        return view('registrar.admin-tools.master-files.faculty-file');
    }

    public function studentProfile()
    {
        $studentNo = request('student_id');
        $previewProfile = null;

        if (!empty($studentNo)) {
            $previewProfile = StudentProfile::where('student_no', $studentNo)->first();
        }

        if (!$previewProfile) {
            $previewProfile = StudentProfile::orderBy('id')->first();
        }

        return view('registrar.admin-tools.master-files.student-profile', compact('previewProfile'));
    }

    public function studentGradeFile()
    {
        return view('registrar.admin-tools.master-files.student-grade-file');
    }

    // Student Maintenance
    public function bedStudentStatus()
    {
        return view('registrar.admin-tools.student-maintenance.bed-student-status');
    }

    public function bedDays()
    {
        return view('registrar.admin-tools.student-maintenance.bed-days');
    }

    public function studentUpdate()
    {
        return view('registrar.admin-tools.student-maintenance.student-update');
    }

    private function mapAcademicCalendarRow(AcademicCalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'date' => optional($event->event_date)->format('Y-m-d') ?: '',
            'timeFrom' => $event->time_from ? substr((string) $event->time_from, 0, 5) : '',
            'timeTo' => $event->time_to ? substr((string) $event->time_to, 0, 5) : '',
            'event' => (string) $event->title,
            'venue' => (string) ($event->venue ?? ''),
            'inCharge' => (string) ($event->in_charge ?? ''),
            'postUntil' => optional($event->post_until)->format('Y-m-d') ?: optional($event->event_date)->format('Y-m-d'),
        ];
    }

    private function mapAnnouncementRow(SystemAnnouncement $announcement): array
    {
        return [
            'id' => $announcement->id,
            'from' => optional($announcement->date_from)->format('Y-m-d') ?: '',
            'to' => optional($announcement->date_to)->format('Y-m-d') ?: '',
            'title' => (string) $announcement->title,
            'type' => (string) ($announcement->announcement_type ?: 'Everyone'),
            'program' => (string) ($announcement->program ?: 'All Programs'),
            'content' => (string) $announcement->content,
        ];
    }

    private function splitUserName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['', ''];
        }

        if (strpos($fullName, ',') !== false) {
            $parts = explode(',', $fullName, 2);
            return [trim($parts[0]), trim($parts[1])];
        }

        $pieces = preg_split('/\s+/', $fullName) ?: [];
        $lastName = (string) array_pop($pieces);
        $firstName = trim(implode(' ', $pieces));

        return [$lastName, $firstName];
    }

    private function seedSchoolSemRows(): void
    {
        $rows = [
            ['school_year' => '2025-2026', 'semester' => 'First'],
            ['school_year' => '2025-2026', 'semester' => 'Second'],
            ['school_year' => '2026-2027', 'semester' => 'First'],
        ];

        foreach ($rows as $row) {
            SystemSchoolSemester::create($row);
        }
    }

    private function seedGradePostingRows(): void
    {
        $rows = [
            ['school_year' => '2025-2026', 'semester' => 'Second', 'period' => 'Prelim', 'date_from' => '2026-01-19'],
            ['school_year' => '2025-2026', 'semester' => 'Second', 'period' => 'Midterm', 'date_from' => '2026-02-20'],
            ['school_year' => '2025-2026', 'semester' => 'Second', 'period' => 'Final', 'date_from' => '2026-03-27'],
        ];

        foreach ($rows as $row) {
            SystemGradePosting::create($row);
        }
    }

    private function seedAnnouncements(): void
    {
        $rows = [
            [
                'date_from' => '2026-01-13',
                'date_to' => '2026-01-31',
                'title' => 'Academic Year 2025-2026 Midterm Examination',
                'announcement_type' => 'Everyone',
                'program' => 'All Programs',
                'content' => 'Midterm examinations will run from January 13 to January 31. Please settle pending requirements.',
            ],
            [
                'date_from' => '2026-02-15',
                'date_to' => '2026-02-22',
                'title' => 'Final Examination Week Advisory',
                'announcement_type' => 'Students',
                'program' => 'All Programs',
                'content' => 'Final examination schedule and assigned rooms are available at the registrar help desk.',
            ],
        ];

        foreach ($rows as $row) {
            SystemAnnouncement::create($row);
        }
    }
}
