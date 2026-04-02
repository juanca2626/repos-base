<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rates extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rates_plans_calendarys_id',
        'num_adult',
        'num_child',
        'num_infant',
        'price_adult',
        'price_child',
        'price_infant',
        'price_extra',
        'price_total'
    ];

    public function ratesPlansCaldendary()
    {
        return $this->belongsTo('App\RatesPlansCalendarys', 'rates_plans_calendarys_id');
    }
}
