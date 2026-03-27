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
    return redirect('/admin/access');
});

Auth::routes();
Route::get('/password/setup', 'Auth\FirstLoginPasswordController@show')->name('password.first_reset');
Route::post('/password/setup', 'Auth\FirstLoginPasswordController@update')->name('password.first_reset.update');

Route::get('/home', 'HomeController@index')->name('home');

// Michael's placeholder routes
Route::get('/applicant', function () {
    return view('applicant.applicant-login-placeholder');
});

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
Route::post('/login/module-auth', 'Admin\AdminController@moduleAuthLogin')->name('module.login.submit');

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
        Route::get('/requirements', 'Registrar\RegistrarController@requirements')->name('requirements');
        Route::get('/citizenship', 'Registrar\RegistrarController@citizenship')->name('citizenship');
        Route::get('/religion', 'Registrar\RegistrarController@religion')->name('religion');
        Route::get('/approval-status', 'Registrar\RegistrarController@approvalStatus')->name('approval-status');
        Route::get('/batch-upload', 'Registrar\RegistrarController@batchUpload')->name('batch-upload');
        Route::get('/document-list', 'Registrar\RegistrarController@documentList')->name('document-list');
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
            Route::post('/program-file/setup', 'Registrar\RegistrarController@saveProgramSetup')->name('program-file.setup');
            Route::get('/subject-file', 'Registrar\RegistrarController@subjectFile')->name('subject-file');
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
        });

        // Forms
        Route::prefix('forms')->name('forms.')->group(function () {
            Route::get('/placeholder', 'Registrar\RegistrarController@formsPlaceholder')->name('placeholder');
            Route::get('/tor', 'Registrar\RegistrarController@formsTor')->name('tor');
            Route::get('/diploma', 'Registrar\RegistrarController@formsDiploma')->name('diploma');
            Route::get('/graduation-clearance', 'Registrar\RegistrarController@formsGraduationClearance')->name('graduation-clearance');
            Route::get('/honorable-dismissal', 'Registrar\RegistrarController@formsHonorableDismissal')->name('honorable-dismissal');
            Route::get('/official-grade-report', 'Registrar\RegistrarController@formsOfficialGradeReport')->name('official-grade-report');
            Route::get('/permission-cross-enroll', 'Registrar\RegistrarController@formsPermissionCrossEnroll')->name('permission-cross-enroll');
            Route::get('/waiver-cancellation', 'Registrar\RegistrarController@formsWaiverCancellation')->name('waiver-cancellation');
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
            Route::get('/grading-periods', 'Registrar\Services\GradingAcademicController@gradingPeriods')->name('grading-periods');
            Route::get('/grading-components', 'Registrar\Services\GradingAcademicController@gradingComponents')->name('grading-components');
            Route::get('/transmutation', 'Registrar\Services\GradingAcademicController@transmutation')->name('transmutation');
            Route::get('/deficiency', 'Registrar\Services\GradingAcademicController@deficiency')->name('deficiency');
        });

        Route::prefix('reports-admin')->name('reports-admin.')->group(function () {
            Route::get('/academic-reports', 'Registrar\Services\ReportsAdminController@academicReports')->name('academic-reports');
            Route::get('/guidance-reports', 'Registrar\Services\ReportsAdminController@guidanceReports')->name('guidance-reports');
            Route::get('/certifications', 'Registrar\Services\ReportsAdminController@certifications')->name('certifications');
            Route::get('/tagging-of-graduates', 'Registrar\Services\ReportsAdminController@taggingOfGraduates')->name('tagging-of-graduates');
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
            Route::get('/admission-config', 'Registrar\Services\AdminToolsController@admissionConfig')->name('admission-config');
            Route::get('/academic-calendar', 'Registrar\Services\AdminToolsController@academicCalendar')->name('academic-calendar');
            Route::get('/announcement', 'Registrar\Services\AdminToolsController@announcement')->name('announcement');
        });

        Route::prefix('access-management')->name('access-management.')->group(function () {
            Route::get('/user-accounts', 'Registrar\Services\AdminToolsController@userAccounts')->name('user-accounts');
            Route::get('/report-access', 'Registrar\Services\AdminToolsController@reportAccess')->name('report-access');
        });

        Route::prefix('master-files')->name('master-files.')->group(function () {
            Route::get('/faculty-file', 'Registrar\Services\AdminToolsController@facultyFile')->name('faculty-file');
            Route::get('/student-profile', 'Registrar\Services\AdminToolsController@studentProfile')->name('student-profile');
            Route::get('/student-grade-file', 'Registrar\Services\AdminToolsController@studentGradeFile')->name('student-grade-file');
        });

        Route::prefix('student-maintenance')->name('student-maintenance.')->group(function () {
            Route::get('/bed-student-status', 'Registrar\Services\AdminToolsController@bedStudentStatus')->name('bed-student-status');
            Route::get('/bed-days', 'Registrar\Services\AdminToolsController@bedDays')->name('bed-days');
            Route::get('/student-update', 'Registrar\Services\AdminToolsController@studentUpdate')->name('student-update');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Applicant Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->name('applicant.')->middleware(['auth', 'force_password_reset'])->group(function () {
    Route::get('/application-form', 'Applicant\ApplicantController@applicationForm')->name('application-form');
    Route::post('/application-form', 'Applicant\ApplicantController@saveApplicationForm')->name('application-form.save');
    Route::get('/schedule-of-exam', 'Applicant\ApplicantController@scheduleOfExam')->name('schedule-of-exam');
    Route::get('/exam-result', 'Applicant\ApplicantController@examResult')->name('exam-result');
});

/*
|--------------------------------------------------------------------------
| Faculty Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('faculty')->name('faculty.')->middleware(['auth', 'force_password_reset'])->group(function () {
    Route::get('/load', 'Faculty\FacultyController@facultyLoad')->name('load');
    Route::get('/class-list', 'Faculty\FacultyController@classList')->name('class-list');
    Route::get('/calendar', 'Faculty\FacultyController@calendar')->name('calendar');
    Route::get('/grading-sheet', 'Faculty\FacultyController@gradingSheet')->name('grading-sheet');
    Route::post('/grading-sheet/update', 'Faculty\FacultyController@updateGrades')->name('grading-sheet.update');
    Route::get('/evaluation', 'Faculty\FacultyController@evaluation')->name('evaluation');
});
