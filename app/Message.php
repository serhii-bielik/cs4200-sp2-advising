<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'message';

    public $timestamps = false;

    protected $fillable = [
        'chat_id', 'sender_id', 'content', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'timestamp',
    ];

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_id')
            ->select('id', 'name', 'email', 'phone', 'group_id')
            ->with('group');
    }
}
