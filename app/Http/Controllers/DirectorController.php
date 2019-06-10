<?php

namespace App\Http\Controllers;

use App\AdviserAdvisee;
use App\Faculty;
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

        $students = User::whereIn('group_id', [UserGroup::Student, UserGroup::Inactive])
            ->with('faculty', 'reservation', 'adviser', 'group')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($students); $i++) {
            if (isset($students[$i]['adviser'][0])) {
                $students[$i]['adviser'] = $students[$i]['adviser'][0];
            } else {
                $students[$i]['adviser'] = null;
            }
        }

        return $students;
    }

    public function updateStudent()
    {
        $this->isDirector();

        $studentId = intval(request('student_id'));

        $student = User::whereIn('group_id', [UserGroup::Student, UserGroup::Inactive])
            ->where('id', $studentId)
            ->with('faculty', 'reservation', 'adviser', 'publicNotes')
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student does not exist.'], 400);
        }

        $email = request('email');
        if (isset($email)) {
            $student->email = $email;
        }

        $au_id = request('au_id');
        if (isset($au_id)) {
            $student->au_id = $au_id;
        }

        $name = request('name');
        if (isset($name)) {
            $student->name = $name;
        }

        $faculty_id = request('faculty_id');
        if (isset($faculty_id)) {
            $faculty = Faculty::where('id', $faculty_id)->first();
            if ($faculty) {
                $student->faculty_id = $faculty->id;
            }
        }

        $student->save();

        $student = $student->toArray();

        if (isset($student['adviser'][0])) {
            $student['adviser'] = $student['adviser'][0];
        } else {
            $student['adviser'] = null;
        }

        return $student;
    }

    public function student()
    {
        $this->isDirector();

        $studentId = intval(request('studentId'));

        $student = User::where('group_id', UserGroup::Student)
            ->where('id', $studentId)
            ->with('faculty', 'reservation', 'adviser', 'publicNotes')
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student does not exist.'], 400);
        }

        $student = $student->toArray();

        if (isset($student['adviser'][0])) {
            $student['adviser'] = $student['adviser'][0];
        } else {
            $student['adviser'] = null;
        }

        return $student;
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
            return response()->json(['error' => 'Adviser does not exist.'], 400);
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

        try {
            auth()->user()->dismissStudents($studentIds);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }

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
            return response()->json(['error' => 'Period does not exist.'], 400);
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

    public function reportForPeriod()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriod($periodId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function reportForPeriodReserved()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriodReserved($periodId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function reportForPeriodUnreserved()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriodUnreserved($periodId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function reportForPeriodAttended()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriodAttended($periodId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function reportForPeriodCancelled()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriodCancelled($periodId);
        } catch (\Exception $message) {
            return response()->json(['error' => $message->getMessage()], 400);
        }
    }

    public function reportForPeriodMissed()
    {
        $this->isDirector();

        $periodId = request('periodId');

        if (!$periodId) {
            return response()->json(['error' => 'Specify periodId.'], 400);
        }

        try {
            return auth()->user()->getDirectorStatsForPeriodMissed($periodId);
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
