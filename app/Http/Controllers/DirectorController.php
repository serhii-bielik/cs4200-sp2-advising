<?php

namespace App\Http\Controllers;

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

        return User::where('group_id', UserGroup::Student)->with('faculty')->get();
    }

    public function unassignedStudents()
    {
        $this->isDirector();

        return User::where('group_id', UserGroup::Student)->
        doesntHave('studentAdviser')->get();
    }

    public function advisers()
    {
        $this->isDirector();

        return User::where('group_id', UserGroup::Adviser)
            ->orWhere('group_id', UserGroup::Director)->get();
    }

    public function assign()
    {
        $this->isDirector();

        //TODO: Validation
        $adviserId = intval(request('adviserId'));
        $adviser = User::where('id', $adviserId)->first();
        if(!isset($adviser)) {
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

        $adviser->addStudents(request('studentIds'), $adviserId);

        return $adviser->students;
    }

    public function dismiss()
    {
        $this->isDirector();

        //TODO: Validation
        $adviserId = intval(request('adviserId'));
        $adviser = User::where('id', $adviserId)->first();
        if(!isset($adviser)) {
            return response()->json(['error' => 'Adviser does not exists.'], 400);
        }

        $adviser->dismissStudents(request('studentIds'), $adviserId);

        return $adviser->students;
    }

    public function periods()
    {
        $this->isDirector();

        return Period::get();
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
}
