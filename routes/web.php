<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
    ->where('module', 'registrar|accounting|cashier|faculty|student|applicant|parent');

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
Route::post('/login/parent', 'Admin\AdminController@parentLogin')->name('parent.login.submit')->middleware('throttle:20,1');
Route::post('/login/module-auth', 'Admin\AdminController@moduleAuthLogin')->name('module.login.submit');
Route::get('/parent/create-account', 'Portal\ParentController@showCreateAccount')->name('parent.create-account');
Route::get('/parent/create-account/students', 'Auth\ParentCreateAccountController@studentLookup')->name('parent.create-account.students')->middleware('throttle:60,1');
Route::post('/parent/create-account', 'Auth\ParentCreateAccountController@store')->name('parent.create-account.submit')->middleware('throttle:10,1');

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
    Route::post('/start', 'Applicant\ApplicantOnboardingController@start')->name('start')->middleware('throttle:20,1');
    Route::get('/basic-details', 'Applicant\ApplicantOnboardingController@basicDetails')->name('basic-details');
    Route::post('/basic-details', 'Applicant\ApplicantOnboardingController@storeBasicDetails')->name('basic-details.store')->middleware('throttle:20,1');
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
Route::prefix('student')->name('student.')->middleware(['auth', 'student.user', 'force_password_reset', 'user.access'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('student.schedule');
    })->name('access-module');
    Route::get('/section-offering', 'Student\StudentController@sectionOffering')->name('section-offering');
    Route::get('/grades', 'Student\StudentController@grades')->name('grades');
    Route::get('/schedule', 'Student\StudentController@schedule')->name('schedule');
    Route::get('/cor', 'Student\StudentController@cor')->name('cor');
    Route::get('/events', 'Student\StudentController@events')->name('events');
    Route::get('/forms/{category}', 'Student\StudentController@forms')->name('forms.show');
    Route::get('/profile', 'Student\StudentController@profile')->name('profile');
    Route::get('/profile/edit', 'Student\StudentController@editProfile')->name('profile.edit');
    Route::post('/profile', 'Student\StudentController@updateProfile')->name('profile.update');
    Route::get('/notifications/feed', 'Student\StudentController@notificationsFeed')->name('notifications.feed');
    Route::post('/notifications/mark-read', 'Student\StudentController@markNotificationsRead')->name('notifications.mark-read');
    Route::post('/notifications/{notificationDelivery}/dismiss', 'Student\StudentController@dismissNotification')->name('notifications.dismiss');
});

