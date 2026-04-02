<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationReminder extends Model
{
    use SoftDeletes;

    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }
}
