<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/access');
});

Route::get('/access', function () {
    return view('student.access-module');
})->name('access-module');

Route::get('/admin', function () {
    return redirect('/admin/access');
});

Auth::routes();
Route::get('/password/setup', 'Auth\FirstLoginPasswordController@show')->name('password.first_reset');
Route::post('/password/setup', 'Auth\FirstLoginPasswordController@update')->name('password.first_reset.update');

Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| Module Login Routes
|--------------------------------------------------------------------------
| Each module (Registrar, Accounting, etc.) gets its own login page.
| Student & Applicant also get their own login pages.
*/
Route::get('/login/{module}', 'Admin\AdminController@moduleLogin')->name('module.login')
    ->where('module', 'registrar|accounting|cashier|faculty|student|applicant');

/*
|--------------------------------------------------------------------------
| Demo Login (No Backend Auth)
|--------------------------------------------------------------------------
| Clicking "Sign In" on any module login page just redirects straight
| to that module's first page. No real authentication is performed.
| Replace this with real auth when the backend team is ready.
*/
Route::post('/demo-login', 'Admin\AdminController@demoLogin')->name('demo.login');
Route::post('/login/student', 'Admin\AdminController@studentLogin')->name('student.login.submit');
Route::post('/login/applicant', 'Admin\AdminController@applicantLogin')->name('applicant.login.submit');
Route::post('/login/module-auth', 'Admin\AdminController@moduleAuthLogin')->name('module.login.submit');

