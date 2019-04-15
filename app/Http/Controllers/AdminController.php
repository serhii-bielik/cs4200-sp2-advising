<?php

namespace App\Http\Controllers;

use App\Imports\AdvisersImport;
use App\Imports\StudentsImport;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // TODO: Check group id
        //abort_unless(auth()->user()->group_id == UserGroup::Admin, 403);
    }

    public function advisers()
    {
        $advisers = User::where('group_id', UserGroup::Adviser)
            ->orWhere('group_id', UserGroup::Director)->get();

        return view('admin.advisers.list', compact('advisers'));
    }

    public function advisersUpload()
    {
        $file = request()->file('advisers')->store('uploads');

        Excel::import(new AdvisersImport(), $file);

        //unlink($file); TODO: remove

        return back();
    }

    public function students()
    {
        $students = User::where('group_id', UserGroup::Student)->get();

        return view('admin.students.list', compact('students'));
    }

    public function studentsUpload()
    {
        $file = request()->file('students')->store('uploads');

        Excel::import(new StudentsImport(), $file);

        //unlink($file); TODO: remove

        return back();
    }
}
