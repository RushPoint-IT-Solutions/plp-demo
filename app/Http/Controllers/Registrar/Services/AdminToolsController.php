<?php

namespace App\Http\Controllers\Registrar\Services;

use App\BedDay;
use App\BedStudentStatus;
use App\AcademicTerm;
use App\AnnouncementType;
use App\Faculty;
use App\Http\Controllers\Controller;
use App\MasterFacultyFile;
use App\MasterStudentGradeFile;
use App\MasterStudentProfileFile;
use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\Student;
use App\StudentGradeRecord;
use App\StudentUpdateRun;
use App\SystemAnnouncement;
use App\SystemConfigNameSignature;
use App\SystemConfigSignatureDesignation;
use App\SystemCurriculumDisplaySetting;
use App\SystemCutoffEntry;
use App\SystemCutoffType;
use App\SystemEmailSenderSetting;
use App\SystemGradePosting;
use App\SystemIncProcessRun;
use App\SystemReportDetailSetting;
use App\SystemSchoolSemester;
use App\ReportPermission;
use App\AccessControlModule;
use App\AccessControlPermissionType;
use App\UserAccessControl;
use App\AcademicCalendarAudienceType;
use App\AcademicCalendarEvent;
use App\Course;
use App\StudentProfile;
use App\Support\SystemConfigSchoolTermOptions;
use App\User;
use App\UserAccountStatus;
use App\YearBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminToolsController extends Controller
{
    public function configuration()
    {
        $schoolSemRows = [];
        $gradePostingRows = [];
        $signatureDesignationRows = [];
        $signatureRows = [];
        $cutoffTypeRows = [];
        $cutoffDateRows = [];
        $sectionCutoffRows = [];
        $cutoffConfigRows = [];
        $curriculumDisplayRows = [];
        $reportDetails = [
            'region' => '',
            'division' => '',
            'schoolId' => '',
            'schoolName' => '',
            'contactDetails' => '',
        ];
        $emailSender = [
            'email' => '',
            'passwordMasked' => '',
        ];
        $latestIncRun = null;
        $academicTermRows = $this->buildAcademicTermRows();

        if (Schema::hasTable('system_school_semesters')) {
            if (SystemSchoolSemester::query()->count() === 0) {
                $this->seedSchoolSemRows();
            }

            $schoolSemRows = SystemSchoolSemester::query()
                ->with('academicTerm')
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'sy' => (string) ($row->school_year ?: ''),
                        'semester' => (string) ($row->semester ?: ''),
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
                ->with('academicTerm')
                ->orderByDesc('date_from')
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'sy' => (string) ($row->school_year ?: ''),
                        'semester' => (string) ($row->semester ?: ''),
                        'period' => (string) $row->period,
                        'dateFrom' => optional($row->date_from)->format('Y-m-d') ?: '',
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_config_signature_designations')) {
            $signatureDesignationRows = SystemConfigSignatureDesignation::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => (string) $row->code,
                        'name' => (string) $row->name,
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_config_name_signatures')) {
            $signatureRows = SystemConfigNameSignature::query()
                ->with('designation')
                ->orderBy('designation_id')
                ->get()
                ->map(function ($row) {
                    return $this->mapSignatureRow($row);
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_cutoff_types')) {
            $cutoffTypeRows = SystemCutoffType::query()
                ->orderBy('name')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => (string) $row->code,
                        'name' => (string) $row->name,
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_cutoff_entries')) {
            $cutoffRows = SystemCutoffEntry::query()
                ->with(['cutoffType', 'academicTerm'])
                ->orderByDesc('cutoff_date')
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return $this->mapCutoffRow($row);
                })
                ->values();

            $cutoffDateRows = $cutoffRows->filter(function ($row) {
                return in_array((string) ($row['typeCode'] ?? ''), ['ENROLLMENT', 'FACULTY_LOADING'], true);
            })->values()->all();

            $sectionCutoffRows = $cutoffRows->filter(function ($row) {
                return (string) ($row['typeCode'] ?? '') === 'SECTION_OFFERING';
            })->values()->all();

            $cutoffConfigRows = $cutoffRows->filter(function ($row) {
                return (string) ($row['typeCode'] ?? '') === 'CHANGING_DELETING_ADDING';
            })->values()->all();
        }

        if (Schema::hasTable('system_curriculum_display_settings')) {
            $curriculumDisplayRows = SystemCurriculumDisplaySetting::query()
                ->with('academicTerm')
                ->orderByDesc('id')
                ->get()
                ->map(function ($row) {
                    return $this->mapCurriculumDisplayRow($row);
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('system_report_detail_settings')) {
            $reportDetailRow = SystemReportDetailSetting::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            if ($reportDetailRow) {
                $reportDetails = [
                    'region' => (string) $reportDetailRow->region,
                    'division' => (string) $reportDetailRow->division,
                    'schoolId' => (string) $reportDetailRow->school_id,
                    'schoolName' => (string) $reportDetailRow->school_name,
                    'contactDetails' => (string) $reportDetailRow->contact_details,
                ];
            }
        }

        if (Schema::hasTable('system_email_sender_settings')) {
            $emailSenderRow = SystemEmailSenderSetting::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            if ($emailSenderRow) {
                $emailSender = [
                    'email' => (string) $emailSenderRow->sender_email,
                    'passwordMasked' => '********',
                ];
            }
        }

        if (Schema::hasTable('system_inc_process_runs')) {
            $latestRun = SystemIncProcessRun::query()
                ->with('academicTerm')
                ->orderByDesc('id')
                ->first();

            if ($latestRun) {
                $latestIncRun = [
                    'schoolYear' => (string) ($latestRun->school_year ?: ''),
                    'semester' => (string) ($latestRun->semester ?: ''),
                    'processedCount' => (int) $latestRun->processed_count,
                    'createdAt' => optional($latestRun->created_at)->format('Y-m-d H:i:s') ?: '',
                ];
            }
        }

        return view(
            'registrar.admin-tools.system-config.configuration',
            compact(
                'schoolSemRows',
                'gradePostingRows',
                'signatureDesignationRows',
                'signatureRows',
                'cutoffTypeRows',
                'cutoffDateRows',
                'sectionCutoffRows',
                'cutoffConfigRows',
                'curriculumDisplayRows',
                'reportDetails',
                'emailSender',
                'latestIncRun',
                'academicTermRows'
            )
        );
    }

    public function admissionConfig()
    {
        return view('registrar.admin-tools.system-config.admission-config');
    }

    public function academicCalendar()
    {
        $calendarRows = [];

        if (Schema::hasTable('academic_calendar_events')) {
            $calendarQuery = AcademicCalendarEvent::query()
                ->orderBy('event_date')
                ->orderBy('time_from');

            if (Schema::hasTable('academic_calendar_event_audiences')
                && Schema::hasTable('academic_calendar_audience_types')) {
                $calendarQuery->with('audienceTypes');
            }

            $calendarRows = $calendarQuery
                ->get()
                ->map(function ($event) {
                    return $this->mapAcademicCalendarRow($event);
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
            'timeTo' => 'required|date_format:H:i|after:timeFrom',
            'event' => 'required|string|max:190',
            'venue' => 'nullable|string|max:190',
            'inCharge' => 'nullable|string|max:190',
            'postUntil' => 'nullable|date|after_or_equal:date',
            'audiences' => 'nullable|array',
            'audiences.*' => 'nullable|string|in:student,faculty,applicant',
        ]);

        $audienceCodes = $this->normalizeAcademicCalendarAudiences($validated['audiences'] ?? null);

        $event = DB::transaction(function () use ($validated, $audienceCodes) {
            $createdEvent = AcademicCalendarEvent::create([
                'event_date' => $validated['date'],
                'time_from' => $validated['timeFrom'],
                'time_to' => $validated['timeTo'],
                'title' => $validated['event'],
                'venue' => $validated['venue'] ?? null,
                'in_charge' => $validated['inCharge'] ?? null,
                'post_until' => $validated['postUntil'] ?? $validated['date'],
                'event_type' => 'event',
                'is_active' => true,
            ]);

            $this->syncAcademicCalendarAudiences($createdEvent, $audienceCodes);

            return $createdEvent;
        });

        if (Schema::hasTable('academic_calendar_event_audiences')
            && Schema::hasTable('academic_calendar_audience_types')) {
            $event->load('audienceTypes');
        }

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
            'timeTo' => 'required|date_format:H:i|after:timeFrom',
            'event' => 'required|string|max:190',
            'venue' => 'nullable|string|max:190',
            'inCharge' => 'nullable|string|max:190',
            'postUntil' => 'nullable|date|after_or_equal:date',
            'audiences' => 'nullable|array',
            'audiences.*' => 'nullable|string|in:student,faculty,applicant',
        ]);

        $audienceCodes = $this->normalizeAcademicCalendarAudiences($validated['audiences'] ?? null);

        DB::transaction(function () use ($academicCalendarEvent, $validated, $audienceCodes) {
            $academicCalendarEvent->update([
                'event_date' => $validated['date'],
                'time_from' => $validated['timeFrom'],
                'time_to' => $validated['timeTo'],
                'title' => $validated['event'],
                'venue' => $validated['venue'] ?? null,
                'in_charge' => $validated['inCharge'] ?? null,
                'post_until' => $validated['postUntil'] ?? $validated['date'],
            ]);

            $this->syncAcademicCalendarAudiences($academicCalendarEvent, $audienceCodes);
        });

        if (Schema::hasTable('academic_calendar_event_audiences')
            && Schema::hasTable('academic_calendar_audience_types')) {
            $academicCalendarEvent->load('audienceTypes');
        }

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

        $this->ensureAnnouncementAudienceLookups();

        if (Schema::hasTable('system_announcements')) {
            if (SystemAnnouncement::query()->count() === 0) {
                $this->seedAnnouncements();
            }

            $announcementRows = SystemAnnouncement::query()
                ->with(['announcementTypeLookup', 'canonicalCourse'])
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
            'type' => 'required|string|max:40',
            'program' => 'nullable|string|max:120',
            'content' => 'required|string',
        ]);

        $audienceCode = $this->normalizeAnnouncementAudienceCode($validated['type'] ?? null);
        if (!$audienceCode) {
            return response()->json([
                'message' => 'The selected audience is invalid.',
                'errors' => [
                    'type' => ['Audience must be Everyone, Students, Faculty, Staff, or Applicant.'],
                ],
            ], 422);
        }

        $programValue = isset($validated['program']) ? trim((string) $validated['program']) : '';
        $programValue = $programValue !== '' ? $programValue : 'All Programs';
        $courseId = null;
        $announcementTypeId = $this->resolveAnnouncementTypeId($audienceCode);
        $hasProgramColumn = Schema::hasColumn('system_announcements', 'program');
        $hasCourseColumn = Schema::hasColumn('system_announcements', 'course_id');

        if (strtolower($programValue) !== 'all programs') {
            if ($hasCourseColumn) {
                $courseId = $this->resolveCourseId($programValue);

                if (!$courseId) {
                    return response()->json([
                        'message' => 'The selected program is invalid.',
                        'errors' => [
                            'program' => ['Program must match an existing course code or name.'],
                        ],
                    ], 422);
                }
            } elseif (!$hasProgramColumn) {
                return response()->json([
                    'message' => 'Program targeting is unavailable in the current announcement schema.',
                    'errors' => [
                        'program' => ['Program selection is not supported by the current database schema.'],
                    ],
                ], 422);
            }
        }

        $duplicatePayload = [
            'from' => $validated['from'],
            'to' => $validated['to'],
            'title' => $validated['title'],
            'audience_code' => $audienceCode,
            'announcement_type_id' => $announcementTypeId,
            'program' => $programValue,
            'content' => $validated['content'],
        ];

        if ($hasCourseColumn) {
            $duplicatePayload['course_id'] = $courseId;
        }

        $existingAnnouncement = $this->findDuplicateAnnouncement($duplicatePayload);

        if ($existingAnnouncement) {
            $this->syncAnnouncementPortalNotification(
                $existingAnnouncement,
                $request->user() ? (int) $request->user()->id : null
            );

            if ($existingAnnouncement->relationLoaded('announcementTypeLookup') === false
                || $existingAnnouncement->relationLoaded('canonicalCourse') === false) {
                $existingAnnouncement->load(['announcementTypeLookup', 'canonicalCourse']);
            }

            return response()->json([
                'ok' => true,
                'already_exists' => true,
                'row' => $this->mapAnnouncementRow($existingAnnouncement),
            ]);
        }

        $announcementPayload = [
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $audienceCode,
            'content' => $validated['content'],
        ];

        if ($hasProgramColumn) {
            $announcementPayload['program'] = $programValue;
        }

        if ($hasCourseColumn) {
            $announcementPayload['course_id'] = $courseId;
        }

        if (Schema::hasColumn('system_announcements', 'announcement_type_id')) {
            $announcementPayload['announcement_type_id'] = $announcementTypeId;
        }

        $announcement = SystemAnnouncement::create($announcementPayload);
        $announcement->load(['announcementTypeLookup', 'canonicalCourse']);

        $this->syncAnnouncementPortalNotification(
            $announcement,
            $request->user() ? (int) $request->user()->id : null
        );

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
            'type' => 'required|string|max:40',
            'program' => 'nullable|string|max:120',
            'content' => 'required|string',
        ]);

        $audienceCode = $this->normalizeAnnouncementAudienceCode($validated['type'] ?? null);
        if (!$audienceCode) {
            return response()->json([
                'message' => 'The selected audience is invalid.',
                'errors' => [
                    'type' => ['Audience must be Everyone, Students, Faculty, Staff, or Applicant.'],
                ],
            ], 422);
        }

        $programValue = isset($validated['program']) ? trim((string) $validated['program']) : '';
        $programValue = $programValue !== '' ? $programValue : 'All Programs';
        $courseId = null;
        $announcementTypeId = $this->resolveAnnouncementTypeId($audienceCode);
        $hasProgramColumn = Schema::hasColumn('system_announcements', 'program');
        $hasCourseColumn = Schema::hasColumn('system_announcements', 'course_id');

        if (strtolower($programValue) !== 'all programs') {
            if ($hasCourseColumn) {
                $courseId = $this->resolveCourseId($programValue);

                if (!$courseId) {
                    return response()->json([
                        'message' => 'The selected program is invalid.',
                        'errors' => [
                            'program' => ['Program must match an existing course code or name.'],
                        ],
                    ], 422);
                }
            } elseif (!$hasProgramColumn) {
                return response()->json([
                    'message' => 'Program targeting is unavailable in the current announcement schema.',
                    'errors' => [
                        'program' => ['Program selection is not supported by the current database schema.'],
                    ],
                ], 422);
            }
        }

        $duplicatePayload = [
            'from' => $validated['from'],
            'to' => $validated['to'],
            'title' => $validated['title'],
            'audience_code' => $audienceCode,
            'announcement_type_id' => $announcementTypeId,
            'program' => $programValue,
            'content' => $validated['content'],
            'exclude_id' => (int) $systemAnnouncement->id,
        ];

        if ($hasCourseColumn) {
            $duplicatePayload['course_id'] = $courseId;
        }

        $duplicateAnnouncement = $this->findDuplicateAnnouncement($duplicatePayload);

        if ($duplicateAnnouncement) {
            return response()->json([
                'message' => 'A matching announcement already exists.',
                'errors' => [
                    'title' => ['This announcement already exists.'],
                ],
            ], 422);
        }

        $announcementPayload = [
            'date_from' => $validated['from'],
            'date_to' => $validated['to'],
            'title' => $validated['title'],
            'announcement_type' => $audienceCode,
            'content' => $validated['content'],
        ];

        if ($hasProgramColumn) {
            $announcementPayload['program'] = $programValue;
        }

        if ($hasCourseColumn) {
            $announcementPayload['course_id'] = $courseId;
        }

        if (Schema::hasColumn('system_announcements', 'announcement_type_id')) {
            $announcementPayload['announcement_type_id'] = $announcementTypeId;
        }

        $systemAnnouncement->update($announcementPayload);
        $systemAnnouncement->load(['announcementTypeLookup', 'canonicalCourse']);

        $this->syncAnnouncementPortalNotification(
            $systemAnnouncement,
            $request->user() ? (int) $request->user()->id : null
        );

        return response()->json([
            'ok' => true,
            'row' => $this->mapAnnouncementRow($systemAnnouncement),
        ]);
    }

    public function announcementDestroy(SystemAnnouncement $systemAnnouncement): JsonResponse
    {
        $this->removeAnnouncementPortalNotification($systemAnnouncement);
        $systemAnnouncement->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationSchoolSemStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:30',
        ]);

        $schoolYear = $this->sanitizeSchoolYear($validated['school_year']);
        $semester = $this->normalizeSemesterLabel($validated['semester']);

        $row = SystemSchoolSemester::query()
            ->get()
            ->first(function (SystemSchoolSemester $candidate) use ($schoolYear, $semester) {
                return $this->matchesSchoolSemester($candidate, $schoolYear, $semester);
            });

        if (!$row) {
            $row = SystemSchoolSemester::create([
                'school_year' => $schoolYear,
                'semester' => $semester,
            ]);
        }

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

        $schoolYear = $this->sanitizeSchoolYear($validated['school_year']);
        $semester = $this->normalizeSemesterLabel($validated['semester']);

        $hasDuplicate = SystemSchoolSemester::query()
            ->where('id', '!=', $systemSchoolSemester->id)
            ->get()
            ->contains(function (SystemSchoolSemester $candidate) use ($schoolYear, $semester) {
                return $this->matchesSchoolSemester($candidate, $schoolYear, $semester);
            });

        if ($hasDuplicate) {
            throw ValidationException::withMessages([
                'school_year' => ['The selected School Year and Semester already exists.'],
            ]);
        }

        $systemSchoolSemester->update([
            'school_year' => $schoolYear,
            'semester' => $semester,
        ]);

        return response()->json([
            'ok' => true,
            'row' => [
                'id' => $systemSchoolSemester->id,
                'sy' => (string) $systemSchoolSemester->school_year,
                'semester' => (string) $systemSchoolSemester->semester,
            ],
        ]);
    }

    private function matchesSchoolSemester(SystemSchoolSemester $row, string $schoolYear, string $semester): bool
    {
        return trim((string) $row->school_year) === trim((string) $schoolYear)
            && trim((string) $this->normalizeSemesterLabel((string) $row->semester))
                === trim((string) $this->normalizeSemesterLabel($semester));
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

        $row = SystemGradePosting::create([
            'school_year' => $this->sanitizeSchoolYear($validated['school_year']),
            'semester' => $this->normalizeSemesterLabel($validated['semester']),
            'period' => trim((string) $validated['period']),
            'date_from' => $validated['date_from'],
        ]);

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

        $systemGradePosting->update([
            'school_year' => $this->sanitizeSchoolYear($validated['school_year']),
            'semester' => $this->normalizeSemesterLabel($validated['semester']),
            'period' => trim((string) $validated['period']),
            'date_from' => $validated['date_from'],
        ]);

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

    public function configurationSignatureStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'designation_id' => 'required|integer|exists:system_config_signature_designations,id',
            'signer_name' => 'required|string|max:190',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $row = SystemConfigNameSignature::query()->firstOrNew([
            'designation_id' => (int) $validated['designation_id'],
        ]);

        if ($request->hasFile('signature_file') && !empty($row->signature_path)) {
            Storage::disk('public')->delete($row->signature_path);
        }

        $row->signer_name = trim((string) $validated['signer_name']);
        $row->is_active = true;

        if ($request->hasFile('signature_file')) {
            $row->signature_path = $request->file('signature_file')->store('registrar/signatures', 'public');
        }

        $row->save();
        $row->load('designation');

        return response()->json([
            'ok' => true,
            'row' => $this->mapSignatureRow($row),
        ]);
    }

    public function configurationSignatureUpdate(Request $request, SystemConfigNameSignature $systemConfigNameSignature): JsonResponse
    {
        $validated = $request->validate([
            'designation_id' => 'required|integer|exists:system_config_signature_designations,id',
            'signer_name' => 'required|string|max:190',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $duplicateDesignation = SystemConfigNameSignature::query()
            ->where('designation_id', (int) $validated['designation_id'])
            ->where('id', '!=', $systemConfigNameSignature->id)
            ->exists();

        if ($duplicateDesignation) {
            return response()->json([
                'message' => 'The selected designation already has a signature entry.',
                'errors' => [
                    'designation_id' => ['Choose another designation or edit the existing row.'],
                ],
            ], 422);
        }

        if ($request->hasFile('signature_file') && !empty($systemConfigNameSignature->signature_path)) {
            Storage::disk('public')->delete($systemConfigNameSignature->signature_path);
        }

        $systemConfigNameSignature->designation_id = (int) $validated['designation_id'];
        $systemConfigNameSignature->signer_name = trim((string) $validated['signer_name']);
        $systemConfigNameSignature->is_active = true;

        if ($request->hasFile('signature_file')) {
            $systemConfigNameSignature->signature_path = $request->file('signature_file')->store('registrar/signatures', 'public');
        }

        $systemConfigNameSignature->save();
        $systemConfigNameSignature->load('designation');

        return response()->json([
            'ok' => true,
            'row' => $this->mapSignatureRow($systemConfigNameSignature),
        ]);
    }

    public function configurationSignatureDestroy(SystemConfigNameSignature $systemConfigNameSignature): JsonResponse
    {
        if (!empty($systemConfigNameSignature->signature_path)) {
            Storage::disk('public')->delete($systemConfigNameSignature->signature_path);
        }

        $systemConfigNameSignature->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationCutoffStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_code' => 'required|string|max:80|exists:system_cutoff_types,code',
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'event_date' => 'nullable|date',
            'cutoff_date' => 'required|date',
            'student_no' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:190',
        ]);

        $typeCode = strtoupper(trim((string) $validated['type_code']));
        $cutoffType = SystemCutoffType::query()->where('code', $typeCode)->first();
        if (!$cutoffType) {
            return response()->json([
                'message' => 'The selected cut-off type is invalid.',
                'errors' => [
                    'type_code' => ['Unknown cut-off type.'],
                ],
            ], 422);
        }

        $row = new SystemCutoffEntry();
        $row->cutoff_type_id = $cutoffType->id;
        $row->student_no = isset($validated['student_no']) ? trim((string) $validated['student_no']) : null;
        $row->event_date = $validated['event_date'] ?? null;
        $row->cutoff_date = $validated['cutoff_date'];
        $row->notes = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $row->school_year = $this->sanitizeSchoolYear($validated['school_year']);
        $row->semester = $this->normalizeSemesterLabel($validated['semester']);
        $row->save();
        $row->load(['cutoffType', 'academicTerm']);

        return response()->json([
            'ok' => true,
            'row' => $this->mapCutoffRow($row),
        ]);
    }

    public function configurationCutoffUpdate(Request $request, SystemCutoffEntry $systemCutoffEntry): JsonResponse
    {
        $validated = $request->validate([
            'type_code' => 'required|string|max:80|exists:system_cutoff_types,code',
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'event_date' => 'nullable|date',
            'cutoff_date' => 'required|date',
            'student_no' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:190',
        ]);

        $typeCode = strtoupper(trim((string) $validated['type_code']));
        $cutoffType = SystemCutoffType::query()->where('code', $typeCode)->first();
        if (!$cutoffType) {
            return response()->json([
                'message' => 'The selected cut-off type is invalid.',
                'errors' => [
                    'type_code' => ['Unknown cut-off type.'],
                ],
            ], 422);
        }

        $systemCutoffEntry->cutoff_type_id = $cutoffType->id;
        $systemCutoffEntry->student_no = isset($validated['student_no']) ? trim((string) $validated['student_no']) : null;
        $systemCutoffEntry->event_date = $validated['event_date'] ?? null;
        $systemCutoffEntry->cutoff_date = $validated['cutoff_date'];
        $systemCutoffEntry->notes = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $systemCutoffEntry->school_year = $this->sanitizeSchoolYear($validated['school_year']);
        $systemCutoffEntry->semester = $this->normalizeSemesterLabel($validated['semester']);
        $systemCutoffEntry->save();
        $systemCutoffEntry->load(['cutoffType', 'academicTerm']);

        return response()->json([
            'ok' => true,
            'row' => $this->mapCutoffRow($systemCutoffEntry),
        ]);
    }

    public function configurationCutoffDestroy(SystemCutoffEntry $systemCutoffEntry): JsonResponse
    {
        $systemCutoffEntry->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationCurriculumDisplayStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'display_status' => 'required|string|in:Display,Hide',
        ]);

        $schoolYear = $this->sanitizeSchoolYear($validated['school_year']);
        $semester = $this->normalizeSemesterLabel($validated['semester']);
        $term = $this->resolveAcademicTerm($schoolYear, $semester);

        $row = SystemCurriculumDisplaySetting::query()->updateOrCreate(
            ['academic_term_id' => $term->id],
            ['display_status' => trim((string) $validated['display_status'])]
        );
        $row->load('academicTerm');

        return response()->json([
            'ok' => true,
            'row' => $this->mapCurriculumDisplayRow($row),
        ]);
    }

    public function configurationCurriculumDisplayUpdate(Request $request, SystemCurriculumDisplaySetting $systemCurriculumDisplaySetting): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
            'display_status' => 'required|string|in:Display,Hide',
        ]);

        $schoolYear = $this->sanitizeSchoolYear($validated['school_year']);
        $semester = $this->normalizeSemesterLabel($validated['semester']);
        $term = $this->resolveAcademicTerm($schoolYear, $semester);

        $systemCurriculumDisplaySetting->update([
            'academic_term_id' => $term->id,
            'display_status' => trim((string) $validated['display_status']),
        ]);
        $systemCurriculumDisplaySetting->load('academicTerm');

        return response()->json([
            'ok' => true,
            'row' => $this->mapCurriculumDisplayRow($systemCurriculumDisplaySetting),
        ]);
    }

    public function configurationCurriculumDisplayDestroy(SystemCurriculumDisplaySetting $systemCurriculumDisplaySetting): JsonResponse
    {
        $systemCurriculumDisplaySetting->delete();

        return response()->json(['ok' => true]);
    }

    public function configurationReportDetailsSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'region' => 'required|string|max:190',
            'division' => 'required|string|max:190',
            'school_id' => 'required|string|max:120',
            'school_name' => 'required|string|max:190',
            'contact_details' => 'required|string|max:255',
        ]);

        $row = SystemReportDetailSetting::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            $row = new SystemReportDetailSetting();
            $row->is_active = true;
        }

        $row->region = trim((string) $validated['region']);
        $row->division = trim((string) $validated['division']);
        $row->school_id = trim((string) $validated['school_id']);
        $row->school_name = trim((string) $validated['school_name']);
        $row->contact_details = trim((string) $validated['contact_details']);
        $row->save();

        SystemReportDetailSetting::query()
            ->where('id', '!=', $row->id)
            ->update(['is_active' => false]);

        return response()->json([
            'ok' => true,
            'row' => [
                'region' => (string) $row->region,
                'division' => (string) $row->division,
                'schoolId' => (string) $row->school_id,
                'schoolName' => (string) $row->school_name,
                'contactDetails' => (string) $row->contact_details,
            ],
        ]);
    }

    public function configurationEmailSenderSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:190',
            'password' => 'nullable|string|min:6|max:190',
        ]);

        $row = SystemEmailSenderSetting::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            $row = new SystemEmailSenderSetting();
            $row->is_active = true;
        }

        $passwordInput = trim((string) ($validated['password'] ?? ''));
        if (!$row->exists && $passwordInput === '') {
            return response()->json([
                'message' => 'Password is required for first-time email sender setup.',
                'errors' => [
                    'password' => ['Please provide a password.'],
                ],
            ], 422);
        }

        $row->sender_email = trim((string) $validated['email']);
        if ($passwordInput !== '') {
            $row->sender_password_encrypted = Crypt::encryptString($passwordInput);
        }
        $row->save();

        SystemEmailSenderSetting::query()
            ->where('id', '!=', $row->id)
            ->update(['is_active' => false]);

        return response()->json([
            'ok' => true,
            'row' => [
                'email' => (string) $row->sender_email,
                'passwordMasked' => '********',
            ],
        ]);
    }

    public function configurationOverdueIncProcess(Request $request): JsonResponse
    {
        if (!Schema::hasTable('student_grade_records')) {
            return response()->json([
                'message' => 'Student grade records table is unavailable.',
            ], 422);
        }

        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'semester' => 'required|string|max:40',
        ]);

        $schoolYear = $this->sanitizeSchoolYear($validated['school_year']);
        $semester = $this->normalizeSemesterLabel($validated['semester']);
        $aliases = $this->semesterAliases($semester);

        $baseQuery = StudentGradeRecord::query()
            ->whereRaw('TRIM(school_year) = ?', [$schoolYear])
            ->where('inc', true)
            ->where(function ($query) use ($aliases) {
                foreach ($aliases as $index => $alias) {
                    if ($index === 0) {
                        $query->whereRaw('LOWER(TRIM(term)) = ?', [$alias]);
                    } else {
                        $query->orWhereRaw('LOWER(TRIM(term)) = ?', [$alias]);
                    }
                }
            });

        $processedCount = (int) (clone $baseQuery)->count();
        $updatedCount = 0;

        if ($processedCount > 0) {
            $updatedCount = (int) (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereNull('remarks')->orWhereRaw('TRIM(remarks) = ?', ['']);
                })
                ->update([
                    'remarks' => 'Overdue INC processed on ' . now()->format('Y-m-d'),
                ]);
        }

        $notes = 'Matched ' . $processedCount . ' INC record(s); updated ' . $updatedCount . ' remarks.';

        $runRow = null;
        if (Schema::hasTable('system_inc_process_runs')) {
            $run = new SystemIncProcessRun([
                'triggered_by_user_id' => $request->user() ? (int) $request->user()->id : null,
                'processed_count' => $processedCount,
                'notes' => $notes,
            ]);
            $run->school_year = $schoolYear;
            $run->semester = $semester;
            $run->save();

            $runRow = [
                'schoolYear' => (string) ($run->school_year ?: ''),
                'semester' => (string) ($run->semester ?: ''),
                'processedCount' => (int) $run->processed_count,
                'createdAt' => optional($run->created_at)->format('Y-m-d H:i:s') ?: '',
            ];
        }

        return response()->json([
            'ok' => true,
            'processedCount' => $processedCount,
            'updatedCount' => $updatedCount,
            'message' => $notes,
            'run' => $runRow,
        ]);
    }

    // Access Management
    public function userAccounts()
    {
        return view('registrar.admin-tools.access-management.user-accounts', [
            'userAccountDataUrl' => route('registrar.admin-tools.access-management.user-accounts.data'),
            'userAccountUpdateTemplate' => route('registrar.admin-tools.access-management.user-accounts.update', ['user' => '__ID__']),
            'userAccountDeleteTemplate' => route('registrar.admin-tools.access-management.user-accounts.destroy', ['user' => '__ID__']),
            'userAccessControlModulesUrl' => route('registrar.admin-tools.access-management.user-accounts.access-control.modules'),
            'userAccessControlShowTemplate' => route('registrar.admin-tools.access-management.user-accounts.access-control.show', ['user' => '__ID__']),
            'userAccessControlUpdateTemplate' => route('registrar.admin-tools.access-management.user-accounts.access-control.update', ['user' => '__ID__']),
        ]);
    }

    public function userAccountsData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|string|max:190',
            'last_name' => 'nullable|string|max:190',
            'first_name' => 'nullable|string|max:190',
            'user_type' => 'nullable|string|max:50',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $hasNormalizedTables = $this->hasNormalizedUserAccountTables();
        $hasLegacyStatusTable = Schema::hasTable('user_account_statuses');

        $query = User::query()
            ->select([
                'users.id',
                'users.username',
                'users.name',
                'users.email',
                'users.module',
            ]);

        if ($hasLegacyStatusTable) {
            $query
                ->leftJoin('user_account_statuses as uas', 'uas.user_id', '=', 'users.id')
                ->addSelect(DB::raw('COALESCE(uas.is_inactive, 0) as legacy_is_inactive'));
        } else {
            $query->addSelect(DB::raw('0 as legacy_is_inactive'));
        }

        if ($hasNormalizedTables) {
            $query
                ->leftJoin('user_account_profiles as uap', 'uap.user_id', '=', 'users.id')
                ->leftJoin('user_account_types as uat', 'uat.id', '=', 'uap.user_account_type_id')
                ->leftJoin('user_account_states as ust', 'ust.id', '=', 'uap.user_account_state_id')
                ->addSelect([
                    DB::raw('COALESCE(uap.is_sample, 0) as profile_is_sample'),
                    DB::raw('COALESCE(uat.code, users.module) as normalized_type_code'),
                    DB::raw('COALESCE(uat.name, users.module) as normalized_type_name'),
                    DB::raw("COALESCE(ust.code, '') as normalized_state_code"),
                ]);
        }

        if (!empty($validated['user_id'])) {
            $needle = '%' . trim((string) $validated['user_id']) . '%';
            $query->where('users.username', 'like', $needle);
        }

        if (!empty($validated['last_name'])) {
            $needle = '%' . trim((string) $validated['last_name']) . '%';
            $query->where('users.name', 'like', $needle);
        }

        if (!empty($validated['first_name'])) {
            $needle = '%' . trim((string) $validated['first_name']) . '%';
            $query->where('users.name', 'like', $needle);
        }

        if (!empty($validated['user_type'])) {
            $typeValue = strtolower(trim((string) $validated['user_type']));

            if ($hasNormalizedTables) {
                $query->where(function ($inner) use ($typeValue) {
                    $inner->whereRaw('LOWER(COALESCE(uat.code, users.module)) = ?', [$typeValue])
                        ->orWhereRaw('LOWER(COALESCE(uat.name, users.module)) = ?', [$typeValue]);
                });
            } else {
                $query->whereRaw('LOWER(users.module) = ?', [$typeValue]);
            }
        }

        $paginator = $query
            ->orderBy('users.name')
            ->orderBy('users.username')
            ->paginate($perPage, ['*'], 'page', $page);

        $rows = collect($paginator->items())
            ->map(function ($row) use ($hasNormalizedTables) {
                $fullName = trim((string) ($row->name ?: $row->username));
                list($lastName, $firstName) = $this->splitUserName($fullName);

                $typeCode = $this->normalizeModuleCode((string) ($row->module ?: 'user'));
                $typeName = ucfirst($typeCode);

                if ($hasNormalizedTables) {
                    $normalizedTypeCode = trim((string) ($row->normalized_type_code ?: ''));
                    $normalizedTypeName = trim((string) ($row->normalized_type_name ?: ''));
                    if ($normalizedTypeCode !== '') {
                        $typeCode = $this->normalizeModuleCode($normalizedTypeCode);
                    }
                    if ($normalizedTypeName !== '') {
                        $typeName = $normalizedTypeName;
                    }
                }

                $isInactive = $this->parseBooleanInput($row->legacy_is_inactive);
                if ($hasNormalizedTables) {
                    $stateCode = strtolower(trim((string) ($row->normalized_state_code ?: '')));
                    if ($stateCode === 'active') {
                        $isInactive = false;
                    } elseif ($stateCode === 'inactive') {
                        $isInactive = true;
                    }
                }

                return [
                    'pk' => (int) $row->id,
                    'userId' => (string) $row->username,
                    'lastName' => $lastName,
                    'firstName' => $firstName,
                    'fullName' => $fullName,
                    'userType' => $typeName,
                    'userTypeCode' => $typeCode,
                    'email' => (string) ($row->email ?: ''),
                    'inactive' => $isInactive,
                    'isSample' => $hasNormalizedTables ? $this->parseBooleanInput($row->profile_is_sample) : false,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'rows' => $rows,
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function userAccountAccessControlModules(): JsonResponse
    {
        if (!$this->hasUserAccessControlTables()) {
            return response()->json([
                'ok' => false,
                'message' => 'Access control tables are unavailable. Please run the access-control migration first.',
            ], 422);
        }

        $metadata = $this->fetchAccessControlMetadata();

        return response()->json([
            'ok' => true,
            'modules' => $metadata['modules'],
            'permissionTypes' => $metadata['permissionTypes'],
        ]);
    }

    public function userAccountAccessControlShow(User $user): JsonResponse
    {
        if (!$this->hasUserAccessControlTables()) {
            return response()->json([
                'ok' => false,
                'message' => 'Access control tables are unavailable. Please run the access-control migration first.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'data' => $this->buildUserAccessControlPayload($user),
        ]);
    }

    public function userAccountAccessControlUpdate(Request $request, User $user): JsonResponse
    {
        if (!$this->hasUserAccessControlTables()) {
            return response()->json([
                'ok' => false,
                'message' => 'Access control tables are unavailable. Please run the access-control migration first.',
            ], 422);
        }

        $permissionInput = $request->input('permissions');
        if (!is_array($permissionInput) || empty($permissionInput)) {
            throw ValidationException::withMessages([
                'permissions' => ['Please provide module permissions before saving access control.'],
            ]);
        }

        $metadata = $this->fetchAccessControlMetadata();
        $moduleIdByCode = $metadata['moduleIdByCode'];
        $permissionTypeIdByCode = $metadata['permissionTypeIdByCode'];

        foreach ($permissionInput as $moduleCode => $actions) {
            if (!array_key_exists($moduleCode, $moduleIdByCode)) {
                throw ValidationException::withMessages([
                    'permissions' => ['Unknown module code: ' . $moduleCode],
                ]);
            }

            if (!is_array($actions)) {
                throw ValidationException::withMessages([
                    'permissions' => ['Invalid permission payload for module: ' . $moduleCode],
                ]);
            }

            foreach ($actions as $permissionCode => $allowed) {
                if (!array_key_exists($permissionCode, $permissionTypeIdByCode)) {
                    throw ValidationException::withMessages([
                        'permissions' => ['Unknown permission type: ' . $permissionCode],
                    ]);
                }
            }
        }

        $rows = [];
        $now = now();

        foreach ($moduleIdByCode as $moduleCode => $moduleId) {
            $moduleActions = [];
            if (array_key_exists($moduleCode, $permissionInput) && is_array($permissionInput[$moduleCode])) {
                $moduleActions = $permissionInput[$moduleCode];
            }

            foreach ($permissionTypeIdByCode as $permissionCode => $permissionTypeId) {
                $rows[] = [
                    'user_id' => $user->id,
                    'access_control_module_id' => $moduleId,
                    'access_control_permission_type_id' => $permissionTypeId,
                    'is_allowed' => $this->parseBooleanInput(array_key_exists($permissionCode, $moduleActions) ? $moduleActions[$permissionCode] : false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($user, $rows) {
            UserAccessControl::query()->where('user_id', $user->id)->delete();
            if (!empty($rows)) {
                UserAccessControl::query()->insert($rows);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Access control saved successfully.',
            'data' => $this->buildUserAccessControlPayload($user),
        ]);
    }

    public function userAccountsUpdate(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|max:190|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:190',
            'email' => 'nullable|email|max:190|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|max:190',
            'inactive' => 'nullable',
            'user_type' => 'nullable|string|max:50',
        ]);

        $user->username = (string) $validated['user_id'];
        if (!empty($validated['full_name'])) {
            $user->name = (string) $validated['full_name'];
        }

        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'] !== '' ? (string) $validated['email'] : null;
        }

        $typeCode = $this->normalizeModuleCode((string) ($validated['user_type'] ?? $user->module));
        $user->module = $typeCode;

        if (!empty($validated['password'])) {
            $user->password = Hash::make((string) $validated['password']);
            $user->force_password_reset = false;
        }

        $user->save();

        $inactive = $this->parseBooleanInput($request->input('inactive', false));

        if (Schema::hasTable('user_account_statuses')) {
            UserAccountStatus::updateOrCreate(
                ['user_id' => $user->id],
                ['is_inactive' => $inactive]
            );
        }

        $this->syncUserAccountProfile($user, $inactive, $typeCode);

        list($lastName, $firstName) = $this->splitUserName((string) ($user->name ?: $user->username));
        $typeName = ucfirst($typeCode);

        if ($this->hasNormalizedUserAccountTables()) {
            $lookupName = DB::table('user_account_types')
                ->where('code', $typeCode)
                ->value('name');
            if (!empty($lookupName)) {
                $typeName = (string) $lookupName;
            }
        }

        return response()->json([
            'ok' => true,
            'row' => [
                'pk' => $user->id,
                'userId' => (string) $user->username,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'fullName' => (string) ($user->name ?: $user->username),
                'userType' => $typeName,
                'userTypeCode' => $typeCode,
                'email' => (string) ($user->email ?: ''),
                'inactive' => $inactive,
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

    private function hasNormalizedUserAccountTables(): bool
    {
        return Schema::hasTable('user_account_profiles')
            && Schema::hasTable('user_account_types')
            && Schema::hasTable('user_account_states');
    }

    private function hasUserAccessControlTables(): bool
    {
        return Schema::hasTable('access_control_modules')
            && Schema::hasTable('access_control_permission_types')
            && Schema::hasTable('user_access_controls');
    }

    private function fetchAccessControlMetadata(): array
    {
        $moduleRows = AccessControlModule::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'parent_id']);

        $permissionTypeRows = AccessControlPermissionType::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'code', 'label']);

        $modules = $moduleRows
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'code' => (string) $row->code,
                    'name' => (string) $row->name,
                    'parentId' => $row->parent_id ? (int) $row->parent_id : null,
                ];
            })
            ->values()
            ->all();

        $permissionTypes = $permissionTypeRows
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'code' => (string) $row->code,
                    'label' => (string) $row->label,
                ];
            })
            ->values()
            ->all();

        return [
            'moduleRows' => $moduleRows,
            'permissionTypeRows' => $permissionTypeRows,
            'modules' => $modules,
            'permissionTypes' => $permissionTypes,
            'moduleIdByCode' => $moduleRows->pluck('id', 'code')->toArray(),
            'permissionTypeIdByCode' => $permissionTypeRows->pluck('id', 'code')->toArray(),
        ];
    }

    private function buildDefaultUserAccessMatrix(string $moduleCode, array $modules, array $permissionTypes): array
    {
        $matrix = [];

        foreach ($modules as $module) {
            $code = (string) $module['code'];
            $matrix[$code] = [];

            foreach ($permissionTypes as $permissionType) {
                $matrix[$code][(string) $permissionType['code']] = false;
            }
        }

        $normalizedModuleCode = $this->normalizeModuleCode($moduleCode);
        if ($normalizedModuleCode === 'admin' || $normalizedModuleCode === 'registrar') {
            foreach ($matrix as $code => $actions) {
                foreach ($actions as $permissionCode => $flag) {
                    $matrix[$code][$permissionCode] = true;
                }
            }

            return $matrix;
        }

        if (!array_key_exists($normalizedModuleCode, $matrix)) {
            return $matrix;
        }

        if (array_key_exists('view', $matrix[$normalizedModuleCode])) {
            $matrix[$normalizedModuleCode]['view'] = true;
        }
        if (array_key_exists('edit', $matrix[$normalizedModuleCode])) {
            $matrix[$normalizedModuleCode]['edit'] = true;
        }

        return $matrix;
    }

    private function buildUserAccessControlPayload(User $user): array
    {
        $metadata = $this->fetchAccessControlMetadata();
        $moduleRows = $metadata['moduleRows'];
        $permissionTypeRows = $metadata['permissionTypeRows'];
        $modules = $metadata['modules'];
        $permissionTypes = $metadata['permissionTypes'];

        $moduleIds = $moduleRows->pluck('id')->all();
        $permissionTypeIds = $permissionTypeRows->pluck('id')->all();

        $storedRows = UserAccessControl::query()
            ->where('user_id', $user->id)
            ->whereIn('access_control_module_id', $moduleIds)
            ->whereIn('access_control_permission_type_id', $permissionTypeIds)
            ->get(['access_control_module_id', 'access_control_permission_type_id', 'is_allowed']);

        $storedMap = [];
        foreach ($storedRows as $row) {
            $moduleId = (int) $row->access_control_module_id;
            $permissionTypeId = (int) $row->access_control_permission_type_id;
            if (!array_key_exists($moduleId, $storedMap)) {
                $storedMap[$moduleId] = [];
            }

            $storedMap[$moduleId][$permissionTypeId] = $this->parseBooleanInput($row->is_allowed);
        }

        $defaultMatrix = [];
        if ($storedRows->isEmpty()) {
            $defaultMatrix = $this->buildDefaultUserAccessMatrix((string) ($user->module ?: ''), $modules, $permissionTypes);
        }

        $modulePayload = $moduleRows->map(function ($module) use ($permissionTypeRows, $storedMap, $defaultMatrix) {
            $moduleId = (int) $module->id;
            $moduleCode = (string) $module->code;

            $actions = [];
            foreach ($permissionTypeRows as $permissionType) {
                $permissionTypeId = (int) $permissionType->id;
                $permissionCode = (string) $permissionType->code;

                $allowedFromStore = false;
                if (array_key_exists($moduleId, $storedMap) && array_key_exists($permissionTypeId, $storedMap[$moduleId])) {
                    $allowedFromStore = $storedMap[$moduleId][$permissionTypeId];
                }

                if (!empty($defaultMatrix)) {
                    $allowedFromStore = $this->parseBooleanInput(
                        isset($defaultMatrix[$moduleCode]) && array_key_exists($permissionCode, $defaultMatrix[$moduleCode])
                            ? $defaultMatrix[$moduleCode][$permissionCode]
                            : false
                    );
                }

                $actions[$permissionCode] = $allowedFromStore;
            }

            return [
                'id' => $moduleId,
                'code' => $moduleCode,
                'name' => (string) $module->name,
                'parentId' => $module->parent_id ? (int) $module->parent_id : null,
                'actions' => $actions,
            ];
        })->values()->all();

        return [
            'user' => [
                'id' => (int) $user->id,
                'userId' => (string) $user->username,
                'name' => (string) ($user->name ?: $user->username),
                'userType' => ucfirst((string) ($user->module ?: 'user')),
            ],
            'permissionTypes' => $permissionTypes,
            'modules' => $modulePayload,
            'source' => $storedRows->isEmpty() ? 'role-default' : 'explicit',
        ];
    }

    private function syncUserAccountProfile(User $user, bool $inactive, string $typeCode): void
    {
        if (!$this->hasNormalizedUserAccountTables()) {
            return;
        }

        $normalizedCode = $this->normalizeModuleCode($typeCode);
        $now = now();

        DB::table('user_account_types')->updateOrInsert(
            ['code' => $normalizedCode],
            [
                'name' => ucfirst($normalizedCode),
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

        $typeId = DB::table('user_account_types')->where('code', $normalizedCode)->value('id');
        $stateId = DB::table('user_account_states')
            ->where('code', $inactive ? 'inactive' : 'active')
            ->value('id');

        if (!$typeId || !$stateId) {
            return;
        }

        $currentIsSample = DB::table('user_account_profiles')
            ->where('user_id', $user->id)
            ->value('is_sample');

        DB::table('user_account_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'user_account_type_id' => $typeId,
                'user_account_state_id' => $stateId,
                'is_sample' => $this->parseBooleanInput($currentIsSample),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function normalizeModuleCode(string $moduleCode): string
    {
        $code = strtolower(trim($moduleCode));
        if ($code === '') {
            return 'user';
        }

        if ($code === 'administrator') {
            return 'admin';
        }

        return $code;
    }

    private function parseBooleanInput($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
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

    public function studentGradeRecords(Request $request): JsonResponse
    {
        $studentNo = trim((string) $request->query('student_no', ''));
        if ($studentNo === '') {
            return response()->json(['ok' => true, 'records' => []]);
        }

        if (Schema::hasTable('student_grade_records')) {
            $count = StudentGradeRecord::where('student_no', $studentNo)->count();
            if ($count === 0) {
                $this->seedStudentGradeRecordsFor($studentNo);
            }
        }

        $records = StudentGradeRecord::where('student_no', $studentNo)
            ->orderByDesc('school_year')
            ->orderByRaw("CASE WHEN LOWER(term) LIKE '%first%' THEN 1 WHEN LOWER(term) LIKE '%second%' THEN 2 WHEN LOWER(term) LIKE '%summer%' THEN 3 ELSE 4 END")
            ->orderBy('subject_code')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'school_year' => $r->school_year,
                    'term' => $r->term,
                    'subject_code' => $r->subject_code,
                    'equiv_subject_code' => $r->equiv_subject_code,
                    'professor' => $r->professor,
                    'description' => $r->description,
                    'units' => $r->units,
                    'status' => $r->status,
                    'section_code' => $r->section_code,
                    'final_grade' => $r->final_grade,
                    'inc' => (bool) $r->inc,
                    'grade_status' => $r->grade_status,
                    'remarks' => $r->remarks,
                    'created_at' => optional($r->created_at)->toDateTimeString(),
                    'updated_at' => optional($r->updated_at)->toDateTimeString(),
                ];
            })
            ->values()
            ->all();

        return response()->json(['ok' => true, 'records' => $records]);
    }

    public function studentGradeRecordStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:80',
            'school_year' => 'required|string|max:30',
            'term' => 'required|string|max:30',
            'subject_code' => 'required|string|max:60',
            'equiv_subject_code' => 'nullable|string|max:60',
            'professor' => 'nullable|string|max:190',
            'description' => 'nullable|string|max:255',
            'units' => 'required|numeric|min:0|max:99',
            'status' => 'nullable|string|max:30',
            'section_code' => 'nullable|string|max:60',
            'final_grade' => 'nullable|numeric',
            'inc' => 'nullable|boolean',
            'grade_status' => 'nullable|string|max:10',
            'remarks' => 'nullable|string|max:255',
        ]);

        $sourceStudentId = Student::where('student_no', $validated['student_no'])->value('id');

        $record = StudentGradeRecord::create([
            'student_id' => $sourceStudentId,
            'student_no' => $validated['student_no'],
            'school_year' => trim($validated['school_year']),
            'term' => trim($validated['term']),
            'subject_code' => trim($validated['subject_code']),
            'equiv_subject_code' => isset($validated['equiv_subject_code']) ? trim($validated['equiv_subject_code']) : null,
            'professor' => isset($validated['professor']) ? trim($validated['professor']) : null,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'units' => (float) $validated['units'],
            'status' => isset($validated['status']) ? trim($validated['status']) : null,
            'section_code' => isset($validated['section_code']) ? trim($validated['section_code']) : null,
            'final_grade' => isset($validated['final_grade']) ? (float) $validated['final_grade'] : null,
            'inc' => (bool) ($validated['inc'] ?? false),
            'grade_status' => isset($validated['grade_status']) ? trim($validated['grade_status']) : null,
            'remarks' => isset($validated['remarks']) ? trim($validated['remarks']) : null,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function studentGradeRecordUpdate(Request $request, StudentGradeRecord $studentGradeRecord): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:30',
            'term' => 'required|string|max:30',
            'subject_code' => 'required|string|max:60',
            'equiv_subject_code' => 'nullable|string|max:60',
            'professor' => 'nullable|string|max:190',
            'description' => 'nullable|string|max:255',
            'units' => 'required|numeric|min:0|max:99',
            'status' => 'nullable|string|max:30',
            'section_code' => 'nullable|string|max:60',
            'final_grade' => 'nullable|numeric',
            'inc' => 'nullable|boolean',
            'grade_status' => 'nullable|string|max:10',
            'remarks' => 'nullable|string|max:255',
        ]);

        $studentGradeRecord->update([
            'school_year' => trim($validated['school_year']),
            'term' => trim($validated['term']),
            'subject_code' => trim($validated['subject_code']),
            'equiv_subject_code' => isset($validated['equiv_subject_code']) ? trim($validated['equiv_subject_code']) : null,
            'professor' => isset($validated['professor']) ? trim($validated['professor']) : null,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'units' => (float) $validated['units'],
            'status' => isset($validated['status']) ? trim($validated['status']) : null,
            'section_code' => isset($validated['section_code']) ? trim($validated['section_code']) : null,
            'final_grade' => isset($validated['final_grade']) ? (float) $validated['final_grade'] : null,
            'inc' => (bool) ($validated['inc'] ?? false),
            'grade_status' => isset($validated['grade_status']) ? trim($validated['grade_status']) : null,
            'remarks' => isset($validated['remarks']) ? trim($validated['remarks']) : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function studentGradeRecordDestroy(StudentGradeRecord $studentGradeRecord): JsonResponse
    {
        $studentGradeRecord->delete();

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

        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = array_values($configOptions['school_years'] ?? []);
        $semesterMap = is_array($configOptions['semester_map'] ?? null)
            ? $configOptions['semester_map']
            : [];
        $termOptions = SystemConfigSchoolTermOptions::semesterOptionsForYear(
            $semesterMap,
            (string) ($configOptions['default_school_year'] ?? '')
        );

        $defaultSchoolYear = count($schoolYearOptions)
            ? (string) $schoolYearOptions[0]
            : (string) ($configOptions['default_school_year'] ?? '');
        $defaultTerm = count($termOptions)
            ? (string) $termOptions[0]
            : (string) ($configOptions['default_semester'] ?? 'First');

        return view('registrar.admin-tools.student-maintenance.student-update', compact(
            'courseOptions',
            'operatorOptions',
            'schoolYearOptions',
            'semesterMap',
            'termOptions',
            'defaultSchoolYear',
            'defaultTerm'
        ));
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

    private function buildAcademicTermRows(): array
    {
        if (!Schema::hasTable('academic_terms')) {
            return [];
        }

        return AcademicTerm::query()
            ->orderByDesc('school_year')
            ->orderByRaw("CASE
                WHEN LOWER(TRIM(term)) IN ('first', '1st semester', 'first semester') THEN 1
                WHEN LOWER(TRIM(term)) IN ('second', '2nd semester', 'second semester') THEN 2
                WHEN LOWER(TRIM(term)) IN ('summer', 'summer semester') THEN 3
                ELSE 4
            END")
            ->orderByDesc('id')
            ->get()
            ->map(function ($row) {
                $normalizedSemester = $this->normalizeSemesterLabel((string) $row->term);

                return [
                    'id' => (int) $row->id,
                    'schoolYear' => (string) $row->school_year,
                    'semester' => $normalizedSemester,
                    'label' => (string) $row->school_year . ' - ' . $normalizedSemester,
                ];
            })
            ->values()
            ->all();
    }

    private function mapSignatureRow(SystemConfigNameSignature $row): array
    {
        $designationName = '';
        if ($row->relationLoaded('designation') && $row->designation) {
            $designationName = (string) $row->designation->name;
        }

        if ($designationName === '' && $row->designation_id) {
            $designationName = (string) SystemConfigSignatureDesignation::query()
                ->where('id', $row->designation_id)
                ->value('name');
        }

        $signatureUrl = '';
        if (!empty($row->signature_path)) {
            $signatureUrl = asset('storage/' . ltrim((string) $row->signature_path, '/'));
        }

        return [
            'id' => $row->id,
            'designationId' => (int) $row->designation_id,
            'designation' => $designationName,
            'name' => (string) $row->signer_name,
            'signaturePath' => (string) ($row->signature_path ?: ''),
            'signatureUrl' => $signatureUrl,
        ];
    }

    private function mapCutoffRow(SystemCutoffEntry $row): array
    {
        $typeCode = '';
        $typeName = '';

        if ($row->relationLoaded('cutoffType') && $row->cutoffType) {
            $typeCode = (string) $row->cutoffType->code;
            $typeName = (string) $row->cutoffType->name;
        }

        if ($typeCode === '' || $typeName === '') {
            $typeRow = SystemCutoffType::query()
                ->where('id', $row->cutoff_type_id)
                ->first();

            if ($typeRow) {
                $typeCode = (string) $typeRow->code;
                $typeName = (string) $typeRow->name;
            }
        }

        return [
            'id' => $row->id,
            'typeCode' => $typeCode,
            'type' => $typeName,
            'sy' => (string) ($row->school_year ?: ''),
            'semester' => (string) ($row->semester ?: ''),
            'eventDate' => optional($row->event_date)->format('Y-m-d') ?: '',
            'cutoffDate' => optional($row->cutoff_date)->format('Y-m-d') ?: '',
            'studentNo' => (string) ($row->student_no ?: ''),
            'notes' => (string) ($row->notes ?: ''),
        ];
    }

    private function mapCurriculumDisplayRow(SystemCurriculumDisplaySetting $row): array
    {
        return [
            'id' => $row->id,
            'sy' => (string) ($row->school_year ?: ''),
            'semester' => (string) ($row->semester ?: ''),
            'status' => (string) $row->display_status,
        ];
    }

    private function sanitizeSchoolYear($schoolYear): string
    {
        return trim((string) $schoolYear);
    }

    private function normalizeSemesterLabel($semester): string
    {
        $normalized = strtolower(trim((string) $semester));

        if ($normalized === '') {
            return '';
        }

        if (in_array($normalized, ['first', '1st', '1st semester', 'first semester'], true)) {
            return 'First';
        }

        if (in_array($normalized, ['second', '2nd', '2nd semester', 'second semester'], true)) {
            return 'Second';
        }

        if (in_array($normalized, ['summer', 'summer semester'], true)) {
            return 'Summer';
        }

        return ucfirst($normalized);
    }

    private function semesterAliases(string $semester): array
    {
        $normalized = strtolower(trim($semester));

        if ($normalized === 'first') {
            return ['first', '1st semester', 'first semester'];
        }

        if ($normalized === 'second') {
            return ['second', '2nd semester', 'second semester'];
        }

        if ($normalized === 'summer') {
            return ['summer', 'summer semester'];
        }

        return [$normalized];
    }

    private function resolveAcademicTerm(string $schoolYear, string $semester): AcademicTerm
    {
        $normalizedSchoolYear = $this->sanitizeSchoolYear($schoolYear);
        $normalizedSemester = $this->normalizeSemesterLabel($semester);

        return AcademicTerm::firstOrCreate(
            ['canonical_key' => strtolower($normalizedSchoolYear . '|' . $normalizedSemester)],
            ['school_year' => $normalizedSchoolYear, 'term' => $normalizedSemester]
        );
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
            'audiences' => $this->resolveAcademicCalendarAudiences($event),
        ];
    }

    private function normalizeAcademicCalendarAudiences($audiences): array
    {
        $allowedCodes = ['student', 'faculty', 'applicant'];
        if (!is_array($audiences) || empty($audiences)) {
            return $allowedCodes;
        }

        $normalizedCodes = collect($audiences)
            ->map(function ($code) {
                return strtolower(trim((string) $code));
            })
            ->filter(function ($code) use ($allowedCodes) {
                return in_array($code, $allowedCodes, true);
            })
            ->unique()
            ->values()
            ->all();

        return empty($normalizedCodes) ? $allowedCodes : $normalizedCodes;
    }

    private function syncAcademicCalendarAudiences(AcademicCalendarEvent $event, array $audienceCodes): void
    {
        if (!Schema::hasTable('academic_calendar_event_audiences')
            || !Schema::hasTable('academic_calendar_audience_types')) {
            return;
        }

        $audienceTypeIds = [];
        foreach ($audienceCodes as $audienceCode) {
            $audienceType = AcademicCalendarAudienceType::query()->firstOrCreate(
                ['code' => $audienceCode],
                ['label' => ucfirst($audienceCode)]
            );

            $audienceTypeIds[] = (int) $audienceType->id;
        }

        $event->audienceTypes()->sync($audienceTypeIds);
    }

    private function resolveAcademicCalendarAudiences(AcademicCalendarEvent $event): array
    {
        $defaultAudiences = ['student', 'faculty', 'applicant'];

        if (!Schema::hasTable('academic_calendar_event_audiences')
            || !Schema::hasTable('academic_calendar_audience_types')) {
            return $defaultAudiences;
        }

        $audienceTypes = $event->relationLoaded('audienceTypes')
            ? $event->getRelation('audienceTypes')
            : $event->audienceTypes()->get(['code']);

        $resolvedCodes = $audienceTypes
            ->pluck('code')
            ->map(function ($code) {
                return strtolower(trim((string) $code));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($resolvedCodes) ? $defaultAudiences : $resolvedCodes;
    }

    private function mapAnnouncementRow(SystemAnnouncement $announcement): array
    {
        $audienceCode = $this->normalizeAnnouncementAudienceCode($announcement->announcement_type) ?: SystemAnnouncement::AUDIENCE_EVERYONE;
        $resolvedProgram = trim((string) $announcement->program);

        if ($resolvedProgram === '') {
            $course = $announcement->relationLoaded('canonicalCourse')
                ? $announcement->getRelation('canonicalCourse')
                : null;

            if (!$course && !empty($announcement->course_id)) {
                $course = Course::query()
                    ->select('id', 'code', 'name')
                    ->find((int) $announcement->course_id);
            }

            if ($course) {
                $resolvedProgram = trim((string) ($course->code ?: $course->name));
            }
        }

        if ($resolvedProgram === '') {
            $resolvedProgram = 'All Programs';
        }

        return [
            'id' => $announcement->id,
            'from' => optional($announcement->date_from)->format('Y-m-d') ?: '',
            'to' => optional($announcement->date_to)->format('Y-m-d') ?: '',
            'title' => (string) $announcement->title,
            'type' => $audienceCode,
            'typeLabel' => $this->announcementAudienceLabel($audienceCode),
            'program' => $resolvedProgram,
            'content' => (string) $announcement->content,
        ];
    }

    private function announcementAudienceOptions(): array
    {
        return [
            SystemAnnouncement::AUDIENCE_EVERYONE => 'Everyone',
            SystemAnnouncement::AUDIENCE_STUDENTS => 'Students',
            SystemAnnouncement::AUDIENCE_FACULTY => 'Faculty',
            SystemAnnouncement::AUDIENCE_STAFF => 'Staff',
            SystemAnnouncement::AUDIENCE_APPLICANT => 'Applicant',
        ];
    }

    private function announcementAudienceAliases(): array
    {
        return [
            'everyone' => SystemAnnouncement::AUDIENCE_EVERYONE,
            'all' => SystemAnnouncement::AUDIENCE_EVERYONE,
            'all users' => SystemAnnouncement::AUDIENCE_EVERYONE,
            'all user' => SystemAnnouncement::AUDIENCE_EVERYONE,
            'students' => SystemAnnouncement::AUDIENCE_STUDENTS,
            'student' => SystemAnnouncement::AUDIENCE_STUDENTS,
            'faculty' => SystemAnnouncement::AUDIENCE_FACULTY,
            'teacher' => SystemAnnouncement::AUDIENCE_FACULTY,
            'teachers' => SystemAnnouncement::AUDIENCE_FACULTY,
            'staff' => SystemAnnouncement::AUDIENCE_STAFF,
            'registrar' => SystemAnnouncement::AUDIENCE_STAFF,
            'admin' => SystemAnnouncement::AUDIENCE_STAFF,
            'administrator' => SystemAnnouncement::AUDIENCE_STAFF,
            'applicant' => SystemAnnouncement::AUDIENCE_APPLICANT,
            'applicants' => SystemAnnouncement::AUDIENCE_APPLICANT,
        ];
    }

    private function normalizeAnnouncementAudienceCode($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return SystemAnnouncement::AUDIENCE_EVERYONE;
        }

        $aliases = $this->announcementAudienceAliases();
        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return null;
    }

    private function announcementAudienceLabel(string $audienceCode): string
    {
        $options = $this->announcementAudienceOptions();
        return $options[$audienceCode] ?? $options[SystemAnnouncement::AUDIENCE_EVERYONE];
    }

    private function ensureAnnouncementAudienceLookups(): void
    {
        if (!Schema::hasTable('announcement_types')) {
            return;
        }

        foreach ($this->announcementAudienceOptions() as $code => $label) {
            AnnouncementType::query()->updateOrCreate(
                ['code' => $code],
                ['label' => $label]
            );
        }
    }

    private function resolveAnnouncementTypeId(string $audienceCode): ?int
    {
        if (!Schema::hasTable('announcement_types')) {
            return null;
        }

        $normalizedCode = $this->normalizeAnnouncementAudienceCode($audienceCode) ?: SystemAnnouncement::AUDIENCE_EVERYONE;

        $lookup = AnnouncementType::query()
            ->whereRaw('LOWER(TRIM(code)) = ?', [$normalizedCode])
            ->first();

        if (!$lookup) {
            $lookup = AnnouncementType::query()->create([
                'code' => $normalizedCode,
                'label' => $this->announcementAudienceLabel($normalizedCode),
            ]);
        }

        return $lookup ? (int) $lookup->id : null;
    }

    private function findDuplicateAnnouncement(array $payload): ?SystemAnnouncement
    {
        if (!Schema::hasTable('system_announcements')) {
            return null;
        }

        $normalizedAudience = $this->normalizeAnnouncementAudienceCode($payload['audience_code'] ?? null)
            ?: SystemAnnouncement::AUDIENCE_EVERYONE;
        $normalizedTitle = strtolower(trim((string) ($payload['title'] ?? '')));
        $normalizedProgram = strtolower(trim((string) ($payload['program'] ?? 'All Programs')));
        $normalizedContent = strtolower(trim((string) ($payload['content'] ?? '')));

        $hasProgramColumn = Schema::hasColumn('system_announcements', 'program');
        $hasCourseColumn = Schema::hasColumn('system_announcements', 'course_id');
        $hasAnnouncementTypeColumn = Schema::hasColumn('system_announcements', 'announcement_type');
        $hasAnnouncementTypeIdColumn = Schema::hasColumn('system_announcements', 'announcement_type_id');

        $query = SystemAnnouncement::query()
            ->whereDate('date_from', (string) ($payload['from'] ?? ''))
            ->whereDate('date_to', (string) ($payload['to'] ?? ''))
            ->whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle])
            ->whereRaw('LOWER(TRIM(content)) = ?', [$normalizedContent]);

        if ($hasAnnouncementTypeColumn) {
            $query->whereRaw('LOWER(TRIM(announcement_type)) = ?', [$normalizedAudience]);
        } elseif ($hasAnnouncementTypeIdColumn) {
            $announcementTypeId = isset($payload['announcement_type_id']) ? (int) $payload['announcement_type_id'] : 0;
            if ($announcementTypeId > 0) {
                $query->where('announcement_type_id', $announcementTypeId);
            } else {
                $query->whereNull('announcement_type_id');
            }
        }

        if ($hasProgramColumn) {
            $query->whereRaw('LOWER(TRIM(program)) = ?', [$normalizedProgram]);
        }

        if (!empty($payload['exclude_id'])) {
            $query->where('id', '!=', (int) $payload['exclude_id']);
        }

        if ($hasCourseColumn && array_key_exists('course_id', $payload)) {
            if (!empty($payload['course_id'])) {
                $query->where('course_id', (int) $payload['course_id']);
            } else {
                $query->whereNull('course_id');
            }
        }

        return $query->orderByDesc('id')->first();
    }

    private function buildAnnouncementNotificationPayload(SystemAnnouncement $announcement): array
    {
        $titleText = trim((string) $announcement->title);
        $title = $titleText !== ''
            ? 'Announcement: ' . $titleText
            : 'New Registrar Announcement';

        $content = trim((string) $announcement->content);
        $message = $content !== '' ? $content : 'A registrar announcement is available.';

        $fromLabel = optional($announcement->date_from)->format('M d, Y');
        $toLabel = optional($announcement->date_to)->format('M d, Y');
        $dateText = '';

        if ($fromLabel && $toLabel && $fromLabel !== $toLabel) {
            $dateText = 'Effective from ' . $fromLabel . ' to ' . $toLabel . '.';
        } elseif ($fromLabel) {
            $dateText = 'Effective on ' . $fromLabel . '.';
        } elseif ($toLabel) {
            $dateText = 'Available until ' . $toLabel . '.';
        }

        if ($dateText !== '') {
            if ($message !== '' && !preg_match('/[.!?]$/', $message)) {
                $message .= '.';
            }

            $message = trim($message . ' ' . $dateText);
        }

        return [
            'title' => $title,
            'message' => $message,
        ];
    }

    private function syncAnnouncementPortalNotification(SystemAnnouncement $announcement, ?int $createdByUserId = null): void
    {
        if (!Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'SYSTEM_ANNOUNCEMENT_POSTED'],
            ['name' => 'System Announcement Posted']
        );

        $payload = $this->buildAnnouncementNotificationPayload($announcement);

        $notification = PortalNotification::query()->firstOrCreate(
            [
                'source_module' => 'system_announcement',
                'source_reference' => 'system_announcement:' . $announcement->id,
            ],
            [
                'notification_type_id' => $type->id,
                'title' => $payload['title'],
                'message' => $payload['message'],
                'source_url' => '',
                'created_by_user_id' => $createdByUserId,
            ]
        );

        $hasChanges = false;

        if ((int) $notification->notification_type_id !== (int) $type->id) {
            $notification->notification_type_id = $type->id;
            $hasChanges = true;
        }

        if ((string) $notification->title !== (string) $payload['title']) {
            $notification->title = (string) $payload['title'];
            $hasChanges = true;
        }

        if ((string) $notification->message !== (string) $payload['message']) {
            $notification->message = (string) $payload['message'];
            $hasChanges = true;
        }

        if ((string) ($notification->source_url ?: '') !== '') {
            $notification->source_url = '';
            $hasChanges = true;
        }

        if ($createdByUserId && empty($notification->created_by_user_id)) {
            $notification->created_by_user_id = $createdByUserId;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $notification->save();
        }

        $this->syncAnnouncementNotificationDeliveries($notification, $announcement);
    }

    private function announcementAudienceTargetModules(string $audienceCode): array
    {
        $normalizedAudience = $this->normalizeAnnouncementAudienceCode($audienceCode) ?: SystemAnnouncement::AUDIENCE_EVERYONE;

        $map = [
            SystemAnnouncement::AUDIENCE_EVERYONE => ['student', 'faculty', 'registrar', 'applicant'],
            SystemAnnouncement::AUDIENCE_STUDENTS => ['student'],
            SystemAnnouncement::AUDIENCE_FACULTY => ['faculty'],
            SystemAnnouncement::AUDIENCE_STAFF => ['registrar'],
            SystemAnnouncement::AUDIENCE_APPLICANT => ['applicant'],
        ];

        return $map[$normalizedAudience] ?? $map[SystemAnnouncement::AUDIENCE_EVERYONE];
    }

    private function syncAnnouncementNotificationDeliveries(PortalNotification $notification, SystemAnnouncement $announcement): void
    {
        if (!Schema::hasTable('notification_deliveries') || !Schema::hasTable('users')) {
            return;
        }

        $targetModules = $this->announcementAudienceTargetModules((string) $announcement->announcement_type);
        $targetUserIds = User::query()
            ->whereIn('module', $targetModules)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($targetUserIds)) {
            NotificationDelivery::query()
                ->where('portal_notification_id', $notification->id)
                ->delete();

            return;
        }

        NotificationDelivery::query()
            ->where('portal_notification_id', $notification->id)
            ->whereNotIn('user_id', $targetUserIds)
            ->delete();

        $deliveredAt = $announcement->created_at ?: now();

        foreach ($targetUserIds as $targetUserId) {
            NotificationDelivery::query()->firstOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => $targetUserId,
                ],
                [
                    'delivered_at' => $deliveredAt,
                ]
            );
        }
    }

    private function removeAnnouncementPortalNotification(SystemAnnouncement $announcement): void
    {
        if (!Schema::hasTable('portal_notifications')) {
            return;
        }

        $notifications = PortalNotification::query()
            ->where('source_module', 'system_announcement')
            ->where('source_reference', 'system_announcement:' . $announcement->id)
            ->get();

        if ($notifications->isEmpty()) {
            return;
        }

        foreach ($notifications as $notification) {
            if (Schema::hasTable('notification_deliveries')) {
                NotificationDelivery::query()
                    ->where('portal_notification_id', $notification->id)
                    ->delete();
            }

            $notification->delete();
        }
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
        $this->ensureAnnouncementAudienceLookups();

        $hasProgramColumn = Schema::hasColumn('system_announcements', 'program');

        $rows = [
            [
                'date_from' => '2026-01-13',
                'date_to' => '2026-01-31',
                'title' => 'Academic Year 2025-2026 Midterm Examination',
                'announcement_type' => SystemAnnouncement::AUDIENCE_EVERYONE,
                'content' => 'Midterm examinations will run from January 13 to January 31. Please settle pending requirements.',
            ],
            [
                'date_from' => '2026-02-15',
                'date_to' => '2026-02-22',
                'title' => 'Final Examination Week Advisory',
                'announcement_type' => SystemAnnouncement::AUDIENCE_STUDENTS,
                'content' => 'Final examination schedule and assigned rooms are available at the registrar help desk.',
            ],
        ];

        foreach ($rows as $row) {
            if ($hasProgramColumn) {
                $row['program'] = 'All Programs';
            }

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

    private function seedStudentGradeRecordsFor(string $studentNo): void
    {
        $dummyTerms = [
            ['sy' => '2022-2023', 'term' => 'Second', 'subjects' => [
                ['code' => 'CDI8', 'equiv' => 'CDI8', 'prof' => null, 'desc' => 'TRAFFIC MANAGEMENT AND TRANSPORT SECURITY', 'units' => 3, 'grade' => 1.75, 'status' => 'P'],
                ['code' => 'CRIMNLSTC3', 'equiv' => 'CRIMNLSTC3', 'prof' => null, 'desc' => 'FORENSIC CHEMISTRY AND TOXICOLOGY', 'units' => 3, 'grade' => 2.50, 'status' => 'P'],
                ['code' => 'DT222', 'equiv' => 'DT222', 'prof' => null, 'desc' => 'MARKSMANSHIP & COMBAT SHOOTING', 'units' => 2, 'grade' => 2.00, 'status' => 'P'],
                ['code' => 'ECO210', 'equiv' => 'ECO210', 'prof' => null, 'desc' => 'BASIC ECONOMICS WITH TAXATION AND AGRARIAN REFORM', 'units' => 3, 'grade' => 2.00, 'status' => 'P'],
                ['code' => 'ENG212', 'equiv' => 'ENG212', 'prof' => null, 'desc' => 'PHILIPPINE LITERATURE', 'units' => 3, 'grade' => 2.00, 'status' => 'P'],
                ['code' => 'PHILO202', 'equiv' => 'PHILO202', 'prof' => null, 'desc' => 'LOGIC', 'units' => 3, 'grade' => 2.00, 'status' => 'P'],
            ]],
            ['sy' => '2023-2024', 'term' => 'First', 'subjects' => [
                ['code' => 'CRIM111', 'equiv' => 'CRIM111', 'prof' => null, 'desc' => 'INTRO TO CRIMINOLOGY & PSYCHOLOGY OF CRIMES', 'units' => 3, 'grade' => 1.75, 'status' => 'P'],
                ['code' => 'ENG111A', 'equiv' => 'ENG111A', 'prof' => null, 'desc' => 'STUDY AND THINKING SKILLS', 'units' => 3, 'grade' => 1.75, 'status' => 'P'],
                ['code' => 'MATH100', 'equiv' => 'MATH100', 'prof' => null, 'desc' => 'COLLEGE ALGEBRA', 'units' => 3, 'grade' => 1.75, 'status' => 'P'],
                ['code' => 'SOCSC111', 'equiv' => 'SOCSC111', 'prof' => null, 'desc' => 'GENERAL PSYCHOLOGY W/ POP ED.', 'units' => 3, 'grade' => 1.50, 'status' => 'P'],
            ]],
            ['sy' => '2023-2024', 'term' => 'Second', 'subjects' => [
                ['code' => 'CRIM113', 'equiv' => 'CRIM113', 'prof' => 'MANALAC, REYNALDO ERBER', 'desc' => 'ETHICS AND VALUES', 'units' => 3, 'grade' => 2.00, 'status' => 'P'],
                ['code' => 'CRIS211', 'equiv' => 'CRIS211', 'prof' => 'CADEJITA AALBasam', 'desc' => 'PERSONAL IDENTIFICATION (FINGERPRINTS)', 'units' => 3, 'grade' => 2.25, 'status' => 'P'],
                ['code' => 'CRJO313', 'equiv' => 'CRJO313', 'prof' => 'CADEJITA AALBasam', 'desc' => 'QUESTIONED DOCUMENT EXAMINATION', 'units' => 3, 'grade' => 2.50, 'status' => 'P'],
                ['code' => 'ENG121', 'equiv' => 'ENG121', 'prof' => 'KOMJAK, LURESITA GRAVIOLA', 'desc' => 'COMMUNICATION ARTS 2', 'units' => 3, 'grade' => 1.75, 'status' => 'P'],
                ['code' => 'FIL121', 'equiv' => 'FIL121', 'prof' => 'SANTOS, ANGELINE MALT', 'desc' => 'PAGBASA AT PAGSULAT TUNGO SA PANANALIKSIK', 'units' => 3, 'grade' => 2.50, 'status' => 'P'],
                ['code' => 'STAT311', 'equiv' => 'STAT311', 'prof' => 'CAPRILLO, TERESITA', 'desc' => 'BASIC STATISTICS', 'units' => 3, 'grade' => 2.25, 'status' => 'P'],
            ]],
        ];

        $sourceStudentId = Student::where('student_no', $studentNo)->value('id');

        foreach ($dummyTerms as $term) {
            foreach ($term['subjects'] as $subject) {
                StudentGradeRecord::create([
                    'student_id' => $sourceStudentId,
                    'student_no' => $studentNo,
                    'school_year' => $term['sy'],
                    'term' => $term['term'],
                    'subject_code' => $subject['code'],
                    'equiv_subject_code' => $subject['equiv'],
                    'professor' => $subject['prof'],
                    'description' => $subject['desc'],
                    'units' => $subject['units'],
                    'section_code' => 'BSCRIM',
                    'final_grade' => $subject['grade'],
                    'inc' => false,
                    'grade_status' => $subject['status'],
                    'remarks' => null,
                ]);
            }
        }
    }
}
