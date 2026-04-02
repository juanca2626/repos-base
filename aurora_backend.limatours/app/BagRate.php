<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BagRate extends Model
{
    use SoftDeletes;

    protected  $fillable = ['bag_room_id','rate_plan_rooms_id'];

    public function bag_room()
    {
        return $this->belongsTo('App\BagRoom');
    }

// @codingStandardsIgnoreLine
    public function rate_plan_room()
    {
        return $this->belongsTo('App\RatesPlansRooms', 'rate_plan_rooms_id');
    }
}
