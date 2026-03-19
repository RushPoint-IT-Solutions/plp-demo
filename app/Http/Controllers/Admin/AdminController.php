<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Real student login using username + password.
     */
    public function studentLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $remember = (bool) $request->input('remember', false);

        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'module' => 'student',
        ], $remember)) {
            $request->session()->regenerate();

            return redirect()->route('student.section-offering');
        }

        return back()->withErrors([
            'username' => 'Invalid student credentials.',
        ])->withInput($request->only('username', 'remember'));
    }
}
