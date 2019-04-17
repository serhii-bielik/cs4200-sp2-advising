<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrivateNote extends Model
{
    protected $table = 'private_note';

    public $timestamps = false;

    protected $fillable = [
        'advisee_id', 'created_by', 'note', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'timestamp',
    ];
}
