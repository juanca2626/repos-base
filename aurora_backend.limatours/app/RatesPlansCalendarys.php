<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property  PoliciesCancelations policies_cancelation
 */
class RatesPlansCalendarys extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'date',
        'restriction_status',
        'restriction_arrival',
        'restriction_departure',
        'rates_plans_room_id',
        'policies_rate_id',
        'status'
    ];

    public function ratesPlansRooms()
    {
        return $this->belongsTo('App\RatesPlansRooms', 'rates_plans_room_id', 'id');
    }

    public function ratesPlans()
    {
        return $this->hasOneThrough(
            'App\RatesPlans',
            'App\RatesPlansRooms',
            'rates_plans_id',
            'id',
            'rates_plans_room_id'
        );
    }

    public function policiesRates()
    {
        return $this->hasOne('App\PoliciesRates', 'id', 'policies_rate_id');
    }

    public function policies_rates()
    {
        return $this->hasOne('App\PoliciesRates', 'id', 'policies_rate_id');
    }

    public function policies_cancelation()
    {
        return $this->belongsTo('App\PoliciesCancelations', 'policies_cancelation_id');
    }

    public function rate()
    {
        return $this->hasMany('App\Rates', 'rates_plans_calendarys_id', 'id');
    }

    public function room()
    {
        return $this->hasManyThrough(
            'App\Room',
            'App\RatesPlansRooms',
            'room_id',
            'id',
            'rates_plans_room_id',
            'room_id'
        );
    }

    /**
     * @param $rateType
     * @param $rate
     * @return mixed
     */
    public function getRateByType($rateType, $rate)
    {
        return $this->rate->first(function ($value, $key) use ($rateType, $rate) {
            switch ($rateType) {
                case 'price_adult':
                    return $value->num_adult == $rate['num_adult'];
                    break;
                case 'price_child':
                    return $value->num_child == $rate['num_child'];
                    break;
                case 'price_extra':
                    return $value->price_extra != null;
                    break;
                case 'price_total':
                    return $value->price_total != null and $value->price_total > 0;
                    break;
                default:
                    return false;
            }
        });
    }

    /**
     * @param $rate
     * @return mixed|Rates
     */
    public function addRate($rate)
    {
        $rateSelected = new Rates();
        $rateSelected->fill($rate);

        $this->rate->add($rateSelected);

        return $rateSelected;
    }
}
