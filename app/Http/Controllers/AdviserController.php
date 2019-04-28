<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use Illuminate\Http\Request;

class AdviserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function isAdviser()
    {
        $groupId = auth()->user()->group_id;
        abort_unless( $groupId == UserGroup::Adviser || $groupId == UserGroup::Director,
            403, "You must login under adviser/director account in order to use this API");
    }

    public function dashboard()
    {
        $this->isAdviser();

        return view('adviser.dashboard');
    }

    public function students()
    {
        $this->isAdviser();

        return auth()->user()->students;
    }

    public function messages()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));

        $adviser = auth()->user();

        $data = $adviser->messages($studentId, $adviser->id);

        if ($data === -1) {
            return response()->json(['error' => 'Adviser and students are not connected.'], 400);
        }

        return $data;
    }

    public function addMessage()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $message = request('message');

        $adviser = auth()->user();

        $adviser->addMessage($studentId, $adviser->id, $message);

        $data = $adviser->messages($studentId, $adviser->id);

        if ($data === -1) {
            return response()->json(['error' => 'Adviser and students are not connected.'], 400);
        }

        return $data;
    }

    public function getPublicNotes()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        return User::where('group_id', UserGroup::Student)->where('id', $studentId)->first()->publicNotes;
    }

    public function addPublicNote()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $note = request('note');

        $adviser = auth()->user();

        $adviser->addPublicNote($studentId, $note);

        return User::where('group_id', UserGroup::Student)->where('id', $studentId)->first()->publicNotes;
    }

    public function removePublicNote()
    {
        $this->isAdviser();

        $studentId = intval(request('student_id'));

        $student = User::where('group_id', UserGroup::Student)->where('id', $studentId)->first();
        if (!isset($student)) {
            return response()->json(['error' => 'Student does not exists.'], 400);
        }

        $noteId = intval(request('note_id'));

        if (!$student->removePublicNote($noteId)) {
            return response()->json(['error' => 'Public note does not exists.'], 400);
        }

        return $student->publicNotes;
    }

    public function getPrivateNotes()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        return User::where('group_id', UserGroup::Student)->where('id', $studentId)->first()->privateNotes;
    }

    public function addPrivateNote()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $note = request('note');

        $adviser = auth()->user();

        $adviser->addPrivateNote($studentId, $note);

        return User::where('group_id', UserGroup::Student)->where('id', $studentId)->first()->privateNotes;
    }

    public function removePrivateNote()
    {
        $this->isAdviser();

        $studentId = intval(request('student_id'));

        $student = User::where('group_id', UserGroup::Student)->where('id', $studentId)->first();
        if (!isset($student)) {
            return response()->json(['error' => 'Student does not exists.'], 400);
        }

        $noteId = intval(request('note_id'));

        if (!$student->removePrivateNote($noteId)) {
            return response()->json(['error' => 'Private note does not exists.'], 400);
        }

        return $student->privateNotes;
    }
}
