<?php

namespace App\Http\Controllers\Registrar\Services;

use App\BedDay;
use App\BedStudentStatus;
use App\Faculty;
use App\Http\Controllers\Controller;
use App\MasterFacultyFile;
use App\MasterStudentGradeFile;
use App\MasterStudentProfileFile;
use App\Student;
use App\StudentUpdateRun;
use App\SystemAnnouncement;
use App\SystemGradePosting;
use App\SystemSchoolSemester;
use App\ReportPermission;
use App\AcademicCalendarEvent;
use App\Course;
use App\StudentProfile;
use App\User;
use App\UserAccountStatus;
use App\YearBlock;
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

        $programValue = isset($validated['program']) ? trim((string) $validated['program']) : '';
        $programValue = $programValue !== '' ? $programValue : 'All Programs';
        $courseId = null;

        if (strtolower($programValue) !== 'all programs') {
            $courseId = $this->resolveCourseId($programValue);

            if (!$courseId) {
                return response()->json([
                    'message' => 'The selected program is invalid.',
                    'errors' => [
                        'program' => ['Program must match an existing course code or name.'],
                    ],
                ], 422);
            }
        }

        $announcement = SystemAnnouncement::create([
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $validated['type'] ?? 'Everyone',
            'program' => $programValue,
            'course_id' => $courseId,
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

        $programValue = isset($validated['program']) ? trim((string) $validated['program']) : '';
        $programValue = $programValue !== '' ? $programValue : 'All Programs';
        $courseId = null;

        if (strtolower($programValue) !== 'all programs') {
            $courseId = $this->resolveCourseId($programValue);

            if (!$courseId) {
                return response()->json([
                    'message' => 'The selected program is invalid.',
                    'errors' => [
                        'program' => ['Program must match an existing course code or name.'],
                    ],
                ], 422);
            }
        }

        $systemAnnouncement->update([
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $validated['type'] ?? 'Everyone',
            'program' => $programValue,
            'course_id' => $courseId,
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
        $ffRows = [];
        $cfgFaculty = null;
        $cfgFormState = [];
        $cfgDetailRows = $this->defaultFacultyConfigSections();

        if (Schema::hasTable('master_faculty_files')) {
            if (MasterFacultyFile::query()->count() === 0) {
                $this->seedMasterFacultyFiles();
            }

            $ffRows = MasterFacultyFile::query()
                ->orderBy('name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => (string) $row->code,
                        'name' => (string) $row->name,
                        'department' => (string) $row->department,
                        'status' => (string) $row->status,
                    ];
                })
                ->values()
                ->all();

            if (request('view') === 'config') {
                $cfgId = request('id');
                if (!empty($cfgId)) {
                    $cfgFaculty = MasterFacultyFile::query()->find($cfgId);
                }

                if (!$cfgFaculty && request('code')) {
                    $cfgFaculty = MasterFacultyFile::query()
                        ->where('code', request('code'))
                        ->first();
                }

                if ($cfgFaculty) {
                    $payload = is_array($cfgFaculty->config_payload) ? $cfgFaculty->config_payload : [];
                    $storedFormState = $payload['form_state'] ?? [];
                    $storedSections = $payload['sections'] ?? [];

                    if (is_array($storedFormState)) {
                        $cfgFormState = $storedFormState;
                    }

                    if (is_array($storedSections)) {
                        foreach ($cfgDetailRows as $sectionKey => $defaultRows) {
                            if (isset($storedSections[$sectionKey]) && is_array($storedSections[$sectionKey])) {
                                $cfgDetailRows[$sectionKey] = $storedSections[$sectionKey];
                            }
                        }
                    }
                }
            }
        }

        return view('registrar.admin-tools.master-files.faculty-file', compact('ffRows', 'cfgFaculty', 'cfgFormState', 'cfgDetailRows'));
    }

    public function facultyFileStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:60|unique:master_faculty_files,code',
            'name' => 'required|string|max:190',
            'department' => 'required|string|max:190',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $sourceFacultyId = Faculty::query()
            ->where('code', $validated['code'])
            ->orWhere('name', $validated['name'])
            ->value('id');

        $row = MasterFacultyFile::create([
            'code' => $validated['code'],
            'source_faculty_id' => $sourceFacultyId ?: null,
            'name' => $validated['name'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'snapshot_taken_at' => now(),
            'is_snapshot' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'department' => (string) $row->department,
                'status' => (string) $row->status,
            ],
        ]);
    }

    public function facultyFileUpdate(Request $request, MasterFacultyFile $masterFacultyFile): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:60|unique:master_faculty_files,code,' . $masterFacultyFile->id,
            'name' => 'required|string|max:190',
            'department' => 'required|string|max:190',
            'status' => 'required|string|in:Active,Inactive',
            'config_payload' => 'nullable|array',
            'config_payload.form_state' => 'nullable|array',
            'config_payload.sections' => 'nullable|array',
        ]);

        $sourceFacultyId = Faculty::query()
            ->where('code', $validated['code'])
            ->orWhere('name', $validated['name'])
            ->value('id');

        $updateData = [
            'code' => $validated['code'],
            'source_faculty_id' => $sourceFacultyId ?: null,
            'name' => $validated['name'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'is_snapshot' => true,
        ];

        if (array_key_exists('config_payload', $validated)) {
            $updateData['config_payload'] = $validated['config_payload'];
        }

        $masterFacultyFile->update($updateData);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $masterFacultyFile->id,
                'code' => (string) $masterFacultyFile->code,
                'name' => (string) $masterFacultyFile->name,
                'department' => (string) $masterFacultyFile->department,
                'status' => (string) $masterFacultyFile->status,
            ],
        ]);
    }

    public function facultyFileDestroy(MasterFacultyFile $masterFacultyFile): JsonResponse
    {
        $masterFacultyFile->delete();

        return response()->json(['ok' => true]);
    }

    public function studentProfile()
    {
        $spRows = [];

        if (Schema::hasTable('master_student_profiles')) {
            if (MasterStudentProfileFile::query()->count() === 0) {
                $this->seedMasterStudentProfileFiles();
            }

            $spRows = MasterStudentProfileFile::query()
                ->orderBy('student_name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'studentId' => (string) $row->student_no,
                        'name' => (string) $row->student_name,
                        'course' => (string) $row->course,
                        'yearLevel' => (string) $row->year_level,
                    ];
                })
                ->values()
                ->all();
        }

        $studentNo = request('student_id');
        $previewProfile = null;

        if (!empty($studentNo)) {
            $previewProfile = StudentProfile::where('student_no', $studentNo)->first();
        }

        if (!$previewProfile) {
            $previewProfile = StudentProfile::orderBy('id')->first();
        }

        return view('registrar.admin-tools.master-files.student-profile', compact('previewProfile', 'spRows'));
    }

    public function studentProfileStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:80|unique:master_student_profiles,student_no',
            'name' => 'required|string|max:190',
            'course' => 'required|string|max:190',
            'year_level' => 'required|string|max:30',
        ]);

        $sourceStudentId = Student::query()
            ->where('student_no', $validated['student_id'])
            ->value('id');

        $row = MasterStudentProfileFile::create([
            'student_no' => $validated['student_id'],
            'source_student_id' => $sourceStudentId ?: null,
            'student_name' => $validated['name'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'snapshot_taken_at' => now(),
            'is_snapshot' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'studentId' => (string) $row->student_no,
                'name' => (string) $row->student_name,
                'course' => (string) $row->course,
                'yearLevel' => (string) $row->year_level,
            ],
        ]);
    }

    public function studentProfileUpdate(Request $request, MasterStudentProfileFile $masterStudentProfile): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:80|unique:master_student_profiles,student_no,' . $masterStudentProfile->id,
            'name' => 'required|string|max:190',
            'course' => 'required|string|max:190',
            'year_level' => 'required|string|max:30',
        ]);

        $sourceStudentId = Student::query()
            ->where('student_no', $validated['student_id'])
            ->value('id');

        $masterStudentProfile->update([
            'student_no' => $validated['student_id'],
            'source_student_id' => $sourceStudentId ?: null,
            'student_name' => $validated['name'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'is_snapshot' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $masterStudentProfile->id,
                'studentId' => (string) $masterStudentProfile->student_no,
                'name' => (string) $masterStudentProfile->student_name,
                'course' => (string) $masterStudentProfile->course,
                'yearLevel' => (string) $masterStudentProfile->year_level,
            ],
        ]);
    }

    public function studentProfileDestroy(MasterStudentProfileFile $masterStudentProfile): JsonResponse
    {
        $masterStudentProfile->delete();

        return response()->json(['ok' => true]);
    }

    public function studentGradeFile()
    {
        $sgfRows = [];

        if (Schema::hasTable('master_student_grade_files')) {
            if (MasterStudentGradeFile::query()->count() === 0) {
                $this->seedMasterStudentGradeFiles();
            }

            $sgfRows = MasterStudentGradeFile::query()
                ->orderBy('student_name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'studentId' => (string) $row->student_no,
                        'name' => (string) $row->student_name,
                        'course' => (string) $row->course,
                        'yearLevel' => (string) $row->year_level,
                    ];
                })
                ->values()
                ->all();
        }

        return view('registrar.admin-tools.master-files.student-grade-file', compact('sgfRows'));
    }

    public function studentGradeFileStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:80|unique:master_student_grade_files,student_no',
            'name' => 'required|string|max:190',
            'course' => 'required|string|max:190',
            'year_level' => 'required|string|max:30',
        ]);

        $sourceStudentId = Student::query()
            ->where('student_no', $validated['student_id'])
            ->value('id');

        $row = MasterStudentGradeFile::create([
            'student_no' => $validated['student_id'],
            'source_student_id' => $sourceStudentId ?: null,
            'student_name' => $validated['name'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'snapshot_taken_at' => now(),
            'is_snapshot' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'studentId' => (string) $row->student_no,
                'name' => (string) $row->student_name,
                'course' => (string) $row->course,
                'yearLevel' => (string) $row->year_level,
            ],
        ]);
    }

    public function studentGradeFileUpdate(Request $request, MasterStudentGradeFile $masterStudentGradeFile): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:80|unique:master_student_grade_files,student_no,' . $masterStudentGradeFile->id,
            'name' => 'required|string|max:190',
            'course' => 'required|string|max:190',
            'year_level' => 'required|string|max:30',
        ]);

        $sourceStudentId = Student::query()
            ->where('student_no', $validated['student_id'])
            ->value('id');

        $masterStudentGradeFile->update([
            'student_no' => $validated['student_id'],
            'source_student_id' => $sourceStudentId ?: null,
            'student_name' => $validated['name'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'is_snapshot' => true,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $masterStudentGradeFile->id,
                'studentId' => (string) $masterStudentGradeFile->student_no,
                'name' => (string) $masterStudentGradeFile->student_name,
                'course' => (string) $masterStudentGradeFile->course,
                'yearLevel' => (string) $masterStudentGradeFile->year_level,
            ],
        ]);
    }

    public function studentGradeFileDestroy(MasterStudentGradeFile $masterStudentGradeFile): JsonResponse
    {
        $masterStudentGradeFile->delete();

        return response()->json(['ok' => true]);
    }

    // Student Maintenance
    public function bedStudentStatus()
    {
        $bsRows = [];

        if (Schema::hasTable('bed_student_statuses')) {
            if (BedStudentStatus::query()->count() === 0) {
                $this->seedBedStudentStatuses();
            }

            $bsRows = BedStudentStatus::query()
                ->orderBy('student_name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'studentId' => (string) $row->student_no,
                        'name' => (string) $row->student_name,
                        'course' => (string) ($row->course ?: '-'),
                        'yearLevel' => (string) ($row->year_level ?: '-'),
                        'section' => (string) ($row->section ?: ''),
                        'schoolYear' => (string) ($row->school_year ?: ''),
                        'term' => (string) ($row->term ?: ''),
                        'noPayment' => (bool) $row->no_payment,
                        'noSection' => (bool) $row->no_section,
                    ];
                })
                ->values()
                ->all();
        }

        $bsSchoolYears = collect($bsRows)->pluck('schoolYear')->filter()->unique()->values()->all();
        $bsTerms = collect($bsRows)->pluck('term')->filter()->unique()->values()->all();
        $bsYearLevels = collect($bsRows)->pluck('yearLevel')->filter(function ($value) {
            return trim((string) $value) !== '' && $value !== '-';
        })->unique()->values()->all();
        $bsSections = collect($bsRows)->pluck('section')->filter()->unique()->values()->all();

        if (!count($bsSchoolYears)) {
            $bsSchoolYears = ['2025-2026'];
        }
        if (!count($bsTerms)) {
            $bsTerms = ['First', 'Second'];
        }

        return view('registrar.admin-tools.student-maintenance.bed-student-status', compact('bsRows', 'bsSchoolYears', 'bsTerms', 'bsYearLevels', 'bsSections'));
    }

    public function bedStudentStatusUpdate(Request $request, BedStudentStatus $bedStudentStatus): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:80',
            'name' => 'required|string|max:190',
            'course' => 'required|string|max:190',
            'year_level' => 'required|string|max:30',
            'section' => 'nullable|string|max:20',
        ]);

        $bedStudentStatus->update([
            'student_no' => $validated['student_id'],
            'student_name' => $validated['name'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'section' => $validated['section'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function bedStudentStatusDestroy(BedStudentStatus $bedStudentStatus): JsonResponse
    {
        $bedStudentStatus->delete();

        return response()->json(['ok' => true]);
    }

    public function bedDays()
    {
        $bedDayRows = [];

        if (Schema::hasTable('bed_days')) {
            if (BedDay::query()->count() === 0) {
                $this->seedBedDays();
            }

            $bedDayRows = BedDay::query()
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'sy' => (string) $row->school_year,
                        'sem' => (string) $row->semester,
                        'month' => (string) $row->month_name,
                        'days' => (string) $row->number_of_days,
                    ];
                })
                ->values()
                ->all();
        }

        return view('registrar.admin-tools.student-maintenance.bed-days', compact('bedDayRows'));
    }

    public function bedDaysStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
            'month_name' => 'required|string|max:30',
            'number_of_days' => 'required|integer|min:0|max:31',
        ]);

        $row = BedDay::create($validated);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $row->id,
                'sy' => (string) $row->school_year,
                'sem' => (string) $row->semester,
                'month' => (string) $row->month_name,
                'days' => (string) $row->number_of_days,
            ],
        ]);
    }

    public function bedDaysUpdate(Request $request, BedDay $bedDay): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
            'month_name' => 'required|string|max:30',
            'number_of_days' => 'required|integer|min:0|max:31',
        ]);

        $bedDay->update($validated);

        return response()->json(['ok' => true]);
    }

    public function bedDaysDestroy(BedDay $bedDay): JsonResponse
    {
        $bedDay->delete();

        return response()->json(['ok' => true]);
    }

    public function studentUpdate()
    {
        $courseOptions = Course::query()
            ->join('students', 'students.course_id', '=', 'courses.id')
            ->selectRaw("COALESCE(NULLIF(TRIM(courses.code), ''), courses.name) as course_option")
            ->distinct()
            ->orderBy('course_option')
            ->pluck('course_option')
            ->values()
            ->all();

        $operatorOptions = User::query()
            ->whereIn('module', ['registrar', 'admin'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return view('registrar.admin-tools.student-maintenance.student-update', compact('courseOptions', 'operatorOptions'));
    }

    public function studentUpdateRun(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action_name' => 'required|string|max:80',
            'run_mode' => 'nullable|string|max:40',
            'school_year' => 'nullable|string|max:30',
            'term' => 'nullable|string|max:30',
            'period' => 'nullable|string|max:80',
            'operator' => 'nullable|string|max:120',
            'course' => 'nullable|string|max:120',
            'year_level' => 'nullable|string|max:30',
            'section' => 'nullable|string|max:20',
            'student_no' => 'nullable|string|max:80',
            'include_unpaid_only' => 'nullable|boolean',
            'active_only' => 'nullable|boolean',
        ]);

        $query = Student::query();

        if (!empty($validated['school_year'])) {
            $query->whereHas('academicTerm', function ($termQuery) use ($validated) {
                $termQuery->where('school_year', trim((string) $validated['school_year']));
            });
        }

        if (!empty($validated['term'])) {
            $query->whereHas('academicTerm', function ($termQuery) use ($validated) {
                $termQuery->where('term', trim((string) $validated['term']));
            });
        }

        if (!empty($validated['course'])) {
            $resolvedCourseId = $this->resolveCourseId($validated['course']);

            if ($resolvedCourseId) {
                $query->where('course_id', $resolvedCourseId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($validated['year_level'])) {
            $resolvedYearBlockId = $this->resolveYearBlockId($validated['year_level']);

            if ($resolvedYearBlockId) {
                $query->where('year_block_id', $resolvedYearBlockId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($validated['student_no'])) {
            $query->where('student_no', $validated['student_no']);
        }

        $affectedCount = (int) $query->count();

        if (Schema::hasTable('student_update_runs')) {
            StudentUpdateRun::create([
                'action_name' => $validated['action_name'],
                'run_mode' => $validated['run_mode'] ?? null,
                'school_year' => $validated['school_year'] ?? null,
                'term' => $validated['term'] ?? null,
                'period' => $validated['period'] ?? null,
                'operator' => $validated['operator'] ?? null,
                'course' => $validated['course'] ?? null,
                'year_level' => $validated['year_level'] ?? null,
                'section' => $validated['section'] ?? null,
                'student_no' => $validated['student_no'] ?? null,
                'include_unpaid_only' => (bool) ($validated['include_unpaid_only'] ?? false),
                'active_only' => (bool) ($validated['active_only'] ?? false),
                'affected_count' => $affectedCount,
            ]);
        }

        return response()->json([
            'ok' => true,
            'affected_count' => $affectedCount,
            'message' => 'Action processed successfully.',
        ]);
    }

    private function resolveCourseId($courseValue)
    {
        $courseText = trim((string) $courseValue);
        if ($courseText === '') {
            return null;
        }

        return Course::query()
            ->whereRaw('LOWER(TRIM(code)) = ?', [strtolower($courseText)])
            ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower($courseText)])
            ->value('id');
    }

    private function resolveYearBlockId($yearLevelValue)
    {
        $normalizedLabel = $this->normalizeYearBlockLabel($yearLevelValue);
        if (!$normalizedLabel) {
            return null;
        }

        return YearBlock::query()
            ->whereRaw('LOWER(TRIM(label)) = ?', [strtolower($normalizedLabel)])
            ->value('id');
    }

    private function normalizeYearBlockLabel($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['1', '1st', '1st year', '1st yr', 'first', 'first year'], true)) {
            return '1st Year';
        }

        if (in_array($normalized, ['2', '2nd', '2nd year', '2nd yr', 'second', 'second year'], true)) {
            return '2nd Year';
        }

        if (in_array($normalized, ['3', '3rd', '3rd year', '3rd yr', 'third', 'third year'], true)) {
            return '3rd Year';
        }

        if (in_array($normalized, ['4', '4th', '4th year', '4th yr', '4a', 'fourth', 'fourth year'], true)) {
            return '4th Year';
        }

        if (preg_match('/^1/', $normalized)) {
            return '1st Year';
        }

        if (preg_match('/^2/', $normalized)) {
            return '2nd Year';
        }

        if (preg_match('/^3/', $normalized)) {
            return '3rd Year';
        }

        if (preg_match('/^4/', $normalized)) {
            return '4th Year';
        }

        return null;
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

    private function seedBedDays(): void
    {
        $rows = [
            ['school_year' => '2025-2026', 'semester' => 'First', 'month_name' => 'January', 'number_of_days' => 20],
            ['school_year' => '2025-2026', 'semester' => 'First', 'month_name' => 'February', 'number_of_days' => 19],
            ['school_year' => '2025-2026', 'semester' => 'Second', 'month_name' => 'June', 'number_of_days' => 22],
        ];

        foreach ($rows as $row) {
            BedDay::create($row);
        }
    }

    private function seedBedStudentStatuses(): void
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term'])
            ->orderBy('name')
            ->limit(40)
            ->get(['student_no', 'name', 'course_id', 'year_block_id', 'academic_term_id']);

        foreach ($students as $index => $student) {
            BedStudentStatus::create([
                'student_no' => (string) $student->student_no,
                'student_name' => (string) $student->name,
                'course' => (string) ($student->program ?: '-'),
                'year_level' => (string) ($student->year_level ?: '-'),
                'section' => ['A', 'B', 'C'][$index % 3],
                'school_year' => (string) ($student->school_year ?: '2025-2026'),
                'term' => (string) ($student->semester ?: 'First'),
                'no_payment' => false,
                'no_section' => false,
            ]);
        }
    }

    private function seedMasterFacultyFiles(): void
    {
        $facultyRows = Faculty::query()
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'code', 'name']);

        if ($facultyRows->count()) {
            foreach ($facultyRows as $facultyRow) {
                MasterFacultyFile::create([
                    'code' => (string) ($facultyRow->code ?: ('FAC-' . str_pad((string) $facultyRow->id, 4, '0', STR_PAD_LEFT))),
                    'name' => (string) $facultyRow->name,
                    'department' => 'Computer Studies',
                    'status' => 'Active',
                ]);
            }

            return;
        }

        $fallbackRows = [
            ['code' => '01A', 'name' => 'Dela Cruz, Juan', 'department' => 'Computer Studies', 'status' => 'Active'],
            ['code' => '02A', 'name' => 'Benedict, John', 'department' => 'Computer Studies', 'status' => 'Inactive'],
            ['code' => '03A', 'name' => 'Rivera, Angelo', 'department' => 'Engineering', 'status' => 'Active'],
        ];

        foreach ($fallbackRows as $row) {
            MasterFacultyFile::create($row);
        }
    }

    private function defaultFacultyConfigSections(): array
    {
        return [
            'education' => [
                [
                    'level' => 'College',
                    'schoolName' => 'Pamantasan ng Lungsod ng Pasig',
                    'courseDegree' => 'BS Computer Science',
                    'dateGraduated' => '04/15/2022',
                ],
                [
                    'level' => 'Graduate Studies',
                    'schoolName' => 'University of Makati',
                    'courseDegree' => 'MIT',
                    'dateGraduated' => '06/20/2025',
                ],
            ],
            'registration' => [
                [
                    'name' => 'LET Professional Teacher',
                    'rating' => '84.60',
                    'date' => '09/24/2023',
                ],
            ],
            'organization' => [
                [
                    'position' => 'Member',
                    'name' => 'Philippine Society of IT Educators',
                    'date' => '01/10/2024',
                ],
            ],
            'work' => [
                [
                    'position' => 'IT Instructor',
                    'company' => 'PLP Senior High Department',
                    'date' => '08/01/2024',
                ],
            ],
            'training' => [
                [
                    'title' => 'Outcomes-Based Education Seminar',
                    'place' => 'Pasig City',
                    'date' => '11/12/2024',
                ],
            ],
        ];
    }

    private function seedMasterStudentProfileFiles(): void
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label'])
            ->orderBy('name')
            ->limit(80)
            ->get(['student_no', 'name', 'course_id', 'year_block_id']);

        if ($students->count()) {
            foreach ($students as $student) {
                MasterStudentProfileFile::create([
                    'student_no' => (string) $student->student_no,
                    'student_name' => (string) $student->name,
                    'course' => (string) ($student->program ?: 'Not Set'),
                    'year_level' => (string) ($student->year_level ?: 'Not Set'),
                ]);
            }

            return;
        }

        $fallbackRows = [
            ['student_no' => '2223A8137', 'student_name' => 'Bares, Mark Jay', 'course' => 'Bachelor of Science in Computer Science', 'year_level' => 'Fourth'],
            ['student_no' => '2223A8138', 'student_name' => 'Austero, Andrea Jane', 'course' => 'Bachelor of Science in Computer Science', 'year_level' => 'Fourth'],
            ['student_no' => '2223A8141', 'student_name' => 'Dela Cruz, Juan', 'course' => 'Bachelor of Science in Information Technology', 'year_level' => 'Third'],
        ];

        foreach ($fallbackRows as $row) {
            MasterStudentProfileFile::create($row);
        }
    }

    private function seedMasterStudentGradeFiles(): void
    {
        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label'])
            ->orderBy('name')
            ->limit(80)
            ->get(['student_no', 'name', 'course_id', 'year_block_id']);

        if ($students->count()) {
            foreach ($students as $student) {
                MasterStudentGradeFile::create([
                    'student_no' => (string) $student->student_no,
                    'student_name' => (string) $student->name,
                    'course' => (string) ($student->program ?: 'Not Set'),
                    'year_level' => (string) ($student->year_level ?: 'Not Set'),
                ]);
            }

            return;
        }

        $fallbackRows = [
            ['student_no' => '2223A8137', 'student_name' => 'Bares, Mark Jay', 'course' => 'Bachelor of Science in Computer Science', 'year_level' => 'Fourth'],
            ['student_no' => '2223A8138', 'student_name' => 'Austero, Andrea Jane', 'course' => 'Bachelor of Science in Computer Science', 'year_level' => 'Fourth'],
            ['student_no' => '2223A8141', 'student_name' => 'Dela Cruz, Juan', 'course' => 'Bachelor of Science in Information Technology', 'year_level' => 'Third'],
        ];

        foreach ($fallbackRows as $row) {
            MasterStudentGradeFile::create($row);
        }
    }
}
