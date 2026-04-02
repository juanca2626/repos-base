<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'price_total',
    ];

    public function ratesPlansCaldendary(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlansCalendarys', 'rates_plans_calendarys_id');
    }
}
