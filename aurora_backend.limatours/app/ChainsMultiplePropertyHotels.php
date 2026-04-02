<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChainsMultiplePropertyHotels extends Model
{
    use SoftDeletes;

    public function rates_plans()
    {
        return $this->belongsTo(\App\RatesPlans::class,'rate_plan_id');
    }

}
