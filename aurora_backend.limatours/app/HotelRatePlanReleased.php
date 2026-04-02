<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRatePlanReleased extends Model
{
    use SoftDeletes;
    protected $table = 'hotel_rate_plan_released';

    public function released_ranges()
    {
        return $this->hasMany('App\HotelRatePlanReleasedRange');
    }

    public function rate_plan()
    {
        return $this->belongsTo('App\RatesPlans', 'rate_plan_id');
    }
}
