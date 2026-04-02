<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRatePlanCalendar extends Model
{

    protected $fillable = ['date', 'status'];

    public function service_rates()
    {
        return $this->belongsTo('App\ServiceRate', 'service_rate_id','id');
    }

}
