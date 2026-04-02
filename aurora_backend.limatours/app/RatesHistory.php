<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RatesHistory extends Model
{

    protected $fillable = [
        'rates_plan_id',
        'meal_id',
        'hotel_id',
        'data',
        'dataRooms'
    ];

    public function ratesPlan()
    {
        return $this->hasOne('App\RatesPlans', 'id', 'rates_plan_id');
    }

    public function policiesRates()
    {
        return $this->hasOne('App\PoliciesRates', 'id', 'policies_rate_id');
    }

    public function meal()
    {
        return $this->hasOne('App\Meal', 'id', 'meal_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
