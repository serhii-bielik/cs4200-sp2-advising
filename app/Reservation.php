<?php

namespace App;

use App\Structures\ReservationStatuses;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservation';

    protected $fillable = [
        'advisee_id', 'timeslot_id', 'status_id', 'closed_by', 'note',
    ];

    public function timeslot()
    {
        return $this->belongsTo('App\Timeslot', 'timeslot_id');
    }

    public function student()
    {
        return $this->belongsTo('App\User', 'advisee_id')->select('id', 'name', 'email', 'phone');
    }

    public function studentFull()
    {
        return $this->belongsTo('App\User', 'advisee_id');
    }

    public function status()
    {
        return $this->hasOne('App\ReservationStatus', 'id', 'status_id');
    }

    public function cancel($userId)
    {
        $this->status_id = ReservationStatuses::Canceled;
        $this->closed_by = $userId;
        $this->save();
    }

    public function attend($userId)
    {
        $this->status_id = ReservationStatuses::Advised;
        $this->closed_by = $userId;
        $this->save();
    }

    public function miss($userId)
    {
        $this->status_id = ReservationStatuses::Missed;
        $this->closed_by = $userId;
        $this->save();
    }
}
