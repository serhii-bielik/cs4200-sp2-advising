<?php

namespace App;

use App\Notifications\AccountActivated;
use App\Notifications\AccountSuspended;
use App\Notifications\AdviserCancelledReservation;
use App\Notifications\AdviserConfirmedReservation;
use App\Notifications\AdviserGotNewStudent;
use App\Notifications\AdvisingPeriodCreated;
use App\Notifications\AdvisingTimeslotsCreated;
use App\Notifications\GoogleCalendarEvent;
use App\Notifications\GoogleCalendarManager;
use App\Notifications\StudentAccountActivated;
use App\Notifications\StudentAccountSuspended;
use App\Notifications\StudentAssignedToAdviser;
use App\Notifications\StudentCancelledReservation;
use App\Notifications\StudentDismissed;
use App\Notifications\StudentRemoved;
use App\Notifications\StudentMadeFlexibleReservation;
use App\Notifications\StudentMadeReservation;
use App\Notifications\StudentMissedReservation;
use App\Structures\ReservationStatuses;
use DateInterval;
use DateTime;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'au_id', 'group_id', 'faculty_id', 'gpa', 'credits', 'phone', 'interval', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'google_id', 'token', 'refresh_token', 'expires_in',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function addStudents(Array $students, $adviserId)
    {
        $adviser = User::where('id', $adviserId)
            ->whereIn('group_id', [UserGroup::Adviser, UserGroup::Director])->first();

        if (!$adviser) {
            throw new \Exception("Adviser #$adviserId does not exists.");
        }

        foreach ($students as $studentId) {

            $isAssigned = AdviserAdvisee::where('adviser_id', $adviserId)
                ->where('advisee_id', $studentId)->first();

            if (!$isAssigned) {

                $student = User::where('id', $studentId)
                    ->where('group_id', UserGroup::Student)->first();
                if (!$student) {
                    throw new \Exception("Student #$studentId does not exists.");
                }

                $currentAdviser = $student->adviser;
                if (count($currentAdviser)) {
                    $currentAdviser[0]->notify(new StudentDismissed($student->name,
                        $currentAdviser[0]->name, $currentAdviser[0]->cc_email));
                }

                AdviserAdvisee::where('advisee_id', $studentId)->delete();

                $adviser->notify(new AdviserGotNewStudent($student->name, $adviser->name, $adviser->cc_email));
                $student->notify(new StudentAssignedToAdviser($student->name, $adviser->name, $student->cc_email));

                AdviserAdvisee::create([
                   'adviser_id' => $adviserId,
                   'advisee_id' => $studentId,
                   'director_id' => $this->id,
                ]);
            }
        }
    }

    public function dismissStudents($studentIds)
    {
        foreach ($studentIds as $studentId) {

            $student = User::where('id', $studentId)
                ->where('group_id', UserGroup::Student)->first();

            if (!$student) {
                throw new \Exception("Student #$studentId does not exists.");
            }

            $currentAdviser = $student->adviser;
            if (count($currentAdviser)) {
                $currentAdviser[0]->notify(new StudentDismissed($student->name,
                    $currentAdviser[0]->name, $currentAdviser[0]->cc_email));
            }

            AdviserAdvisee::where('advisee_id', $studentId)->delete();
        }
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id', 'id');
    }

    public function faculty()
    {
        return $this->belongsTo('App\Faculty', 'faculty_id', 'id');
    }

    public function reservation()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();

        if ($lastPeriod) {
            return $this->hasOne('App\Reservation', 'advisee_id', 'id')
                ->with('status', 'timeslot')
                ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Missed, ReservationStatuses::Unconfirmed])
                ->whereHas('timeslot', function ($query) use ($lastPeriod) {
                    $query->where('period_id', '=', $lastPeriod->id);
                })
                ->orderByDesc('created_at');
        } else {
            return $this->hasOne('App\Reservation', 'advisee_id', 'id')
                ->with('status', 'timeslot')
                ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Missed, ReservationStatuses::Unconfirmed])
                ->orderByDesc('created_at');
        }
    }

    public function students()
    {
        return $this->belongsToMany('App\User', 'adviser_advisee', 'adviser_id', 'advisee_id')
            ->with('faculty', 'reservation');
    }

    public function lastPublicNoteForStudent()
    {
        return $this->hasMany('App\PublicNote', 'advisee_id')->orderByDesc('id')->limit(1);
    }

    public function publicNotes()
    {
        return $this->hasMany('App\PublicNote', 'advisee_id')->orderByDesc('id');
    }

    public function isStudent()
    {
        return $this->group_id == UserGroup::Student;
    }

    public function addPublicNote($studentId, $note)
    {
        PublicNote::create([
            'advisee_id' => $studentId,
            'created_by' => $this->id,
            'note' => $note,
        ]);
    }

    public function removePublicNote($noteId)
    {
        $note = PublicNote::where('id', $noteId)->first();
        if (isset($note)) {
            $note->delete();
            return true;
        }
        return false;
    }

    public function removePrivateNote($noteId)
    {
        $note = PrivateNote::where('id', $noteId)->first();
        if (isset($note)) {
            $note->delete();
            return true;
        }
        return false;
    }

    public function addPrivateNote($studentId, $note)
    {
        PrivateNote::create([
            'advisee_id' => $studentId,
            'created_by' => $this->id,
            'note' => $note,
        ]);
    }

    public function privateNotes()
    {
        return $this->hasMany('App\PrivateNote', 'advisee_id')->orderByDesc('id');
    }

    public function studentAdviser()
    {
        return $this->belongsToMany('App\User', 'adviser_advisee', 'advisee_id', 'adviser_id')
            ->select('user.id', 'user.au_id', 'user.name', 'user.email', 'user.phone', 'user.office', 'user.interval', 'user.avatar', 'user.is_notification', 'user.is_allow_flexible_timeslots')
            ->orderByDesc('adviser_advisee.id')->limit(1);
    }

    public function adviser()
    {
        return $this->belongsToMany('App\User', 'adviser_advisee', 'advisee_id', 'adviser_id', 'id')
            ->select('user.id', 'user.au_id', 'user.name', 'user.email', 'user.phone', 'user.office')
            ->orderByDesc('adviser_advisee.id');
    }

    private function getAdviserAdviseeChatId($studentId, $adviserId)
    {
        $chat = AdviserAdvisee::where('adviser_id', $adviserId)->where('advisee_id', $studentId)
            ->orderByDesc('id')->first();

        if (!isset($chat)) {
            return null;
        }

        return $chat->id;
    }

    public function messages($studentId, $adviserId)
    {
        $chatId = $this->getAdviserAdviseeChatId($studentId, $adviserId);

        if ($chatId === null) {
            return -1;
        }

        return Message::where('chat_id', $chatId)->orderBy('id')->get();
    }

    public function recentMessages($studentId, $adviserId)
    {
        $chatId = $this->getAdviserAdviseeChatId($studentId, $adviserId);

        if ($chatId === null) {
            return -1;
        }

        return Message::where('chat_id', $chatId)
            ->where('sender_id', '<>', $studentId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function addMessage($studentId, $adviserId, $message)
    {
        $chatId = $this->getAdviserAdviseeChatId($studentId, $adviserId);

        if ($chatId === null) {
            return -1;
        }

        Message::create([
            'chat_id' => $chatId,
            'sender_id' => $this->id,
            'content' => $message,
        ]);
    }

    public function removePeriod($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (isset($period)) {
            $period->delete();
            return true;
        }
        return false;
    }

    public function addPeriod($startDate, $endDate)
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();

        if ($lastPeriod) {
            $lastPeriodEndDate = DateTime::createFromFormat('Y-m-d', $lastPeriod->end_date);

            if ($startDate <= $lastPeriodEndDate) {
                throw new \Exception('The new period can not be earlier than the last day of the latest period ' .
                    "($lastPeriod->start_date - $lastPeriod->end_date)");
            }
        }

        $year = intval($startDate->format('Y'));
        $semester = 1;

        $month = intval($startDate->format('n'));
        if ($month >= 1 && $month <= 5) {
            $semester = 2;
            $year--;
        } else if ($month >= 6 && $month <= 7) {
            $semester = 3;
            $year--;
        }

        DB::table('notifications')->truncate();

        Period::create([
            'director_id' => $this->id,
            'semester' => $semester,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate
        ]);
    }

    public function getTimeslotsForLastPeriod()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();

        $timeslots = Timeslot::select('date')
            ->where('adviser_id', $this->id)
            ->where('period_id', $lastPeriod->id)
            ->groupBy('date')
            ->get();

        return $timeslots;
    }

    public function getTimeslotsForDate($date)
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();

        $timeslots = Timeslot::where('adviser_id', $this->id)
            ->where('period_id', $lastPeriod->id)
            ->where('date', $date)
            ->orderby('time')
            ->with('activeReservation')
            ->get();

        return $timeslots;
    }

    public function addTimeslotForDate($date, $time)
    {
        if ($date < new DateTime()) {
            throw new \Exception("You cannot add timeslots to the past");
        }

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();

        $minDate = $lastPeriod->start_date;
        $maxDate = $lastPeriod->end_date;

        if ($date < DateTime::createFromFormat('Y-m-d', $minDate) ||
            $date > DateTime::createFromFormat('Y-m-d', $maxDate)) {
            throw new \Exception("Current date is outside current advising period ($lastPeriod->start_date - $lastPeriod->end_date)");
        }

        return Timeslot::create([
            'adviser_id' => $this->id,
            'period_id' => $lastPeriod->id,
            'date' => $date->format('Y-m-d'),
            'time' => $time->format('H:i:s'),
        ]);
    }

    public function getStudentTimeslots()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        $adviser = $this->studentAdviser;

        if (!$adviser || !$lastPeriod) {
            return [];
        }

        $timeslots = Timeslot::select('date')
            ->where('adviser_id', $adviser[0]->id)
            ->where('period_id', $lastPeriod->id)
            ->groupBy('date')
            ->get();

        return $timeslots;
    }

    public function getStudentTimeslotsForDate($date)
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        $adviser = $this->studentAdviser;

        if (!$adviser || !$lastPeriod) {
            return [];
        }

        $timeslots = Timeslot::select('id', 'time')->where('adviser_id', $adviser[0]->id)
            ->where('period_id', $lastPeriod->id)
            ->where('date', $date)
            ->with('isReserved')
            ->get();

        return $timeslots;
    }

    public function removeTimeslotById($timeslotId)
    {
        $timeslot = Timeslot::where('id', $timeslotId)
            ->where('adviser_id', $this->id)
            ->first();

        if (!$timeslot) {
            throw new \Exception("Timeslot was not found in the system");
        }

        if (Carbon::createFromFormat('Y-m-d', $timeslot->date) < new DateTime()) {
            throw new \Exception("You cannot modify timeslots in the past");
        }

        $timeslot->delete();

        return ['status' => 'success'];
    }

    public function updateTimeslotsForDate($dateRaw, $timeslotsRaw)
    {
        if ($dateRaw < new DateTime()) {
            throw new \Exception("You cannot modify timeslots in the past");
        }

        $date = $dateRaw->format('Y-m-d');
        $timeslots = [];

        foreach ($timeslotsRaw as $time) {
            $t = DateTime::createFromFormat('H:i', $time);
            if ($t) {
                $timeslots[] = $t->format('H:i');
            }
        }

        Timeslot::where('date', $date)
            ->whereNotIn('time', $timeslots)
            ->delete();

        foreach ($timeslots as $time) {
            $t = Timeslot::where('date', $date)
                ->where('time', $time)
                ->first();
            if(!$t) {
                $this->addTimeslotForDate($dateRaw, DateTime::createFromFormat('H:i', $time));
            }
        }

        return $this->getTimeslotsForDate($date);
    }

    public function makeFlexibleReservation($date, $time)
    {
        if ($date < (new DateTime())->add(new DateInterval('P1D'))) {
            throw new \Exception("You can make reservation at least 1 day before selected appointment");
        }

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception("Advising period is not yet created");
        }

        $adviser = $this->studentAdviser->first();

        if (!$adviser) {
            throw new \Exception("You do not have adviser yet");
        }

        if (!$adviser->is_allow_flexible_timeslots) {
            throw new \Exception("Your adviser does not allow flexible reservation");
        }

        $reservation = $this->getCurrentReservation($lastPeriod->id, $adviser->id);
        if ($reservation) {
            if ($reservation->status_id == ReservationStatuses::Booked ||
                $reservation->status_id == ReservationStatuses::Unconfirmed) {
                throw new \Exception("You already have active reservation in this advising period.");
            } else if ($reservation->status_id == ReservationStatuses::Advised) {
                throw new \Exception("You already advised in this advising period.");
            }
        }

        $timeslot = Timeslot::where('adviser_id', $adviser->id)
            ->where('period_id', $lastPeriod->id)
            ->where('date', $date->format('Y-m-d'))
            ->where('time', $time->format('H:i:s'))
            ->first();

        if(!$timeslot) {
            $timeslot = $adviser->addTimeslotForDate($date, $time);
        }

        return $this->makeReservation($timeslot->id, true);
    }

    public function makeReservation($timeslotId, $isUnconfirmed = false)
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception("Advising period is not yet created");
        }

        $adviser = $this->studentAdviser;
        if (!$adviser) {
            throw new \Exception("You do not have adviser yet");
        }

        $reservation = $this->getCurrentReservation($lastPeriod->id, $adviser[0]->id);
        if ($reservation) {
            if ($reservation->status_id == ReservationStatuses::Booked ||
                $reservation->status_id == ReservationStatuses::Unconfirmed) {
                throw new \Exception("You already have active reservation in this advising period.");
            } else if ($reservation->status_id == ReservationStatuses::Advised) {
                throw new \Exception("You already advised in this advising period.");
            }
        }

        $timeslot = Timeslot::where('id', $timeslotId)
            ->where('adviser_id', $adviser[0]->id)
            ->where('period_id', $lastPeriod->id)
            ->first();

        if (!$timeslot) {
            throw new \Exception("Timeslot was not found in the system");
        }

        if (Carbon::createFromFormat('Y-m-d', $timeslot->date) < (new DateTime())->add(new DateInterval('P1D'))) {
            throw new \Exception("You can make reservation at least 1 day before selected appointment");
        }

        if ($timeslot->isReserved) {
            throw new \Exception("Timeslot is reserved already");
        }

        $newReservation = $timeslot->makeReservation($this->id, $isUnconfirmed);

        if ($adviser[0]->is_notification) {
            if ($isUnconfirmed) {
                $adviser[0]->notify(new StudentMadeFlexibleReservation($timeslot, $this->name,
                    $adviser[0]->name, $adviser[0]->cc_email));
            } else {
                $adviser[0]->notify(new StudentMadeReservation($timeslot, $this->name,
                    $adviser[0]->name, $adviser[0]->cc_email));
            }
        }

        $calendar = new GoogleCalendarManager($this);

        $startTime = Carbon::parse("$timeslot->date $timeslot->time");
        $endTime = Carbon::parse("$timeslot->date $timeslot->time")->addMinutes($adviser[0]->interval);

        $event = $calendar->addEvent(new GoogleCalendarEvent(
            "Advising for $this->name", "You are required to meet with your adviser {$adviser[0]->name} twice per semester.",
            $startTime->toRfc3339String(), $endTime->toRfc3339String(), $adviser[0]->office, $this->email, $adviser[0]->email
        ));

        if ($event) {
            $newReservation->calendar_id = $event->getId();
            $newReservation->save();
        }

        return $newReservation;
    }

    public function getCurrentReservation($lastPeriodId, $adviserId)
    {
        return Reservation::where('advisee_id', $this->id)
            ->whereIn('timeslot_id', Timeslot::select('id')
                                    ->where('period_id', $lastPeriodId)
                                    ->where('adviser_id', $adviserId)
                                    ->whereIn('status_id',
                                        [ReservationStatuses::Advised, ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->first();
    }

    public function cancelReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);
        $timeslot = $reservation->timeslot;

        if ($this->group_id == UserGroup::Student) {
            if ($reservation->status_id == ReservationStatuses::Booked) {
                $timeslotDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$timeslot->date $timeslot->time");
                $nowDateTime = new DateTime();
                $minTime = config('app.restrictReservationCancellationTime');

                if ($timeslotDateTime->getTimestamp() - $nowDateTime->getTimestamp() < $minTime) {
                    throw new \Exception("Too late to cancel this reservation.");
                }
            }
        }

        $isRemoveTimeslot = $reservation->status_id == ReservationStatuses::Unconfirmed ? true : false;

        $reservation->cancel($this->id);

        $reservation->status;

        if ($this->group_id == UserGroup::Student) {

            $adviser = $this->studentAdviser[0];
            if ($adviser->is_notification) {
                $adviser->notify(new StudentCancelledReservation($timeslot->date, $timeslot->time, $this->name,
                    $adviser->name, $adviser->cc_email));
            }

            if ($reservation->calendar_id) {
                $calendar = new GoogleCalendarManager($this);
                $calendar->removeEvent($reservation->calendar_id);
            }

        } else {

            $student = $reservation->studentFull;

            if ($student->is_notification) {
                $student->notify(new AdviserCancelledReservation($timeslot->date, $timeslot->time,
                    $student->name, $student->cc_email));
            }

            if ($reservation->calendar_id) {
                $calendar = new GoogleCalendarManager($student);
                $calendar->removeEvent($reservation->calendar_id);
            }

        }

        if ($isRemoveTimeslot) {
            $timeslot->delete();
            return ['status' => 'success',
                'message' => 'The proposed appointment was canceled'];
        } else {
            return $reservation;
        }
    }

    public function studentRemove($studentId)
    {
        $student = User::where('id', $studentId)
            ->where('group_id', UserGroup::Student)->first();

        if (!$student) {
            throw new \Exception("Student #$studentId does not exists.");
        }

        $currentAdviser = $student->adviser;
        if (count($currentAdviser)) {
            $currentAdviser[0]->notify(new StudentRemoved($student->name,
                $currentAdviser[0]->name, $currentAdviser[0]->cc_email));
        }

        $student->delete();

        return ['status' => 'success',
            'message' => 'Student has been removed.'];
    }

    public function studentSuspend($studentId)
    {
        $student = User::where('id', $studentId)
            ->where('group_id', UserGroup::Student)->first();

        if (!$student) {
            throw new \Exception("Student #$studentId does not exists or already suspended.");
        }

        $student->group_id = UserGroup::Inactive;
        $student->save();

        $currentAdviser = $student->adviser;
        if (count($currentAdviser)) {
            $currentAdviser[0]->notify(new StudentAccountSuspended($student->name,
                $currentAdviser[0]->name, $currentAdviser[0]->cc_email));
        }

        $student->notify(new AccountSuspended($student->name, $student->cc_email));

        return ['status' => 'success',
            'message' => 'Student\'s account has been suspended.'];
    }

    public function studentActivate($studentId)
    {
        $student = User::where('id', $studentId)
            ->where('group_id', UserGroup::Inactive)->first();

        if (!$student) {
            throw new \Exception("Student #$studentId does not exists or already activated.");
        }

        $student->group_id = UserGroup::Student;
        $student->save();

        $currentAdviser = $student->adviser;
        if (count($currentAdviser)) {
            $currentAdviser[0]->notify(new StudentAccountActivated($student->name,
                $currentAdviser[0]->name, $currentAdviser[0]->cc_email));
        }

        $student->notify(new AccountActivated($student->name, $student->cc_email));

        return ['status' => 'success',
            'message' => 'Student\'s account has been activated.'];
    }

    private function isTooEarlyToChangeReservationStatus($timeslot)
    {
        $timeslotDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$timeslot->date $timeslot->time");
        $nowDateTime = new DateTime();

        if ($timeslotDateTime->getTimestamp() - ($nowDateTime->getTimestamp() + 10*60) > 0) {
            throw new \Exception("Too early to change this reservation's status.");
        }
    }

    public function attendReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);

        // $this->isTooEarlyToChangeReservationStatus($reservation->timeslot);

        if ($reservation->status_id != ReservationStatuses::Booked) {
            throw new \Exception("Reservation was not confirmed and cannot be attended.");
        }

        $reservation->attend($this->id);
        $reservation->status;

        return $reservation;
    }

    public function missReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);

        // $this->isTooEarlyToChangeReservationStatus($reservation->timeslot);

        if ($reservation->status_id != ReservationStatuses::Booked) {
            throw new \Exception("Reservation was not confirmed and cannot be missed.");
        }

        $student = $reservation->student;
        if ($student->is_notification) {
            $student->notify(new StudentMissedReservation($reservation->timeslot->date, $reservation->timeslot->time,
                $student->name, $student->cc_email));
        }

        $reservation->miss($this->id);
        $reservation->status;

        return $reservation;
    }

    public function confirmReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);

        // $this->isTooEarlyToChangeReservationStatus($reservation->timeslot);

        if ($reservation->status_id != ReservationStatuses::Unconfirmed) {
            throw new \Exception("Reservation has different status and cannot be confirmed.");
        }

        $reservation->confirm();
        $reservation->status;

        $student = $reservation->student;
        if ($student->is_notification) {
            $student->notify(new AdviserConfirmedReservation($reservation->timeslot->date, $reservation->timeslot->time,
                $student->name, $student->cc_email));
        }

        return $reservation;
    }

    public function getStudentReservation()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception("Advising period is not yet created");
        }

        $adviser = $this->studentAdviser;
        if (!$adviser) {
            throw new \Exception("You do not have adviser yet");
        }

        $reservation = $this->getCurrentReservation($lastPeriod->id, $adviser[0]->id);

        if ($reservation) {
            if ($reservation->status_id == ReservationStatuses::Advised) {
                throw new \Exception("You already advised in this advising period.");
            }

            $reservation->status;
            $reservation->timeslot;

            return $reservation;
        }

        throw new \Exception("You don't have active reservation yet. Please make it via Appointment.");
    }

    public function getAdviserReservations()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception("Advising period is not yet created");
        }

        return Reservation::whereIn('timeslot_id', Timeslot::select('id')
                ->where('period_id', $lastPeriod->id)
                ->where('adviser_id', $this->id)
                ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->with('timeslot', 'student', 'status')
            ->get();
    }

    public function getAdviserUnconfirmedReservations()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception("Advising period is not yet created");
        }

        return Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->whereIn('status_id', [ReservationStatuses::Unconfirmed]))
            ->with('timeslot', 'student', 'status')
            ->get();
    }

    /**
     * @param $reservationId
     * @return mixed
     * @throws \Exception
     */
    private function getReservationById($reservationId)
    {
        if ($this->group_id == UserGroup::Student) {
            $reservation = Reservation::where('id', $reservationId)
                ->where('advisee_id', $this->id)
                ->first();
        } else {
            // TODO: Only adviser's student reservation (optional)
            $reservation = Reservation::where('id', $reservationId)
                ->first();
        }

        if (!$reservation) {
            throw new \Exception("Reservation was not found in the system or it is not related to your account.");
        }

        if ($reservation->status_id == ReservationStatuses::Booked ||
            $reservation->status_id == ReservationStatuses::Unconfirmed) {
            return $reservation;
        }

        throw new \Exception("Reservation has wrong status (should be 'Booked' or 'Unconfirmed')");
    }

    public function getAdviserStats($withoutMessages = false)
    {
        $stats = [];

        $stats['total_advisee'] = $this->students->count();

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            $stats['reserved'] = 0;
            $stats['attended'] = 0;
            $stats['canceled'] = 0;
            $stats['missed'] = 0;

            $stats['new_reservation'] = 0;
            $stats['new_cancellation'] = 0;

            $stats['today_reservation'] = [];
        }

        $stats['reserved'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->count();

        $stats['attended'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Advised))
            ->count();

        $stats['canceled'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->count();

        $stats['missed'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Missed))
            ->count();

        $stats['new_reservation'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->whereDate('created_at', Carbon::today())
            ->count();

        $stats['new_cancellation'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->whereDate('created_at', Carbon::today())
            ->count();

        $stats['today_reservation'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->whereDate('date', Carbon::today())
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->with('timeslot', 'student')
            ->get();

        if (!$withoutMessages) {
            $stats['recent_message'] = Message::whereIn('chat_id', AdviserAdvisee::select('id')
                ->where('adviser_id', $this->id))
                ->where('sender_id', '<>', $this->id) //TODO: With or without "myself"
                ->orderByDesc('created_at')
                ->with('sender')
                ->limit(5)
                ->get();
        }

        return $stats;
    }

    public function getDirectorStatsForPeriodUnreserved($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $unreserved = User::where('group_id', UserGroup::Student)
            ->whereNotIn('id', Reservation::select('advisee_id')
                ->whereIn('timeslot_id', Timeslot::select('id')
                    ->where('period_id', $period->id)
                    ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Unconfirmed])))
            ->with('faculty', 'adviser')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($unreserved); $i++) {
            if (isset($unreserved[$i]['adviser'][0])) {
                $unreserved[$i]['adviser'] = $unreserved[$i]['adviser'][0];
            } else {
                $unreserved[$i]['adviser'] = null;
            }
        }

        return $unreserved;
    }

    public function getDirectorStatsForPeriodAttended($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $reservations = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Advised))
            ->with('studentForReport', 'timeslot', 'closedBy')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($reservations); $i++) {
            if (isset($reservations[$i]['student_for_report']['adviser'][0])) {
                $reservations[$i]['student_for_report']['adviser'] = $reservations[$i]['student_for_report']['adviser'][0];
            } else {
                $reservations[$i]['student_for_report']['adviser'] = null;
            }

            $reservations[$i]['student'] = $reservations[$i]['student_for_report'];
            unset($reservations[$i]['student_for_report']);
        }

        return $reservations;
    }

    public function getDirectorStatsForPeriodCancelled($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $reservations = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->with('studentForReport', 'timeslot', 'closedBy')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($reservations); $i++) {
            if (isset($reservations[$i]['student_for_report']['adviser'][0])) {
                $reservations[$i]['student_for_report']['adviser'] = $reservations[$i]['student_for_report']['adviser'][0];
            } else {
                $reservations[$i]['student_for_report']['adviser'] = null;
            }

            $reservations[$i]['student'] = $reservations[$i]['student_for_report'];
            unset($reservations[$i]['student_for_report']);
        }

        return $reservations;
    }

    public function getDirectorStatsForPeriodMissed($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $reservations = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Missed))
            ->with('studentForReport', 'timeslot', 'closedBy')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($reservations); $i++) {
            if (isset($reservations[$i]['student_for_report']['adviser'][0])) {
                $reservations[$i]['student_for_report']['adviser'] = $reservations[$i]['student_for_report']['adviser'][0];
            } else {
                $reservations[$i]['student_for_report']['adviser'] = null;
            }

            $reservations[$i]['student'] = $reservations[$i]['student_for_report'];
            unset($reservations[$i]['student_for_report']);
        }

        return $reservations;
    }

    public function getDirectorStatsForPeriodReserved($periodId)
    {
        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $reservations = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->with('studentForReport', 'timeslot', 'closedBy')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($reservations); $i++) {
            if (isset($reservations[$i]['student_for_report']['adviser'][0])) {
                $reservations[$i]['student_for_report']['adviser'] = $reservations[$i]['student_for_report']['adviser'][0];
            } else {
                $reservations[$i]['student_for_report']['adviser'] = null;
            }

            $reservations[$i]['student'] = $reservations[$i]['student_for_report'];
            unset($reservations[$i]['student_for_report']);
        }

        return $reservations;
    }

    public function getDirectorStatsForPeriod($periodId)
    {
        $stats = [];

        $stats['total_advisee'] = User::select('id')
            ->where('group_id', UserGroup::Student)
            ->count();

        $period = Period::where('id', $periodId)->first();
        if (!$period) {
            throw new \Exception("Period #$periodId does not exist.");
        }

        $stats['total_reserved'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->count();

        $stats['total_attended'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Advised))
            ->count();

        $stats['total_canceled'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->count();

        $stats['total_missed'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $period->id)
            ->where('status_id', ReservationStatuses::Missed))
            ->count();

        $total_unreserved = User::where('group_id', UserGroup::Student)
            ->whereNotIn('id', Reservation::select('advisee_id')
                ->whereIn('timeslot_id', Timeslot::select('id')
                    ->where('period_id', $period->id)
                    ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Unconfirmed])))
            ->with('faculty', 'adviser')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($total_unreserved); $i++) {
            if (isset($total_unreserved[$i]['adviser'][0])) {
                $total_unreserved[$i]['adviser'] = $total_unreserved[$i]['adviser'][0];
            } else {
                $total_unreserved[$i]['adviser'] = null;
            }
        }

        $stats['total_unreserved'] = count($total_unreserved);
        $stats['unreserved_user'] = $total_unreserved;

        return $stats;
    }

    public function getDirectorStats()
    {
        $stats = [];

        $stats['total_advisee'] = User::select('id')
            ->where('group_id', UserGroup::Student)
            ->count();

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            $stats['total_reserved'] = 0;
            $stats['total_attended'] = 0;
            $stats['total_canceled'] = 0;
            $stats['total_missed'] = 0;
            $stats['total_unreserved'] = 0;

            $stats['unreserved_user'] = [];
        }

        $stats['total_reserved'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->count();

        $stats['total_attended'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('status_id', ReservationStatuses::Advised))
            ->count();

        $stats['total_canceled'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->count();

        $stats['total_missed'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('status_id', ReservationStatuses::Missed))
            ->count();

        $total_unreserved = User::where('group_id', UserGroup::Student)
            ->whereNotIn('id', Reservation::select('advisee_id')
                ->whereIn('timeslot_id', Timeslot::select('id')
                    ->where('period_id', $lastPeriod->id)
                    ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Unconfirmed])))
            ->with('faculty', 'adviser')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($total_unreserved); $i++) {
            if (isset($total_unreserved[$i]['adviser'][0])) {
                $total_unreserved[$i]['adviser'] = $total_unreserved[$i]['adviser'][0];
            } else {
                $total_unreserved[$i]['adviser'] = null;
            }
        }

        $stats['total_unreserved'] = count($total_unreserved);
        $stats['unreserved_user'] = $total_unreserved;

        return $stats;
    }

    public function getAdviserData($adviserId)
    {
        $adviserData = [];

        $adviser = User::where('id', $adviserId)->with('faculty', 'group')->first();
        $adviserData['adviser'] = $adviser->toArray();
        $adviserData['students'] = $adviser->students;
        $adviserData['report'] = $adviser->getAdviserStatsForDirector();

        return $adviserData;
    }

    private function getAdviserStatsForDirector()
    {
        $stats = [];

        $stats['total_advisee'] = $this->students->count();

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            $stats['attended'] = 0;
            $stats['missed'] = 0;

            $stats['total_reservation'] = 0;
            $stats['total_cancellation'] = 0;
        }

        $stats['attended'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Advised))
            ->count();

        $stats['missed'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Missed))
            ->count();

        $stats['total_reservation'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed]))
            ->count();

        $stats['total_cancellation'] = Reservation::whereIn('timeslot_id', Timeslot::select('id')
            ->where('period_id', $lastPeriod->id)
            ->where('adviser_id', $this->id)
            ->where('status_id', ReservationStatuses::Canceled))
            ->count();

        return $stats;
    }

    public function directorNotifyPeriod()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception('There is no period to notify');
        }

        if ($lastPeriod->is_notified == 1) {
            throw new \Exception('The last period was already notified to advisers.');
        }

        $advisers = User::whereIn("group_id", [UserGroup::Adviser, UserGroup::Director])
            ->where('id', '<>', $this->id)
            ->get();

        foreach ($advisers as $adviser) {
            $adviser->notify(new AdvisingPeriodCreated($lastPeriod, $adviser->name, $adviser->cc_email));
        }

        $lastPeriod->is_notified = 1;
        $lastPeriod->save();

        return ['status' => 'success',
                'message' => 'The system has started to send notifications for advisers.'];
    }

    public function directorNotifyPeriodStatus()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception('There is no period to notify');
        }

        if ($lastPeriod->is_notified == 1) {
            return ['is_notified' => 1,
                'message' => 'The current advising period is already notified to advisers.'];
        }

        return ['is_notified' => 0,
            'message' => 'The current advising period is not yet notified to advisers.'];
    }

    public function adviserNotifyTimeslots()
    {
        $cacheKey = "adv{$this->id}notifyTimeslots";
        if (Cache::has($cacheKey)) {
            throw new \Exception('You can use notification only once per 24 hours.');
        }

        $lastPeriod = Period::orderBy('start_date', 'desc')->first();
        if (!$lastPeriod) {
            throw new \Exception('There is no period to notify');
        }

        $students = $this->students;

        foreach ($students as $student) {
            if (!$student->reservation) {
                $student->notify(new AdvisingTimeslotsCreated($lastPeriod, $student->name, $student->cc_email));
            }
        }

        Cache::put($cacheKey, true, 60 * 60 * 24);

        return ['status' => 'success',
            'message' => 'The system has started to send notifications for students.'];
    }
}
