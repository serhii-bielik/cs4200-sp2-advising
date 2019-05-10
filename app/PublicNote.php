<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicNote extends Model
{
    protected $table = 'public_note';

    public $timestamps = false;

    protected $fillable = [
        'advisee_id', 'created_by', 'note', 'created_at',
    ];
}
