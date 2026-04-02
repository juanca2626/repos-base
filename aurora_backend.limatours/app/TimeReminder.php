<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeReminder extends Model
{
    //
    public function type()
    {
        return $this->hasOne('App\TypeReminder', 'id', 'reminder_type_id');
    }
}
