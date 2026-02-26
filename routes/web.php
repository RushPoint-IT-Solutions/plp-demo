<?php

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
| Each module (Registrar, Accounting, etc.) gets its own login page
*/
Route::get('/login/{module}', 'Admin\AdminController@moduleLogin')->name('module.login')
    ->where('module', 'registrar|accounting|cashier|faculty|applicant');

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/section-offering', 'Student\StudentController@sectionOffering')->name('section-offering');
    Route::get('/grades', 'Student\StudentController@grades')->name('grades');
    Route::get('/schedule', 'Student\StudentController@schedule')->name('schedule');
    Route::get('/events', 'Student\StudentController@events')->name('events');
    Route::get('/profile', 'Student\StudentController@profile')->name('profile');
});
