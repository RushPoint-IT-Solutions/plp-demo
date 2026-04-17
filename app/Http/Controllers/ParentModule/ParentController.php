<?php

namespace App\Http\Controllers\ParentModule;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $parent = null;
        $linkedStudents = collect();

        if ($user) {
            $parent = $user->parentProfile;

            if ($parent) {
                $linkedStudents = $parent->studentLinks()
                    ->with(['student', 'relationshipType'])
                    ->orderByDesc('is_primary_contact')
                    ->orderBy('id')
                    ->get();
            }
        }

        return view('parent.dashboard', [
            'parent' => $parent,
            'linkedStudents' => $linkedStudents,
            'user' => $user,
        ]);
    }
}
