<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use Illuminate\Http\Request;

class AdviserController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }

    private function isAdviser()
    {
//        $groupId = auth()->user()->group_id;
//        abort_unless( $groupId == UserGroup::Adviser || $groupId == UserGroup::Director,
//            403, "You must login under adviser/director account in order to use this API");
    }

    public function dashboard()
    {
        $this->isAdviser();

        return view('adviser.dashboard');
    }

    public function students()
    {
        $this->isAdviser();

        $userId = request('userId');
        if (isset($userId)) {
            $user = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (isset($user)) {
                return $user->students;
            }
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

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

    public function settings()
    {
        $settings = [];

        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

        $settings['phone'] = $adviser->phone;
        $settings['office'] = $adviser->office;
        $settings['isNotification'] = $adviser->is_notification;
        $settings['interval'] = $adviser->interval;

        return $settings;
    }

    public function setSettings()
    {
        $settings = [];

        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

        $phone = request('phone');
        if (isset($phone)) {
            $adviser->phone = $phone;
        }
        $office = request('office');
        if (isset($office)) {
            $adviser->office = $office;
        }
        $isNotification = request('isNotification');
        if (isset($isNotification)) {
            $adviser->is_notification = $isNotification;
        }
        $interval = request('interval');
        if (isset($interval)) {
            $adviser->interval = $interval;
        }
        $adviser->save();

        $settings['phone'] = $adviser->phone;
        $settings['office'] = $adviser->office;
        $settings['isNotification'] = $adviser->is_notification;
        $settings['interval'] = $adviser->interval;

        return $settings;
    }

    public function messages()
    {
        $this->isAdviser();

        $studentId = intval(request('studentId'));
        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

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
        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

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
        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

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
        $userId = request('userId');

        if (isset($userId)) {
            $adviser = User::where('id', intval($userId))
                ->whereBetween('group_id', [UserGroup::Adviser, UserGroup::Director])
                ->first();
            if (!isset($adviser)) {
                return response()->json(['error' => 'Adviser does not exists.'], 400);
            }
        } else {
            $adviser = auth()->user();
        }

        $adviser->addPrivateNote($studentId, $note);

        return User::where('id', $studentId)->first()->privateNotes;
    }
}
