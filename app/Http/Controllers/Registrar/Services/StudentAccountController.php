<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;

class StudentAccountController extends Controller
{
    public function studentDiscipline()
    {
        return view('registrar.services.student-account.student-discipline');
    }

    public function family()
    {
        return view('registrar.services.student-account.family');
    }

    public function changePassword()
    {
        return view('registrar.services.student-account.change-password');
    }
}
