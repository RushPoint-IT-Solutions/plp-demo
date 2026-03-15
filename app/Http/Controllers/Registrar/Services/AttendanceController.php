<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('registrar.services.classroom-faculty.attendance');
    }
}
