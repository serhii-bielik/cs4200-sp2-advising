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

    public function isReserved()
    {
        return $this->hasOne('App\Reservation', 'timeslot_id')
            ->select('timeslot_id', 'id')->where('status_id', ReservationStatus::Booked)->limit(1);
    }

    public function makeReservation($userId)
    {
        return Reservation::create([
            'advisee_id' => $userId,
            'timeslot_id' => $this->id,
            'status_id' => ReservationStatus::Booked,
        ]);
    }
}
