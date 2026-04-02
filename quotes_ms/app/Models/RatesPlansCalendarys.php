<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property  PoliciesCancelations policies_cancelation
 */
class RatesPlansCalendarys extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'rates_plans_room_id',
        'policies_rate_id',
        'status',
    ];

    public function ratesPlansRooms(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlansRooms', 'rates_plans_room_id', 'id');
    }

    public function ratesPlans(): HasOneThrough
    {
        return $this->hasOneThrough(
            'App\Models\RatesPlans',
            'App\Models\RatesPlansRooms',
            'rates_plans_id',
            'id',
            'rates_plans_room_id'
        );
    }

    public function policiesRates(): HasOne
    {
        return $this->hasOne('App\Models\PoliciesRates', 'id', 'policies_rate_id');
    }

    public function policies_rates(): HasOne
    {
        return $this->hasOne('App\Models\PoliciesRates', 'id', 'policies_rate_id');
    }

    public function policies_cancelation(): BelongsTo
    {
        return $this->belongsTo('App\Models\PoliciesCancelations', 'policies_cancelation_id');
    }

    public function rate(): HasMany
    {
        return $this->hasMany('App\Models\Rates', 'rates_plans_calendarys_id', 'id');
    }

    public function room(): HasManyThrough
    {
        return $this->hasManyThrough(
            'App\Models\Room',
            'App\Models\RatesPlansRooms',
            'room_id',
            'id',
            'rates_plans_room_id',
            'room_id'
        );
    }

    public function getRateByType($rateType, $rate): mixed
    {
        return $this->rate->first(function ($value, $key) use ($rateType, $rate) {
            return match ($rateType) {
                'price_adult' => $value->num_adult == $rate['num_adult'],
                'price_child' => $value->num_child == $rate['num_child'],
                'price_extra' => $value->price_extra != null,
                'price_total' => $value->price_total != null and $value->price_total > 0,
                default       => false,
            };
        });
    }

    public function addRate($rate): Rates
    {
        $rateSelected = new Rates();
        $rateSelected->fill($rate);

        $this->rate->add($rateSelected);

        return $rateSelected;
    }
}
