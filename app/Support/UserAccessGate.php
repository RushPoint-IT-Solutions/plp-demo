<?php

namespace App\Support;

use App\AccessControlModule;
use App\AccessControlPermissionType;
use App\User;
use App\UserAccessControl;
use Illuminate\Support\Facades\Schema;

class UserAccessGate
{
    public static function allows(User $user = null, string $moduleCode = null, string $permissionCode = 'view'): bool
    {
        if (!$user || !$moduleCode) {
            return false;
        }

        $normalizedRole = self::normalizeRole((string) ($user->module ?: ''));
        $moduleCode = trim((string) $moduleCode);
        $permissionCode = trim((string) ($permissionCode ?: 'view'));

        if (in_array($normalizedRole, ['admin', 'registrar'], true) && !self::hasTables()) {
            return true;
        }

        if (!self::hasTables()) {
            return false;
        }

        $module = AccessControlModule::query()
            ->where('code', $moduleCode)
            ->where('is_active', true)
            ->first(['id', 'code']);

        $permissionType = AccessControlPermissionType::query()
            ->where('code', $permissionCode)
            ->first(['id', 'code']);

        if (!$module || !$permissionType) {
            return in_array($normalizedRole, ['admin', 'registrar'], true);
        }

        $hasExplicitRows = UserAccessControl::query()
            ->where('user_id', (int) $user->id)
            ->exists();

        if (!$hasExplicitRows) {
            return self::defaultAllows($normalizedRole, $moduleCode, $permissionCode);
        }

        $allowed = UserAccessControl::query()
            ->where('user_id', (int) $user->id)
            ->where('access_control_module_id', (int) $module->id)
            ->where('access_control_permission_type_id', (int) $permissionType->id)
            ->value('is_allowed');

        if ($permissionCode !== 'view' && self::truthy($allowed)) {
            return true;
        }

        return self::truthy($allowed);
    }

