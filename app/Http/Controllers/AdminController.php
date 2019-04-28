<?php

namespace App\Http\Controllers;

use App\Imports\AdvisersImport;
use App\Imports\StudentAdviserRelationImport;
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
    }

    private function isAdmin()
    {
        $groupId = auth()->user()->group_id;
        abort_unless( $groupId == UserGroup::Admin,
            403, "You must login under admin account in order to use this API");
    }

    public function advisers()
    {
        $this->isAdmin();

        $advisers = User::where('group_id', UserGroup::Adviser)
            ->orWhere('group_id', UserGroup::Director)->get();

        return view('admin.advisers.list', compact('advisers'));
    }

    public function advisersUpload()
    {
        $this->isAdmin();

        $file = request()->file('advisers')->store('uploads');

        Excel::import(new AdvisersImport(), $file);

        //unlink($file); TODO: remove remove import file

        return back();
    }

    public function students()
    {
        $this->isAdmin();

        $students = User::where('group_id', UserGroup::Student)->get();

        return view('admin.students.list', compact('students'));
    }

    public function studentsUpload()
    {
        $this->isAdmin();

        $file = request()->file('students')->store('uploads');

        Excel::import(new StudentsImport(), $file);
        Excel::import(new StudentAdviserRelationImport(), $file);

        //unlink($file); TODO: remove import file

        return back();
    }
}
