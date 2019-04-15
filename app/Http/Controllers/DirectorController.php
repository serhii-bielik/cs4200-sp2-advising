<?php

namespace App\Http\Controllers;

use App\Faculty;
use App\User;
use App\UserGroup;

class DirectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // TODO: Check group id
        //abort_unless(auth()->user()->group_id == UserGroup::Admin, 403);
    }

    public function faculties()
    {
        return Faculty::all();
    }

    public function students()
    {
        return User::where('group_id', UserGroup::Student)->get();
    }

    public function advisers()
    {
        return User::where('group_id', UserGroup::Adviser)
            ->orWhere('group_id', UserGroup::Director)->get();
    }

    public function assign()
    {
        //TODO: Validation
        $adviserId = intval(request('data.adviser'));
        $adviser = User::where('id', $adviserId)->first();
        if(!isset($adviser)) {
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

        $adviser->addStudents(request('data.students'), $adviserId);

        return $adviser->students;
    }

    public function dismiss()
    {
        //TODO: Validation
        $adviserId = intval(request('data.adviser'));
        $adviser = User::where('id', $adviserId)->first();
        if(!isset($adviser)) {
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

        $adviser->dismissStudents(request('data.students'), $adviserId);

        return $adviser->students;
    }
}
