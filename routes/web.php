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
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/', function () {
        return view('student.access-module');
    })->name('access-module');
    Route::get('/section-offering', 'Student\StudentController@sectionOffering')->name('section-offering');
    Route::get('/grades', 'Student\StudentController@grades')->name('grades');
    Route::get('/schedule', 'Student\StudentController@schedule')->name('schedule');
    Route::get('/events', 'Student\StudentController@events')->name('events');
    Route::get('/profile', 'Student\StudentController@profile')->name('profile');
});

/*
|--------------------------------------------------------------------------
| Registrar Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/dashboard', 'Registrar\RegistrarController@dashboard')->name('dashboard');

    // Process sub-pages
    Route::prefix('process')->name('process.')->group(function () {
        Route::get('/application', 'Registrar\RegistrarController@applicationProcess')->name('application');
        Route::get('/citizenship', 'Registrar\RegistrarController@citizenship')->name('citizenship');
        Route::get('/religion', 'Registrar\RegistrarController@religion')->name('religion');
        Route::get('/approval-status', 'Registrar\RegistrarController@approvalStatus')->name('approval-status');
        Route::get('/batch-upload', 'Registrar\RegistrarController@batchUpload')->name('batch-upload');
        Route::get('/document-list', 'Registrar\RegistrarController@documentList')->name('document-list');
        Route::get('/admission-report', 'Registrar\RegistrarController@admissionReport')->name('admission-report');
        Route::get('/schedule-exam', 'Registrar\RegistrarController@scheduleExam')->name('schedule-exam');
    });
});
