<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRatePlanReleasedRangeParam extends Model
{
    use SoftDeletes;

    public function room()
    {
        return $this->belongsTo('App\Room','room_id','id');
    }
}
