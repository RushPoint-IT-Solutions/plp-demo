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

Route::get('/applicant-form', function () {
    return view('applicant.applicant-form-placeholder');
});

Route::get('/student', function () {
    return view('student.student-login-placeholder');
});

Route::get('/section-offering', 'StudentController@sectionOffering');

Route::get('/schedule', 'StudentController@schedule');
