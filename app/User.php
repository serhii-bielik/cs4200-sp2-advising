<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use phpDocumentor\Reflection\Types\Integer;

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

    public function students()
    {
        return $this->belongsToMany('App\User', 'adviser_advisee', 'adviser_id', 'advisee_id');
    }
}