    public static function routeModuleCode(?string $routeName): ?string
    {
        $routeName = (string) $routeName;

        $exact = [
            'registrar.dashboard' => 'registrar_dashboard',
            'registrar.messaging' => 'communication_messages_module',
            'registrar.communication.tickets' => 'communication_ticketing_system',
            'registrar.communication.stakeholders' => 'communication_stakeholders',
            'registrar.communication.email-templates' => 'communication_email_templates',
            'registrar.process.application' => 'admissions_application_list',
            'registrar.process.document-list' => 'admissions_document_submission',
            'registrar.process.approval-status' => 'admissions_approval_status',
            'registrar.process.exam-list' => 'admissions_exam_schedule',
            'registrar.process.exam-interview-scheduling' => 'admissions_exam_interview_scheduling',
            'registrar.process.requirements' => 'admissions_requirements',
            'registrar.process.batch-upload' => 'admissions_batch_upload_photos',
            'registrar.process.citizenship' => 'admissions_citizenship',
            'registrar.process.exam-category' => 'admissions_exam_category',
            'registrar.process.religion.index' => 'admissions_religion',
            'registrar.process.reports' => 'reports_academic_reports',
            'registrar.registrar-menu.student-mgmt.student-records' => 'student_records_student_list',
            'registrar.registrar-menu.student-mgmt.student-enrollment' => 'student_records_enrollment_list',
            'registrar.registrar-menu.student-mgmt.clinic-record' => 'student_records_clinic_records',
            'registrar.registrar-menu.alumni.tracker' => 'reports_alumni_tracker',
            'registrar.services.student-account.student-discipline' => 'student_records_discipline',
            'registrar.services.student-account.family' => 'student_records_family',
            'registrar.services.student-account.change-password' => 'student_records_change_password',
            'registrar.registrar-menu.academic-master.program-file' => 'academics_program_file',
            'registrar.registrar-menu.academic-master.subject-file' => 'academics_subject_file',
            'registrar.registrar-menu.academic-master.curriculum-file' => 'academics_curriculum_file',
            'registrar.registrar-menu.academic-master.curriculum-year-tracking' => 'academics_curriculum_year_tracking',
            'registrar.registrar-menu.academic-master.pre-requisites' => 'academics_prerequisites',
            'registrar.registrar-menu.scheduling.room-file' => 'academics_room_file',
            'registrar.registrar-menu.scheduling.room-section-offering-management' => 'academics_room_section_offering_management',
            'registrar.registrar-menu.scheduling.coordination-deans-faculty' => 'academics_coordination_deans_faculty',
            'registrar.registrar-menu.scheduling.section-offering' => 'academics_section_offering',
            'registrar.registrar-menu.scheduling.class-schedule-preparation' => 'academics_class_schedule_preparation',
            'registrar.registrar-menu.scheduling.slot-monitoring' => 'academics_slot_monitoring',
            'registrar.registrar-menu.scheduling.section-merging' => 'academics_section_merging',
            'registrar.services.classroom-faculty.class-list' => 'academics_class_list',
            'registrar.services.classroom-faculty.attendance' => 'academics_attendance',
            'registrar.services.classroom-faculty.faculty-loads' => 'academics_faculty_loads',
            'registrar.services.grading-academic.grading-system' => 'academics_grading_system',
            'registrar.services.grading-academic.grading-periods' => 'academics_grading_periods',
            'registrar.services.grading-academic.grading-components' => 'academics_grading_components',
            'registrar.services.grading-academic.transmutation' => 'academics_transmutation_table',
            'registrar.services.grading-academic.incomplete-failing' => 'academics_incomplete_failing_grades',
            'registrar.services.grading-academic.deficiency' => 'academics_deficiency',
            'registrar.services.grading-academic.scholastic-comments' => 'academics_scholastic_comments',
            'registrar.registrar-menu.faculty-mgmt.faculty-list' => 'faculty_directory',
            'registrar.registrar-menu.faculty-mgmt.grading-sheet' => 'faculty_grading_sheets',
            'registrar.registrar-menu.faculty-mgmt.evaluation' => 'faculty_evaluation',
            'registrar.registrar-menu.forms.diploma' => 'documents_forms_diploma',
            'registrar.registrar-menu.forms.official-grade-report' => 'documents_forms_official_grade_report',
            'registrar.registrar-menu.forms.honorable-dismissal' => 'documents_forms_honorable_dismissal',
            'registrar.registrar-menu.forms.application-leave-of-absence-enrolled' => 'documents_forms_leave_of_absence',
            'registrar.registrar-menu.forms.permission-cross-enroll' => 'documents_forms_cross_enroll',
            'registrar.registrar-menu.forms.request-form-f-137a' => 'documents_forms_f137a',
            'registrar.registrar-menu.forms.graduation-clearance' => 'documents_forms_graduation_clearance',
            'registrar.registrar-menu.forms.waiver-cancellation' => 'documents_forms_waiver_cancellation',
            'registrar.registrar-menu.forms.citizens-charter' => 'documents_forms_citizens_charter',
            'registrar.services.reports-admin.academic-reports' => 'reports_academic_reports',
            'registrar.services.reports-admin.certifications' => 'reports_certifications',
            'registrar.services.reports-admin.tagging-of-graduates' => 'reports_graduation_tagging',
            'registrar.services.reports-admin.guidance-reports' => 'reports_guidance_reports',
            'registrar.admin-tools.system-config.configuration' => 'system_configuration',
            'registrar.admin-tools.system-config.academic-calendar' => 'system_academic_calendar',
            'registrar.admin-tools.system-config.announcement' => 'system_announcements',
            'registrar.admin-tools.system-config.admission-config' => 'system_configuration',
            'registrar.admin-tools.access-management.user-accounts' => 'system_user_accounts',
            'registrar.admin-tools.access-management.report-access' => 'system_report_access',
            'registrar.admin-tools.master-files.faculty-file' => 'system_faculty_file',
            'registrar.admin-tools.master-files.student-profile' => 'system_student_profile',
            'registrar.admin-tools.master-files.student-grade-file' => 'system_student_grade_file',
            'registrar.admin-tools.student-maintenance.student-update' => 'system_student_update',
            'registrar.admin-tools.audit-trail' => 'system_audit_trail',
            'student.access-module' => 'student_portal_dashboard',
            'student.section-offering' => 'student_portal_dashboard',
            'student.schedule' => 'student_portal_dashboard',
            'student.cor' => 'student_portal_dashboard',
            'student.events' => 'student_portal_dashboard',
            'student.grades' => 'student_portal_grades',
            'student.profile' => 'student_portal_profile',
            'student.profile.edit' => 'student_portal_profile',
            'parent.access-module' => 'parent_portal_dashboard',
            'parent.dashboard' => 'parent_portal_dashboard',
            'parent.grades' => 'parent_portal_grades',
            'parent.student-profile' => 'parent_portal_grades',
            'parent.calendar' => 'parent_portal_dashboard',
            'parent.profile' => 'parent_portal_dashboard',
            'parent.contact-us' => 'parent_portal_contact_us',
            'parent.change-password' => 'parent_portal_dashboard',
            'parent.change-password.update' => 'parent_portal_dashboard',
            'parent.messaging' => 'parent_portal_contact_us',
            'applicant.application-form' => 'applicant_portal_application',
            'applicant.schedule-of-exam' => 'applicant_portal_dashboard',
            'applicant.calendar' => 'applicant_portal_dashboard',
            'applicant.correspondence' => 'applicant_portal_dashboard',
            'applicant.exam-result' => 'applicant_portal_dashboard',
            'applicant.messaging' => 'applicant_portal_help_center',
            'applicant.medical-clearance' => 'applicant_portal_requirements',
            'applicant.documents-submitted' => 'applicant_portal_requirements',
            'faculty.load' => 'faculty_faculty_loads',
            'faculty.load.download' => 'faculty_faculty_loads',
            'faculty.class-list' => 'faculty_class_list',
            'faculty.calendar' => 'faculty_directory',
            'faculty.grading-sheet' => 'faculty_grading_sheets',
            'faculty.evaluation' => 'faculty_evaluation',
            'faculty.profile' => 'faculty_directory',
            'faculty.profile.edit' => 'faculty_directory',
            'faculty.messaging' => 'faculty_messaging',
        ];

        if (isset($exact[$routeName])) {
            return $exact[$routeName];
        }

        $prefixes = [
            'registrar.messaging.' => 'communication_messages_module',
            'registrar.notifications.' => 'registrar_dashboard',
            'registrar.help.' => 'communication_ticketing_system',
            'registrar.communication.tickets.' => 'communication_ticketing_system',
            'registrar.communication.email-templates.' => 'communication_email_templates',
            'registrar.process.application.' => 'admissions_application_list',
            'registrar.process.approval-status.' => 'admissions_approval_status',
            'registrar.process.batch-upload.' => 'admissions_batch_upload_photos',
            'registrar.process.document-list.' => 'admissions_document_submission',
            'registrar.process.religion.' => 'admissions_religion',
            'registrar.process.reports.' => 'reports_academic_reports',
            'registrar.registrar-menu.academic-master.program-file.' => 'academics_program_file',
            'registrar.registrar-menu.student-mgmt.student-records.' => 'student_records_student_list',
            'registrar.registrar-menu.student-mgmt.student-enrollment.' => 'student_records_enrollment_list',
            'registrar.registrar-menu.student-mgmt.academic-record.' => 'student_records_student_list',
            'registrar.registrar-menu.academic-master.subject-file.' => 'academics_subject_file',
            'registrar.registrar-menu.academic-master.curriculum-file.' => 'academics_curriculum_file',
            'registrar.registrar-menu.academic-master.pre-requisites.' => 'academics_prerequisites',
            'registrar.registrar-menu.academic-master.letter-grade' => 'academics_grading_system',
            'registrar.registrar-menu.scheduling.room-file.' => 'academics_room_file',
            'registrar.registrar-menu.scheduling.class-schedule-preparation.' => 'academics_class_schedule_preparation',
            'registrar.registrar-menu.scheduling.section-offering.' => 'academics_section_offering',
            'registrar.registrar-menu.scheduling.slot-monitoring.' => 'academics_slot_monitoring',
            'registrar.registrar-menu.scheduling.section-merging.' => 'academics_section_merging',
            'registrar.registrar-menu.faculty-mgmt.faculty-create' => 'faculty_directory',
            'registrar.registrar-menu.faculty-mgmt.grading-sheet.' => 'faculty_grading_sheets',
            'registrar.registrar-menu.alumni.tracker.' => 'reports_alumni_tracker',
            'registrar.registrar-menu.forms.placeholder' => 'documents_forms_certificates',
            'registrar.registrar-menu.forms.tor' => 'documents_forms_copy_of_grades',
            'registrar.registrar-menu.forms.cog.' => 'documents_forms_copy_of_grades',
            'registrar.registrar-menu.forms.cor.' => 'documents_forms_registration_certificate',
            'registrar.registrar-menu.forms.certificates.' => 'documents_forms_certificates',
            'registrar.registrar-menu.forms.application-leave-of-absence-enrolled.' => 'documents_forms_leave_of_absence',
            'registrar.registrar-menu.forms.official-grade-report.' => 'documents_forms_official_grade_report',
            'registrar.registrar-menu.forms.honorable-dismissal.' => 'documents_forms_honorable_dismissal',
            'registrar.registrar-menu.forms.request-form-f-137a.' => 'documents_forms_f137a',
            'registrar.registrar-menu.forms.graduation-clearance.' => 'documents_forms_graduation_clearance',
            'registrar.registrar-menu.forms.permission-cross-enroll.' => 'documents_forms_cross_enroll',
            'registrar.registrar-menu.forms.waiver-cancellation.' => 'documents_forms_waiver_cancellation',
            'registrar.services.classroom-faculty.class-list.' => 'academics_class_list',
            'registrar.services.classroom-faculty.faculty-loads.' => 'academics_faculty_loads',
            'registrar.services.grading-academic.grading-system.' => 'academics_grading_system',
            'registrar.services.grading-academic.grading-periods.' => 'academics_grading_periods',
            'registrar.services.grading-academic.grading-components.' => 'academics_grading_components',
            'registrar.services.grading-academic.transmutation.' => 'academics_transmutation_table',
            'registrar.services.grading-academic.deficiency.' => 'academics_deficiency',
            'registrar.services.reports-admin.academic-reports.' => 'reports_academic_reports',
            'registrar.services.reports-admin.certifications.' => 'reports_certifications',
            'registrar.services.reports-admin.tagging-of-graduates.' => 'reports_graduation_tagging',
            'registrar.services.student-account.student-discipline.' => 'student_records_discipline',
            'registrar.admin-tools.system-config.configuration.' => 'system_configuration',
            'registrar.admin-tools.system-config.academic-calendar.' => 'system_academic_calendar',
            'registrar.admin-tools.system-config.announcement.' => 'system_announcements',
            'registrar.admin-tools.access-management.user-accounts.' => 'system_user_accounts',
            'registrar.admin-tools.access-management.report-access.' => 'system_report_access',
            'registrar.admin-tools.master-files.faculty-file.' => 'system_faculty_file',
            'registrar.admin-tools.master-files.student-profile.' => 'system_student_profile',
            'registrar.admin-tools.master-files.student-grade-file.' => 'system_student_grade_file',
            'registrar.admin-tools.student-maintenance.student-update.' => 'system_student_update',
            'registrar.admin-tools.student-maintenance.bed-student-status' => 'system_student_update',
            'registrar.admin-tools.student-maintenance.bed-days' => 'system_student_update',
            'student.forms.' => 'student_portal_forms',
            'student.profile.' => 'student_portal_profile',
            'student.notifications.' => 'student_portal_dashboard',
            'parent.help.' => 'parent_portal_help_center',
            'parent.contact-us.' => 'parent_portal_contact_us',
            'parent.notifications.' => 'parent_portal_dashboard',
            'applicant.help.' => 'applicant_portal_help_center',
            'applicant.application-form.' => 'applicant_portal_application',
            'applicant.notifications.' => 'applicant_portal_dashboard',
            'faculty.profile.' => 'faculty_directory',
            'faculty.grading-sheet.' => 'faculty_grading_sheets',
            'faculty.faculty.grading-sheet.' => 'faculty_grading_sheets',
            'faculty.notifications.' => 'faculty_directory',
        ];

        foreach ($prefixes as $prefix => $moduleCode) {
            if (strpos($routeName, $prefix) === 0) {
                return $moduleCode;
            }
        }

        return null;
    }

    public static function permissionForMethod(string $method): string
    {
        return in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'], true) ? 'view' : 'edit';
    }

    private static function defaultAllows(string $role, string $moduleCode, string $permissionCode): bool
    {
        if (in_array($role, ['admin', 'registrar'], true)) {
            return true;
        }

        $rolePrefixes = [
            'faculty' => ['faculty_', 'academics_grading_', 'academics_class_', 'academics_attendance'],
            'student' => ['student_portal_'],
            'applicant' => ['applicant_portal_'],
            'parent' => ['parent_portal_'],
        ];

        foreach ($rolePrefixes[$role] ?? [] as $prefix) {
            if (strpos($moduleCode, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    private static function hasTables(): bool
    {
        return Schema::hasTable('access_control_modules')
            && Schema::hasTable('access_control_permission_types')
            && Schema::hasTable('user_access_controls');
    }

    private static function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));
        return $role === '' ? 'user' : $role;
    }

    private static function truthy($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'yes', 'on'], true);
    }
}
