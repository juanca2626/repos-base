<?php

namespace App;

use Illuminate\Database\Eloquent\Model; 

class QuoteServiceRoomHyperguest extends Model
{
    protected $table = 'quote_service_rooms_hyperguest';

    public function room()
    {
        return $this->belongsTo('App\Room');
    }

    public function rate_plan()
    {
        return $this->belongsTo('App\RatesPlans');
    }
}
