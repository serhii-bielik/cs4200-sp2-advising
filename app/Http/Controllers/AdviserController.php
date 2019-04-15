<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdviserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // TODO: Check group id
        //abort_unless(auth()->user()->group_id == UserGroup::Admin, 403);
    }

    public function dashboard()
    {
        return view('adviser.dashboard');
    }

    public function students()
    {
        return auth()->user()->students;
    }
}
