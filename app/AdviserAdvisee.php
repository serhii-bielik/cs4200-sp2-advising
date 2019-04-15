<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdviserAdvisee extends Model
{
    protected $table = 'adviser_advisee';

    public $timestamps = false;

    protected $fillable = [
        'adviser_id', 'advisee_id', 'director_id',
    ];
}
