<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservation';

    protected $fillable = [
        'advisee_id', 'timeslot_id', 'status_id', 'closed_by', 'note',
    ];
}
