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


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/', function () {
    return view('student.access-module');
    });

// Placeholder Routes for Luis's tasks
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

Route::get('/student', function () {
    return view('student.student-login-placeholder');
});