/*
|--------------------------------------------------------------------------
| Parent Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('parent')->name('parent.')->middleware(['auth', 'parent.user', 'force_password_reset', 'user.access'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('parent.grades');
    })->name('access-module');

    Route::get('/dashboard', 'ParentModule\ParentController@dashboard')->name('dashboard');
    Route::get('/help-center', 'Portal\ParentController@helpCenter')->name('help.center');
    Route::get('/help-center/tickets/create', 'Portal\ParentController@createHelpCenterTicket')->name('help.tickets.create');
    Route::post('/help-center/tickets', 'Portal\ParentController@storeHelpCenterTicket')->name('help.tickets.store')->middleware('throttle:20,1');
    Route::get('/help-center/live-chat', 'Portal\ParentController@helpCenterLiveChat')->name('help.live-chat');
    Route::get('/help-center/{topic}', 'Portal\ParentController@helpCenterTopic')->name('help.topic');
    Route::get('/profile', 'Portal\ParentController@profile')->name('profile');
    Route::get('/grades', 'Portal\ParentController@grades')->name('grades');
    Route::get('/student-profile', 'Portal\ParentController@studentProfile')->name('student-profile');
    Route::get('/calendar', 'Portal\ParentController@calendar')->name('calendar');
    Route::get('/contact-us', 'Portal\ParentController@contactUs')->name('contact-us');
    Route::post('/contact-us', 'Portal\ParentController@submitContactUs')->name('contact-us.submit')->middleware('throttle:20,1');
    Route::get('/change-password', 'Portal\ParentController@changePassword')->name('change-password');
    Route::post('/change-password', 'Portal\ParentController@updatePassword')->name('change-password.update')->middleware('throttle:10,1');
    Route::get('/messaging', 'Portal\ParentController@messaging')->name('messaging');
    Route::get('/notifications/feed', 'Portal\ParentController@notificationsFeed')->name('notifications.feed');
    Route::post('/notifications/mark-read', 'Portal\ParentController@markNotificationsRead')->name('notifications.mark-read');
    Route::post('/notifications/{notificationDelivery}/dismiss', 'Portal\ParentController@dismissNotification')->name('notifications.dismiss');
});

/*
|--------------------------------------------------------------------------
| Registrar Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('registrar')->name('registrar.')->middleware(['auth', 'force_password_reset', 'user.access'])->group(function () {
    Route::get('/dashboard', 'Registrar\RegistrarController@dashboard')->name('dashboard');
    Route::get('/profile', 'Registrar\RegistrarController@profile')->name('profile');
    Route::post('/profile/change-password', 'Registrar\RegistrarController@updatePassword')->name('profile.password.update')->middleware('throttle:10,1');
    Route::get('/messaging', 'Registrar\RegistrarController@messaging')->name('messaging');
    Route::post('/messaging', 'Registrar\RegistrarController@storeRegistrarMessage')->name('messaging.store')->middleware('throttle:30,1');
    Route::put('/messaging/{registrarMessage}', 'Registrar\RegistrarController@updateRegistrarMessage')->name('messaging.update')->middleware('throttle:30,1');
    Route::delete('/messaging/{registrarMessage}', 'Registrar\RegistrarController@deleteRegistrarMessage')->name('messaging.delete')->middleware('throttle:30,1');
    Route::get('/communication/tickets', 'Registrar\RegistrarController@communicationTickets')->name('communication.tickets');
    Route::post('/communication/tickets', 'Registrar\RegistrarController@storeCommunicationTicket')->name('communication.tickets.store')->middleware('throttle:30,1');
    Route::put('/communication/tickets/{supportTicket}', 'Registrar\RegistrarController@updateCommunicationTicket')->name('communication.tickets.update')->middleware('throttle:60,1');
    Route::get('/communication/stakeholders', 'Registrar\RegistrarController@stakeholderCommunication')->name('communication.stakeholders');
    Route::get('/communication/email-templates', 'Registrar\RegistrarController@emailNotificationTemplates')->name('communication.email-templates');
    Route::post('/communication/email-templates', 'Registrar\RegistrarController@storeEmailNotificationTemplate')->name('communication.email-templates.store')->middleware('throttle:30,1');
    Route::put('/communication/email-templates/{registrarEmailTemplate}', 'Registrar\RegistrarController@updateEmailNotificationTemplate')->name('communication.email-templates.update')->middleware('throttle:60,1');
    Route::get('/notifications/feed', 'Registrar\RegistrarController@notificationsFeed')->name('notifications.feed');
    Route::post('/notifications/mark-read', 'Registrar\RegistrarController@markNotificationsRead')->name('notifications.mark-read');
    Route::post('/notifications/{notificationDelivery}/dismiss', 'Registrar\RegistrarController@dismissNotification')->name('notifications.dismiss');
    Route::get('/help-center', 'Registrar\RegistrarController@helpCenter')->name('help.center');
    Route::get('/help-center/tickets/create', 'Registrar\RegistrarController@createHelpCenterTicket')->name('help.tickets.create');
    Route::post('/help-center/tickets', 'Registrar\RegistrarController@storeHelpCenterTicket')->name('help.tickets.store')->middleware('throttle:20,1');
    Route::get('/help-center/live-chat', 'Registrar\RegistrarController@helpCenterLiveChat')->name('help.live-chat');
    Route::get('/help-center/{topic}', 'Registrar\RegistrarController@helpCenterTopic')->name('help.topic');

    // Process sub-pages
    Route::prefix('process')->name('process.')->group(function () {
        Route::get('/application', 'Registrar\RegistrarController@applicationProcess')->name('application');
        Route::get('/application/print', 'Registrar\RegistrarController@applicationProcessPrint')->name('application.print');
        Route::get('/application/{applicant}/form', 'Registrar\RegistrarController@applicantFormEditor')->name('application.form.edit');
        Route::post('/application/{applicant}/form', 'Registrar\RegistrarController@saveApplicantFormFromRegistrar')->name('application.form.save');
        Route::post('/application/{applicant}/form/step-1', 'Registrar\RegistrarController@saveApplicantFormStep1FromRegistrar')->name('application.form.step-1.save');
        Route::post('/application/{applicant}/form/step-2', 'Registrar\RegistrarController@saveApplicantFormStep2FromRegistrar')->name('application.form.step-2.save');
        Route::post('/application/{applicant}/form/step-3', 'Registrar\RegistrarController@saveApplicantFormStep3FromRegistrar')->name('application.form.step-3.save');
        Route::post('/application/{applicant}/form/step-4', 'Registrar\RegistrarController@saveApplicantFormStep4FromRegistrar')->name('application.form.step-4.save');
        Route::put('/application/{applicant}/exam-schedule', 'Registrar\RegistrarController@updateApplicantExamSchedule')->name('application.exam-schedule.update');
        Route::put('/application/{applicant}/exam-result', 'Registrar\RegistrarController@updateApplicantExamResult')->name('application.exam-result.update');
        Route::put('/application/{applicant}/approval-status', 'Registrar\RegistrarController@updateApplicantApprovalStatus')->name('application.approval-status.update');
        Route::put('/application/{applicant}/exam-interview', 'Registrar\RegistrarController@updateApplicantExamInterview')->name('application.exam-interview.update');
        Route::post('/application/bulk-status-update', 'Registrar\RegistrarController@bulkUpdateApplicantStatus')->name('application.bulk-status-update');
        Route::post('/application/bulk-convert-to-student', 'Registrar\RegistrarController@bulkConvertToStudent')->name('application.bulk-convert-to-student');
        Route::get('/application/{applicant}/documents', 'Registrar\RegistrarController@applicantDocumentsData')->name('application.documents.data')->middleware('throttle:30,1');
        Route::get('/application/{applicant}/documents/available', 'Registrar\RegistrarController@availableApplicantDocuments')->name('application.documents.available')->middleware('throttle:30,1');
        Route::post('/application/{applicant}/documents/assign', 'Registrar\RegistrarController@assignApplicantDocuments')->name('application.documents.assign')->middleware('throttle:30,1');
        Route::post('/application/{applicant}/documents/requirement/new', 'Registrar\RegistrarController@storeApplicantDocumentRequirement')->name('application.documents.requirement.store')->middleware('throttle:30,1');
        Route::delete('/application/{applicant}/documents/{registrarRequirement}/file', 'Registrar\RegistrarController@destroyApplicantDocumentFile')->name('application.documents.file.delete')->middleware('throttle:30,1');
        Route::delete('/application/{applicant}/documents/{registrarRequirement}/assignment', 'Registrar\RegistrarController@destroyApplicantDocumentAssignment')->name('application.documents.assignment.delete')->middleware('throttle:30,1');
        Route::post('/application/{applicant}/documents/{registrarRequirement}', 'Registrar\RegistrarController@upsertApplicantDocument')->name('application.documents.upsert')->middleware('throttle:30,1');
        Route::get('/application/{applicant}/documents/file/{submissionFile}', 'Registrar\RegistrarController@applicantDocumentFile')->name('application.documents.file')->middleware('throttle:60,1');
        Route::get('/requirements', 'Registrar\RegistrarController@requirements')->name('requirements');
        Route::get('/citizenship', 'Registrar\RegistrarController@citizenship')->name('citizenship');
        // Route::get('/religion', 'Registrar\RegistrarController@religion')->name('religion');
        Route::get('/approval-status/data', 'Registrar\RegistrarController@approvalStatusData')->name('approval-status.data');
        Route::post('/approval-status', 'Registrar\RegistrarController@storeApprovalStatus')->name('approval-status.store');
        Route::put('/approval-status/{applicationStatus}', 'Registrar\RegistrarController@updateApprovalStatus')->name('approval-status.update');
        Route::delete('/approval-status/{applicationStatus}', 'Registrar\RegistrarController@destroyApprovalStatus')->name('approval-status.delete');
        Route::get('/approval-status', 'Registrar\RegistrarController@approvalStatus')->name('approval-status');
        Route::get('/exam-category', 'Registrar\RegistrarController@examCategory')->name('exam-category');
        Route::get('/exam-list', 'Registrar\RegistrarController@examList')->name('exam-list');
        Route::get('/exam-interview-scheduling', 'Registrar\RegistrarController@examInterviewScheduling')->name('exam-interview-scheduling');
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
            Route::delete('/program-file/department/{department}', 'Registrar\RegistrarController@destroyDepartment')->name('program-file.department.destroy');
            Route::post('/program-file/setup', 'Registrar\RegistrarController@saveProgramSetup')->name('program-file.setup');
            Route::put('/program-file/setup/{course}', 'Registrar\RegistrarController@updateProgramSetup')->name('program-file.setup.update');
            Route::delete('/program-file/setup/{course}', 'Registrar\RegistrarController@destroyProgramSetup')->name('program-file.setup.delete');
            Route::get('/subject-file', 'Registrar\RegistrarController@subjectFile')->name('subject-file');
            Route::get('/subject-file/data', 'Registrar\RegistrarController@subjectFileData')->name('subject-file.data');
            Route::post('/subject-file', 'Registrar\RegistrarController@storeSubjectFile')->name('subject-file.store');
            Route::put('/subject-file/{subjectId}', 'Registrar\RegistrarController@updateSubjectFile')->name('subject-file.update');
            Route::delete('/subject-file/{subjectId}', 'Registrar\RegistrarController@destroySubjectFile')->name('subject-file.delete');
            Route::get('/curriculum-file', 'Registrar\RegistrarController@curriculumFile')->name('curriculum-file');
            Route::post('/curriculum-file/setup', 'Registrar\RegistrarController@saveCurriculumSetup')->name('curriculum-file.setup')->middleware('throttle:60,1');
            Route::put('/curriculum-file/{courseCurriculum}/workflow', 'Registrar\RegistrarController@updateCurriculumWorkflow')->name('curriculum-file.workflow')->middleware('throttle:60,1');
            Route::get('/curriculum-year-tracking', 'Registrar\RegistrarController@curriculumYearTracking')->name('curriculum-year-tracking');
            Route::get('/pre-requisites', 'Registrar\RegistrarController@preRequisites')->name('pre-requisites');
            Route::get('/pre-requisites/data', 'Registrar\RegistrarController@preRequisitesData')->name('pre-requisites.data')->middleware('throttle:60,1');
            Route::get('/pre-requisites/download/pdf', 'Registrar\RegistrarController@downloadPreRequisitesPdf')->name('pre-requisites.download')->middleware('throttle:60,1');
            Route::get('/pre-requisites/subjects/{courseCurriculumSubjectId}/download/pdf', 'Registrar\RegistrarController@downloadPreRequisitesSubjectPdf')->name('pre-requisites.subject.download')->middleware('throttle:60,1');
            Route::get('/pre-requisites/subjects/{courseCurriculumSubjectId}', 'Registrar\RegistrarController@preRequisitesSubjectDetail')->name('pre-requisites.subject.show')->middleware('throttle:60,1');
            Route::put('/pre-requisites/subjects/{courseCurriculumSubjectId}', 'Registrar\RegistrarController@updatePreRequisitesSubjectDetail')->name('pre-requisites.subject.update')->middleware('throttle:60,1');
            Route::get('/letter-grade', 'Registrar\RegistrarController@letterGrade')->name('letter-grade');
        });

        // Scheduling
        Route::prefix('scheduling')->name('scheduling.')->group(function () {
            Route::get('/room-file', 'Registrar\RegistrarController@roomFile')->name('room-file');
            Route::get('/room-file/data', 'Registrar\RegistrarController@roomFileData')->name('room-file.data')->middleware('throttle:60,1');
            Route::post('/room-file/buildings', 'Registrar\RegistrarController@storeRoomBuilding')->name('room-file.building.store')->middleware('throttle:60,1');
            Route::post('/room-file/hallways', 'Registrar\RegistrarController@storeRoomHallway')->name('room-file.hallway.store')->middleware('throttle:60,1');
            Route::post('/room-file', 'Registrar\RegistrarController@storeRoomFile')->name('room-file.store')->middleware('throttle:60,1');
            Route::put('/room-file/{room}', 'Registrar\RegistrarController@updateRoomFile')->name('room-file.update')->middleware('throttle:60,1');
            Route::delete('/room-file/{room}', 'Registrar\RegistrarController@destroyRoomFile')->name('room-file.delete')->middleware('throttle:60,1');
            Route::get('/room-generation-assignment', 'Registrar\RegistrarController@roomGenerationAssignment')->name('room-generation-assignment');
            Route::post('/room-generation-assignment/generate', 'Registrar\RegistrarController@generateRoomsForAssignment')->name('room-generation-assignment.generate')->middleware('throttle:30,1');
            Route::post('/room-generation-assignment/generate-teachers', 'Registrar\RegistrarController@generateTeacherAllowedSubjectsForAssignment')->name('room-generation-assignment.generate-teachers')->middleware('throttle:30,1');
            Route::post('/room-generation-assignment/assign', 'Registrar\RegistrarController@assignRoomsPerSectionSubject')->name('room-generation-assignment.assign')->middleware('throttle:30,1');
            Route::post('/room-generation-assignment/assign-pending-availability', 'Registrar\RegistrarController@assignPendingRoomsByAvailability')->name('room-generation-assignment.assign-pending-availability')->middleware('throttle:30,1');
            Route::post('/room-generation-assignment/teacher-allowed-subjects', 'Registrar\RegistrarController@storeTeacherAllowedSubjectForAssignment')->name('room-generation-assignment.teacher-allowed-subjects.store')->middleware('throttle:30,1');
            Route::delete('/room-generation-assignment/teacher-allowed-subjects', 'Registrar\RegistrarController@destroyTeacherAllowedSubjectForAssignment')->name('room-generation-assignment.teacher-allowed-subjects.destroy')->middleware('throttle:30,1');
            Route::get('/room-generation-assignment/report', 'Registrar\RegistrarController@roomAssignmentReport')->name('room-generation-assignment.report')->middleware('throttle:60,1');
            Route::get('/teacher-generation-assignment', 'Registrar\RegistrarController@teacherGenerationAssignment')->name('teacher-generation-assignment');
            Route::post('/teacher-generation-assignment/generate', 'Registrar\RegistrarController@generateTeachersForAssignedSubjects')->name('teacher-generation-assignment.generate')->middleware('throttle:30,1');
            Route::get('/teacher-generation-assignment/report', 'Registrar\RegistrarController@teacherAssignmentReport')->name('teacher-generation-assignment.report')->middleware('throttle:60,1');
            Route::get('/room-section-offering-management', 'Registrar\RegistrarController@roomSectionOfferingManagement')->name('room-section-offering-management');
            Route::get('/coordination-deans-faculty', 'Registrar\RegistrarController@coordinationDeansFaculty')->name('coordination-deans-faculty');
            Route::get('/academic-term-lifecycle', 'Registrar\RegistrarController@academicTermLifecycle')->name('academic-term-lifecycle');
            Route::post('/academic-term-lifecycle/close', 'Registrar\RegistrarController@closeCurrentSemester')->name('academic-term-lifecycle.close')->middleware('throttle:10,1');
            Route::post('/academic-term-lifecycle/open', 'Registrar\RegistrarController@openNewAcademicTerm')->name('academic-term-lifecycle.open')->middleware('throttle:10,1');
            Route::post('/academic-term-lifecycle/{academicTerm}/publish', 'Registrar\RegistrarController@publishNewAcademicTerm')->name('academic-term-lifecycle.publish')->middleware('throttle:10,1');
            Route::get('/promotion-readiness', 'Registrar\RegistrarController@promotionReadiness')->name('promotion-readiness');
            Route::get('/promotion-readiness/data', 'Registrar\RegistrarController@promotionReadinessData')->name('promotion-readiness.data')->middleware('throttle:60,1');
            Route::get('/promotion-readiness/section', 'Registrar\RegistrarController@promotionReadinessSection')->name('promotion-readiness.section')->middleware('throttle:60,1');
            Route::get('/promotion-readiness/preview', 'Registrar\RegistrarController@promotionReadinessPreview')->name('promotion-readiness.preview')->middleware('throttle:60,1');
            Route::post('/promotion-readiness/move', 'Registrar\RegistrarController@generatePromotionReadinessMovement')->name('promotion-readiness.move')->middleware('throttle:20,1');
            Route::get('/academic-setup-automation', 'Registrar\RegistrarController@academicSetupAutomation')->name('academic-setup-automation');
            Route::get('/academic-setup-automation/movement-preview', 'Registrar\RegistrarController@academicSetupMovementPreview')->name('academic-setup-automation.movement-preview')->middleware('throttle:60,1');
            Route::post('/academic-setup-automation/generate', 'Registrar\RegistrarController@generateAcademicSetupAutomation')->name('academic-setup-automation.generate')->middleware('throttle:20,1');
            Route::post('/academic-setup-automation/{generationLog}/publish', 'Registrar\RegistrarController@publishAcademicSetupAutomation')->name('academic-setup-automation.publish')->middleware('throttle:20,1');
            Route::get('/section-offering', 'Registrar\RegistrarController@sectionOffering')->name('section-offering');
            Route::get('/section-offering/data', 'Registrar\RegistrarController@sectionOfferingData')->name('section-offering.data')->middleware('throttle:60,1');
            Route::get('/section-offering/curriculum-subjects', 'Registrar\RegistrarController@sectionOfferingCurriculumSubjects')->name('section-offering.curriculum-subjects')->middleware('throttle:60,1');
            Route::post('/section-offering', 'Registrar\RegistrarController@storeSectionOffering')->name('section-offering.store')->middleware('throttle:60,1');
            Route::get('/class-schedule-preparation', 'Registrar\RegistrarController@classSchedulePreparation')->name('class-schedule-preparation');
            Route::post('/class-schedule-preparation/auto-generate', 'Registrar\RegistrarController@autoGenerateClassSchedulePreparation')->name('class-schedule-preparation.auto-generate')->middleware('throttle:30,1');
            Route::put('/class-schedule-preparation/{subject}', 'Registrar\RegistrarController@updateClassSchedulePreparation')->name('class-schedule-preparation.update')->middleware('throttle:60,1');
            Route::get('/slot-monitoring', 'Registrar\RegistrarController@slotMonitoring')->name('slot-monitoring');
            Route::get('/slot-monitoring/data', 'Registrar\RegistrarController@slotMonitoringData')->name('slot-monitoring.data')->middleware('throttle:60,1');
            Route::get('/slot-monitoring/reports/{reportType}', 'Registrar\RegistrarController@slotMonitoringReport')->name('slot-monitoring.report')->middleware('throttle:60,1')->where('reportType', 'actual-size|under-20|dissolved|closed');
            Route::post('/slot-monitoring', 'Registrar\RegistrarController@storeSlotMonitoring')->name('slot-monitoring.store')->middleware('throttle:60,1');
            Route::put('/slot-monitoring/{slotMonitoring}', 'Registrar\RegistrarController@updateSlotMonitoring')->name('slot-monitoring.update')->middleware('throttle:60,1');
            Route::delete('/slot-monitoring/{slotMonitoring}', 'Registrar\RegistrarController@destroySlotMonitoring')->name('slot-monitoring.delete')->middleware('throttle:60,1');
            Route::get('/section-merging', 'Registrar\RegistrarController@sectionMerging')->name('section-merging');
            Route::get('/section-merging/data', 'Registrar\RegistrarController@sectionMergingData')->name('section-merging.data')->middleware('throttle:60,1');
            Route::post('/section-merging', 'Registrar\RegistrarController@storeSectionMerging')->name('section-merging.store')->middleware('throttle:60,1');
        });

        // Student Management
        Route::prefix('student-management')->name('student-mgmt.')->group(function () {
            Route::get('/student-enrollment', 'Registrar\RegistrarController@studentEnrollment')->name('student-enrollment');
            Route::get('/student-records', 'Registrar\RegistrarController@studentRecordList')->name('student-records');
            Route::get('/student-records/{student}', 'Registrar\RegistrarController@studentRecordProfile')->name('student-records.profile');
            Route::post('/student-records/{student}/scholarships', 'Registrar\ScholarshipController@tagStudent')->name('student-records.scholarships.save')->middleware('throttle:30,1');
            Route::delete('/student-records/{student}/scholarships/{tag}', 'Registrar\ScholarshipController@untagStudent')->name('student-records.scholarships.delete')->middleware('throttle:30,1');
            Route::post('/student-records/{student}/scholastic-comments', 'Registrar\RegistrarController@studentScholasticCommentSave')->name('student-records.scholastic-comments.save')->middleware('throttle:30,1');
            Route::post('/student-records/{student}/requirements', 'Registrar\RegistrarController@studentRequirementStore')->name('student-records.requirements.store')->middleware('throttle:30,1');
            Route::post('/student-records/{student}/requirements/{requirement}/upload', 'Registrar\RegistrarController@studentRequirementUpload')->name('student-records.requirements.upload')->middleware('throttle:30,1');
            Route::post('/student-records/{student}/medical', 'Registrar\RegistrarController@studentRecordMedicalSave')->name('student-records.medical.save');
            Route::post('/student-records/{student}/clinic', 'Registrar\RegistrarController@studentClinicRecordSave')->name('student-records.clinic.save');
            Route::delete('/student-records/{student}/clinic/{clinic}', 'Registrar\RegistrarController@studentClinicRecordDelete')->name('student-records.clinic.delete');
            Route::get('/student-records/{student}/print/tor', 'Registrar\RegistrarController@studentPrintTor')->name('student-records.print.tor');
            Route::get('/student-records/{student}/print/diploma', 'Registrar\RegistrarController@studentPrintDiploma')->name('student-records.print.diploma');
            Route::post('/student-enrollment', 'Registrar\RegistrarController@storeStudent')->name('student-enrollment.store');
            Route::put('/student-enrollment/{student}', 'Registrar\RegistrarController@updateStudent')->name('student-enrollment.update')->middleware('throttle:60,1');
            Route::delete('/student-enrollment/{student}', 'Registrar\RegistrarController@destroyStudent')->name('student-enrollment.destroy')->middleware('throttle:60,1');
            Route::get('/clinic-record', 'Registrar\RegistrarController@clinicRecord')->name('clinic-record');
            Route::get('/academic-record/{student}', 'Registrar\RegistrarController@studentAcademicRecord')->name('academic-record.show');
            Route::put('/academic-record/{student}/profile', 'Registrar\RegistrarController@updateStudentAcademicProfile')->name('academic-record.update-profile')->middleware('throttle:30,1');
            Route::get('/academic-record/{student}/grades', 'Registrar\RegistrarController@getStudentGradeRecords')->name('academic-record.grades')->middleware('throttle:60,1');
            Route::post('/academic-record/{student}/grades', 'Registrar\RegistrarController@storeStudentGradeRecord')->name('academic-record.grades.store')->middleware('throttle:30,1');
            Route::put('/academic-record/{student}/grades/{record}', 'Registrar\RegistrarController@updateStudentGradeRecord')->name('academic-record.grades.update')->middleware('throttle:30,1');
            Route::delete('/academic-record/{student}/grades/{record}', 'Registrar\RegistrarController@destroyStudentGradeRecord')->name('academic-record.grades.destroy')->middleware('throttle:30,1');
            Route::post('/academic-record/grade-corrections/{gradeCorrectionRequest}/approve', 'Registrar\RegistrarController@approveGradeCorrectionRequest')->name('academic-record.grade-corrections.approve')->middleware('throttle:30,1');
            Route::post('/academic-record/grade-corrections/{gradeCorrectionRequest}/reject', 'Registrar\RegistrarController@rejectGradeCorrectionRequest')->name('academic-record.grade-corrections.reject')->middleware('throttle:30,1');
        });

        // Scholarship Module
        Route::prefix('scholarships')->name('scholarships.')->group(function () {
            Route::get('/', 'Registrar\ScholarshipController@index')->name('index');
            Route::post('/', 'Registrar\ScholarshipController@store')->name('store')->middleware('throttle:30,1');
            Route::put('/{scholarship}', 'Registrar\ScholarshipController@update')->name('update')->middleware('throttle:30,1');
            Route::delete('/{scholarship}', 'Registrar\ScholarshipController@destroy')->name('destroy')->middleware('throttle:30,1');
            Route::get('/reports/scholars-by-type', 'Registrar\ScholarshipController@report')->name('report');
        });

        // Faculty Management
        Route::prefix('faculty-management')->name('faculty-mgmt.')->group(function () {
            Route::get('/faculty-create', 'Registrar\RegistrarController@facultyCreate')->name('faculty-create');
            Route::post('/faculty-create', 'Registrar\RegistrarController@storeFaculty')->name('faculty-create.store');
            Route::get('/departments', 'Registrar\Services\FacultyLoadsController@departments')->name('departments');
            Route::post('/departments', 'Registrar\Services\FacultyLoadsController@storeDepartment')->name('departments.store');
            Route::put('/departments/{department}', 'Registrar\Services\FacultyLoadsController@updateDepartment')->name('departments.update');
            Route::delete('/departments/{department}', 'Registrar\Services\FacultyLoadsController@destroyDepartment')->name('departments.destroy');
            Route::get('/faculty-list', 'Registrar\Services\FacultyLoadsController@index')->name('faculty-list');
            Route::get('/faculty-list/{faculty}', 'Registrar\Services\FacultyLoadsController@show')->name('faculty-list.show');
            Route::put('/faculty-list/{faculty}/profile', 'Registrar\Services\FacultyLoadsController@updateProfile')->name('faculty-list.profile.update');
            Route::post('/faculty-list/{faculty}/allowed-subjects', 'Registrar\Services\FacultyLoadsController@allowSubject')->name('faculty-list.allowed-subjects.store');
            Route::delete('/faculty-list/{faculty}/allowed-subjects/{subject}', 'Registrar\Services\FacultyLoadsController@removeAllowedSubject')->name('faculty-list.allowed-subjects.destroy');
            Route::get('/grading-sheet', 'Registrar\RegistrarController@gradingSheet')->name('grading-sheet');
            Route::post('/grading-sheet/action', 'Registrar\RegistrarController@gradingSheetAction')->name('grading-sheet.action');
            Route::post('/grading-sheet/update-phase', 'Registrar\RegistrarController@gradingSheetUpdatePhase')->name('grading-sheet.update-phase')->middleware('throttle:60,1');
            Route::get('/evaluation', 'Registrar\RegistrarController@evaluation')->name('evaluation');
            Route::post('/evaluation', 'Registrar\RegistrarController@storeEvaluationForm')->name('evaluation.store')->middleware('throttle:30,1');
            Route::post('/evaluation/{evaluationForm}/publish', 'Registrar\RegistrarController@publishEvaluationForm')->name('evaluation.publish')->middleware('throttle:30,1');
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
            Route::get('/application-leave-of-absence-enrolled/{student}', 'Registrar\RegistrarController@formsApplicationLeaveAbsenceEnrolled')->name('application-leave-of-absence-enrolled.show');
            Route::get('/application-leave-of-absence-enrolled', 'Registrar\RegistrarController@formsApplicationLeaveAbsenceEnrolled')->name('application-leave-of-absence-enrolled');
            Route::get('/diploma', 'Registrar\RegistrarController@formsDiploma')->name('diploma');
            Route::get('/graduation-clearance/{student}', 'Registrar\RegistrarController@formsGraduationClearance')->name('graduation-clearance.show');
            Route::get('/graduation-clearance', 'Registrar\RegistrarController@formsGraduationClearance')->name('graduation-clearance');
            Route::get('/clearance-2/{student}', 'Registrar\RegistrarController@formsClearance2')->name('clearance-2.show');
            Route::get('/clearance-2', 'Registrar\RegistrarController@formsClearance2')->name('clearance-2');
            Route::get('/honorable-dismissal/template/layout/{student?}', 'Registrar\RegistrarController@getTemplateLayoutData')->name('honorable-dismissal.template.layout');
            Route::post('/honorable-dismissal/template/layout', 'Registrar\RegistrarController@saveTemplateLayout')->name('honorable-dismissal.template.save');
            Route::get('/honorable-dismissal/{student}', 'Registrar\RegistrarController@formsHonorableDismissal')->name('honorable-dismissal.show');
            Route::get('/honorable-dismissal', 'Registrar\RegistrarController@formsHonorableDismissal')->name('honorable-dismissal');
            Route::post('/honorable-dismissal/{student}/tag', 'Registrar\RegistrarController@formsHonorableDismissalTag')->name('honorable-dismissal.tag');
            Route::post('/honorable-dismissal/{student}/issue', 'Registrar\RegistrarController@formsHonorableDismissalIssue')->name('honorable-dismissal.issue');
            Route::post('/honorable-dismissal/bulk-issue', 'Registrar\RegistrarController@formsHonorableDismissalBulkIssue')->name('honorable-dismissal.bulk-issue');
            Route::get('/official-grade-report', 'Registrar\RegistrarController@formsOfficialGradeReport')->name('official-grade-report');
            Route::get('/official-grade-report/filter', 'Registrar\RegistrarController@formsOfficialGradeReportList')->name('official-grade-report.filter');
            Route::get('/official-grade-report/{student}/data', 'Registrar\RegistrarController@formsOfficialGradeReportData')->name('official-grade-report.data');
            Route::get('/permission-cross-enroll', 'Registrar\RegistrarController@formsPermissionCrossEnroll')->name('permission-cross-enroll');
            Route::get('/citizens-charter', 'Registrar\RegistrarController@formsCitizensCharter')->name('citizens-charter');
            Route::get('/request-form-f-137a/{student}', 'Registrar\RegistrarController@formsRequestFormF137a')->name('request-form-f-137a.show');
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
                Route::get('/deans-honors/{student}', 'Registrar\\RegistrarController@formsCertificateDeansHonors')->name('deans-honors.show');
                Route::get('/deans-honors', 'Registrar\\RegistrarController@formsCertificateDeansHonors')->name('deans-honors');
                Route::get('/presidents-honors/{student}', 'Registrar\\RegistrarController@formsCertificatePresidentsHonors')->name('presidents-honors.show');
                Route::get('/presidents-honors', 'Registrar\\RegistrarController@formsCertificatePresidentsHonors')->name('presidents-honors');
                Route::get('/8c2-certificate-of-graduation/{student}', 'Registrar\\RegistrarController@formsCertificateGraduation8c2')->name('certificate-graduation-8c2.show');
                Route::get('/8c2-certificate-of-graduation', 'Registrar\\RegistrarController@formsCertificateGraduation8c2')->name('certificate-graduation-8c2');
                Route::get('/8d2-certificate-of-honor/{student}', 'Registrar\\RegistrarController@formsCertificateHonor8d2')->name('certificate-honor-8d2.show');
                Route::get('/8d2-certificate-of-honor', 'Registrar\\RegistrarController@formsCertificateHonor8d2')->name('certificate-honor-8d2');
            });

            // Copy Of Grades (COG)
            Route::prefix('cog')->name('cog.')->group(function () {
                Route::get('/copy-of-grades', 'Registrar\\RegistrarController@formsCopyOfGradesCog')->name('copy-of-grades');
            });

            // Certificate of Registration (COR)
            Route::prefix('cor')->name('cor.')->group(function () {
                Route::get('/students/search', 'Registrar\\RegistrarController@formsCorStudentSearch')->name('students.search');
                Route::get('/certificate-of-registration', 'Registrar\\RegistrarController@formsCertificateOfRegistration')->name('certificate-of-registration');
            });
        });
    });

    // Services sub-pages
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/section-list', 'Registrar\Services\SectionListController@index')->name('section-list');

        Route::prefix('classroom-faculty')->name('classroom-faculty.')->group(function () {
            Route::get('/class-list', 'Registrar\Services\ClassListController@index')->name('class-list');
            Route::get('/class-list/export/{format}', 'Registrar\Services\ClassListController@export')->name('class-list.export');
            Route::get('/attendance', 'Registrar\Services\AttendanceController@index')->name('attendance');

            Route::prefix('faculty-loads')->name('faculty-loads.')->group(function () {
                Route::get('/', 'Registrar\Services\FacultyLoadsController@index')->name('index');
                Route::get('/{faculty}', 'Registrar\Services\FacultyLoadsController@show')->name('show');
                Route::put('/{faculty}/profile', 'Registrar\Services\FacultyLoadsController@updateProfile')->name('profile.update');
                Route::post('/{faculty}/assign', 'Registrar\Services\FacultyLoadsController@assign')->name('assign');
                Route::post('/{faculty}/allowed-subjects', 'Registrar\Services\FacultyLoadsController@allowSubject')->name('allowed-subjects.store');
                Route::delete('/{faculty}/allowed-subjects/{subject}', 'Registrar\Services\FacultyLoadsController@removeAllowedSubject')->name('allowed-subjects.destroy');
                Route::get('/{faculty}/print-strength-of-classes', 'Registrar\Services\FacultyLoadsController@printStrengthOfClasses')->name('print-strength-of-classes');
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
            Route::post('/transmutation/copy', 'Registrar\Services\GradingAcademicController@transmutationCopy')->name('transmutation.copy');
            Route::put('/transmutation/{transmutationRule}', 'Registrar\Services\GradingAcademicController@transmutationUpdate')->name('transmutation.update');
            Route::delete('/transmutation/{transmutationRule}', 'Registrar\Services\GradingAcademicController@transmutationDestroy')->name('transmutation.destroy');
            Route::get('/incomplete-failing', 'Registrar\Services\GradingAcademicController@incompleteFailing')->name('incomplete-failing');
            Route::get('/deficiency', 'Registrar\Services\GradingAcademicController@deficiency')->name('deficiency');
            Route::get('/scholastic-comments', 'Registrar\Services\GradingAcademicController@scholasticComments')->name('scholastic-comments');
            Route::get('/deficiency/students/search', 'Registrar\Services\GradingAcademicController@deficiencyStudentSearch')->name('deficiency.students.search');
            Route::post('/deficiency/students', 'Registrar\Services\GradingAcademicController@deficiencyStudentStore')->name('deficiency.students.store');
            Route::get('/deficiency/{student}/records', 'Registrar\Services\GradingAcademicController@deficiencyRecords')->name('deficiency.records');
            Route::post('/deficiency/{student}/records', 'Registrar\Services\GradingAcademicController@deficiencyStore')->name('deficiency.store');
            Route::put('/deficiency/records/{studentDeficiency}', 'Registrar\Services\GradingAcademicController@deficiencyUpdate')->name('deficiency.update');
            Route::delete('/deficiency/records/{studentDeficiency}', 'Registrar\Services\GradingAcademicController@deficiencyDestroy')->name('deficiency.destroy');
        });

        Route::prefix('reports-admin')->name('reports-admin.')->group(function () {
            Route::get('/academic-reports', 'Registrar\Services\ReportsAdminController@academicReports')->name('academic-reports');
            Route::get('/gwa-report', 'Registrar\Services\ReportsAdminController@gwaReport')->name('gwa-report');
            Route::post('/gwa-report/create-test', 'Registrar\Services\ReportsAdminController@gwaReportCreateTest')->name('gwa-report.create-test');
            Route::post('/academic-reports/issue', 'Registrar\Services\ReportsAdminController@issueAcademicReport')->name('academic-reports.issue');
            Route::get('/guidance-reports', 'Registrar\Services\ReportsAdminController@guidanceReports')->name('guidance-reports');
            Route::get('/students/search', 'Registrar\Services\ReportsAdminController@studentSearch')->name('students.search');
            Route::get('/certifications', 'Registrar\Services\ReportsAdminController@certifications')->name('certifications');
            Route::post('/certifications/issue', 'Registrar\Services\ReportsAdminController@issueCertification')->name('certifications.issue');
            Route::get('/tagging-of-graduates', 'Registrar\Services\ReportsAdminController@taggingOfGraduates')->name('tagging-of-graduates');
            Route::put('/tagging-of-graduates/{student}', 'Registrar\Services\ReportsAdminController@taggingOfGraduatesUpdate')->name('tagging-of-graduates.update');
        });

        Route::prefix('student-account')->name('student-account.')->group(function () {
            Route::get('/student-discipline', 'Registrar\Services\StudentAccountController@studentDiscipline')->name('student-discipline');
            Route::get('/student-discipline/data', 'Registrar\Services\StudentAccountController@studentDisciplineData')->name('student-discipline.data');
            Route::get('/student-discipline/students/search', 'Registrar\Services\StudentAccountController@studentDisciplineStudentSearch')->name('student-discipline.students.search');
            Route::get('/student-discipline/programs/search', 'Registrar\Services\StudentAccountController@studentDisciplineProgramSearch')->name('student-discipline.programs.search');
            Route::post('/student-discipline/students', 'Registrar\Services\StudentAccountController@studentDisciplineStudentStore')->name('student-discipline.students.store');
            Route::put('/student-discipline/students/{studentDisciplineStudent}', 'Registrar\Services\StudentAccountController@studentDisciplineStudentUpdate')->name('student-discipline.students.update');
            Route::delete('/student-discipline/students/{studentDisciplineStudent}', 'Registrar\Services\StudentAccountController@studentDisciplineStudentDestroy')->name('student-discipline.students.destroy');
            Route::get('/student-discipline/students/{studentDisciplineStudent}/records', 'Registrar\Services\StudentAccountController@studentDisciplineRecords')->name('student-discipline.records');
            Route::post('/student-discipline/students/{studentDisciplineStudent}/records', 'Registrar\Services\StudentAccountController@studentDisciplineRecordStore')->name('student-discipline.records.store');
            Route::put('/student-discipline/records/{studentDisciplineRecord}', 'Registrar\Services\StudentAccountController@studentDisciplineRecordUpdate')->name('student-discipline.records.update');
            Route::delete('/student-discipline/records/{studentDisciplineRecord}', 'Registrar\Services\StudentAccountController@studentDisciplineRecordDestroy')->name('student-discipline.records.destroy');
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
            Route::post('/configuration/signature', 'Registrar\Services\AdminToolsController@configurationSignatureStore')->name('configuration.signature.store');
            Route::get('/configuration/signature/{systemConfigNameSignature}/file', 'Registrar\Services\AdminToolsController@configurationSignatureFile')->name('configuration.signature.file');
            Route::delete('/configuration/signature/{systemConfigNameSignature}/file', 'Registrar\Services\AdminToolsController@configurationSignatureFileDestroy')->name('configuration.signature.file.destroy');
            Route::put('/configuration/signature/{systemConfigNameSignature}', 'Registrar\Services\AdminToolsController@configurationSignatureUpdate')->name('configuration.signature.update');
            Route::delete('/configuration/signature/{systemConfigNameSignature}', 'Registrar\Services\AdminToolsController@configurationSignatureDestroy')->name('configuration.signature.destroy');
            Route::post('/configuration/cutoff', 'Registrar\Services\AdminToolsController@configurationCutoffStore')->name('configuration.cutoff.store');
            Route::put('/configuration/cutoff/{systemCutoffEntry}', 'Registrar\Services\AdminToolsController@configurationCutoffUpdate')->name('configuration.cutoff.update');
            Route::delete('/configuration/cutoff/{systemCutoffEntry}', 'Registrar\Services\AdminToolsController@configurationCutoffDestroy')->name('configuration.cutoff.destroy');
            Route::post('/configuration/curriculum-display', 'Registrar\Services\AdminToolsController@configurationCurriculumDisplayStore')->name('configuration.curriculum-display.store');
            Route::put('/configuration/curriculum-display/{systemCurriculumDisplaySetting}', 'Registrar\Services\AdminToolsController@configurationCurriculumDisplayUpdate')->name('configuration.curriculum-display.update');
            Route::delete('/configuration/curriculum-display/{systemCurriculumDisplaySetting}', 'Registrar\Services\AdminToolsController@configurationCurriculumDisplayDestroy')->name('configuration.curriculum-display.destroy');
            Route::post('/configuration/report-details', 'Registrar\Services\AdminToolsController@configurationReportDetailsSave')->name('configuration.report-details.save');
            Route::post('/configuration/email-sender', 'Registrar\Services\AdminToolsController@configurationEmailSenderSave')->name('configuration.email-sender.save');
            Route::post('/configuration/overdue-inc/process', 'Registrar\Services\AdminToolsController@configurationOverdueIncProcess')->name('configuration.overdue-inc.process');
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
            Route::get('/user-accounts/data', 'Registrar\Services\AdminToolsController@userAccountsData')->name('user-accounts.data');
            Route::post('/user-accounts', 'Registrar\Services\AdminToolsController@userAccountsStore')->name('user-accounts.store')->middleware('throttle:60,1');
            Route::get('/user-accounts/access-control/modules', 'Registrar\Services\AdminToolsController@userAccountAccessControlModules')->name('user-accounts.access-control.modules');
            Route::get('/user-accounts/{user}/access-control', 'Registrar\Services\AdminToolsController@userAccountAccessControlShow')->name('user-accounts.access-control.show');
            Route::put('/user-accounts/{user}/access-control', 'Registrar\Services\AdminToolsController@userAccountAccessControlUpdate')->name('user-accounts.access-control.update')->middleware('throttle:60,1');
            Route::put('/user-accounts/{user}', 'Registrar\Services\AdminToolsController@userAccountsUpdate')->name('user-accounts.update');
            Route::delete('/user-accounts/{user}', 'Registrar\Services\AdminToolsController@userAccountsDestroy')->name('user-accounts.destroy');
            Route::get('/report-access', 'Registrar\Services\AdminToolsController@reportAccess')->name('report-access');
            Route::put('/report-access/{user}', 'Registrar\Services\AdminToolsController@reportAccessUpdate')->name('report-access.update');

            Route::get('/roles', 'Registrar\Services\AdminToolsController@accessControlRolesData')->name('roles');
            Route::post('/roles', 'Registrar\Services\AdminToolsController@accessControlRolesStore')->name('roles.store');
            Route::put('/roles/{accessControlRole}', 'Registrar\Services\AdminToolsController@accessControlRolesUpdate')->name('roles.update');
            Route::delete('/roles/{accessControlRole}', 'Registrar\Services\AdminToolsController@accessControlRolesDestroy')->name('roles.destroy');
            Route::get('/roles/{accessControlRole}/access-control', 'Registrar\Services\AdminToolsController@accessControlRoleAccessControlShow')->name('roles.access-control.show');
            Route::put('/roles/{accessControlRole}/access-control', 'Registrar\Services\AdminToolsController@accessControlRoleAccessControlUpdate')->name('roles.access-control.update')->middleware('throttle:60,1');
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
            Route::get('/student-grade-file/records', 'Registrar\Services\AdminToolsController@studentGradeRecords')->name('student-grade-file.records');
            Route::post('/student-grade-file/records', 'Registrar\Services\AdminToolsController@studentGradeRecordStore')->name('student-grade-file.records.store');
            Route::put('/student-grade-file/records/{studentGradeRecord}', 'Registrar\Services\AdminToolsController@studentGradeRecordUpdate')->name('student-grade-file.records.update');
            Route::delete('/student-grade-file/records/{studentGradeRecord}', 'Registrar\Services\AdminToolsController@studentGradeRecordDestroy')->name('student-grade-file.records.destroy');
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

        Route::get('/audit-trail', 'Registrar\Services\AdminToolsController@auditTrail')->name('audit-trail');

        Route::prefix('grade-override')->name('grade-override.')->group(function () {
            Route::get('/', 'Registrar\RegistrarController@gradeOverrideIndex')->name('index');
            Route::get('/terms', 'Registrar\RegistrarController@gradeOverrideTerms')->name('terms')->middleware('throttle:60,1');
            Route::get('/subjects', 'Registrar\RegistrarController@gradeOverrideSubjects')->name('subjects')->middleware('throttle:60,1');
            Route::get('/students', 'Registrar\RegistrarController@gradeOverrideStudents')->name('students')->middleware('throttle:60,1');
            Route::post('/update', 'Registrar\RegistrarController@gradeOverrideUpdate')->name('update')->middleware('throttle:30,1');
            Route::get('/logs', 'Registrar\RegistrarController@gradeOverrideLogs')->name('logs')->middleware('throttle:60,1');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Applicant Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->name('applicant.')->middleware(['auth', 'applicant.user', 'force_password_reset', 'user.access'])->group(function () {
    Route::get('/application-form', 'Applicant\ApplicantController@applicationForm')->name('application-form');
    Route::get('/help-center', 'Applicant\ApplicantController@helpCenter')->name('help.center');
    Route::get('/help-center/tickets/create', 'Applicant\ApplicantController@createHelpCenterTicket')->name('help.tickets.create');
    Route::post('/help-center/tickets', 'Applicant\ApplicantController@storeHelpCenterTicket')->name('help.tickets.store')->middleware('throttle:20,1');
    Route::get('/help-center/live-chat', 'Applicant\ApplicantController@helpCenterLiveChat')->name('help.live-chat');
    Route::get('/help-center/{topic}', 'Applicant\ApplicantController@helpCenterTopic')->name('help.topic');
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
    Route::get('/notifications/feed', 'Applicant\ApplicantController@notificationsFeed')->name('notifications.feed');
    Route::post('/notifications/mark-read', 'Applicant\ApplicantController@markNotificationsRead')->name('notifications.mark-read');
    Route::post('/notifications/{notificationDelivery}/dismiss', 'Applicant\ApplicantController@dismissNotification')->name('notifications.dismiss');
    Route::get('/messaging', 'Applicant\ApplicantController@messaging')->name('messaging');
    Route::get('/medical-clearance', 'Applicant\ApplicantController@medicalClearance')->name('medical-clearance');
    Route::get('/documents-submitted', 'Applicant\ApplicantController@documentsSubmitted')->name('documents-submitted');
});

/*
|--------------------------------------------------------------------------
| Faculty Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('faculty')->name('faculty.')->middleware(['auth', 'force_password_reset', 'user.access'])->group(function () {
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
    Route::get('/notifications/feed', 'Faculty\FacultyController@notificationsFeed')->name('notifications.feed');
    Route::post('/notifications/mark-read', 'Faculty\FacultyController@markNotificationsRead')->name('notifications.mark-read');
    Route::post('/notifications/{notificationDelivery}/dismiss', 'Faculty\FacultyController@dismissNotification')->name('notifications.dismiss');
    Route::post('/grading-sheet/update-row', 'Faculty\FacultyController@updateGradeRow')->name('faculty.grading-sheet.update-row');
    Route::post('/grading-sheet/submit-grades', 'Faculty\FacultyController@submitGrades')->name('faculty.grading-sheet.submit');
});






Route::prefix('registrar/process/religion')->name('registrar.process.religion.')->middleware(['auth', 'force_password_reset', 'user.access'])->group(function () {
    Route::get('/', 'ReligionController@index')->name('index');
    Route::get('/data', 'ReligionController@getData')->name('data');
    Route::post('/', 'ReligionController@store')->name('store');
    Route::get('/{id}/edit', 'ReligionController@edit')->name('edit');
    Route::put('/{id}', 'ReligionController@update')->name('update');
    Route::delete('/{id}', 'ReligionController@destroy')->name('destroy');
});
