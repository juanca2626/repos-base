<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRatePlanReleasedRange extends Model
{
    use SoftDeletes;

    public function released_params()
    {
        return $this->hasMany('App\HotelRatePlanReleasedRangeParam');
    }

    public function hotelRatePlanReleased()
    {
        return $this->belongsTo('App\HotelRatePlanReleased','hotel_rate_plan_released_id','id');
    }


}
