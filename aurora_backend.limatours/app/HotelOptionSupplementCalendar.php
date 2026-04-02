<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelOptionSupplementCalendar extends Model
{
    public function supplement()
    {
        return $this->hasOne('App\Suplement', 'id','supplement_id');
    }

    public function rate_supplements()
    {
        return $this->belongsTo('App\RateSupplement', 'supplement_id','supplement_id');
    }

}
