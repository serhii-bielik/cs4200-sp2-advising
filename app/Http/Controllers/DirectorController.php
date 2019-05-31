<?php

namespace App\Http\Controllers;

use App\AdviserAdvisee;
use App\Period;
use App\User;
use App\UserGroup;
use DateTime;

class DirectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function isDirector()
    {
        abort_unless(auth()->user()->group_id == UserGroup::Director,
            403, "You must login under director account in order to use this API");
    }

    public function students()
    {
        $this->isDirector();

        return User::where('group_id', UserGroup::Student)
            ->with('faculty')
            ->get();
    }

    private function getUnassignedStudents()
    {
        return User::where('group_id', UserGroup::Student)
            ->with('faculty')
            ->doesntHave('studentAdviser')
            ->get();
    }

    public function getAssignedStudents()
    {
        $students = User::where('group_id', UserGroup::Student)
            ->with('adviser', 'faculty')
            ->has('adviser')
            ->get()
            ->toArray();
        for ($i = 0; $i < count($students); $i++) {
            $students[$i]['adviser'] = $students[$i]['adviser'][0];
        }

        return $students;
    }

    public function unassignedStudents()
    {
        $this->isDirector();

        return $this->getUnassignedStudents();
    }

    public function assignedStudents()
    {
        $this->isDirector();

        return $this->getAssignedStudents();
    }

    public function advisers()
    {
        $this->isDirector();

        return User::where('group_id', UserGroup::Adviser)
            ->orWhere('group_id', UserGroup::Director)
            ->withCount('students')
            ->get();
    }

    public function assign()
    {
        $this->isDirector();

        $adviserId = intval(request('adviserId'));
        $adviser = User::where('id', $adviserId)
            ->whereIn('group_id', [UserGroup::Adviser, UserGroup::Director])
            ->first();
        if(!isset($adviser)) {
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

        $studentIds = request('studentIds');
        if (!count($studentIds)) {
            return response()->json(['error' => 'Specify studentIds to assign'], 400);
        }

        try {
            auth()->user()->addStudents($studentIds, $adviserId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }

        return $this->getUnassignedStudents();
    }

    public function dismiss()
    {
        $this->isDirector();

        $studentIds = request('studentIds');

        if (!count($studentIds)) {
            return response()->json(['error' => 'Specify studentIds'], 400);
        }

        AdviserAdvisee::whereIn('advisee_id', $studentIds)->delete();

        return $this->getUnassignedStudents();
    }

    public function periods()
    {
        $this->isDirector();

        return Period::with('director')->get();
    }

    public function addPeriod()
    {
        $this->isDirector();

        $director = auth()->user();

        $startDate = DateTime::createFromFormat('Y-m-d', request('startDate'));
        if (!$startDate) {
            return response()->json(['error' => 'Invalid startDate format. Please use: Y-m-d'], 400);
        }
        $endDate = DateTime::createFromFormat('Y-m-d', request('endDate'));
        if (!$endDate) {
            return response()->json(['error' => 'Invalid endDate format. Please use: Y-m-d'], 400);
        }

        if ($endDate <= $startDate) {
            return response()->json(['error' => 'startDate can not be greater than endDate'], 400);
        }

        try {
            $director->addPeriod($startDate, $endDate);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return Period::get();
    }

    public function removePeriod()
    {
        $this->isDirector();

        $director = auth()->user();
        $periodId = request('id');

        if (!$director->removePeriod($periodId)) {
            return response()->json(['error' => 'Period does not exists.'], 400);
        }

        return Period::get();
    }

    public function report()
    {
        $this->isDirector();

        try {
            return auth()->user()->getDirectorStats();
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function adviserData()
    {
        $this->isDirector();

        $adviserId = intval(request('adviserId'));

        try {
            return auth()->user()->getAdviserData($adviserId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function notifyPeriod()
    {
        $this->isDirector();

        try {
            return auth()->user()->directorNotifyPeriod();
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function notifyPeriodStatus()
    {
        $this->isDirector();

        try {
            return auth()->user()->directorNotifyPeriodStatus();
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }
}
