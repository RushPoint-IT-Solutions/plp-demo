<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Show the admin access module page.
     */
    public function accessModule()
    {
        return view('admin.access-module');
    }

    /**
     * Show the module-specific login page.
     *
     * @param string $module  e.g. 'registrar', 'accounting', 'cashier', 'faculty', 'student', 'applicant'
     */
    public function moduleLogin($module)
    {
        return view('auth.login', ['module' => $module]);
    }

    /**
     * Demo login – no real authentication.
     * Just redirects to the first page of whatever module was selected.
     * Replace with real auth logic when the backend team is ready.
     */
    public function demoLogin(Request $request)
    {
        $module = $request->input('module', 'student');

        $redirectMap = [
            'student'    => 'student.grades',
            'applicant'  => 'applicant.application-form',
            'registrar'  => 'registrar.dashboard',
            'accounting' => 'admin.access-module',
            'cashier'    => 'admin.access-module',
            'faculty'    => 'faculty.load',
        ];

        $routeName = $redirectMap[$module] ?? 'admin.access-module';

        return redirect()->route($routeName);
    }
}
