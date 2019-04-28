<?php

namespace App;

use DateTime;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
                   'director_id' => auth()->user()->id,
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

    public function students()
    {
        return $this->belongsToMany('App\User', 'adviser_advisee', 'adviser_id', 'advisee_id');
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

    public function addPeriod($startDate, $endDate)
    {
        $date = DateTime::createFromFormat('Y-m-d', $startDate);

        $year = intval($date->format('Y'));
        $semester = 1;

        $month = intval($date->format('n'));
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
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}
