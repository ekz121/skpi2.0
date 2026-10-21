<?php

namespace App\Http\Controllers;

class StaffController extends Controller
{
    public function dashboard()
    {
        return view('staff.dashboard');
    }
}
