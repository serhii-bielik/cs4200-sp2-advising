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
        return User::where('id', $studentId)->first()->publicNotes;
    }

    public function addPublicNote()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $note = request('note');

        $adviser = auth()->user();

        $adviser->addPublicNote($studentId, $note);

        return User::where('id', $studentId)->first()->publicNotes;
    }

    public function getPrivateNotes()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        return User::where('id', $studentId)->first()->privateNotes;
    }

    public function addPrivateNote()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $note = request('note');

        $adviser = auth()->user();

        $adviser->addPrivateNote($studentId, $note);

        return User::where('id', $studentId)->first()->privateNotes;
    }
}
