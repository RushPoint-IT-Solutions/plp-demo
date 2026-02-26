<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
     * @param string $module  e.g. 'registrar', 'accounting', 'cashier', 'faculty'
     */
    public function moduleLogin($module)
    {
        return view('auth.login', ['module' => $module]);
    }
}
