<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    protected $table = 'timeslot';

    protected $fillable = [
        'adviser_id', 'period_id', 'date', 'time',
    ];

    public function activeReservation()
    {
        return $this->hasOne('App\Reservation', 'timeslot_id')
            ->where('status_id', ReservationStatus::Booked)->limit(1);
    }
}
