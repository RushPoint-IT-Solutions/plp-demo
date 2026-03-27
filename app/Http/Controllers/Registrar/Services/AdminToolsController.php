<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\AcademicCalendarEvent;
use App\StudentProfile;
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
        return view('registrar.admin-tools.access-management.report-access');
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
