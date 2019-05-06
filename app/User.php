<?php

namespace App;

use App\Structures\ReservationStatuses;
use DateTime;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
        'password', 'remember_token', 'google_id'
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
        foreach ($students as $studentId) {
            $isAssigned = AdviserAdvisee::where('adviser_id', $adviserId)
                ->where('advisee_id', $studentId)->first();
            if (!isset($isAssigned)) {
                AdviserAdvisee::create([
                   'adviser_id' => $adviserId,
                   'advisee_id' => $studentId,
                   'director_id' => $this->id,
                ]);
            }
        }
    }

    public function dismissStudents(Array $students, $adviserId)
    {
        foreach ($students as $studentId) {
            $isAssigned = AdviserAdvisee::where('adviser_id', $adviserId)
                ->where('advisee_id', $studentId)->first();
            if (isset($isAssigned)) {
                $isAssigned->delete();
            }
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
        // TODO: Period control, exclude data from previous period.

        return $this->hasOne('App\Reservation', 'advisee_id', 'id')
            ->with('status')
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Advised, ReservationStatuses::Missed])
            ->orderByDesc('created_at');
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
                ->orderByDesc('adviser_advisee.id')->limit(1)->select('user.id', 'user.name', 'user.email', 'user.phone', 'user.office');
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
            ->with('activeReservation')
            ->get();

        return $timeslots;
    }

    public function addTimeslotForDate($date, $time)
    {
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

    public function makeReservation($timeslotId)
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
            if ($reservation->status_id == ReservationStatuses::Booked) {
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

        if ($timeslot->isReserved) {
            throw new \Exception("Timeslot is reserved already");
        }

        return $timeslot->makeReservation($this->id);
    }

    public function getCurrentReservation($lastPeriodId, $adviserId)
    {
        return Reservation::where('advisee_id', $this->id)
            ->whereIn('timeslot_id', Timeslot::select('id')
                                    ->where('period_id', $lastPeriodId)
                                    ->where('adviser_id', $adviserId)
                                    ->whereIn('status_id', [ReservationStatuses::Advised, ReservationStatuses::Booked]))
            ->first();
    }

    public function cancelReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);

        $timeslot = $reservation->timeslot;

        $timeslotDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$timeslot->date $timeslot->time");
        $nowDateTime = new DateTime();
        $minTime = config('app.restrictReservationCancellationTime');

        if ($timeslotDateTime->getTimestamp() - $nowDateTime->getTimestamp() < $minTime) {
            throw new \Exception("Too late to cancel this reservation.");
        }

        $reservation->cancel($this->id);

        $reservation->status;

        return $reservation;
    }

    public function attendReservation($reservationId)
    {
        $reservation = $this->getReservationById($reservationId);

        $reservation->attend($this->id);

        $reservation->status;

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

        throw new \Exception("You don't have active reservation yet.");
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
                ->where('status_id', ReservationStatuses::Booked))
            ->with('timeslot', 'student')
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

        if ($reservation->status_id != ReservationStatuses::Booked) {
            throw new \Exception("Reservation has wrong status (should be 'Booked')");
        }

        return $reservation;
    }
}
