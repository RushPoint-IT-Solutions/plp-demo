<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Where to redirect users after successful login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        switch ($user->module) {
            case 'registrar':
                return redirect()->route('registrar.dashboard');
            case 'faculty':
                return redirect()->route('faculty.load');
            case 'applicant':
                return redirect()->route('applicant.application-form');
            case 'student':
            default:
                return redirect()->route('student.access-module');
        }
    }

    /**
     * Log the user out of the application and redirect by access module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $module = $user ? (string) $user->module : '';

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($module === 'student' || $module === 'applicant') {
            return redirect()->route('access-module');
        }

        return redirect()->route('admin.access-module');
    }

    /**
     * Show the application's login form.
     * Defaults to 'student' module if accessed via /login directly.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $module = request()->get('module', 'student');
        return view('auth.login', ['module' => $module]);
    }
}
