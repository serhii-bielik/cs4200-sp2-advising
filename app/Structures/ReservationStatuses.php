<?php

namespace App\Structures;

abstract class ReservationStatuses
{
    const Booked = 1;
    const Canceled = 2;
    const Advised = 3;
    const Missed = 4;
}