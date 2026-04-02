<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allotment extends Model
{
    public function rate_plan_room()
    {
        return $this->belongsTo('App\RatesPlansRooms','rate_plan_rooms_id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}
