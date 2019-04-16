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

    public function getInterval()
    {
        $this->isAdviser();

        return auth()->user()->interval;
    }

    public function setInterval()
    {
        $this->isAdviser();

        $adviser = auth()->user();
        $adviser->interval = intval(request('interval'));
        $adviser->save();
        return $adviser->interval;
    }

    public function getNotification()
    {
        $this->isAdviser();

        return auth()->user()->is_notification;
    }

    public function setNotification()
    {
        $this->isAdviser();

        $adviser = auth()->user();
        $adviser->is_notification = intval(request('notification'));
        $adviser->save();
        return $adviser->is_notification;
    }

    public function messages()
    {
        $this->isAdviser();

        $adviser = auth()->user();
        $studentId = intval(request('studentID'));

        $data = $adviser->messages($studentId, $adviser->id);

        if ($data === -1) {
            return response()->json(['error' => 'Adviser and students are not connected.'], 400);
        }

        return $data;
    }

    public function addMessage()
    {
        $this->isAdviser();

        $adviser = auth()->user();
        $studentId = intval(request('data.studentId'));
        $message = request('data.message');

        $adviser->addMessage($studentId, $adviser->id, $message);

        $data = $adviser->messages($studentId, $adviser->id);

        if ($data === -1) {
            return response()->json(['error' => 'Adviser and students are not connected.'], 400);
        }

        return $data;
    }
}
