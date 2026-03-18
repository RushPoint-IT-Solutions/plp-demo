<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;

class ClassListController extends Controller
{
    public function index()
    {
        return view('registrar.services.classroom-faculty.class-list');
    }
}
