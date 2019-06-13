<?php

namespace App\Http\Controllers;

use App\Faculty;
use App\Group;
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
            ->with('faculty', 'group')
            ->orWhere('group_id', UserGroup::Director)->get();

        $faculties = Faculty::all();
        $groups = Group::whereNotIn('id', [UserGroup::Admin, UserGroup::Inactive, UserGroup::Student])
                    ->get();

        return view('admin.advisers.list', compact('advisers', 'faculties', 'groups'));
    }

    public function userData()
    {
        $this->isAdmin();

        $userId = request('userId');

        if (!$userId) {
            return response()->json(['error' => 'Specify student_id'], 400);
        }

        $user = User::where('id', $userId)
            ->select('id', 'name', 'au_id', 'email', 'group_id', 'faculty_id')
            ->where('group_id', '<>', UserGroup::Admin)
            ->first();

        if (!$userId) {
            return response()->json(['error' => 'User does not found'], 400);
        }

        return $user;
    }

    public function updateUserData()
    {
        $this->isAdmin();

        $id = request('id');
        if (!$id) {
            return response()->json(['error' => 'Specify id'], 400);
        }
        $name = request('name');
        if (!$name) {
            return response()->json(['error' => 'Specify name'], 400);
        }
        $au_id = request('au_id');
        if (!$au_id) {
            return response()->json(['error' => 'Specify au_id'], 400);
        }
        $email = request('email');
        if (!$email) {
            return response()->json(['error' => 'Specify email'], 400);
        }
        $group_id = request('group_id');
        if (!$group_id) {
            return response()->json(['error' => 'Specify group_id'], 400);
        }
        if ($group_id == UserGroup::Admin) {
            return response()->json(['error' => 'Wrong group_id'], 400);
        }
        $faculty_id = request('faculty_id');
        if (!$faculty_id) {
            return response()->json(['error' => 'Specify faculty_id'], 400);
        }
        $faculty = Faculty::where('id', $faculty_id)->first();
        if (!$faculty) {
            return response()->json(['error' => 'Wrong faculty_id'], 400);
        }

        $user = User::where('id', $id)
            ->where('group_id', '<>', UserGroup::Admin)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User does not found'], 400);
        }

        $user->au_id = $au_id;
        $user->name = $name;
        $user->email = $email;
        $user->group_id = $group_id;
        $user->faculty_id = $faculty_id;
        $user->save();

        return ['status' => 'success',
            'message' => 'User has been updated.'];
    }

    public function advisersUpload()
    {
        $this->isAdmin();

        $file = request()->file('advisers')->store('uploads');

        Excel::import(new AdvisersImport(), $file);

        return back();
    }

    public function system()
    {
        $this->isAdmin();

        return view('admin.system');
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

        $students = User::whereIn('group_id', [UserGroup::Student, UserGroup::Inactive])->get();

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

    public function removeUser()
    {
        $this->isAdmin();

        $userId = request('user_id');

        if (!$userId) {
            return response()->json(['error' => 'Specify user_id'], 400);
        }

        try {

            $user = User::where('id', $userId)
                ->where('group_id', '<>', UserGroup::Admin)->first();

            if (!$user) {
                throw new \Exception("User #$userId does not exists.");
            }

            $user->delete();

            return ['status' => 'success',
                'message' => 'User has been removed.'];

        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function studentSuspend()
    {
        $this->isAdmin();

        $studentId = request('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Specify student_id'], 400);
        }

        try {
            return auth()->user()->studentSuspend($studentId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function studentActivate()
    {
        $this->isAdmin();

        $studentId = request('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Specify student_id'], 400);
        }

        try {
            return auth()->user()->studentActivate($studentId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }
}
