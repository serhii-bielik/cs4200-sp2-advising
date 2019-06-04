<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use DateTime;
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

    public function recentMessages()
    {
        $this->isStudent();

        $student = User::where('id', auth()->user()->id)->with('studentAdviser')->first();
        $data = $student->recentMessages($student->id, $student->studentAdviser[0]->id);

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

    public function timeslots()
    {
        $this->isStudent();

        return auth()->user()->getStudentTimeslots();
    }

    public function timeslotsByDate()
    {
        $this->isStudent();

        $date = DateTime::createFromFormat('Y-m-d', request('date'));
        if (!$date) {
            return response()->json(['error' => 'Invalid date format. Please use: Y-m-d'], 400);
        }

        return auth()->user()->getStudentTimeslotsForDate($date->format('Y-m-d'));
    }

    public function makeReservation()
    {
        $this->isStudent();

        $timeslotId = intval(request('timeslot_id'));

        if (!$timeslotId) {
            $date = DateTime::createFromFormat('Y-m-d', request('date'));
            if (!$date) {
                return response()->json(['error' => 'Invalid date format. Please use: Y-m-d'], 400);
            }

            $time = DateTime::createFromFormat('H:i', request('time'));
            if (!$time) {
                return response()->json(['error' => 'Invalid time format. Please use: H:i'], 400);
            }

            try {
                return auth()->user()->makeFlexibleReservation($date, $time);
            } catch (\Exception $message) {
                return response()->json(['error' => $message->getMessage()], 400);
            }
        } else {
            try {
                return auth()->user()->makeReservation($timeslotId);
            } catch (\Exception $message) {
                return response()->json(['error' => $message->getMessage()], 400);
            }
        }
    }

    public function cancelReservation()
    {
        $this->isStudent();

        $reservationId = intval(request('reservation_id'));

        try {
            return auth()->user()->cancelReservation($reservationId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function getReservation()
    {
        $this->isStudent();

        try {
            return auth()->user()->getStudentReservation();
        } catch (\Exception $message) {
            if (strpos($message->getMessage(), "already advised") !== false) {
                return response()->json(['error' => $message->getMessage(), 'type' => 'advised'], 400);
            }
            if (strpos($message->getMessage(), "don't have active reservation") !== false) {
                return response()->json(['error' => $message->getMessage(), 'type' => 'none'], 400);
            }
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }
}
