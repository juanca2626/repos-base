<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
