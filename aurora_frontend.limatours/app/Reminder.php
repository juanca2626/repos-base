<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $table = 'reminder_notification';

    protected $fillable = [
        'title', 'content', 'users',
    ];

    public function user()
    {
        return $this->belongsToMany('App\User', 'user', 'user', 'code', '', 'code');
    }
}
