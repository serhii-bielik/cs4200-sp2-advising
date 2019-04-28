<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function isStudent()
    {
        abort_unless(auth()->user()->group_id == UserGroup::Student,
            403, "You must login under student account in order to use this API");
    }

    public function dashboard()
    {
        $this->isStudent();

        return view('student.dashboard');
    }

    public function note()
    {
        $this->isStudent();

        return auth()->user()->lastPublicNoteForStudent;
    }

    public function notes()
    {
        $this->isStudent();

        return auth()->user()->publicNotes;
    }

    public function adviser()
    {
        $this->isStudent();

        return auth()->user()->studentAdviser->first();
    }

    public function info()
    {
        $this->isStudent();

        return User::where('id', auth()->user()->id)->with('faculty', 'studentAdviser', 'lastPublicNoteForStudent')->first();
    }

    public function getNotification()
    {
        $this->isStudent();

        return auth()->user()->is_notification;
    }

    public function setNotification()
    {
        $this->isStudent();

        $student = auth()->user();
        $student->is_notification = intval(request('notification'));
        $student->save();
        return $student->is_notification;
    }

    public function messages()
    {
        $this->isStudent();

        $student = User::where('id', auth()->user()->id)->with('studentAdviser')->first();
        $data = $student->messages($student->id, $student->studentAdviser[0]->id);

        if ($data === -1) {
            return response()->json(['error' => 'Adviser and students are not connected.'], 400);
        }

        return $data;
    }

    public function addMessage()
    {
        $this->isStudent();

        $student = User::where('id', auth()->user()->id)->with('studentAdviser')->first();
        $student->addMessage($student->id, $student->studentAdviser[0]->id, request('message'));

        return $this->messages();
    }
}