/*
|--------------------------------------------------------------------------
| Public Applicant Onboarding Routes
|--------------------------------------------------------------------------
*/
Route::prefix('apply')->name('applicant.apply.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('applicant.apply.welcome');
    });
    Route::get('/apply-welcome', 'Applicant\ApplicantOnboardingController@welcome')->name('welcome');
    Route::post('/start', 'Applicant\ApplicantOnboardingController@start')->name('start');
    Route::get('/basic-details', 'Applicant\ApplicantOnboardingController@basicDetails')->name('basic-details');
    Route::post('/basic-details', 'Applicant\ApplicantOnboardingController@storeBasicDetails')->name('basic-details.store');
    Route::get('/form-preview', 'Applicant\ApplicantOnboardingController@formPreview')->name('form-preview');
    Route::post('/form-preview', 'Applicant\ApplicantOnboardingController@savePreviewForm')->name('form-preview.save');
    Route::post('/form-preview/step-1', 'Applicant\ApplicantOnboardingController@savePreviewStep1')->name('form-preview.step-1.save');
    Route::post('/form-preview/step-2', 'Applicant\ApplicantOnboardingController@savePreviewStep2')->name('form-preview.step-2.save');
    Route::post('/form-preview/step-3', 'Applicant\ApplicantOnboardingController@savePreviewStep3')->name('form-preview.step-3.save');
    Route::post('/form-preview/step-4', 'Applicant\ApplicantOnboardingController@savePreviewStep4')->name('form-preview.step-4.save');
    Route::post('/form-preview/continue', 'Applicant\ApplicantOnboardingController@continuePreviewForm')->name('form-preview.continue');
    Route::post('/form-preview/reset-progress', 'Applicant\ApplicantOnboardingController@resetPreviewForm')->name('form-preview.reset-progress');
    Route::get('/preview/schedule-of-exam', 'Applicant\ApplicantOnboardingController@previewScheduleOfExam')->name('preview.schedule-of-exam');
    Route::get('/preview/calendar', 'Applicant\ApplicantOnboardingController@previewCalendar')->name('preview.calendar');
    Route::get('/preview/exam-result', 'Applicant\ApplicantOnboardingController@previewExamResult')->name('preview.exam-result');
    Route::get('/preview/correspondence', 'Applicant\ApplicantOnboardingController@previewCorrespondence')->name('preview.correspondence');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/access', 'Admin\AdminController@accessModule')->name('access-module');
});

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->middleware(['auth', 'student.user', 'force_password_reset'])->group(function () {
    Route::get('/', function () {
        return view('student.access-module');
    })->name('access-module');
    Route::get('/section-offering', 'Student\StudentController@sectionOffering')->name('section-offering');
    Route::get('/grades', 'Student\StudentController@grades')->name('grades');
    Route::get('/schedule', 'Student\StudentController@schedule')->name('schedule');
    Route::get('/events', 'Student\StudentController@events')->name('events');
    Route::get('/forms/{category}', 'Student\StudentController@forms')->name('forms.show');
    Route::get('/profile', 'Student\StudentController@profile')->name('profile');
    Route::get('/profile/edit', 'Student\StudentController@editProfile')->name('profile.edit');
    Route::post('/profile', 'Student\StudentController@updateProfile')->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Registrar Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('registrar')->name('registrar.')->middleware(['auth', 'force_password_reset'])->group(function () {
    Route::get('/dashboard', 'Registrar\RegistrarController@dashboard')->name('dashboard');
    Route::get('/messaging', 'Registrar\RegistrarController@messaging')->name('messaging');

    // Process sub-pages
    Route::prefix('process')->name('process.')->group(function () {
        Route::get('/application', 'Registrar\RegistrarController@applicationProcess')->name('application');
        Route::get('/application/{applicant}/form', 'Registrar\RegistrarController@applicantFormEditor')->name('application.form.edit');
        Route::post('/application/{applicant}/form', 'Registrar\RegistrarController@saveApplicantFormFromRegistrar')->name('application.form.save');
        Route::post('/application/{applicant}/form/step-1', 'Registrar\RegistrarController@saveApplicantFormStep1FromRegistrar')->name('application.form.step-1.save');
        Route::post('/application/{applicant}/form/step-2', 'Registrar\RegistrarController@saveApplicantFormStep2FromRegistrar')->name('application.form.step-2.save');
        Route::post('/application/{applicant}/form/step-3', 'Registrar\RegistrarController@saveApplicantFormStep3FromRegistrar')->name('application.form.step-3.save');
        Route::post('/application/{applicant}/form/step-4', 'Registrar\RegistrarController@saveApplicantFormStep4FromRegistrar')->name('application.form.step-4.save');
        Route::put('/application/{applicant}/exam-schedule', 'Registrar\RegistrarController@updateApplicantExamSchedule')->name('application.exam-schedule.update');
        Route::put('/application/{applicant}/exam-result', 'Registrar\RegistrarController@updateApplicantExamResult')->name('application.exam-result.update');
        Route::put('/application/{applicant}/approval-status', 'Registrar\RegistrarController@updateApplicantApprovalStatus')->name('application.approval-status.update');
        Route::get('/requirements', 'Registrar\RegistrarController@requirements')->name('requirements');
        Route::get('/citizenship', 'Registrar\RegistrarController@citizenship')->name('citizenship');
        Route::get('/religion', 'Registrar\RegistrarController@religion')->name('religion');
        Route::get('/approval-status/data', 'Registrar\RegistrarController@approvalStatusData')->name('approval-status.data');
        Route::post('/approval-status', 'Registrar\RegistrarController@storeApprovalStatus')->name('approval-status.store');
        Route::put('/approval-status/{applicationStatus}', 'Registrar\RegistrarController@updateApprovalStatus')->name('approval-status.update');
        Route::delete('/approval-status/{applicationStatus}', 'Registrar\RegistrarController@destroyApprovalStatus')->name('approval-status.delete');
        Route::get('/approval-status', 'Registrar\RegistrarController@approvalStatus')->name('approval-status');
        Route::get('/exam-category', 'Registrar\RegistrarController@examCategory')->name('exam-category');
        Route::get('/exam-list', 'Registrar\RegistrarController@examList')->name('exam-list');
        Route::get('/batch-upload', 'Registrar\RegistrarController@batchUpload')->name('batch-upload');
        Route::post('/batch-upload', 'Registrar\RegistrarController@storeBatchUpload')->name('batch-upload.store');
        Route::get('/batch-upload/image/{studentProfileImage}', 'Registrar\RegistrarController@batchUploadImage')->name('batch-upload.image');
        Route::get('/document-list', 'Registrar\RegistrarController@documentList')->name('document-list');
        Route::post('/document-list', 'Registrar\RegistrarController@storeDocumentRequirement')->name('document-list.store');
        Route::put('/document-list/{documentRequirement}', 'Registrar\RegistrarController@updateDocumentRequirement')->name('document-list.update');
        Route::delete('/document-list/{documentRequirement}', 'Registrar\RegistrarController@destroyDocumentRequirement')->name('document-list.delete');
        Route::get('/reports', 'Registrar\RegistrarController@reports')->name('reports');
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/unifast', 'Registrar\RegistrarController@reportsUnifast')->name('unifast');
            Route::get('/oss-nstp-form', 'Registrar\RegistrarController@reportsOssNstpForm')->name('oss-nstp-form');
        });
    });

    // Registrar menu sub-pages
    Route::prefix('registrar-menu')->name('registrar-menu.')->group(function () {
        // Academic Master
        Route::prefix('academic-master')->name('academic-master.')->group(function () {
            Route::get('/program-file', 'Registrar\RegistrarController@programFile')->name('program-file');
            Route::post('/program-file/department', 'Registrar\RegistrarController@saveDepartmentSetup')->name('program-file.department.store');
            Route::post('/program-file/setup', 'Registrar\RegistrarController@saveProgramSetup')->name('program-file.setup');
            Route::put('/program-file/setup/{course}', 'Registrar\RegistrarController@updateProgramSetup')->name('program-file.setup.update');
            Route::delete('/program-file/setup/{course}', 'Registrar\RegistrarController@destroyProgramSetup')->name('program-file.setup.delete');
            Route::get('/subject-file', 'Registrar\RegistrarController@subjectFile')->name('subject-file');
            Route::get('/curriculum-file', 'Registrar\RegistrarController@curriculumFile')->name('curriculum-file');
            Route::get('/pre-requisites', 'Registrar\RegistrarController@preRequisites')->name('pre-requisites');
            Route::get('/letter-grade', 'Registrar\RegistrarController@letterGrade')->name('letter-grade');
        });

        // Scheduling
        Route::prefix('scheduling')->name('scheduling.')->group(function () {
            Route::get('/room-file', 'Registrar\RegistrarController@roomFile')->name('room-file');
            Route::get('/section-offering', 'Registrar\RegistrarController@sectionOffering')->name('section-offering');
            Route::get('/slot-monitoring', 'Registrar\RegistrarController@slotMonitoring')->name('slot-monitoring');
            Route::get('/section-merging', 'Registrar\RegistrarController@sectionMerging')->name('section-merging');
        });

        // Student Management
        Route::prefix('student-management')->name('student-mgmt.')->group(function () {
            Route::get('/student-enrollment', 'Registrar\RegistrarController@studentEnrollment')->name('student-enrollment');
            Route::post('/student-enrollment', 'Registrar\RegistrarController@storeStudent')->name('student-enrollment.store');
            Route::get('/clinic-record', 'Registrar\RegistrarController@clinicRecord')->name('clinic-record');
        });

        // Faculty Management
        Route::prefix('faculty-management')->name('faculty-mgmt.')->group(function () {
            Route::get('/faculty-create', 'Registrar\RegistrarController@facultyCreate')->name('faculty-create');
            Route::post('/faculty-create', 'Registrar\RegistrarController@storeFaculty')->name('faculty-create.store');
            Route::get('/grading-sheet', 'Registrar\RegistrarController@gradingSheet')->name('grading-sheet');
            Route::get('/evaluation', 'Registrar\RegistrarController@evaluation')->name('evaluation');
        });

        // Alumni Tracker
        Route::prefix('alumni')->name('alumni.')->group(function () {
            Route::get('/tracker', 'Registrar\RegistrarController@alumniTracker')->name('tracker');
            Route::put('/tracker/config', 'Registrar\RegistrarController@alumniTrackerSaveConfig')->name('tracker.config');
        });

        // Forms
        Route::prefix('forms')->name('forms.')->group(function () {
            Route::get('/placeholder', 'Registrar\RegistrarController@formsPlaceholder')->name('placeholder');
            Route::get('/tor', 'Registrar\RegistrarController@formsTor')->name('tor');
            Route::get('/application-leave-of-absence-enrolled', 'Registrar\RegistrarController@formsApplicationLeaveAbsenceEnrolled')->name('application-leave-of-absence-enrolled');
            Route::get('/diploma', 'Registrar\RegistrarController@formsDiploma')->name('diploma');
            Route::get('/graduation-clearance', 'Registrar\RegistrarController@formsGraduationClearance')->name('graduation-clearance');
            Route::get('/honorable-dismissal', 'Registrar\RegistrarController@formsHonorableDismissal')->name('honorable-dismissal');
            Route::get('/official-grade-report', 'Registrar\RegistrarController@formsOfficialGradeReport')->name('official-grade-report');
            Route::get('/official-grade-report/{student}/data', 'Registrar\RegistrarController@formsOfficialGradeReportData')->name('official-grade-report.data');
            Route::get('/permission-cross-enroll', 'Registrar\RegistrarController@formsPermissionCrossEnroll')->name('permission-cross-enroll');
            Route::get('/citizens-charter', 'Registrar\RegistrarController@formsCitizensCharter')->name('citizens-charter');
            Route::get('/request-form-f-137a', 'Registrar\RegistrarController@formsRequestFormF137a')->name('request-form-f-137a');
            Route::post('/permission-cross-enroll', 'Registrar\RegistrarController@formsPermissionCrossEnrollStore')->name('permission-cross-enroll.store');
            Route::put('/permission-cross-enroll/{crossEnrollmentRequest}', 'Registrar\RegistrarController@formsPermissionCrossEnrollUpdate')->name('permission-cross-enroll.update');
            Route::delete('/permission-cross-enroll/{crossEnrollmentRequest}', 'Registrar\RegistrarController@formsPermissionCrossEnrollDestroy')->name('permission-cross-enroll.destroy');
            Route::get('/waiver-cancellation', 'Registrar\RegistrarController@formsWaiverCancellation')->name('waiver-cancellation');
            Route::post('/waiver-cancellation', 'Registrar\RegistrarController@formsWaiverCancellationStore')->name('waiver-cancellation.store');
            Route::put('/waiver-cancellation/{cancellationWaiver}', 'Registrar\RegistrarController@formsWaiverCancellationUpdate')->name('waiver-cancellation.update');
            Route::delete('/waiver-cancellation/{cancellationWaiver}', 'Registrar\RegistrarController@formsWaiverCancellationDestroy')->name('waiver-cancellation.destroy');

            // Certificates
            Route::prefix('certificates')->name('certificates.')->group(function () {
                Route::get('/certificate-of-gwa/{student}', 'Registrar\\RegistrarController@formsCertificateGwa')->name('certificate-gwa.show');
                Route::get('/certificate-of-gwa', 'Registrar\\RegistrarController@formsCertificateGwa')->name('certificate-gwa');
                Route::get('/8c2-certificate-of-graduation', 'Registrar\\RegistrarController@formsCertificateGraduation8c2')->name('certificate-graduation-8c2');
                Route::get('/8d2-certificate-of-honor', 'Registrar\\RegistrarController@formsCertificateHonor8d2')->name('certificate-honor-8d2');
            });

            // Copy Of Grades (COG)
            Route::prefix('cog')->name('cog.')->group(function () {
                Route::get('/copy-of-grades', 'Registrar\\RegistrarController@formsCopyOfGradesCog')->name('copy-of-grades');
            });

            // Certificate of Registration (COR)
            Route::prefix('cor')->name('cor.')->group(function () {
                Route::get('/certificate-of-registration', 'Registrar\\RegistrarController@formsCertificateOfRegistration')->name('certificate-of-registration');
            });
        });
    });

    // Services sub-pages
    Route::prefix('services')->name('services.')->group(function () {
        Route::prefix('classroom-faculty')->name('classroom-faculty.')->group(function () {
            Route::get('/class-list', 'Registrar\Services\ClassListController@index')->name('class-list');
            Route::get('/attendance', 'Registrar\Services\AttendanceController@index')->name('attendance');

            Route::prefix('faculty-loads')->name('faculty-loads.')->group(function () {
                Route::get('/', 'Registrar\Services\FacultyLoadsController@index')->name('index');
                Route::get('/{faculty}', 'Registrar\Services\FacultyLoadsController@show')->name('show');
                Route::post('/{faculty}/assign', 'Registrar\Services\FacultyLoadsController@assign')->name('assign');
            });
        });

        Route::prefix('grading-academic')->name('grading-academic.')->group(function () {
            Route::get('/grading-system', 'Registrar\Services\GradingAcademicController@gradingSystem')->name('grading-system');
            Route::post('/grading-system', 'Registrar\Services\GradingAcademicController@gradingSystemStore')->name('grading-system.store');
            Route::put('/grading-system/{gradeRule}', 'Registrar\Services\GradingAcademicController@gradingSystemUpdate')->name('grading-system.update');
            Route::delete('/grading-system/{gradeRule}', 'Registrar\Services\GradingAcademicController@gradingSystemDestroy')->name('grading-system.destroy');
            Route::get('/grading-periods', 'Registrar\Services\GradingAcademicController@gradingPeriods')->name('grading-periods');
            Route::post('/grading-periods', 'Registrar\Services\GradingAcademicController@gradingPeriodsStore')->name('grading-periods.store');
            Route::put('/grading-periods/{gradingPeriod}', 'Registrar\Services\GradingAcademicController@gradingPeriodsUpdate')->name('grading-periods.update');
            Route::delete('/grading-periods/{gradingPeriod}', 'Registrar\Services\GradingAcademicController@gradingPeriodsDestroy')->name('grading-periods.destroy');
            Route::get('/grading-components', 'Registrar\Services\GradingAcademicController@gradingComponents')->name('grading-components');
            Route::post('/grading-components', 'Registrar\Services\GradingAcademicController@gradingComponentsStore')->name('grading-components.store');
            Route::put('/grading-components/{gradingComponent}', 'Registrar\Services\GradingAcademicController@gradingComponentsUpdate')->name('grading-components.update');
            Route::delete('/grading-components/{gradingComponent}', 'Registrar\Services\GradingAcademicController@gradingComponentsDestroy')->name('grading-components.destroy');
            Route::get('/transmutation', 'Registrar\Services\GradingAcademicController@transmutation')->name('transmutation');
            Route::post('/transmutation', 'Registrar\Services\GradingAcademicController@transmutationStore')->name('transmutation.store');
            Route::put('/transmutation/{transmutationRule}', 'Registrar\Services\GradingAcademicController@transmutationUpdate')->name('transmutation.update');
            Route::delete('/transmutation/{transmutationRule}', 'Registrar\Services\GradingAcademicController@transmutationDestroy')->name('transmutation.destroy');
            Route::get('/deficiency', 'Registrar\Services\GradingAcademicController@deficiency')->name('deficiency');
            Route::post('/deficiency/students', 'Registrar\Services\GradingAcademicController@deficiencyStudentStore')->name('deficiency.students.store');
            Route::get('/deficiency/{student}/records', 'Registrar\Services\GradingAcademicController@deficiencyRecords')->name('deficiency.records');
            Route::post('/deficiency/{student}/records', 'Registrar\Services\GradingAcademicController@deficiencyStore')->name('deficiency.store');
            Route::put('/deficiency/records/{studentDeficiency}', 'Registrar\Services\GradingAcademicController@deficiencyUpdate')->name('deficiency.update');
            Route::delete('/deficiency/records/{studentDeficiency}', 'Registrar\Services\GradingAcademicController@deficiencyDestroy')->name('deficiency.destroy');
        });

        Route::prefix('reports-admin')->name('reports-admin.')->group(function () {
            Route::get('/academic-reports', 'Registrar\Services\ReportsAdminController@academicReports')->name('academic-reports');
            Route::post('/academic-reports/issue', 'Registrar\Services\ReportsAdminController@issueAcademicReport')->name('academic-reports.issue');
            Route::get('/guidance-reports', 'Registrar\Services\ReportsAdminController@guidanceReports')->name('guidance-reports');
            Route::get('/certifications', 'Registrar\Services\ReportsAdminController@certifications')->name('certifications');
            Route::post('/certifications/issue', 'Registrar\Services\ReportsAdminController@issueCertification')->name('certifications.issue');
            Route::get('/tagging-of-graduates', 'Registrar\Services\ReportsAdminController@taggingOfGraduates')->name('tagging-of-graduates');
            Route::put('/tagging-of-graduates/{student}', 'Registrar\Services\ReportsAdminController@taggingOfGraduatesUpdate')->name('tagging-of-graduates.update');
        });

        Route::prefix('student-account')->name('student-account.')->group(function () {
            Route::get('/student-discipline', 'Registrar\Services\StudentAccountController@studentDiscipline')->name('student-discipline');
            Route::get('/family', 'Registrar\Services\StudentAccountController@family')->name('family');
            Route::get('/change-password', 'Registrar\Services\StudentAccountController@changePassword')->name('change-password');
        });
    });

    // Admin Tools
    Route::prefix('admin-tools')->name('admin-tools.')->group(function () {
        Route::prefix('system-config')->name('system-config.')->group(function () {
            Route::get('/configuration', 'Registrar\Services\AdminToolsController@configuration')->name('configuration');
            Route::post('/configuration/school-sem', 'Registrar\Services\AdminToolsController@configurationSchoolSemStore')->name('configuration.school-sem.store');
            Route::put('/configuration/school-sem/{systemSchoolSemester}', 'Registrar\Services\AdminToolsController@configurationSchoolSemUpdate')->name('configuration.school-sem.update');
            Route::delete('/configuration/school-sem/{systemSchoolSemester}', 'Registrar\Services\AdminToolsController@configurationSchoolSemDestroy')->name('configuration.school-sem.destroy');
            Route::post('/configuration/grade-posting', 'Registrar\Services\AdminToolsController@configurationGradePostingStore')->name('configuration.grade-posting.store');
            Route::put('/configuration/grade-posting/{systemGradePosting}', 'Registrar\Services\AdminToolsController@configurationGradePostingUpdate')->name('configuration.grade-posting.update');
            Route::delete('/configuration/grade-posting/{systemGradePosting}', 'Registrar\Services\AdminToolsController@configurationGradePostingDestroy')->name('configuration.grade-posting.destroy');
            Route::get('/admission-config', 'Registrar\Services\AdminToolsController@admissionConfig')->name('admission-config');
            Route::get('/academic-calendar', 'Registrar\Services\AdminToolsController@academicCalendar')->name('academic-calendar');
            Route::post('/academic-calendar', 'Registrar\Services\AdminToolsController@academicCalendarStore')->name('academic-calendar.store');
            Route::put('/academic-calendar/{academicCalendarEvent}', 'Registrar\Services\AdminToolsController@academicCalendarUpdate')->name('academic-calendar.update');
            Route::delete('/academic-calendar/{academicCalendarEvent}', 'Registrar\Services\AdminToolsController@academicCalendarDestroy')->name('academic-calendar.destroy');
            Route::get('/announcement', 'Registrar\Services\AdminToolsController@announcement')->name('announcement');
            Route::post('/announcement', 'Registrar\Services\AdminToolsController@announcementStore')->name('announcement.store');
            Route::put('/announcement/{systemAnnouncement}', 'Registrar\Services\AdminToolsController@announcementUpdate')->name('announcement.update');
            Route::delete('/announcement/{systemAnnouncement}', 'Registrar\Services\AdminToolsController@announcementDestroy')->name('announcement.destroy');
        });

        Route::prefix('access-management')->name('access-management.')->group(function () {
            Route::get('/user-accounts', 'Registrar\Services\AdminToolsController@userAccounts')->name('user-accounts');
            Route::put('/user-accounts/{user}', 'Registrar\Services\AdminToolsController@userAccountsUpdate')->name('user-accounts.update');
            Route::delete('/user-accounts/{user}', 'Registrar\Services\AdminToolsController@userAccountsDestroy')->name('user-accounts.destroy');
            Route::get('/report-access', 'Registrar\Services\AdminToolsController@reportAccess')->name('report-access');
            Route::put('/report-access/{user}', 'Registrar\Services\AdminToolsController@reportAccessUpdate')->name('report-access.update');
        });

        Route::prefix('master-files')->name('master-files.')->group(function () {
            Route::get('/faculty-file', 'Registrar\Services\AdminToolsController@facultyFile')->name('faculty-file');
            Route::post('/faculty-file', 'Registrar\Services\AdminToolsController@facultyFileStore')->name('faculty-file.store');
            Route::put('/faculty-file/{masterFacultyFile}', 'Registrar\Services\AdminToolsController@facultyFileUpdate')->name('faculty-file.update');
            Route::delete('/faculty-file/{masterFacultyFile}', 'Registrar\Services\AdminToolsController@facultyFileDestroy')->name('faculty-file.destroy');
            Route::get('/student-profile', 'Registrar\Services\AdminToolsController@studentProfile')->name('student-profile');
            Route::post('/student-profile', 'Registrar\Services\AdminToolsController@studentProfileStore')->name('student-profile.store');
            Route::put('/student-profile/{masterStudentProfile}', 'Registrar\Services\AdminToolsController@studentProfileUpdate')->name('student-profile.update');
            Route::delete('/student-profile/{masterStudentProfile}', 'Registrar\Services\AdminToolsController@studentProfileDestroy')->name('student-profile.destroy');
            Route::get('/student-grade-file', 'Registrar\Services\AdminToolsController@studentGradeFile')->name('student-grade-file');
            Route::post('/student-grade-file', 'Registrar\Services\AdminToolsController@studentGradeFileStore')->name('student-grade-file.store');
            Route::put('/student-grade-file/{masterStudentGradeFile}', 'Registrar\Services\AdminToolsController@studentGradeFileUpdate')->name('student-grade-file.update');
            Route::delete('/student-grade-file/{masterStudentGradeFile}', 'Registrar\Services\AdminToolsController@studentGradeFileDestroy')->name('student-grade-file.destroy');
        });

        Route::prefix('student-maintenance')->name('student-maintenance.')->group(function () {
            Route::get('/bed-student-status', 'Registrar\Services\AdminToolsController@bedStudentStatus')->name('bed-student-status');
            Route::put('/bed-student-status/{bedStudentStatus}', 'Registrar\Services\AdminToolsController@bedStudentStatusUpdate')->name('bed-student-status.update');
            Route::delete('/bed-student-status/{bedStudentStatus}', 'Registrar\Services\AdminToolsController@bedStudentStatusDestroy')->name('bed-student-status.destroy');
            Route::get('/bed-days', 'Registrar\Services\AdminToolsController@bedDays')->name('bed-days');
            Route::post('/bed-days', 'Registrar\Services\AdminToolsController@bedDaysStore')->name('bed-days.store');
            Route::put('/bed-days/{bedDay}', 'Registrar\Services\AdminToolsController@bedDaysUpdate')->name('bed-days.update');
            Route::delete('/bed-days/{bedDay}', 'Registrar\Services\AdminToolsController@bedDaysDestroy')->name('bed-days.destroy');
            Route::get('/student-update', 'Registrar\Services\AdminToolsController@studentUpdate')->name('student-update');
            Route::post('/student-update/run', 'Registrar\Services\AdminToolsController@studentUpdateRun')->name('student-update.run');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Applicant Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->name('applicant.')->middleware(['auth', 'applicant.user', 'force_password_reset'])->group(function () {
    Route::get('/application-form', 'Applicant\ApplicantController@applicationForm')->name('application-form');
    Route::post('/application-form/reset-progress', 'Applicant\ApplicantController@resetApplicationFormProgress')->name('application-form.reset-progress');
    Route::post('/application-form/step-1', 'Applicant\ApplicantController@saveApplicationFormStep1')->name('application-form.step-1.save');
    Route::post('/application-form/step-2', 'Applicant\ApplicantController@saveApplicationFormStep2')->name('application-form.step-2.save');
    Route::post('/application-form/step-3', 'Applicant\ApplicantController@saveApplicationFormStep3')->name('application-form.step-3.save');
    Route::post('/application-form/step-4', 'Applicant\ApplicantController@saveApplicationFormStep4')->name('application-form.step-4.save');
    Route::post('/application-form/continue', 'Applicant\ApplicantController@continueApplicationForm')->name('application-form.continue');
    Route::post('/application-form', 'Applicant\ApplicantController@saveApplicationForm')->name('application-form.save');
    Route::get('/schedule-of-exam', 'Applicant\ApplicantController@scheduleOfExam')->name('schedule-of-exam');
    Route::get('/calendar', 'Applicant\ApplicantController@calendar')->name('calendar');
    Route::get('/correspondence', 'Applicant\ApplicantController@correspondence')->name('correspondence');
    Route::get('/exam-result', 'Applicant\ApplicantController@examResult')->name('exam-result');
});

/*
|--------------------------------------------------------------------------
| Faculty Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('faculty')->name('faculty.')->middleware(['auth', 'force_password_reset'])->group(function () {
    Route::get('/load', 'Faculty\FacultyController@facultyLoad')->name('load');
    Route::get('/load/download', 'Faculty\FacultyController@downloadLoad')->name('load.download');
    Route::get('/class-list', 'Faculty\FacultyController@classList')->name('class-list');
    Route::get('/calendar', 'Faculty\FacultyController@calendar')->name('calendar');
    Route::get('/grading-sheet', 'Faculty\FacultyController@gradingSheet')->name('grading-sheet');
    Route::post('/grading-sheet/update', 'Faculty\FacultyController@updateGrades')->name('grading-sheet.update');
    Route::get('/evaluation', 'Faculty\FacultyController@evaluation')->name('evaluation');
    Route::get('/profile', 'Faculty\FacultyController@profile')->name('profile');
    Route::get('/profile/edit', 'Faculty\FacultyController@editProfile')->name('profile.edit');
    Route::post('/profile', 'Faculty\FacultyController@updateProfile')->name('profile.update');
    Route::get('/messaging', 'Faculty\FacultyController@messaging')->name('messaging');
});
