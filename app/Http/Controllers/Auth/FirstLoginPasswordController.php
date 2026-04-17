<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FirstLoginPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        // If not forced to reset, go to home
        if (!Auth::user()->force_password_reset) {
            return redirect('/home');
        }

        return view('auth.first_login_reset');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->force_password_reset = false;
        $user->save();

        // Redirect based on module
        switch ($user->module) {
            case 'registrar':
                return redirect()->route('registrar.dashboard')->with('status', 'Password changed successfully!');
            case 'faculty':
                return redirect()->route('faculty.load')->with('status', 'Password changed successfully!');
            case 'applicant':
                return redirect()->route('applicant.application-form')->with('status', 'Password changed successfully!');
            case 'parent':
                return redirect()->route('parent.dashboard')->with('status', 'Password changed successfully!');
            case 'student':
            default:
                return redirect()->route('student.grades')->with('status', 'Password changed successfully!');
        }
    }
}
