<?php

namespace App\Http\Controllers;

use App\Imports\AdvisersImport;
use App\Imports\StudentAdviserRelationImport;
use App\Imports\StudentsImport;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        abort_unless( ($groupId == UserGroup::Admin || $groupId == UserGroup::Director),
            403, "You must login under admin or director account in order to use this API");
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

        return back();
    }

    public function main()
    {
        $this->isAdmin();

        return view('admin.main');
    }

    public function systemReset()
    {
        $this->isAdmin();

        auth()->logout();

        DB::table('jobs')->truncate();
        DB::table('message')->truncate();
        DB::table('adviser_advisee')->delete();
        DB::table('user')->delete();

        return redirect()->to(env('APP_URL', '/'));
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

        return back();
    }

    public function studentRemove()
    {
        $this->isAdmin();

        $studentId = request('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Specify student_id'], 400);
        }

        try {
            return auth()->user()->studentRemove($studentId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }
}
