<?php

namespace App;

abstract class ReservationStatus
{
    const Booked = 1;
    const Canceled = 2;
    const Advised = 3;
    const Missed = 3;
}