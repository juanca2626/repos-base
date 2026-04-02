<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteServiceRoom extends Model
{
    public function service()
    {
        return $this->belongsTo('App\QuoteService','quote_service_id','id');
    }
    public function rate_plan_room()
    {
        return $this->belongsTo('App\RatesPlansRooms');
    }
}
