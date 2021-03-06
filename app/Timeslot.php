<?php

namespace App;

use App\Structures\ReservationStatuses;
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
            ->whereIn('status_id', [ReservationStatuses::Booked, ReservationStatuses::Unconfirmed])->limit(1);
    }

    public function isReserved()
    {
        return $this->hasOne('App\Reservation', 'timeslot_id')
            ->select('timeslot_id', 'id')->whereIn('status_id', [
                ReservationStatuses::Booked, ReservationStatuses::Unconfirmed, ReservationStatuses::Advised])->limit(1);
    }

    public function makeReservation($userId, $isUnconfirmed)
    {
        return Reservation::create([
            'advisee_id' => $userId,
            'timeslot_id' => $this->id,
            'status_id' => $isUnconfirmed ? ReservationStatuses::Unconfirmed : ReservationStatuses::Booked,
        ]);
    }
}
