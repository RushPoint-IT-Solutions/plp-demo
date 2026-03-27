<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\ReportPermission;
use App\AcademicCalendarEvent;
use App\StudentProfile;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminToolsController extends Controller
{
    public function configuration()
    {
        return view('registrar.admin-tools.system-config.configuration');
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

    public function announcement()
    {
        return view('registrar.admin-tools.system-config.announcement');
    }

    // Access Management
    public function userAccounts()
    {
        return view('registrar.admin-tools.access-management.user-accounts');
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
}
