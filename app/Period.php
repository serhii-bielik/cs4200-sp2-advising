<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $table = 'period';

    protected $fillable = [
        'director_id', 'semester', 'year', 'start_date', 'end_date', 'is_notified'
    ];
}
