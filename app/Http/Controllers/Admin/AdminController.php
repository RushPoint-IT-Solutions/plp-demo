<?php

namespace App\Http\Controllers\Admin;

use App\Applicant;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private function hasRequiredRoleLink(User $user, string $module): bool
    {
        switch ($module) {
            case 'student':
                return !is_null($user->student_id);
            case 'faculty':
                return !is_null($user->faculty_id);
            case 'registrar':
                return !is_null($user->registrar_id);
            case 'applicant':
                return !is_null($user->applicant_id);
            default:
                return true;
        }
    }

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
     * Real student login using username OR student number + password.
     */
    public function studentLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->filled('remember');
        $loginIdentifier = trim($credentials['username']);

        $isAuthenticated = Auth::attempt([
            'username' => $loginIdentifier,
            'password' => $credentials['password'],
        ], $remember);

        if (!$isAuthenticated) {
            $studentUser = User::where('module', 'student')
                ->whereHas('student', function ($query) use ($loginIdentifier) {
                    $query->where('student_no', $loginIdentifier);
                })
                ->first();

            if ($studentUser) {
                $isAuthenticated = Auth::attempt([
                    'username' => $studentUser->username,
                    'password' => $credentials['password'],
                ], $remember);
            }
        }

        if ($isAuthenticated) {
            $user = Auth::user();
            if (!$user || $user->module !== 'student' || !$this->hasRequiredRoleLink($user, 'student')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Student account is not linked yet. Please contact the registrar.',
                ])->withInput($request->only('username', 'remember'));
            }

            // Ensure remember cookie is explicitly set for persistent login
            Auth::login($user, $remember);

            $request->session()->regenerate();

            return redirect()->route('student.grades');
        }

        return back()->withErrors([
            'username' => 'Invalid student credentials.',
        ])->withInput($request->only('username', 'remember'));
    }

    /**
     * Real login for registrar/faculty using username + password.
     */
    public function moduleAuthLogin(Request $request)
    {
        $credentials = $request->validate([
            'module' => 'required|string|in:registrar,faculty',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $module = $credentials['module'];
        $remember = $request->filled('remember');

        $isAuthenticated = Auth::attempt([
            'username' => trim($credentials['username']),
            'password' => $credentials['password'],
        ], $remember);

        if (!$isAuthenticated) {
            return back()->withErrors([
                'username' => 'Invalid ' . $module . ' credentials.',
            ])->withInput($request->only('username', 'remember'));
        }

        $user = Auth::user();
        if (!$user || $user->module !== $module || !$this->hasRequiredRoleLink($user, $module)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'username' => ucfirst($module) . ' account is not linked yet. Please contact the administrator.',
            ])->withInput($request->only('username', 'remember'));
        }

        // Ensure remember cookie is explicitly set for persistent login
        Auth::login($user, $remember);

        $request->session()->regenerate();

        $redirectMap = [
            'registrar' => 'registrar.dashboard',
            'faculty' => 'faculty.load',
        ];

        return redirect()->route($redirectMap[$module]);
    }

    /**
     * Real applicant login using username OR applicant number + password.
     */
    public function applicantLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->filled('remember');
        $loginIdentifier = trim($credentials['username']);

        $isAuthenticated = Auth::attempt([
            'username' => $loginIdentifier,
            'password' => $credentials['password'],
        ], $remember);

        if (!$isAuthenticated) {
            $applicantUser = User::where('module', 'applicant')
                ->whereHas('applicant', function ($query) use ($loginIdentifier) {
                    $query->where('applicant_id', $loginIdentifier);
                })
                ->first();

            if ($applicantUser) {
                $isAuthenticated = Auth::attempt([
                    'username' => $applicantUser->username,
                    'password' => $credentials['password'],
                ], $remember);
            }
        }

        if ($isAuthenticated) {
            $user = Auth::user();

            if ($user && $user->module === 'applicant' && is_null($user->applicant_id)) {
                $legacyApplicant = Applicant::query()
                    ->where('applicant_id', $loginIdentifier)
                    ->orWhere('applicant_id', $user->username)
                    ->first();

                if ($legacyApplicant) {
                    $user->applicant_id = $legacyApplicant->id;
                    $user->save();
                    $user->refresh();
                }
            }

            if (!$user || $user->module !== 'applicant' || !$this->hasRequiredRoleLink($user, 'applicant')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Applicant account is not linked yet. Please contact admissions.',
                ])->withInput($request->only('username', 'remember'));
            }

            // Ensure remember cookie is explicitly set for persistent login
            Auth::login($user, $remember);

            $request->session()->regenerate();

            return redirect()->route('applicant.application-form');
        }

        return back()->withErrors([
            'username' => 'Invalid applicant credentials.',
        ])->withInput($request->only('username', 'remember'));
    }
}
